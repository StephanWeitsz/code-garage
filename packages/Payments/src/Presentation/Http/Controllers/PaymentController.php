<?php

namespace CodeGarage\Payments\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Enrollments\Application\Services\EnrollmentService;
use CodeGarage\Payments\Application\Services\PaymentService;
use CodeGarage\Payments\Infrastructure\Persistence\Eloquent\Models\Payment;

class PaymentController extends Controller
{
    public function index(Request $request, PaymentService $payments): View
    {
        $user = $request->user();
        abort_unless($user?->can('payments.view-own') || $user?->can('payments.view'), 403);

        $records = $user->can('payments.view')
            ? $payments->reviewQueueFor($user, 150)
            : $payments->forUser($user->id, 100);

        return view('payments::index', [
            'payments' => $records,
            'canReview' => $user->can('payments.view'),
        ]);
    }

    public function review(Request $request, PaymentService $payments): View
    {
        $user = $request->user();
        abort_unless($user?->can('payments.view'), 403);

        $courses = Course::query()
            ->when(
                $user->hasRole('lecturer') && ! $user->hasRole('admin'),
                fn ($query) => $query->where('lecturer_id', $user->id),
            )
            ->orderBy('title')
            ->get();

        return view('payments::review', [
            'payments' => $payments->reviewQueueFor($user, 200),
            'courses' => $courses,
            'students' => \App\Models\User::query()
                ->role('student')
                ->orderBy('name')
                ->limit(200)
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function checkout(
        Course $course,
        Request $request,
        EnrollmentService $enrollments,
        PaymentService $payments,
    ): View|RedirectResponse {
        $user = $request->user();
        abort_unless($user?->can('enrollments.create'), 403);

        if ($course->pricing_type === 'free') {
            return redirect()
                ->route('courses.show', $course->slug)
                ->with('status', 'This is a free course, so payment is not required.');
        }

        if ($enrollments->isEnrolled($user->id, $course->id)) {
            return redirect()
                ->route('courses.show', $course->slug)
                ->with('status', 'You are already enrolled in this course.');
        }

        return view('payments::checkout', [
            'course' => $course,
            'payments' => Payment::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->latest('id')
                ->limit(10)
                ->get(),
            'hasPaidAccess' => $payments->hasPaidEnrollmentAccess($user->id, $course->id),
        ]);
    }

    public function startPortal(Course $course, Request $request, PaymentService $payments): RedirectResponse|Response
    {
        $user = $request->user();
        abort_unless($user?->can('enrollments.create'), 403);
        abort_if($course->pricing_type === 'free', 422);

        $channel = (string) $request->input('channel', 'payfast');

        if (! in_array($channel, ['payfast'], true)) {
            $channel = 'payfast';
        }

        if ($channel === 'payfast') {
            if (blank(config('payments.payfast.merchant_id')) || blank(config('payments.payfast.merchant_key'))) {
                return back()->with('status', 'PayFast is not configured yet. Add merchant credentials in environment settings first.');
            }

            $payment = $payments->createPayFastIntent($user->id, $course);
            $nameParts = collect(explode(' ', trim((string) $user->name)));
            $firstName = (string) ($nameParts->shift() ?? '');
            $lastName = (string) $nameParts->implode(' ');

            return response()
                ->view('payments::payfast-redirect', [
                    'payment' => $payment,
                    'payfastUrl' => $payments->payFastProcessUrl(),
                    'fields' => $payments->buildPayFastFields($payment, [
                        'name_first' => $firstName,
                        'name_last' => $lastName,
                        'email_address' => $user->email,
                        'item_name' => Str::limit($course->title, 90, ''),
                        'item_description' => Str::limit($course->description ?? $course->title, 220, ''),
                    ]),
                ])
                ->setStatusCode(200);
        }

        return back()->with('status', 'Unsupported payment channel selected.');
    }

    public function payfastReturn(string $reference): RedirectResponse
    {
        $payment = Payment::query()->where('reference', $reference)->firstOrFail();

        return redirect()
            ->route('payments.checkout', $payment->course_id)
            ->with('status', 'Returned from PayFast. Payment confirmation is processed automatically. If verification is delayed, refresh shortly.');
    }

    public function payfastCancel(string $reference): RedirectResponse
    {
        $payment = Payment::query()->where('reference', $reference)->firstOrFail();

        if ($payment->status === 'pending') {
            $payment->forceFill(['status' => 'cancelled'])->save();
        }

        return redirect()
            ->route('payments.checkout', $payment->course_id)
            ->with('status', 'PayFast payment was cancelled. You can retry checkout anytime.');
    }

    public function payfastNotify(Request $request, PaymentService $payments): Response
    {
        $payload = $request->post();
        $reference = (string) ($payload['m_payment_id'] ?? '');

        if ($reference === '') {
            return response('Missing payment reference.', 400);
        }

        $payment = Payment::query()->where('reference', $reference)->first();
        if ($payment === null) {
            return response('Unknown payment reference.', 404);
        }

        if ($payment->status === 'paid') {
            return response('OK', 200);
        }

        if ((string) ($payload['merchant_id'] ?? '') !== (string) config('payments.payfast.merchant_id')) {
            return response('Merchant mismatch.', 400);
        }

        if (! $payments->verifyPayFastSignature($payload)) {
            return response('Invalid signature.', 400);
        }

        $sandbox = (bool) data_get($payment->metadata, 'sandbox', true);
        if (! $payments->verifyPayFastServerConfirmation($payload, $sandbox)) {
            return response('Server confirmation failed.', 400);
        }

        $expectedAmount = number_format((float) $payment->amount, 2, '.', '');
        $reportedAmount = number_format((float) $request->input('amount_gross', 0), 2, '.', '');
        if ($expectedAmount !== $reportedAmount) {
            return response('Amount mismatch.', 400);
        }

        $paymentStatus = strtoupper((string) $request->input('payment_status', ''));
        if ($paymentStatus === 'COMPLETE') {
            $payments->markAsPaidByGateway($payment, $payload);

            return response('OK', 200);
        }

        if (in_array($paymentStatus, ['FAILED', 'CANCELLED'], true)) {
            $payments->markAsFailedByGateway($payment, $payload);
        }

        return response('OK', 200);
    }

    public function submitBankTransfer(Course $course, Request $request, PaymentService $payments): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->can('enrollments.create'), 403);
        abort_if($course->pricing_type === 'free', 422);

        $validated = $request->validate([
            'payer_name' => ['required', 'string', 'max:120'],
            'transfer_reference' => ['required', 'string', 'max:120'],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'proof_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('proof_file')) {
            $path = $request->file('proof_file')->store('payments/proofs', 'public');
            $validated['proof_file_path'] = $path;
            $validated['proof_file_original_name'] = $request->file('proof_file')->getClientOriginalName();
        }

        $payments->submitBankTransferProof($user->id, $course, $validated);

        return back()->with('status', 'Proof submitted. A lecturer/admin needs to verify the payment before enrollment is unlocked.');
    }

    public function markPaid(Payment $payment, Request $request, PaymentService $payments): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->can('payments.mark-paid'), 403);

        if ($user->hasRole('lecturer') && ! $user->hasRole('admin')) {
            abort_unless((int) ($payment->course?->lecturer_id ?? 0) === (int) $user->id, 403);
        }

        $payments->markAsPaid($payment, $user->id);

        return back()->with('status', 'Payment marked as paid. Student can now enroll.');
    }

