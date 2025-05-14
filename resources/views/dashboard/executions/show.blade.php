<!-- resources/views/dashboard/executions/show.blade.php -->
@extends('layouts.dashboard')

@section('title', 'Execution Details')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.executions.index') }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
            Test Executions
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">{{ substr($execution->id, 0, 8) }}</span>
    </li>
@endsection

@section('content')
<div class="space-y-6" x-data="executionDetails({
    executionId: '{{ $execution->id }}',
    statusName: '{{ $execution->status->name ?? 'unknown' }}',
    isRunning: {{ json_encode($execution->isRunning() ?? false) }},
    logs: {{ json_encode($logs) }},
    hasMoreLogs: {{ json_encode($hasMoreLogs) }},
    logOffset: 0,
    logFileExists: {{ json_encode($logFileExists) }},
    logFilePath: '{{ $logFilePath }}',
    refreshInterval: 5000
})">
    <!-- Execution Header -->
    <div class="bg-white dark:bg-zinc-800 shadow-md rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold text-zinc-900 dark:text-white">
                    Test Execution: {{ substr($execution->id, 0, 8) }}
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Script: {{ $execution->testScript->name ?? 'Unknown Script' }}
                </p>
            </div>
            <div>
                <!-- Status Badge -->
                <span x-show="statusName === 'pending'" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                    Pending
                </span>
                <span x-show="statusName === 'running'" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Running
                </span>
                <span x-show="statusName === 'completed'" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                    Completed
                </span>
                <span x-show="statusName === 'failed'" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                    Failed
                </span>
                <span x-show="statusName === 'aborted'" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400">
                    Aborted
                </span>
                <span x-show="!['pending', 'running', 'completed', 'failed', 'aborted'].includes(statusName)" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300">
                    {{ $execution->status->name ?? 'Unknown' }}
                </span>
            </div>
        </div>

        <!-- Execution Details -->
        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="space-y-4">
                <h2 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Details</h2>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Initiator:</dt>
                        <dd class="text-sm text-zinc-900 dark:text-zinc-200">{{ $execution->initiator->name ?? 'System' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Environment:</dt>
                        <dd class="text-sm text-zinc-900 dark:text-zinc-200">{{ $execution->environment->name ?? 'Unknown' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Start Time:</dt>
                        <dd class="text-sm text-zinc-900 dark:text-zinc-200">{{ $execution->start_time ? $execution->start_time->format('M j, Y g:i:s A') : 'Not started' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">End Time:</dt>
                        <dd class="text-sm text-zinc-900 dark:text-zinc-200" x-ref="endTimeDisplay">{{ $execution->end_time ? $execution->end_time->format('M j, Y g:i:s A') : 'Running...' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Duration:</dt>
                        <dd class="text-sm text-zinc-900 dark:text-zinc-200" x-ref="durationDisplay">
                            <span x-show="!isRunning">{{ $execution->duration ? gmdate('H:i:s', $execution->duration) : '-' }}</span>
                            <span x-show="isRunning" class="text-blue-600 dark:text-blue-400">
                                Running... <span x-ref="liveTimer"></span>
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="space-y-4 md:col-span-2">
                <h2 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Containers</h2>
                <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-600/50">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-100 dark:bg-zinc-800">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Container ID</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Started</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Duration</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($execution->containers as $container)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-200">{{ substr($container->container_id, 0, 12) }}</td>
                                    <td class="px-4 py-3">
                                        @if($container->status === 'running')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                                Running
                                            </span>
                                        @elseif($container->status === 'completed')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                Completed
                                            </span>
                                        @elseif($container->status === 'failed')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                                Failed
                                            </span>
                                        @elseif($container->status === 'terminated')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400">
                                                Terminated
                                            </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300">
                                                {{ ucfirst($container->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $container->start_time ? $container->start_time->diffForHumans() : 'Not started' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                        @if($container->start_time && $container->end_time)
                                            {{ gmdate('H:i:s', $container->start_time->diffInSeconds($container->end_time)) }}
                                        @elseif($container->status === 'running')
                                            Running...
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400 text-center">
                                        No containers provisioned yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex flex-wrap gap-3 justify-end">
            @if($execution->isRunning())
                <button @click="abortExecution()" class="px-3 py-2 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-md hover:bg-red-200 dark:hover:bg-red-800/50 inline-flex items-center">
                    <i data-lucide="square" class="w-4 h-4 mr-2"></i> Abort Execution
                </button>
            @endif
            <a href="{{ route('dashboard.executions.logs.download', $execution->id) }}" class="px-3 py-2 bg-zinc-100 text-zinc-700 dark:bg-zinc-700/30 dark:text-zinc-300 rounded-md hover:bg-zinc-200 dark:hover:bg-zinc-700/50 inline-flex items-center" x-bind:class="{ 'opacity-50 pointer-events-none': !logFileExists }">
                <i data-lucide="download" class="w-4 h-4 mr-2"></i> Download Logs
            </a>
            <a href="{{ route('dashboard.executions.index') }}" class="px-3 py-2 bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 rounded-md hover:bg-indigo-200 dark:hover:bg-indigo-800/50 inline-flex items-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Executions
            </a>
        </div>
    </div>

    <!-- Execution Logs -->
    <div class="bg-white dark:bg-zinc-800 shadow-md rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
            <h2 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">
                Execution Logs
            </h2>
            <div class="flex items-center space-x-2">
                <div x-show="isRunning" class="text-xs text-zinc-500 dark:text-zinc-400">
                    Auto-refreshing logs...
                </div>
                <button @click="refreshLogs" class="px-2 py-1 bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 rounded hover:bg-indigo-200 dark:hover:bg-indigo-800/50 inline-flex items-center text-xs">
                    <i data-lucide="refresh-cw" class="w-3 h-3 mr-1"></i> Refresh
                </button>
            </div>
        </div>

        <div class="p-6">
            <!-- Log Display -->
            <div class="bg-zinc-900 rounded-lg overflow-hidden text-white font-mono text-sm">
                <div class="flex bg-zinc-800 px-4 py-2 border-b border-zinc-700 justify-between items-center">
                    <div class="flex items-center">
                        <i data-lucide="terminal" class="w-4 h-4 mr-2 text-zinc-400"></i>
                        <span class="text-zinc-200">Test Execution Log</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button @click="clearLogs" class="text-zinc-400 hover:text-zinc-200 transition-colors">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <div class="relative">
                    <pre id="log-container" x-ref="logContainer" class="p-4 overflow-auto max-h-96 text-zinc-300 whitespace-pre-wrap" style="font-size: 0.85rem; line-height: 1.5;" x-html="formattedLogs"></pre>

                    <!-- Loading Overlay -->
                    <div x-show="isLoadingLogs" class="absolute inset-0 bg-zinc-900/80 flex items-center justify-center">
                        <div class="flex flex-col items-center">
                            <svg class="animate-spin h-8 w-8 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="mt-2 text-indigo-400">Loading logs...</span>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div x-show="!isLoadingLogs && (!logs || logs.trim() === '')" class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <i data-lucide="file-text" class="h-12 w-12 text-zinc-700 mx-auto mb-3"></i>
                            <p class="text-zinc-500">No logs available yet.</p>
                            <p class="text-zinc-600 text-xs mt-1" x-show="isRunning">Logs will appear as execution progresses.</p>
                        </div>
                    </div>
                </div>

                <!-- Load More Button -->
                <div x-show="hasMoreLogs" class="px-4 py-2 bg-zinc-800 border-t border-zinc-700 flex justify-center">
                    <button @click="loadMoreLogs" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors" x-bind:disabled="isLoadingMore">
                        <span x-show="!isLoadingMore">Load more logs</span>
                        <span x-show="isLoadingMore">Loading...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Resource Metrics -->
    <div class="bg-white dark:bg-zinc-800 shadow-md rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700" x-show="hasResourceMetrics">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h2 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">
                Resource Metrics
            </h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- CPU Usage Chart -->
                <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-600/50">
                    <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-4">CPU Usage</h3>
                    <div class="h-64">
                        <canvas id="cpu-chart"></canvas>
                    </div>
                </div>

                <!-- Memory Usage Chart -->
                <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-600/50">
                    <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-4">Memory Usage</h3>
                    <div class="h-64">
                        <canvas id="memory-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Abort Execution Confirmation Modal -->
<div x-data="{ show: false }" x-show="show" @open-abort-modal.window="show = true" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-zinc-900/60 dark:bg-zinc-900/80" aria-hidden="true" @click="show = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-zinc-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="px-4 pt-5 pb-4 bg-white dark:bg-zinc-800 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                        <i data-lucide="alert-triangle" class="w-6 h-6 text-red-600 dark:text-red-400"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg font-medium leading-6 text-zinc-900 dark:text-zinc-100" id="modal-title">
                            Abort Execution
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Are you sure you want to abort this test execution? This action cannot be undone and may result in incomplete test results.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-4 py-3 bg-zinc-50 dark:bg-zinc-800/50 sm:px-6 sm:flex sm:flex-row-reverse border-t border-zinc-200 dark:border-zinc-700">
                <button type="button" @click="$dispatch('confirm-abort'); show = false" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Abort
                </button>
                <button type="button" @click="show = false" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm hover:bg-zinc-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    function executionDetails(config) {
        return {
            executionId: config.executionId,
            statusName: config.statusName,
            isRunning: config.isRunning,
            logs: config.logs || '',
            hasMoreLogs: config.hasMoreLogs || false,
            logOffset: config.logOffset || 0,
            logFileExists: config.logFileExists || false,
            logFilePath: config.logFilePath || '',
            refreshInterval: config.refreshInterval || 5000,
            pollingInterval: null,
            timerInterval: null,
            isLoadingLogs: false,
            isLoadingMore: false,
            hasResourceMetrics: false,
            cpuChart: null,
            memoryChart: null,

            get formattedLogs() {
                if (!this.logs) return '';

                // Color coding based on log level
                return this.logs
                    .replace(/\[ERROR\].*$/gm, '<span class="text-red-400">$&</span>')
                    .replace(/\[WARN\].*$/gm, '<span class="text-yellow-400">$&</span>')
                    .replace(/\[INFO\].*$/gm, '<span class="text-blue-400">$&</span>')
                    .replace(/\[DEBUG\].*$/gm, '<span class="text-green-400">$&</span>')
                    .replace(/\[PASS\].*$/gm, '<span class="text-green-500">$&</span>')
                    .replace(/\[FAIL\].*$/gm, '<span class="text-red-500">$&</span>');
            },

            init() {
                // Initialize log container and scrolling
                this.$nextTick(() => {
                    this.scrollLogsToBottom();

                    // Start polling if execution is running
                    if (this.isRunning) {
                        this.startPolling();
                    }

                    // Try to load resource metrics if available
                    this.loadResourceMetrics();
                });

                // Listen for abort confirmation
                this.$el.addEventListener('confirm-abort', () => {
                    this.confirmAbort();
                });
            },

            startPolling() {
                // Clear any existing intervals
                this.clearIntervals();

                // Set up polling for status updates
                this.pollingInterval = setInterval(() => {
                    this.checkStatus();
                    this.refreshLogs();
                }, this.refreshInterval);

                // Set up timer for live duration updates
                const startTime = new Date("{{ $execution->start_time ?? now() }}");

                this.timerInterval = setInterval(() => {
                    const now = new Date();
                    const diffInSeconds = Math.floor((now - startTime) / 1000);

                    const hours = Math.floor(diffInSeconds / 3600);
                    const minutes = Math.floor((diffInSeconds % 3600) / 60);
                    const seconds = diffInSeconds % 60;

                    const formattedTime = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                    const liveTimer = this.$refs.liveTimer;
                    if (liveTimer) {
                        liveTimer.textContent = formattedTime;
                    }
                }, 1000);
            },

            clearIntervals() {
                if (this.pollingInterval) {
                    clearInterval(this.pollingInterval);
                    this.pollingInterval = null;
                }

                if (this.timerInterval) {
                    clearInterval(this.timerInterval);
                    this.timerInterval = null;
                }
            },

            async checkStatus() {
                try {
                    const response = await fetch(`/api/executions/${this.executionId}/status`);
                    if (!response.ok) throw new Error('Failed to fetch status');

                    const data = await response.json();
                    if (!data.success) throw new Error(data.message || 'Failed to get status');

                    // Update status
                    this.statusName = data.data.status.name;
                    this.isRunning = data.data.status.name === 'running';

                    // Update end time and duration if execution is complete
                    if (data.data.end_time && !this.isRunning) {
                        const endTimeDisplay = this.$refs.endTimeDisplay;
                        const durationDisplay = this.$refs.durationDisplay;

                        if (endTimeDisplay) {
                            // Format the end time for display
                            const endDate = new Date(data.data.end_time);
                            endTimeDisplay.textContent = endDate.toLocaleString();
                        }

                        if (durationDisplay && data.data.duration) {
                            const hours = Math.floor(data.data.duration / 3600);
                            const minutes = Math.floor((data.data.duration % 3600) / 60);
                            const seconds = data.data.duration % 60;

                            durationDisplay.querySelector('span:first-child').textContent =
                                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                        }

                        // Stop polling if execution is no longer running
                        this.clearIntervals();

                        // Refresh the resource metrics
                        this.loadResourceMetrics();
                    }
                } catch (error) {
                    console.error('Error checking execution status:', error);
                }
            },

            async refreshLogs() {
                try {
                    this.isLoadingLogs = true;

                    const response = await fetch(`/dashboard/executions/${this.executionId}/logs?offset=0&limit=1000`);
                    if (!response.ok) throw new Error('Failed to fetch logs');

                    const data = await response.json();
                    if (!data.success) throw new Error(data.message || 'Failed to get logs');

                    this.logs = data.data.logs;
                    this.hasMoreLogs = data.data.hasMore;
                    this.logOffset = data.data.nextOffset;

                    // Scroll to bottom of logs after update
                    this.$nextTick(() => {
                        this.scrollLogsToBottom();
                    });
                } catch (error) {
                    console.error('Error refreshing logs:', error);
                } finally {
                    this.isLoadingLogs = false;
                }
            },

            async loadMoreLogs() {
                try {
                    this.isLoadingMore = true;

                    const response = await fetch(`/dashboard/executions/${this.executionId}/logs?offset=${this.logOffset}&limit=1000`);
                    if (!response.ok) throw new Error('Failed to fetch more logs');

                    const data = await response.json();
                    if (!data.success) throw new Error(data.message || 'Failed to get more logs');

                    // Prepend the older logs to the beginning
                    this.logs = data.data.logs + this.logs;
                    this.hasMoreLogs = data.data.hasMore;
                    this.logOffset = data.data.nextOffset;

                    // No need to scroll after loading more (older) logs
                } catch (error) {
                    console.error('Error loading more logs:', error);
                } finally {
                    this.isLoadingMore = false;
                }
            },

            scrollLogsToBottom() {
                const logContainer = this.$refs.logContainer;
                if (logContainer) {
                    logContainer.scrollTop = logContainer.scrollHeight;
                }
            },

            clearLogs() {
                this.logs = '';
            },

            abortExecution() {
                window.dispatchEvent(new CustomEvent('open-abort-modal'));
            },

            async confirmAbort() {
                try {
                    const response = await fetch(`/dashboard/executions/${this.executionId}/abort`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Update status immediately
                        this.statusName = 'aborted';
                        this.isRunning = false;

                        // Show success notification
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {
                                type: 'success',
                                message: 'Test execution aborted successfully'
                            }
                        }));

                        // Refresh page after a short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        throw new Error(data.message || 'Failed to abort execution');
                    }
                } catch (error) {
                    console.error('Error aborting execution:', error);

                    // Show error notification
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            type: 'error',
                            message: `Failed to abort execution: ${error.message}`
                        }
                    }));
                }
            },

            async loadResourceMetrics() {
                try {
                    const response = await fetch(`/api/executions/${this.executionId}/metrics`);
                    if (!response.ok) {
                        console.log('No metrics available for this execution');
                        return;
                    }

                    const data = await response.json();
                    if (!data.success || !data.data || !data.data.metrics || data.data.metrics.length === 0) {
                        return;
                    }

                    this.hasResourceMetrics = true;

                    this.$nextTick(() => {
                        this.initCharts(data.data.metrics);
                    });
                } catch (error) {
                    console.error('Error loading resource metrics:', error);
                }
            },

            initCharts(metrics) {
                // Prepare data for charts
                const timestamps = metrics.map(m => new Date(m.metric_time).toLocaleTimeString());
                const cpuData = metrics.map(m => m.cpu_usage);
                const memoryData = metrics.map(m => m.memory_usage);

                // Destroy existing charts if they exist
                if (this.cpuChart) {
                    this.cpuChart.destroy();
                }

                if (this.memoryChart) {
                    this.memoryChart.destroy();
                }

                // Get the current theme mode
                const isDarkMode = document.documentElement.classList.contains('dark');
                const textColor = isDarkMode ? '#d1d5db' : '#1f2937';
                const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

                // Initialize CPU chart
                const cpuCtx = document.getElementById('cpu-chart').getContext('2d');
                this.cpuChart = new Chart(cpuCtx, {
                    type: 'line',
                    data: {
                        labels: timestamps,
                        datasets: [{
                            label: 'CPU Usage (%)',
                            data: cpuData,
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    color: textColor
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: gridColor
                                },
                                ticks: {
                                    color: textColor
                                }
                            },
                            y: {
                                grid: {
                                    color: gridColor
                                },
                                ticks: {
                                    color: textColor
                                },
                                min: 0,
                                max: 100
                            }
                        }
                    }
                });

                // Initialize Memory chart
                const memoryCtx = document.getElementById('memory-chart').getContext('2d');
                this.memoryChart = new Chart(memoryCtx, {
                    type: 'line',
                    data: {
                        labels: timestamps,
                        datasets: [{
                            label: 'Memory Usage (MB)',
                            data: memoryData,
                            borderColor: '#8b5cf6',
                            backgroundColor: 'rgba(139, 92, 246, 0.1)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    color: textColor
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: gridColor
                                },
                                ticks: {
                                    color: textColor
                                }
                            },
                            y: {
                                grid: {
                                    color: gridColor
                                },
                                ticks: {
                                    color: textColor
                                },
                                min: 0
                            }
                        }
                    }
                });
            }
        };
    }
</script>
@endpush
