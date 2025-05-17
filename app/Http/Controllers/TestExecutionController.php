<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreTestExecutionRequest;
use App\Http\Requests\LoadMoreLogsRequest;
use App\Models\TestExecution;
use App\Models\TestScript;
use App\Models\Environment;
use App\Models\Project;
use App\Services\TestExecutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\Log;
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
        // Get current team
        $team = $this->getCurrentTeam($request);

        // Get projects for this team
        $projects = $team
            ->projects()
            ->orderBy('name')
            ->get(['id', 'name']);
        $selectedProjectId = $request->input('project_id');

        // Initialize empty collections
        $scripts = collect();
        $environments = collect();

        // If a project is selected, get its scripts and environments
        if ($selectedProjectId) {
            // Verify the project belongs to this team
            $project = $projects->firstWhere('id', $selectedProjectId);

            if ($project) {
                // Get test cases for this project, then get scripts
                $testCaseIds = \App\Models\TestCase::whereHas('testSuite', function ($query) use ($selectedProjectId) {
                    $query->where('project_id', $selectedProjectId);
                })->pluck('id');

                $scripts = \App\Models\TestScript::whereIn('test_case_id', $testCaseIds)
                    ->with(['testCase:id,title', 'creator:id,name'])
                    ->get(['id', 'name', 'framework_type', 'test_case_id']);

                // Get environments for this project (both project-specific and global)
                $environments = \App\Models\Environment::where('is_active', true)
                    ->where(function ($query) use ($selectedProjectId) {
                        $query->where('is_global', true)->orWhereHas('projects', function ($q) use ($selectedProjectId) {
                            $q->where('projects.id', $selectedProjectId);
                        });
                    })
                    ->get();
            }
        }

        // Apply filters to test executions query
        $query = TestExecution::with(['testScript', 'initiator', 'environment', 'status'])->orderByDesc('created_at');

        // Apply remaining filters as before...
        if ($request->filled('status')) {
            $query->whereHas('status', function ($q) use ($request) {
                $q->where('name', $request->status);
            });
        }

        if ($request->filled('environment_id')) {
            $query->where('environment_id', $request->environment_id);
        }

        if ($request->filled('script_id')) {
            $query->where('script_id', $request->script_id);
        }

        // Date filter logic remains unchanged
        if ($request->filled('date_filter')) {
            // Existing date filter code...
        }

        // Define status and date filter options for pills
        $statusOptions = ['pending', 'running', 'completed', 'failed', 'aborted'];
        $dateFilterOptions = ['today', 'week', 'month'];

        // Paginate results
        $executions = $query->paginate(10)->withQueryString();

        return view('dashboard.executions.index', compact(
            'executions',
            'environments',
            'scripts',
            'projects',
            'selectedProjectId',
            'statusOptions',
            'dateFilterOptions'
        ));
    }

    /**
     * Show the combined creation and monitoring dashboard.
     */
    public function dashboard(Request $request, $executionId = null)
    {
        // Get current team
        $team = $this->getCurrentTeam($request);

        // Get projects for this team
        $projects = $team
            ->projects()
            ->orderBy('name')
            ->get(['id', 'name']);
        $selectedProjectId = $request->input('project_id');

        // Get active environments
        $environments = Environment::where('is_active', true)->get();

        // Load execution details if an ID is provided
        $execution = null;
        $logs = null;
        $hasMoreLogs = false;
        $logFileExists = false;
        $logFilePath = null;

        if ($executionId) {
            $execution = TestExecution::with(['testScript', 'initiator', 'environment', 'status', 'containers'])
                ->find($executionId);

            if ($execution) {
                ['logs' => $logs, 'hasMore' => $hasMoreLogs] = $this->execService->getRecentLogs($execution);
                $logFileExists = file_exists(storage_path("app/executions/{$execution->id}/execution_log.txt"));
                $logFilePath = $execution->id;
            }
        }

        return view('dashboard.executions.combined', compact(
            'projects',
            'environments',
            'selectedProjectId',
            'execution',
            'logs',
            'hasMoreLogs',
            'logFileExists',
            'logFilePath'
        ));
    }

    public function create(Request $request)
    {
        // Get current team
        $team = $this->getCurrentTeam($request);

        // Get all projects for this team for the project dropdown
        $projects = $team
            ->projects()
            ->orderBy('name')
            ->get(['id', 'name']);

        // Default to empty scripts collection
        $scripts = collect();

        // If project_id is provided, we can pre-select that project
        $selectedProjectId = $request->input('project_id');

        // Get active environments
        $environments = Environment::where('is_active', true)->get();

        return view('dashboard.executions.create', compact('scripts', 'environments', 'projects', 'selectedProjectId'));
    }

    /**
     * Store a newly created test execution.
     */
    public function store(StoreTestExecutionRequest $request)
    {
        try {
            Log::info('TestExecutionController: Received execution request', [
                'user_id' => Auth::id(),
                'validated_data' => $request->validated(),
            ]);

            // Create execution
            $execution = $this->execService->create($request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test execution queued successfully!',
                    'execution' => $execution->load(['testScript', 'initiator', 'environment', 'status'])
                ]);
            }

            return redirect()->route('dashboard.executions.show', $execution->id)
                ->with('success', 'Test execution queued successfully!');
        } catch (\Exception $e) {
            Log::error('TestExecutionController: Failed to create test execution', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create test execution: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create test execution: ' . $e->getMessage());
        }
    }

    /**
     * Get execution data in JSON format.
     */
    public function getJson(TestExecution $execution)
    {
        $execution->load(['testScript', 'initiator', 'environment', 'status', 'containers']);
        ['logs' => $logs, 'hasMore' => $hasMoreLogs] = $this->execService->getRecentLogs($execution);

        return response()->json([
            'success' => true,
            'execution' => $execution,
            'logs' => $logs,
            'hasMoreLogs' => $hasMoreLogs,
            'logOffset' => 0,
            'logFileExists' => file_exists(storage_path("app/executions/{$execution->id}/execution_log.txt")),
        ]);
    }

    public function show(TestExecution $execution)
    {
        $execution->load(['testScript', 'initiator', 'environment', 'status', 'containers']);

        ['logs' => $logs, 'hasMore' => $hasMore] = $this->execService->getRecentLogs($execution);

        $containerStatus = $this->execService->getContainerStatuses($execution);

        return view('dashboard.executions.show', [
            'execution' => $execution,
            'logs' => $logs,
            'hasMoreLogs' => $hasMore,
            'containerStatus' => $containerStatus,
            'logFileExists' => file_exists(storage_path("app/executions/{$execution->id}/execution_log.txt")),
            'logFilePath' => $execution->id,
        ]);
    }

    /**
     * Get more logs for an execution via AJAX.
     */
    public function loadMoreLogs(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'offset' => 'nullable|integer|min:0',
            'limit' => 'nullable|integer|min:10|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Invalid parameters',
                    'errors' => $validator->errors(),
                ],
                422,
            );
        }

        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 1000);

        try {
            $data = $this->execService->getMoreLogs($id, $offset, $limit);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to load logs: ' . $e->getMessage(),
                ],
                500,
            );
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
                'updated_at' => $execution->updated_at,
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
