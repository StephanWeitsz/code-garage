<?php

namespace CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Repositories;

use App\Enums\CourseStatus;
use Illuminate\Support\Collection;
use CodeGarage\Courses\Domain\Repositories\CourseRepository;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Shared\Infrastructure\Persistence\EloquentRepository;

class EloquentCourseRepository extends EloquentRepository implements CourseRepository
{
    protected function modelClass(): string
    {
        return Course::class;
    }

    public function findBySlug(string $slug): ?Course
    {
        return $this->newQuery()->where('slug', $slug)->first();
    }

    public function published(int $limit = 12): Collection
    {
        return $this->newQuery()
            ->whereIn('status', CourseStatus::publicStatuses())
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }
}
