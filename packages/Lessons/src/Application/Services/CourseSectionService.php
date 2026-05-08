<?php

namespace CodeGarage\Lessons\Application\Services;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use CodeGarage\Lessons\Domain\Repositories\CourseSectionRepository;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\CourseSection;
use CodeGarage\Shared\Application\Services\ApplicationService;

class CourseSectionService extends ApplicationService
{
    public function __construct(
        protected CourseSectionRepository $sections,
        Dispatcher $events,
    ) {
        parent::__construct($events);
    }

    public function forCourse(int $courseId): Collection
    {
        return $this->sections->forCourse($courseId);
    }

    public function create(array $attributes): CourseSection
    {
        return $this->transaction(function () use ($attributes) {
            return $this->sections->create([
                ...$attributes,
                'slug' => Str::slug($attributes['title']),
            ]);
        });
    }

    public function update(CourseSection $section, array $attributes): CourseSection
    {
        return $this->transaction(function () use ($section, $attributes) {
            if (isset($attributes['title'])) {
                $attributes['slug'] = Str::slug($attributes['title']);
            }

            return $this->sections->update($section, $attributes);
        });
    }
}
