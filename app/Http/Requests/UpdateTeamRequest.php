<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Current user must be an owner or admin of the team
        $team = $this->route('team');
        return $this->user()->can('update', $team);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'logo' => 'nullable|image|max:2048', // 2MB max
            'remove_logo' => 'nullable|boolean',
        ];
    }
}
