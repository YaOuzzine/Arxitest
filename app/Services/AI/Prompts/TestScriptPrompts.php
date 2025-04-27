<?php

namespace App\Services\AI\Prompts;

use App\Models\TestCase;
use App\Models\Project;

class TestScriptPrompts extends BasePrompt
{
    /**
     * Get the system prompt for test script generation
     *
     * @param array $context Additional context
     * @return string
     */
    public static function getSystemPrompt(array $context = []): string
    {
        // Extract context
        $testCaseId = self::contextValue($context, 'test_case_id');
        $frameworkType = self::contextValue($context, 'framework_type', 'selenium-python');
        $codeContext = self::contextValue($context, 'code', '');
        $testCaseTitle = self::contextValue($context, 'test_case_title', '');
        $testCaseSteps = self::contextValue($context, 'test_case_steps', '');
        $testCaseResults = self::contextValue($context, 'test_case_expected_results', '');

        // Load test case details if only ID is provided
        if ($testCaseId && (empty($testCaseTitle) || empty($testCaseSteps) || empty($testCaseResults))) {
            $testCase = TestCase::find($testCaseId);
            if ($testCase) {
                $testCaseTitle = $testCase->title;
                $testCaseSteps = $testCase->steps;
                $testCaseResults = $testCase->expected_results;

                // Get project information via suite
                $projectName = '';
                $projectSettings = [];
                if ($testCase->testSuite && $testCase->testSuite->project) {
                    $project = $testCase->testSuite->project;
                    $projectName = $project->name;
                    $projectSettings = $project->settings ?? [];
                }
            }
        }

        // Format test case steps
        $formattedSteps = '';
        if (is_array($testCaseSteps)) {
            $formattedSteps = "Steps:\n";
            foreach ($testCaseSteps as $index => $step) {
                $formattedSteps .= ($index + 1) . ". {$step}\n";
            }
        } else {
            $formattedSteps = "Steps: {$testCaseSteps}\n";
        }

        // Include relevant code snippets if provided
        $codeSnippets = '';
        if (!empty($codeContext)) {
            $relevantCode = self::extractRelevantCode($codeContext);
            $codeSnippets = "\nRelevant Code/API Context:\n```\n{$relevantCode}\n```\n";
        }

        // Framework-specific guidance
        $frameworkGuidance = '';
        switch ($frameworkType) {
            case 'selenium-python':
                $frameworkGuidance = <<<SELENIUM
Use Selenium WebDriver with Python:
- Include necessary imports (webdriver, By, WebDriverWait, expected_conditions)
- Create proper setup and teardown methods
- Use explicit waits for element interactions
- Structure as a proper unittest or pytest class
- Include comments for clarity
- Handle any required authentication or session management
SELENIUM;
                break;
            case 'cypress':
                $frameworkGuidance = <<<CYPRESS
Use Cypress JavaScript syntax:
- Include proper imports and describe/it structure
- Use Cypress best practices (cy.get, .should, etc.)
- Handle page loads and asynchronous content properly
- Use beforeEach for setup operations if needed
- Consider using custom commands for repeated actions
CYPRESS;
                break;
            default:
                $frameworkGuidance = "Use best practices for the {$frameworkType} framework.";
                break;
        }

        // Test case context
        $testCaseContext = '';
        if (!empty($testCaseTitle)) {
            $testCaseContext .= "Test Case: {$testCaseTitle}\n";
            $testCaseContext .= $formattedSteps;
            $testCaseContext .= "Expected Results: {$testCaseResults}\n";
        }

        // Combine all context
        $combinedContext = $testCaseContext . $codeSnippets;
        if (!empty($combinedContext)) {
            $combinedContext = "Context Information:\n" . $combinedContext;
        }

        return <<<PROMPT
You are an AI assistant that specializes in generating test automation scripts.
You'll create a complete {$frameworkType} test script based on the provided information.

{$combinedContext}

{$frameworkGuidance}

Create a complete, working test script - do not abbreviate or omit parts of the code.
Your response should be ONLY the code for the test script, without explanations, markdown formatting, or code block markers.
PROMPT;
    }
}
