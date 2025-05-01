<?php

namespace App\Services\AI\Prompts;

use App\Models\TestCase;
use App\Models\TestScript;

class TestDataPrompts extends BasePrompt
{
    /**
     * Get the system prompt for test data generation
     *
     * @param array $context Additional context
     * @return string
     */
    public static function getSystemPrompt(array $context = []): string
    {
        // Extract context
        $testCaseId = self::contextValue($context, 'test_case_id');
        $scriptId = self::contextValue($context, 'script_id');
        $format = self::contextValue($context, 'format', 'json');
        $testCaseTitle = self::contextValue($context, 'test_case_title', '');
        $testCaseSteps = self::contextValue($context, 'test_case_steps', '');
        $testCaseResults = self::contextValue($context, 'test_case_expected_results', '');
        $scriptContent = self::contextValue($context, 'script_content', '');

        // Load test case details if only ID is provided
        if ($testCaseId && (empty($testCaseTitle) || empty($testCaseSteps) || empty($testCaseResults))) {
            $testCase = TestCase::find($testCaseId);
            if ($testCase) {
                $testCaseTitle = $testCase->title;
                // Check if steps is already an array or needs to be decoded
                if (is_array($testCase->steps)) {
                    $testCaseSteps = $testCase->steps;
                } else {
                    $testCaseSteps = json_decode($testCase->steps, true) ?? [];
                }
                $testCaseResults = $testCase->expected_results;
            }
        }

        // Load script content if only ID is provided
        if ($scriptId && empty($scriptContent)) {
            $script = TestScript::find($scriptId);
            if ($script) {
                $scriptContent = $script->script_content;
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

        // Include relevant script snippets if provided
        $scriptSnippets = '';
        if (!empty($scriptContent)) {
            $relevantScript = self::extractRelevantCode($scriptContent);
            $scriptSnippets = "\nTest Script Context:\n```\n{$relevantScript}\n```\n";
        }

        // Format-specific guidance
        $formatGuidance = '';
        switch ($format) {
            case 'json':
                $formatGuidance = <<<JSON
Create well-structured, valid JSON test data:
- Ensure proper JSON syntax with quoted keys and values
- Use arrays and nested objects as appropriate
- Include both valid and invalid test scenarios if applicable
- Provide diverse data values to cover different test cases
JSON;
                break;
            case 'csv':
                $formatGuidance = <<<CSV
Create well-structured CSV test data:
- Include a header row with field names
- Format data with proper comma separation
- Escape commas within fields with quotes if needed
- Provide several rows to cover different test scenarios
CSV;
                break;
            case 'xml':
                $formatGuidance = <<<XML
Create well-structured XML test data:
- Include XML declaration
- Use proper nesting and element structure
- Include both attributes and element values as appropriate
- Create a valid XML document that could be parsed by an XML reader
XML;
                break;
            case 'plain':
                $formatGuidance = <<<PLAIN
Create plain text test data:
- Use a logical structure with clear organization
- Include headers or section markers if appropriate
- Format in a way that would be easy for a human to read
- Consider whitespace and line breaks for readability
PLAIN;
                break;
            default:
                $formatGuidance = "Create well-structured test data in {$format} format.";
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
        $combinedContext = $testCaseContext . $scriptSnippets;
        if (!empty($combinedContext)) {
            $combinedContext = "Context Information:\n" . $combinedContext;
        }

        return <<<PROMPT
You are an AI assistant that generates test data for software testing.
Create realistic test data in {$format} format based on the following information:

{$combinedContext}

{$formatGuidance}

Your response should ONLY contain the test data in the requested format, with no explanations or markdown formatting.
PROMPT;
    }
}
