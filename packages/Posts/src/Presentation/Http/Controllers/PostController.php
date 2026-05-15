<?php

namespace CodeGarage\Posts\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\Enrollment;
use CodeGarage\Lessons\Infrastructure\Persistence\Eloquent\Models\Lesson;
use CodeGarage\Posts\Application\Services\PostService;
use CodeGarage\Posts\Infrastructure\Persistence\Eloquent\Models\Post;
use CodeGarage\Posts\Presentation\Http\Requests\StorePostReplyRequest;
use CodeGarage\Posts\Presentation\Http\Requests\StorePostRequest;

class PostController extends Controller
{
    public function index(Request $request, PostService $posts): View
    {
        $user = $request->user();
        abort_unless($user?->can('posts.view'), 403);

        $canCreateAnnouncement = $user->can('posts.create');
        $canCreateAbsence = $user->hasRole('student') && $user->can('posts.create-own');
        $canManageLifecycle = $user->hasAnyRole(['admin', 'lecturer']);
        $lessons = $this->lessonOptionsFor($user, $canCreateAnnouncement);
        $selectedType = (string) $request->query('type', '');
        $selectedLessonId = $request->query('lesson_id');

        if (! in_array($selectedType, ['', 'announcement', 'discussion', 'absence_notice', 'ad'], true)) {
            $selectedType = '';
        }

        if (filled($selectedLessonId) && ! $lessons->has((int) $selectedLessonId)) {
            $selectedLessonId = null;
        }

        $feed = $posts->feed([
            'type' => $selectedType,
            'lesson_id' => $selectedLessonId,
            'can_manage' => $canManageLifecycle,
        ]);
        $feed->appends($request->query());

        return view('posts::index', [
            'posts' => $feed,
            'lessons' => $lessons,
            'canCreateAnnouncement' => $canCreateAnnouncement,
            'canCreateAbsence' => $canCreateAbsence,
            'canManageLifecycle' => $canManageLifecycle,
            'selectedType' => $selectedType,
            'selectedLessonId' => $selectedLessonId,
        ]);
    }

    public function show(int $post, Request $request, PostService $posts): View
    {
        $user = $request->user();
        abort_unless($user?->can('posts.view'), 403);

        $record = $posts->find($post);
        abort_if($record === null, 404);

        $canManageLifecycle = $this->canManageDiscussionLifecycle($user, $record);
        abort_if($record->status === 'archived' && ! $canManageLifecycle, 404);

        return view('posts::show', [
            'post' => $record,
            'canManageLifecycle' => $canManageLifecycle,
            'isReplyLocked' => $record->isAd() || in_array($record->status, ['closed', 'archived', 'inactive'], true),
            'isPublicAd' => false,
        ]);
    }

    public function publicAd(int $post, PostService $posts): View
    {
        $record = $posts->find($post);

        abort_if($record === null || ! $record->isAd(), 404);
        abort_unless(Post::query()->visibleToPublic()->whereKey($record->id)->exists(), 404);

        return view('posts::show', [
            'post' => $record,
            'canManageLifecycle' => false,
            'isReplyLocked' => true,
            'isPublicAd' => true,
        ]);
    }

