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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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
     * The filesystem disk to use for storing logs.
     */
    protected string $logDisk;

    /**
     * The container monitoring timeout in seconds.
     */
    protected int $containerTimeout;

    /**
     * The interval in seconds between container checks.
     */
    protected int $checkInterval;

    /**
     * Is this running on Windows?
     */
    protected bool $isWindows;

    /**
     * Create a new job instance.
     */
    public function __construct(TestExecution $execution)
    {
        $this->execution = $execution;
        $this->logDisk = Config::get('testing.log_disk', 'local');
        $this->containerTimeout = Config::get('testing.container_timeout', 1200); // 20 minutes
        $this->checkInterval = Config::get('testing.check_interval', 5); // 5 seconds
        $this->isWindows = PHP_OS_FAMILY === 'Windows';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Test execution starting", [
            'execution_id' => $this->execution->id,
            'os' => PHP_OS_FAMILY
        ]);

        $container = null;
        $workDir = null;

        try {
            // 1. Update status to preparing workspace
            $this->updateExecutionStatus(ExecutionStatus::PENDING);

            // 2. Get test script and environment details
            $script = $this->execution->testScript;
            $environment = $this->execution->environment;

            if (!$script) {
                throw new \Exception("Test script not found for execution #{$this->execution->id}");
            }

            Log::info("Using test script", [
                'execution_id' => $this->execution->id,
                'script_id' => $script->id,
                'framework_type' => $script->framework_type
            ]);

            // 3. Prepare working directory
            $workDir = $this->prepareWorkingDirectory($script);

            // 4. Create container record
            $containerId = 'arxitest_' . Str::random(10);
            $container = $this->createContainerRecord($containerId, $script, $environment, $workDir);

            // 5. Update status to running when starting container
            $this->updateExecutionStatus(ExecutionStatus::RUNNING);

            // 6. Start the Docker container
            $this->startContainer($container, $script, $environment, $workDir);

            // 7. Monitor container execution
            $this->monitorContainer($container);

            // 8. Process test results
            $this->processResults($container);
        } catch (\Exception $e) {
            Log::error("Error executing test", [
                'execution_id' => $this->execution->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Set execution to failed
            $this->updateExecutionStatus(ExecutionStatus::FAILED);

            // Update container status if it exists
            if ($container) {
                $container->status = Container::FAILED;
                $container->end_time = now();
                $container->save();

                // Try to clean up the container
                $this->cleanupContainer($container->container_id);
            }
        } finally {
            // Cleanup work directory
            if ($workDir && file_exists($workDir) && is_dir($workDir)) {
                $this->cleanupWorkDirectory($workDir);
            }
        }
    }

    /**
     * Prepare the working directory for test execution.
     */
    protected function prepareWorkingDirectory($script): string
    {
        $workDirName = 'execution_' . $this->execution->id . '_' . Str::random(8);
        $workDir = storage_path("app" . DIRECTORY_SEPARATOR . "executions" . DIRECTORY_SEPARATOR . $workDirName);

        Log::info("Creating working directory", [
            'execution_id' => $this->execution->id,
            'work_dir' => $workDir
        ]);

        if (!is_dir($workDir)) {
            mkdir($workDir, 0755, true);
        }

        // Write test script to file
        $scriptExtension = $this->getScriptExtension($script->framework_type);
        $scriptPath = $workDir . DIRECTORY_SEPARATOR . 'test_script' . $scriptExtension;

        // Make sure to save with LF line endings, not CRLF (Windows default)
        $content = $script->script_content;
        if ($this->isWindows) {
            $content = str_replace("\r\n", "\n", $content);
        }

        file_put_contents($scriptPath, $content);

        Log::info("Test script written to file", [
            'execution_id' => $this->execution->id,
            'script_path' => $scriptPath,
            'size' => strlen($content)
        ]);

        return $workDir;
    }

    /**
     * Convert a local path to a Docker-compatible mount path.
     */
    protected function getDockerMountPath(string $localPath): string
    {
        if ($this->isWindows) {
            // If using WSL2, Docker expects paths like /c/Users/...
            // Convert C:\path\to\dir to /c/path/to/dir
            $path = str_replace('\\', '/', $localPath);
            if (preg_match('/^([A-Z]):\/(.*)$/', $path, $matches)) {
                return '/' . strtolower($matches[1]) . '/' . $matches[2];
            }

            // For Windows without pattern match, return normalized path
            return str_replace('\\', '/', $localPath);
        }

        return $localPath;
    }

    /**
     * Create a container record in the database.
     */
    protected function createContainerRecord(string $containerId, $script, $environment, string $workDir): Container
    {
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

        Log::info("Created container record", [
            'execution_id' => $this->execution->id,
            'container_id' => $containerId,
            'container_record_id' => $container->id
        ]);

        return $container;
    }

    /**
     * Start the Docker container.
     */
    protected function startContainer(Container $container, $script, $environment, string $workDir): void
    {
        $containerId = $container->container_id;
        $dockerImage = $this->getDockerImage($script->framework_type);
        $scriptExtension = $this->getScriptExtension($script->framework_type);

        // Get Docker-compatible mount path
        $mountPath = $this->getDockerMountPath($workDir);

        // Create a simpler shell script that's compatible with basic /bin/sh
        $startScriptPath = $workDir . DIRECTORY_SEPARATOR . 'run_test.sh';
        $logFilePath = '/tests/output.log';

        // Create a wrapper script that captures all output using basic sh
        $startScriptContent = "#!/bin/sh\n";
        $startScriptContent .= "echo 'Starting test execution...' > $logFilePath\n";

        if ($script->framework_type === 'selenium-python') {
            // Run the test and capture the exit code directly (no PIPESTATUS)
            $startScriptContent .= "python -m unittest -v /tests/test_script$scriptExtension > /tests/test_output.tmp 2>&1\n";
            $startScriptContent .= "TEST_EXIT_CODE=\$?\n";
            $startScriptContent .= "cat /tests/test_output.tmp | tee -a $logFilePath\n";
        } elseif ($script->framework_type === 'cypress') {
            $startScriptContent .= "npx cypress run --spec /tests/test_script$scriptExtension > /tests/test_output.tmp 2>&1\n";
            $startScriptContent .= "TEST_EXIT_CODE=\$?\n";
            $startScriptContent .= "cat /tests/test_output.tmp | tee -a $logFilePath\n";
        } else {
            $startScriptContent .= "/tests/test_script$scriptExtension > /tests/test_output.tmp 2>&1\n";
            $startScriptContent .= "TEST_EXIT_CODE=\$?\n";
            $startScriptContent .= "cat /tests/test_output.tmp | tee -a $logFilePath\n";
        }

        // Add command to write exit code to a file
        $startScriptContent .= "echo \$TEST_EXIT_CODE > /tests/exit_code.txt\n";

        // Add a small delay to ensure logs are written
        $startScriptContent .= "sleep 2\n";

        // Exit with the original exit code
        $startScriptContent .= "exit \$TEST_EXIT_CODE\n";

        // Save the start script with Unix line endings
        $content = str_replace("\r\n", "\n", $startScriptContent);
        file_put_contents($startScriptPath, $content);

        // Make the script executable (won't work on Windows but harmless)
        if (!$this->isWindows) {
            chmod($startScriptPath, 0755);
        }

        // Build Docker command
        $command = ['docker', 'run'];
        $command[] = '--name';
        $command[] = $containerId;

        // Use detached mode for more reliability
        $command[] = '-d';

        $command[] = "--label";
        $command[] = "arxitest_execution_id={$this->execution->id}";
        $command[] = "-v";
        $command[] = "{$mountPath}:/tests";

        // Add environment variables
        if ($environment && $environment->configuration) {
            foreach ($environment->configuration as $key => $value) {
                $command[] = '-e';
                // Properly handle quoting for Windows shell
                if ($this->isWindows) {
                    $escapedValue = str_replace('"', '\"', $value);
                    $command[] = "{$key}=\"{$escapedValue}\"";
                } else {
                    $command[] = "{$key}={$value}";
                }
            }
        }

        // Add Chrome options for Selenium
        if ($script->framework_type === 'selenium-python') {
            $command[] = '-e';
            $chromeOptions = 'CHROME_OPTIONS=--headless --no-sandbox --disable-dev-shm-usage --disable-gpu';
            if ($this->isWindows) {
                $chromeOptions = '"' . $chromeOptions . '"';
            }
            $command[] = $chromeOptions;
        }

        // Use a shell as entry point to run our script
        $command[] = '--entrypoint';
        $command[] = '/bin/sh';
        $command[] = $dockerImage;
        $command[] = '/tests/run_test.sh';

        Log::info("Starting Docker container", [
            'execution_id' => $this->execution->id,
            'container_id' => $containerId,
            'docker_image' => $dockerImage,
            'command' => implode(' ', $command),
            'mount_path' => $mountPath,
            'start_script' => $startScriptContent
        ]);

        // Use Process to run Docker command
        $process = new Process($command);
        $process->setTimeout(60); // 60-second timeout for starting container

        try {
            $process->mustRun();

            Log::info("Container started successfully", [
                'execution_id' => $this->execution->id,
                'container_id' => $containerId,
                'output' => trim($process->getOutput())
            ]);

            // Update container status
            $container->status = Container::RUNNING;
            $container->save();
        } catch (ProcessFailedException $e) {
            Log::error("Failed to start Docker container", [
                'execution_id' => $this->execution->id,
                'container_id' => $containerId,
                'error' => $e->getMessage(),
                'stdout' => $process->getOutput(),
                'stderr' => $process->getErrorOutput()
            ]);

            throw new \Exception("Failed to start Docker container: " . $process->getErrorOutput());
        }
    }

    /**
     * Monitor the container execution.
     */
    protected function monitorContainer(Container $container): void
    {
        $containerId = $container->container_id;
        $startTime = time();
        $logPath = "executions" . DIRECTORY_SEPARATOR . $this->execution->id . DIRECTORY_SEPARATOR . "execution_log.txt";

        Log::info("Started monitoring container", [
            'execution_id' => $this->execution->id,
            'container_id' => $containerId,
            'timeout' => $this->containerTimeout,
            'check_interval' => $this->checkInterval
        ]);

        // Initialize log file
        Storage::disk($this->logDisk)->put(
            $logPath,
            "=== TEST EXECUTION STARTED AT " . now()->toDateTimeString() . " ===\n\n"
        );

        // Track last log position to avoid duplicates
        $lastLogSize = 0;

        while (true) {
            // Check if monitoring timeout is reached
            if (time() - $startTime >= $this->containerTimeout) {
                Log::warning("Container execution timed out", [
                    'execution_id' => $this->execution->id,
                    'container_id' => $containerId,
                    'elapsed_time' => time() - $startTime,
                    'timeout' => $this->containerTimeout
                ]);

                Storage::disk($this->logDisk)->append(
                    $logPath,
                    "\n=== EXECUTION TIMED OUT AFTER " .
                        round((time() - $startTime) / 60) . " MINUTES ===\n"
                );

                // Stop the container
                $this->stopContainer($containerId);
                $this->failExecution($container, "timeout");
                break;
            }

            // Check container status
            $status = $this->getContainerStatus($containerId);

            // If container has exited, break the loop
            if (in_array($status, ['exited', 'dead', 'not-found'])) {
                Log::info("Container has finished execution", [
                    'execution_id' => $this->execution->id,
                    'container_id' => $containerId,
                    'status' => $status
                ]);

                Storage::disk($this->logDisk)->append(
                    $logPath,
                    "\n=== CONTAINER " . strtoupper($status) . " ===\n"
                );

                break;
            }

            // Collect logs from container
            $this->fetchAndStoreLogs($containerId, $logPath, $lastLogSize);

            // Collect resource metrics
            $this->collectResourceMetrics($container);

            // Sleep before next check
            sleep($this->checkInterval);
        }

        // Try to get the output log file from the container
        $workDir = $container->configuration['work_dir'];
        $outputLogPath = $workDir . DIRECTORY_SEPARATOR . 'output.log';
        $exitCodePath = $workDir . DIRECTORY_SEPARATOR . 'exit_code.txt';

        if (file_exists($outputLogPath)) {
            $outputContent = file_get_contents($outputLogPath);
            if (!empty($outputContent)) {
                Storage::disk($this->logDisk)->append(
                    $logPath,
                    "\n=== TEST OUTPUT ===\n" . $outputContent
                );

                Log::info("Read test output from log file", [
                    'execution_id' => $this->execution->id,
                    'container_id' => $containerId,
                    'output_size' => strlen($outputContent)
                ]);
            }
        } else {
            Log::warning("Output log file not found", [
                'execution_id' => $this->execution->id,
                'container_id' => $containerId,
                'expected_path' => $outputLogPath
            ]);
        }

        // Try to get the exit code file
        if (file_exists($exitCodePath)) {
            $exitCode = trim(file_get_contents($exitCodePath));
            Storage::disk($this->logDisk)->append(
                $logPath,
                "\n=== EXIT CODE: $exitCode ===\n"
            );

            Log::info("Read exit code from file", [
                'execution_id' => $this->execution->id,
                'container_id' => $containerId,
                'exit_code' => $exitCode
            ]);
        }

        // Ensure we get the final logs as well
        $this->fetchAndStoreLogs($containerId, $logPath, $lastLogSize, true);

        // Update execution with log file path
        $this->execution->s3_results_key = $logPath;
        $this->execution->save();
    }

    /**
     * Get the current container status.
     */
    protected function getContainerStatus(string $containerId): string
    {
        $process = new Process(['docker', 'inspect', '--format={{.State.Status}}', $containerId]);
        $process->run();

        if (!$process->isSuccessful()) {
            return 'not-found';
        }

        return trim($process->getOutput());
    }

    /**
     * Fetch logs from the container and append to the log file.
     */
    protected function fetchAndStoreLogs(string $containerId, string $logPath, int &$lastLogSize, bool $isFinal = false): void
    {
        try {
            // For final logs, use a more aggressive approach to ensure we get everything
            if ($isFinal) {
                // Allow a little time for logs to flush to Docker's logging system
                sleep(1);

                // Use --timestamps to help debugging and include stderr explicitly
                $process = new Process(['docker', 'logs', '--timestamps', '--details', $containerId]);
                $process->setTimeout(30); // Longer timeout for final log collection
                $process->run();

                if (!$process->isSuccessful()) {
                    Log::warning("Failed to fetch final container logs", [
                        'execution_id' => $this->execution->id,
                        'container_id' => $containerId,
                        'error' => $process->getErrorOutput()
                    ]);
                    return;
                }

                $logs = $process->getOutput();

                // If we have logs, append them no matter what
                if (!empty($logs)) {
                    Storage::disk($this->logDisk)->append($logPath, "\n=== FINAL LOGS ===\n");
                    Storage::disk($this->logDisk)->append($logPath, $logs);

                    Log::info("Final logs collected", [
                        'execution_id' => $this->execution->id,
                        'container_id' => $containerId,
                        'size' => strlen($logs)
                    ]);
                } else {
                    // Try one more time with different options if no logs found
                    $process = new Process(['docker', 'logs', '--tail', 'all', $containerId]);
                    $process->run();
                    $logs = $process->getOutput();

                    if (!empty($logs)) {
                        Storage::disk($this->logDisk)->append($logPath, "\n=== FINAL LOGS (RETRY) ===\n");
                        Storage::disk($this->logDisk)->append($logPath, $logs);

                        Log::info("Final logs collected on retry", [
                            'execution_id' => $this->execution->id,
                            'size' => strlen($logs)
                        ]);
                    } else {
                        // If we still have no logs, try to get the exit details
                        $inspectProcess = new Process(['docker', 'inspect', $containerId]);
                        $inspectProcess->run();

                        if ($inspectProcess->isSuccessful()) {
                            $inspect = $inspectProcess->getOutput();
                            Storage::disk($this->logDisk)->append($logPath, "\n=== CONTAINER DETAILS ===\n");
                            Storage::disk($this->logDisk)->append($logPath, $inspect);

                            Log::info("No logs available, added container inspect data", [
                                'execution_id' => $this->execution->id,
                                'container_id' => $containerId
                            ]);
                        }
                    }
                }

                return;
            }

            // Regular log capture during execution
            // Use --since flag to get only new logs since start time
            $process = new Process(['docker', 'logs', '--timestamps', $containerId]);
            $process->run();

            if (!$process->isSuccessful()) {
                Log::warning("Failed to fetch container logs", [
                    'execution_id' => $this->execution->id,
                    'container_id' => $containerId,
                    'error' => $process->getErrorOutput()
                ]);
                return;
            }

            $logs = $process->getOutput();

            // If there are no logs yet, don't do anything
            if (empty($logs)) {
                return;
            }

            // Get only new logs - this is the key fix
            $currentSize = strlen($logs);

            // Log the raw content for debugging
            Log::debug("Fetched raw container logs", [
                'execution_id' => $this->execution->id,
                'container_id' => $containerId,
                'log_size' => $currentSize,
                'last_log_size' => $lastLogSize,
                'has_new_content' => ($currentSize > $lastLogSize)
            ]);

            if ($currentSize > $lastLogSize) {
                // Extract only the new content
                $newLogs = substr($logs, $lastLogSize);

                // Append only new logs to the log file
                Storage::disk($this->logDisk)->append($logPath, $newLogs);

                Log::debug("Appended new logs", [
                    'execution_id' => $this->execution->id,
                    'container_id' => $containerId,
                    'new_bytes' => strlen($newLogs),
                    'sample' => substr($newLogs, 0, min(100, strlen($newLogs))) . (strlen($newLogs) > 100 ? '...' : '')
                ]);

                // Update last log size for next iteration
                $lastLogSize = $currentSize;
            }
        } catch (\Exception $e) {
            Log::error("Error fetching container logs", [
                'execution_id' => $this->execution->id,
                'container_id' => $containerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Stop a container.
     */
    protected function stopContainer(string $containerId): void
    {
        $process = new Process(['docker', 'stop', $containerId]);
        $process->run();

        if ($process->isSuccessful()) {
            Log::info("Container stopped", [
                'execution_id' => $this->execution->id,
                'container_id' => $containerId
            ]);
        } else {
            Log::warning("Failed to stop container", [
                'execution_id' => $this->execution->id,
                'container_id' => $containerId,
                'error' => $process->getErrorOutput()
            ]);
        }
    }

    /**
     * Collect resource metrics for the container.
     */
    protected function collectResourceMetrics(Container $container): void
    {
        $containerId = $container->container_id;

        $process = new Process(['docker', 'stats', $containerId, '--no-stream', '--format', '{{.CPUPerc}}|{{.MemUsage}}']);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::warning("Failed to collect resource metrics", [
                'execution_id' => $this->execution->id,
                'container_id' => $containerId,
                'error' => $process->getErrorOutput()
            ]);
            return;
        }

        $output = trim($process->getOutput());
        if (empty($output)) {
            return;
        }

        // Parse the stats output
        $stats = explode('|', $output);
        if (count($stats) >= 2) {
            $cpuPerc = trim($stats[0]);
            $memUsage = trim($stats[1]);

            // Extract numeric values
            $cpuValue = floatval(str_replace('%', '', $cpuPerc));

            // Memory is typically in format "100MiB / 8GiB"
            $memParts = explode('/', $memUsage);
            $memValue = floatval(preg_replace('/[^0-9.]/', '', $memParts[0]));

            try {
                // Save the metrics
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

                Log::debug("Resource metrics saved", [
                    'execution_id' => $this->execution->id,
                    'container_id' => $containerId,
                    'cpu' => $cpuValue,
                    'memory' => $memValue
                ]);
            } catch (\Exception $e) {
                Log::warning("Failed to save resource metrics", [
                    'execution_id' => $this->execution->id,
                    'container_id' => $containerId,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Process the results after container execution completes.
     */
    protected function processResults(Container $container): void
    {
        $containerId = $container->container_id;

        Log::info("Processing test results", [
            'execution_id' => $this->execution->id,
            'container_id' => $containerId
        ]);

        // Check for exit code file first
        $exitCodePath = $container->configuration['work_dir'] . DIRECTORY_SEPARATOR . 'exit_code.txt';
        if (file_exists($exitCodePath)) {
            $exitCode = (int)trim(file_get_contents($exitCodePath));
            Log::info("Using exit code from file", [
                'execution_id' => $this->execution->id,
                'container_id' => $containerId,
                'exit_code' => $exitCode
            ]);
        } else {
            // Fall back to Docker's exit code
            $exitCode = $this->getContainerExitCode($containerId);
            Log::info("Using Docker exit code", [
                'execution_id' => $this->execution->id,
                'container_id' => $containerId,
                'exit_code' => $exitCode
            ]);
        }

        // Update container status based on exit code
        if ($exitCode === 0) {
            $container->status = Container::COMPLETED;
            $this->updateExecutionStatus(ExecutionStatus::COMPLETED);
        } else {
            $container->status = Container::FAILED;
            $this->updateExecutionStatus(ExecutionStatus::FAILED);

            // Add exit code info to the log
            $logPath = "executions" . DIRECTORY_SEPARATOR . $this->execution->id . DIRECTORY_SEPARATOR . "execution_log.txt";
            Storage::disk($this->logDisk)->append(
                $logPath,
                "\n=== TEST EXECUTION FAILED WITH EXIT CODE {$exitCode} ===\n"
            );
        }

        $container->end_time = now();
        $container->save();

        // Clean up container
        $this->cleanupContainer($containerId);
    }

    /**
     * Get the container exit code.
     */
    protected function getContainerExitCode(string $containerId): int
    {
        $process = new Process(['docker', 'inspect', '--format={{.State.ExitCode}}', $containerId]);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::error("Failed to get container exit code", [
                'execution_id' => $this->execution->id,
                'container_id' => $containerId,
                'error' => $process->getErrorOutput()
            ]);
            // Return non-zero exit code to indicate failure
            return 1;
        }

        return (int) trim($process->getOutput());
    }

    /**
     * Helper method to fail the execution with the given reason
     */
    protected function failExecution(Container $container, string $reason): void
    {
        // Get appropriate status based on failure reason
        $statusName = ($reason === 'timeout') ? ExecutionStatus::TIMEOUT : ExecutionStatus::FAILED;
        $this->updateExecutionStatus($statusName);

        // Update container status
        $container->status = ($reason === 'timeout') ? Container::TERMINATED : Container::FAILED;
        $container->end_time = now();
        $container->save();

        Log::warning("Execution failed", [
            'execution_id' => $this->execution->id,
            'container_id' => $container->container_id,
            'reason' => $reason
        ]);

        // For timeout, throw an exception to break out of the monitoring loop
        if ($reason === 'timeout') {
            throw new \Exception("Test execution timed out");
        }
    }

    /**
     * Update the execution status.
     */
    protected function updateExecutionStatus(string $statusName): void
    {
        try {
            // Validate the status name against constants
            if (!in_array($statusName, [
                ExecutionStatus::PENDING,
                ExecutionStatus::RUNNING,
                ExecutionStatus::COMPLETED,
                ExecutionStatus::FAILED,
                ExecutionStatus::ABORTED,
                ExecutionStatus::TIMEOUT
            ])) {
                // Log a warning and fall back to a default status
                Log::warning("Invalid status name used", [
                    'execution_id' => $this->execution->id,
                    'status_name' => $statusName,
                    'falling_back_to' => ExecutionStatus::RUNNING
                ]);

                // Default to RUNNING for unrecognized intermediate states
                $statusName = ExecutionStatus::RUNNING;
            }

            $status = ExecutionStatus::where('name', $statusName)->first();
            if (!$status) {
                // This should now never happen, but if it does, it's a critical error
                $errorMsg = "Status not found in database: {$statusName}";
                Log::error($errorMsg, [
                    'execution_id' => $this->execution->id,
                    'status_name' => $statusName
                ]);
                throw new \RuntimeException($errorMsg);
            }

            $this->execution->status_id = $status->id;

            // Set end time for final statuses
            if (in_array($statusName, [
                ExecutionStatus::COMPLETED,
                ExecutionStatus::FAILED,
                ExecutionStatus::ABORTED,
                ExecutionStatus::TIMEOUT
            ])) {
                $this->execution->end_time = now();
            }

            $this->execution->save();

            Log::info("Updated execution status", [
                'execution_id' => $this->execution->id,
                'status' => $statusName
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update execution status", [
                'execution_id' => $this->execution->id,
                'status' => $statusName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw to ensure job fails if status update fails
            throw new \RuntimeException("Failed to update execution status: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Clean up the Docker container.
     */
    protected function cleanupContainer(string $containerId): void
    {
        try {
            // First stop the container if it's still running
            $stopProcess = new Process(['docker', 'stop', $containerId]);
            $stopProcess->run();

            // Then remove it
            $rmProcess = new Process(['docker', 'rm', $containerId]);
            $rmProcess->run();

            if ($rmProcess->isSuccessful()) {
                Log::info("Container removed", [
                    'execution_id' => $this->execution->id,
                    'container_id' => $containerId
                ]);
            } else {
                Log::warning("Failed to remove container", [
                    'execution_id' => $this->execution->id,
                    'container_id' => $containerId,
                    'error' => $rmProcess->getErrorOutput()
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error cleaning up container", [
                'execution_id' => $this->execution->id,
                'container_id' => $containerId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clean up the working directory.
     */
    protected function cleanupWorkDirectory(string $workDir): void
    {
        try {
            if ($this->isWindows) {
                // Windows-specific directory removal
                $process = new Process(['cmd', '/c', 'rmdir', '/s', '/q', $workDir]);
            } else {
                // Unix directory removal
                $process = new Process(['rm', '-rf', $workDir]);
            }

            $process->run();

            if ($process->isSuccessful()) {
                Log::info("Working directory removed", [
                    'execution_id' => $this->execution->id,
                    'work_dir' => $workDir
                ]);
            } else {
                Log::warning("Failed to remove working directory", [
                    'execution_id' => $this->execution->id,
                    'work_dir' => $workDir,
                    'error' => $process->getErrorOutput()
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error cleaning up working directory", [
                'execution_id' => $this->execution->id,
                'work_dir' => $workDir,
                'error' => $e->getMessage()
            ]);
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

        $configImages = Config::get('testing.docker_images', []);
        Log::info("Docker image config", [
            'execution_id' => $this->execution->id,
            'config' =>  $configImages,
        ]);
        return match ($frameworkType) {
            'selenium-python' => $configImages['selenium_python'] ?? 'youzzine/arxitest-selenium-python:latest',
            'cypress' => $configImages['cypress'] ?? 'youzzine/arxitest-cypress:latest',
            default => $configImages['default'] ?? 'alpine:latest'
        };
    }
}
