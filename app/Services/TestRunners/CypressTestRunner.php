<?php

namespace App\Services\TestRunners;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\TestExecution;
use App\Models\Container;

class CypressTestRunner
{
    /**
     * Run a Cypress test in a Docker container
     *
     * @param TestExecution $execution The test execution record
     * @return array Result data including success status and output
     */
    public function runTest(TestExecution $execution)
    {
        $testScript = $execution->testScript;
        $environmentConfig = $execution->environment->configuration ?? [];

        // Create a unique container name for this execution
        $containerName = 'cypress_test_' . Str::uuid();

        // Create a container record
        $container = Container::create([
            'execution_id' => $execution->id,
            'container_id' => $containerName,
            'status' => 'pending',
            'configuration' => [
                'framework' => 'cypress',
                'resources' => [
                    'cpu' => '1',
                    'memory' => '2g'
                ],
                'environment' => $environmentConfig
            ],
            'start_time' => now(),
        ]);

        // Create a temporary test directory structure
        $tempDir = storage_path('app/temp/cypress_' . $execution->id);
        $specDir = $tempDir . '/cypress/e2e';
        $resultsDir = $tempDir . '/results';

        if (!file_exists($specDir)) {
            mkdir($specDir, 0755, true);
        }

        if (!file_exists($resultsDir)) {
            mkdir($resultsDir, 0755, true);
        }

        // Write the test script to file
        $specFile = $specDir . '/test_spec.js';
        file_put_contents($specFile, $testScript->script_content);

        // Create cypress.config.js
        $configContent = "module.exports = {
  reporter: 'junit',
  reporterOptions: {
    mochaFile: 'results/results.xml',
    toConsole: true
  },
  video: true,
  screenshotOnRunFailure: true,
  e2e: {
    setupNodeEvents(on, config) {
      return config
    },
  },
};";
        file_put_contents($tempDir . '/cypress.config.js', $configContent);

        // Command to run Cypress in the Docker container
        $command = "docker run --rm --name {$containerName} "
                 . "--network=host " // To access the browser
                 . "-v {$tempDir}:/e2e "
                 . "cypress/included:12.8.1 "
                 . "--project /e2e";

        try {
            // Update container status
            $container->update(['status' => 'running']);

            // Execute the command
            Log::info("Executing Cypress test: {$command}");
            $process = Process::timeout(300)->run($command);

            // Get output
            $output = $process->output();
            $errorOutput = $process->errorOutput();
            $allOutput = $output . "\n" . $errorOutput;

            // Process exit code
            $exitCode = $process->exitCode();
            $success = $exitCode === 0;

            // Store logs
            $logsKey = $this->storeTestLogs($allOutput, $execution->id);

            // Update container status
            $container->update([
                'status' => $success ? 'completed' : 'failed',
                'end_time' => now(),
                's3_logs_key' => $logsKey
            ]);

            // Process and store test results
            $resultsData = $this->processResults($tempDir, $execution->id);

            return [
                'success' => $success,
                'results' => $resultsData,
                'logs' => $allOutput
            ];

        } catch (\Exception $e) {
            Log::error("Error executing Cypress test: " . $e->getMessage());

            $container->update([
                'status' => 'failed',
                'end_time' => now()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        } finally {
            // Cleanup is optional - you may want to keep files for debugging
            // $this->cleanupFiles($tempDir);
        }
    }

    /**
     * Process and store test results
     *
     * @param string $tempDir Path to the temporary directory
     * @param string $executionId The test execution ID
     * @return array|null Processed results data
     */
    private function processResults($tempDir, $executionId)
    {
        $resultsFile = $tempDir . '/results/results.xml';
        $screenshotsDir = $tempDir . '/cypress/screenshots';
        $videosDir = $tempDir . '/cypress/videos';

        $resultsData = [
            'summary' => [
                'total' => 0,
                'passed' => 0,
                'failed' => 0,
                'skipped' => 0,
                'duration' => 0
            ],
            'tests' => [],
            'artifacts' => []
        ];

        // Parse JUnit XML report if available
        if (file_exists($resultsFile)) {
            $xml = simplexml_load_file($resultsFile);
            if ($xml) {
                // Extract test suite info
                foreach ($xml->testsuite as $suite) {
                    $suiteName = (string)$suite['name'];
                    $resultsData['summary']['total'] += (int)$suite['tests'];
                    $resultsData['summary']['passed'] += (int)$suite['tests'] - (int)$suite['failures'] - (int)$suite['skipped'];
                    $resultsData['summary']['failed'] += (int)$suite['failures'];
                    $resultsData['summary']['skipped'] += (int)$suite['skipped'];
                    $resultsData['summary']['duration'] += (float)$suite['time'];

                    // Extract test case info
                    foreach ($suite->testcase as $case) {
                        $testCase = [
                            'name' => (string)$case['name'],
                            'classname' => (string)$case['classname'],
                            'duration' => (float)$case['time'],
                            'status' => 'passed'
                        ];

                        if (isset($case->failure)) {
                            $testCase['status'] = 'failed';
                            $testCase['failure'] = [
                                'message' => (string)$case->failure['message'],
                                'type' => (string)$case->failure['type'],
                                'content' => (string)$case->failure
                            ];
                        } elseif (isset($case->skipped)) {
                            $testCase['status'] = 'skipped';
                        }

                        $resultsData['tests'][] = $testCase;
                    }
                }
            }
        }

        // Store screenshots
        if (is_dir($screenshotsDir)) {
            $screenshots = glob($screenshotsDir . '/**/*.png');
            foreach ($screenshots as $screenshot) {
                $filename = basename($screenshot);
                $targetPath = "test-artifacts/{$executionId}/screenshots/{$filename}";
                Storage::put($targetPath, file_get_contents($screenshot));
                $resultsData['artifacts'][] = [
                    'type' => 'screenshot',
                    'path' => $targetPath,
                    'name' => $filename
                ];
            }
        }

        // Store videos
        if (is_dir($videosDir)) {
            $videos = glob($videosDir . '/*.mp4');
            foreach ($videos as $video) {
                $filename = basename($video);
                $targetPath = "test-artifacts/{$executionId}/videos/{$filename}";
                Storage::put($targetPath, file_get_contents($video));
                $resultsData['artifacts'][] = [
                    'type' => 'video',
                    'path' => $targetPath,
                    'name' => $filename
                ];
            }
        }

        // Store the processed results
        $resultsPath = "test-results/cypress_{$executionId}.json";
        Storage::put($resultsPath, json_encode($resultsData, JSON_PRETTY_PRINT));

        return $resultsData;
    }

    /**
     * Store test logs in storage
     *
     * @param string $output The combined output
     * @param string $executionId The test execution ID
     * @return string The storage path
     */
    private function storeTestLogs($output, $executionId)
    {
        $logPath = "test-logs/cypress_{$executionId}.log";
        Storage::put($logPath, $output);
        return $logPath;
    }

    /**
     * Clean up temporary files
     *
     * @param string $path The directory to clean
     */
    private function cleanupFiles($path)
    {
        if (is_dir($path)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }

            rmdir($path);
        }
    }
}
