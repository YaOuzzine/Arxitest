<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestCaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // For now, keep the same authorization logic as in controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'expected_results' => 'required|string',
            'steps' => 'required|array|min:1',
            'steps.*' => 'required|string|max:500',
            'suite_id' => [
                'required',
                function ($attribute, $value, $fail) use ($project) {
                    $suite = \App\Models\TestSuite::find($value);
                    if (!$suite || $suite->project_id !== $project->id) {
                        $fail('The selected test suite is invalid.');
                    }
                }
            ],
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:draft,active,deprecated,archived',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50'
        ];
    }
}
