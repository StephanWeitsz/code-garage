<?php

namespace CodeGarage\Courses\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use CodeGarage\Courses\Application\Services\CourseService;
use CodeGarage\Enrollments\Application\Services\EnrollmentService;
use CodeGarage\Enrollments\Infrastructure\Persistence\Eloquent\Models\Enrollment;
use CodeGarage\Lessons\Application\Services\CourseSectionService;
use CodeGarage\Queries\Infrastructure\Persistence\Eloquent\Models\CourseQuery;

class CourseCatalogController extends Controller
{
    public function index(CourseService $courses): View
    {
        return view('courses::index', [
            'courses' => $courses->published(),
        ]);
    }

    public function show(
        string $slug,
        CourseService $courses,
        CourseSectionService $sections,
        EnrollmentService $enrollments,
    ): View {
        $course = $courses->findBySlug($slug);
        abort_if($course === null, 404);

        $user = request()->user();
        $isEnrolled = $user ? $enrollments->isEnrolled($user->id, $course->id) : false;
        $enrollment = $user && $isEnrolled
            ? $enrollments->enrollmentForUserAndCourse($user->id, $course->id)
            : null;
        $canManageEnrollments = $user
            ? ($user->hasRole('admin') || ($user->hasRole('lecturer') && (int) $course->lecturer_id === (int) $user->id))
            : false;

        return view('courses::show', [
            'course' => $course,
            'sections' => $sections->forCourse($course->id),
            'isEnrolled' => $isEnrolled,
            'completion' => $user ? $enrollments->completionForCourse($user->id, $course->id) : 0,
            'meetingLink' => $isEnrolled ? ($enrollment?->meeting_url ?: $course->default_meeting_url) : null,
            'canManageEnrollments' => $canManageEnrollments,
            'enrolledStudents' => $canManageEnrollments
                ? Enrollment::query()
                    ->with('user')
                    ->where('course_id', $course->id)
                    ->orderByDesc('enrolled_at')
                    ->get()
                : collect(),
            'myCourseQueries' => $user
                ? CourseQuery::query()
                    ->where('course_id', $course->id)
                    ->where('user_id', $user->id)
                    ->latest()
                    ->limit(5)
                    ->get()
                : collect(),
            'courseQueries' => $canManageEnrollments
                ? CourseQuery::query()
                    ->with('student')
                    ->where('course_id', $course->id)
                    ->latest()
                    ->limit(20)
                    ->get()
                : collect(),
        ]);
    }
}
