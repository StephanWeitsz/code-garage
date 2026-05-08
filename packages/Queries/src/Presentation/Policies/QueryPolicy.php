<?php

namespace CodeGarage\Queries\Presentation\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use CodeGarage\Shared\Authorization\BasePolicy;

class QueryPolicy extends BasePolicy
{
    public function viewAny(User $user): Response
    {
        return $this->allowIf($user, 'queries.view');
    }

    public function view(User $user): Response
    {
        return $this->allowIf($user, 'queries.view');
    }

    public function viewOwn(User $user): Response
    {
        return $this->allowIf($user, 'queries.view-own');
    }

    public function create(User $user): Response
    {
        return $this->allowIf($user, 'queries.create');
    }

    public function reply(User $user): Response
    {
        return $this->allowIf($user, 'queries.reply');
    }

    public function replyOwn(User $user): Response
    {
        return $this->allowIf($user, 'queries.reply-own');
    }

    public function assign(User $user): Response
    {
        return $this->allowIf($user, 'queries.assign');
    }

    public function resolve(User $user): Response
    {
        return $this->allowIf($user, 'queries.resolve');
    }

    public function escalate(User $user): Response
    {
        return $this->allowIf($user, 'queries.escalate');
    }
}
