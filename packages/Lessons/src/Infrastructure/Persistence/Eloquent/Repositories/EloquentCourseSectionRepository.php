<?php

namespace CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use CodeGarage\Lessons\Domain\Repositories\CourseSectionRepository;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\CourseSection;
use CodeGarage\Shared\Infrastructure\Persistence\EloquentRepository;

class EloquentCourseSectionRepository extends EloquentRepository implements CourseSectionRepository
{
    protected function modelClass(): string
    {
        return CourseSection::class;
    }

    public function forCourse(int $courseId): Collection
    {
        return $this->newQuery()
            ->where('course_id', $courseId)
            ->with('lessons')
            ->orderBy('sequence')
            ->get();
    }
}
