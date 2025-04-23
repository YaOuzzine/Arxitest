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
use Illuminate\Support\Facades\Log;
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

    /**
     * Display a specific test execution.
     */
    public function show(TestExecution $execution)
    {
        $execution->load(['testScript', 'initiator', 'environment', 'status', 'containers']);

        return view('dashboard.executions.show', compact('execution'));
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
