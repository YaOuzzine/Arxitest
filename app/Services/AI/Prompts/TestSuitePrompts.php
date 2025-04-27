<?php

namespace App\Services\AI\Prompts;

use App\Models\Project;
use App\Models\TestSuite;

class TestSuitePrompts extends BasePrompt
{
    /**
     * Get the system prompt for test suite generation
     *
     * @param array $context Additional context
     * @return string
     */
    public static function getSystemPrompt(array $context = []): string
    {
        // Extract context
        $projectId = self::contextValue($context, 'project_id');
        $projectName = self::contextValue($context, 'project_name', 'the project');
        $codeContext = self::contextValue($context, 'code', '');

        // Load additional context from database if needed
        $projectContext = '';
        if ($projectId) {
            $project = Project::find($projectId);
            if ($project) {
                $projectName = $project->name;
                $projectDescription = $project->description ?? '';

                if (!empty($projectDescription)) {
                    $projectContext .= "Project Description: " . self::truncate($projectDescription, 300) . "\n";
                }

                // Get existing test suites (limited number)
                $existingSuites = $project->testSuites()->select('name', 'description')->limit(5)->get();
                if ($existingSuites->isNotEmpty()) {
                    $projectContext .= "\nExisting Test Suites:\n";
                    foreach ($existingSuites as $suite) {
                        $projectContext .= "- {$suite->name}\n";
                    }
                }

                // Get default settings
                $defaultSettings = $project->settings ?? [];
                if (!empty($defaultSettings)) {
                    $projectContext .= "\nProject Default Settings:\n";
                    if (isset($defaultSettings['default_priority'])) {
                        $projectContext .= "- Default Priority: {$defaultSettings['default_priority']}\n";
                    }
                    if (isset($defaultSettings['default_framework'])) {
                        $projectContext .= "- Default Test Framework: {$defaultSettings['default_framework']}\n";
                    }
                }
            }
        }

        // Include relevant code snippets if provided
        $codeSnippets = '';
        if (!empty($codeContext)) {
            $relevantCode = self::extractRelevantCode($codeContext);
            $codeSnippets = "\nRelevant Code Context:\n```\n{$relevantCode}\n```\n";
        }

        // Combine all context
        $combinedContext = $projectContext . $codeSnippets;
        if (!empty($combinedContext)) {
            $combinedContext = "Context Information:\n" . $combinedContext;
        }

        return <<<PROMPT
You are an AI assistant designed to generate Test Suite details in a specific JSON format based on user requirements.
You are generating a Test Suite for project: "{$projectName}".

{$combinedContext}

The user will provide requirements for a software feature or component.
Your task is to generate a JSON object containing ONLY the following keys: "name", "description", and "settings".
- "name": A concise, descriptive name for the Test Suite (max 100 chars).
- "description": A brief summary of what the Test Suite covers (max 255 chars).
- "settings": A JSON object with AT LEAST the key "default_priority" set to one of "low", "medium", or "high". You can optionally add other relevant settings like "execution_mode" ("sequential" or "parallel") if implied by the requirements.

Example Output JSON:
{
  "name": "User Authentication Suite",
  "description": "Tests for user registration, login, password recovery, and session management functionality.",
  "settings": {
    "default_priority": "high",
    "execution_mode": "sequential"
  }
}

Ensure the output is **strictly** a single JSON object with no extra text, explanations, or markdown formatting.
PROMPT;
    }
}
