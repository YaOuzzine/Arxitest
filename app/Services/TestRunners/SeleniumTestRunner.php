<?php

namespace App\Services\TestRunners;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\TestExecution;
use App\Models\Container;

class SeleniumTestRunner
{
    /**
     * Run a Selenium test in a Docker container
     *
     * @param TestExecution $execution The test execution record
     * @return array Result data including success status and output
     */
    public function runTest(TestExecution $execution)
    {
        $testScript = $execution->testScript;
        $environmentConfig = $execution->environment->configuration ?? [];

        // Create a unique container name for this execution
        $containerName = 'selenium_test_' . Str::uuid();

        // Create a container record
        $container = Container::create([
            'execution_id' => $execution->id,
            'container_id' => $containerName,
            'status' => 'pending',
            'configuration' => [
                'framework' => 'selenium_python',
                'resources' => [
                    'cpu' => '1',
                    'memory' => '2g'
                ],
                'environment' => $environmentConfig
            ],
            'start_time' => now(),
        ]);

        // Create a temporary script file from the content
        $scriptPath = storage_path('app/temp/' . $execution->id);
        $scriptFile = $scriptPath . '/test_script.py';
        $resultsFile = $scriptPath . '/results.json';

        if (!file_exists($scriptPath)) {
            mkdir($scriptPath, 0755, true);
        }

        file_put_contents($scriptFile, $testScript->script_content);

        // Create a simple pytest.ini file for JSON output
        $pytestIni = "[pytest]\naddopts = --json-report\njson_report_file = results.json";
        file_put_contents($scriptPath . '/pytest.ini', $pytestIni);

        // Command to run the Selenium test in the Docker container
        $command = "docker run --rm --name {$containerName} "
                 . "--network=host " // To access the Selenium service
                 . "-v " . $scriptPath . ":/tests "
                 . "python:3.9 "
                 . "bash -c 'cd /tests && "
                 . "pip install selenium pytest pytest-json-report && "
                 . "python -m pytest test_script.py -v'";

        try {
            // Update container status
            $container->update(['status' => 'running']);

            // Execute the command
            Log::info("Executing Selenium test: {$command}");
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
            $results = null;
            if (file_exists($resultsFile)) {
                $results = json_decode(file_get_contents($resultsFile), true);
            }

            return [
                'success' => $success,
                'results' => $results,
                'logs' => $allOutput
            ];

        } catch (\Exception $e) {
            Log::error("Error executing Selenium test: " . $e->getMessage());

            $container->update([
                'status' => 'failed',
                'end_time' => now()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        } finally {
            // Cleanup temporary files (optional - you may want to keep them for debugging)
            // $this->cleanupFiles($scriptPath);
        }
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
        $logPath = "test-logs/selenium_{$executionId}.log";
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
            $files = glob($path . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($path);
        }
    }
}
