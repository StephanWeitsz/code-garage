<?php

namespace Tests\Feature\Lessons;

use App\Enums\CourseStatus;
use App\Enums\DifficultyLevel;
use App\Enums\LessonContentType;
use App\Models\User;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\CourseSection;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseSectionsAndLessonsStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_sections_and_lessons_are_returned_in_sequence_order(): void
    {
        $lecturer = User::factory()->create();

        $course = Course::create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Robot Logic',
            'description' => 'Build navigation and control flow.',
            'difficulty_level' => DifficultyLevel::Intermediate->value,
            'category' => 'Robotics',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => 'once_off',
            'pricing_amount' => 799.00,
            'pricing_currency' => 'ZAR',
        ]);

        CourseSection::create([
            'course_id' => $course->id,
            'title' => 'Movement Control',
            'description' => 'Motors and wheel patterns.',
            'sequence' => 2,
        ]);

        $firstSection = CourseSection::create([
            'course_id' => $course->id,
            'title' => 'Sensors',
            'description' => 'Signal handling.',
            'sequence' => 1,
        ]);

        Lesson::create([
            'course_id' => $course->id,
            'course_section_id' => $firstSection->id,
            'title' => 'Infrared Intro',
            'slug' => 'infrared-intro',
            'content' => 'Reading infrared values.',
            'content_type' => LessonContentType::Text->value,
            'sequence' => 2,
        ]);

        Lesson::create([
            'course_id' => $course->id,
            'course_section_id' => $firstSection->id,
            'title' => 'Ultrasonic Basics',
            'slug' => 'ultrasonic-basics',
            'content' => 'Distance measurement.',
            'content_type' => LessonContentType::Text->value,
            'sequence' => 1,
            'is_preview' => true,
        ]);

        $orderedSections = $course->fresh()->sections;
        $orderedLessons = $firstSection->fresh()->lessons;

        $this->assertSame(['Sensors', 'Movement Control'], $orderedSections->pluck('title')->all());
        $this->assertSame(['Ultrasonic Basics', 'Infrared Intro'], $orderedLessons->pluck('title')->all());
        $this->assertTrue((bool) $orderedLessons->first()->is_preview);
    }

    public function test_course_delete_cascades_to_sections_and_lessons(): void
    {
        $lecturer = User::factory()->create();

        $course = Course::create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Microcontrollers 101',
            'description' => 'Control devices safely.',
            'difficulty_level' => DifficultyLevel::Beginner->value,
            'category' => 'Electronics',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => 'hourly',
            'pricing_amount' => 120.00,
            'pricing_currency' => 'ZAR',
        ]);

        $section = CourseSection::create([
            'course_id' => $course->id,
            'title' => 'Bootstrapping',
            'description' => 'Board setup.',
            'sequence' => 1,
        ]);

        $lesson = Lesson::create([
            'course_id' => $course->id,
            'course_section_id' => $section->id,
            'title' => 'Install IDE',
            'slug' => 'install-ide',
            'content' => 'Download and verify tools.',
            'content_type' => LessonContentType::Text->value,
            'sequence' => 1,
        ]);

        $course->delete();

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
        $this->assertDatabaseMissing('course_sections', ['id' => $section->id]);
        $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);
    }
}
