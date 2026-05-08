<?php

namespace CodeGarage\Assignments\Application\Services;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;
use CodeGarage\Assignments\Infrastructure\Persistence\Eloquent\Models\Assignment;
use CodeGarage\Assignments\Infrastructure\Persistence\Eloquent\Models\AssignmentSubmission;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\LessonCompletion;
use CodeGarage\Shared\Application\Services\ApplicationService;

class AssignmentService extends ApplicationService
{
    public function __construct(Dispatcher $events)
    {
        parent::__construct($events);
    }

    public function forUser(int $userId, bool $canManage): Collection
    {
        $query = Assignment::query()
            ->with(['course', 'lesson', 'author'])
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at');

        if (! $canManage) {
            $query->where('is_published', true);
        }

        $assignments = $query->get();

        if ($canManage) {
            return $assignments;
        }

        $completedLessonIds = LessonCompletion::query()
            ->where('user_id', $userId)
            ->pluck('lesson_id')
            ->all();

        $submissions = AssignmentSubmission::query()
            ->where('student_id', $userId)
            ->get()
            ->keyBy('assignment_id');

        return $assignments
            ->filter(function (Assignment $assignment) use ($completedLessonIds) {
                if ($assignment->lesson_id === null) {
                    return false;
                }

                if ($assignment->requires_completion_before_lesson_complete) {
                    return true;
                }

                return in_array((int) $assignment->lesson_id, $completedLessonIds, true);
            })
            ->values()
            ->map(function (Assignment $assignment) use ($submissions) {
                $assignment->setRelation('mySubmission', $submissions->get($assignment->id));

                return $assignment;
            });
    }

    public function create(array $attributes): Assignment
    {
        return $this->transaction(fn () => Assignment::query()->create($attributes));
    }

    public function find(int $assignmentId): ?Assignment
    {
        return Assignment::query()
            ->with(['course', 'author', 'submissions.student', 'submissions.grader'])
            ->find($assignmentId);
    }

    public function submissionForStudent(int $assignmentId, int $studentId): ?AssignmentSubmission
    {
        return AssignmentSubmission::query()
            ->where('assignment_id', $assignmentId)
            ->where('student_id', $studentId)
            ->first();
    }

    public function submit(int $assignmentId, int $studentId, array $payload): AssignmentSubmission
    {
        return $this->transaction(function () use ($assignmentId, $studentId, $payload) {
            $existing = AssignmentSubmission::query()
                ->where('assignment_id', $assignmentId)
                ->where('student_id', $studentId)
                ->first();

            return AssignmentSubmission::query()->updateOrCreate(
                [
                    'assignment_id' => $assignmentId,
                    'student_id' => $studentId,
                ],
                [
                    'content' => $payload['content'] ?? null,
                    'attachment_path' => $payload['attachment_path'] ?? $existing?->attachment_path,
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'due_at' => $existing?->due_at,
                ],
            );
        });
    }

    public function forLessonForStudent(int $lessonId, ?int $studentId, bool $canManage = false): Collection
    {
        $assignments = Assignment::query()
            ->with('course')
            ->where('lesson_id', $lessonId)
            ->where('is_published', true)
            ->orderBy('created_at')
            ->get();

        if ($canManage) {
            return $assignments;
        }

        if ($studentId === null) {
            return collect();
        }

        $submissions = AssignmentSubmission::query()
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->where('student_id', $studentId)
            ->get()
            ->keyBy('assignment_id');

        return $assignments
            ->map(function (Assignment $assignment) use ($submissions) {
                $assignment->setRelation('mySubmission', $submissions->get($assignment->id));

                return $assignment;
            });
    }

