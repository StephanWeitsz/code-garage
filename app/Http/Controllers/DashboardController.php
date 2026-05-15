<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use CodeGarage\Courses\Application\Services\CourseService;
use CodeGarage\Enrollments\Application\Services\EnrollmentService;
use CodeGarage\Events\Infrastructure\Persistence\Eloquent\Models\Event;
use CodeGarage\Posts\Infrastructure\Persistence\Eloquent\Models\Post;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        CourseService $courses,
        EnrollmentService $enrollments,
    ): View {
        $user = $request->user();

        return view('portal.dashboard', [
            'featuredCourses' => $courses->published(3),
            'myCourses' => $user ? $enrollments->forUser($user->id) : collect(),
            'activeAds' => Post::query()
                ->visibleToPublic()
                ->with('author')
                ->orderByDesc('is_pinned')
                ->latest()
                ->limit(4)
                ->get(),
            'activeDiscussions' => Post::query()
                ->with(['author', 'course', 'lesson'])
                ->whereIn('status', ['published', 'closed'])
                ->whereIn('type', ['discussion', 'announcement'])
                ->orderByDesc('is_pinned')
                ->latest()
                ->limit(6)
                ->get(),
            'activeEvents' => Event::query()
                ->published()
                ->upcoming()
                ->orderBy('starts_at')
                ->limit(4)
                ->get(),
        ]);
    }
}
