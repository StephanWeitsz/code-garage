<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Courses\Presentation\Policies\CoursePolicy;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\Enrollment;
use CodeGarage\Enrollments\Presentation\Policies\EnrollmentPolicy;
use CodeGarage\Assignments\Infrastructure\Persistence\Eloquent\Models\Assignment;
use CodeGarage\Assignments\Presentation\Policies\AssignmentPolicy;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;
use CodeGarage\Lessons\Presentation\Policies\LessonPolicy;
use CodeGarage\Posts\Infrastructure\Persistence\Eloquent\Models\Post;
use CodeGarage\Posts\Presentation\Policies\PostPolicy;
use CodeGarage\Payments\Infrastructure\Persistence\Eloquent\Models\Payment;
use CodeGarage\Payments\Presentation\Policies\PaymentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Course::class => CoursePolicy::class,
        Lesson::class => LessonPolicy::class,
        Enrollment::class => EnrollmentPolicy::class,
        Assignment::class => AssignmentPolicy::class,
        Post::class => PostPolicy::class,
        Payment::class => PaymentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function (User $user) {
            return $user->hasRole(config('permissions.super_admin_role', 'admin')) ? true : null;
        });
    }
}
