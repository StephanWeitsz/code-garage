<?php

namespace App\Http\Controllers;

use App\Enums\CourseStatus;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Spatie\Permission\Models\Role;

class WelcomeController extends Controller
{
    public function __invoke(): View
    {
        $lecturerRoleExists = Role::query()
            ->where('name', 'lecturer')
            ->where('guard_name', config('auth.defaults.guard', 'web'))
            ->exists();

        if (! $lecturerRoleExists) {
            return view('welcome', [
                'featuredLecturer' => null,
            ]);
        }

        $featuredLecturer = User::query()
            ->role('lecturer')
            ->where('is_featured_lecturer', true)
            ->with(['taughtCourses' => fn ($query) => $query
                ->whereIn('status', CourseStatus::publicStatuses())
                ->latest('published_at')])
            ->withCount(['taughtCourses as published_courses_count' => fn ($query) => $query
                ->whereIn('status', CourseStatus::publicStatuses())])
            ->first();

        if (! $featuredLecturer) {
            $featuredLecturer = User::query()
                ->role('lecturer')
                ->with(['taughtCourses' => fn ($query) => $query
                    ->whereIn('status', CourseStatus::publicStatuses())
                    ->latest('published_at')])
                ->withCount(['taughtCourses as published_courses_count' => fn ($query) => $query
                    ->whereIn('status', CourseStatus::publicStatuses())])
                ->orderByDesc('published_courses_count')
                ->orderBy('name')
                ->first();
        }

        return view('welcome', [
            'featuredLecturer' => $featuredLecturer,
        ]);
    }
}
