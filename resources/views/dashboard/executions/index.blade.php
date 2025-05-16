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
                                        :active="request('project_id') === $project->id">{{ $project->name }}</x-dropdown.item>
                                @endforeach
                            </x-slot:content>
                        </x-dropdown.index>
                    </div>


                    <!-- Script Dropdown -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Test Script</label>
                        <x-dropdown.search width="full" searchTerm="searchTerm" triggerClasses="w-full">
                            <x-slot:trigger>
                                <div class="w-full flex items-center justify-between px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 shadow-sm cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700"
                                    :class="{ 'opacity-50': !filters.project_id }">
                                    <span x-text="filters.scriptLabel || 'All Scripts'"></span>
                                    <div class="flex items-center">
                                        <div x-show="isLoadingScripts" class="mr-2">
                                            <svg class="animate-spin h-4 w-4 text-indigo-500"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                        </div>
                                        <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400"></i>
                                    </div>
                                </div>
                            </x-slot:trigger>
                            <x-slot:content>
                                <!-- No project selected message -->
                                <div x-show="!filters.project_id"
                                    class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400 text-center">
                                    Please select a project first
                                </div>

                                <!-- Loading indicator -->
                                <div x-show="filters.project_id && isLoadingScripts"
                                    class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400 text-center">
                                    <svg class="animate-spin h-5 w-5 mx-auto mb-2 text-indigo-500"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Loading scripts...
                                </div>

                                <!-- No scripts message -->
                                <div x-show="filters.project_id && !isLoadingScripts && filteredScripts.length === 0"
                                    class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400 text-center">
                                    No scripts found
                                </div>

                                <!-- All Scripts option -->
                                <template x-if="filters.project_id && filteredScripts.length > 0">
                                    <x-dropdown.item @click="selectScript('', 'All Scripts')">All Scripts</x-dropdown.item>
                                </template>

                                <!-- Script list -->
                                <template x-for="script in filteredScripts" :key="script.id">
                                    <x-dropdown.item @click="selectScript(script.id, script.name)" :class="{'bg-indigo-50 dark:bg-indigo-900/20': filters.script_id === script.id}">
                                        <div class="flex flex-col">
                                            <span x-text="script.name"></span>
                                            <span x-text="script.framework_type"
                                                class="text-xs text-zinc-500 dark:text-zinc-400"></span>
                                        </div>
                                    </x-dropdown.item>
                                </template>
                            </x-slot:content>
                        </x-dropdown.search>
                    </div>

                    <!-- Environment Dropdown -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Environment</label>
                        <x-dropdown.search width="full" searchTerm="environmentSearchTerm" triggerClasses="w-full">
                            <x-slot:trigger>
                                <div class="w-full flex items-center justify-between px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 shadow-sm cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700"
                                    :class="{ 'opacity-50': !filters.project_id }">
                                    <span x-text="filters.environmentLabel || 'All Environments'"></span>
                                    <div class="flex items-center">
                                        <div x-show="isLoadingEnvironments" class="mr-2">
                                            <svg class="animate-spin h-4 w-4 text-indigo-500"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                        </div>
                                        <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400"></i>
                                    </div>
                                </div>
                            </x-slot:trigger>
                            <x-slot:content>
                                <!-- No project selected message -->
                                <div x-show="!filters.project_id"
                                    class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400 text-center">
                                    Please select a project first
                                </div>

                                <!-- Loading indicator -->
                                <div x-show="filters.project_id && isLoadingEnvironments"
                                    class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400 text-center">
                                    <svg class="animate-spin h-5 w-5 mx-auto mb-2 text-indigo-500"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Loading environments...
                                </div>

                                <!-- No environments message -->
                                <div x-show="filters.project_id && !isLoadingEnvironments && filteredEnvironments.length === 0"
                                    class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400 text-center">
                                    No environments found
                                </div>

                                <!-- All Environments option -->
                                <template x-if="filters.project_id && filteredEnvironments.length > 0">
                                    <x-dropdown.item @click="selectEnvironment('', 'All Environments')">All
                                        Environments</x-dropdown.item>
                                </template>

                                <!-- Environment list -->
                                <template x-for="env in filteredEnvironments" :key="env.id">
                                    <x-dropdown.item @click="selectEnvironment(env.id, env.name)" :class="{'bg-indigo-50 dark:bg-indigo-900/20': filters.environment_id === env.id}">
                                        <div class="flex flex-col">
                                            <span x-text="env.name"></span>
                                            <span x-text="env.is_global ? 'Global' : 'Project-specific'"
                                                class="text-xs text-zinc-500 dark:text-zinc-400"></span>
                                        </div>
                                    </x-dropdown.item>
                                </template>
                            </x-slot:content>
                        </x-dropdown.search>
                    </div>

                    <!-- Status Filter Pills -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Status</label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="toggleStatus('')"
                                class="px-3 py-1.5 rounded-full text-sm font-medium border transition-colors"
                                :class="{
                                    'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700 text-indigo-700 dark:text-indigo-300': selectedStatuses
                                        .length === 0,
                                    'bg-white dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300': selectedStatuses
                                        .length > 0
                                }">
                                All Statuses
                            </button>

                            @foreach ($statusOptions as $status)
                                <button type="button" @click="toggleStatus('{{ $status }}')"
                                    class="px-3 py-1.5 rounded-full text-sm font-medium border transition-colors"
                                    :class="{
                                        'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700 text-indigo-700 dark:text-indigo-300': isStatusActive(
                                            '{{ $status }}'),
                                        'bg-white dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300':
                                            !isStatusActive('{{ $status }}')
                                    }">
                                    {{ ucfirst($status) }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Time Period Filter Pills -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Time Period</label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="toggleDateFilter('')"
                                class="px-3 py-1.5 rounded-full text-sm font-medium border transition-colors"
                                :class="{
                                    'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700 text-indigo-700 dark:text-indigo-300': selectedDateFilters
                                        .length === 0,
                                    'bg-white dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300': selectedDateFilters
                                        .length > 0
                                }">
                                All Time
                            </button>

                            @foreach ($dateFilterOptions as $filter)
                                <button type="button" @click="toggleDateFilter('{{ $filter }}')"
                                    class="px-3 py-1.5 rounded-full text-sm font-medium border transition-colors"
                                    :class="{
                                        'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700 text-indigo-700 dark:text-indigo-300': isDateFilterActive(
                                            '{{ $filter }}'),
                                        'bg-white dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300':
                                            !isDateFilterActive('{{ $filter }}')
                                    }">
                                    {{ $filter === 'today' ? 'Today' : ($filter === 'week' ? 'This Week' : 'This Month') }}
                                </button>
                            @endforeach
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
                    project_id: '{{ request('project_id') }}',
                    projectLabel: 'All Projects',
                    script_id: '{{ request('script_id') }}',
                    scriptLabel: 'All Scripts',
                    environment_id: '{{ request('environment_id') }}',
                    environmentLabel: 'All Environments',
                    date_filter: '{{ request('date_filter') }}',
                    dateLabel: '{{ request('date_filter') ? getDateFilterLabel(request('date_filter')) : 'All Time' }}'
                },
                scripts: @json($scripts ?? []),
                environments: @json($environments ?? []),
                filteredScripts: [],
                filteredEnvironments: [],
                isLoadingScripts: false,
                isLoadingEnvironments: false,
                searchTerm: '',
                environmentSearchTerm: '',
                selectedStatuses: [],
                selectedDateFilters: [],

                init() {
                    // Initialize dropdown labels
                    this.initializeDropdownLabels();

                    // Initialize filtered lists
                    this.filteredScripts = this.scripts;
                    this.filteredEnvironments = this.environments;

                    // Initialize status and date filter pills
                    this.selectedStatuses = this.filters.status ? [this.filters.status] : [];
                    this.selectedDateFilters = this.filters.date_filter ? [this.filters.date_filter] : [];

                    // Set up watchers
                    this.$watch('searchTerm', () => this.filterScripts());
                    this.$watch('environmentSearchTerm', () => this.filterEnvironments());
                },

                initializeDropdownLabels() {
                    // Project label
                    if (this.filters.project_id) {
                        const project = @json($projects).find(p => p.id === this.filters.project_id);
                        if (project) {
                            this.filters.projectLabel = project.name;
                        }
                    }

                    // Script label
                    if (this.filters.script_id) {
                        const script = this.scripts.find(s => s.id === this.filters.script_id);
                        if (script) {
                            this.filters.scriptLabel = script.name;
                        }
                    }

                    // Environment label
                    if (this.filters.environment_id) {
                        const env = this.environments.find(e => e.id === this.filters.environment_id);
                        if (env) {
                            this.filters.environmentLabel = env.name;
                        }
                    }
                },

                selectProject(id, name) {
                    this.filters.project_id = id;
                    this.filters.projectLabel = id ? name : 'All Projects';

                    // Reset script and environment when project changes
                    this.filters.script_id = '';
                    this.filters.scriptLabel = 'All Scripts';
                    this.filters.environment_id = '';
                    this.filters.environmentLabel = 'All Environments';

                    if (id) {
                        // Load scripts and environments via AJAX
                        this.loadScriptsForProject(id);
                        this.loadEnvironmentsForProject(id);
                    } else {
                        // Reset to empty
                        this.scripts = [];
                        this.environments = [];
                        this.filteredScripts = [];
                        this.filteredEnvironments = [];
                    }
                },

                async loadScriptsForProject(projectId) {
                    this.isLoadingScripts = true;

                    try {
                        const response = await fetch(`/dashboard/api/projects/${projectId}/test-scripts`);
                        if (!response.ok) throw new Error(`Failed to fetch scripts (${response.status})`);

                        const result = await response.json();
                        if (result.success) {
                            this.scripts = result.scripts || [];
                            this.filteredScripts = this.scripts;
                        } else {
                            throw new Error(result.message || 'Failed to load scripts');
                        }
                    } catch (error) {
                        console.error('Error loading scripts:', error);
                        this.scripts = [];
                        this.filteredScripts = [];

                        // Show error notification
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {
                                type: 'error',
                                message: `Failed to load scripts: ${error.message}`
                            }
                        }));
                    } finally {
                        this.isLoadingScripts = false;
                    }
                },

                async loadEnvironmentsForProject(projectId) {
                    this.isLoadingEnvironments = true;

                    try {
                        const response = await fetch(`/dashboard/api/projects/${projectId}/environments`);
                        if (!response.ok) throw new Error(`Failed to fetch environments (${response.status})`);

                        const result = await response.json();
                        if (result.success) {
                            this.environments = result.environments || [];
                            this.filteredEnvironments = this.environments;
                        } else {
                            throw new Error(result.message || 'Failed to load environments');
                        }
                    } catch (error) {
                        console.error('Error loading environments:', error);
                        this.environments = [];
                        this.filteredEnvironments = [];

                        // Show error notification
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {
                                type: 'error',
                                message: `Failed to load environments: ${error.message}`
                            }
                        }));
                    } finally {
                        this.isLoadingEnvironments = false;
                    }
                },

                filterScripts() {
                    if (!this.searchTerm) {
                        this.filteredScripts = this.scripts;
                        return;
                    }

                    const term = this.searchTerm.toLowerCase();
                    this.filteredScripts = this.scripts.filter(script =>
                        script.name?.toLowerCase().includes(term) ||
                        script.framework_type?.toLowerCase().includes(term) ||
                        (script.test_case?.title && script.test_case.title.toLowerCase().includes(term))
                    );
                },

                filterEnvironments() {
                    if (!this.environmentSearchTerm) {
                        this.filteredEnvironments = this.environments;
                        return;
                    }

                    const term = this.environmentSearchTerm.toLowerCase();
                    this.filteredEnvironments = this.environments.filter(env =>
                        env.name?.toLowerCase().includes(term) ||
                        (env.is_global ? 'global' : 'project').includes(term)
                    );
                },

                selectScript(id, name) {
                    this.filters.script_id = id;
                    this.filters.scriptLabel = id ? name : 'All Scripts';
                },

                selectEnvironment(id, name) {
                    this.filters.environment_id = id;
                    this.filters.environmentLabel = id ? name : 'All Environments';
                },

                // For status pills
                toggleStatus(status) {
                    this.selectedStatuses = status ? [status] : []; // Single selection
                    this.filters.status = status || '';
                },

                isStatusActive(status) {
                    return this.selectedStatuses.includes(status);
                },

                // For date filter pills
                toggleDateFilter(filter) {
                    this.selectedDateFilters = filter ? [filter] : []; // Single selection
                    this.filters.date_filter = filter || '';
                },

                isDateFilterActive(filter) {
                    return this.selectedDateFilters.includes(filter);
                },

                applyFilters() {
                    let url = new URL(window.location.href);

                    // Update or clear parameters based on filter values
                    if (this.filters.project_id) url.searchParams.set('project_id', this.filters.project_id);
                    else url.searchParams.delete('project_id');

                    if (this.filters.script_id) url.searchParams.set('script_id', this.filters.script_id);
                    else url.searchParams.delete('script_id');

                    if (this.filters.environment_id) url.searchParams.set('environment_id', this.filters.environment_id);
                    else url.searchParams.delete('environment_id');

                    if (this.filters.status) url.searchParams.set('status', this.filters.status);
                    else url.searchParams.delete('status');

                    if (this.filters.date_filter) url.searchParams.set('date_filter', this.filters.date_filter);
                    else url.searchParams.delete('date_filter');

                    // Redirect to filtered URL
                    window.location.href = url.toString();
                },

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
