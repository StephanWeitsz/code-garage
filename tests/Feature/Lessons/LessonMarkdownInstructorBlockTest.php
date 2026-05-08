<?php

namespace Tests\Feature\Lessons;

use App\Enums\CourseStatus;
use App\Enums\DifficultyLevel;
use App\Enums\LessonContentType;
use App\Models\User;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\Enrollment;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\CourseSection;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LessonMarkdownInstructorBlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_markdown_block_is_hidden_for_student_and_visible_for_lecturer(): void
    {
        $lecturer = User::factory()->create();
        $student = User::factory()->create();

        Role::findOrCreate('lecturer', 'web');
        Role::findOrCreate('student', 'web');
        $lecturer->assignRole('lecturer');
        $student->assignRole('student');

        $course = Course::query()->create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Programming Essentials',
            'description' => 'Learn core programming fundamentals.',
            'difficulty_level' => DifficultyLevel::Beginner->value,
            'category' => 'Programming',
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
            'title' => 'Variables',
            'slug' => 'variables',
            'content' => "Public intro text.\n\n:::instructor\nLecturer-only hint.\n:::\n\nPublic wrap-up text.",
            'content_type' => LessonContentType::Markdown->value,
            'sequence' => 1,
            'is_preview' => false,
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
            ->assertSeeText('Public intro text.')
            ->assertSeeText('Public wrap-up text.')
            ->assertDontSeeText('Lecturer-only hint.');

        $this->actingAs($lecturer)
            ->get("/courses/{$course->slug}/lessons/{$lesson->slug}")
            ->assertOk()
            ->assertSeeText('Public intro text.')
            ->assertSeeText('Public wrap-up text.')
            ->assertSeeText('Lecturer-only hint.');
    }
}
