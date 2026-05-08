<?php

namespace CodeGarage\Shared\Authorization;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

abstract class BasePolicy
{
    use HandlesAuthorization;

    protected function allowIf(User $user, string $permission): Response
    {
        return $user->can($permission)
            ? Response::allow()
            : Response::deny('You are not authorized to perform this action.');
    }
}
