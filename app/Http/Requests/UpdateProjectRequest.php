<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Use TeamPolicy@update to decide
        // return Auth::check() && Auth::user()->can('update', $this->route('team'));
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:200',
            'logo'        => 'nullable|image|max:2048',
            'remove_logo' => 'boolean',
        ];
    }
}
