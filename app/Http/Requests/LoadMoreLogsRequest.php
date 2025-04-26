<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class LoadMoreLogsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authenticated access for AJAX log loading
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'offset' => 'nullable|integer|min:0',
            'limit'  => 'nullable|integer|min:1|max:2000',
        ];
    }
}
