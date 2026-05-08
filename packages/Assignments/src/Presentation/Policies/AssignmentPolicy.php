<?php

namespace CodeGarage\Assignments\Presentation\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use CodeGarage\Assignments\Infrastructure\Persistence\Eloquent\Models\Assignment;
use CodeGarage\Shared\Authorization\BasePolicy;

class AssignmentPolicy extends BasePolicy
{
    public function viewAny(User $user): Response
    {
        return $this->allowIf($user, 'assignments.view');
    }

    public function view(User $user, Assignment $assignment): Response
    {
        if ($user->can('assignments.view')) {
            return Response::allow();
        }

        return Response::deny('You are not authorized to view this assignment.');
    }

    public function create(User $user): Response
    {
        return $this->allowIf($user, 'assignments.create');
    }

    public function submit(User $user): Response
    {
        return $this->allowIf($user, 'assignments.submit');
    }

    public function grade(User $user): Response
    {
        return $this->allowIf($user, 'assignments.grade');
    }

    public function update(User $user, Assignment $assignment): Response
    {
        return $this->allowIf($user, 'assignments.update');
    }

    public function delete(User $user, Assignment $assignment): Response
    {
        return $this->allowIf($user, 'assignments.delete');
    }
}
