<?php

namespace App\Services;

use App\Models\Project;
use App\Models\TestCase;
use App\Models\TestData;
use App\Models\TestScript;
use App\Models\TestSuite;
use Illuminate\Support\Facades\Log;

class RelationshipValidationService
{
    /**
     * Validate the relationship between project and test suite.
     */
    public function validateProjectSuiteRelationship(Project $project, ?TestSuite $testSuite): bool
    {
        if ($testSuite && $testSuite->project_id !== $project->id) {
            Log::warning('Invalid project-suite relationship', [
                'project_id' => $project->id,
                'suite_id' => $testSuite->id,
                'suite_project_id' => $testSuite->project_id,
            ]);
            throw new \Exception('Test suite not found in this project.');
        }

        return true;
    }

    /**
     * Validate the relationship between project and test case.
     */
    public function validateProjectTestCaseRelationship(Project $project, TestCase $testCase): bool
    {
        // First check if the test case belongs to a suite in this project
        $suite = $testCase->testSuite;
        $suiteMatches = $suite && $suite->project_id === $project->id;

        // Next check if the test case belongs to a story in this project
        $story = $testCase->story;
        $storyMatches = $story && $story->project_id === $project->id;

        // Test case belongs to the project if either relationship is valid
        if (!$suiteMatches && !$storyMatches) {
            Log::warning('Invalid project-test case relationship', [
                'project_id' => $project->id,
                'test_case_id' => $testCase->id,
                'suite_id' => $suite->id ?? 'null',
                'suite_project_id' => $suite->project_id ?? 'null',
                'story_id' => $story->id ?? 'null',
                'story_project_id' => $story->project_id ?? 'null',
            ]);
            throw new \Exception('Test case not found in this project.');
        }

        return true;
    }

    /**
     * Validate the relationship between test case and test data.
     */
    public function validateTestCaseTestDataRelationship(TestCase $testCase, TestData $testData): bool
    {
        if (!$testCase->testData()->where('test_data.id', $testData->id)->exists()) {
            Log::warning('Invalid test case-test data relationship', [
                'test_case_id' => $testCase->id,
                'test_data_id' => $testData->id,
            ]);
            throw new \Exception('Test data not found for this test case.');
        }

        return true;
    }

    /**
     * Validate the relationship between test case and test script.
     */
    public function validateTestCaseTestScriptRelationship(TestCase $testCase, TestScript $testScript): bool
    {
        if ($testScript->test_case_id !== $testCase->id) {
            Log::warning('Invalid test case-test script relationship', [
                'test_case_id' => $testCase->id,
                'test_script_id' => $testScript->id,
                'script_test_case_id' => $testScript->test_case_id,
            ]);
            throw new \Exception('Test script not found in this test case.');
        }

        return true;
    }

    /**
     * Validate relationships between project, test case, and optionally test script.
     */
    public function validateRelationships(Project $project, TestCase $testCase, ?TestScript $testScript = null): bool
    {
        // Validate project-test case relationship
        $this->validateProjectTestCaseRelationship($project, $testCase);

        // If test script is provided, validate test case-test script relationship
        if ($testScript) {
            $this->validateTestCaseTestScriptRelationship($testCase, $testScript);
        }

        return true;
    }

    /**
     * Validate relationships for test data operations.
     */
    public function validateTestDataRelationships(Project $project, TestCase $testCase, ?TestData $testData = null): bool
    {
        // Validate project-test case relationship
        $this->validateProjectTestCaseRelationship($project, $testCase);

        // If test data is provided, validate test case-test data relationship
        if ($testData) {
            $this->validateTestCaseTestDataRelationship($testCase, $testData);
        }

        return true;
    }
}
