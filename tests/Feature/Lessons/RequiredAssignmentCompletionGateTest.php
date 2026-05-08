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

class RequiredAssignmentCompletionGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_assignment_blocks_lesson_completion_until_submitted(): void
    {
        $lecturer = User::factory()->create();
        $student = User::factory()->create();

        Role::findOrCreate('student', 'web');
        $student->assignRole('student');

        Permission::findOrCreate('lessons.complete', 'web');
        Permission::findOrCreate('assignments.view', 'web');
        Permission::findOrCreate('assignments.submit', 'web');
        $student->givePermissionTo(['lessons.complete', 'assignments.view', 'assignments.submit']);

        $course = Course::query()->create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Basic Programming',
            'description' => 'Programming essentials.',
            'difficulty_level' => DifficultyLevel::Beginner->value,
            'category' => 'Programming',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => 'free',
            'pricing_currency' => 'ZAR',
        ]);

        $section = CourseSection::query()->create([
            'course_id' => $course->id,
            'title' => 'Section 1',
            'sequence' => 1,
        ]);

        $lesson = Lesson::query()->create([
            'course_id' => $course->id,
            'course_section_id' => $section->id,
            'title' => 'Loops',
            'slug' => 'loops',
            'content' => 'Loop basics.',
            'content_type' => LessonContentType::Text->value,
            'sequence' => 1,
            'is_preview' => true,
        ]);

        $nextLesson = Lesson::query()->create([
            'course_id' => $course->id,
            'course_section_id' => $section->id,
            'title' => 'Functions',
            'slug' => 'functions',
            'content' => 'Function basics.',
            'content_type' => LessonContentType::Text->value,
            'sequence' => 2,
            'is_preview' => true,
        ]);

        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'author_id' => $lecturer->id,
            'title' => 'Loop worksheet',
            'instructions' => 'Submit loop exercises.',
            'requires_completion_before_lesson_complete' => true,
            'is_published' => true,
            'max_points' => 100,
        ]);

        Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        $this->actingAs($student)
            ->post("/lessons/{$lesson->id}/complete")
            ->assertRedirect();

        $this->assertDatabaseMissing('lesson_completions', [
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
        ]);

        AssignmentSubmission::query()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->actingAs($student)
            ->post("/lessons/{$lesson->id}/complete")
            ->assertRedirect("/courses/{$course->slug}/lessons/{$nextLesson->slug}");
    }
}
