<?php

namespace CodeGarage\Lessons\Domain\Repositories;

use Illuminate\Support\Collection;
use CodeGarage\Shared\Domain\Repositories\Repository;

interface CourseSectionRepository extends Repository
{
    public function forCourse(int $courseId): Collection;
}
