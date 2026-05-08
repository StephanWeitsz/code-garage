<?php

namespace CodeGarage\Courses\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('courses.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'cover_image' => ['nullable', 'string', 'max:2048'],
            'knowledge_prerequisites' => ['nullable', 'array'],
            'knowledge_prerequisites.*' => ['nullable', 'string', 'max:255'],
            'equipment_requirements' => ['nullable', 'array'],
            'equipment_requirements.*.name' => ['required_with:equipment_requirements', 'string', 'max:120'],
            'equipment_requirements.*.url' => ['nullable', 'url', 'max:2048'],
            'equipment_requirements.*.notes' => ['nullable', 'string', 'max:500'],
            'difficulty_level' => ['required', 'in:beginner,intermediate,advanced'],
            'category' => ['required', 'string', 'max:120'],
            'status' => ['required', 'in:draft,build,published'],
            'pricing_type' => ['required', 'in:free,once_off,per_lesson,hourly'],
            'pricing_amount' => ['required_unless:pricing_type,free', 'nullable', 'numeric', 'min:0'],
            'pricing_currency' => ['required', 'string', 'size:3'],
            'default_meeting_url' => ['nullable', 'url', 'max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('pricing_currency')) {
            $this->merge([
                'pricing_currency' => strtoupper((string) $this->input('pricing_currency')),
            ]);
        }
    }
}
