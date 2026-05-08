<?php

namespace CodeGarage\Enrollments\Presentation\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\Enrollment;
use CodeGarage\Shared\Authorization\BasePolicy;

class EnrollmentPolicy extends BasePolicy
{
    public function viewAny(User $user): Response
    {
        if ($user->hasRole('student')) {
            return Response::allow();
        }

        return $this->allowIf($user, 'enrollments.view');
    }

    public function view(User $user, Enrollment $enrollment): Response
    {
        if ($enrollment->user_id === $user->id) {
            return Response::allow();
        }

        return $this->allowIf($user, 'enrollments.view');
    }

    public function create(User $user): Response
    {
        return $this->allowIf($user, 'enrollments.create');
    }
}
