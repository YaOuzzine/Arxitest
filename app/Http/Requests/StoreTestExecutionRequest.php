<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTestExecutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only authenticated users can start an execution
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'script_id'      => 'required|exists:test_scripts,id',
            'environment_id' => 'required|exists:environments,id',
        ];
    }
}
