<?php

namespace Tests\Feature\Assignments;

use App\Enums\CourseStatus;
use App\Enums\DifficultyLevel;
use App\Enums\LessonContentType;
use App\Models\User;
use CodeGarage\Assignments\Infrastructure\Persistence\Eloquent\Models\Assignment;
use CodeGarage\Assignments\Infrastructure\Persistence\Eloquent\Models\AssignmentSubmission;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\LessonCompletion;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\CourseSection;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AssignmentsFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_can_create_assignment_and_student_can_submit(): void
    {
        $lecturer = User::factory()->create();
        $student = User::factory()->create();

        Role::findOrCreate('lecturer', 'web');
        Role::findOrCreate('student', 'web');

        $lecturer->assignRole('lecturer');
        $student->assignRole('student');

        $course = Course::query()->create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Embedded Systems Fundamentals',
            'description' => 'Core embedded programming concepts.',
            'difficulty_level' => DifficultyLevel::Intermediate->value,
            'category' => 'Engineering',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => 'free',
            'pricing_currency' => 'ZAR',
        ]);

        $this->actingAs($lecturer)->post('/assignments', [
            'course_id' => $course->id,
            'title' => 'Sensor Calibration Lab',
            'instructions' => 'Submit your calibration report.',
            'max_points' => 100,
            'is_published' => true,
        ])->assertRedirect('/assignments');

        $assignment = Assignment::query()->first();
        $this->assertNotNull($assignment);

        $this->actingAs($student)->post("/assignments/{$assignment->id}/submit", [
            'content' => 'Attached report with test results.',
        ])->assertRedirect();

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'status' => 'submitted',
        ]);

        $submission = AssignmentSubmission::query()->first();
        $this->assertNotNull($submission);
    }

    public function test_student_assignments_page_hides_lesson_linked_assignment_until_lesson_completed(): void
    {
        $lecturer = User::factory()->create();
        $student = User::factory()->create();

        Role::findOrCreate('student', 'web');
        $student->assignRole('student');

        Permission::findOrCreate('assignments.view', 'web');
        Permission::findOrCreate('lessons.complete', 'web');
        $student->givePermissionTo(['assignments.view', 'lessons.complete']);

        $course = Course::query()->create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Programming Foundations',
            'description' => 'Core programming concepts.',
            'difficulty_level' => DifficultyLevel::Beginner->value,
            'category' => 'Programming',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => 'free',
            'pricing_currency' => 'ZAR',
        ]);

        $section = CourseSection::query()->create([
            'course_id' => $course->id,
            'title' => 'Basics',
            'sequence' => 1,
        ]);

        $lesson = Lesson::query()->create([
            'course_id' => $course->id,
            'course_section_id' => $section->id,
            'title' => 'Variables',
            'slug' => 'variables',
            'content' => 'Intro content.',
            'content_type' => LessonContentType::Text->value,
            'sequence' => 1,
            'is_preview' => true,
        ]);

        Assignment::query()->create([
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'author_id' => $lecturer->id,
            'title' => 'Variables worksheet',
            'instructions' => 'Solve variable exercises.',
            'is_published' => true,
            'max_points' => 100,
        ]);

        $this->actingAs($student)
            ->get('/assignments')
            ->assertOk()
            ->assertDontSeeText('Variables worksheet');

        LessonCompletion::query()->create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'completed_at' => now(),
        ]);

        $this->actingAs($student)
            ->get('/assignments')
            ->assertOk()
            ->assertSeeText('Variables worksheet');
    }
}
