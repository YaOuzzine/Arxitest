@extends('layouts.dashboard')

@section('title', $project->name)

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects') }}"
            class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
            Projects
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">{{ $project->name }}</span>
    </li>
@endsection

@section('content')
<div class="h-full" x-data="projectDetails('{{ $project->id }}')" x-init="$nextTick(() => {
    @if(session('success'))
    showNotificationMessage('success', '{{ session('success') }}');
    @endif
    @if(session('error'))
    showNotificationMessage('error', '{{ session('error') }}');
    @endif
})">
        <!-- Project Header -->
        <div
            class="mb-6 bg-gradient-to-br from-white/90 to-white/50 dark:from-zinc-800/90 dark:to-zinc-800/50 rounded-2xl shadow-lg border border-zinc-200/50 dark:border-zinc-700/30 backdrop-blur-sm transition-all duration-300">
            <div class="p-6">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div class="flex flex-col md:flex-row gap-5 items-start">
                        <!-- Project Icon -->
                        <div
                            class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-zinc-800 to-zinc-600 dark:from-zinc-700 dark:to-zinc-500 rounded-2xl shadow-lg flex items-center justify-center text-white">
                            <i data-lucide="folder" class="w-8 h-8"></i>
                        </div>

                        <!-- Project Info -->
                        <div>
                            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white flex items-center">
                                {{ $project->name }}
                                <span
                                    class="ml-2 px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Active</span>
                            </h1>
                            <p class="mt-1 text-zinc-600 dark:text-zinc-400 text-sm max-w-2xl">
                                {{ $project->description ?: 'No description provided' }}
                            </p>
                            <div class="flex items-center gap-4 mt-3">
                                <div class="flex items-center text-zinc-500 dark:text-zinc-400 text-xs">
                                    <i data-lucide="calendar" class="w-4 h-4 mr-1"></i>
                                    Created {{ $project->created_at->diffForHumans() }}
                                </div>
                                <div class="flex items-center text-zinc-500 dark:text-zinc-400 text-xs">
                                    <i data-lucide="layers" class="w-4 h-4 mr-1"></i>
                                    {{ $project->testSuites->count() }}
                                    {{ Str::plural('Test Suite', $project->testSuites->count()) }}
                                </div>
                                <div class="flex items-center text-zinc-500 dark:text-zinc-400 text-xs">
                                    <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i>
                                    {{ $project->testSuites->flatMap->testCases->count() }}
                                    {{ Str::plural('Test Case', $project->testSuites->flatMap->testCases->count()) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-3 mt-2 md:mt-0">
                        <a href="{{ route('dashboard.projects.edit', $project->id) }}" class="btn-outline">
                            <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                            Edit Project
                        </a>
                        <div class="relative" x-data="{ showMenu: false }">
                            <button @click="showMenu = !showMenu" type="button" class="btn-outline">
                                <i data-lucide="more-horizontal" class="w-4 h-4 mr-1"></i>
                                Actions
                            </button>
                            <div x-show="showMenu" @click.away="showMenu = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white dark:bg-zinc-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                                <div class="py-1" role="none">
                                    <a href="#"
                                        class="text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 block px-4 py-2 text-sm">
                                        <div class="flex items-center">
                                            <i data-lucide="play" class="w-4 h-4 mr-2"></i>
                                            Run All Tests
                                        </div>
                                    </a>
                                    <a href="#"
                                        class="text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 block px-4 py-2 text-sm">
                                        <div class="flex items-center">
                                            <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                                            Export Project
                                        </div>
                                    </a>
                                    <a href="#"
                                        class="text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 block px-4 py-2 text-sm">
                                        <div class="flex items-center">
                                            <i data-lucide="copy" class="w-4 h-4 mr-2"></i>
                                            Clone Project
                                        </div>
                                    </a>
                                    <div class="border-t border-zinc-200 dark:border-zinc-700 my-1"></div>
                                    <button @click="checkDeleteModal(); showMenu = false" type="button"
                                        class="text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10 w-full text-left px-4 py-2 text-sm">
                                        <div class="flex items-center">
                                            <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
                                            Delete Project
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Tabs -->
            <div class="border-t border-zinc-200 dark:border-zinc-700">
                <nav class="flex overflow-x-auto" aria-label="Tabs">
                    <button @click="activeTab = 'overview'"
                        :class="activeTab === 'overview' ? 'border-zinc-800 dark:border-white text-zinc-800 dark:text-white' :
                            'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-700'"
                        class="whitespace-nowrap border-b-2 py-4 px-6 font-medium text-sm flex items-center">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 mr-2"></i>
                        Overview
                    </button>
                    <button @click="activeTab = 'test-suites'"
                        :class="activeTab === 'test-suites' ?
                            'border-zinc-800 dark:border-white text-zinc-800 dark:text-white' :
                            'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-700'"
                        class="whitespace-nowrap border-b-2 py-4 px-6 font-medium text-sm flex items-center">
                        <i data-lucide="layers" class="w-4 h-4 mr-2"></i>
                        Test Suites
                    </button>
                    <button @click="activeTab = 'executions'"
                        :class="activeTab === 'executions' ? 'border-zinc-800 dark:border-white text-zinc-800 dark:text-white' :
                            'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-700'"
                        class="whitespace-nowrap border-b-2 py-4 px-6 font-medium text-sm flex items-center">
                        <i data-lucide="play" class="w-4 h-4 mr-2"></i>
                        Executions
                    </button>
                    <button @click="activeTab = 'integrations'"
                        :class="activeTab === 'integrations' ?
                            'border-zinc-800 dark:border-white text-zinc-800 dark:text-white' :
                            'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-700'"
                        class="whitespace-nowrap border-b-2 py-4 px-6 font-medium text-sm flex items-center">
                        <i data-lucide="link" class="w-4 h-4 mr-2"></i>
                        Integrations
                    </button>
                    <button @click="activeTab = 'settings'"
                        :class="activeTab === 'settings' ? 'border-zinc-800 dark:border-white text-zinc-800 dark:text-white' :
                            'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-700'"
                        class="whitespace-nowrap border-b-2 py-4 px-6 font-medium text-sm flex items-center">
                        <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                        Settings
                    </button>
                </nav>
            </div>
        </div>

        <!-- Floating Notification -->
        <div x-show="showNotification" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2"
            class="fixed bottom-6 right-6 z-50 max-w-sm w-full shadow-lg border rounded-xl p-4"
            :class="{
                'bg-green-50/80 border-green-200/50 dark:bg-green-900/30 dark:border-green-800/30': notificationType === 'success',
                'bg-red-50/80 border-red-200/50 dark:bg-red-900/30 dark:border-red-800/30': notificationType === 'error'
            }">
            <div class="flex items-start">
                <div x-show="notificationType === 'success'"
                    class="flex-shrink-0 w-5 h-5 mr-3 text-green-600 dark:text-green-400">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                </div>
                <div x-show="notificationType === 'error'"
                    class="flex-shrink-0 w-5 h-5 mr-3 text-red-600 dark:text-red-400">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-medium mb-1"
                        :class="{
                            'text-green-800 dark:text-green-200': notificationType === 'success',
                            'text-red-800 dark:text-red-200': notificationType === 'error'
                        }">
                        <span x-show="notificationType === 'success'">Success</span>
                        <span x-show="notificationType === 'error'">Error</span>
                    </h4>
                    <p class="text-sm"
                        :class="{
                            'text-green-700/90 dark:text-green-300/90': notificationType === 'success',
                            'text-red-700/90 dark:text-red-300/90': notificationType === 'error'
                        }"
                        x-text="notificationMessage"></p>
                </div>
                <button @click="hideNotification"
                    class="ml-4 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Overview Tab Content -->
        <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Test Cases Card -->
                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 transition-all duration-300 hover:shadow-md hover:translate-y-[-2px]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div
                                class="p-2 bg-blue-100/50 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                                <i data-lucide="check-circle" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Test Cases</h3>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $stats->totalTestCases }}</div>
                        <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400 flex items-center">
                            <i data-lucide="arrow-up" class="w-4 h-4 mr-1 text-green-500"></i>
                            <span>{{ $stats->testCasesGrowth }}% growth</span>
                        </div>
                    </div>
                </div>

                <!-- Pass Rate Card -->
                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 transition-all duration-300 hover:shadow-md hover:translate-y-[-2px]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div
                                class="p-2 bg-green-100/50 dark:bg-green-900/30 rounded-lg text-green-600 dark:text-green-400">
                                <i data-lucide="percent" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Pass Rate</h3>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $stats->passRate }}%</div>
                        <div class="mt-2 w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2.5">
                            <div class="bg-green-500 h-2.5 rounded-full" style="width: {{ $stats->passRate }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Last Execution Card -->
                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 transition-all duration-300 hover:shadow-md hover:translate-y-[-2px]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div
                                class="p-2 bg-purple-100/50 dark:bg-purple-900/30 rounded-lg text-purple-600 dark:text-purple-400">
                                <i data-lucide="clock" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Last Run</h3>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $stats->lastExecutionTime }}
                        </div>
                        <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $stats->lastExecutionStatus }}
                        </div>
                    </div>
                </div>

                <!-- Avg Execution Time Card -->
                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 transition-all duration-300 hover:shadow-md hover:translate-y-[-2px]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div
                                class="p-2 bg-orange-100/50 dark:bg-orange-900/30 rounded-lg text-orange-600 dark:text-orange-400">
                                <i data-lucide="timer" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Avg. Time</h3>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $stats->avgExecutionTime }}</div>
                        <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            per test suite execution
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Suite Distribution Chart Card -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div
                    class="lg:col-span-2 bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Test Execution History</h3>
                        <div class="flex space-x-2">
                            <button
                                class="px-3 py-1 text-xs font-medium rounded-md bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200">
                                7 Days
                            </button>
                            <button
                                class="px-3 py-1 text-xs font-medium rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                30 Days
                            </button>
                            <button
                                class="px-3 py-1 text-xs font-medium rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                90 Days
                            </button>
                        </div>
                    </div>
                    <div class="h-64">
                        <!-- Placeholder for Chart -->
                        <div
                            class="w-full h-full flex items-center justify-center text-zinc-400 dark:text-zinc-500 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700">
                            <div class="text-center">
                                <i data-lucide="bar-chart-2" class="w-10 h-10 mx-auto mb-2"></i>
                                <p>Test Execution Graph</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200 mb-6">Test Suite Distribution</h3>
                    <div class="h-64">
                        <!-- Placeholder for Pie Chart -->
                        <div
                            class="w-full h-full flex items-center justify-center text-zinc-400 dark:text-zinc-500 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700">
                            <div class="text-center">
                                <i data-lucide="pie-chart" class="w-10 h-10 mx-auto mb-2"></i>
                                <p>Test Distribution Chart</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity & Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div
                    class="lg:col-span-2 bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Recent Activity</h3>
                        <a href="#"
                            class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                            View All
                        </a>
                    </div>
                    <div class="space-y-5">
                        @foreach ($recentActivities as $activity)
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <img class="h-10 w-10 rounded-full" src="{{ $activity->user->avatar_url }}"
                                        alt="{{ $activity->user->name }}">
                                </div>
                                <div class="ml-3 min-w-0 flex-1">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $activity->user->name }}
                                    </p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {!! $activity->description !!}
                                    </p>
                                    <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200 mb-6">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="#"
                            class="flex items-center p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/30 hover:bg-zinc-100 dark:hover:bg-zinc-700/50 transition-colors">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">Create Test Suite</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Add a new test suite to this project
                                </p>
                            </div>
                        </a>
                        <a href="#"
                            class="flex items-center p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/30 hover:bg-zinc-100 dark:hover:bg-zinc-700/50 transition-colors">
                            <div
                                class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg text-green-600 dark:text-green-400">
                                <i data-lucide="play" class="w-4 h-4"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">Run All Tests</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Execute all test suites in this project
                                </p>
                            </div>
                        </a>
                        <a href="#"
                            class="flex items-center p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/30 hover:bg-zinc-100 dark:hover:bg-zinc-700/50 transition-colors">
                            <div
                                class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg text-purple-600 dark:text-purple-400">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">Sync with Jira</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Update test cases from user stories</p>
                            </div>
                        </a>
                        <a href="#"
                            class="flex items-center p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/30 hover:bg-zinc-100 dark:hover:bg-zinc-700/50 transition-colors">
                            <div
                                class="p-2 bg-orange-100 dark:bg-orange-900/30 rounded-lg text-orange-600 dark:text-orange-400">
                                <i data-lucide="download" class="w-4 h-4"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">Export Reports</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Download test execution reports</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Test Suites Tab Content -->
        <div x-show="activeTab === 'test-suites'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div
                class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 mb-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Test Suites</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Manage your test suites and their test cases
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="relative">
                            <input type="text" placeholder="Search test suites..."
                                class="pl-9 pr-4 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 w-full text-sm">
                            <div class="absolute left-3 top-2">
                                <i data-lucide="search" class="w-4 h-4 text-zinc-400 dark:text-zinc-500"></i>
                            </div>
                        </div>
                        <button class="btn-primary">
                            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                            New Test Suite
                        </button>
                    </div>
                </div>

                <!-- Test Suites List -->
                <div>
                    @forelse($project->testSuites as $suite)
                        <div
                            class="bg-zinc-50 dark:bg-zinc-700/30 rounded-xl mb-4 border border-zinc-200 dark:border-zinc-700 overflow-hidden transition-all duration-300 hover:shadow-md">
                            <!-- Suite Header -->
                            <div class="flex items-center justify-between p-4">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg text-indigo-600 dark:text-indigo-400">
                                        <i data-lucide="layers" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-base font-medium text-zinc-800 dark:text-zinc-200">
                                            {{ $suite->name }}</h4>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $suite->testCases->count() }}
                                            {{ Str::plural('test case', $suite->testCases->count()) }} •
                                            Last run
                                            {{ $suite->lastExecution ? $suite->lastExecution->created_at->diffForHumans() : 'Never' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span
                                        class="px-2.5 py-1 text-xs font-medium rounded-full
                            @if ($suite->lastExecution && $suite->lastExecution->status === 'passed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                            @elseif($suite->lastExecution && $suite->lastExecution->status === 'failed')
                                bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                            @else
                                bg-zinc-100 text-zinc-800 dark:bg-zinc-600/30 dark:text-zinc-400 @endif">
                                        @if ($suite->lastExecution)
                                            {{ ucfirst($suite->lastExecution->status) }}
                                        @else
                                            Not run
                                        @endif
                                    </span>
                                    <div class="relative" x-data="{ showActions: false }">
                                        <button @click="showActions = !showActions"
                                            class="p-1.5 rounded-md text-zinc-500 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-600">
                                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                        </button>
                                        <!-- Actions Dropdown -->
                                        <div x-show="showActions" @click.away="showActions = false"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white dark:bg-zinc-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                            <div class="py-1">
                                                <a href="#"
                                                    class="text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 block px-4 py-2 text-sm">
                                                    Run Tests
                                                </a>
                                                <a href="#"
                                                    class="text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 block px-4 py-2 text-sm">
                                                    Edit Suite
                                                </a>
                                                <a href="#"
                                                    class="text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 block px-4 py-2 text-sm">
                                                    View Results
                                                </a>
                                                <div class="border-t border-zinc-200 dark:border-zinc-700 my-1"></div>
                                                <a href="#"
                                                    class="text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10 block px-4 py-2 text-sm">
                                                    Delete Suite
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <button
                                        class="p-1.5 rounded-md text-zinc-500 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-600">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Suite Details (Collapsed by default) -->
                            <div class="border-t border-zinc-200 dark:border-zinc-700 p-4 hidden">
                                <!-- Test Cases Table -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                        <thead>
                                            <tr>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                    Test Case
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                    Status
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                    Last Run
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                    Duration
                                                </th>
                                                <th scope="col" class="relative px-4 py-3">
                                                    <span class="sr-only">Actions</span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                            @foreach ($suite->testCases as $testCase)
                                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        <div>
                                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                                {{ $testCase->title }}
                                                            </div>
                                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                                {{ Str::limit($testCase->description, 60) }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        <span
                                                            class="px-2 py-1 text-xs rounded-full
                                            @if ($testCase->lastExecution && $testCase->lastExecution->status === 'passed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                            @elseif($testCase->lastExecution && $testCase->lastExecution->status === 'failed')
                                                bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                            @else
                                                bg-zinc-100 text-zinc-800 dark:bg-zinc-600/30 dark:text-zinc-400 @endif">
                                                            @if ($testCase->lastExecution)
                                                                {{ ucfirst($testCase->lastExecution->status) }}
                                                            @else
                                                                Not run
                                                            @endif
                                                        </span>
                                                    </td>
                                                    <td
                                                        class="px-4 py-3 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                                        {{ $testCase->lastExecution ? $testCase->lastExecution->created_at->diffForHumans() : 'Never' }}
                                                    </td>
                                                    <td
                                                        class="px-4 py-3 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                                        {{ $testCase->lastExecution ? $testCase->lastExecution->duration : '-' }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                                        <a href="#"
                                                            class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                                            View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div
                            class="bg-zinc-50 dark:bg-zinc-800/50 border border-dashed border-zinc-300 dark:border-zinc-700 rounded-xl p-8 text-center">
                            <div
                                class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-700 mb-4">
                                <i data-lucide="layers" class="w-8 h-8 text-zinc-400 dark:text-zinc-500"></i>
                            </div>
                            <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200 mb-2">No Test Suites Yet</h3>
                            <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-6">
                                Get started by creating your first test suite to organize your test cases.
                            </p>
                            <button class="btn-primary">
                                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                Create First Test Suite
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Executions Tab Content -->
        <div x-show="activeTab === 'executions'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div
                class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 mb-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Test Executions</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            View history of test runs and their results
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="relative">
                            <select
                                class="pl-4 pr-9 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 appearance-none text-sm">
                                <option>All Suites</option>
                                @foreach ($project->testSuites as $suite)
                                    <option value="{{ $suite->id }}">{{ $suite->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-3 top-2.5 pointer-events-none">
                                <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400 dark:text-zinc-500"></i>
                            </div>
                        </div>
                        <button class="btn-primary">
                            <i data-lucide="play" class="w-4 h-4 mr-2"></i>
                            Run Tests
                        </button>
                    </div>
                </div>

                <!-- Executions Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead>
                            <tr>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    ID/Suite
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Environment
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Started By
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Time
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Duration
                                </th>
                                <th scope="col" class="relative px-4 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @if (count($testExecutions) > 0)
                                @foreach ($testExecutions as $execution)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                    #{{ $execution->id }}
                                                </div>
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ $execution->testSuite->name }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span
                                                class="px-2.5 py-1 text-xs rounded-full
                                    @if ($execution->status === 'passed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                    @elseif($execution->status === 'failed')
                                        bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                    @elseif($execution->status === 'running')
                                        bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                    @else
                                        bg-zinc-100 text-zinc-800 dark:bg-zinc-600/30 dark:text-zinc-400 @endif">
                                                {{ ucfirst($execution->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $execution->environment }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <img class="h-6 w-6 rounded-full mr-2"
                                                    src="{{ $execution->initiator->avatar_url }}"
                                                    alt="{{ $execution->initiator->name }}">
                                                <span
                                                    class="text-sm text-zinc-800 dark:text-zinc-300">{{ $execution->initiator->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $execution->created_at->format('M d, Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $execution->duration }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="#"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                                View Report
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                        <div class="flex flex-col items-center">
                                            <i data-lucide="clipboard-list"
                                                class="w-8 h-8 mb-3 text-zinc-400 dark:text-zinc-500"></i>
                                            <p>No test executions found</p>
                                            <button class="btn-secondary mt-3 text-sm">
                                                <i data-lucide="play" class="w-3 h-3 mr-1"></i>
                                                Run First Test
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Integrations Tab Content -->
        <div x-show="activeTab === 'integrations'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div
                class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 mb-6">
                <div class="flex flex-col md:flex-row items-start justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Integrations</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Connect your project with external systems
                        </p>
                    </div>
                    <button class="btn-primary">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        Add Integration
                    </button>
                </div>

                <!-- Integrations Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Jira Integration -->
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center">
                                <div
                                    class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                                    <i data-lucide="trello" class="w-5 h-5"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-base font-medium text-zinc-800 dark:text-zinc-200">Jira</h4>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Issue tracking</p>
                                </div>
                            </div>
                            <span
                                class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                Connected
                            </span>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                <span class="font-medium">Project:</span> ARXITEST
                            </p>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                <span class="font-medium">Base URL:</span> jira.company.com
                            </p>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button
                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                Configure
                            </button>
                        </div>
                    </div>

                    <!-- GitHub Integration -->
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center">
                                <div
                                    class="p-2 bg-zinc-100 dark:bg-zinc-800/50 rounded-lg text-zinc-800 dark:text-zinc-300">
                                    <i data-lucide="github" class="w-5 h-5"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-base font-medium text-zinc-800 dark:text-zinc-200">GitHub</h4>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Repository integration</p>
                                </div>
                            </div>
                            <span
                                class="px-2 py-1 text-xs rounded-full bg-zinc-100 text-zinc-800 dark:bg-zinc-600/30 dark:text-zinc-400">
                                Not Connected
                            </span>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                Connect to GitHub to sync test scripts with your repository.
                            </p>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button
                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                Connect
                            </button>
                        </div>
                    </div>

                    <!-- Slack Integration -->
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center">
                                <div
                                    class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg text-purple-600 dark:text-purple-400">
                                    <i data-lucide="message-square" class="w-5 h-5"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-base font-medium text-zinc-800 dark:text-zinc-200">Slack</h4>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Notifications</p>
                                </div>
                            </div>
                            <span
                                class="px-2 py-1 text-xs rounded-full bg-zinc-100 text-zinc-800 dark:bg-zinc-600/30 dark:text-zinc-400">
                                Not Connected
                            </span>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                Send test execution notifications to your Slack channels.
                            </p>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button
                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                Connect
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Tab Content -->
        <div x-show="activeTab === 'settings'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div
                class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 mb-6">
                <div class="flex flex-col md:flex-row items-start justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Project Settings</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Configure your project preferences
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button type="button" class="btn-secondary">
                            Cancel
                        </button>
                        <button type="submit" class="btn-primary">
                            Save Changes
                        </button>
                    </div>
                </div>

                <!-- Project Settings Form -->
                <form>
                    <div class="space-y-6">
                        <div>
                            <h4 class="text-base font-medium text-zinc-800 dark:text-zinc-200 mb-3">General</h4>
                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label for="project-name"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Project Name
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" name="project-name" id="project-name"
                                            class="block w-full rounded-md border-zinc-300 dark:border-zinc-600 shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:bg-zinc-700/50 dark:text-white sm:text-sm"
                                            value="{{ $project->name }}">
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="default-framework"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Default Framework
                                    </label>
                                    <div class="mt-1">
                                        <select id="default-framework" name="default-framework"
                                            class="block w-full rounded-md border-zinc-300 dark:border-zinc-600 shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:bg-zinc-700/50 dark:text-white sm:text-sm">
                                            <option value="selenium-python"
                                                {{ $project->settings['default_framework'] === 'selenium-python' ? 'selected' : '' }}>
                                                Selenium (Python)</option>
                                            <option value="cypress"
                                                {{ $project->settings['default_framework'] === 'cypress' ? 'selected' : '' }}>
                                                Cypress</option>
                                            <option value="playwright"
                                                {{ $project->settings['default_framework'] === 'playwright' ? 'selected' : '' }}>
                                                Playwright</option>
                                            <option value="rest-assured"
                                                {{ $project->settings['default_framework'] === 'rest-assured' ? 'selected' : '' }}>
                                                REST Assured</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="project-description"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Description
                                    </label>
                                    <div class="mt-1">
                                        <textarea id="project-description" name="project-description" rows="3"
                                            class="block w-full rounded-md border-zinc-300 dark:border-zinc-600 shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:bg-zinc-700/50 dark:text-white sm:text-sm">{{ $project->description }}</textarea>
                                    </div>
                                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                        A brief description of your project and its purpose
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6">
                            <h4 class="text-base font-medium text-zinc-800 dark:text-zinc-200 mb-3">Test Generation</h4>

                            <div class="flex items-center mb-4">
                                <div class="flex h-5 items-center">
                                    <input id="auto-generate-tests" name="auto-generate-tests" type="checkbox"
                                        class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-zinc-600 focus:ring-zinc-500"
                                        {{ $project->settings['auto_generate_tests'] ? 'checked' : '' }}>
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="auto-generate-tests"
                                        class="font-medium text-zinc-700 dark:text-zinc-300">Auto-generate tests from user
                                        stories</label>
                                    <p class="text-zinc-500 dark:text-zinc-400">Arxitest will automatically create test
                                        scripts from Jira user stories</p>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <div class="flex h-5 items-center">
                                    <input id="execute-on-creation" name="execute-on-creation" type="checkbox"
                                        class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-zinc-600 focus:ring-zinc-500"
                                        {{ isset($project->settings['execute_on_creation']) && $project->settings['execute_on_creation'] ? 'checked' : '' }}>
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="execute-on-creation"
                                        class="font-medium text-zinc-700 dark:text-zinc-300">Execute tests automatically
                                        upon creation</label>
                                    <p class="text-zinc-500 dark:text-zinc-400">Newly created tests will be executed
                                        immediately</p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6">
                            <h4 class="text-base font-medium text-zinc-800 dark:text-zinc-200 mb-3">Environments</h4>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label for="default-environment"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Default Environment
                                    </label>
                                    <div class="mt-1">
                                        <select id="default-environment" name="default-environment"
                                            class="block w-full rounded-md border-zinc-300 dark:border-zinc-600 shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:bg-zinc-700/50 dark:text-white sm:text-sm">
                                            <option value="development"
                                                {{ isset($project->settings['default_environment']) && $project->settings['default_environment'] === 'development' ? 'selected' : '' }}>
                                                Development</option>
                                            <option value="staging"
                                                {{ isset($project->settings['default_environment']) && $project->settings['default_environment'] === 'staging' ? 'selected' : '' }}>
                                                Staging</option>
                                            <option value="production"
                                                {{ isset($project->settings['default_environment']) && $project->settings['default_environment'] === 'production' ? 'selected' : '' }}>
                                                Production</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="container-timeout"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Container Timeout (seconds)
                                    </label>
                                    <div class="mt-1">
                                        <input type="number" name="container-timeout" id="container-timeout"
                                            class="block w-full rounded-md border-zinc-300 dark:border-zinc-600 shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:bg-zinc-700/50 dark:text-white sm:text-sm"
                                            value="{{ $project->settings['container_timeout'] ?? 600 }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6">
                            <h4 class="text-base font-medium text-red-600 dark:text-red-400 mb-3">Danger Zone</h4>

                            <div
                                class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i data-lucide="alert-triangle"
                                            class="h-5 w-5 text-red-600 dark:text-red-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h5 class="text-sm font-medium text-red-800 dark:text-red-300">Delete this project
                                        </h5>
                                        <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                                            Once you delete a project, there is no going back. All data, test suites, and
                                            test cases associated with this project will be permanently deleted.
                                        </p>
                                        <div class="mt-4">
                                            <button @click="checkDeleteModal" type="button"
                                                class="inline-flex items-center px-3 py-2 border border-red-300 dark:border-red-700 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 dark:text-red-400 bg-white dark:bg-zinc-800 hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-zinc-800">
                                                Delete Project
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Project Confirmation Modal -->
        <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            x-cloak>
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-zinc-500 dark:bg-zinc-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity"
                    aria-hidden="true" @click="showDeleteModal = false">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    @click.away="showDeleteModal = false"
                    class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full px-4 pt-5 pb-4 sm:p-6">
                    <div>
                        <div
                            class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30">
                            <i data-lucide="trash-2" class="h-6 w-6 text-red-600 dark:text-red-400"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-white" id="modal-title">
                                Delete Project
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    Are you sure you want to delete the project "<span
                                        class="font-medium text-zinc-800 dark:text-zinc-200">{{ $project->name }}</span>"?
                                    This action cannot be undone and all associated data will be permanently deleted.
                                </p>
                            </div>
                            <div class="mt-4">
                                <div class="flex items-center">
                                    <input id="confirm-delete" type="checkbox"
                                        class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-red-600 focus:ring-red-500">
                                    <label for="confirm-delete"
                                        class="ml-2 block text-sm text-zinc-700 dark:text-zinc-300">
                                        I understand that this action is irreversible
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button @click="showDeleteModal = false" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-zinc-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 sm:mt-0 sm:col-start-1 sm:text-sm">
                            Cancel
                        </button>
                        <button @click="deleteProject()" :disabled="isDeleting" type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-zinc-800 sm:col-start-2 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isDeleting">Delete Project</span>
                            <span x-show="isDeleting" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Deleting...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Custom styles for project view */
        .btn-primary {
            @apply inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200;
        }

        .btn-secondary {
            @apply inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200;
        }

        .btn-outline {
            @apply inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();

            // Toggle test suite details when clicking on header
            document.querySelectorAll('.test-suite-toggle').forEach(button => {
                button.addEventListener('click', function() {
                    const details = this.closest('.test-suite-item').querySelector(
                        '.test-suite-details');
                    details.classList.toggle('hidden');

                    // Animate the chevron icon
                    const icon = this.querySelector('svg');
                    icon.classList.toggle('rotate-180');
                });
            });
        });
    </script>
@endpush
