<?php

namespace CodeGarage\Queries\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $guestRules = $this->user() === null ? ['required'] : ['nullable'];

        return [
            'name' => [...$guestRules, 'string', 'max:255'],
            'email' => [...$guestRules, 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }
}
