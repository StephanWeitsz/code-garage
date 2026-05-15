<?php

namespace Tests\Feature\Posts;

use App\Enums\CourseStatus;
use App\Enums\DifficultyLevel;
use App\Enums\LessonContentType;
use App\Models\User;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\Enrollment;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\CourseSection;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;
use CodeGarage\Posts\Infrastructure\Persistence\Eloquent\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PostsDiscussionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_permissions_can_create_post_and_reply(): void
    {
        $author = User::factory()->create();
        $responder = User::factory()->create();

        Permission::findOrCreate('posts.view', 'web');
        Permission::findOrCreate('posts.create', 'web');

        $author->givePermissionTo(['posts.view', 'posts.create']);
        $responder->givePermissionTo(['posts.view']);

        $course = Course::query()->create([
            'lecturer_id' => $author->id,
            'title' => 'Hardware Lab',
            'description' => 'Hands-on hardware course.',
            'difficulty_level' => DifficultyLevel::Beginner->value,
            'category' => 'Electronics',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => 'free',
            'pricing_currency' => 'ZAR',
        ]);

        $section = CourseSection::query()->create([
            'course_id' => $course->id,
            'title' => 'Week 1',
            'sequence' => 1,
        ]);

        $lesson = Lesson::query()->create([
            'course_id' => $course->id,
            'course_section_id' => $section->id,
            'title' => 'Soldering Basics',
            'slug' => 'soldering-basics',
            'content' => 'Safety and setup.',
            'content_type' => LessonContentType::Text->value,
            'sequence' => 1,
            'is_preview' => true,
        ]);

        $this->actingAs($author)->post('/posts', [
            'lesson_id' => $lesson->id,
            'title' => 'Week 2 Check-in',
            'body' => 'Share blockers and wins from this week.',
            'type' => 'discussion',
        ])->assertRedirect('/posts');

        $post = \CodeGarage\Posts\Infrastructure\Persistence\Eloquent\Models\Post::query()->first();
        $this->assertNotNull($post);

        $this->actingAs($responder)->post("/posts/{$post->id}/replies", [
            'body' => 'I completed the wiring challenge and posted notes.',
        ])->assertRedirect();

        $this->assertDatabaseHas('post_replies', [
            'post_id' => $post->id,
            'author_id' => $responder->id,
        ]);
    }

    public function test_student_can_create_absence_notice_for_enrolled_lesson_only(): void
    {
        $lecturer = User::factory()->create();
        $student = User::factory()->create();

        Permission::findOrCreate('posts.view', 'web');
        Permission::findOrCreate('posts.create-own', 'web');
        Permission::findOrCreate('posts.create', 'web');
        Role::findOrCreate('student', 'web');

        $student->assignRole('student');
        $student->givePermissionTo(['posts.view', 'posts.create-own']);

        $course = Course::query()->create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Robotics Lab',
            'description' => 'Robotics practicals.',
            'difficulty_level' => DifficultyLevel::Beginner->value,
            'category' => 'Robotics',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => 'free',
            'pricing_currency' => 'ZAR',
        ]);

        $section = CourseSection::query()->create([
            'course_id' => $course->id,
            'title' => 'Week 3',
            'sequence' => 1,
        ]);

        $lesson = Lesson::query()->create([
            'course_id' => $course->id,
            'course_section_id' => $section->id,
            'title' => 'Motor calibration',
            'slug' => 'motor-calibration',
            'content' => 'Calibration steps.',
            'content_type' => LessonContentType::Text->value,
            'sequence' => 1,
            'is_preview' => true,
        ]);

        Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        $this->actingAs($student)->post('/posts', [
            'lesson_id' => $lesson->id,
            'body' => 'I cannot attend next class due to a medical appointment.',
            'type' => 'absence_notice',
        ])->assertRedirect('/posts');

        $this->actingAs($student)->post('/posts', [
            'lesson_id' => $lesson->id,
            'title' => 'Question about motor setup',
            'body' => 'Can we use a lower voltage motor for this practical?',
            'type' => 'discussion',
        ])->assertRedirect('/posts');

        $this->assertDatabaseHas('posts', [
            'lesson_id' => $lesson->id,
            'type' => 'absence_notice',
            'author_id' => $student->id,
        ]);

        $this->assertDatabaseHas('posts', [
            'lesson_id' => $lesson->id,
            'type' => 'discussion',
            'author_id' => $student->id,
            'title' => 'Question about motor setup',
        ]);

        $this->actingAs($student)->post('/posts', [
            'lesson_id' => $lesson->id,
            'title' => 'Important',
            'body' => 'Trying to make an announcement.',
            'type' => 'announcement',
        ])->assertSessionHasErrors('type');

        $this->actingAs($student)
            ->get('/posts?type=absence_notice')
            ->assertOk()
            ->assertSeeText('Absence notice for Motor calibration');
    }

    public function test_privileged_user_can_create_global_announcement_but_discussion_requires_lesson(): void
    {
        $admin = User::factory()->create();

        Permission::findOrCreate('posts.view', 'web');
        Permission::findOrCreate('posts.create', 'web');

        $admin->givePermissionTo(['posts.view', 'posts.create']);

        $this->actingAs($admin)->post('/posts', [
            'title' => 'Platform maintenance',
            'body' => 'Site maintenance tonight at 21:00.',
            'type' => 'announcement',
        ])->assertRedirect('/posts');

        $this->assertDatabaseHas('posts', [
            'author_id' => $admin->id,
            'type' => 'announcement',
            'lesson_id' => null,
            'course_id' => null,
            'title' => 'Platform maintenance',
        ]);

        $this->actingAs($admin)->post('/posts', [
            'title' => 'General discussion',
            'body' => 'Let us discuss progress.',
            'type' => 'discussion',
        ])->assertSessionHasErrors('lesson_id');
    }

    public function test_admin_can_close_archive_and_reopen_discussion(): void
    {
        $admin = User::factory()->create();
        $responder = User::factory()->create();

        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');

        Permission::findOrCreate('posts.view', 'web');
        Permission::findOrCreate('posts.create', 'web');

        $admin->givePermissionTo(['posts.view', 'posts.create']);
        $responder->givePermissionTo(['posts.view']);

        $course = Course::query()->create([
            'lecturer_id' => $admin->id,
            'title' => 'Community Workshop',
            'description' => 'General workshop updates.',
            'difficulty_level' => DifficultyLevel::Beginner->value,
            'category' => 'General',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => 'free',
            'pricing_currency' => 'ZAR',
        ]);

        $section = CourseSection::query()->create([
            'course_id' => $course->id,
            'title' => 'Intro',
            'sequence' => 1,
        ]);

        $lesson = Lesson::query()->create([
            'course_id' => $course->id,
            'course_section_id' => $section->id,
            'title' => 'Welcome',
            'slug' => 'welcome',
            'content' => 'Welcome notes.',
            'content_type' => LessonContentType::Text->value,
            'sequence' => 1,
            'is_preview' => true,
        ]);

        $this->actingAs($admin)->post('/posts', [
            'lesson_id' => $lesson->id,
            'title' => 'Open discussion',
            'body' => 'Ask anything here.',
            'type' => 'discussion',
        ])->assertRedirect('/posts');

        $post = \CodeGarage\Posts\Infrastructure\Persistence\Eloquent\Models\Post::query()->firstOrFail();

        $this->actingAs($admin)
            ->post("/posts/{$post->id}/close")
            ->assertRedirect();

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'closed',
        ]);

        $this->actingAs($responder)
            ->post("/posts/{$post->id}/replies", [
                'body' => 'Can I still reply?',
            ])
            ->assertStatus(422);

        $this->actingAs($admin)
            ->post("/posts/{$post->id}/archive")
            ->assertRedirect('/posts');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'archived',
        ]);

        $this->actingAs($responder)
            ->get("/posts/{$post->id}")
            ->assertNotFound();

        $this->actingAs($admin)
            ->post("/posts/{$post->id}/reopen")
            ->assertRedirect();

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'published',
        ]);
    }

    public function test_only_active_ads_are_visible_publicly(): void
    {
        $author = User::factory()->create();

        $activeAd = Post::query()->create([
            'author_id' => $author->id,
            'title' => 'Holiday Robotics Workshop',
            'body' => 'Build and program a small robotics project during the school holiday.',
            'type' => 'ad',
            'status' => 'active',
            'cta_label' => 'Book a seat',
            'cta_url' => 'https://example.com/robotics',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
        ]);

        $inactiveAd = Post::query()->create([
            'author_id' => $author->id,
            'title' => 'Hidden Promotion',
            'body' => 'This should not be public.',
            'type' => 'ad',
            'status' => 'inactive',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSeeText('Holiday Robotics Workshop')
            ->assertDontSeeText('Hidden Promotion');

        $this->get("/ads/{$activeAd->id}")
            ->assertOk()
            ->assertSeeText('Holiday Robotics Workshop')
            ->assertSeeText('Book a seat');

        $this->get("/ads/{$inactiveAd->id}")
            ->assertNotFound();
    }

    public function test_privileged_user_can_create_visible_ad_with_image_from_posts_form(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();

        Permission::findOrCreate('posts.view', 'web');
        Permission::findOrCreate('posts.create', 'web');

        $admin->givePermissionTo(['posts.view', 'posts.create']);

        $this->actingAs($admin)->post('/posts', [
            'title' => 'Python Bootcamp Open Day',
            'body' => 'Join the open day and see what students build in the first month.',
            'type' => 'ad',
            'is_active' => '1',
            'starts_at' => now('Africa/Johannesburg')->format('Y-m-d\TH:i'),
            'ends_at' => now('Africa/Johannesburg')->addWeek()->format('Y-m-d\TH:i'),
            'cta_label' => 'Register',
            'cta_url' => 'https://example.com/register',
            'image' => UploadedFile::fake()->image('open-day.png', 900, 500),
        ])->assertRedirect('/posts');

        $ad = Post::query()->where('type', 'ad')->firstOrFail();

        $this->assertSame('active', $ad->status);
        $this->assertNotNull($ad->image_path);
        $this->assertTrue($ad->starts_at->lessThanOrEqualTo(now()));
        Storage::disk('public')->assertExists($ad->image_path);

        $this->get('/')
            ->assertOk()
            ->assertSeeText('Python Bootcamp Open Day');
    }
}
