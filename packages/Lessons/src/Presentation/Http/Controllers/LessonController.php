<?php

namespace CodeGarage\Lessons\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use CodeGarage\Assignments\Application\Services\AssignmentService;
use CodeGarage\Courses\Application\Services\CourseService;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Enrollments\Application\Services\EnrollmentService;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\LessonCompletion;
use CodeGarage\Lessons\Application\Services\CourseSectionService;
use CodeGarage\Lessons\Application\Services\LessonService;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;
use CodeGarage\Lessons\Presentation\Http\Requests\StoreLessonRequest;
use CodeGarage\Lessons\Presentation\Http\Requests\UpdateLessonRequest;
use CodeGarage\Posts\Application\Services\PostService;

class LessonController extends Controller
{
    public function show(
        string $courseSlug,
        string $lessonSlug,
        CourseService $courses,
        LessonService $lessons,
        CourseSectionService $sections,
        EnrollmentService $enrollments,
        AssignmentService $assignments,
        PostService $posts,
    ): View {
        $course = $courses->findBySlug($courseSlug);
        abort_if($course === null, 404);

        $lesson = $lessons->findByCourseAndSlug($course->id, $lessonSlug);
        abort_if($lesson === null, 404);

        $user = request()->user();
        $isEnrolled = $user ? $enrollments->isEnrolled($user->id, $course->id) : false;
        $canManage = $user?->hasAnyRole(['admin', 'lecturer']) ?? false;
        $isLecturer = $user?->hasRole('lecturer') ?? false;
        $isStudentPreview = $canManage && request()->boolean('preview_as_student');
        $isCompleted = $user ? $enrollments->isLessonCompleted($user->id, $lesson->id) : false;
        $blockingAssignments = $user
            ? $assignments->blockingAssignmentsForLessonAndStudent($lesson->id, $user->id)
            : collect();
        $completedLessonIds = $user
            ? LessonCompletion::query()
                ->select('lesson_completions.lesson_id')
                ->join('lessons', function (JoinClause $join) use ($course) {
                    $join->on('lessons.id', '=', 'lesson_completions.lesson_id')
                        ->where('lessons.course_id', $course->id);
                })
                ->where('lesson_completions.user_id', $user->id)
                ->pluck('lesson_completions.lesson_id')
                ->map(fn ($lessonId): int => (int) $lessonId)
                ->all()
            : [];

        if ($user && $isCompleted) {
            $completion = LessonCompletion::query()
                ->where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)
                ->first();

            if ($completion?->completed_at) {
                $assignments->assignForLessonCompletion($user->id, $lesson->id, $completion->completed_at);
            }
        }

        abort_unless($lesson->is_preview || $isEnrolled || $canManage, 403);

        $renderedContent = $lesson->content;
        if ($lesson->content_type->value === 'markdown') {
            $markdown = $this->filterInstructorMarkdownBlocks($lesson->content, $isLecturer && ! $isStudentPreview);
            $renderedContent = (string) Str::markdown($markdown, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
        }

        return view('lessons::lesson', [
            'course' => $course,
            'lesson' => $lesson,
            'sections' => $sections->forCourse($course->id),
            'isEnrolled' => $isEnrolled,
            'isCompleted' => $isCompleted,
            'canComplete' => $user ? ($isEnrolled && $user->can('lessons.complete') && $blockingAssignments->isEmpty()) : false,
            'blockingAssignments' => $blockingAssignments,
            'completedLessonIds' => $completedLessonIds,
            'renderedContent' => $renderedContent,
            'assignments' => $assignments->forLessonForStudent($lesson->id, $user?->id, $canManage && ! $isStudentPreview),
            'lessonPosts' => $posts->forLesson($lesson->id, $user?->id, $canManage && ! $isStudentPreview),
            'isStudentPreview' => $isStudentPreview,
        ]);
    }

    public function store(StoreLessonRequest $request, LessonService $lessons): RedirectResponse
    {
        $lesson = $lessons->create($request->validated());
        $course = Course::query()->findOrFail($lesson->course_id);

        return redirect()->route('lessons.show', [$course->slug, $lesson->slug])
            ->with('status', 'Lesson created successfully.');
    }

    public function update(
        UpdateLessonRequest $request,
        Lesson $lesson,
        LessonService $lessons,
    ): RedirectResponse {
        $this->authorize('update', $lesson);
        $lessons->update($lesson, $request->validated());

        return back()->with('status', 'Lesson updated successfully.');
    }

    private function filterInstructorMarkdownBlocks(string $markdown, bool $isLecturer): string
    {
        if ($isLecturer) {
            return preg_replace('/(?m)^:::instructor\s*$|^:::\s*$/', '', $markdown) ?? $markdown;
        }

        return preg_replace('/(?ms)^:::instructor\s*$.*?^:::\s*$/m', '', $markdown) ?? $markdown;
    }
}
