<?php

namespace CodeGarage\Queries\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Queries\Infrastructure\Persistence\Eloquent\Models\CourseQuery;
use CodeGarage\Queries\Presentation\Http\Requests\StoreCourseQueryRequest;
use Illuminate\Http\RedirectResponse;

class CourseQueryController extends Controller
{
    public function store(StoreCourseQueryRequest $request, Course $course): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        CourseQuery::query()->create([
            'course_id' => $course->id,
            'user_id' => $user?->id,
            'name' => $user?->name ?: $validated['name'],
            'email' => $user?->email ?: $validated['email'],
            'mobile' => $validated['mobile'] ?? $user?->mobile,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'audience' => $user ? 'registered_student' : 'prospective_student',
            'status' => 'open',
        ]);

        return back()->with('status', 'Your course query has been logged. We will get back to you soon.');
    }
}
