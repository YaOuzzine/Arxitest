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
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4">
                    <!-- Project Dropdown -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Project</label>
                        <x-dropdown.search width="full" triggerClasses="w-full" searchTerm="projectSearchTerm"
                            placeholder="Search projects..." noResultsMessage="No projects found" maxHeight="max-h-60">
                            <x-slot:trigger>
                                <div
                                    class="w-full flex items-center justify-between px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 shadow-sm cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                    <span x-text="filters.projectLabel || 'All Projects'"></span>
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400"></i>
                                </div>
                            </x-slot:trigger>
                            <x-slot:content>
                                <div class="py-1">
                                    <button type="button" @click="selectProject('', 'All Projects')"
                                        class="w-full text-left px-4 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                        :class="{
                                            'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300': filters
                                                .project_id === ''
                                        }">
                                        All Projects
                                    </button>
                                    <template x-for="project in filteredProjects" :key="project.id">
                                        <button type="button" @click="selectProject(project.id, project.name)"
                                            class="w-full text-left px-4 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                            :class="{
                                                'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300': filters
                                                    .project_id === project.id
                                            }">
                                            <span x-text="project.name"></span>
                                        </button>
                                    </template>
                                </div>
                            </x-slot:content>
                        </x-dropdown.search>
                    </div>

                    <!-- Script Dropdown -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Test Script</label>
                        <x-dropdown.search width="full" triggerClasses="w-full" searchTerm="scriptSearchTerm"
                            placeholder="Search scripts..." noResultsMessage="No scripts found" maxHeight="max-h-60">
                            <x-slot:trigger>
                                <div class="w-full flex items-center justify-between px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 shadow-sm cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700"
                                    :class="{ 'opacity-50 pointer-events-none': !filters.project_id }">
                                    <span x-text="filters.scriptLabel || 'All Scripts'"></span>
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400"></i>
                                    <div x-show="isLoadingScripts" class="absolute inset-y-0 right-10 flex items-center">
                                        <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </x-slot:trigger>
                            <x-slot:content>
                                <div x-show="!filters.project_id"
                                    class="py-3 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    Please select a project first
                                </div>
                                <div x-show="filters.project_id && isLoadingScripts" class="py-3 text-center">
                                    <svg class="animate-spin h-5 w-5 mx-auto text-indigo-500"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Loading scripts...</p>
                                </div>
                                <div x-show="filters.project_id && !isLoadingScripts">
                                    <button type="button" @click="selectScript('', 'All Scripts')"
                                        class="w-full text-left px-4 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                        :class="{
                                            'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300': filters
                                                .script_id === ''
                                        }">
                                        All Scripts
                                    </button>
                                    <template x-for="script in filteredScripts" :key="script.id">
                                        <button type="button" @click="selectScript(script.id, script.name)"
                                            class="w-full text-left px-4 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                            :class="{
                                                'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300': filters
                                                    .script_id === script.id
                                            }">
                                            <div class="flex flex-col">
                                                <span x-text="script.name"></span>
                                                <span class="text-xs text-zinc-500 dark:text-zinc-400"
                                                    x-text="script.framework_type"></span>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </x-slot:content>
                        </x-dropdown.search>
                    </div>

                    <!-- Environment Dropdown -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Environment</label>
                        <x-dropdown.search width="full" triggerClasses="w-full" searchTerm="environmentSearchTerm"
                            placeholder="Search environments..." noResultsMessage="No environments found"
                            maxHeight="max-h-60">
                            <x-slot:trigger>
                                <div class="w-full flex items-center justify-between px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 shadow-sm cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700"
                                    :class="{ 'opacity-50 pointer-events-none': !filters.project_id }">
                                    <span x-text="filters.environmentLabel || 'All Environments'"></span>
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400"></i>
                                    <div x-show="isLoadingEnvironments"
                                        class="absolute inset-y-0 right-10 flex items-center">
                                        <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </x-slot:trigger>
                            <x-slot:content>
                                <div x-show="!filters.project_id"
                                    class="py-3 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    Please select a project first
                                </div>
                                <div x-show="filters.project_id && isLoadingEnvironments" class="py-3 text-center">
                                    <svg class="animate-spin h-5 w-5 mx-auto text-indigo-500"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Loading environments...</p>
                                </div>
                                <div x-show="filters.project_id && !isLoadingEnvironments">
                                    <button type="button" @click="selectEnvironment('', 'All Environments')"
                                        class="w-full text-left px-4 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                        :class="{
                                            'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300': filters
                                                .environment_id === ''
                                        }">
                                        All Environments
                                    </button>
                                    <template x-for="env in filteredEnvironments" :key="env.id">
                                        <button type="button" @click="selectEnvironment(env.id, env.name)"
                                            class="w-full text-left px-4 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                            :class="{
                                                'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300': filters
                                                    .environment_id === env.id
                                            }">
                                            <div class="flex flex-col">
                                                <span x-text="env.name"></span>
                                                <span class="text-xs text-zinc-500 dark:text-zinc-400"
                                                    x-text="env.is_global ? 'Global' : 'Project specific'"></span>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </x-slot:content>
                        </x-dropdown.search>
                    </div>
                </div>

                <!-- Status Filter Pills -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Status</label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="toggleStatusFilter('all')"
                            class="status-pill px-3 py-1.5 rounded-full text-sm font-medium border transition-colors duration-200"
                            :class="!filters.status ?
                                'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700 text-indigo-700 dark:text-indigo-300' :
                                'bg-white dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300'">
                            All Statuses
                        </button>

                        <button type="button" @click="toggleStatusFilter('pending')"
                            class="status-pill px-3 py-1.5 rounded-full text-sm font-medium border transition-colors duration-200"
                            :class="filters.status === 'pending' ?
                                'bg-yellow-100 dark:bg-yellow-900/30 border-yellow-300 dark:border-yellow-700 text-yellow-800 dark:text-yellow-300' :
                                'bg-white dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300'">
                            Pending
                        </button>

                        <button type="button" @click="toggleStatusFilter('running')"
                            class="status-pill px-3 py-1.5 rounded-full text-sm font-medium border transition-colors duration-200"
                            :class="filters.status === 'running' ?
                                'bg-blue-100 dark:bg-blue-900/30 border-blue-300 dark:border-blue-700 text-blue-800 dark:text-blue-300' :
                                'bg-white dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300'">
                            Running
                        </button>

                        <button type="button" @click="toggleStatusFilter('completed')"
                            class="status-pill px-3 py-1.5 rounded-full text-sm font-medium border transition-colors duration-200"
                            :class="filters.status === 'completed' ?
                                'bg-green-100 dark:bg-green-900/30 border-green-300 dark:border-green-700 text-green-800 dark:text-green-300' :
                                'bg-white dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300'">
                            Completed
                        </button>

                        <button type="button" @click="toggleStatusFilter('failed')"
                            class="status-pill px-3 py-1.5 rounded-full text-sm font-medium border transition-colors duration-200"
                            :class="filters.status === 'failed' ?
                                'bg-red-100 dark:bg-red-900/30 border-red-300 dark:border-red-700 text-red-800 dark:text-red-300' :
                                'bg-white dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300'">
                            Failed
                        </button>

                        <button type="button" @click="toggleStatusFilter('aborted')"
                            class="status-pill px-3 py-1.5 rounded-full text-sm font-medium border transition-colors duration-200"
                            :class="filters.status === 'aborted' ?
                                'bg-orange-100 dark:bg-orange-900/30 border-orange-300 dark:border-orange-700 text-orange-800 dark:text-orange-300' :
                                'bg-white dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300'">
                            Aborted
                        </button>
                    </div>
                </div>

                <!-- Time Period Filter Pills -->
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Time Period</label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="toggleDateFilter('all')"
                            class="date-pill px-3 py-1.5 rounded-full text-sm font-medium border transition-colors duration-200"
                            :class="!filters.date_filter ?
                                'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700 text-indigo-700 dark:text-indigo-300' :
                                'bg-white dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300'">
                            All Time
                        </button>

                        <button type="button" @click="toggleDateFilter('today')"
                            class="date-pill px-3 py-1.5 rounded-full text-sm font-medium border transition-colors duration-200"
                            :class="filters.date_filter === 'today' ?
                                'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700 text-indigo-700 dark:text-indigo-300' :
                                'bg-white dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300'">
                            Today
                        </button>

                        <button type="button" @click="toggleDateFilter('week')"
                            class="date-pill px-3 py-1.5 rounded-full text-sm font-medium border transition-colors duration-200"
                            :class="filters.date_filter === 'week' ?
                                'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700 text-indigo-700 dark:text-indigo-300' :
                                'bg-white dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300'">
                            This Week
                        </button>

                        <button type="button" @click="toggleDateFilter('month')"
                            class="date-pill px-3 py-1.5 rounded-full text-sm font-medium border transition-colors duration-200"
                            :class="filters.date_filter === 'month' ?
                                'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700 text-indigo-700 dark:text-indigo-300' :
                                'bg-white dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300'">
                            This Month
                        </button>
                    </div>
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
        function executionsPage() {
            return {
                pollingIntervals: {},
                timerIntervals: {},
                filters: {
                    status: '{{ request('status') }}',
                    statusLabel: '{{ request('status') ? ucfirst(request('status')) : 'All Statuses' }}',
                    project_id: '{{ request('project_id') }}',
                    projectLabel: '{{ request('project_id') && isset($projects->firstWhere('id', request('project_id'))->name) ? $projects->firstWhere('id', request('project_id'))->name : 'All Projects' }}',
                    script_id: '{{ request('script_id') }}',
                    scriptLabel: '{{ request('script_id') ? (isset($scripts) && $scripts->isNotEmpty() && $scripts->firstWhere('id', request('script_id')) ? $scripts->firstWhere('id', request('script_id'))->name : 'Selected Script') : 'All Scripts' }}',
                    environment_id: '{{ request('environment_id') }}',
                    environmentLabel: '{{ request('environment_id') ? (isset($environments) && $environments->isNotEmpty() && $environments->firstWhere('id', request('environment_id')) ? $environments->firstWhere('id', request('environment_id'))->name : 'Selected Environment') : 'All Environments' }}',
                    date_filter: '{{ request('date_filter') }}',
                    dateLabel: '{{ request('date_filter')
                        ? (function ($filter) {
                            $labels = [
                                'today' => 'Today',
                                'week' => 'This Week',
                                'month' => 'This Month',
                            ];
                            return $labels[$filter] ?? 'All Time';
                        })(request('date_filter'))
                        : 'All Time' }}'
                },
                scripts: [],
                environments: [],
                filteredProjects: [],
                filteredScripts: [],
                filteredEnvironments: [],
                projectSearchTerm: '',
                scriptSearchTerm: '',
                environmentSearchTerm: '',
                isLoadingScripts: false,
                isLoadingEnvironments: false,

                init() {
                    // Initialize data
                    this.filteredProjects = @json($projects);

                    // Watch for search changes
                    this.$watch('projectSearchTerm', () => this.filterProjects());
                    this.$watch('scriptSearchTerm', () => this.filterScripts());
                    this.$watch('environmentSearchTerm', () => this.filterEnvironments());

                    // If a project is already selected, load its related data
                    if (this.filters.project_id) {
                        this.loadScriptsForProject(this.filters.project_id);
                        this.loadEnvironmentsForProject(this.filters.project_id);
                    }
                },

                filterProjects() {
                    if (!this.projectSearchTerm.trim()) {
                        this.filteredProjects = @json($projects);
                        return;
                    }

                    const term = this.projectSearchTerm.toLowerCase().trim();
                    this.filteredProjects = @json($projects).filter(project =>
                        project.name.toLowerCase().includes(term)
                    );
                },

                filterScripts() {
                    if (!this.scriptSearchTerm.trim()) {
                        this.filteredScripts = this.scripts;
                        return;
                    }

                    const term = this.scriptSearchTerm.toLowerCase().trim();
                    this.filteredScripts = this.scripts.filter(script =>
                        script.name.toLowerCase().includes(term) ||
                        script.framework_type.toLowerCase().includes(term)
                    );
                },

                filterEnvironments() {
                    if (!this.environmentSearchTerm.trim()) {
                        this.filteredEnvironments = this.environments;
                        return;
                    }

                    const term = this.environmentSearchTerm.toLowerCase().trim();
                    this.filteredEnvironments = this.environments.filter(env =>
                        env.name.toLowerCase().includes(term) ||
                        (env.is_global ? 'global' : 'project').includes(term)
                    );
                },

                // Select functions with automatic navigation
                selectProject(id, name) {
                    if (id === this.filters.project_id) return;

                    // Update URL and navigate
                    let url = new URL(window.location.href);

                    if (id) {
                        url.searchParams.set('project_id', id);
                    } else {
                        url.searchParams.delete('project_id');
                    }

                    // Remove script and environment filters when changing project
                    url.searchParams.delete('script_id');
                    url.searchParams.delete('environment_id');

                    window.location.href = url.toString();
                },

                selectScript(id, name) {
                    if (id === this.filters.script_id) return;

                    let url = new URL(window.location.href);

                    if (id) {
                        url.searchParams.set('script_id', id);
                    } else {
                        url.searchParams.delete('script_id');
                    }

                    window.location.href = url.toString();
                },

                selectEnvironment(id, name) {
                    if (id === this.filters.environment_id) return;

                    let url = new URL(window.location.href);

                    if (id) {
                        url.searchParams.set('environment_id', id);
                    } else {
                        url.searchParams.delete('environment_id');
                    }

                    window.location.href = url.toString();
                },

                toggleStatusFilter(status) {
                    let url = new URL(window.location.href);

                    if (status === 'all') {
                        url.searchParams.delete('status');
                    } else if (this.filters.status === status) {
                        // If already selected, deselect it (revert to 'all')
                        url.searchParams.delete('status');
                    } else {
                        url.searchParams.set('status', status);
                    }

                    window.location.href = url.toString();
                },

                toggleDateFilter(filter) {
                    let url = new URL(window.location.href);

                    if (filter === 'all') {
                        url.searchParams.delete('date_filter');
                    } else if (this.filters.date_filter === filter) {
                        // If already selected, deselect it (revert to 'all')
                        url.searchParams.delete('date_filter');
                    } else {
                        url.searchParams.set('date_filter', filter);
                    }

                    window.location.href = url.toString();
                },

                async loadScriptsForProject(projectId) {
                    if (!projectId) return;

                    this.isLoadingScripts = true;

                    try {
                        const response = await fetch(`/dashboard/api/projects/${projectId}/test-scripts`);
                        const data = await response.json();

                        if (data.success) {
                            this.scripts = data.scripts || [];
                            this.filteredScripts = this.scripts;

                            // Update script label if needed
                            if (this.filters.script_id) {
                                const script = this.scripts.find(s => s.id === this.filters.script_id);
                                if (script) {
                                    this.filters.scriptLabel = script.name;
                                }
                            }
                        } else {
                            console.error('Failed to load scripts:', data.message);
                        }
                    } catch (error) {
                        console.error('Error loading scripts:', error);
                    } finally {
                        this.isLoadingScripts = false;
                    }
                },

                async loadEnvironmentsForProject(projectId) {
                    if (!projectId) return;

                    this.isLoadingEnvironments = true;

                    try {
                        const response = await fetch(`/dashboard/api/projects/${projectId}/environments`);
                        const data = await response.json();

                        if (data.success) {
                            this.environments = data.environments || [];
                            this.filteredEnvironments = this.environments;

                            // Update environment label if needed
                            if (this.filters.environment_id) {
                                const env = this.environments.find(e => e.id === this.filters.environment_id);
                                if (env) {
                                    this.filters.environmentLabel = env.name;
                                }
                            }
                        } else {
                            console.error('Failed to load environments:', data.message);
                        }
                    } catch (error) {
                        console.error('Error loading environments:', error);
                    } finally {
                        this.isLoadingEnvironments = false;
                    }
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

        // Helper function to get date filter label
        function getDateFilterLabel(filter) {
            const labels = {
                'today': 'Today',
                'week': 'This Week',
                'month': 'This Month'
            };
            return labels[filter] || 'All Time';
        }
    </script>
@endpush
