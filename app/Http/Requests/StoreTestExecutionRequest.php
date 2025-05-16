<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestExecutionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Or add your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'script_id' => 'required|exists:test_scripts,id',
            'environment_id' => 'required|exists:environments,id',
            'enable_timeout' => 'nullable|boolean',
            'timeout_minutes' => 'nullable|integer|min:1|max:60',
            'priority' => 'nullable|boolean',
            'notify_completion' => 'nullable|boolean',
        ];
    }
}
