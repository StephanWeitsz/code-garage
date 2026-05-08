<?php

namespace Tests\Feature\Payments;

use App\Enums\CourseStatus;
use App\Enums\DifficultyLevel;
use App\Models\User;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Payments\Infrastructure\Persistence\Eloquent\Models\Payment;
use CodeGarage\Payments\Presentation\Mail\OutstandingPaymentReminderMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OutstandingReminderScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduled_command_sends_reminders_for_eligible_outstanding_payments(): void
    {
        Mail::fake();
        config()->set('payments.reminders.min_payment_age_days', 0);

        [$student, $course] = $this->studentAndCourse();

        Payment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'channel' => 'payfast',
            'status' => 'pending',
            'amount' => 499.00,
            'currency' => 'ZAR',
            'reference' => 'REM-ELIGIBLE-1',
        ]);

        $this->artisan('payments:send-outstanding-reminders')
            ->assertExitCode(0);

        Mail::assertSent(OutstandingPaymentReminderMail::class, 1);
    }

    public function test_scheduled_command_skips_recently_reminded_payment_without_force(): void
    {
        Mail::fake();
        config()->set('payments.reminders.min_payment_age_days', 0);

        [$student, $course] = $this->studentAndCourse();

        Payment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'channel' => 'payfast',
            'status' => 'pending',
            'amount' => 499.00,
            'currency' => 'ZAR',
            'reference' => 'REM-SKIP-1',
            'metadata' => [
                'reminders' => [
                    'count' => 1,
                    'last_sent_at' => now()->subHours(2)->toIso8601String(),
                ],
            ],
        ]);

        config()->set('payments.reminders.min_interval_hours', 24);

        $this->artisan('payments:send-outstanding-reminders')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    private function studentAndCourse(): array
    {
        $lecturer = User::factory()->create();
        $student = User::factory()->create();

        Role::findOrCreate('student', 'web');
        $student->assignRole('student');

        $course = Course::query()->create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Reminder Course '.uniqid(),
            'description' => 'Course for reminder schedule tests.',
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
