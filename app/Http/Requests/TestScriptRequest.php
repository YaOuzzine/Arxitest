<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestScriptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // We'll keep actual authorization in the service
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'framework_type' => 'required|string|in:selenium-python,cypress,other',
            'script_content' => 'required|string',
        ];
    }
}
