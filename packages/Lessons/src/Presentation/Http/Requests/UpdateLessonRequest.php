<?php

namespace CodeGarage\Lessons\Presentation\Http\Requests;

class UpdateLessonRequest extends StoreLessonRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('lessons.update') ?? false;
    }
}
