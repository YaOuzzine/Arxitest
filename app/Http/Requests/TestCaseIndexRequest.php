<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TestCaseIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ensure the user is authenticated to access this list
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * These rules validate the filter parameters if they are present.
     * We use 'nullable' because filters are optional.
     * 'exists' checks ensure the provided IDs correspond to actual records.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'nullable|uuid|exists:projects,id',
            'story_id'   => 'nullable|uuid|exists:stories,id', // Ensure story exists
            'suite_id'   => 'nullable|uuid|exists:test_suites,id', // Ensure suite exists
            'search'     => 'nullable|string|max:255',
            'sort'       => 'nullable|string|in:title,updated_at,created_at,priority,status', // Add allowed sort fields
            'direction'  => 'nullable|string|in:asc,desc',
        ];
    }

    /**
     * Get the filter data from the request.
     * This helper method makes it easy to pass filters to the service.
     *
     * @return array
     */
    public function filters(): array
    {
        // Return only the validated data relevant for filtering
        return $this->validated();
    }
}
