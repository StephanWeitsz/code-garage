<?php

namespace Tests\Feature\Payments;

use App\Enums\CourseStatus;
use App\Enums\DifficultyLevel;
use App\Models\User;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Payments\Application\Services\PaymentService;
use CodeGarage\Payments\Infrastructure\Persistence\Eloquent\Models\Payment;
use CodeGarage\Payments\Presentation\Mail\OutstandingPaymentReminderMail;
use CodeGarage\Payments\Presentation\Mail\PaymentInvoiceMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentEmailNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_email_is_sent_when_payfast_marks_payment_paid(): void
    {
        Mail::fake();
        [$student, $course] = $this->studentAndCourse();

        config()->set('payments.payfast.merchant_id', '10000100');
        config()->set('payments.payfast.merchant_key', '46f0cd694581a');
        config()->set('payments.payfast.passphrase', 'pass123');
        config()->set('payments.payfast.sandbox', true);
        config()->set('payments.payfast.sandbox_validate_url', 'https://sandbox.payfast.co.za/eng/query/validate');

        $payment = Payment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'channel' => 'payfast',
            'status' => 'pending',
            'amount' => 499.00,
            'currency' => 'ZAR',
            'reference' => 'PFS-EMAIL-1001',
            'metadata' => ['sandbox' => true],
        ]);

        Http::fake([
            'sandbox.payfast.co.za/eng/query/validate' => Http::response('VALID', 200),
        ]);

        $payload = [
            'merchant_id' => '10000100',
            'merchant_key' => '46f0cd694581a',
            'm_payment_id' => $payment->reference,
            'amount_gross' => '499.00',
            'payment_status' => 'COMPLETE',
            'item_name' => 'Course payment',
        ];
        $payload['signature'] = app(PaymentService::class)->generatePayFastSignature($payload);

        $this->post('/payments/payfast/notify', $payload)->assertOk();

        Mail::assertSent(PaymentInvoiceMail::class, function (PaymentInvoiceMail $mail) use ($payment): bool {
            return $mail->payment->reference === $payment->reference;
        });
    }

    public function test_outstanding_reminder_can_be_sent_for_pending_payment(): void
    {
        Mail::fake();
        [$student, $course, $lecturer] = $this->studentCourseAndLecturer();

        $payment = Payment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'channel' => 'payfast',
            'status' => 'pending',
            'amount' => 499.00,
            'currency' => 'ZAR',
            'reference' => 'PFS-EMAIL-1002',
        ]);

        $this->actingAs($lecturer)
            ->post('/payments/'.$payment->id.'/send-reminder')
            ->assertRedirect();

        Mail::assertSent(OutstandingPaymentReminderMail::class, function (OutstandingPaymentReminderMail $mail) use ($payment): bool {
            return $mail->payment->reference === $payment->reference;
        });
    }

    private function studentAndCourse(): array
    {
        $lecturer = User::factory()->create();
        $student = User::factory()->create();

        Role::findOrCreate('student', 'web');
        $student->assignRole('student');

        Permission::findOrCreate('enrollments.create', 'web');
        $student->givePermissionTo('enrollments.create');

        $course = Course::query()->create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Email Course '.uniqid(),
            'description' => 'Course for payment email tests.',
            'difficulty_level' => DifficultyLevel::Beginner->value,
            'category' => 'Business',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => 'once_off',
            'pricing_amount' => 499.00,
            'pricing_currency' => 'ZAR',
        ]);

        return [$student, $course];
    }

    private function studentCourseAndLecturer(): array
    {
        [$student, $course] = $this->studentAndCourse();
        $lecturer = User::query()->findOrFail($course->lecturer_id);

        Role::findOrCreate('lecturer', 'web');
        $lecturer->assignRole('lecturer');
        Permission::findOrCreate('payments.view', 'web');
        $lecturer->givePermissionTo('payments.view');

        return [$student, $course, $lecturer];
    }
}
