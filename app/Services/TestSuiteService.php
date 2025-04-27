<?php

namespace App\Services;

use App\Models\Project;
use App\Models\TestCase;
use App\Models\TestSuite;
use Illuminate\Support\Str;

class TestSuiteService
{
    /**
     * Create a new TestSuite under a given project.
     */
    public function create(Project $project, array $data): TestSuite
    {
        $suite = new TestSuite();
        $suite->project_id  = $project->id;
        $suite->name        = $data['name'];
        $suite->description = $data['description'] ?? null;
        $suite->settings    = [
            'default_priority' => $data['settings']['default_priority'],
            'execution_mode'   => $data['settings']['execution_mode'] ?? 'sequential',
        ];
        $suite->save();

        return $suite;
    }

    /**
     * Update an existing TestSuite.
     */
    public function update(TestSuite $suite, array $data): TestSuite
    {
        $suite->name        = $data['name'];
        $suite->description = $data['description'] ?? null;

        $settings = $suite->settings ?? [];
        $settings['default_priority'] = $data['settings']['default_priority'];
        $settings['execution_mode']   = $data['settings']['execution_mode']
            ?? $settings['execution_mode']
            ?? 'sequential';

        $suite->settings = $settings;
        $suite->save();

        return $suite;
    }


    /**
     * Delete a TestSuite.
     */
    public function delete(TestSuite $suite): void
    {
        $suite->delete();
    }


    /**
     * Add existing test cases to a test suite.
     *
     * @param TestSuite $testSuite The test suite to add cases to
     * @param array $testCaseIds Array of test case IDs to add
     * @return array Information about the operation
     */
    public function addTestCasesToSuite(TestSuite $testSuite, array $testCaseIds): array
    {
        $project = $testSuite->project;
        $added = 0;
        $errors = [];

        foreach ($testCaseIds as $testCaseId) {
            try {
                $testCase = TestCase::findOrFail($testCaseId);

                // Ensure the test case belongs to this project via story
                if ($testCase->story && $testCase->story->project_id !== $project->id) {
                    $errors[] = "Test case '{$testCase->title}' belongs to a different project.";
                    continue;
                }

                // Update the test case to belong to this suite
                $testCase->suite_id = $testSuite->id;
                $testCase->save();

                $added++;
            } catch (\Exception $e) {
                $errors[] = "Failed to add test case ID {$testCaseId}: " . $e->getMessage();
            }
        }

        return [
            'success' => $added > 0,
            'added' => $added,
            'errors' => $errors,
            'message' => $added > 0
                ? "{$added} test " . Str::plural('case', $added) . " added to suite."
                : "No test cases were added."
        ];
    }
}
