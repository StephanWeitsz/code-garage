<?php

namespace Tests\Feature\Payments;

use App\Enums\CourseStatus;
use App\Enums\DifficultyLevel;
use App\Models\User;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Payments\Infrastructure\Persistence\Eloquent\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_payment_index(): void
    {
        [$student, $course] = $this->studentAndCourse();

        Payment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'channel' => 'bank_transfer',
            'status' => 'awaiting_verification',
            'amount' => 499.00,
            'currency' => 'ZAR',
            'reference' => 'BNK-OPS-1001',
        ]);

        $this->actingAs($student)
            ->get('/payments')
            ->assertOk()
            ->assertSeeText('Payment history')
            ->assertSeeText('BNK-OPS-1001');
    }

    public function test_lecturer_can_open_review_queue_and_record_manual_cash(): void
    {
        [$student, $course, $lecturer] = $this->studentCourseAndLecturer();

        $this->actingAs($lecturer)
            ->get('/payments/review')
            ->assertOk()
            ->assertSeeText('Payment review queue');

        $this->actingAs($lecturer)
            ->post("/payments/collect/{$course->id}", [
                'student_user_id' => $student->id,
                'notes' => 'Paid in person at campus desk.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'channel' => 'manual_cash',
            'status' => 'paid',
        ]);
    }

    public function test_student_can_submit_eft_with_proof_file_and_download_it(): void
    {
        Storage::fake('public');
        [$student, $course] = $this->studentAndCourse();

        $this->actingAs($student)
            ->post("/payments/checkout/{$course->id}/bank-transfer", [
                'payer_name' => 'Sam Student',
                'transfer_reference' => 'STU-TRF-100',
                'paid_at' => now()->toDateTimeString(),
                'notes' => 'Paid from FNB app',
                'proof_file' => UploadedFile::fake()->create('proof.pdf', 120, 'application/pdf'),
            ])
            ->assertRedirect();

        $payment = Payment::query()->latest('id')->first();
        $this->assertNotNull($payment);
        $this->assertNotEmpty(data_get($payment->metadata, 'proof_file_path'));

        Storage::disk('public')->assertExists((string) data_get($payment->metadata, 'proof_file_path'));

        $this->actingAs($student)
            ->get('/payments/'.$payment->id.'/proof')
            ->assertOk();
    }

    private function studentAndCourse(): array
    {
        $lecturer = User::factory()->create();
        Role::findOrCreate('lecturer', 'web');
        $lecturer->assignRole('lecturer');

        $student = User::factory()->create();
        Role::findOrCreate('student', 'web');
        $student->assignRole('student');

        Permission::findOrCreate('enrollments.create', 'web');
        Permission::findOrCreate('payments.view-own', 'web');
        $student->givePermissionTo(['enrollments.create', 'payments.view-own']);

        $course = Course::query()->create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Payments Ops '.uniqid(),
            'description' => 'Operations flow course.',
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

        Permission::findOrCreate('payments.view', 'web');
        Permission::findOrCreate('payments.collect', 'web');
        $lecturer->givePermissionTo(['payments.view', 'payments.collect']);

        return [$student, $course, $lecturer];
    }
}
