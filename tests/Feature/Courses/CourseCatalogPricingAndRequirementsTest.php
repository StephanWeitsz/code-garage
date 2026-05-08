<?php

namespace Tests\Feature\Courses;

use App\Enums\CourseStatus;
use App\Enums\DifficultyLevel;
use App\Models\User;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseCatalogPricingAndRequirementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_only_shows_published_courses(): void
    {
        $lecturer = User::factory()->create();

        Course::create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Published Robotics',
            'description' => 'Public course',
            'difficulty_level' => DifficultyLevel::Beginner->value,
            'category' => 'Robotics',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => 'once_off',
            'pricing_amount' => 499.00,
            'pricing_currency' => 'ZAR',
        ]);

        Course::create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Build Robotics',
            'description' => 'Hidden while building',
            'difficulty_level' => DifficultyLevel::Beginner->value,
            'category' => 'Robotics',
            'status' => CourseStatus::Build->value,
            'pricing_type' => 'free',
            'pricing_currency' => 'ZAR',
        ]);

        Course::create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Draft Robotics',
            'description' => 'Hidden while planning',
            'difficulty_level' => DifficultyLevel::Beginner->value,
            'category' => 'Robotics',
            'status' => CourseStatus::Draft->value,
            'pricing_type' => 'free',
            'pricing_currency' => 'ZAR',
        ]);

        $response = $this->get('/courses');

        $response->assertOk();
        $response->assertSeeText('Published Robotics');
        $response->assertDontSeeText('Build Robotics');
        $response->assertDontSeeText('Draft Robotics');
    }

    public function test_course_show_displays_pricing_and_requirements(): void
    {
        $lecturer = User::factory()->create();

        $course = Course::create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Arduino Builders',
            'description' => 'Build and code real hardware.',
            'difficulty_level' => DifficultyLevel::Intermediate->value,
            'category' => 'Robotics',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'knowledge_prerequisites' => [
                'Basic computer literacy',
                'Intro to programming',
            ],
            'equipment_requirements' => [
                [
                    'name' => 'Arduino Starter Kit',
                    'url' => 'https://example.com/arduino-kit',
                    'notes' => 'Any compatible clone is also fine.',
                ],
            ],
            'pricing_type' => 'per_lesson',
            'pricing_amount' => 89.50,
            'pricing_currency' => 'ZAR',
        ]);

        $response = $this->get("/courses/{$course->slug}");

        $response->assertOk();
        $response->assertSeeText('ZAR 89.50');
        $response->assertSeeText('per lesson');
        $response->assertSeeText('Basic computer literacy');
        $response->assertSeeText('Arduino Starter Kit');
        $response->assertSee('https://example.com/arduino-kit', false);
    }

    public function test_course_show_displays_free_badge_for_free_courses(): void
    {
        $lecturer = User::factory()->create();

        $course = Course::create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Scratch Primer',
            'description' => 'Learn the core concepts with visual blocks.',
            'difficulty_level' => DifficultyLevel::Beginner->value,
            'category' => 'Programming Basics',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => 'free',
            'pricing_currency' => 'ZAR',
        ]);

        $response = $this->get("/courses/{$course->slug}");

        $response->assertOk();
        $response->assertSeeText('Free');
    }

    public function test_catalog_displays_cover_image_on_course_card(): void
    {
        $lecturer = User::factory()->create();

        Course::create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Robot Vision',
            'description' => 'Use camera input for navigation.',
            'cover_image' => 'https://example.com/robot-vision.jpg',
            'difficulty_level' => DifficultyLevel::Intermediate->value,
            'category' => 'Robotics',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => 'free',
            'pricing_currency' => 'ZAR',
        ]);

        $response = $this->get('/courses');

        $response->assertOk();
        $response->assertSee('https://example.com/robot-vision.jpg', false);
    }

    public function test_course_show_displays_equipment_without_purchase_link(): void
    {
        $lecturer = User::factory()->create();

        $course = Course::create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Build Smart Robots',
            'description' => 'Hardware and coding fundamentals.',
            'difficulty_level' => DifficultyLevel::Intermediate->value,
            'category' => 'Robotics',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'equipment_requirements' => [
                [
                    'name' => 'Laptop',
                    'notes' => 'Minimum Intel i5 and 8GB RAM.',
                ],
                [
                    'name' => 'Arduino starter kit',
                    'url' => 'https://example.com/arduino',
                ],
            ],
            'pricing_type' => 'free',
            'pricing_currency' => 'ZAR',
        ]);

        $response = $this->get("/courses/{$course->slug}");

        $response->assertOk();
        $response->assertSeeText('Laptop');
        $response->assertSeeText('Minimum Intel i5 and 8GB RAM.');
        $response->assertSeeText('Arduino starter kit');
    }


    public function test_guest_can_log_a_course_query_before_registering(): void
    {
        $lecturer = User::factory()->create();

        $course = Course::create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Python Starter',
            'description' => 'Learn Python from scratch.',
            'difficulty_level' => DifficultyLevel::Beginner->value,
            'category' => 'Programming Basics',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => 'free',
            'pricing_currency' => 'ZAR',
        ]);

        $response = $this->post("/courses/{$course->id}/queries", [
            'name' => 'Prospective Learner',
            'email' => 'prospect@example.com',
            'mobile' => '0820000000',
            'subject' => 'Course schedule',
            'message' => 'Please send me more information about when this course starts.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Your course query has been logged. We will get back to you soon.');

        $this->assertDatabaseHas('course_queries', [
            'course_id' => $course->id,
            'user_id' => null,
            'name' => 'Prospective Learner',
            'email' => 'prospect@example.com',
            'audience' => 'prospective_student',
            'status' => 'open',
        ]);
    }

    public function test_registered_student_can_log_a_course_query_from_their_account(): void
    {
        $lecturer = User::factory()->create();
        $student = User::factory()->create([
            'name' => 'Registered Student',
            'email' => 'student@example.com',
        ]);

        $course = Course::create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Robotics Lab',
            'description' => 'Build and code robots.',
            'difficulty_level' => DifficultyLevel::Intermediate->value,
            'category' => 'Robotics',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => 'free',
            'pricing_currency' => 'ZAR',
        ]);

        $response = $this->actingAs($student)->post("/courses/{$course->id}/queries", [
            'subject' => 'Extra details',
            'message' => 'I am registered and want to understand what hardware I should prepare.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('course_queries', [
            'course_id' => $course->id,
            'user_id' => $student->id,
            'name' => 'Registered Student',
            'email' => 'student@example.com',
            'audience' => 'registered_student',
            'status' => 'open',
        ]);
    }
}
