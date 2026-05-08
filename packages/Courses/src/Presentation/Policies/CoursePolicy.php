<?php

namespace CodeGarage\Courses\Presentation\Policies;

use App\Enums\CourseStatus;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Shared\Authorization\BasePolicy;

class CoursePolicy extends BasePolicy
{
    public function viewAny(?User $user): Response
    {
        if ($user === null) {
            return Response::allow();
        }

        return $this->allowIf($user, 'courses.view');
    }

    public function view(?User $user, Course $course): Response
    {
        if (in_array($course->status->value, CourseStatus::publicStatuses(), true)) {
            return Response::allow();
        }

        if ($user === null) {
            return Response::deny('This course is not available yet.');
        }

        return $this->allowIf($user, 'courses.view');
    }

    public function create(User $user): Response
    {
        return $this->allowIf($user, 'courses.create');
    }

    public function update(User $user, Course $course): Response
    {
        if ($user->can('courses.update') || $course->lecturer_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('You may only update your own courses.');
    }

    public function delete(User $user, Course $course): Response
    {
        if ($user->can('courses.delete') || $course->lecturer_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('You may only delete your own courses.');
    }

    public function publish(User $user): Response
    {
        return $this->allowIf($user, 'courses.publish');
    }
}
