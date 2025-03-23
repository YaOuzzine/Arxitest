@extends('layouts.app')

@section('content')
<div id="execution-status" data-execution-id="{{ $testExecution->id }}" data-status="{{ $testExecution->executionStatus->name }}" class="hidden"></div>
<div class="container mx-auto px-4 py-6">
    <!-- Navigation -->
    <div class="flex items-center text-sm text-gray-500 mb-4">
        <a href="{{ route('test-executions.index') }}" class="hover:text-blue-600">Test Executions</a>
        <svg class="h-4 w-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-gray-700">Execution #{{ substr($testExecution->id, 0, 8) }}</span>
    </div>

    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                {{ $testExecution->testScript->name ?? 'Unknown Script' }}
            </h1>
            <p class="text-gray-600">
                Execution started {{ $testExecution->start_time ? $testExecution->start_time->format('M d, Y \a\t h:i A') : 'N/A' }}
                by {{ $testExecution->initiator->name ?? 'Unknown' }}
            </p>
        </div>
        <div class="flex space-x-3">
            @if($testExecution->s3_results_key)
                <a href="{{ route('api.test-executions.download-results', $testExecution->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Download Results
                </a>
            @endif

            @if($testExecution->executionStatus->name == 'Failed' && $testExecution->end_time)
                <a href="{{ route('test-executions.create', ['script_id' => $testExecution->script_id, 'env_id' => $testExecution->environment_id]) }}" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                    </svg>
                    Rerun Test
                </a>
            @elseif($testExecution->executionStatus->name == 'Passed' && $testExecution->end_time)
                <a href="{{ route('test-executions.create', ['script_id' => $testExecution->script_id, 'env_id' => $testExecution->environment_id]) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                    </svg>
                    Run Again
                </a>
            @elseif($testExecution->executionStatus->name == 'Running')
                <form method="POST" action="{{ route('test-executions.destroy', $testExecution->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg flex items-center" onclick="return confirm('Are you sure you want to cancel this execution?')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        Cancel Execution
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Execution Status -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 flex justify-between items-center border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-800">
                @if($testExecution->executionStatus->name == 'Running')
                    <div class="flex items-center">
                        <div class="mr-2 animate-pulse">
                            <div class="h-3 w-3 rounded-full bg-blue-500"></div>
                        </div>
                        <span>Execution in Progress</span>
                    </div>
                @else
                    Execution Status
                @endif
            </h2>
            <div>
                @if($testExecution->executionStatus->name == 'Passed')
                    <span id="status-badge" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        Passed
                    </span>
                @elseif($testExecution->executionStatus->name == 'Failed')
                    <span id="status-badge" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                        Failed
                    </span>
                @elseif($testExecution->executionStatus->name == 'Running')
                    <span id="status-badge" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                        Running
                    </span>
                @else
                    <span id="status-badge" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                        {{ $testExecution->executionStatus->name }}
                    </span>
                @endif
            </div>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">Test Script</h3>
                <p class="text-sm text-gray-900">
                    <a href="{{ route('test-scripts.show', $testExecution->testScript->id ?? '') }}" class="text-blue-600 hover:underline">
                        {{ $testExecution->testScript->name ?? 'Unknown' }}
                    </a>
                </p>
                <p class="text-xs text-gray-500">
                    {{ $testExecution->testScript->testSuite->name ?? '' }} /
                    {{ $testExecution->testScript->testSuite->project->name ?? '' }}
                </p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">Environment</h3>
                <p class="text-sm text-gray-900">{{ $testExecution->environment->name ?? 'Unknown' }}</p>
                <p class="text-xs text-gray-500">
                    @if($testExecution->environment && isset($testExecution->environment->configuration['type']))
                        {{ $testExecution->environment->configuration['type'] }}
                    @endif
                </p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">Duration</h3>
                <p class="text-sm text-gray-900">
                    @if($testExecution->start_time && $testExecution->end_time)
                        {{ $testExecution->start_time->diffInSeconds($testExecution->end_time) }} seconds
                    @elseif($testExecution->start_time && !$testExecution->end_time)
                        <span id="running-duration" data-start="{{ $testExecution->start_time->timestamp }}">
                            Calculating...
                        </span>
                    @else
                        N/A
                    @endif
                </p>
                <p class="text-xs text-gray-500">
                    Started: {{ $testExecution->start_time ? $testExecution->start_time->format('M d, Y h:i:s A') : 'N/A' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Container Information -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-800">Containers</h2>
        </div>

        @if($testExecution->containers->isEmpty())
            <div class="p-6 text-center">
                <p class="text-gray-500">No containers were used for this execution.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Container ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resources</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logs</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($testExecution->containers as $container)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                    {{ substr($container->container_id, 0, 12) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($container->status == 'running')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Running
                                        </span>
                                    @elseif($container->status == 'completed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                    @elseif($container->status == 'failed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Failed
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ ucfirst($container->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $container->start_time ? $container->start_time->format('M d, Y H:i:s') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($container->start_time && $container->end_time)
                                        {{ $container->start_time->diffInSeconds($container->end_time) }}s
                                    @elseif($container->start_time && !$container->end_time)
                                        <span class="text-blue-600 animate-pulse">In progress</span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if(isset($container->configuration['resources']))
                                        <span title="CPU / Memory">{{ $container->configuration['resources']['cpu'] ?? 'N/A' }} / {{ $container->configuration['resources']['memory'] ?? 'N/A' }}</span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if($container->s3_logs_key)
                                        <button type="button" onclick="showContainerLogs('{{ $container->id }}')" class="text-indigo-600 hover:text-indigo-900">
                                            View Logs
                                        </button>
                                    @else
                                        <span class="text-gray-400">No logs</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Resource Metrics -->
    @if($testExecution->containers->isNotEmpty() && $testExecution->containers->first()->resourceMetrics->isNotEmpty())
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-800">Resource Utilization</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">CPU Usage</h3>
                        <div class="h-64 bg-gray-100 rounded-md p-4">
                            <!-- CPU Chart placeholder - in a real app, you'd render a chart here -->
                            <div class="flex items-center justify-center h-full text-gray-500">
                                CPU usage chart would render here
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Memory Usage</h3>
                        <div class="h-64 bg-gray-100 rounded-md p-4">
                            <!-- Memory Chart placeholder - in a real app, you'd render a chart here -->
                            <div class="flex items-center justify-center h-full text-gray-500">
                                Memory usage chart would render here
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500">
                    <p>Charts display resource metrics collected during test execution. This helps optimize container sizing and identify performance bottlenecks.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Test Results -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-800">Test Results</h2>
        </div>

        @if($testExecution->executionStatus->name == 'Running')
            <div class="p-6 text-center">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-500 mx-auto mb-4"></div>
                <p class="text-gray-700">Test is currently running...</p>
                <p class="text-sm text-gray-500 mt-2">Results will be available when the test completes.</p>
            </div>
        @elseif(!$testExecution->s3_results_key)
            <div class="p-6 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-700">No results available</p>
            </div>
        @else
            <div class="p-6">
                <!-- In a real application, you would fetch and display the results from S3 -->
                <div class="border border-gray-200 rounded-md p-4 mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-sm font-medium text-gray-700">Test Summary</h3>
                        <span class="{{ $testExecution->executionStatus->name == 'Passed' ? 'text-green-600' : 'text-red-600' }} font-medium">
                            {{ $testExecution->executionStatus->name }}
                        </span>
                    </div>
                    <div class="text-sm text-gray-600">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                            <div class="p-2 border border-gray-100 rounded-md">
                                <div class="text-gray-500 text-xs">Total Tests</div>
                                <div class="font-bold text-lg">10</div>
                            </div>
                            <div class="p-2 border border-gray-100 rounded-md">
                                <div class="text-gray-500 text-xs">Passed</div>
                                <div class="font-bold text-lg text-green-600">8</div>
                            </div>
                            <div class="p-2 border border-gray-100 rounded-md">
                                <div class="text-gray-500 text-xs">Failed</div>
                                <div class="font-bold text-lg text-red-600">2</div>
                            </div>
                            <div class="p-2 border border-gray-100 rounded-md">
                                <div class="text-gray-500 text-xs">Duration</div>
                                <div class="font-bold text-lg">45s</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Cases -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Test Cases</h3>

                    <div class="border border-gray-200 rounded-md overflow-hidden">
                        <div class="divide-y divide-gray-200">
                            <!-- Test Case 1 -->
                            <div class="p-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 pt-1">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-green-100 text-green-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-medium text-gray-900">test_user_login_success</h4>
                                            <div class="text-sm text-gray-500">2.3s</div>
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500">
                                            Verifies that a user can successfully login with valid credentials
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Test Case 2 -->
                            <div class="p-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 pt-1">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-100 text-red-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-medium text-gray-900">test_project_creation_with_integration</h4>
                                            <div class="text-sm text-gray-500">1.8s</div>
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500">
                                            Verifies that a new project can be created with Jira integration
                                        </div>
                                        <div class="mt-2 p-2 bg-red-50 border border-red-100 rounded-md text-xs text-red-600 overflow-x-auto">
                                            <p class="font-mono">Error: Expected status code 201 but got 403. Access denied when creating integration.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- More test cases would be here -->
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Container Logs Modal -->
<div id="logs-modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900" id="logs-modal-title">Container Logs</h3>
            <button type="button" onclick="hideLogsModal()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-6 max-h-96 overflow-y-auto">
            <pre id="container-logs" class="bg-gray-800 text-white p-4 rounded-md text-sm overflow-x-auto whitespace-pre-wrap h-64">Loading logs...</pre>
        </div>
        <div class="px-6 py-4 bg-gray-50 flex justify-end rounded-b-lg">
            <button type="button" onclick="hideLogsModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg">
                Close
            </button>
            <button type="button" onclick="downloadLogs()" class="ml-2 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                Download Logs
            </button>
        </div>
    </div>
</div>

<script>
    // Update running duration in real-time
    const runningDuration = document.getElementById('running-duration');
    if (runningDuration) {
        const startTime = parseInt(runningDuration.getAttribute('data-start'));

        function updateDuration() {
            const now = Math.floor(Date.now() / 1000);
            const seconds = now - startTime;
            runningDuration.textContent = seconds + ' seconds';
        }

        // Initial update
        updateDuration();

        // Update every second
        setInterval(updateDuration, 1000);
    }

    // Container logs modal
    function showContainerLogs(containerId) {
    document.getElementById('logs-modal').classList.remove('hidden');
    document.getElementById('logs-modal-title').textContent = 'Container Logs';
    document.getElementById('container-logs').textContent = 'Loading logs...';

    // Fetch logs from the API
    fetch(`/api/containers/${containerId}/logs`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('container-logs').textContent = data.logs || 'No logs available';
        })
        .catch(error => {
            document.getElementById('container-logs').textContent = 'Error loading logs: ' + error.message;
        });
    }

    function hideLogsModal() {
        document.getElementById('logs-modal').classList.add('hidden');
    }

    function downloadLogs() {
        const logs = document.getElementById('container-logs').textContent;
        const blob = new Blob([logs], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'container-logs.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    // Close modal when clicking outside
    document.getElementById('logs-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideLogsModal();
        }
    });
</script>
@endsection
