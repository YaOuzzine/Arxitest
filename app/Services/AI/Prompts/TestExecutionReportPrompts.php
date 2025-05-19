<?php

namespace App\Services\AI\Prompts;

class TestExecutionReportPrompts extends BasePrompt
{
    /**
     * Get the system prompt for test execution report generation
     *
     * @param array $context Additional context
     * @return string
     */
    public static function getSystemPrompt(array $context = []): string
    {
        // Extract context
        $executionId = self::contextValue($context, 'execution_id', '');
        $testCaseTitle = self::contextValue($context, 'test_case_title', '');
        // Direct access for array type
        $testCaseSteps = $context['test_case_steps'] ?? [];
        $testCaseExpectedResults = self::contextValue($context, 'test_case_expected_results', '');
        $scriptName = self::contextValue($context, 'script_name', '');
        $environmentName = self::contextValue($context, 'environment_name', '');
        $executionStatus = self::contextValue($context, 'execution_status', '');
        $executionDuration = self::contextValue($context, 'execution_duration', '');
        $executionLogs = self::contextValue($context, 'execution_logs', '');

        // Format test case steps
        $stepsText = '';
        if (is_array($testCaseSteps)) {
            foreach ($testCaseSteps as $index => $step) {
                $stepsText .= ($index + 1) . ". $step\n";
            }
        } else {
            $stepsText = (string)$testCaseSteps; // Convert to string if somehow not an array
        }

        return <<<PROMPT
You are an AI assistant specialized in analyzing test execution results and generating clear, structured reports.

Please analyze the following test execution logs and generate a comprehensive report with the following sections:

1. **Execution Summary**
   - Test Case: {$testCaseTitle}
   - Script: {$scriptName}
   - Environment: {$environmentName}
   - Status: {$executionStatus}
   - Duration: {$executionDuration}

2. **Test Case Comparison**
   - Original Test Case Steps:
{$stepsText}
   - Expected Results: {$testCaseExpectedResults}
   - Actual Steps Executed (extract from logs)
   - Actual Results (extract from logs)

3. **Test Data Analysis**
   - Data used in the test (extract from logs)
   - Any data-related issues identified

4. **Issues and Recommendations**
   - Any errors or warnings detected in the logs
   - Possible causes for failures (if any)
   - Recommendations for fixing issues

5. **Success Criteria Evaluation**
   - Which test case steps passed/failed
   - Whether the test met the expected results
   - Overall test execution quality assessment

Here are the complete execution logs:

{$executionLogs}

Return your analysis in JSON format with the following structure:
{
  "summary": {
    "title": "Execution Summary",
    "content": "Formatted execution summary..."
  },
  "comparison": {
    "title": "Test Case Comparison",
    "content": "Formatted comparison analysis..."
  },
  "data_analysis": {
    "title": "Test Data Analysis",
    "content": "Formatted data analysis..."
  },
  "issues": {
    "title": "Issues and Recommendations",
    "content": "Formatted issues and recommendations..."
  },
  "evaluation": {
    "title": "Success Criteria Evaluation",
    "content": "Formatted evaluation..."
  },
  "status": "overall_status", // "passed", "failed", "inconclusive"
  "confidence": 0.85 // number between 0-1 indicating confidence in analysis
}

Focus on extracting meaningful insights from the logs rather than just summarizing them. Identify patterns in failures, data issues, and potential optimizations.
PROMPT;
    }
}