    public function reject(Payment $payment, Request $request, PaymentService $payments): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->can('payments.mark-paid'), 403);

        if ($user->hasRole('lecturer') && ! $user->hasRole('admin')) {
            abort_unless((int) ($payment->course?->lecturer_id ?? 0) === (int) $user->id, 403);
        }

        $validated = $request->validate([
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $payments->markAsRejected($payment, $user->id, $validated['review_note'] ?? null);

        return back()->with('status', 'Payment marked as rejected.');
    }

    public function sendReminder(Payment $payment, Request $request, PaymentService $payments): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $isOwner = (int) $payment->user_id === (int) $user->id;
        $isManager = $user->can('payments.view')
            && ($user->hasRole('admin') || (int) ($payment->course?->lecturer_id ?? 0) === (int) $user->id);

        abort_unless($isOwner || $isManager, 403);
        abort_if($payment->status === 'paid', 422);

        $sent = $payments->sendOutstandingReminder($payment, true);

        return back()->with(
            'status',
            $sent
                ? 'Outstanding payment reminder email sent.'
                : 'Reminder not sent. Payment may already be settled or recipient unavailable.',
        );
    }

    public function collectManual(Course $course, Request $request, PaymentService $payments): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->can('payments.collect'), 403);

        if ($user->hasRole('lecturer') && ! $user->hasRole('admin')) {
            abort_unless((int) $course->lecturer_id === (int) $user->id, 403);
        }

        $validated = $request->validate([
            'student_user_id' => ['required', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $payments->recordManualCollection(
            (int) $validated['student_user_id'],
            $course,
            $user->id,
            $validated['notes'] ?? null,
        );

        return back()->with('status', 'Manual cash payment recorded successfully.');
    }

    public function downloadProof(Payment $payment, Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $isOwner = (int) $payment->user_id === (int) $user->id;
        $isManager = $user->can('payments.view')
            && ($user->hasRole('admin') || (int) ($payment->course?->lecturer_id ?? 0) === (int) $user->id);

        abort_unless($isOwner || $isManager, 403);

        $path = (string) data_get($payment->metadata, 'proof_file_path', '');
        abort_if($path === '' || ! Storage::disk('public')->exists($path), 404);

        $name = (string) data_get($payment->metadata, 'proof_file_original_name', basename($path));

        return Storage::disk('public')->download($path, $name);
    }
}
