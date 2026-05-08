<?php

namespace CodeGarage\Assignments\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'lecturer']) ?? false;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
            'title' => ['required', 'string', 'max:180'],
            'instructions' => ['required', 'string'],
            'due_at' => ['nullable', 'date'],
            'due_days_after_completion' => ['nullable', 'integer', 'min:1', 'max:60'],
            'requires_completion_before_lesson_complete' => ['nullable', 'boolean'],
            'max_points' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'is_published' => ['nullable', 'boolean'],
        ];
    }
}
