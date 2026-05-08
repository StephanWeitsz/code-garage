<?php

namespace Tests\Feature\Payments;

use App\Enums\CourseStatus;
use App\Enums\DifficultyLevel;
use App\Models\User;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\Enrollment;
use CodeGarage\Payments\Infrastructure\Persistence\Eloquent\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentEnrollmentGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_course_allows_direct_enrollment(): void
    {
        [$student, $course] = $this->studentAndCourse('free');

        $this->actingAs($student)
            ->post('/enrollments', ['course_id' => $course->id])
            ->assertRedirect();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_paid_course_redirects_to_checkout_when_no_paid_payment_exists(): void
    {
        [$student, $course] = $this->studentAndCourse('once_off', 799.00);

        $this->actingAs($student)
            ->post('/enrollments', ['course_id' => $course->id])
            ->assertRedirect("/payments/checkout/{$course->id}");

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_paid_course_allows_enrollment_after_paid_payment_record_exists(): void
    {
        [$student, $course] = $this->studentAndCourse('once_off', 1499.00);

        Payment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'channel' => 'bank_transfer',
            'status' => 'paid',
            'amount' => 1499.00,
            'currency' => 'ZAR',
            'reference' => 'BNK-TESTPAID1',
            'paid_at' => now(),
            'verified_at' => now(),
            'verified_by' => $student->id,
        ]);

        $this->actingAs($student)
            ->post('/enrollments', ['course_id' => $course->id])
            ->assertRedirect();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $this->assertEquals(1, Enrollment::query()->where('user_id', $student->id)->where('course_id', $course->id)->count());
    }

    public function test_checkout_page_displays_bank_transfer_account_details(): void
    {
        [$student, $course] = $this->studentAndCourse('once_off', 499.00);

        $this->actingAs($student)
            ->get("/payments/checkout/{$course->id}")
            ->assertOk()
            ->assertSeeText('Bank transfer (EFT)')
            ->assertSeeText('Code Garage (Pty) Ltd');
    }

    private function studentAndCourse(string $pricingType, ?float $amount = null): array
    {
        $lecturer = User::factory()->create();
        $student = User::factory()->create();

        Role::findOrCreate('student', 'web');
        $student->assignRole('student');

        Permission::findOrCreate('enrollments.create', 'web');
        $student->givePermissionTo('enrollments.create');

        $course = Course::query()->create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Payments Foundations '.uniqid(),
            'description' => 'Course for enrollment/payment flow tests.',
            'difficulty_level' => DifficultyLevel::Beginner->value,
            'category' => 'Business',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => $pricingType,
            'pricing_amount' => $amount,
            'pricing_currency' => 'ZAR',
        ]);

        return [$student, $course];
    }
}
