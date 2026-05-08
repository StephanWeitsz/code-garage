<?php

namespace CodeGarage\Courses\Domain\Repositories;

use Illuminate\Support\Collection;
use CodeGarage\Shared\Domain\Repositories\Repository;

interface CourseRepository extends Repository
{
    public function findBySlug(string $slug);

    public function published(int $limit = 12): Collection;
}
