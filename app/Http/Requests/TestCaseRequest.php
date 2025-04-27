<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'title'             => 'required|string|max:200',
            'description'       => 'nullable|string|max:1000',
            'expected_results'  => 'required|string',
            'steps'             => 'required|array|min:1',
            'steps.*'           => 'required|string|max:500',

            // Required story_id, must exist and belong to this project
            'story_id' => [
                'required',
                'uuid',
                function ($attribute, $value, $fail) use ($project) {
                    $story = \App\Models\Story::find($value);
                    if (!$story || $story->project_id !== $project->id) {
                        $fail('The selected story is invalid.');
                    }
                },
            ],

            // Optional suite_id, if present must belong to this project
            'suite_id' => [
                'nullable',
                'uuid',
                function ($attribute, $value, $fail) use ($project) {
                    if ($value) {
                        $suite = \App\Models\TestSuite::find($value);
                        if (!$suite || $suite->project_id !== $project->id) {
                            $fail('The selected test suite is invalid.');
                        }
                    }
                },
            ],

            'priority'  => 'required|in:low,medium,high',
            'status'    => 'required|in:draft,active,deprecated,archived',
            'tags'      => 'nullable|array',
            'tags.*'    => 'string|max:50',
        ];
    }
}
