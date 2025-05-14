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
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TestExecutionController extends Controller
{
    use JsonResponse;

    protected TestExecutionService $execService;

    public function __construct(TestExecutionService $execService)
    {
        $this->execService = $execService;
    }

    public function index(Request $request)
    {
        // Get base query with necessary relationships
        $query = TestExecution::with(['testScript', 'initiator', 'environment', 'status'])
            ->orderByDesc('created_at');

        // Apply filters
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            $query->whereHas('status', function ($q) use ($status) {
                $q->where('name', $status);
            });
        }

        if ($request->filled('environment_id') && $request->environment_id !== 'all') {
            $query->where('environment_id', $request->environment_id);
        }

        // Apply date filters
        if ($request->filled('date_filter')) {
            $dateFilter = $request->date_filter;
            $today = now()->startOfDay();

            switch ($dateFilter) {
                case 'today':
                    $query->whereDate('start_time', $today);
                    break;
                case 'yesterday':
                    $query->whereDate('start_time', $today->copy()->subDay());
                    break;
                case 'week':
                    $query->whereBetween('start_time', [
                        $today->copy()->startOfWeek(),
                        $today->copy()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereBetween('start_time', [
                        $today->copy()->startOfMonth(),
                        $today->copy()->endOfMonth()
                    ]);
                    break;
            }
        }

        // Apply search if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('testScript', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('environment', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Get all environments for the filter dropdown
        $environments = Environment::where('is_active', true)->get();

        // Paginate the results
        $executions = $query->paginate(10)->withQueryString();

        return view('dashboard.executions.index', compact('executions', 'environments'));
    }

    public function create()
    {
        // Get test scripts with additional data for better display
        $scripts = TestScript::with(['testCase:id,title', 'creator:id,name'])
            ->get()
            ->map(function ($script) {
                // Keep only the data we need
                return [
                    'id' => $script->id,
                    'name' => $script->name,
                    'framework_type' => $script->framework_type,
                    'test_case' => $script->testCase ? [
                        'id' => $script->testCase->id,
                        'title' => $script->testCase->title
                    ] : null
                ];
            });

        // Get active environments
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

    /**
     * Get more logs for an execution via AJAX.
     */
    public function loadMoreLogs(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'offset' => 'nullable|integer|min:0',
            'limit' => 'nullable|integer|min:10|max:2000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors()
            ], 422);
        }

        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 1000);

        try {
            $data = $this->execService->getMoreLogs($id, $offset, $limit);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the current status of a test execution.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatus($id)
    {
        try {
            $execution = TestExecution::with('status')->findOrFail($id);

            return $this->successResponse([
                'status' => $execution->status->name,
                'end_time' => $execution->end_time,
                'updated_at' => $execution->updated_at
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get execution status: ' . $e->getMessage(), 500);
        }
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
        if ($result['success']) {
            return $this->successResponse([], $result['message']);
        } else {
            return $this->errorResponse($result['message'], 500);
        }
    }

    public function abort(TestExecution $execution)
    {
        $result = $this->execService->abort($execution);
        if (request()->expectsJson()) {
            if ($result['success']) {
                return $this->successResponse([], $result['message']);
            } else {
                return $this->errorResponse($result['message'], 500);
            }
        }
        return redirect()
            ->route('dashboard.executions.show', $execution->id)
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
