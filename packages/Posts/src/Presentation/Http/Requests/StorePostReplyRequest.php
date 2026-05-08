<?php

namespace CodeGarage\Posts\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('posts.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string'],
        ];
    }
}
