<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoryIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'    => 'nullable|exists:projects,id',
            'suite_id'      => 'nullable|exists:test_suites,id',
            'test_case_id'  => 'nullable|exists:test_cases,id',
            'source'        => 'nullable|array',
            'source.*'      => 'in:manual,jira,github,azure',
            'search'        => 'nullable|string',
            'sort'          => 'nullable|in:title,created_at,updated_at,source,external_id',
            'direction'     => 'nullable|in:asc,desc',
            'per_page'      => 'nullable|integer',
        ];
    }
}
