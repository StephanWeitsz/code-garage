<?php

namespace CodeGarage\Assignments\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use CodeGarage\Assignments\Application\Services\AssignmentService;
use CodeGarage\Assignments\Infrastructure\Persistence\Eloquent\Models\Assignment;
use CodeGarage\Assignments\Infrastructure\Persistence\Eloquent\Models\AssignmentSubmission;
use CodeGarage\Enrollments\Application\Services\EnrollmentService;
use CodeGarage\Assignments\Presentation\Http\Requests\GradeSubmissionRequest;
use CodeGarage\Assignments\Presentation\Http\Requests\StoreAssignmentRequest;
use CodeGarage\Assignments\Presentation\Http\Requests\SubmitAssignmentRequest;

class AssignmentController extends Controller
{
    public function index(Request $request, AssignmentService $assignments): View
    {
        $user = $request->user();
        $canManage = $user?->hasAnyRole(['admin', 'lecturer']) ?? false;

        return view('assignments::index', [
            'assignments' => $assignments->forUser($user->id, $canManage),
            'canManage' => $canManage,
        ]);
    }

    public function show(
        int $assignment,
        Request $request,
        AssignmentService $assignments,
        EnrollmentService $enrollments,
    ): View
    {
        $record = $assignments->find($assignment);
        abort_if($record === null, 404);

        if (! $record->is_published && ! $request->user()?->hasAnyRole(['admin', 'lecturer'])) {
            abort(403);
        }

        if (
            $request->user()?->hasRole('student')
            && $record->lesson_id !== null
            && ! $record->requires_completion_before_lesson_complete
            && ! $enrollments->isLessonCompleted($request->user()->id, (int) $record->lesson_id)
        ) {
            abort(403);
        }

        return view('assignments::show', [
            'assignment' => $record,
            'mySubmission' => $request->user()->hasRole('student')
                ? $assignments->submissionForStudent($record->id, $request->user()->id)
                : null,
            'canManage' => $request->user()?->hasAnyRole(['admin', 'lecturer']) ?? false,
        ]);
    }

    public function store(StoreAssignmentRequest $request, AssignmentService $assignments): RedirectResponse
    {
        $assignments->create([
            ...$request->validated(),
            'author_id' => $request->user()->id,
            'max_points' => (int) ($request->validated('max_points') ?? 100),
            'is_published' => (bool) ($request->validated('is_published') ?? true),
        ]);

        return redirect()->route('assignments.index')->with('status', 'Assignment created successfully.');
    }

    public function submit(
        int $assignment,
        SubmitAssignmentRequest $request,
        AssignmentService $assignments,
        EnrollmentService $enrollments,
    ): RedirectResponse {
        $record = Assignment::query()->findOrFail($assignment);

        abort_unless($record->is_published, 403);

        if (
            $record->lesson_id !== null
            && ! $record->requires_completion_before_lesson_complete
            && ! $enrollments->isLessonCompleted($request->user()->id, (int) $record->lesson_id)
        ) {
            abort(403);
        }

        $payload = $request->validated();

        if ($request->hasFile('attachment')) {
            $payload['attachment_path'] = $request->file('attachment')
                ->store('assignment-submissions', 'public');
        }

        $assignments->submit($record->id, $request->user()->id, $payload);

        return back()->with('status', 'Assignment submitted successfully.');
    }

    public function grade(
        int $assignment,
        int $submission,
        GradeSubmissionRequest $request,
        AssignmentService $assignments,
    ): RedirectResponse {
        $record = Assignment::query()->findOrFail($assignment);
        $targetSubmission = AssignmentSubmission::query()
            ->where('assignment_id', $record->id)
            ->findOrFail($submission);

        $assignments->grade($targetSubmission->id, $request->user()->id, $request->validated());

        return back()->with('status', 'Submission graded successfully.');
    }
}
