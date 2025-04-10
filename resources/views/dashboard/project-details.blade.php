@extends('layouts.dashboard')

@section('title', 'Project Details')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard') }}?page=projects" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
            Projects
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300" x-text="project.name"></span>
    </li>
@endsection

@section('content')
<div class="h-full" x-data="{
    activeTab: 'overview',
    project: {
        name: 'User Management',
        description: 'Tests for user registration, authentication, and profile management features.',
        test_suites: 3,
        test_cases: 18,
        pass_rate: 92,
        last_execution: '2 hours ago'
    },
    testSuites: [
        {
            id: 'TS-001',
            name: 'User Authentication',
            cases: 8,
            last_run: '2 hours ago',
            status: 'passed',
            pass_rate: 100
        },
        {
            id: 'TS-002',
            name: 'User Registration',
            cases: 6,
            last_run: 'Yesterday',
            status: 'failed',
            pass_rate: 83
        },
        {
            id: 'TS-003',
            name: 'Profile Management',
            cases: 4,
            last_run: '3 days ago',
            status: 'passed',
            pass_rate: 100
        }
    ],
    environments: [
        { id: 'ENV-001', name: 'Development', test_count: 42, pass_rate: 95 },
        { id: 'ENV-002', name: 'Staging', test_count: 36, pass_rate: 89 },
        { id: 'ENV-003', name: 'Production', test_count: 24, pass_rate: 100 }
    ]
}">
    <div class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-12 w-12 rounded-lg bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 flex items-center justify-center">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" x-text="project.name"></h1>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400" x-text="project.description"></p>
                    </div>
                </div>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <button class="btn-secondary inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                    <i data-lucide="settings" class="mr-2 -ml-1 w-4 h-4"></i>
                    Settings
                </button>
                <button class="btn-primary inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                    <i data-lucide="play" class="mr-2 -ml-1 w-4 h-4"></i>
                    Run Tests
                </button>
            </div>
        </div>

        <div class="mt-6 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex space-x-8">
                <button
                    @click="activeTab = 'overview'"
                    class="py-2 px-1 font-medium text-sm transition-colors duration-200 relative"
                    :class="activeTab === 'overview' ? 'text-zinc-800 dark:text-white' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'"
                >
                    Overview
                    <span
                        class="absolute bottom-0 inset-x-0 h-0.5 transition-transform duration-200 transform"
                        :class="activeTab === 'overview' ? 'bg-zinc-800 dark:bg-white scale-x-100' : 'scale-x-0'"
                    ></span>
                </button>
                <button
                    @click="activeTab = 'test-suites'"
                    class="py-2 px-1 font-medium text-sm transition-colors duration-200 relative"
                    :class="activeTab === 'test-suites' ? 'text-zinc-800 dark:text-white' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'"
                >
                    Test Suites
                    <span
                        class="absolute bottom-0 inset-x-0 h-0.5 transition-transform duration-200 transform"
                        :class="activeTab === 'test-suites' ? 'bg-zinc-800 dark:bg-white scale-x-100' : 'scale-x-0'"
                    ></span>
                </button>
                <button
                    @click="activeTab = 'environments'"
                    class="py-2 px-1 font-medium text-sm transition-colors duration-200 relative"
                    :class="activeTab === 'environments' ? 'text-zinc-800 dark:text-white' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'"
                >
                    Environments
                    <span
                        class="absolute bottom-0 inset-x-0 h-0.5 transition-transform duration-200 transform"
                        :class="activeTab === 'environments' ? 'bg-zinc-800 dark:bg-white scale-x-100' : 'scale-x-0'"
                    ></span>
                </button>
                <button
                    @click="activeTab = 'integrations'"
                    class="py-2 px-1 font-medium text-sm transition-colors duration-200 relative"
                    :class="activeTab === 'integrations' ? 'text-zinc-800 dark:text-white' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'"
                >
                    Integrations
                    <span
                        class="absolute bottom-0 inset-x-0 h-0.5 transition-transform duration-200 transform"
                        :class="activeTab === 'integrations' ? 'bg-zinc-800 dark:bg-white scale-x-100' : 'scale-x-0'"
                    ></span>
                </button>
            </div>
        </div>

        <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg p-6 border border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-4">Project Summary</h3>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-zinc-500 dark:text-zinc-400">Test Suites:</dt>
                            <dd class="text-zinc-900 dark:text-white font-medium" x-text="project.test_suites"></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500 dark:text-zinc-400">Test Cases:</dt>
                            <dd class="text-zinc-900 dark:text-white font-medium" x-text="project.test_cases"></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500 dark:text-zinc-400">Pass Rate:</dt>
                            <dd class="text-zinc-900 dark:text-white font-medium" x-text="project.pass_rate + '%'">
                                <div class="w-full bg-zinc-200 dark:bg-zinc-600 rounded-full h-2 mt-1">
                                    <div class="h-2 rounded-full"
                                         :class="{
                                             'bg-green-500': project.pass_rate >= 90,
                                             'bg-yellow-500': project.pass_rate >= 75 && project.pass_rate < 90,
                                             'bg-red-500': project.pass_rate < 75
                                         }"
                                         :style="{ width: project.pass_rate + '%' }"></div>
                                </div>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-zinc-500 dark:text-zinc-400">Last Execution:</dt>
                            <dd class="text-zinc-900 dark:text-white font-medium" x-text="project.last_execution"></dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg p-6 border border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-4">Execution History</h3>
                    <div class="h-48">
                        <canvas id="execution-history-chart"></canvas>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg p-6 border border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-4">Test Case Distribution</h3>
                    <div class="h-48 flex items-center justify-center">
                        <canvas id="test-distribution-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'test-suites'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
            <div class="mb-6 flex justify-between items-center mt-6">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Test Suites</h3>
                <button class="btn-primary inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                    <i data-lucide="plus" class="mr-2 -ml-1 w-4 h-4"></i>
                    New Test Suite
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="suite in testSuites" :key="suite.id">
                    <div class="card-hover bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 transition-all duration-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-md bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                    <i data-lucide="layers" class="w-5 h-5"></i>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-medium text-zinc-900 dark:text-white" x-text="suite.name"></h4>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400" x-text="suite.id"></span>
                                </div>
                            </div>
                            <div>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': suite.status === 'passed',
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': suite.status === 'failed',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': suite.status === 'running',
                                        'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300': suite.status === 'pending'
                                    }"
                                    x-text="suite.status === 'passed' ? 'Passed' : suite.status === 'failed' ? 'Failed' : suite.status === 'running' ? 'Running' : 'Pending'"
                                ></span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Test Cases:</span>
                                <span class="text-zinc-900 dark:text-white font-medium" x-text="suite.cases"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Pass Rate:</span>
                                <span class="text-zinc-900 dark:text-white font-medium" x-text="suite.pass_rate + '%'"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Last Run:</span>
                                <span class="text-zinc-900 dark:text-white font-medium" x-text="suite.last_run"></span>
                            </div>

                            <div class="w-full bg-zinc-200 dark:bg-zinc-600 rounded-full h-2">
                                <div class="h-2 rounded-full"
                                     :class="{
                                         'bg-green-500': suite.pass_rate >= 90,
                                         'bg-yellow-500': suite.pass_rate >= 75 && suite.pass_rate < 90,
                                         'bg-red-500': suite.pass_rate < 75
                                     }"
                                     :style="{ width: suite.pass_rate + '%' }"></div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-between">
                            <button class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 inline-flex items-center">
                                <i data-lucide="edit" class="w-4 h-4 mr-1"></i>
                                Edit
                            </button>
                            <button class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 inline-flex items-center">
                                <i data-lucide="play" class="w-4 h-4 mr-1"></i>
                                Run Tests
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="activeTab === 'environments'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
            <div class="mb-6 flex justify-between items-center mt-6">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Environments</h3>
                <button class="btn-primary inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                    <i data-lucide="plus" class="mr-2 -ml-1 w-4 h-4"></i>
                    Add Environment
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <template x-for="env in environments" :key="env.id">
                    <div class="card-hover bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 transition-all duration-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-md bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                    <i data-lucide="server" class="w-5 h-5"></i>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-medium text-zinc-900 dark:text-white" x-text="env.name"></h4>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400" x-text="env.id"></span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Test Count:</span>
                                <span class="text-zinc-900 dark:text-white font-medium" x-text="env.test_count"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Pass Rate:</span>
                                <span class="text-zinc-900 dark:text-white font-medium" x-text="env.pass_rate + '%'"></span>
                            </div>

                            <div class="w-full bg-zinc-200 dark:bg-zinc-600 rounded-full h-2">
                                <div class="h-2 rounded-full"
                                     :class="{
                                         'bg-green-500': env.pass_rate >= 90,
                                         'bg-yellow-500': env.pass_rate >= 75 && env.pass_rate < 90,
                                         'bg-red-500': env.pass_rate < 75
                                     }"
                                     :style="{ width: env.pass_rate + '%' }"></div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-between">
                            <button class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 inline-flex items-center">
                                <i data-lucide="settings" class="w-4 h-4 mr-1"></i>
                                Configure
                            </button>
                            <button class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 inline-flex items-center">
                                <i data-lucide="play" class="w-4 h-4 mr-1"></i>
                                Run Tests
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="activeTab === 'integrations'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
            <div class="mb-6 flex justify-between items-center mt-6">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Integrations</h3>
                <button class="btn-primary inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                    <i data-lucide="plus" class="mr-2 -ml-1 w-4 h-4"></i>
                    Add Integration
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="card-hover bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full overflow-hidden">
                                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/jira/jira-original.svg" alt="Jira" class="h-full w-full">
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-zinc-900 dark:text-white">Jira</h4>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    Connected
                                </span>
                            </div>
                        </div>
                        <div>
                            <button class="p-1 text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>

                    <div class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">
                        Connected to <span class="font-medium text-zinc-900 dark:text-white">UserManagement</span> project
                    </div>

                    <div class="mt-2 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">Synced issues</span>
                            <span class="text-zinc-900 dark:text-white">32</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">Last synced</span>
                            <span class="text-zinc-900 dark:text-white">1 hour ago</span>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-between">
                        <button class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 inline-flex items-center">
                            <i data-lucide="settings" class="w-4 h-4 mr-1"></i>
                            Configure
                        </button>
                        <button class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 inline-flex items-center">
                            <i data-lucide="refresh-cw" class="w-4 h-4 mr-1"></i>
                            Sync
                        </button>
                    </div>
                </div>

                <div class="card-hover bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full overflow-hidden">
                                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/github/github-original.svg" alt="GitHub" class="h-full w-full dark:bg-white dark:rounded-full">
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-zinc-900 dark:text-white">GitHub</h4>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    Connected
                                </span>
                            </div>
                        </div>
                        <div>
                            <button class="p-1 text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>

                    <div class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">
                        Connected to <span class="font-medium text-zinc-900 dark:text-white">organization/user-service</span> repository
                    </div>

                    <div class="mt-2 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">Linked PRs</span>
                            <span class="text-zinc-900 dark:text-white">8</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">Last commit</span>
                            <span class="text-zinc-900 dark:text-white">Today</span>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-between">
                        <button class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 inline-flex items-center">
                            <i data-lucide="settings" class="w-4 h-4 mr-1"></i>
                            Configure
                        </button>
                        <button class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 inline-flex items-center">
                            <i data-lucide="git-pull-request" class="w-4 h-4 mr-1"></i>
                            View PRs
                        </button>
                    </div>
                </div>

                <div class="border-2 border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg p-6 flex flex-col items-center justify-center text-center hover:border-zinc-400 dark:hover:border-zinc-500 transition-colors duration-200 cursor-pointer">
                    <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mb-4">
                        <i data-lucide="plus" class="w-8 h-8 text-zinc-500 dark:text-zinc-400"></i>
                    </div>
                    <h4 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">Add Integration</h4>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Connect with your favorite tools</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Re-render the charts if the theme changes
        document.addEventListener('themeChanged', function() {
            initializeCharts();
        });

        initializeCharts();

        function initializeCharts() {
            const isDarkMode = document.documentElement.classList.contains('dark');
            const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            const textColor = isDarkMode ? '#9ca3af' : '#6b7280';

            // Execution History Chart
            const executionCtx = document.getElementById('execution-history-chart');
            if (executionCtx) {
                // If there's an existing chart, destroy it
                if (window.executionHistoryChart) {
                    window.executionHistoryChart.destroy();
                }

                // Sample data for execution history
                const historyData = {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [
                        {
                            label: 'Executions',
                            data: [3, 5, 2, 8, 4, 6, 2],
                            borderColor: '#8b5cf6',
                            backgroundColor: isDarkMode ? 'rgba(139, 92, 246, 0.2)' : 'rgba(139, 92, 246, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#8b5cf6',
                            pointBorderColor: isDarkMode ? '#1f2937' : '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }
                    ]
                };

                // Initialize the chart
                window.executionHistoryChart = new Chart(executionCtx, {
                    type: 'line',
                    data: historyData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: isDarkMode ? '#1f2937' : '#ffffff',
                                titleColor: isDarkMode ? '#ffffff' : '#111827',
                                bodyColor: isDarkMode ? '#d1d5db' : '#4b5563',
                                borderColor: isDarkMode ? '#374151' : '#e5e7eb',
                                borderWidth: 1,
                                padding: 12,
                                boxPadding: 6
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: textColor,
                                    font: {
                                        family: "'Inter', sans-serif",
                                        size: 12
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: gridColor,
                                    drawBorder: false
                                },
                                ticks: {
                                    color: textColor,
                                    font: {
                                        family: "'Inter', sans-serif",
                                        size: 12
                                    },
                                    padding: 10,
                                    precision: 0
                                }
                            }
                        },
                        animation: {
                            duration: 1000,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            }

            // Test Distribution Chart (Donut)
            const distributionCtx = document.getElementById('test-distribution-chart');
            if (distributionCtx) {
                // If there's an existing chart, destroy it
                if (window.testDistributionChart) {
                    window.testDistributionChart.destroy();
                }

                // Sample data for test case distribution
                const distributionData = {
                    labels: ['Passed', 'Failed', 'Skipped', 'Pending'],
                    datasets: [
                        {
                            data: [12, 2, 1, 3],
                            backgroundColor: [
                                '#10b981', // Green
                                '#ef4444', // Red
                                '#f59e0b', // Yellow
                                '#6b7280'  // Gray
                            ],
                            borderColor: isDarkMode ? '#1f2937' : '#ffffff',
                            borderWidth: 2,
                            hoverOffset: 4,
                            cutout: '70%'
                        }
                    ]
                };

                // Initialize the chart
                window.testDistributionChart = new Chart(distributionCtx, {
                    type: 'doughnut',
                    data: distributionData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: textColor,
                                    font: {
                                        family: "'Inter', sans-serif",
                                        size: 12
                                    },
                                    padding: 20,
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: isDarkMode ? '#1f2937' : '#ffffff',
                                titleColor: isDarkMode ? '#ffffff' : '#111827',
                                bodyColor: isDarkMode ? '#d1d5db' : '#4b5563',
                                borderColor: isDarkMode ? '#374151' : '#e5e7eb',
                                borderWidth: 1,
                                padding: 12,
                                boxPadding: 6,
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((acc, data) => acc + data, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        animation: {
                            duration: 1000,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            }
        }

        // Add animations to the cards
        const cards = document.querySelectorAll('.card-hover');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.classList.add('transform', 'shadow-md');
            });

            card.addEventListener('mouseleave', function() {
                this.classList.remove('shadow-md');
            });
        });
    });
</script>
@endpush
