<?php

namespace CodeGarage\Lessons\Presentation\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;
use CodeGarage\Shared\Authorization\BasePolicy;

class LessonPolicy extends BasePolicy
{
    public function viewAny(?User $user): Response
    {
        if ($user === null) {
            return Response::allow();
        }

        return $this->allowIf($user, 'lessons.view');
    }

    public function view(?User $user, Lesson $lesson): Response
    {
        if ($lesson->is_preview) {
            return Response::allow();
        }

        if ($user === null) {
            return Response::deny('Please sign in to access this lesson.');
        }

        return $this->allowIf($user, 'lessons.view');
    }

    public function create(User $user): Response
    {
        return $this->allowIf($user, 'lessons.create');
    }

    public function update(User $user): Response
    {
        return $this->allowIf($user, 'lessons.update');
    }

    public function delete(User $user): Response
    {
        return $this->allowIf($user, 'lessons.delete');
    }

    public function complete(User $user): Response
    {
        return $this->allowIf($user, 'lessons.complete');
    }
}
