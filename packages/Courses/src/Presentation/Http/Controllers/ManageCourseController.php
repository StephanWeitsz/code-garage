<?php

namespace CodeGarage\Courses\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use CodeGarage\Courses\Application\Services\CourseService;
use CodeGarage\Courses\Infrastructure\Persistence\Eloquent\Models\Course;
use CodeGarage\Courses\Presentation\Http\Requests\StoreCourseRequest;
use CodeGarage\Courses\Presentation\Http\Requests\UpdateCourseRequest;

class ManageCourseController extends Controller
{
    public function store(StoreCourseRequest $request, CourseService $courses): RedirectResponse
    {
        $course = $courses->create([
            ...$request->validated(),
            'lecturer_id' => $request->user()->id,
            'published_at' => $request->validated('status') === 'published' ? now() : null,
        ]);

        return redirect()->route('courses.show', $course->slug)
            ->with('status', 'Course created successfully.');
    }

    public function update(
        UpdateCourseRequest $request,
        Course $course,
        CourseService $courses,
    ): RedirectResponse {
        $this->authorize('update', $course);

        $course = $courses->update($course, [
            ...$request->validated(),
            'published_at' => $request->validated('status') === 'published' ? now() : null,
        ]);

        return redirect()->route('courses.show', $course->slug)
            ->with('status', 'Course updated successfully.');
    }
}
