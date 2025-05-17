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
use Symfony\Component\Process\Process;
use Illuminate\Support\Str;

class RunTestExecutionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $execution;
    protected $logPath;
    protected $logFile;
    protected $containerName;
    protected $containerInstance;
    protected $timeout = 600; // Default timeout in seconds (10 minutes)

    /**
     * Create a new job instance.
     */
    public function __construct(TestExecution $execution)
    {
        $this->execution = $execution;

        // Create a base directory path without any trailing slashes
        $baseDir = rtrim(storage_path('app'), '/\\');

        // Build paths with explicit directory separators
        $execDir = 'executions' . DIRECTORY_SEPARATOR . $execution->id;
        $this->logPath = $baseDir . DIRECTORY_SEPARATOR . $execDir;
        $this->logFile = $this->logPath . DIRECTORY_SEPARATOR . 'execution_log.txt';
        $this->containerName = "test_exec_{$execution->id}";

        // Debug log paths at construction time
        Log::debug("Job constructor paths", [
            'execution_id' => $execution->id,
            'storage_path' => storage_path(),
            'app_path' => $baseDir,
            'log_path' => $this->logPath,
            'log_file' => $this->logFile
        ]);

        // Get custom timeout if specified in metadata
        if (isset($execution->metadata['timeout_minutes']) && is_numeric($execution->metadata['timeout_minutes'])) {
            $this->timeout = (int)$execution->metadata['timeout_minutes'] * 60;
        }
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $scriptName = $this->execution->testScript->name ?? 'Unknown Script';
            $envName = $this->execution->environment->name ?? 'Unknown Environment';

            Log::info("Starting test execution", [
                'execution_id' => $this->execution->id,
                'script_name' => $scriptName,
                'environment' => $envName
            ]);

            // Check all parent directories exist
            $parentDir = dirname($this->logPath);
            Log::debug("Directory structure check", [
                'execution_id' => $this->execution->id,
                'log_path' => $this->logPath,
                'parent_dir' => $parentDir,
                'parent_exists' => file_exists($parentDir),
                'storage_app_dir' => storage_path('app'),
                'storage_app_exists' => file_exists(storage_path('app')),
                'storage_app_executions' => storage_path('app' . DIRECTORY_SEPARATOR . 'executions'),
                'executions_exists' => file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'executions'))
            ]);

            // Make sure parent directories exist
            $this->createParentDirectories();

            // Create log directory
            $dirResult = mkdir($this->logPath, 0755, true);

            Log::debug("Log directory creation", [
                'execution_id' => $this->execution->id,
                'log_path' => $this->logPath,
                'creation_result' => $dirResult,
                'dir_exists_after' => is_dir($this->logPath),
                'dir_writable' => is_dir($this->logPath) ? is_writable($this->logPath) : false
            ]);

            if (!is_dir($this->logPath)) {
                throw new \Exception("Could not create log directory at: {$this->logPath}");
            }

            // Initialize log file
            $logInitResult = file_put_contents($this->logFile, "=== Test Execution Log ===\n");
            if ($logInitResult === false) {
                throw new \Exception("Could not write to log file at: {$this->logFile}");
            }

            $this->appendLog("Starting execution for script: {$scriptName}");
            $this->appendLog("Environment: {$envName}");

            // Update execution status to running
            $this->updateExecutionStatus('running');

            // Prepare test script and environment
            $this->prepareTestFiles();

            // Create and run container
            $this->createContainer();
            $exitCode = $this->runContainer();

            // Process results and generate report
            $this->processResults($exitCode);

            Log::info("Test execution completed", [
                'execution_id' => $this->execution->id,
                'exit_code' => $exitCode
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to start container: {$e->getMessage()}", [
                'execution_id' => $this->execution->id,
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);

            Log::debug("Execution error trace", [
                'execution_id' => $this->execution->id,
                'trace' => $e->getTraceAsString()
            ]);

            $this->updateExecutionStatus('failed');
            $this->cleanup();
        }
    }

    /**
     * Create parent directories for log path
     */
    protected function createParentDirectories()
    {
        // Create the executions directory
        $executionsDir = dirname($this->logPath);
        if (!is_dir($executionsDir)) {
            $result = mkdir($executionsDir, 0755, true);
            Log::debug("Created executions directory", [
                'directory' => $executionsDir,
                'result' => $result,
                'exists_after' => is_dir($executionsDir)
            ]);
        }
    }

    /**
     * Append to log file
     */
    protected function appendLog($message)
    {
        try {
            $timestamp = now()->format('Y-m-d H:i:s');
            $logLine = "[{$timestamp}] {$message}\n";

            $result = file_put_contents($this->logFile, $logLine, FILE_APPEND);

            if ($result === false) {
                Log::warning("Failed to write to log file", [
                    'execution_id' => $this->execution->id,
                    'log_file' => $this->logFile
                ]);
            }
        } catch (\Exception $e) {
            Log::warning("Error writing to log file: {$e->getMessage()}", [
                'execution_id' => $this->execution->id
            ]);
        }
    }

    /**
     * Prepare test script and environment files
     */
    protected function prepareTestFiles()
    {
        $this->appendLog("Preparing test files...");

        $testScript = $this->execution->testScript;
        if (!$testScript) {
            throw new \Exception("Test script not found");
        }

        // Determine file extension based on framework type
        $extension = $this->getFileExtension($testScript->framework_type);
        $scriptPath = $this->logPath . DIRECTORY_SEPARATOR . 'test_script' . $extension;

        Log::debug("Writing test script file", [
            'execution_id' => $this->execution->id,
            'script_path' => $scriptPath,
            'directory_exists' => is_dir($this->logPath),
            'directory_writable' => is_writable($this->logPath),
            'content_length' => strlen($testScript->script_content ?? '')
        ]);

        // Write script content to file
        $scriptResult = file_put_contents($scriptPath, $testScript->script_content);

        if ($scriptResult === false) {
            Log::error("Failed to write test script", [
                'execution_id' => $this->execution->id,
                'script_path' => $scriptPath,
                'directory_exists' => is_dir($this->logPath)
            ]);
            throw new \Exception("Failed to write test script file");
        }

        $this->appendLog("Test script written successfully");

        // Create environment file
        $this->createEnvironmentFile();

        // Load test data if available
        $testCase = $testScript->testCase;
        if ($testCase && $testCase->testData && $testCase->testData->isNotEmpty()) {
            $this->appendLog("Processing test data...");

            foreach ($testCase->testData as $testData) {
                $dataFileName = Str::slug($testData->name) . $this->getDataExtension($testData->format);
                $dataPath = $this->logPath . DIRECTORY_SEPARATOR . $dataFileName;

                $dataResult = file_put_contents($dataPath, $testData->content);
                if ($dataResult === false) {
                    Log::warning("Failed to write test data file", [
                        'execution_id' => $this->execution->id,
                        'data_path' => $dataPath
                    ]);
                } else {
                    $this->appendLog("Test data written: {$dataFileName}");
                }
            }
        }
    }

    /**
     * Create a .env file with environment variables
     */
    protected function createEnvironmentFile()
    {
        $environment = $this->execution->environment;
        if (!$environment) {
            $this->appendLog("[WARN] No environment configured for this execution");
            return;
        }

        $this->appendLog("Setting up environment: {$environment->name}");
        $envContent = "# Environment: {$environment->name}\n";

        // Ensure environment->configuration is an array
        $config = $environment->configuration;
        if (is_array($config)) {
            foreach ($config as $key => $value) {
                // Mask sensitive values in logs
                $logValue = Str::contains(strtolower($key), ['password', 'token', 'secret', 'key'])
                    ? '********'
                    : $value;

                $this->appendLog("ENV: {$key}={$logValue}");
                $envContent .= "{$key}={$value}\n";
            }
        } else {
            Log::warning("Environment configuration is not an array", [
                'execution_id' => $this->execution->id,
                'config_type' => gettype($config)
            ]);
        }

        // Add execution context variables
        $envContent .= "TEST_EXECUTION_ID={$this->execution->id}\n";
        $envContent .= "TEST_SCRIPT_NAME={$this->execution->testScript->name}\n";

        $envPath = $this->logPath . DIRECTORY_SEPARATOR . '.env';
        $envResult = file_put_contents($envPath, $envContent);

        if ($envResult === false) {
            Log::warning("Failed to write environment file", [
                'execution_id' => $this->execution->id,
                'env_path' => $envPath
            ]);
        } else {
            $this->appendLog("Environment file created");
        }
    }

    /**
     * Get file extension based on framework type
     */
    protected function getFileExtension($frameworkType)
    {
        return match ($frameworkType) {
            'selenium-python' => '.py',
            'cypress' => '.js',
            default => '.txt'
        };
    }

    /**
     * Get file extension for test data
     */
    protected function getDataExtension($format)
    {
        return match ($format) {
            'json' => '.json',
            'csv' => '.csv',
            'xml' => '.xml',
            default => '.txt'
        };
    }

    /**
     * Create Docker container for the test
     */
    protected function createContainer()
    {
        $this->appendLog("Creating container...");

        // Determine which Docker image to use based on framework type
        $frameworkType = $this->execution->testScript->framework_type;
        $dockerImage = $this->getDockerImage($frameworkType);

        // Create container record in database
        $this->containerInstance = Container::create([
            'execution_id' => $this->execution->id,
            'container_id' => 'pending', // Will update with actual ID
            'container_type' => $frameworkType,
            'status' => 'pending',
            'start_time' => now(),
        ]);

        // Normalize path for Docker volume mounting (convert Windows path if needed)
        $volumePath = $this->getDockerPath($this->logPath);

        Log::debug("Docker volume path preparation", [
            'execution_id' => $this->execution->id,
            'original_path' => $this->logPath,
            'docker_volume_path' => $volumePath
        ]);

        // Build Docker run command
        $createCmd = [
            'docker', 'create',
            '--name', $this->containerName,
            '--network', 'host',
            '-v', "{$volumePath}:/tests",
        ];

        // Add environment variables from environment file
        $envFilePath = $this->logPath . DIRECTORY_SEPARATOR . '.env';
        if (file_exists($envFilePath)) {
            $createCmd[] = '--env-file';
            $createCmd[] = $this->getDockerPath($envFilePath);
        } else {
            Log::warning("Environment file not found for Docker", [
                'execution_id' => $this->execution->id,
                'env_file_path' => $envFilePath
            ]);
        }

        // Add resource limits
        $createCmd[] = '--memory';
        $createCmd[] = '1g';
        $createCmd[] = '--cpus';
        $createCmd[] = '1.0';

        // Add image and command
        $createCmd[] = $dockerImage;

        // Add command based on framework type
        $createCmd = array_merge($createCmd, $this->getContainerCommand($frameworkType));

        // Debug output
        $this->appendLog("Docker command: " . implode(' ', $createCmd));

        // Create container
        $process = new Process($createCmd);
        $process->setTimeout(60); // 1 minute timeout for container creation
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception("Container creation failed: " . $process->getErrorOutput());
        }

        // Get actual container ID
        $containerId = trim($process->getOutput());
        $this->containerInstance->update([
            'container_id' => $containerId,
            'status' => 'created'
        ]);

        $this->appendLog("Container created: {$containerId}");
    }

    /**
     * Get Docker image based on framework type
     */
    protected function getDockerImage($frameworkType)
    {
        return match ($frameworkType) {
            'selenium-python' => 'arxitest/selenium-python:latest',
            'cypress' => 'cypress/included:latest',
            default => 'alpine:latest'
        };
    }

    /**
     * Get container command array based on framework type
     */
    protected function getContainerCommand($frameworkType)
    {
        return match ($frameworkType) {
            'selenium-python' => ['python', '-u', '/tests/test_script.py'],
            'cypress' => ['cypress', 'run', '--spec', '/tests/test_script.js'],
            default => ['cat', '/tests/test_script.txt']
        };
    }

    /**
     * Convert Windows paths to Docker-compatible paths
     */
    protected function getDockerPath($path)
    {
        // For Windows, convert C:\path\to\dir to /c/path/to/dir
        if (DIRECTORY_SEPARATOR === '\\') {
            // Check if path starts with a drive letter (e.g., C:)
            if (preg_match('/^([A-Z]:)(.*)$/i', $path, $matches)) {
                $driveLetter = strtolower($matches[1][0]); // Get drive letter and make lowercase
                $remainingPath = str_replace('\\', '/', $matches[2]); // Convert backslashes to forward slashes
                $dockerPath = "/{$driveLetter}{$remainingPath}";

                Log::debug("Windows path converted for Docker", [
                    'windows_path' => $path,
                    'docker_path' => $dockerPath
                ]);

                return $dockerPath;
            }
        }

        // For non-Windows or paths without drive letters, just replace backslashes
        return str_replace('\\', '/', $path);
    }

    /**
     * Run the container and capture output
     */
    protected function runContainer()
    {
        $this->containerInstance->update(['status' => 'running']);
        $this->appendLog("Starting container execution...");

        // Start container
        $startProcess = new Process(['docker', 'start', '-a', $this->containerName]);
        $startProcess->setTimeout($this->timeout + 30); // Add buffer time

        // Capture output in real-time
        $startProcess->run(function ($type, $buffer) {
            $this->appendLog($buffer);
        });

        $exitCode = $startProcess->getExitCode();

        // Update container status
        $this->containerInstance->update([
            'status' => $exitCode === 0 ? 'completed' : 'failed',
            'exit_code' => $exitCode,
            'end_time' => now()
        ]);

        $this->appendLog("Container execution finished with exit code: {$exitCode}");

        return $exitCode;
    }

    /**
     * Process execution results and generate report
     */
    protected function processResults($exitCode)
    {
        // Set final status based on exit code
        $status = $exitCode === 0 ? 'completed' : 'failed';
        $this->updateExecutionStatus($status);

        // Clean up resources
        $this->cleanup();
    }

    /**
     * Clean up resources
     */
    protected function cleanup()
    {
        $this->appendLog("Cleaning up resources...");

        try {
            // Only attempt to remove if we have a container
            if ($this->containerInstance && $this->containerInstance->container_id &&
                $this->containerInstance->container_id !== 'pending') {

                // Remove container
                $process = new Process(['docker', 'rm', '-f', $this->containerName]);
                $process->run();

                if (!$process->isSuccessful()) {
                    Log::warning("Failed to remove container", [
                        'execution_id' => $this->execution->id,
                        'container_name' => $this->containerName,
                        'error' => $process->getErrorOutput()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Cleanup error: {$e->getMessage()}", [
                'execution_id' => $this->execution->id
            ]);
        }
    }

    /**
     * Update execution status
     */
    protected function updateExecutionStatus($statusName)
    {
        try {
            $status = ExecutionStatus::where('name', $statusName)->first();

            if (!$status) {
                Log::warning("Status '{$statusName}' not found, using default", [
                    'execution_id' => $this->execution->id
                ]);

                // Try to fallback to a generic status
                $status = ExecutionStatus::where('name', 'failed')->first();

                if (!$status) {
                    throw new \Exception("Could not find status '{$statusName}' or fallback status");
                }
            }

            $data = ['status_id' => $status->id];

            // If this is a completion status, set the end time
            if (in_array($statusName, ['completed', 'failed', 'aborted', 'timeout'])) {
                $data['end_time'] = now();
            }

            $this->execution->update($data);

        } catch (\Exception $e) {
            Log::error("Error updating execution status: {$e->getMessage()}", [
                'execution_id' => $this->execution->id
            ]);
        }
    }
}
