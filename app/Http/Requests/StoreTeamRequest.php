<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only authenticated users may create teams
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:200',
            'logo'        => 'nullable|image|max:2048',
            'invites'     => 'nullable|json',
        ];
    }
}
