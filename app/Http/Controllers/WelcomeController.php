<?php

namespace App\Http\Controllers;

use App\Enums\CourseStatus;
use App\Models\User;
use Illuminate\Contracts\View\View;
use CodeGarage\Posts\Infrastructure\Persistence\Eloquent\Models\Post;
use Spatie\Permission\Models\Role;

class WelcomeController extends Controller
{
    public function __invoke(): View
    {
        $activeAds = Post::query()
            ->visibleToPublic()
            ->with('author')
            ->orderByDesc('is_pinned')
            ->latest()
            ->limit(3)
            ->get();

        $lecturerRoleExists = Role::query()
            ->where('name', 'lecturer')
            ->where('guard_name', config('auth.defaults.guard', 'web'))
            ->exists();

        if (! $lecturerRoleExists) {
            return view('welcome', [
                'featuredLecturers' => collect(),
                'activeAds' => $activeAds,
            ]);
        }

        $featuredLecturers = User::query()
            ->role('lecturer')
            ->where('is_featured_lecturer', true)
            ->with(['taughtCourses' => fn ($query) => $query
                ->whereIn('status', CourseStatus::publicStatuses())
                ->latest('published_at')])
            ->withCount(['taughtCourses as published_courses_count' => fn ($query) => $query
                ->whereIn('status', CourseStatus::publicStatuses())])
            ->orderByDesc('published_courses_count')
            ->orderBy('name')
            ->limit(6)
            ->get();

        if ($featuredLecturers->isEmpty()) {
            $featuredLecturers = User::query()
                ->role('lecturer')
                ->with(['taughtCourses' => fn ($query) => $query
                    ->whereIn('status', CourseStatus::publicStatuses())
                    ->latest('published_at')])
                ->withCount(['taughtCourses as published_courses_count' => fn ($query) => $query
                    ->whereIn('status', CourseStatus::publicStatuses())])
                ->orderByDesc('published_courses_count')
                ->orderBy('name')
                ->limit(6)
                ->get();
        }

        return view('welcome', [
            'featuredLecturers' => $featuredLecturers,
            'activeAds' => $activeAds,
        ]);
    }
}
