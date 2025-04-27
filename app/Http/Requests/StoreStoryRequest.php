<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'project_id' => 'required|uuid|exists:projects,id',
            'epic_id' => 'nullable|uuid|exists:epics,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'A project must be selected.',
            'project_id.exists' => 'The selected project does not exist.',
            'epic_id.exists' => 'The selected epic does not exist.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Set source as 'manual' for stories created in the system
        $this->merge([
            'source' => 'manual',
        ]);
    }
}
