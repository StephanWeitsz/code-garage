<?php

namespace CodeGarage\Posts\Presentation\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use CodeGarage\Posts\Infrastructure\Persistence\Eloquent\Models\Post;
use CodeGarage\Shared\Authorization\BasePolicy;

class PostPolicy extends BasePolicy
{
    public function viewAny(User $user): Response
    {
        return $this->allowIf($user, 'posts.view');
    }

    public function view(User $user, Post $post): Response
    {
        return $this->allowIf($user, 'posts.view');
    }

    public function create(User $user): Response
    {
        if ($user->can('posts.create') || $user->can('posts.create-own')) {
            return Response::allow();
        }

        return Response::deny('You are not authorized to perform this action.');
    }

    public function update(User $user, Post $post): Response
    {
        return $this->allowIf($user, 'posts.update');
    }

    public function delete(User $user, Post $post): Response
    {
        return $this->allowIf($user, 'posts.delete');
    }

    public function publish(User $user): Response
    {
        return $this->allowIf($user, 'posts.publish');
    }
}
