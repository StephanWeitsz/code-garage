<?php

namespace Tests\Feature\Payments;

use App\Enums\CourseStatus;
use App\Enums\DifficultyLevel;
use App\Models\User;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Payments\Application\Services\PaymentService;
use CodeGarage\Payments\Infrastructure\Persistence\Eloquent\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PayFastIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_start_payfast_checkout(): void
    {
        [$student, $course] = $this->studentAndPaidCourse();

        config()->set('payments.payfast.merchant_id', '10000100');
        config()->set('payments.payfast.merchant_key', '46f0cd694581a');
        config()->set('payments.payfast.passphrase', 'pass123');
        config()->set('payments.payfast.sandbox', true);

        $response = $this->actingAs($student)->post("/payments/checkout/{$course->id}/portal", [
            'channel' => 'payfast',
        ]);

        $response->assertOk();
        $response->assertSee('Redirecting to PayFast', false);
        $response->assertSee('sandbox.payfast.co.za/eng/process', false);

        $this->assertDatabaseHas('payments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'channel' => 'payfast',
            'status' => 'pending',
        ]);
    }

    public function test_payfast_notify_marks_payment_as_paid_when_signature_and_validation_pass(): void
    {
        [$student, $course] = $this->studentAndPaidCourse();

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
            'reference' => 'PFS-TESTPFAAA',
            'metadata' => [
                'sandbox' => true,
            ],
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

        $signature = app(PaymentService::class)->generatePayFastSignature($payload);
        $payload['signature'] = $signature;

        $this->post('/payments/payfast/notify', $payload)
            ->assertOk()
            ->assertSeeText('OK');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
        ]);
    }

    private function studentAndPaidCourse(): array
    {
        $lecturer = User::factory()->create();
        $student = User::factory()->create();

        Role::findOrCreate('student', 'web');
        $student->assignRole('student');

        Permission::findOrCreate('enrollments.create', 'web');
        $student->givePermissionTo('enrollments.create');

        $course = Course::query()->create([
            'lecturer_id' => $lecturer->id,
            'title' => 'PayFast Course '.uniqid(),
            'description' => 'Course for PayFast tests.',
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
}