    public function store(StorePostRequest $request, PostService $posts): RedirectResponse
    {
        $user = $request->user();
        $type = (string) $request->validated('type', $user->hasRole('student') ? 'absence_notice' : 'discussion');
        $lessonId = $request->validated('lesson_id');
        $lesson = filled($lessonId)
            ? Lesson::query()->with('course')->findOrFail((int) $lessonId)
            : null;

        $isPinned = false;
        $title = (string) $request->validated('title', '');
        $courseId = $lesson?->course_id;

        if ($user->hasRole('student')) {
            abort_if($lesson === null, 422);

            $isEnrolled = Enrollment::query()
                ->where('user_id', $user->id)
                ->where('course_id', $lesson->course_id)
                ->exists();

            abort_unless($isEnrolled, 403);
            abort_unless(in_array($type, ['absence_notice', 'discussion'], true), 403);

            if (blank($title)) {
                $title = $type === 'absence_notice'
                    ? 'Absence notice for '.$lesson->title
                    : 'Question about '.$lesson->title;
            }
        } else {
            abort_unless($user->can('posts.create'), 403);

            if ($lesson !== null && $user->hasRole('lecturer') && ! $user->hasRole('admin')) {
                abort_unless((int) $lesson->course->lecturer_id === (int) $user->id, 403);
            }

            abort_if($type === 'absence_notice', 422);
            $isPinned = (bool) $request->validated('is_pinned', false);
        }

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('ads', 'public')
            : null;

        $startsAt = $this->normalizeLocalDateTime($request->validated('starts_at'));
        $endsAt = $this->normalizeLocalDateTime($request->validated('ends_at'));

        $posts->create([
            'course_id' => $courseId,
            'lesson_id' => $lesson?->id,
            'author_id' => $user->id,
            'title' => $title,
            'body' => (string) $request->validated('body'),
            'image_path' => $imagePath,
            'cta_label' => $request->validated('cta_label'),
            'cta_url' => $request->validated('cta_url'),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => $type === 'ad' && ! (bool) $request->validated('is_active', true) ? 'inactive' : ($type === 'ad' ? 'active' : 'published'),
            'type' => $type,
            'is_pinned' => $isPinned,
        ]);

        return redirect()->route('posts.index')->with('status', 'Post published successfully.');
    }

    public function reply(
        int $post,
        StorePostReplyRequest $request,
        PostService $posts,
    ): RedirectResponse {
        $record = Post::query()->findOrFail($post);
        abort_if(in_array($record->status, ['closed', 'archived'], true), 422);

        $posts->reply($record->id, $request->user()->id, $request->validated('body'));

        return back()->with('status', 'Reply posted.');
    }

    public function close(int $post, Request $request, PostService $posts): RedirectResponse
    {
        $record = Post::query()->with('course')->findOrFail($post);
        abort_unless($this->canManageDiscussionLifecycle($request->user(), $record), 403);

        $posts->updateStatus($record, 'closed');

        return back()->with('status', 'Discussion closed. New replies are disabled.');
    }

    public function archive(int $post, Request $request, PostService $posts): RedirectResponse
    {
        $record = Post::query()->with('course')->findOrFail($post);
        abort_unless($this->canManageDiscussionLifecycle($request->user(), $record), 403);

        $posts->updateStatus($record, 'archived');

        return redirect()->route('posts.index')->with('status', 'Discussion archived.');
    }

    public function reopen(int $post, Request $request, PostService $posts): RedirectResponse
    {
        $record = Post::query()->with('course')->findOrFail($post);
        abort_unless($this->canManageDiscussionLifecycle($request->user(), $record), 403);

        $posts->updateStatus($record, 'published');

        return back()->with('status', 'Discussion reopened.');
    }

    protected function lessonOptionsFor($user, bool $canCreateAnnouncement): Collection
    {
        $query = Lesson::query()
            ->with('course')
            ->orderBy('course_id')
            ->orderBy('sequence');

        if ($canCreateAnnouncement) {
            if ($user->hasRole('lecturer') && ! $user->hasRole('admin')) {
                $query->whereHas('course', fn ($courseQuery) => $courseQuery->where('lecturer_id', $user->id));
            }
        } else {
            $enrolledCourseIds = Enrollment::query()
                ->where('user_id', $user->id)
                ->pluck('course_id');

            $query->whereIn('course_id', $enrolledCourseIds);
        }

        return $query->get()->mapWithKeys(function (Lesson $lesson): array {
            $label = sprintf(
                '%s - %02d. %s',
                $lesson->course?->title ?? 'Course',
                $lesson->sequence,
                $lesson->title,
            );

            return [$lesson->id => $label];
        });
    }

    protected function canManageDiscussionLifecycle($user, Post $post): bool
    {
        if (! $user || ! $user->hasAnyRole(['admin', 'lecturer'])) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($post->course && (int) $post->course->lecturer_id === (int) $user->id) {
            return true;
        }

        return (int) $post->author_id === (int) $user->id;
    }

    protected function normalizeLocalDateTime(?string $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        return Carbon::parse($value, 'Africa/Johannesburg')->utc();
    }
}
