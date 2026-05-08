<?php

namespace CodeGarage\DevelopmentRequests\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDevelopmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['required', 'email', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'preferred_contact_method' => ['required', Rule::in(['email', 'phone', 'whatsapp'])],
            'project_name' => ['required', 'string', 'max:255'],
            'project_type' => ['required', 'string', 'max:120'],
            'project_goal' => ['required', 'string', 'min:20', 'max:5000'],
            'target_users' => ['nullable', 'string', 'max:3000'],
            'current_process' => ['nullable', 'string', 'max:3000'],
            'must_have_features' => ['nullable', 'array', 'max:8'],
            'must_have_features.*' => ['nullable', 'string', 'max:255'],
            'nice_to_have_features' => ['nullable', 'array', 'max:8'],
            'nice_to_have_features.*' => ['nullable', 'string', 'max:255'],
            'integrations' => ['nullable', 'string', 'max:3000'],
            'content_and_data' => ['nullable', 'string', 'max:3000'],
            'timeline' => ['nullable', 'string', 'max:120'],
            'budget_range' => ['nullable', 'string', 'max:120'],
            'success_measure' => ['nullable', 'string', 'max:3000'],
            'additional_context' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'must_have_features' => $this->cleanList($this->input('must_have_features', [])),
            'nice_to_have_features' => $this->cleanList($this->input('nice_to_have_features', [])),
        ]);
    }

    private function cleanList(mixed $items): array
    {
        return collect(is_array($items) ? $items : [])
            ->map(fn ($item) => is_string($item) ? trim($item) : null)
            ->filter()
            ->values()
            ->all();
    }
}
