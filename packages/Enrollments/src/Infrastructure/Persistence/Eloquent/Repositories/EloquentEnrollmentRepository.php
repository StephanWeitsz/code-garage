<?php

namespace CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use CodeGarage\Enrollments\Domain\Repositories\EnrollmentRepository;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\Enrollment;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\LessonCompletion;
use CodeGarage\Shared\Infrastructure\Persistence\EloquentRepository;

class EloquentEnrollmentRepository extends EloquentRepository implements EnrollmentRepository
{
    protected function modelClass(): string
    {
        return Enrollment::class;
    }

    public function findByUserAndCourse(int $userId, int $courseId): ?Enrollment
    {
        return $this->newQuery()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();
    }

    public function forUser(int $userId): Collection
    {
        return $this->newQuery()
            ->with('course')
            ->where('user_id', $userId)
            ->orderByDesc('enrolled_at')
            ->get();
    }

    public function completionCountForCourse(int $userId, array $lessonIds): int
    {
        if ($lessonIds === []) {
            return 0;
        }

        return LessonCompletion::query()
            ->where('user_id', $userId)
            ->whereIn('lesson_id', $lessonIds)
            ->count();
    }
}
