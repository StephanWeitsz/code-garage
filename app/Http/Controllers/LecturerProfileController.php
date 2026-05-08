<?php

namespace App\Http\Controllers;

use App\Enums\CourseStatus;
use App\Models\User;
use Illuminate\Contracts\View\View;

class LecturerProfileController extends Controller
{
    public function __invoke(User $lecturer): View
    {
        abort_unless($lecturer->hasRole('lecturer'), 404);

        $courses = $lecturer->taughtCourses()
            ->whereIn('status', CourseStatus::publicStatuses())
            ->latest('published_at')
            ->get();

        return view('lecturers.show', [
            'lecturer' => $lecturer,
            'courses' => $courses,
        ]);
    }
}
