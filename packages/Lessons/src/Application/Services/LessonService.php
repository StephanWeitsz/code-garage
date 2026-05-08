<?php

namespace CodeGarage\Lessons\Application\Services;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use CodeGarage\Lessons\Domain\Repositories\LessonRepository;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;
use CodeGarage\Shared\Application\Services\ApplicationService;

class LessonService extends ApplicationService
{
    public function __construct(
        protected LessonRepository $lessons,
        Dispatcher $events,
    ) {
        parent::__construct($events);
    }

    public function forCourse(int $courseId): Collection
    {
        return $this->lessons->forCourse($courseId);
    }

    public function findByCourseAndSlug(int $courseId, string $slug): ?Lesson
    {
        return $this->lessons->findByCourseAndSlug($courseId, $slug);
    }

    public function create(array $attributes): Lesson
    {
        return $this->transaction(function () use ($attributes) {
            return $this->lessons->create([
                ...$attributes,
                'sequence' => $attributes['sequence'] ?? $this->lessons->nextSequenceForSection((int) $attributes['course_section_id']),
                'slug' => Str::slug($attributes['title']),
            ]);
        });
    }

    public function update(Lesson $lesson, array $attributes): Lesson
    {
        return $this->transaction(function () use ($lesson, $attributes) {
            if (isset($attributes['title'])) {
                $attributes['slug'] = Str::slug($attributes['title']);
            }

            return $this->lessons->update($lesson, $attributes);
        });
    }
}
