<?php

namespace App\Services;

use App\Jobs\RunTestExecutionJob;
use App\Models\ExecutionStatus;
use App\Models\TestExecution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TestExecutionService
{
    /**
     * Create and dispatch a new test execution.
     */
    public function create(array $data): TestExecution
{
    Log::info('TestExecutionService: Starting execution creation', [
        'data' => $data
    ]);

    try {
        // Find pending status
        Log::info('TestExecutionService: Finding pending status');
        $pending = ExecutionStatus::where('name', 'pending')->firstOrFail();
        Log::info('TestExecutionService: Found pending status', ['status_id' => $pending->id]);

        // Create execution record
        Log::info('TestExecutionService: Creating execution record');
        $exec = TestExecution::create([
            'script_id'      => $data['script_id'],
            'initiator_id'   => Auth::id(),
            'environment_id' => $data['environment_id'],
            'status_id'      => $pending->id,
            'start_time'     => now(),
        ]);

        Log::info('TestExecutionService: Execution record created', [
            'execution_id' => $exec->id,
            'script_id' => $exec->script_id,
            'initiator_id' => $exec->initiator_id,
            'environment_id' => $exec->environment_id
        ]);

        // Dispatch job
        Log::info('TestExecutionService: Dispatching execution job', [
            'execution_id' => $exec->id,
            'job_class' => RunTestExecutionJob::class
        ]);

        RunTestExecutionJob::dispatch($exec);

        Log::info('TestExecutionService: Job dispatched successfully');

        return $exec;
    } catch (\Exception $e) {
        Log::error('TestExecutionService: Error in execution creation', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'trace' => $e->getTraceAsString()
        ]);

        throw $e; // Re-throw to be caught by the controller
    }
}

    /**
     * Retrieve the recent portion of logs for the execution.
     * Returns [ 'logs' => string, 'hasMore' => bool ].
     */
    public function getRecentLogs(TestExecution $exec, int $maxBytes = 51200): array
    {
        $path = storage_path("app/executions/{$exec->id}/execution_log.txt");
        $hasMore = false;
        $logs    = '';

        if (file_exists($path)) {
            $size = filesize($path);
            if ($size > $maxBytes) {
                $fp = fopen($path, 'r');
                fseek($fp, -$maxBytes, SEEK_END);
                fgets($fp); // skip partial line
                $logs = stream_get_contents($fp);
                fclose($fp);
                $hasMore = true;
            } else {
                $logs = file_get_contents($path);
            }
        } elseif ($exec->s3_results_key && Storage::exists($exec->s3_results_key)) {
            $size = Storage::size($exec->s3_results_key);
            if ($size > $maxBytes) {
                $tmp = tempnam(sys_get_temp_dir(), 'log_');
                Storage::copy($exec->s3_results_key, $tmp);
                $fp = fopen($tmp, 'r');
                fseek($fp, -$maxBytes, SEEK_END);
                fgets($fp);
                $logs = stream_get_contents($fp);
                fclose($fp);
                unlink($tmp);
                $hasMore = true;
            } else {
                $logs = Storage::get($exec->s3_results_key);
            }
        } else {
            $logs = 'No logs available for this execution.';
        }

        return ['logs' => $logs, 'hasMore' => $hasMore];
    }

    /**
     * Paginated log retrieval by line offset.
     * Returns [ 'logs', 'hasMore', 'nextOffset', 'totalLines' ].
     */
    public function getMoreLogs(string $id, int $offset, int $limit): array
    {
        $path = storage_path("app/executions/{$id}/execution_log.txt");
        $lines = [];
        $total = 0;

        if (! file_exists($path)) {
            return ['error' => 'Log file not found'];
        }

        $fp = fopen($path, 'r');
        while (! feof($fp)) { if (fgets($fp) !== false) { $total++; }}
        rewind($fp);

        $pos = 0;
        while ($pos < $offset && ! feof($fp)) { fgets($fp); $pos++; }

        $count = 0;
        while ($count < $limit && ! feof($fp)) {
            $buffer = fgets($fp);
            if ($buffer !== false) {
                $lines[] = $buffer;
                $count++;
            }
        }
        fclose($fp);

        $hasMore = ($offset + $count) < $total;

        return [
            'logs'       => implode('', $lines),
            'hasMore'    => $hasMore,
            'nextOffset' => $offset + $count,
            'totalLines' => $total,
        ];
    }

    /**
     * Inspect each container's status and return an array.
     */
    public function getContainerStatuses(TestExecution $exec): array
    {
        $statuses = [];
        foreach ($exec->containers as $c) {
            $cid = $c->container_id;
            try {
                exec("docker inspect --format='{{.State.Status}}' {$cid} 2>&1", $out, $code);
                $dockerStatus = ($code === 0 && isset($out[0])) ? trim($out[0]) : 'unknown';
            } catch (\Exception $e) {
                $dockerStatus = 'error: ' . $e->getMessage();
            }
            $statuses[$cid] = [
                'db_status'     => $c->status,
                'docker_status' => $dockerStatus,
                'start_time'    => $c->start_time,
                'end_time'      => $c->end_time,
            ];
        }
        return $statuses;
    }

    /**
     * Perform emergency stop via direct SQL and Docker kill.
     */
    public function emergencyStop(string $id): array
    {
        try {
            DB::statement(
                "UPDATE test_executions SET status_id = (SELECT id FROM execution_statuses WHERE name='aborted'), end_time=NOW() WHERE id = ?",
                [$id]
            );
            $cids = DB::table('containers')
                ->where('execution_id', $id)
                ->where('status', 'running')
                ->pluck('container_id');
            foreach ($cids as $cid) {
                exec("docker kill {$cid} 2>&1");
                DB::statement(
                    "UPDATE containers SET status='terminated', end_time=NOW() WHERE container_id = ?",
                    [$cid]
                );
            }
            return ['success' => true, 'message' => 'Emergency stop executed successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Emergency stop failed: ' . $e->getMessage()];
        }
    }

    /**
     * Abort a running execution and its container.
     */
    public function abort(TestExecution $exec): array
    {
        if (! $exec->isRunning()) {
            return ['success' => false, 'message' => 'Cannot abort a non-running execution.'];
        }
        try {
            $container = $exec->containers()->where('status', 'running')->first();
            if ($container) {
                $cid = $container->container_id;
                exec("docker stop {$cid} 2>&1", $out, $code);
                if ($code !== 0) {
                    Log::error('Failed to stop container: ' . implode("\n", $out));
                }
            }
            $aborted = ExecutionStatus::where('name','aborted')->firstOrFail();
            $exec->update([ 'status_id' => $aborted->id, 'end_time' => now() ]);
            return ['success' => true, 'message' => 'Test execution aborted.'];
        } catch (\Exception $e) {
            Log::error('Abort failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to abort: ' . $e->getMessage()];
        }
    }
}
