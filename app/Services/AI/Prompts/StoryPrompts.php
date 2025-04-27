<?php

namespace App\Services\AI\Prompts;

use App\Models\Project;

class StoryPrompts extends BasePrompt
{
    /**
     * Get the system prompt for story generation
     *
     * @param array $context Additional context
     * @return string
     */
    public static function getSystemPrompt(array $context = []): string
    {
        // Extract context
        $projectId = self::contextValue($context, 'project_id');
        $projectName = self::contextValue($context, 'project_name', 'the project');
        $projectDescription = self::contextValue($context, 'project_description', '');
        $epicName = self::contextValue($context, 'epic_name', '');
        $codeContext = self::contextValue($context, 'code', '');
        $documentContent = self::contextValue($context, 'document_content', '');
        $imageDescription = self::contextValue($context, 'image_description', '');

        // Get additional project context if ID is provided
        $projectContext = '';
        if ($projectId && empty($projectDescription)) {
            $project = Project::find($projectId);
            if ($project) {
                $projectName = $project->name;
                $projectDescription = $project->description ?? '';

                // Get epics for this project (limited number)
                $epics = $project->epics()->select('name', 'status')->limit(5)->get();
                if ($epics->isNotEmpty()) {
                    $projectContext .= "\nProject Epics:\n";
                    foreach ($epics as $epic) {
                        $projectContext .= "- {$epic->name}" . ($epic->status ? " ({$epic->status})" : "") . "\n";
                    }
                }

                // Get recent stories for this project (limited number)
                $stories = $project->stories()->select('title', 'source')->latest()->limit(5)->get();
                if ($stories->isNotEmpty()) {
                    $projectContext .= "\nRecent Stories:\n";
                    foreach ($stories as $story) {
                        $projectContext .= "- {$story->title}\n";
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

        // Include document content if provided (truncated)
        $docContext = '';
        if (!empty($documentContent)) {
            $truncatedContent = self::truncate($documentContent, 2000);
            $docContext = "\nDocument Context:\n{$truncatedContent}\n";
        }

        // Include image description if provided
        $imageContext = '';
        if (!empty($imageDescription)) {
            $imageContext = "\nImage Description:\n{$imageDescription}\n";
        }

        // Combine all context (only include if available to save tokens)
        $combinedContext = "";
        if (!empty($projectDescription)) {
            $combinedContext .= "Project Description: " . self::truncate($projectDescription, 500) . "\n";
        }
        if (!empty($projectContext)) {
            $combinedContext .= $projectContext;
        }
        if (!empty($epicName)) {
            $combinedContext .= "Epic: {$epicName}\n";
        }
        if (!empty($codeSnippets)) {
            $combinedContext .= $codeSnippets;
        }
        if (!empty($docContext)) {
            $combinedContext .= $docContext;
        }
        if (!empty($imageContext)) {
            $combinedContext .= $imageContext;
        }

        return <<<PROMPT
You are an AI assistant designed to generate well-structured user stories for software development in a JSON format.
You are generating a story for project: "{$projectName}".

{$combinedContext}

The user will provide requirements or a description of functionality needed.
Your task is to generate a JSON object containing the following keys:
- "title": A concise, descriptive title for the story (max 100 chars)
- "description": A user story in the format "As a [user role], I want [goal] so that [reason]"
- "acceptance_criteria": An array of specific conditions that must be met for the story to be considered complete
- "priority": One of "low", "medium", or "high" depending on business value and urgency
- "tags": An array of relevant tags/keywords for categorization

Example output format:
{
  "title": "User Login System",
  "description": "As a registered user, I want to log in to the application using my email and password so that I can access my personal account.",
  "acceptance_criteria": [
    "User can enter email and password on the login screen",
    "System validates credentials against stored user data",
    "User is redirected to dashboard on successful login",
    "User sees appropriate error message on failed login",
    "Password field masks characters as they are typed"
  ],
  "priority": "high",
  "tags": ["authentication", "user-account", "security"]
}

Ensure the output is a valid JSON object with no additional text or explanations.
Focus on creating clear, actionable stories that provide value to end users.
PROMPT;
    }
}
