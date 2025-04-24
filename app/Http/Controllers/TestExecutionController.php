<?php

namespace App\Http\Controllers;

use App\Jobs\RunTestExecutionJob;
use App\Models\Container;
use App\Models\Environment;
use App\Models\ExecutionStatus;
use App\Models\TestExecution;
use App\Models\TestScript;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TestExecutionController extends Controller
{
    /**
     * Display a list of test executions.
     */
    public function index(Request $request)
    {
        $query = TestExecution::with(['testScript', 'initiator', 'environment', 'status'])
            ->orderBy('created_at', 'desc');

        // Add filtering options if needed
        if ($request->has('script_id')) {
            $query->where('script_id', $request->script_id);
        }

        $executions = $query->paginate(10);

        return view('dashboard.executions.index', compact('executions'));
    }

    /**
     * Show the form for creating a new test execution.
     */
    public function create()
    {
        $scripts = TestScript::with('testCase')->get();
        $environments = Environment::where('is_active', true)->get();

        return view('dashboard.executions.create', compact('scripts', 'environments'));
    }

    /**
     * Start a new test execution.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'script_id' => 'required|exists:test_scripts,id',
            'environment_id' => 'required|exists:environments,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Get pending status
            $pendingStatus = ExecutionStatus::where('name', 'pending')->firstOrFail();

            // Create the execution record
            $execution = TestExecution::create([
                'script_id' => $request->script_id,
                'initiator_id' => Auth::id(),
                'environment_id' => $request->environment_id,
                'status_id' => $pendingStatus->id,
                'start_time' => now(),
            ]);

            // Dispatch the job to queue
            RunTestExecutionJob::dispatch($execution);

            return redirect()->route('dashboard.executions.show', $execution->id)
                ->with('success', 'Test execution queued successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to create test execution: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to create test execution: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(TestExecution $execution)
    {
        $execution->load(['testScript', 'initiator', 'environment', 'status', 'containers']);

        // Get only the last X lines of logs (e.g., 500 lines)
        $logs = "";
        $logPath = storage_path("app/executions/{$execution->id}/execution_log.txt");

        if (file_exists($logPath)) {
            // Get file size
            $fileSize = filesize($logPath);

            if ($fileSize > 0) {
                // For large files, get only the last portion
                $maxBytes = 50 * 1024; // 50KB of logs initially

                if ($fileSize > $maxBytes) {
                    // Read only the last part of the file
                    $fp = fopen($logPath, 'r');
                    fseek($fp, -$maxBytes, SEEK_END);
                    // Skip the first line (which might be incomplete)
                    fgets($fp);
                    // Read the rest
                    $logs = stream_get_contents($fp);
                    fclose($fp);

                    // Mark that there are more logs to load
                    $hasMoreLogs = true;
                } else {
                    // File is small enough to read entirely
                    $logs = file_get_contents($logPath);
                    $hasMoreLogs = false;
                }
            } else {
                $logs = "Log file exists but is empty.";
                $hasMoreLogs = false;
            }
        } elseif ($execution->s3_results_key) {
            // Try to get from storage, but limit size
            if (Storage::exists($execution->s3_results_key)) {
                // Get file size in storage
                $fileSize = Storage::size($execution->s3_results_key);
                $maxBytes = 50 * 1024; // 50KB

                if ($fileSize > $maxBytes) {
                    // For large S3 files, we need a different approach
                    // This is a simplified example - actual implementation might vary
                    $tempFile = tempnam(sys_get_temp_dir(), 'log_');
                    Storage::copy($execution->s3_results_key, $tempFile);

                    $fp = fopen($tempFile, 'r');
                    fseek($fp, -$maxBytes, SEEK_END);
                    fgets($fp); // Skip potentially incomplete line
                    $logs = stream_get_contents($fp);
                    fclose($fp);
                    unlink($tempFile);

                    $hasMoreLogs = true;
                } else {
                    $logs = Storage::get($execution->s3_results_key);
                    $hasMoreLogs = false;
                }
            } else {
                $logs = "Logs not available in storage.";
                $hasMoreLogs = false;
            }
        } else {
            $logs = "No logs available for this execution.";
            $hasMoreLogs = false;
        }

        // Check container status more directly
        $containers = $execution->containers;
        $containerStatus = [];

        foreach ($containers as $container) {
            $containerId = $container->container_id;

            // Try to get the actual Docker container status
            try {
                exec("docker inspect --format='{{.State.Status}}' {$containerId} 2>&1", $statusOutput, $statusCode);
                $dockerStatus = ($statusCode === 0 && isset($statusOutput[0])) ?
                    trim($statusOutput[0]) :
                    'unknown';

                $containerStatus[$containerId] = [
                    'db_status' => $container->status,
                    'docker_status' => $dockerStatus,
                    'start_time' => $container->start_time,
                    'end_time' => $container->end_time,
                ];
            } catch (\Exception $e) {
                $containerStatus[$containerId] = [
                    'db_status' => $container->status,
                    'docker_status' => 'error: ' . $e->getMessage(),
                    'start_time' => $container->start_time,
                    'end_time' => $container->end_time,
                ];
            }
        }

        return view('dashboard.executions.show', [
            'execution' => $execution,
            'logs' => $logs,
            'hasMoreLogs' => $hasMoreLogs ?? false,
            'containerStatus' => $containerStatus,
            'logFileExists' => file_exists($logPath),
            'logFilePath' => $execution->id // Just pass the ID for the AJAX endpoint
        ]);
    }

    /**
     * Load more logs via AJAX.
     */
    public function loadMoreLogs(Request $request, $id)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 1000); // Number of lines to load

        // Cap the limit to prevent memory issues
        $limit = min($limit, 2000);

        $logPath = storage_path("app/executions/{$id}/execution_log.txt");

        if (!file_exists($logPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Log file not found'
            ]);
        }

        // Get lines from file with offset
        $lines = [];
        $lineCount = 0;
        $currentPosition = 0;
        $fp = fopen($logPath, 'r');

        // Count total lines for pagination info
        $totalLines = 0;
        while (!feof($fp)) {
            $buffer = fgets($fp);
            if ($buffer !== false) {
                $totalLines++;
            }
        }

        // Reset file pointer
        rewind($fp);

        // Skip to the requested offset
        while ($currentPosition < $offset && !feof($fp)) {
            fgets($fp);
            $currentPosition++;
        }

        // Read the requested number of lines
        while ($lineCount < $limit && !feof($fp)) {
            $buffer = fgets($fp);
            if ($buffer !== false) {
                $lines[] = $buffer;
                $lineCount++;
            }
        }

        fclose($fp);

        // Check if there are more logs to load
        $hasMore = ($offset + $lineCount) < $totalLines;

        return response()->json([
            'success' => true,
            'logs' => implode('', $lines),
            'hasMore' => $hasMore,
            'nextOffset' => $offset + $lineCount,
            'totalLines' => $totalLines
        ]);
    }

    // In app/Http/Controllers/TestExecutionController.php

    /**
     * Emergency stop for a test execution, usable even when the web UI is unresponsive.
     */
    public function emergencyStop($id)
    {
        try {

            // Direct database update to minimize memory usage
            DB::statement("
            UPDATE test_executions
            SET status_id = (SELECT id FROM execution_statuses WHERE name = 'aborted'),
                end_time = NOW()
            WHERE id = ?
        ", [$id]);

            // Get container IDs directly with minimal overhead
            $containerIds = DB::table('containers')
                ->where('execution_id', $id)
                ->where('status', 'running')
                ->pluck('container_id');

            foreach ($containerIds as $containerId) {
                // Force kill the container
                exec("docker kill {$containerId} 2>&1");

                // Update container status with direct query
                DB::statement("
                UPDATE containers
                SET status = 'terminated', end_time = NOW()
                WHERE container_id = ?
            ", [$containerId]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Emergency stop executed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Emergency stop failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Abort a running test execution.
     */
    public function abort(TestExecution $execution)
    {
        // Only abort if it's running
        if ($execution->isRunning()) {
            try {
                // Find the running container
                $container = $execution->containers()
                    ->where('status', Container::RUNNING)
                    ->first();

                if ($container) {
                    // Execute docker stop command
                    $containerId = $container->container_id;
                    exec("docker stop {$containerId} 2>&1", $output, $exitCode);

                    if ($exitCode !== 0) {
                        Log::error('Failed to stop container: ' . implode("\n", $output));
                    }
                }

                // Update execution status
                $abortedStatus = ExecutionStatus::where('name', 'aborted')->firstOrFail();
                $execution->status_id = $abortedStatus->id;
                $execution->end_time = now();
                $execution->save();

                return redirect()->route('dashboard.executions.show', $execution->id)
                    ->with('success', 'Test execution aborted.');
            } catch (\Exception $e) {
                Log::error('Failed to abort test execution: ' . $e->getMessage());

                return redirect()->route('dashboard.executions.show', $execution->id)
                    ->with('error', 'Failed to abort test execution: ' . $e->getMessage());
            }
        }

        return redirect()->route('dashboard.executions.show', $execution->id)
            ->with('error', 'Cannot abort test execution that is not running.');
    }
}