    public function assignForLessonCompletion(int $userId, int $lessonId, \DateTimeInterface $completedAt): void
    {
        $completedAtCarbon = CarbonImmutable::instance(\DateTime::createFromInterface($completedAt));

        $assignments = Assignment::query()
            ->where('lesson_id', $lessonId)
            ->where('is_published', true)
            ->get();

        foreach ($assignments as $assignment) {
            $existing = AssignmentSubmission::query()
                ->where('assignment_id', $assignment->id)
                ->where('student_id', $userId)
                ->first();

            $computedDue = $assignment->due_days_after_completion !== null
                ? $completedAtCarbon->addDays((int) $assignment->due_days_after_completion)
                : $assignment->due_at;

            if ($existing === null) {
                AssignmentSubmission::query()->create([
                    'assignment_id' => $assignment->id,
                    'student_id' => $userId,
                    'status' => 'assigned',
                    'due_at' => $computedDue,
                ]);

                continue;
            }

            if ($existing->status !== 'submitted' && $existing->status !== 'graded') {
                $existing->status = 'assigned';
            }

            if ($existing->due_at === null) {
                $existing->due_at = $computedDue;
            }

            $existing->save();
        }
    }

    public function blockingAssignmentsForLessonAndStudent(int $lessonId, int $studentId): Collection
    {
        $requiredAssignments = Assignment::query()
            ->where('lesson_id', $lessonId)
            ->where('is_published', true)
            ->where('requires_completion_before_lesson_complete', true)
            ->get();

        if ($requiredAssignments->isEmpty()) {
            return collect();
        }

        $submissions = AssignmentSubmission::query()
            ->whereIn('assignment_id', $requiredAssignments->pluck('id'))
            ->where('student_id', $studentId)
            ->get()
            ->keyBy('assignment_id');

        return $requiredAssignments
            ->filter(function (Assignment $assignment) use ($submissions) {
                $submission = $submissions->get($assignment->id);

                return ! $submission || ! in_array($submission->status, ['submitted', 'graded'], true);
            })
            ->values();
    }

    public function nextActionableAssignmentForLessonAndStudent(int $lessonId, int $studentId): ?Assignment
    {
        $assignments = Assignment::query()
            ->where('lesson_id', $lessonId)
            ->where('is_published', true)
            ->orderByDesc('requires_completion_before_lesson_complete')
            ->orderBy('created_at')
            ->get();

        if ($assignments->isEmpty()) {
            return null;
        }

        $submissions = AssignmentSubmission::query()
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->where('student_id', $studentId)
            ->get()
            ->keyBy('assignment_id');

        return $assignments->first(function (Assignment $assignment) use ($submissions): bool {
            $submission = $submissions->get($assignment->id);

            return ! $submission || ! in_array($submission->status, ['submitted', 'graded'], true);
        });
    }

    public function grade(int $submissionId, int $graderId, array $payload): AssignmentSubmission
    {
        $submission = AssignmentSubmission::query()->findOrFail($submissionId);

        $submission->fill([
            'score' => $payload['score'] ?? null,
            'feedback' => $payload['feedback'] ?? null,
            'status' => 'graded',
            'graded_by' => $graderId,
            'graded_at' => now(),
        ]);
        $submission->save();

        return $submission->refresh();
    }

    public function progressStatsForStudentByCourse(array $courseIds, int $studentId): array
    {
        if ($courseIds === []) {
            return [];
        }

        $assignmentIdsByCourse = Assignment::query()
            ->select(['id', 'course_id'])
            ->whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->get()
            ->groupBy('course_id');

        $submissionStats = AssignmentSubmission::query()
            ->selectRaw('assignments.course_id as course_id, count(*) as submitted_count, sum(case when assignment_submissions.status = ? then 1 else 0 end) as graded_count, avg(assignment_submissions.score) as average_score', ['graded'])
            ->join('assignments', 'assignments.id', '=', 'assignment_submissions.assignment_id')
            ->where('assignment_submissions.student_id', $studentId)
            ->whereIn('assignments.course_id', $courseIds)
            ->groupBy('assignments.course_id')
            ->get()
            ->keyBy('course_id');

        $result = [];

        foreach ($courseIds as $courseId) {
            $total = (int) ($assignmentIdsByCourse[$courseId]->count() ?? 0);
            $stats = $submissionStats->get($courseId);

            $result[$courseId] = [
                'total' => $total,
                'submitted' => (int) ($stats->submitted_count ?? 0),
                'graded' => (int) ($stats->graded_count ?? 0),
                'average_score' => isset($stats->average_score) ? (float) $stats->average_score : null,
            ];
        }

        return $result;
    }
}
