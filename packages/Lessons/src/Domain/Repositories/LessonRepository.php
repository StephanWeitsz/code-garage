<?php

namespace CodeGarage\Lessons\Domain\Repositories;

use Illuminate\Support\Collection;
use CodeGarage\Shared\Domain\Repositories\Repository;

interface LessonRepository extends Repository
{
    public function forCourse(int $courseId): Collection;

    public function findByCourseAndSlug(int $courseId, string $slug);

    public function nextSequenceForSection(int $courseSectionId): int;
}
