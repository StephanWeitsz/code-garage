<?php

namespace CodeGarage\Enrollments\Domain\Repositories;

use Illuminate\Support\Collection;
use CodeGarage\Shared\Domain\Repositories\Repository;

interface EnrollmentRepository extends Repository
{
    public function findByUserAndCourse(int $userId, int $courseId);

    public function forUser(int $userId): Collection;

    public function completionCountForCourse(int $userId, array $lessonIds): int;
}
