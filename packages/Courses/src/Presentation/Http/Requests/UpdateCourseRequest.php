<?php

namespace CodeGarage\Courses\Presentation\Http\Requests;

class UpdateCourseRequest extends StoreCourseRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('courses.update') ?? false;
    }
}
