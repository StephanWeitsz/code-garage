<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use CodeGarage\Courses\Application\Services\CourseService;
use CodeGarage\Enrollments\Application\Services\EnrollmentService;

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
        ]);
    }
}
