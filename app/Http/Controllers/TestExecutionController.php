<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTestExecutionRequest;
use App\Http\Requests\LoadMoreLogsRequest;
use App\Models\TestExecution;
use App\Models\TestScript;
use App\Models\Environment;
use App\Services\TestExecutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestExecutionController extends Controller
{
    protected TestExecutionService $execService;

    public function __construct(TestExecutionService $execService)
    {
        $this->execService = $execService;
    }

    public function index(Request $request)
    {
        $query = TestExecution::with(['testScript', 'initiator', 'environment', 'status'])
            ->orderByDesc('created_at');

        if ($request->filled('script_id')) {
            $query->where('script_id', $request->script_id);
        }

        $executions = $query->paginate(10);
        return view('dashboard.executions.index', compact('executions'));
    }

    public function create()
    {
        $scripts      = TestScript::with('testCase')->get();
        $environments = Environment::where('is_active', true)->get();
        return view('dashboard.executions.create', compact('scripts', 'environments'));
    }

    public function store(StoreTestExecutionRequest $request)
    {
        $execution = $this->execService->create($request->validated());
        return redirect()
            ->route('dashboard.executions.show', $execution->id)
            ->with('success', 'Test execution queued successfully!');
    }

    public function show(TestExecution $execution)
    {
        $execution->load(['testScript', 'initiator', 'environment', 'status', 'containers']);

        ['logs' => $logs, 'hasMore' => $hasMore] =
            $this->execService->getRecentLogs($execution);

        $containerStatus =
            $this->execService->getContainerStatuses($execution);

        return view('dashboard.executions.show', [
            'execution'      => $execution,
            'logs'           => $logs,
            'hasMoreLogs'    => $hasMore,
            'containerStatus' => $containerStatus,
            'logFileExists'  => file_exists(storage_path("app/executions/{$execution->id}/execution_log.txt")),
            'logFilePath'    => $execution->id,
        ]);
    }

    public function loadMoreLogs(LoadMoreLogsRequest $request, $id)
    {
        $data = $this->execService->getMoreLogs(
            $id,
            $request->input('offset', 0),
            $request->input('limit', 1000)
        );
        return response()->json($data);
    }

    public function downloadLogs(TestExecution $execution)
    {
        // Check if the logs file exists
        $logPath = storage_path("app/executions/{$execution->id}/execution_log.txt");

        if (!file_exists($logPath)) {
            // Check if logs are in S3/storage instead
            if ($execution->s3_results_key && Storage::exists($execution->s3_results_key)) {
                return Storage::download($execution->s3_results_key, "execution_{$execution->id}_logs.txt");
            }

            // No logs found
            return redirect()->back()->with('error', 'No logs found for this execution.');
        }

        // Stream the logs file for download
        return response()->download($logPath, "execution_{$execution->id}_logs.txt");
    }

    public function emergencyStop($id)
    {
        $result = $this->execService->emergencyStop($id);
        return response()->json($result, $result['success'] ? 200 : 500);
    }

    public function abort(TestExecution $execution)
    {
        $result = $this->execService->abort($execution);
        if (request()->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 500);
        }
        return redirect()
            ->route('dashboard.executions.show', $execution->id)
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
