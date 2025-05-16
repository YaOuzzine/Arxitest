<?php

namespace App\Jobs;

use App\Models\Container;
use App\Models\ExecutionStatus;
use App\Models\ResourceMetric;
use App\Models\TestExecution;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RunTestExecutionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800; // 30 minutes

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The test execution instance.
     *
     * @var \App\Models\TestExecution
     */
    protected $execution;

    /**
     * Create a new job instance.
     */
    public function __construct(TestExecution $execution)
    {
        $this->execution = $execution;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('RunTestExecutionJob: Starting job execution', [
        'execution_id' => $this->execution->id,
        'script_id' => $this->execution->script_id,
        'environment_id' => $this->execution->environment_id
    ]);
        $container = null;

        try {
            // 1. Update execution status to running
            $runningStatus = ExecutionStatus::where('name', 'running')->first();
            $this->execution->status_id = $runningStatus->id;
            $this->execution->save();

            // 2. Get test script and environment details
            $script = $this->execution->testScript;
            $environment = $this->execution->environment;

            if (!$script) {
                throw new \Exception("Test script not found for execution #{$this->execution->id}");
            }

            // 3. Prepare working directory
            $workDirName = 'execution_' . $this->execution->id . '_' . Str::random(8);
            $workDir = storage_path("app/executions/{$workDirName}");
            if (!is_dir($workDir)) {
                mkdir($workDir, 0755, true);
            }

            // 4. Write test script to file
            $scriptExtension = $this->getScriptExtension($script->framework_type);
            $scriptPath = $workDir . '/your_tests' . $scriptExtension;
            file_put_contents($scriptPath, $script->script_content);

            // 5. Create container record
            $containerId = 'arxitest_' . Str::random(10);
            $container = Container::create([
                'execution_id' => $this->execution->id,
                'container_id' => $containerId,
                'status' => Container::PENDING,
                'configuration' => [
                    'framework' => $script->framework_type,
                    'environment' => $environment ? $environment->name : 'default',
                    'work_dir' => $workDir,
                ],
                'start_time' => now()
            ]);

            // 6. Prepare Docker run command
            $dockerImage = $this->getDockerImage($script->framework_type);
            $envVars = $this->getEnvironmentVars($environment);
            $envVars .= ' -e CHROME_OPTIONS="--headless --no-sandbox --disable-dev-shm-usage --disable-gpu"';

            $dockerCommand = [
                'docker run',
                "--name {$containerId}",
                '-d',
                "--label arxitest_execution_id={$this->execution->id}",
                "-v {$workDir}:/tests",
                $envVars,
                "--entrypoint python",
                $dockerImage,
                "-m",  // Use Python's module running capability
                "unittest",  // Invoke the unittest module
                "/tests/your_tests{$scriptExtension}"  // Path to your test file
            ];
            $fullCommand = implode(' ', array_filter($dockerCommand));

            // 7. Start the container
            Log::info("Running Docker command: {$fullCommand}");
            exec($fullCommand . ' 2>&1', $output, $exitCode);

            if ($exitCode !== 0) {
                throw new \Exception("Failed to start Docker container: " . implode("\n", $output));
            }

            // Container started successfully, update status
            $container->status = Container::RUNNING;
            $container->save();

            // 8. Monitor container execution
            $this->monitorContainer($container);

            // 9. Process test results
            $this->processResults($container);

            Log::info('RunTestExecutionJob: Job completed successfully');
        } catch (\Exception $e) {
            Log::error("Error executing test #{$this->execution->id}: " . $e->getMessage());

            // Set execution to failed
            $failedStatus = ExecutionStatus::where('name', 'failed')->first();
            if ($failedStatus) {
                $this->execution->status_id = $failedStatus->id;
                $this->execution->end_time = now();
                $this->execution->save();
            }

            // Update container status if it exists
            if ($container) {
                $container->status = Container::FAILED;
                $container->end_time = now();
                $container->save();

                // Try to clean up the container
                $this->cleanupContainer($container->container_id);
            }

            // Cleanup work directory
            if (isset($workDir) && is_dir($workDir)) {
                exec("rm -rf {$workDir}");
            }
        }
    }

    /**
     * Get the script file extension based on framework type.
     */
    protected function getScriptExtension(string $frameworkType): string
    {
        return match ($frameworkType) {
            'selenium-python' => '.py',
            'cypress' => '.js',
            default => '.txt'
        };
    }

    /**
     * Get the Docker image based on framework type.
     */
    protected function getDockerImage(string $frameworkType): string
    {
        return match ($frameworkType) {
            'selenium-python' => 'arxitest/selenium-python:latest',
            'cypress' => 'arxitest/cypress:latest',
            default => 'alpine:latest' // Fallback image
        };
    }

    /**
     * Get environment variables string for Docker.
     */
    protected function getEnvironmentVars($environment): string
    {
        if (!$environment || !$environment->configuration) {
            return '';
        }

        $envVars = [];
        foreach ($environment->configuration as $key => $value) {
            // Properly escape the value for shell
            $escapedValue = addslashes($value);
            $envVars[] = "-e {$key}=\"{$escapedValue}\"";
        }

        return implode(' ', $envVars);
    }

    protected function monitorContainer(Container $container): void
    {
        $containerId = $container->container_id;
        $startTime = time();
        $timeout = 1200; // 20 minutes timeout
        $checkInterval = 5; // Check every 5 seconds
        $memoryLimit = 100 * 1024 * 1024; // 100MB memory limit for PHP process
        $lastLogsHash = ''; // Track changes in logs to detect completion
        $completionPattern = '/Ran \d+ test.* in \d+\.\d+s\s+OK/'; // Pattern to detect successful test completion
        $failurePattern = '/Ran \d+ test.* in \d+\.\d+s\s+FAILED/'; // Pattern to detect failed test completion
        $noChangesCount = 0; // Counter for when logs aren't changing

        Log::info("Monitoring container {$containerId}");

        // Create a log file for this execution
        $executionLogPath = storage_path("app/executions/{$this->execution->id}/execution_log.txt");
        if (!is_dir(dirname($executionLogPath))) {
            mkdir(dirname($executionLogPath), 0755, true);
        }
        file_put_contents($executionLogPath, "Starting execution: " . date('Y-m-d H:i:s') . "\n");

        while (time() - $startTime < $timeout) {
            // Check container status
            if (memory_get_usage(true) > $memoryLimit) {
                $errorMsg = "Memory usage exceeded limit. Stopping execution to prevent crash.";
                Log::error($errorMsg);
                file_put_contents($executionLogPath, $errorMsg . "\n", FILE_APPEND);
                $this->failExecution($container, "Memory limit exceeded");
                break;
            }

            // Check if container is still running
            exec("docker inspect --format='{{.State.Status}}' {$containerId} 2>&1", $statusOutput, $statusCode);

            if ($statusCode !== 0 || !isset($statusOutput[0])) {
                $errorMsg = "Failed to get container status: " . implode("\n", $statusOutput);
                Log::error($errorMsg);
                file_put_contents($executionLogPath, $errorMsg . "\n", FILE_APPEND);
                $this->failExecution($container, "Container monitoring failed");
                break;
            }

            $containerStatus = trim($statusOutput[0]);

            // If container has already exited, break the loop
            if ($containerStatus === 'exited' || $containerStatus === 'dead') {
                Log::info("Container {$containerId} finished with status: {$containerStatus}");
                file_put_contents($executionLogPath, "Container finished with status: {$containerStatus}\n", FILE_APPEND);
                break;
            }

            // Get current logs to track progress
            exec("docker logs {$containerId} 2>&1", $logsOutput, $logsStatus);
            if ($logsStatus === 0 && !empty($logsOutput)) {
                $currentLogs = implode("\n", $logsOutput);

                // Hash the logs to determine if they've changed
                $currentLogsHash = md5($currentLogs);
                $logsChanged = ($currentLogsHash !== $lastLogsHash);

                if ($logsChanged) {
                    file_put_contents($executionLogPath, $currentLogs . "\n", FILE_APPEND);
                    $lastLogsHash = $currentLogsHash;
                    $noChangesCount = 0; // Reset counter when logs change

                    // Check for test completion patterns in the logs
                    if (preg_match($completionPattern, $currentLogs)) {
                        Log::info("Test completion detected in logs for container {$containerId}");
                        file_put_contents($executionLogPath, "Test completion detected in logs\n", FILE_APPEND);
                        // Give container a moment to fully complete and exit on its own
                        sleep(5);
                        break;
                    }

                    if (preg_match($failurePattern, $currentLogs)) {
                        Log::info("Test failure detected in logs for container {$containerId}");
                        file_put_contents($executionLogPath, "Test failure detected in logs\n", FILE_APPEND);
                        // Give container a moment to fully complete and exit on its own
                        sleep(5);
                        break;
                    }
                } else {
                    // If logs aren't changing, increment counter
                    $noChangesCount++;

                    // If logs haven't changed for 6 checks (30 seconds), assume test is done
                    if ($noChangesCount >= 6) {
                        Log::info("No log changes detected for 30 seconds, assuming test completion for {$containerId}");
                        file_put_contents($executionLogPath, "No log changes for 30 seconds, assuming completion\n", FILE_APPEND);
                        break;
                    }
                }

                // Update execution with current logs for better tracking
                $this->execution->s3_results_key = "executions/{$this->execution->id}/execution_log.txt";
                $this->execution->save();
            }

            // Collect resource metrics every 15 seconds
            if (time() % 15 === 0) {
                $this->collectResourceMetrics($container);
            }

            gc_collect_cycles();
            sleep($checkInterval);
        }

        // Check if we hit the timeout
        if (time() - $startTime >= $timeout) {
            Log::warning("Container {$containerId} execution timed out");
            file_put_contents($executionLogPath, "Execution timed out after " . round((time() - $startTime) / 60) . " minutes\n", FILE_APPEND);

            // Stop the container
            exec("docker stop {$containerId} 2>&1");
            $this->failExecution($container, "timeout");
        }

        // Make sure we get final logs
        exec("docker logs {$containerId} 2>&1", $finalLogsOutput, $finalLogsStatus);
        if ($finalLogsStatus === 0 && !empty($finalLogsOutput)) {
            $finalLogs = implode("\n", $finalLogsOutput);
            file_put_contents($executionLogPath, "Final logs:\n" . $finalLogs . "\n", FILE_APPEND);
        }
    }

    /**
     * Helper method to fail the execution with the given reason
     */
    private function failExecution(Container $container, string $reason): void
    {
        // Get appropriate status based on failure reason
        $statusName = ($reason === 'timeout') ? 'timeout' : 'failed';
        $failedStatus = ExecutionStatus::where('name', $statusName)->first();

        // Update execution status
        $this->execution->status_id = $failedStatus->id;
        $this->execution->end_time = now();
        $this->execution->save();

        // Update container status
        $container->status = ($reason === 'timeout') ? Container::TERMINATED : Container::FAILED;
        $container->end_time = now();
        $container->save();

        // If reason is timeout, throw an exception to break out of job
        if ($reason === 'timeout') {
            throw new \Exception("Test execution timed out");
        }
    }

    /**
     * Helper method to update status for failed executions
     */
    private function updateExecutionFailure(Container $container, string $reason): void
    {
        $failedStatus = ExecutionStatus::where('name', 'failed')->first();
        $this->execution->status_id = $failedStatus->id;
        $this->execution->end_time = now();
        $this->execution->save();

        $container->status = Container::FAILED;
        $container->end_time = now();
        $container->save();
    }

    /**
     * Collect resource metrics for the container.
     */
    protected function collectResourceMetrics(Container $container): void
    {
        $containerId = $container->container_id;

        // Get CPU and memory stats
        exec("docker stats {$containerId} --no-stream --format '{{.CPUPerc}}|{{.MemUsage}}' 2>&1", $statsOutput, $statsCode);

        if ($statsCode !== 0 || !isset($statsOutput[0])) {
            Log::warning("Failed to collect resource metrics for container {$containerId}");
            return;
        }

        // Parse stats output
        $stats = explode('|', $statsOutput[0]);
        if (count($stats) >= 2) {
            $cpuPerc = trim($stats[0]);
            $memUsage = trim($stats[1]);

            // Extract numeric values
            $cpuValue = floatval(str_replace('%', '', $cpuPerc));

            // Memory is typically in format "100MiB / 8GiB"
            $memParts = explode('/', $memUsage);
            $memValue = floatval(preg_replace('/[^0-9.]/', '', $memParts[0]));

            // Save metric
            ResourceMetric::create([
                'container_id' => $container->id,
                'cpu_usage' => $cpuValue,
                'memory_usage' => $memValue,
                'additional_metrics' => [
                    'raw_cpu' => $cpuPerc,
                    'raw_memory' => $memUsage
                ],
                'metric_time' => now()
            ]);
        }
    }

    /**
     * Process the results after container execution completes.
     */
    protected function processResults(Container $container): void
    {
        $containerId = $container->container_id;

        // Get container exit code
        exec("docker inspect --format='{{.State.ExitCode}}' {$containerId} 2>&1", $exitCodeOutput, $exitCodeStatus);

        if ($exitCodeStatus !== 0 || !isset($exitCodeOutput[0])) {
            Log::error("Failed to get container exit code: " . implode("\n", $exitCodeOutput));
            throw new \Exception("Failed to get container exit code");
        }

        $exitCode = (int)trim($exitCodeOutput[0]);

        // Get container logs
        exec("docker logs {$containerId} 2>&1", $logsOutput, $logsStatus);

        if ($logsStatus !== 0) {
            Log::error("Failed to get container logs: " . implode("\n", $logsOutput));
        }

        $logs = implode("\n", $logsOutput);

        // Save logs to storage
        $logsPath = "executions/{$this->execution->id}/logs.txt";
        Storage::put($logsPath, $logs);

        // Update container status
        $container->status = Container::COMPLETED;
        $container->end_time = now();
        $container->save();

        // Update execution status based on exit code
        if ($exitCode === 0) {
            $completedStatus = ExecutionStatus::where('name', 'completed')->first();
            $this->execution->status_id = $completedStatus->id;
        } else {
            $failedStatus = ExecutionStatus::where('name', 'failed')->first();
            $this->execution->status_id = $failedStatus->id;
        }

        $this->execution->end_time = now();
        $this->execution->s3_results_key = $logsPath;
        $this->execution->save();

        // Clean up container
        $this->cleanupContainer($containerId);
    }

    /**
     * Clean up the Docker container.
     */
    protected function cleanupContainer($containerId): void
    {
        try {
            // First stop the container if it's still running
            exec("docker stop {$containerId} 2>&1", $stopOutput, $stopCode);

            // Then remove it
            exec("docker rm {$containerId} 2>&1", $rmOutput, $rmCode);

            if ($rmCode !== 0) {
                Log::error("Failed to remove container {$containerId}: " . implode("\n", $rmOutput));
            }
        } catch (\Exception $e) {
            Log::error("Error cleaning up container {$containerId}: " . $e->getMessage());
        }
    }
}
