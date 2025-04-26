<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // We'll handle authorization separately later
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'content' => 'required|string',
            'format' => 'required|string|in:json,csv,xml,plain,other',
            'is_sensitive' => 'boolean',
            'usage_context' => 'nullable|string|max:255',
        ];
    }
}
