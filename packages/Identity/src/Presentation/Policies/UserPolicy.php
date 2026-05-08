<?php

namespace CodeGarage\Identity\Presentation\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use CodeGarage\Shared\Authorization\BasePolicy;

class UserPolicy extends BasePolicy
{
    public function viewAny(User $user): Response
    {
        return $this->allowIf($user, 'identity.users.view');
    }

    public function view(User $user): Response
    {
        return $this->allowIf($user, 'identity.users.view');
    }

    public function create(User $user): Response
    {
        return $this->allowIf($user, 'identity.users.create');
    }

    public function update(User $user): Response
    {
        return $this->allowIf($user, 'identity.users.update');
    }

    public function delete(User $user): Response
    {
        return $this->allowIf($user, 'identity.users.delete');
    }

    public function assignRoles(User $user): Response
    {
        return $this->allowIf($user, 'identity.roles.assign');
    }
}
