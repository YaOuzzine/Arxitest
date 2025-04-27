<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'source' => 'required|string|in:manual,jira,github,azure',
            'external_id' => 'nullable|string|max:100',
            'metadata' => 'nullable|array',
        ];
    }
}
