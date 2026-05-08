<?php

namespace Tests\Feature\Lessons;

use App\Enums\CourseStatus;
use App\Enums\DifficultyLevel;
use App\Enums\LessonContentType;
use App\Models\User;
use CodeGarage\Assignments\Infrastructure\Persistence\Eloquent\Models\Assignment;
use CodeGarage\Assignments\Infrastructure\Persistence\Eloquent\Models\AssignmentSubmission;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\Enrollment;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\CourseSection;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LessonAssignmentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_lesson_completion_assigns_relative_due_date_and_shows_assignment_on_lesson_page(): void
    {
        $lecturer = User::factory()->create();
        $student = User::factory()->create();

        Role::findOrCreate('student', 'web');
        $student->assignRole('student');

        Permission::findOrCreate('lessons.complete', 'web');
        Permission::findOrCreate('posts.view', 'web');
        Permission::findOrCreate('posts.create-own', 'web');
        $student->givePermissionTo(['lessons.complete', 'posts.view', 'posts.create-own']);

        $course = Course::query()->create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Control Systems',
            'description' => 'Learn control loops and feedback.',
            'difficulty_level' => DifficultyLevel::Intermediate->value,
            'category' => 'Engineering',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => 'free',
            'pricing_currency' => 'ZAR',
        ]);

        $section = CourseSection::query()->create([
            'course_id' => $course->id,
            'title' => 'Foundations',
            'sequence' => 1,
        ]);

        $lesson = Lesson::query()->create([
            'course_id' => $course->id,
            'course_section_id' => $section->id,
            'title' => 'PID Basics',
            'slug' => 'pid-basics',
            'content' => 'Concepts and examples.',
            'content_type' => LessonContentType::Text->value,
            'sequence' => 1,
            'is_preview' => true,
        ]);

        $nextLesson = Lesson::query()->create([
            'course_id' => $course->id,
            'course_section_id' => $section->id,
            'title' => 'PID Advanced',
            'slug' => 'pid-advanced',
            'content' => 'Advanced concepts.',
            'content_type' => LessonContentType::Text->value,
            'sequence' => 3,
            'is_preview' => true,
        ]);

        $secondSection = CourseSection::query()->create([
            'course_id' => $course->id,
            'title' => 'Applied Control',
            'sequence' => 2,
        ]);

        Lesson::query()->create([
            'course_id' => $course->id,
            'course_section_id' => $secondSection->id,
            'title' => 'Section 2 Intro',
            'slug' => 'section-2-intro',
            'content' => 'Section 2 material.',
            'content_type' => LessonContentType::Text->value,
            'sequence' => 2,
            'is_preview' => true,
        ]);

        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'author_id' => $lecturer->id,
            'title' => 'Tune a PID controller',
            'instructions' => 'Submit your tuning report.',
            'due_days_after_completion' => 5,
            'max_points' => 100,
            'is_published' => true,
        ]);

        Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        $this->actingAs($student)
            ->get("/courses/{$course->slug}/lessons/{$lesson->slug}")
            ->assertOk()
            ->assertSeeText('Tune a PID controller')
            ->assertSeeText('Mark lesson complete');

        $this->actingAs($student)
            ->post("/lessons/{$lesson->id}/complete")
            ->assertRedirect("/assignments/{$assignment->id}");

        $submission = AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        $this->assertNotNull($submission);
        $this->assertSame('assigned', $submission->status);
        $this->assertNotNull($submission->due_at);

        $this->actingAs($student)
            ->post("/assignments/{$assignment->id}/submit", [
                'content' => 'PID tuning report attached.',
            ])
            ->assertRedirect();

        $this->actingAs($student)
            ->post("/lessons/{$lesson->id}/complete")
            ->assertRedirect("/courses/{$course->slug}/lessons/{$nextLesson->slug}");
    }
}
