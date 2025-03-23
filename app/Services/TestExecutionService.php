<?php

namespace App\Services;

use App\Models\TestExecution;
use App\Models\ExecutionStatus;
use App\Services\TestRunners\SeleniumTestRunner;
use App\Services\TestRunners\CypressTestRunner;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TestExecutionService
{
    /**
     * Execute a test script
     *
     * @param TestExecution $execution The test execution record
     * @return bool Success status
     */
    public function executeTest(TestExecution $execution)
    {
        $testScript = $execution->testScript;

        if (!$testScript) {
            Log::error("Test execution {$execution->id} has no associated test script");
            $this->updateExecutionStatus($execution, 'Failed', "No test script found");
            return false;
        }

        // Determine which runner to use based on framework type
        $runner = $this->getRunnerForFramework($testScript->framework_type);

        if (!$runner) {
            Log::error("Unknown framework type: {$testScript->framework_type}");
            $this->updateExecutionStatus($execution, 'Failed', "Unknown framework type");
            return false;
        }

        try {
            // Update execution status to Running
            $this->updateExecutionStatus($execution, 'Running');

            // Run the test
            $result = $runner->runTest($execution);

            // Process the results
            if ($result['success']) {
                // Store results data
                $resultsKey = $this->storeTestResults($result['results'], $execution->id);

                // Update execution with success status and results location
                $this->updateExecutionStatus($execution, 'Passed', null, $resultsKey);
            } else {
                $errorMessage = isset($result['error']) ? $result['error'] : "Test execution failed";

                // Store results data anyway (even for failures)
                $resultsKey = isset($result['results']) ?
                    $this->storeTestResults($result['results'], $execution->id) : null;

                // Update execution with failure status
                $this->updateExecutionStatus($execution, 'Failed', $errorMessage, $resultsKey);
            }

            return $result['success'];

        } catch (\Exception $e) {
            Log::error("Error during test execution {$execution->id}: " . $e->getMessage());
            $this->updateExecutionStatus($execution, 'Failed', $e->getMessage());
            return false;
        }
    }

    /**
     * Get the appropriate test runner for the framework type
     *
     * @param string $frameworkType The type of test framework
     * @return object|null The test runner instance
     */
    private function getRunnerForFramework($frameworkType)
    {
        switch ($frameworkType) {
            case 'selenium_python':
                return new SeleniumTestRunner();
            case 'cypress':
                return new CypressTestRunner();
            default:
                return null;
        }
    }

    /**
     * Update the execution status
     *
     * @param TestExecution $execution The test execution record
     * @param string $statusName The name of the status
     * @param string|null $errorMessage Optional error message
     * @param string|null $resultsKey Optional storage key for results
     */
    private function updateExecutionStatus(TestExecution $execution, $statusName, $errorMessage = null, $resultsKey = null)
    {
        $status = ExecutionStatus::where('name', $statusName)->first();

        if (!$status) {
            Log::error("Could not find execution status with name: {$statusName}");
            return;
        }

        $updateData = [
            'status_id' => $status->id
        ];

        if ($statusName == 'Running') {
            // For Running, set only start_time if not already set
            if (!$execution->start_time) {
                $updateData['start_time'] = now();
            }
        } else {
            // For other statuses, set the end_time
            $updateData['end_time'] = now();
        }

        if ($resultsKey) {
            $updateData['s3_results_key'] = $resultsKey;
        }

        $execution->update($updateData);

        // Store error message if present
        if ($errorMessage && $execution->containers->isNotEmpty()) {
            $container = $execution->containers->first();

            // Update container configuration with error message
            $config = $container->configuration ?? [];
            $config['error_message'] = $errorMessage;

            $container->update([
                'configuration' => $config,
                'status' => $statusName == 'Passed' ? 'completed' : 'failed'
            ]);
        }
    }

    /**
     * Store test results in storage
     *
     * @param array|null $results The test results data
     * @param string $executionId The test execution ID
     * @return string|null The storage path
     */
    private function storeTestResults($results, $executionId)
    {
        if (!$results) {
            return null;
        }

        $resultsPath = "test-results/{$executionId}.json";
        Storage::put($resultsPath, json_encode($results, JSON_PRETTY_PRINT));

        return $resultsPath;
    }
}
