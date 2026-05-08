<?php

namespace Tests\Feature\Progress;

use App\Enums\CourseStatus;
use App\Enums\DifficultyLevel;
use App\Enums\EnrollmentStatus;
use App\Enums\LessonContentType;
use App\Models\User;
use CodeGarage\Assignments\Infrastructure\Persistence\Eloquent\Models\Assignment;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\Enrollment;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\LessonCompletion;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\CourseSection;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProgressTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_progress_tracking_page(): void
    {
        $lecturer = User::factory()->create();
        $student = User::factory()->create();

        Role::findOrCreate('student', 'web');
        $student->assignRole('student');

        $course = Course::query()->create([
            'lecturer_id' => $lecturer->id,
            'title' => 'IoT Automation Basics',
            'description' => 'Build basic automation routines.',
            'difficulty_level' => DifficultyLevel::Beginner->value,
            'category' => 'IoT',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => 'free',
            'pricing_currency' => 'ZAR',
        ]);

        $section = CourseSection::query()->create([
            'course_id' => $course->id,
            'title' => 'Getting started',
            'sequence' => 1,
        ]);

        $lesson = Lesson::query()->create([
            'course_id' => $course->id,
            'course_section_id' => $section->id,
            'title' => 'Toolchain setup',
            'slug' => 'toolchain-setup',
            'content' => 'Install and verify tools.',
            'content_type' => LessonContentType::Text->value,
            'sequence' => 1,
            'is_preview' => true,
        ]);

        Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => EnrollmentStatus::Active->value,
            'enrolled_at' => now(),
        ]);

        LessonCompletion::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'completed_at' => now(),
        ]);

        Assignment::query()->create([
            'course_id' => $course->id,
            'author_id' => $lecturer->id,
            'title' => 'Mini project',
            'instructions' => 'Build and submit a simple automation scenario.',
            'max_points' => 100,
            'is_published' => true,
        ]);

        $response = $this->actingAs($student)->get('/progress');

        $response->assertOk();
        $response->assertSeeText('Progress Tracking');
        $response->assertSeeText('IoT Automation Basics');
    }
}
