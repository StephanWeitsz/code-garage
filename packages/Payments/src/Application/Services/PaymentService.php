<?php

namespace CodeGarage\Payments\Application\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Payments\Infrastructure\Persistence\Eloquent\Models\Payment;
use CodeGarage\Payments\Presentation\Mail\OutstandingPaymentReminderMail;
use CodeGarage\Payments\Presentation\Mail\PaymentInvoiceMail;

class PaymentService
{
    public function forUser(int $userId, int $limit = 50): Collection
    {
        return Payment::query()
            ->with(['course', 'verifier'])
            ->where('user_id', $userId)
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function reviewQueueFor(User $user, int $limit = 100): Collection
    {
        $query = Payment::query()
            ->with(['user', 'course', 'verifier'])
            ->latest('id');

        if ($user->hasRole('admin')) {
            return $query->limit($limit)->get();
        }

        return $query
            ->whereHas('course', fn ($courseQuery) => $courseQuery->where('lecturer_id', $user->id))
            ->limit($limit)
            ->get();
    }

    public function hasPaidEnrollmentAccess(int $userId, int $courseId): bool
    {
        return Payment::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('status', 'paid')
            ->exists();
    }

    public function createPortalIntent(int $userId, Course $course, string $channel = 'portal'): Payment
    {
        return DB::transaction(function () use ($userId, $course, $channel) {
            $payment = Payment::query()->create([
                'user_id' => $userId,
                'course_id' => $course->id,
                'channel' => $channel,
                'status' => 'pending',
                'amount' => $course->pricing_amount ?? 0,
                'currency' => $course->pricing_currency ?? 'ZAR',
                'reference' => $this->newReference('PTL'),
                'metadata' => [
                    'pricing_type' => $course->pricing_type,
                ],
            ]);

            $this->sendOutstandingReminder($payment, true);

            return $payment;
        });
    }

    public function createPayFastIntent(int $userId, Course $course): Payment
    {
        return DB::transaction(function () use ($userId, $course) {
            $payment = Payment::query()->create([
                'user_id' => $userId,
                'course_id' => $course->id,
                'channel' => 'payfast',
                'status' => 'pending',
                'amount' => $course->pricing_amount ?? 0,
                'currency' => $course->pricing_currency ?? 'ZAR',
                'reference' => $this->newReference('PFS'),
                'metadata' => [
                    'pricing_type' => $course->pricing_type,
                    'provider' => 'payfast',
                    'sandbox' => (bool) config('payments.payfast.sandbox', true),
                ],
            ]);

            $this->sendOutstandingReminder($payment, true);

            return $payment;
        });
    }

    public function submitBankTransferProof(int $userId, Course $course, array $attributes): Payment
    {
        return DB::transaction(function () use ($userId, $course, $attributes) {
            $metadata = [
                'pricing_type' => $course->pricing_type,
                'submitted_from_checkout' => true,
            ];

            if (! empty($attributes['proof_file_path'])) {
                $metadata['proof_file_path'] = $attributes['proof_file_path'];
                $metadata['proof_file_original_name'] = $attributes['proof_file_original_name'] ?? null;
            }

            return Payment::query()->create([
                'user_id' => $userId,
                'course_id' => $course->id,
                'channel' => 'bank_transfer',
                'status' => 'awaiting_verification',
                'amount' => $course->pricing_amount ?? 0,
                'currency' => $course->pricing_currency ?? 'ZAR',
                'reference' => $this->newReference('BNK'),
                'payer_name' => $attributes['payer_name'] ?? null,
                'transfer_reference' => $attributes['transfer_reference'] ?? null,
                'paid_at' => $attributes['paid_at'] ?? null,
                'notes' => $attributes['notes'] ?? null,
                'metadata' => $metadata,
            ]);
        });
    }

    public function markAsPaid(Payment $payment, int $verifiedByUserId): Payment
    {
        $payment->forceFill([
            'status' => 'paid',
            'paid_at' => $payment->paid_at ?? now(),
            'verified_at' => now(),
            'verified_by' => $verifiedByUserId,
        ])->save();

        $payment = $payment->refresh();
        $this->sendInvoice($payment);

        return $payment;
    }

    public function markAsPaidByGateway(Payment $payment, array $gatewayPayload = []): Payment
    {
        $metadata = (array) ($payment->metadata ?? []);
        $metadata['gateway'] = [
            'name' => 'payfast',
            'payload' => $gatewayPayload,
            'paid_at' => now()->toIso8601String(),
        ];

        $payment->forceFill([
            'status' => 'paid',
            'paid_at' => $payment->paid_at ?? now(),
            'metadata' => $metadata,
        ])->save();

        $payment = $payment->refresh();
        $this->sendInvoice($payment);

        return $payment;
    }

    public function markAsFailedByGateway(Payment $payment, array $gatewayPayload = []): Payment
    {
        $metadata = (array) ($payment->metadata ?? []);
        $metadata['gateway'] = [
            'name' => 'payfast',
            'payload' => $gatewayPayload,
            'failed_at' => now()->toIso8601String(),
        ];

        $payment->forceFill([
            'status' => 'failed',
            'metadata' => $metadata,
        ])->save();

        return $payment->refresh();
    }

    public function markAsRejected(Payment $payment, int $reviewedByUserId, ?string $notes = null): Payment
    {
        $metadata = (array) ($payment->metadata ?? []);
        $metadata['review'] = [
            'status' => 'rejected',
            'reviewed_by' => $reviewedByUserId,
            'reviewed_at' => now()->toIso8601String(),
        ];

        $existingNotes = trim((string) $payment->notes);
        $appendedNotes = trim((string) $notes);
        $mergedNotes = $existingNotes;

        if ($appendedNotes !== '') {
            $mergedNotes = $existingNotes !== ''
                ? $existingNotes."\n\nRejected note: ".$appendedNotes
                : 'Rejected note: '.$appendedNotes;
        }

        $payment->forceFill([
            'status' => 'rejected',
            'verified_at' => now(),
            'verified_by' => $reviewedByUserId,
            'notes' => $mergedNotes !== '' ? $mergedNotes : null,
            'metadata' => $metadata,
        ])->save();

        return $payment->refresh();
    }

    public function payFastProcessUrl(): string
    {
        return (bool) config('payments.payfast.sandbox', true)
            ? (string) config('payments.payfast.sandbox_url')
            : (string) config('payments.payfast.live_url');
    }

    public function payFastValidateUrl(bool $sandbox): string
    {
        return $sandbox
            ? (string) config('payments.payfast.sandbox_validate_url')
            : (string) config('payments.payfast.live_validate_url');
    }

    public function buildPayFastFields(Payment $payment, array $customer): array
    {
        $returnUrl = route('payments.payfast.return', $payment->reference);
        $cancelUrl = route('payments.payfast.cancel', $payment->reference);
        $notifyUrl = route('payments.payfast.notify');

        $fields = [
            'merchant_id' => (string) config('payments.payfast.merchant_id'),
            'merchant_key' => (string) config('payments.payfast.merchant_key'),
            'return_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
            'notify_url' => $notifyUrl,
            'name_first' => (string) ($customer['name_first'] ?? ''),
            'name_last' => (string) ($customer['name_last'] ?? ''),
            'email_address' => (string) ($customer['email_address'] ?? ''),
            'm_payment_id' => $payment->reference,
            'amount' => number_format((float) $payment->amount, 2, '.', ''),
            'item_name' => (string) ($customer['item_name'] ?? 'Course payment'),
            'item_description' => (string) ($customer['item_description'] ?? 'Course payment'),
            'custom_str1' => (string) $payment->course_id,
            'custom_str2' => (string) $payment->user_id,
        ];

        $fields['signature'] = $this->generatePayFastSignature($fields);

        return $fields;
    }

    public function generatePayFastSignature(array $fields): string
    {
        $pairs = [];

        foreach ($fields as $key => $value) {
            if ($key === 'signature') {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $pairs[] = $key.'='.urlencode(trim((string) $value));
        }

        $passphrase = (string) config('payments.payfast.passphrase', '');
        if ($passphrase !== '') {
            $pairs[] = 'passphrase='.urlencode($passphrase);
        }

        return md5(implode('&', $pairs));
    }

    public function verifyPayFastSignature(array $payload): bool
    {
        $incoming = (string) ($payload['signature'] ?? '');

        if ($incoming === '') {
            return false;
        }

        $expected = $this->generatePayFastSignature($payload);

        return hash_equals($expected, $incoming);
    }

    public function verifyPayFastServerConfirmation(array $payload, bool $sandbox): bool
    {
        $validateUrl = $this->payFastValidateUrl($sandbox);
        $response = Http::asForm()->post($validateUrl, $payload);

        if (! $response->successful()) {
            return false;
        }

        return strtoupper(trim($response->body())) === 'VALID';
    }

    public function recordManualCollection(int $studentUserId, Course $course, int $collectorUserId, ?string $notes = null): Payment
    {
        return DB::transaction(function () use ($studentUserId, $course, $collectorUserId, $notes) {
            $payment = Payment::query()->create([
                'user_id' => $studentUserId,
                'course_id' => $course->id,
                'channel' => 'manual_cash',
                'status' => 'paid',
                'amount' => $course->pricing_amount ?? 0,
                'currency' => $course->pricing_currency ?? 'ZAR',
                'reference' => $this->newReference('CSH'),
                'paid_at' => now(),
                'verified_at' => now(),
                'verified_by' => $collectorUserId,
                'notes' => $notes,
                'metadata' => [
                    'pricing_type' => $course->pricing_type,
                    'recorded_manually' => true,
                ],
            ]);

            $this->sendInvoice($payment);

            return $payment;
        });
    }

    public function sendInvoice(Payment $payment): void
    {
        $payment->loadMissing('user', 'course');

        $recipient = $payment->user?->email;
        if (blank($recipient)) {
            return;
        }

        Mail::to($recipient)->send(new PaymentInvoiceMail($payment));
    }

    public function sendOutstandingReminder(Payment $payment, bool $force = false): bool
    {
        $payment->loadMissing('user', 'course');

        if (in_array($payment->status, ['paid', 'cancelled', 'rejected'], true)) {
            return false;
        }

        $recipient = $payment->user?->email;
        if (blank($recipient)) {
            return false;
        }

        if (! $force && ! $this->canSendReminder($payment)) {
            return false;
        }

        Mail::to($recipient)->send(new OutstandingPaymentReminderMail($payment));

        $metadata = (array) ($payment->metadata ?? []);
        $count = (int) data_get($metadata, 'reminders.count', 0);
        $metadata['reminders'] = [
            'count' => $count + 1,
            'last_sent_at' => now()->toIso8601String(),
        ];

        $payment->forceFill([
            'metadata' => $metadata,
        ])->save();

        return true;
    }

    public function sendScheduledOutstandingReminders(bool $force = false): int
    {
        $statuses = (array) config('payments.reminders.statuses', ['pending', 'awaiting_verification', 'failed']);
        $minAgeDays = (int) config('payments.reminders.min_payment_age_days', 1);
        $eligibleBefore = now()->subDays(max(0, $minAgeDays));
        $sent = 0;

        Payment::query()
            ->whereIn('status', $statuses)
            ->where('created_at', '<=', $eligibleBefore)
            ->orderBy('id')
            ->chunkById(100, function ($payments) use (&$sent, $force) {
                foreach ($payments as $payment) {
                    if ($this->sendOutstandingReminder($payment, $force)) {
                        $sent++;
                    }
                }
            });

        return $sent;
    }

    protected function canSendReminder(Payment $payment): bool
    {
        $metadata = (array) ($payment->metadata ?? []);
        $count = (int) data_get($metadata, 'reminders.count', 0);
        $lastSentAt = data_get($metadata, 'reminders.last_sent_at');

        $maxEmails = (int) config('payments.reminders.max_emails', 5);
        if ($maxEmails > 0 && $count >= $maxEmails) {
            return false;
        }

        $minIntervalHours = (int) config('payments.reminders.min_interval_hours', 24);
        if ($lastSentAt === null || $minIntervalHours <= 0) {
            return true;
        }

        try {
            $last = Carbon::parse((string) $lastSentAt);
        } catch (\Throwable) {
            return true;
        }

        return $last->diffInHours(now()) >= $minIntervalHours;
    }

    protected function newReference(string $prefix): string
    {
        return sprintf('%s-%s', $prefix, Str::upper(Str::random(10)));
    }
}
