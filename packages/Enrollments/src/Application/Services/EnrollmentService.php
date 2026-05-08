<?php

namespace CodeGarage\Enrollments\Application\Services;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;
use CodeGarage\Assignments\Application\Services\AssignmentService;
use CodeGarage\Enrollments\Domain\Repositories\EnrollmentRepository;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\Enrollment;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\LessonCompletion;
use CodeGarage\Lessons\Application\Services\LessonService;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;
use CodeGarage\Shared\Application\Services\ApplicationService;

class EnrollmentService extends ApplicationService
{
    public function __construct(
        protected EnrollmentRepository $enrollments,
        protected LessonService $lessons,
        protected AssignmentService $assignments,
        Dispatcher $events,
    ) {
        parent::__construct($events);
    }

    public function enroll(int $userId, int $courseId): Enrollment
    {
        $existing = $this->enrollments->findByUserAndCourse($userId, $courseId);

        if ($existing !== null) {
            return $existing;
        }

        return $this->transaction(function () use ($userId, $courseId) {
            return $this->enrollments->create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'status' => 'active',
                'enrolled_at' => now(),
            ]);
        });
    }

    public function isEnrolled(int $userId, int $courseId): bool
    {
        return $this->enrollments->findByUserAndCourse($userId, $courseId) !== null;
    }

    public function enrollmentForUserAndCourse(int $userId, int $courseId): ?Enrollment
    {
        return $this->enrollments->findByUserAndCourse($userId, $courseId);
    }

    public function forUser(int $userId): Collection
    {
        return $this->enrollments->forUser($userId);
    }

    public function markLessonCompleted(int $userId, int $lessonId): LessonCompletion
    {
        $completion = LessonCompletion::query()->firstOrCreate(
            ['user_id' => $userId, 'lesson_id' => $lessonId],
            ['completed_at' => now()],
        );

        $this->assignments->assignForLessonCompletion(
            $userId,
            $lessonId,
            $completion->completed_at ?? now(),
        );

        return $completion;
    }

    public function isLessonCompleted(int $userId, int $lessonId): bool
    {
        return LessonCompletion::query()
            ->where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->exists();
    }

    public function completionForCourse(int $userId, int $courseId): int
    {
        $lessons = $this->lessons->forCourse($courseId);
        $lessonIds = $lessons->pluck('id')->all();
        $total = count($lessonIds);

        if ($total === 0) {
            return 0;
        }

        $completed = $this->enrollments->completionCountForCourse($userId, $lessonIds);

        return (int) round(($completed / $total) * 100);
    }

    public function progressForUser(int $userId): array
    {
        $enrollments = $this->forUser($userId);
        $courseIds = $enrollments->pluck('course_id')->all();

        if ($courseIds === []) {
            return [
                'overview' => [
                    'courses' => 0,
                    'lesson_completion' => 0,
                    'assignments_submitted' => 0,
                ],
                'courses' => [],
            ];
        }

        $lessonTotals = Lesson::query()
            ->selectRaw('course_id, count(*) as total_lessons')
            ->whereIn('course_id', $courseIds)
            ->groupBy('course_id')
            ->pluck('total_lessons', 'course_id');

        $completedLessons = LessonCompletion::query()
            ->selectRaw('lessons.course_id as course_id, count(*) as completed_lessons')
            ->join('lessons', 'lessons.id', '=', 'lesson_completions.lesson_id')
            ->where('lesson_completions.user_id', $userId)
            ->whereIn('lessons.course_id', $courseIds)
            ->groupBy('lessons.course_id')
            ->pluck('completed_lessons', 'course_id');

        $assignmentStats = $this->assignments->progressStatsForStudentByCourse($courseIds, $userId);
        $courses = [];

        foreach ($enrollments as $enrollment) {
            if ($enrollment->course === null) {
                continue;
            }

            $courseId = (int) $enrollment->course_id;
            $totalLessons = (int) ($lessonTotals[$courseId] ?? 0);
            $doneLessons = (int) ($completedLessons[$courseId] ?? 0);
            $completionPercent = $totalLessons > 0
                ? (int) round(($doneLessons / $totalLessons) * 100)
                : 0;
            $assignment = $assignmentStats[$courseId] ?? [
                'total' => 0,
                'submitted' => 0,
                'graded' => 0,
                'average_score' => null,
            ];

            $courses[] = [
                'course' => $enrollment->course,
                'enrolled_at' => $enrollment->enrolled_at,
                'status' => $enrollment->status?->value ?? (string) $enrollment->getAttribute('status'),
                'completion_percent' => $completionPercent,
                'lessons_done' => $doneLessons,
                'lessons_total' => $totalLessons,
                'assignments_total' => (int) $assignment['total'],
                'assignments_submitted' => (int) $assignment['submitted'],
                'assignments_graded' => (int) $assignment['graded'],
                'average_score' => $assignment['average_score'],
            ];
        }

        $totalLessonsAll = array_sum(array_column($courses, 'lessons_total'));
        $totalDoneLessonsAll = array_sum(array_column($courses, 'lessons_done'));
        $lessonCompletion = $totalLessonsAll > 0
            ? (int) round(($totalDoneLessonsAll / $totalLessonsAll) * 100)
            : 0;

        return [
            'overview' => [
                'courses' => count($courses),
                'lesson_completion' => $lessonCompletion,
                'assignments_submitted' => array_sum(array_column($courses, 'assignments_submitted')),
            ],
            'courses' => $courses,
        ];
    }
}
