<?php

namespace CodeGarage\Lessons\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('lessons.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'course_section_id' => ['required', 'integer', 'exists:course_sections,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'required_unless:content_type,image', 'string'],
            'content_type' => ['required', 'in:text,markdown,video,code,image'],
            'lesson_images' => ['nullable', 'array'],
            'lesson_images.*' => ['string', 'max:2048'],
            'sequence' => ['required', 'integer', 'min:1'],
            'is_preview' => ['nullable', 'boolean'],
        ];
    }
}
