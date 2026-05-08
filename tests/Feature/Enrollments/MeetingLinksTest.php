<?php

namespace Tests\Feature\Enrollments;

use App\Enums\CourseStatus;
use App\Enums\DifficultyLevel;
use App\Models\User;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\Enrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MeetingLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_sees_default_course_meeting_link_in_my_learning(): void
    {
        [$student, $course] = $this->makeCourseWithEnrollment();

        $this->actingAs($student)
            ->get('/my-learning')
            ->assertOk()
            ->assertSee('https://meet.google.com/default-course-room', false)
            ->assertSeeText('Join live session');
    }

    public function test_lecturer_can_set_student_specific_meeting_link_override(): void
    {
        [$student, $course, $lecturer, $enrollment] = $this->makeCourseWithEnrollment(includeEnrollment: true);

        $this->actingAs($lecturer)
            ->post('/enrollments/'.$enrollment->id.'/meeting-link', [
                'meeting_url' => 'https://teams.microsoft.com/l/per-student-room',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'meeting_url' => 'https://teams.microsoft.com/l/per-student-room',
        ]);

        $this->actingAs($student)
            ->get('/my-learning')
            ->assertOk()
            ->assertSee('https://teams.microsoft.com/l/per-student-room', false);
    }

    private function makeCourseWithEnrollment(bool $includeEnrollment = false): array
    {
        $lecturer = User::factory()->create();
        $student = User::factory()->create();

        Role::findOrCreate('lecturer', 'web');
        Role::findOrCreate('student', 'web');

        $lecturer->assignRole('lecturer');
        $student->assignRole('student');

        $course = Course::query()->create([
            'lecturer_id' => $lecturer->id,
            'title' => 'Live Session Course '.uniqid(),
            'description' => 'Course with online meeting links.',
            'difficulty_level' => DifficultyLevel::Beginner->value,
            'category' => 'Programming',
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
            'pricing_type' => 'free',
            'pricing_currency' => 'ZAR',
            'default_meeting_url' => 'https://meet.google.com/default-course-room',
        ]);

        $enrollment = Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        return $includeEnrollment
            ? [$student, $course, $lecturer, $enrollment]
            : [$student, $course];
    }
}
