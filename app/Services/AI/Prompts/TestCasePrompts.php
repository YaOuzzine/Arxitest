<?php

namespace App\Services\AI\Prompts;

use App\Models\Project;
use App\Models\Story;
use App\Models\TestCase;
use App\Models\TestSuite;

class TestCasePrompts extends BasePrompt
{
    /**
     * Get the system prompt for test case generation
     *
     * @param array $context Additional context
     * @return string
     */
    public static function getSystemPrompt(array $context = []): string
    {
        // Extract essential context
        $projectId = self::contextValue($context, 'project_id');
        $projectName = self::contextValue($context, 'project_name', 'the project');
        $storyId = self::contextValue($context, 'story_id');
        $storyTitle = self::contextValue($context, 'story_title');
        $storyDescription = self::contextValue($context, 'story_description');
        $suiteId = self::contextValue($context, 'suite_id');
        $suiteName = self::contextValue($context, 'suite_name');
        $codeContext = self::contextValue($context, 'code', '');
        $uiDescription = self::contextValue($context, 'ui_description', '');

        // Load additional context from database if IDs are provided
        $additionalContext = '';

        // Load story details if only ID is provided
        if ($storyId && (empty($storyTitle) || empty($storyDescription))) {
            $story = Story::find($storyId);
            if ($story) {
                $storyTitle = $story->title;
                $storyDescription = $story->description;

                // Get project from story if not already provided
                if (empty($projectId) && $story->project) {
                    $projectId = $story->project_id;
                    $projectName = $story->project->name;
                }
            }
        }

        // Load suite details if only ID is provided
        if ($suiteId && empty($suiteName)) {
            $suite = TestSuite::find($suiteId);
            if ($suite) {
                $suiteName = $suite->name;
                $suiteDescription = $suite->description;
                $additionalContext .= "\nTest Suite: {$suiteName}\n";
                if (!empty($suiteDescription)) {
                    $additionalContext .= "Suite Description: " . self::truncate($suiteDescription, 300) . "\n";
                }

                // Get existing test cases in this suite (for context)
                $existingTestCases = $suite->testCases()->select('title', 'status', 'priority')->limit(5)->get();
                if ($existingTestCases->isNotEmpty()) {
                    $additionalContext .= "\nExisting Test Cases in Suite:\n";
                    foreach ($existingTestCases as $testCase) {
                        $additionalContext .= "- {$testCase->title} (Priority: {$testCase->priority}, Status: {$testCase->status})\n";
                    }
                }
            }
        }

        // Load project details if only ID is provided
        if ($projectId && !$projectName) {
            $project = Project::find($projectId);
            if ($project) {
                $projectName = $project->name;

                // Get default settings from project
                $defaultSettings = $project->settings ?? [];
                if (!empty($defaultSettings)) {
                    $additionalContext .= "\nProject Default Settings:\n";
                    if (isset($defaultSettings['default_priority'])) {
                        $additionalContext .= "- Default Priority: {$defaultSettings['default_priority']}\n";
                    }
                    if (isset($defaultSettings['default_framework'])) {
                        $additionalContext .= "- Default Test Framework: {$defaultSettings['default_framework']}\n";
                    }
                }
            }
        }

        // Format story details
        $storyContext = '';
        if (!empty($storyTitle)) {
            $storyContext .= "Story: {$storyTitle}\n";
            if (!empty($storyDescription)) {
                $storyContext .= "Story Description: " . self::truncate($storyDescription, 500) . "\n";
            }
        }

        // Include relevant code snippets if provided
        $codeSnippets = '';
        if (!empty($codeContext)) {
            $relevantCode = self::extractRelevantCode($codeContext);
            $codeSnippets = "\nRelevant Code Context:\n```\n{$relevantCode}\n```\n";
        }

        // Include UI/UX description if provided
        $uiContext = '';
        if (!empty($uiDescription)) {
            $uiContext = "\nUI/UX Context:\n{$uiDescription}\n";
        }

        // Combine all context
        $combinedContext = $storyContext . $additionalContext . $codeSnippets . $uiContext;
        if (!empty($combinedContext)) {
            $combinedContext = "Context Information:\n" . $combinedContext;
        }

        return <<<PROMPT
You are an AI assistant designed to generate detailed test case specifications in JSON format based on user requirements.
You are helping create a test case for project: "{$projectName}".

{$combinedContext}

The user will provide requirements for a feature, functionality, or scenario to test.
Your task is to generate a JSON object containing the following keys:
- "title": A concise, descriptive title for the test case (max 100 chars)
- "description": A more detailed explanation of what the test case verifies (max 255 chars)
- "steps": An array of strings, each representing a single step in the test case procedure
- "expected_results": A detailed description of what should happen when the test is executed correctly
- "priority": One of "low", "medium", or "high" depending on criticality
- "status": "draft" (always use draft for new test cases)
- "tags": An array of relevant tags/keywords for categorization

Example output format:
{
  "title": "Verify User Login with Valid Credentials",
  "description": "Tests that a registered user can log in successfully with valid username and password",
  "steps": [
    "Navigate to login page",
    "Enter valid username",
    "Enter valid password",
    "Click on login button"
  ],
  "expected_results": "User should be logged in successfully and redirected to the dashboard page. The username should be displayed in the header.",
  "priority": "high",
  "status": "draft",
  "tags": ["login", "authentication", "positive-test"]
}

Ensure the output is a valid JSON object with no additional text or explanations.
Make the steps clear, concise, and executable by a human tester.
PROMPT;
    }
}
