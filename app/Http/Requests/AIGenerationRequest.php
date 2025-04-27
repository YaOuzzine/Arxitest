<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AIGenerationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // We'll use middleware for auth
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'prompt' => 'required|string|min:10|max:2000',
            'context' => 'nullable|array',
            'project_id' => 'nullable|uuid|exists:projects,id',
            'provider' => 'nullable|string|in:openai,claude,deepseek,gemini',
        ];
    }

    /**
     * Get context data for AI generation
     */
    public function context(): array
    {
        $context = $this->input('context', []);

        // Add project ID if present
        if ($this->has('project_id')) {
            $context['project_id'] = $this->input('project_id');

            // Load basic project data
            $project = \App\Models\Project::find($this->input('project_id'));
            if ($project) {
                $context['project_name'] = $project->name;
                $context['project_description'] = $project->description;
            }
        }

        return $context;
    }
}
