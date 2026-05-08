<?php

namespace CodeGarage\Posts\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->can('posts.create') || $user?->can('posts.create-own');
    }

    public function rules(): array
    {
        return [
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
            'title' => ['nullable', 'string', 'max:180'],
            'body' => ['required', 'string'],
            'type' => ['nullable', 'in:discussion,announcement,absence_notice'],
            'is_pinned' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator): void {
            $user = $this->user();
            $type = (string) $this->input('type', $user?->hasRole('student') ? 'absence_notice' : 'discussion');

            if ($user?->hasRole('student')) {
                if (! $user->can('posts.create-own')) {
                    $validator->errors()->add('type', 'You are not authorized to create this post.');
                }

                if (! in_array($type, ['absence_notice', 'discussion'], true)) {
                    $validator->errors()->add('type', 'Students can submit lesson questions or absence notices only.');
                }

                if (blank($this->input('lesson_id'))) {
                    $validator->errors()->add('lesson_id', 'A lesson is required for student posts.');
                }
            } else {
                if (! $user?->can('posts.create')) {
                    $validator->errors()->add('type', 'You are not authorized to create this post.');
                }

                if ($type === 'absence_notice') {
                    $validator->errors()->add('type', 'Absence notices are reserved for students.');
                }

                if (blank((string) $this->input('title'))) {
                    $validator->errors()->add('title', 'A title is required.');
                }

                if ($type === 'discussion' && blank($this->input('lesson_id'))) {
                    $validator->errors()->add('lesson_id', 'A lesson is required for discussion posts.');
                }
            }
        });
    }
}
