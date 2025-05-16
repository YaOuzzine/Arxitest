<!-- resources/views/dashboard/executions/index.blade.php -->
@extends('layouts.dashboard')

@section('title', 'Test Executions')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Test Executions</span>
    </li>
@endsection

@section('content')
    <div class="space-y-6" x-data="executionsPage()">
        <!-- Header -->
        <x-index-header title="Test Executions" description="Monitor and manage your automated test runs" :createRoute="route('dashboard.executions.create')"
            createText="Run New Test" createIcon="play-circle">

            <!-- Optional filters section -->
            <x-slot:filters>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <!-- Project Dropdown -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Project</label>
                        <x-dropdown.index width="full" triggerClasses="w-full">
                            <x-slot:trigger>
                                <div
                                    class="w-full flex items-center justify-between px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 shadow-sm cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                    <span x-text="filters.projectLabel || 'All Projects'"></span>
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400"></i>
                                </div>
                            </x-slot:trigger>
                            <x-slot:content>
                                <x-dropdown.item @click="selectProject('', 'All Projects')" :active="request('project_id') === ''">All
                                    Projects</x-dropdown.item>
                                @foreach ($projects as $project)
                                    <x-dropdown.item @click="selectProject('{{ $project->id }}', '{{ $project->name }}')"
                                        :active="request('project_id') === $project->id">
                                        {{ $project->name }}
                                    </x-dropdown.item>
                                @endforeach
                            </x-slot:content>
                        </x-dropdown.index>
                    </div>
                    <!-- Status Dropdown -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Status</label>
                        <x-dropdown.index width="full" triggerClasses="w-full">
                            <x-slot:trigger>
                                <div
                                    class="w-full flex items-center justify-between px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 shadow-sm cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                    <span x-text="filters.statusLabel || 'All Statuses'"></span>
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400"></i>
                                </div>
                            </x-slot:trigger>
                            <x-slot:content>
                                <x-dropdown.item @click="selectStatus('')" :active="request('status') === ''">All Statuses</x-dropdown.item>
                                <x-dropdown.item @click="selectStatus('pending')"
                                    :active="request('status') === 'pending'">Pending</x-dropdown.item>
                                <x-dropdown.item @click="selectStatus('running')"
                                    :active="request('status') === 'running'">Running</x-dropdown.item>
                                <x-dropdown.item @click="selectStatus('completed')"
                                    :active="request('status') === 'completed'">Completed</x-dropdown.item>
                                <x-dropdown.item @click="selectStatus('failed')" :active="request('status') === 'failed'">Failed</x-dropdown.item>
                                <x-dropdown.item @click="selectStatus('aborted')"
                                    :active="request('status') === 'aborted'">Aborted</x-dropdown.item>
                            </x-slot:content>
                        </x-dropdown.index>
                    </div>

                    <!-- Script Dropdown -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Test Script</label>
                        <x-dropdown.index width="full" triggerClasses="w-full">
                            <x-slot:trigger>
                                <div
                                    class="w-full flex items-center justify-between px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 shadow-sm cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                    <span x-text="filters.scriptLabel || 'All Scripts'"></span>
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400"></i>
                                </div>
                            </x-slot:trigger>
                            <x-slot:content>
                                <div class="max-h-60 overflow-y-auto">
                                    <x-dropdown.item @click="selectScript('', 'All Scripts')" :active="request('script_id') === ''">All
                                        Scripts</x-dropdown.item>
                                    @foreach ($scripts ?? [] as $script)
                                        <x-dropdown.item @click="selectScript('{{ $script->id }}', '{{ $script->name }}')"
                                            :active="request('script_id') === $script->id">
                                            {{ $script->name }}
                                        </x-dropdown.item>
                                    @endforeach
                                </div>
                            </x-slot:content>
                        </x-dropdown.index>
                    </div>

                    <!-- Environment Dropdown -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Environment</label>
                        <x-dropdown.index width="full" triggerClasses="w-full">
                            <x-slot:trigger>
                                <div
                                    class="w-full flex items-center justify-between px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 shadow-sm cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                    <span x-text="filters.environmentLabel || 'All Environments'"></span>
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400"></i>
                                </div>
                            </x-slot:trigger>
                            <x-slot:content>
                                <div class="max-h-60 overflow-y-auto">
                                    <x-dropdown.item @click="selectEnvironment('', 'All Environments')"
                                        :active="request('environment_id') === ''">All Environments</x-dropdown.item>
                                    @foreach ($environments ?? [] as $environment)
                                        <x-dropdown.item
                                            @click="selectEnvironment('{{ $environment->id }}', '{{ $environment->name }}')"
                                            :active="request('environment_id') === $environment->id">
                                            {{ $environment->name }}
                                        </x-dropdown.item>
                                    @endforeach
                                </div>
                            </x-slot:content>
                        </x-dropdown.index>
                    </div>

                    <!-- Time Period Dropdown -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Time Period</label>
                        <x-dropdown.index width="full" triggerClasses="w-full">
                            <x-slot:trigger>
                                <div
                                    class="w-full flex items-center justify-between px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 shadow-sm cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                    <span x-text="filters.dateLabel || 'All Time'"></span>
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400"></i>
                                </div>
                            </x-slot:trigger>
                            <x-slot:content>
                                <x-dropdown.item @click="selectDateFilter('', 'All Time')" :active="request('date_filter') === ''">All
                                    Time</x-dropdown.item>
                                <x-dropdown.item @click="selectDateFilter('today', 'Today')"
                                    :active="request('date_filter') === 'today'">Today</x-dropdown.item>
                                <x-dropdown.item @click="selectDateFilter('week', 'This Week')" :active="request('date_filter') === 'week'">This
                                    Week</x-dropdown.item>
                                <x-dropdown.item @click="selectDateFilter('month', 'This Month')" :active="request('date_filter') === 'month'">This
                                    Month</x-dropdown.item>
                            </x-slot:content>
                        </x-dropdown.index>
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <button type="button" @click="applyFilters()"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center gap-2">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        Apply Filters
                    </button>
                    <button type="button" @click="resetFilters()"
                        class="ml-2 px-4 py-2 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 flex items-center gap-2">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        Reset
                    </button>
                </div>
            </x-slot:filters>
        </x-index-header>

        <!-- Main Content -->
        <div
            class="bg-white dark:bg-zinc-800 shadow-md rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700">
            <!-- Table Header -->
            <div class="border-b border-zinc-200 dark:border-zinc-700 px-6 py-4 bg-zinc-50 dark:bg-zinc-800/50">
                <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">Execution Results</h2>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                ID
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Test Script
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Environment
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Started
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Duration
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse($executions as $execution)
                            <tr id="execution-row-{{ $execution->id }}"
                                class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors"
                                x-data="{
                                    executionId: '{{ $execution->id }}',
                                    statusName: '{{ $execution->status->name ?? 'unknown' }}',
                                    isRunning: {{ json_encode($execution->isRunning() ?? false) }},
                                    startTime: '{{ $execution->start_time }}',
                                    endTime: '{{ $execution->end_time }}',
                                    duration: {{ $execution->duration ?? 'null' }}
                                }" x-init="if (isRunning) startStatusPolling(executionId)">
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                    <a href="{{ route('dashboard.executions.show', $execution->id) }}"
                                        class="hover:underline">
                                        {{ substr($execution->id, 0, 8) }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-200">
                                    {{ $execution->testScript->name ?? 'Unknown Script' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-200">
                                    {{ $execution->environment->name ?? 'Unknown Environment' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap" x-ref="statusCell">
                                    <span x-show="statusName === 'pending'"
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                        Pending
                                    </span>
                                    <span x-show="statusName === 'running'"
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-600 dark:text-blue-400"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Running
                                    </span>
                                    <span x-show="statusName === 'completed'"
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Completed
                                    </span>
                                    <span x-show="statusName === 'failed'"
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                        Failed
                                    </span>
                                    <span x-show="statusName === 'aborted'"
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400">
                                        Aborted
                                    </span>
                                    <span
                                        x-show="!['pending', 'running', 'completed', 'failed', 'aborted'].includes(statusName)"
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300">
                                        {{ $execution->status->name ?? 'Unknown' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400"
                                    x-ref="startTimeCell">
                                    {{ $execution->start_time ? $execution->start_time->diffForHumans() : 'Not started' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400"
                                    x-ref="durationCell">
                                    <span x-show="endTime && !isRunning">
                                        {{ $execution->duration ? gmdate('H:i:s', $execution->duration) : '-' }}
                                    </span>
                                    <span x-show="!endTime && isRunning" class="text-blue-600 dark:text-blue-400">
                                        Running... <span x-ref="liveTimer"></span>
                                    </span>
                                    <span x-show="!endTime && !isRunning">
                                        -
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right space-x-2">
                                    <a href="{{ route('dashboard.executions.show', $execution->id) }}"
                                        class="px-2 py-1 bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 rounded hover:bg-indigo-200 dark:hover:bg-indigo-800/50 inline-flex items-center">
                                        <i data-lucide="eye" class="w-4 h-4 mr-1"></i> View
                                    </a>
                                    <button x-show="isRunning" @click="abortExecution('{{ $execution->id }}')"
                                        class="px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded hover:bg-red-200 dark:hover:bg-red-800/50 inline-flex items-center">
                                        <i data-lucide="square" class="w-4 h-4 mr-1"></i> Abort
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i data-lucide="box" class="h-12 w-12 text-zinc-300 dark:text-zinc-600 mb-4"></i>
                                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-200 mb-1">No test
                                            executions found</h3>
                                        <p class="text-zinc-500 dark:text-zinc-400 max-w-sm">
                                            Run your first test to see results here.
                                        </p>
                                        <a href="{{ route('dashboard.executions.create') }}"
                                            class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                            <i data-lucide="play-circle" class="w-4 h-4 mr-2"></i>
                                            Run a Test
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white dark:bg-zinc-800 px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
                {{ $executions->links() }}
            </div>
        </div>
    </div>

    <!-- Abort Execution Confirmation Modal -->
    <div x-data="{ show: false, executionId: null }" x-show="show" @open-abort-modal.window="show = true; executionId = $event.detail.id"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-zinc-900/60 dark:bg-zinc-900/80" aria-hidden="true"
                @click="show = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-zinc-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-4 pt-5 pb-4 bg-white dark:bg-zinc-800 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                            <i data-lucide="alert-triangle" class="w-6 h-6 text-red-600 dark:text-red-400"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg font-medium leading-6 text-zinc-900 dark:text-zinc-100" id="modal-title">
                                Abort Execution
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    Are you sure you want to abort this test execution? This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="px-4 py-3 bg-zinc-50 dark:bg-zinc-800/50 sm:px-6 sm:flex sm:flex-row-reverse border-t border-zinc-200 dark:border-zinc-700">
                    <button type="button" @click="confirmAbort()"
                        class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Abort
                    </button>
                    <button type="button" @click="show = false"
                        class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm hover:bg-zinc-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function getDateFilterLabel(filter) {
            const labels = {
                'today': 'Today',
                'week': 'This Week',
                'month': 'This Month'
            };
            return labels[filter] || 'All Time';
        }

        function executionsPage() {
            return {
                pollingIntervals: {},
                timerIntervals: {},
                filters: {
                    status: '{{ request('status') }}',
                    statusLabel: '{{ request('status') ? ucfirst(request('status')) : 'All Statuses' }}',
                    project_id: '{{ request('project_id') }}',
                    projectLabel: 'All Projects',
                    script_id: '{{ request('script_id') }}',
                    scriptLabel: 'All Scripts',
                    environment_id: '{{ request('environment_id') }}',
                    environmentLabel: 'All Environments',
                    date_filter: '{{ request('date_filter') }}',
                    dateLabel: '{{ request('date_filter') ? getDateFilterLabel(request('date_filter')) : 'All Time' }}'
                },
                init() {
                    // Initialize dropdown labels
                    this.initializeDropdownLabels();

                    // Add event listener for project change
                    this.$watch('filters.project_id', (value) => {
                        if (value !== this.filters.project_id) {
                            // Reset script and environment when project changes
                            this.filters.script_id = '';
                            this.filters.scriptLabel = 'All Scripts';
                            this.filters.environment_id = '';
                            this.filters.environmentLabel = 'All Environments';

                            this.applyFilters();
                        }
                    });
                },

                initializeDropdownLabels() {
                    // Script label initialization
                    if (this.filters.script_id) {
                        @foreach ($scripts ?? [] as $script)
                            if ('{{ $script->id }}' === this.filters.script_id) {
                                this.filters.scriptLabel = '{{ $script->name }}';
                            }
                        @endforeach
                    }

                    // Environment label initialization
                    if (this.filters.environment_id) {
                        @foreach ($environments ?? [] as $environment)
                            if ('{{ $environment->id }}' === this.filters.environment_id) {
                                this.filters.environmentLabel = '{{ $environment->name }}';
                            }
                        @endforeach
                    }

                    // Date filter label initialization
                    if (this.filters.date_filter) {
                        const dateLabels = {
                            'today': 'Today',
                            'week': 'This Week',
                            'month': 'This Month'
                        };
                        this.filters.dateLabel = dateLabels[this.filters.date_filter] || 'All Time';
                    }
                },

                // Dropdown selection methods
                selectStatus(status) {
                    this.filters.status = status;
                    this.filters.statusLabel = status ? status.charAt(0).toUpperCase() + status.slice(1) : 'All Statuses';
                },

                selectScript(id, name) {
                    this.filters.script_id = id;
                    this.filters.scriptLabel = id ? name : 'All Scripts';
                },

                selectEnvironment(id, name) {
                    this.filters.environment_id = id;
                    this.filters.environmentLabel = id ? name : 'All Environments';
                },

                selectDateFilter(filter, label) {
                    this.filters.date_filter = filter;
                    this.filters.dateLabel = filter ? label : 'All Time';
                },

                // Apply filters and redirect
                applyFilters() {
                    let url = new URL(window.location.href);

                    // Update or clear parameters based on filter values
                    if (this.filters.status) url.searchParams.set('status', this.filters.status);
                    else url.searchParams.delete('status');

                    if (this.filters.script_id) url.searchParams.set('script_id', this.filters.script_id);
                    else url.searchParams.delete('script_id');

                    if (this.filters.environment_id) url.searchParams.set('environment_id', this.filters.environment_id);
                    else url.searchParams.delete('environment_id');

                    if (this.filters.date_filter) url.searchParams.set('date_filter', this.filters.date_filter);
                    else url.searchParams.delete('date_filter');

                    // Redirect to filtered URL
                    window.location.href = url.toString();
                },

                // Reset all filters
                resetFilters() {
                    window.location.href = '{{ route('dashboard.executions.index') }}';
                },

                // Start polling for status updates on running executions
                startStatusPolling(executionId) {
                    // Clear any existing interval for this execution
                    this.clearPolling(executionId);

                    // Reference to the row elements
                    const row = document.getElementById(`execution-row-${executionId}`);
                    if (!row) return;

                    const statusCell = row.querySelector('[x-ref="statusCell"]');
                    const durationCell = row.querySelector('[x-ref="durationCell"]');
                    const liveTimer = row.querySelector('[x-ref="liveTimer"]');
                    const startTimeCell = row.querySelector('[x-ref="startTimeCell"]');

                    // Start a timer for live duration updates
                    const startTime = new Date(row.__x.$data.startTime);

                    this.timerIntervals[executionId] = setInterval(() => {
                        if (!liveTimer) return;

                        const now = new Date();
                        const diffInSeconds = Math.floor((now - startTime) / 1000);

                        const hours = Math.floor(diffInSeconds / 3600);
                        const minutes = Math.floor((diffInSeconds % 3600) / 60);
                        const seconds = diffInSeconds % 60;

                        liveTimer.textContent =
                            `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    }, 1000);

                    // Poll for status updates every 5 seconds
                    this.pollingIntervals[executionId] = setInterval(async () => {
                        try {
                            const response = await fetch(`/api/executions/${executionId}/status`);
                            if (!response.ok) throw new Error('Failed to fetch status');

                            const data = await response.json();
                            if (!data.success) throw new Error(data.message || 'Failed to get status');

                            // Update the status in the row's Alpine data
                            row.__x.$data.statusName = data.data.status.name;
                            row.__x.$data.isRunning = data.data.status.name === 'running';

                            // If execution is no longer running, stop polling
                            if (data.data.status.name !== 'running') {
                                this.clearPolling(executionId);

                                // Update duration if execution is complete
                                if (data.data.end_time) {
                                    row.__x.$data.endTime = data.data.end_time;
                                    row.__x.$data.duration = data.data.duration;

                                    if (durationCell) {
                                        const hours = Math.floor(data.data.duration / 3600);
                                        const minutes = Math.floor((data.data.duration % 3600) / 60);
                                        const seconds = data.data.duration % 60;

                                        durationCell.querySelector('span:first-child').textContent =
                                            `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                                    }
                                }

                                // Refresh the page to get updated data
                                setTimeout(() => {
                                    window.location.reload();
                                }, 3000);
                            }
                        } catch (error) {
                            console.error('Error polling execution status:', error);
                        }
                    }, 5000);
                },

                selectProject(id, name) {
                    this.filters.project_id = id;
                    this.filters.projectLabel = id ? name : 'All Projects';

                    // Reset script and environment when project changes
                    this.filters.script_id = '';
                    this.filters.scriptLabel = 'All Scripts';
                    this.filters.environment_id = '';
                    this.filters.environmentLabel = 'All Environments';

                    this.applyFilters();
                },

                // Clear polling and timer intervals
                clearPolling(executionId) {
                    if (this.pollingIntervals[executionId]) {
                        clearInterval(this.pollingIntervals[executionId]);
                        delete this.pollingIntervals[executionId];
                    }

                    if (this.timerIntervals[executionId]) {
                        clearInterval(this.timerIntervals[executionId]);
                        delete this.timerIntervals[executionId];
                    }
                },

                // Open abort confirmation modal
                abortExecution(executionId) {
                    window.dispatchEvent(new CustomEvent('open-abort-modal', {
                        detail: {
                            id: executionId
                        }
                    }));
                },

                // Confirm and execute abort
                async confirmAbort() {
                    const executionId = this.$el.closest('[x-data]').__x.$data.executionId;
                    if (!executionId) return;

                    try {
                        const response = await fetch(`/dashboard/executions/${executionId}/abort`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Close modal
                            this.$el.closest('[x-data]').__x.$data.show = false;

                            // Update UI to show aborted state
                            const row = document.getElementById(`execution-row-${executionId}`);
                            if (row) {
                                row.__x.$data.statusName = 'aborted';
                                row.__x.$data.isRunning = false;
                                this.clearPolling(executionId);
                            }

                            // Show success notification
                            window.dispatchEvent(new CustomEvent('notify', {
                                detail: {
                                    type: 'success',
                                    message: 'Test execution aborted successfully'
                                }
                            }));

                            // Refresh the page after a short delay
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

                        // Close modal
                        this.$el.closest('[x-data]').__x.$data.show = false;
                    }
                }
            };
        }
    </script>
@endpush
