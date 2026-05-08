<?php

namespace CodeGarage\Enrollments\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use CodeGarage\Assignments\Application\Services\AssignmentService;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Enrollments\Application\Services\EnrollmentService;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\Enrollment;
use CodeGarage\Enrollments\Presentation\Http\Requests\EnrollRequest;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;
use CodeGarage\Payments\Application\Services\PaymentService;

class EnrollmentController extends Controller
{
    public function index(Request $request, EnrollmentService $enrollments): View
    {
        return view('portal.my-learning', [
            'enrollments' => $enrollments->forUser($request->user()->id),
        ]);
    }

    public function store(
        EnrollRequest $request,
        EnrollmentService $enrollments,
        PaymentService $payments,
    ): RedirectResponse
    {
        $course = Course::query()->findOrFail((int) $request->validated('course_id'));

        if ($course->pricing_type !== 'free' && ! $payments->hasPaidEnrollmentAccess($request->user()->id, $course->id)) {
            return redirect()
                ->route('payments.checkout', $course->id)
                ->with('status', 'Payment is required before enrollment can be activated.');
        }

        $enrollments->enroll(
            $request->user()->id,
            $course->id,
        );

        return back()->with('status', 'You are now enrolled in this course.');
    }

    public function completeLesson(
        Request $request,
        int $lessonId,
        EnrollmentService $enrollments,
        AssignmentService $assignments,
    ): RedirectResponse {
        abort_unless($request->user()?->can('lessons.complete'), 403);

        $lesson = Lesson::query()->findOrFail($lessonId);
        $lesson->loadMissing('section', 'course');
        abort_unless($enrollments->isEnrolled($request->user()->id, $lesson->course_id), 403);

        $blockingAssignments = $assignments->blockingAssignmentsForLessonAndStudent($lessonId, $request->user()->id);
        if ($blockingAssignments->isNotEmpty()) {
            return back()->with('status', 'Complete required assignment(s) before marking this lesson complete.');
        }

        $enrollments->markLessonCompleted($request->user()->id, $lessonId);

        $nextAssignment = $assignments->nextActionableAssignmentForLessonAndStudent($lessonId, $request->user()->id);
        if ($nextAssignment !== null) {
            return redirect()
                ->route('assignments.show', $nextAssignment->id)
                ->with('status', 'Lesson marked complete. Complete the linked assignment before continuing.');
        }

        $nextLesson = Lesson::query()
            ->select('lessons.*')
            ->join('course_sections', 'course_sections.id', '=', 'lessons.course_section_id')
            ->where('lessons.course_id', $lesson->course_id)
            ->where(function ($query) use ($lesson) {
                $query->where('course_sections.sequence', '>', $lesson->section?->sequence)
                    ->orWhere(function ($sameSectionQuery) use ($lesson) {
                        $sameSectionQuery
                            ->where('course_sections.sequence', $lesson->section?->sequence)
                            ->where('lessons.sequence', '>', $lesson->sequence);
                    });
            })
            ->orderBy('course_sections.sequence')
            ->orderBy('lessons.sequence')
            ->first();

        if ($nextLesson !== null) {
            return redirect()
                ->route('lessons.show', [$lesson->course->slug, $nextLesson->slug])
                ->with('status', 'Lesson marked as completed. Moving to the next lesson.');
        }

        return redirect()
            ->route('courses.show', $lesson->course->slug)
            ->with('status', 'Lesson marked as completed. You have reached the end of this course sequence.');
    }

    public function updateMeetingLink(Request $request, int $enrollmentId): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->hasAnyRole(['admin', 'lecturer']), 403);

        $enrollment = Enrollment::query()
            ->with('course')
            ->findOrFail($enrollmentId);

        if ($user->hasRole('lecturer') && ! $user->hasRole('admin')) {
            abort_unless((int) ($enrollment->course?->lecturer_id ?? 0) === (int) $user->id, 403);
        }

        $validated = $request->validate([
            'meeting_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $enrollment->forceFill([
            'meeting_url' => $validated['meeting_url'] ?? null,
        ])->save();

        return back()->with('status', 'Meeting link updated for this student enrollment.');
    }
}
