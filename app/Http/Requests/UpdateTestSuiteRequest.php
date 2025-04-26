<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateTestSuiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // For now, assume authenticated users can update
        return Auth::check();
    }

    public function rules(): array
    {
        $projectId = $this->route('project')->id;
        $suiteId   = $this->route('test_suite')->id;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('test_suites')
                    ->where(fn($q) => $q->where('project_id', $projectId))
                    ->ignore($suiteId),
            ],
            'description'              => 'nullable|string|max:255',
            'settings.default_priority'=> 'required|string|in:low,medium,high',
            'settings.execution_mode'  => 'nullable|string|in:sequential,parallel',
        ];
    }
}
