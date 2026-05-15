<?php

namespace CodeGarage\Posts\Application\Services;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use CodeGarage\Posts\Infrastructure\Persistence\Eloquent\Models\Post;
use CodeGarage\Posts\Infrastructure\Persistence\Eloquent\Models\PostReply;
use CodeGarage\Shared\Application\Services\ApplicationService;

class PostService extends ApplicationService
{
    public function __construct(Dispatcher $events)
    {
        parent::__construct($events);
    }

    public function feed(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $canManage = (bool) ($filters['can_manage'] ?? false);

        return Post::query()
            ->with(['author', 'course', 'lesson'])
            ->when(
                ! $canManage,
                fn ($query) => $query->whereIn('status', ['published', 'closed', 'active'])
            )
            ->when(
                filled($filters['type'] ?? null),
                fn ($query) => $query->where('type', (string) $filters['type'])
            )
            ->when(
                filled($filters['lesson_id'] ?? null),
                fn ($query) => $query->where('lesson_id', (int) $filters['lesson_id'])
            )
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function find(int $postId): ?Post
    {
        return Post::query()
            ->with(['author', 'course', 'lesson', 'replies.author'])
            ->find($postId);
    }

    public function forLesson(int $lessonId, ?int $viewerId = null, bool $canManage = false, int $limit = 6): Collection
    {
        return Post::query()
            ->with('author')
            ->where('status', 'published')
            ->where('lesson_id', $lessonId)
            ->when(
                ! $canManage && $viewerId !== null,
                fn ($query) => $query->where(function ($inner) use ($viewerId) {
                    $inner->where('author_id', $viewerId)
                        ->orWhere('type', 'announcement');
                })
            )
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function create(array $attributes): Post
    {
        return $this->transaction(fn () => Post::query()->create($attributes));
    }

    public function reply(int $postId, int $authorId, string $body): PostReply
    {
        return $this->transaction(fn () => PostReply::query()->create([
            'post_id' => $postId,
            'author_id' => $authorId,
            'body' => $body,
        ]));
    }

    public function updateStatus(Post $post, string $status): Post
    {
        $post->status = $status;
        $post->save();

        return $post->refresh();
    }
}
