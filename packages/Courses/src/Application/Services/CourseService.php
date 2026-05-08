<?php

namespace CodeGarage\Courses\Application\Services;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use CodeGarage\Courses\Domain\Repositories\CourseRepository;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Shared\Application\Services\ApplicationService;

class CourseService extends ApplicationService
{
    public function __construct(
        protected CourseRepository $courses,
        Dispatcher $events,
    ) {
        parent::__construct($events);
    }

    public function published(int $limit = 12): Collection
    {
        return $this->courses->published($limit);
    }

    public function findBySlug(string $slug): ?Course
    {
        return $this->courses->findBySlug($slug);
    }

    public function allForManagement(): Collection
    {
        return Course::query()
            ->orderBy('title')
            ->get();
    }

    public function create(array $attributes): Course
    {
        return $this->transaction(function () use ($attributes) {
            return $this->courses->create([
                ...$attributes,
                'slug' => Str::slug($attributes['title']),
            ]);
        });
    }

    public function update(Course $course, array $attributes): Course
    {
        return $this->transaction(function () use ($course, $attributes) {
            if (isset($attributes['title'])) {
                $attributes['slug'] = Str::slug($attributes['title']);
            }

            return $this->courses->update($course, $attributes);
        });
    }
}
