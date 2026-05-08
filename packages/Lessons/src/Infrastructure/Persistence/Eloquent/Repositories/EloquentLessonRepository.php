<?php

namespace CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use CodeGarage\Lessons\Domain\Repositories\LessonRepository;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;
use CodeGarage\Shared\Infrastructure\Persistence\EloquentRepository;

class EloquentLessonRepository extends EloquentRepository implements LessonRepository
{
    protected function modelClass(): string
    {
        return Lesson::class;
    }

    public function forCourse(int $courseId): Collection
    {
        return $this->newQuery()
            ->where('course_id', $courseId)
            ->with('section')
            ->orderBy('course_section_id')
            ->orderBy('sequence')
            ->get();
    }

    public function findByCourseAndSlug(int $courseId, string $slug): ?Lesson
    {
        return $this->newQuery()
            ->where('course_id', $courseId)
            ->where('slug', $slug)
            ->first();
    }

    public function nextSequenceForSection(int $courseSectionId): int
    {
        return ((int) $this->newQuery()
            ->where('course_section_id', $courseSectionId)
            ->max('sequence')) + 1;
    }
}
