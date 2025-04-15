@extends('layouts.dashboard')

{{-- Blade DocBlock --}}
@php
    /**
     * @var \App\Models\Project $project // Includes loaded relations: team, testSuites, testExecutions, projectIntegrations
     * @var stdClass $stats // Calculated statistics object
     * @var \Illuminate\Support\Collection $recentActivities // Collection of recent activity objects
     */
@endphp


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
    <div class="h-full" x-data="projectDetails({
        projectId: '{{ $project->id }}',
        projectName: {{ json_encode($project->name) }},
        initialTab: '{{ request()->query('tab', 'overview') }}',
        stats: {{ json_encode($stats) }},
        testSuitesData: {{ json_encode($project->testSuites) }},
        // Use the separate executions variable passed from the controller
        executionsData: {{ json_encode($executions) }},
        integrationsData: {{ json_encode($project->projectIntegrations) }},
        projectSettingsData: {{ json_encode($project->settings) }},
        recentActivitiesData: {{ json_encode($recentActivities) }},
        csrfToken: '{{ csrf_token() }}'
    })" x-init="initAlpine()">

        <!-- Notification Toast Container -->
        <div x-show="notification.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2"
            class="fixed bottom-6 right-6 z-[100] max-w-sm w-full shadow-lg border rounded-xl p-4 backdrop-blur-sm"
            :class="notification.type === 'success' ?
                'bg-green-50/90 border-green-200/50 dark:bg-green-900/40 dark:border-green-800/30' :
                'bg-red-50/90 border-red-200/50 dark:bg-red-900/40 dark:border-red-800/30'"
            x-cloak>
            <div class="flex items-start">
                <i data-lucide="check-circle" x-show="notification.type === 'success'"
                    class="w-5 h-5 mr-3 text-green-600 dark:text-green-400 flex-shrink-0"></i>
                <i data-lucide="alert-circle" x-show="notification.type === 'error'"
                    class="w-5 h-5 mr-3 text-red-600 dark:text-red-400 flex-shrink-0"></i>
                <div class="flex-1">
                    <h4 class="font-medium mb-1"
                        :class="notification.type === 'success' ? 'text-green-800 dark:text-green-200' :
                            'text-red-800 dark:text-red-200'"
                        x-text="notification.title"></h4>
                    <p class="text-sm"
                        :class="notification.type === 'success' ? 'text-green-700/90 dark:text-green-300/90' :
                            'text-red-700/90 dark:text-red-300/90'"
                        x-text="notification.message"></p>
                </div>
                <button @click="notification.show = false"
                    class="ml-4 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 flex-shrink-0">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Project Header -->
        <div
            class="mb-6 bg-gradient-to-br from-white/90 to-white/50 dark:from-zinc-800/90 dark:to-zinc-800/50 rounded-2xl shadow-lg border border-zinc-200/50 dark:border-zinc-700/30 backdrop-blur-sm transition-all duration-300">
            {{-- Project Header Content --}}
            <div class="p-6">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div class="flex flex-col md:flex-row gap-5 items-start">
                        {{-- Project Icon --}}
                        <div
                            class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-zinc-800 to-zinc-600 dark:from-zinc-700 dark:to-zinc-500 rounded-2xl shadow-lg flex items-center justify-center text-white">
                            <i data-lucide="folder-git-2" class="w-8 h-8"></i> {{-- Changed icon --}}
                        </div>
                        {{-- Project Info --}}
                        <div>
                            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white flex items-center">
                                {{ $project->name }}
                                {{-- Add status badge if needed --}}
                                {{-- <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Active</span> --}}
                            </h1>
                            <p class="mt-1 text-zinc-600 dark:text-zinc-400 text-sm max-w-2xl">
                                {{ $project->description ?: 'No description provided' }}
                            </p>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-3">
                                <div class="flex items-center text-zinc-500 dark:text-zinc-400 text-xs"
                                    title="Created Date">
                                    <i data-lucide="calendar" class="w-4 h-4 mr-1"></i>
                                    Created {{ $project->created_at->diffForHumans() }}
                                </div>
                                <div class="flex items-center text-zinc-500 dark:text-zinc-400 text-xs" title="Test Suites">
                                    <i data-lucide="layers" class="w-4 h-4 mr-1"></i>
                                    <span x-text="stats.totalTestSuites"></span>
                                    {{ Str::plural('Suite', $stats->totalTestSuites) }}
                                </div>
                                <div class="flex items-center text-zinc-500 dark:text-zinc-400 text-xs" title="Test Cases">
                                    <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i>
                                    <span x-text="stats.totalTestCases"></span>
                                    {{ Str::plural('Case', $stats->totalTestCases) }}
                                </div>
                                <div class="flex items-center text-zinc-500 dark:text-zinc-400 text-xs"
                                    title="Last Execution">
                                    <i data-lucide="history" class="w-4 h-4 mr-1"></i>
                                    Ran <span x-text="stats.lastExecutionTime"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Action Buttons --}}
                    <div class="flex flex-wrap gap-3 mt-4 md:mt-0 flex-shrink-0">
                        <button @click="setActiveTab('executions')" class="btn-primary"> {{-- Changed to button --}}
                            <i data-lucide="play" class="w-4 h-4 mr-2"></i>
                            Run Tests
                        </button>
                        <button @click="setActiveTab('settings')" class="btn-outline"> {{-- Changed to button --}}
                            <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                            Project Settings
                        </button>
                        {{-- Removed Edit Project link as Settings Tab covers it --}}
                        <div class="relative" x-data="{ showMenu: false }">
                            <button @click="showMenu = !showMenu" type="button" class="btn-outline">
                                <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                {{-- Removed text for smaller button --}}
                            </button>
                            <div x-show="showMenu" @click.away="showMenu = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-md bg-white dark:bg-zinc-800 shadow-lg ring-1 ring-black dark:ring-zinc-700 ring-opacity-5 focus:outline-none"
                                x-cloak>
                                <div class="py-1">
                                    <a href="#"
                                        class="text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 block px-4 py-2 text-sm">
                                        <div class="flex items-center"><i data-lucide="download"
                                                class="w-4 h-4 mr-2"></i>Export Project</div>
                                    </a>
                                    <a href="#"
                                        class="text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 block px-4 py-2 text-sm">
                                        <div class="flex items-center"><i data-lucide="copy"
                                                class="w-4 h-4 mr-2"></i>Clone Project</div>
                                    </a>
                                    <div class="border-t border-zinc-200 dark:border-zinc-700 my-1"></div>
                                    <button @click="openDeleteModal('project'); showMenu = false" type="button"
                                        class="text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10 w-full text-left px-4 py-2 text-sm">
                                        <div class="flex items-center"><i data-lucide="trash-2"
                                                class="w-4 h-4 mr-2"></i>Delete Project</div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Tabs Navigation -->
            <div class="border-t border-zinc-200/50 dark:border-zinc-700/50">
                <nav class="flex overflow-x-auto -mb-px" aria-label="Tabs">
                    <button @click="setActiveTab('overview')"
                        :class="activeTab === 'overview' ?
                            'border-indigo-500 dark:border-indigo-400 text-indigo-600 dark:text-indigo-400' :
                            'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600'"
                        class="whitespace-nowrap border-b-2 py-4 px-6 font-medium text-sm flex items-center transition-colors duration-200">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 mr-2"></i> Overview
                    </button>
                    <button @click="setActiveTab('test-suites')"
                        :class="activeTab === 'test-suites' ?
                            'border-indigo-500 dark:border-indigo-400 text-indigo-600 dark:text-indigo-400' :
                            'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600'"
                        class="whitespace-nowrap border-b-2 py-4 px-6 font-medium text-sm flex items-center transition-colors duration-200">
                        <i data-lucide="layers" class="w-4 h-4 mr-2"></i> Test Suites (<span
                            x-text="stats.totalTestSuites"></span>)
                    </button>
                    <button @click="setActiveTab('executions')"
                        :class="activeTab === 'executions' ?
                            'border-indigo-500 dark:border-indigo-400 text-indigo-600 dark:text-indigo-400' :
                            'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600'"
                        class="whitespace-nowrap border-b-2 py-4 px-6 font-medium text-sm flex items-center transition-colors duration-200">
                        <i data-lucide="play-circle" class="w-4 h-4 mr-2"></i> Executions
                    </button>
                    <button @click="setActiveTab('integrations')"
                        :class="activeTab === 'integrations' ?
                            'border-indigo-500 dark:border-indigo-400 text-indigo-600 dark:text-indigo-400' :
                            'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600'"
                        class="whitespace-nowrap border-b-2 py-4 px-6 font-medium text-sm flex items-center transition-colors duration-200">
                        <i data-lucide="link-2" class="w-4 h-4 mr-2"></i> Integrations (<span
                            x-text="integrations.length"></span>)
                    </button>
                    <button @click="setActiveTab('settings')"
                        :class="activeTab === 'settings' ?
                            'border-indigo-500 dark:border-indigo-400 text-indigo-600 dark:text-indigo-400' :
                            'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600'"
                        class="whitespace-nowrap border-b-2 py-4 px-6 font-medium text-sm flex items-center transition-colors duration-200">
                        <i data-lucide="settings-2" class="w-4 h-4 mr-2"></i> Settings
                    </button>
                </nav>
            </div>
        </div>

        <!-- Tab Content Panels -->
        <div class="mt-8">
            <!-- Overview Tab -->
            <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                @include('dashboard.projects.partials._tab-overview')
            </div>

            <!-- Test Suites Tab -->
            <div x-show="activeTab === 'test-suites'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                @include('dashboard.projects.partials._tab-test-suites')
            </div>

            <!-- Executions Tab -->
            <div x-show="activeTab === 'executions'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                @include('dashboard.projects.partials._tab-executions')
            </div>

            <!-- Integrations Tab -->
            <div x-show="activeTab === 'integrations'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                @include('dashboard.projects.partials._tab-integrations')
            </div>

            <!-- Settings Tab -->
            <div x-show="activeTab === 'settings'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                {{-- Pass project data for the form --}}
                @include('dashboard.projects.partials._tab-settings', ['project' => $project])
            </div>
        </div>

        <!-- Delete Modals -->
        @include('dashboard.projects.partials._modal-delete-project')
        @include('dashboard.projects.partials._modal-delete-test-suite')

    </div>
@endsection

{{-- Styles --}}
@push('styles')
    <style>
        /* Base button styles - apply common styles here */
        .btn-base {
            @apply inline-flex items-center px-4 py-2 border rounded-lg shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed;
        }

        .btn-primary {
            @apply btn-base border-transparent text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:ring-zinc-500;
        }

        .btn-secondary {
            @apply btn-base border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:ring-zinc-500;
        }

        .btn-outline {
            @apply btn-base border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 bg-transparent hover:bg-zinc-50 dark:hover:bg-zinc-700/50 focus:ring-zinc-500;
        }

        .btn-danger-outline {
            @apply btn-base border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 bg-transparent hover:bg-red-50 dark:hover:bg-red-900/20 focus:ring-red-500;
        }

        .btn-danger {
            @apply btn-base border-transparent text-white bg-red-600 hover:bg-red-700 focus:ring-red-500;
        }

        /* Tab styles adjustment */
        .tab-button {
            position: relative;
            transition: color 0.2s ease, border-color 0.2s ease;
            padding-bottom: calc(1rem - 2px);
            /* Match py-4 but account for border */
            margin-bottom: -1px;
            /* Overlap border */
        }

        /* Tooltip styles */
        [x-tooltip] {
            position: relative;
        }

        [x-tooltip]:before {
            content: attr(x-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(-5px) scale(0.8);
            background-color: #333;
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, transform 0.2s ease;
            pointer-events: none;
            z-index: 10;
        }

        [x-tooltip]:hover:before {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(-8px) scale(1);
        }

        /* Table hover highlight */
        tbody tr:hover {
            background-color: rgba(244, 244, 245, 0.5);
        }

        /* zinc-50/50 */
        .dark tbody tr:hover {
            background-color: rgba(51, 65, 85, 0.2);
        }

        /* zinc-800/20 */
    </style>
@endpush

{{-- Scripts --}}
@push('scripts')
    {{-- Make sure Chart.js is loaded if not globally available --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script> --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('projectDetails', (config) => ({
                // Config & Data
                projectId: config.projectId,
                projectName: config.projectName,
                activeTab: config.initialTab || 'overview',
                stats: config.stats || {},
                testSuites: config.testSuitesData || [],
                executions: config.executionsData || [],
                integrations: config.integrationsData || [],
                settings: { // Use a separate object for settings form binding
                    name: config.projectName,
                    description: @json($project->description), // Get description passed from controller
                    default_framework: config.projectSettingsData?.default_framework ||
                        'selenium-python',
                    auto_generate_tests: config.projectSettingsData?.auto_generate_tests || false,
                    // Add other settings here, matching the form fields in _tab-settings.blade.php
                    // default_environment: config.projectSettingsData?.default_environment || 'development',
                    // container_timeout: config.projectSettingsData?.container_timeout || 600,
                },
                recentActivities: config.recentActivitiesData || [],
                csrfToken: config.csrfToken,

                // UI State
                showDeleteProjectModal: false,
                deleteProjectConfirmText: '',
                isDeletingProject: false,
                showDeleteSuiteModal: false,
                suiteToDelete: null, // { id: '', name: '' }
                deleteSuiteConfirmText: '',
                isDeletingSuite: false,
                isSavingSettings: false,

                // Notifications
                notification: {
                    show: false,
                    type: 'success',
                    title: '',
                    message: '',
                    timeout: null
                },

                // Chart Instance
                executionChartInstance: null,

                // Alpine Init
                initAlpine() {
                    // Load initial data or perform actions based on the initialTab
                    this.setActiveTab(this.activeTab,
                    false); // Set initial tab without pushing state again

                    this.$nextTick(() => {
                        lucide.createIcons();
                        // Only render chart if overview is the initial tab
                        if (this.activeTab === 'overview') {
                            this.renderExecutionHistoryChart();
                        }
                    });

                    // Listen for hash changes to update tab (e.g., browser back/forward)
                    window.addEventListener('hashchange', () => {
                        const hash = window.location.hash.substring(1);
                        const validTabs = ['overview', 'test-suites', 'executions',
                            'integrations', 'settings'
                        ];
                        if (validTabs.includes(hash) && this.activeTab !== hash) {
                            this.setActiveTab(hash,
                            false); // Update tab state without pushing history
                        } else if (!hash && this.activeTab !== 'overview') {
                            this.setActiveTab('overview',
                            false); // Default to overview if hash is empty
                        }
                    });

                    // Auto-hide notification logic
                    this.$watch('notification.show', value => {
                        if (value) {
                            if (this.notification.timeout) clearTimeout(this.notification
                                .timeout);
                            this.notification.timeout = setTimeout(() => {
                                this.notification.show = false;
                            }, 5000);
                        }
                    });
                },

                // --- Core Methods ---
                setActiveTab(tab, updateHistory = true) {
                    this.activeTab = tab;
                    if (updateHistory) {
                        // Update URL hash only when user explicitly clicks a tab
                        history.pushState(null, null, `#${tab}`);
                    }
                    // Re-render chart if switching to overview tab
                    if (tab === 'overview') {
                        this.$nextTick(() => this.renderExecutionHistoryChart());
                    }
                    // Re-initialize icons if needed (sometimes useful after content changes)
                    // this.$nextTick(() => lucide.createIcons());
                },

                showNotification(type, title, message) {
                    this.notification = {
                        show: true,
                        type,
                        title,
                        message,
                        timeout: null
                    };
                },

                // --- Project Deletion ---
                openDeleteModal(type) { // Modified to handle type
                    if (type === 'project') {
                        this.deleteProjectConfirmText = '';
                        this.isDeletingProject = false;
                        this.showDeleteProjectModal = true;
                        this.$nextTick(() => document.getElementById('confirm-delete-project-text')
                            ?.focus());
                    } else if (type === 'suite' && this.suiteToDelete) { // Check suiteToDelete is set
                        this.deleteSuiteConfirmText = '';
                        this.isDeletingSuite = false;
                        this.showDeleteSuiteModal = true;
                        this.$nextTick(() => document.getElementById('confirm-delete-suite-text')
                            ?.focus());
                    }
                },

                closeDeleteModal(type) {
                    if (type === 'project') {
                        if (!this.isDeletingProject) this.showDeleteProjectModal = false;
                    } else if (type === 'suite') {
                        if (!this.isDeletingSuite) {
                            this.showDeleteSuiteModal = false;
                            this.suiteToDelete = null; // Clear the selected suite
                        }
                    }
                },

                async confirmDeleteProject() {
                    if (this.isDeletingProject || this.deleteProjectConfirmText !== this
                        .projectName) return;
                    this.isDeletingProject = true;
                    try {
                        const response = await fetch(
                            `{{ route('dashboard.projects.destroy', $project->id) }}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': this.csrfToken,
                                    'Accept': 'application/json'
                                }
                            });
                        const result = await response.json();
                        if (response.ok && result.success) {
                            // Store success message temporarily and redirect
                            sessionStorage.setItem('flashSuccess', result.message);
                            window.location.href = result.redirect ||
                                `{{ route('dashboard.projects') }}`;
                        } else {
                            throw new Error(result.message || 'Failed to delete project.');
                        }
                    } catch (error) {
                        this.showNotification('error', 'Error', error.message);
                        this.closeDeleteModal('project');
                    } finally {
                        this.isDeletingProject = false; // Reset even on failure/redirect
                    }
                },

                // --- Test Suite Deletion ---
                setSuiteToDelete(suite) { // Called by button click in the partial
                    this.suiteToDelete = {
                        id: suite.id,
                        name: suite.name
                    }; // Store necessary info
                    this.openDeleteModal('suite');
                },

                async confirmDeleteSuite() {
                    if (!this.suiteToDelete || this.isDeletingSuite || this
                        .deleteSuiteConfirmText !== this.suiteToDelete.name) return;
                    this.isDeletingSuite = true;
                    try {
                        const deleteUrl =
                            `{{ url('dashboard/projects/' . $project->id . '/test-suites') }}/${this.suiteToDelete.id}`;
                        const response = await fetch(deleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            }
                        });
                        const result = await response.json();
                        if (response.ok && result.success) {
                            this.testSuites = this.testSuites.filter(s => s.id !== this
                                .suiteToDelete.id);
                            this.stats.totalTestSuites = this.testSuites.length;
                            this.stats.totalTestCases = this.testSuites.reduce((sum, suite) => sum +
                                (suite.test_cases_count || 0), 0);
                            this.showNotification('success', 'Deleted', result.message);
                            this.closeDeleteModal('suite');
                        } else {
                            throw new Error(result.message || 'Failed to delete test suite.');
                        }
                    } catch (error) {
                        this.showNotification('error', 'Error', error.message);
                        this.closeDeleteModal('suite');
                    } finally {
                        this.isDeletingSuite = false;
                    }
                },

                // --- Settings Tab ---
                async saveSettings() {
                    this.isSavingSettings = true;
                    try {
                        // Construct data payload from the 'settings' object in Alpine data
                        const settingsData = {
                            name: this.settings.name,
                            description: this.settings.description,
                            settings: { // Nest settings under a 'settings' key
                                default_framework: this.settings.default_framework,
                                auto_generate_tests: this.settings.auto_generate_tests,
                                // Add other settings here if they are in the this.settings object
                                // default_environment: this.settings.default_environment,
                                // container_timeout: this.settings.container_timeout,
                            }
                        };

                        const response = await fetch(
                            `{{ route('dashboard.projects.update', $project->id) }}`, {
                                method: 'POST', // Use POST with _method for PUT
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrfToken
                                },
                                body: JSON.stringify({
                                    ...settingsData,
                                    _method: 'PUT'
                                })
                            });

                        const result = await response.json();
                        if (response.ok && result.success) {
                            this.showNotification('success', 'Settings Saved',
                                'Project settings updated successfully.');
                            // Update project name in header if it changed
                            if (this.projectName !== settingsData.name) {
                                this.projectName = settingsData.name;
                                // Optionally update the browser title too: document.title = `${settingsData.name} - Project`;
                            }
                            // You might want to re-sync the main $project data if the backend returns it
                        } else {
                            let errorMsg = result.message || 'Failed to save settings.';
                            if (result.errors) {
                                // Basic error joining, could be enhanced to show specific field errors
                                errorMsg = Object.values(result.errors).flat().join(' ');
                            }
                            throw new Error(errorMsg);
                        }
                    } catch (error) {
                        this.showNotification('error', 'Save Error', error.message);
                    } finally {
                        this.isSavingSettings = false;
                    }
                },

                // --- Charting ---
                renderExecutionHistoryChart() {
                    this.$nextTick(() => { // Ensure canvas element exists in DOM
                        if (this.executionChartInstance) {
                            this.executionChartInstance.destroy();
                        }
                        const ctx = document.getElementById('execution-history-chart')
                            ?.getContext('2d');
                        if (!ctx || !this.stats?.executionHistory) {
                            console.warn('Chart canvas or execution history data not found.');
                            return;
                        }

                        const historyData = this.stats.executionHistory;
                        const labels = Object.keys(historyData).map(date => new Date(date +
                            'T00:00:00').toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric'
                        })); // Ensure date parsing is robust
                        const passedData = Object.values(historyData).map(d => d.passed);
                        const failedData = Object.values(historyData).map(d => d.failed);
                        const isDarkMode = document.documentElement.classList.contains('dark');

                        // Define colors based on theme
                        const passedColor = isDarkMode ? 'rgba(74, 222, 128, 0.6)' :
                            'rgba(34, 197, 94, 0.7)'; // Tailwind green-400 / green-500
                        const passedBorderColor = isDarkMode ? 'rgba(74, 222, 128, 1)' :
                            'rgba(22, 163, 74, 1)'; // green-400 / green-600
                        const failedColor = isDarkMode ? 'rgba(248, 113, 113, 0.6)' :
                            'rgba(239, 68, 68, 0.7)'; // red-400 / red-500
                        const failedBorderColor = isDarkMode ? 'rgba(248, 113, 113, 1)' :
                            'rgba(220, 38, 38, 1)'; // red-400 / red-600
                        const gridColor = isDarkMode ? 'rgba(63, 63, 70, 0.3)' :
                            'rgba(229, 231, 235, 0.5)'; // zinc-700 / zinc-200
                        const tickColor = isDarkMode ? '#a1a1aa' :
                        '#71717a'; // zinc-400 / zinc-500
                        const legendColor = isDarkMode ? '#e4e4e7' :
                        '#1f2937'; // zinc-200 / zinc-800
                        const tooltipBgColor = isDarkMode ? '#27272a' :
                        '#ffffff'; // zinc-800 / white
                        const tooltipTitleColor = isDarkMode ? '#f4f4f5' :
                        '#1f2937'; // zinc-100 / zinc-800
                        const tooltipBodyColor = isDarkMode ? '#d4d4d8' :
                        '#3f3f46'; // zinc-300 / zinc-700

                        this.executionChartInstance = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                        label: 'Passed',
                                        data: passedData,
                                        backgroundColor: passedColor,
                                        borderColor: passedBorderColor,
                                        borderWidth: 1,
                                        borderRadius: 4
                                    },
                                    {
                                        label: 'Failed',
                                        data: failedData,
                                        backgroundColor: failedColor,
                                        borderColor: failedBorderColor,
                                        borderWidth: 1,
                                        borderRadius: 4
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    x: {
                                        stacked: true,
                                        grid: {
                                            display: false
                                        },
                                        ticks: {
                                            color: tickColor,
                                            maxRotation: 0,
                                            autoSkipPadding: 10
                                        }
                                    },
                                    y: {
                                        stacked: true,
                                        beginAtZero: true,
                                        grid: {
                                            color: gridColor
                                        },
                                        ticks: {
                                            color: tickColor,
                                            precision: 0
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top',
                                        labels: {
                                            color: legendColor,
                                            boxWidth: 12,
                                            padding: 20
                                        }
                                    },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                        backgroundColor: tooltipBgColor,
                                        titleColor: tooltipTitleColor,
                                        bodyColor: tooltipBodyColor,
                                        padding: 10,
                                        boxPadding: 4,
                                        cornerRadius: 4
                                    }
                                },
                                interaction: {
                                    mode: 'index',
                                    intersect: false
                                }
                            }
                        });
                    });
                },

                // --- Helper Functions ---
                timeAgo(timestamp) {
                    if (!timestamp) return 'N/A';
                    const date = new Date(timestamp);
                    const seconds = Math.floor((new Date() - date) / 1000);
                    let interval = seconds / 31536000;
                    if (interval > 1) return Math.floor(interval) + " years ago";
                    interval = seconds / 2592000;
                    if (interval > 1) return Math.floor(interval) + " months ago";
                    interval = seconds / 86400;
                    if (interval > 1) return Math.floor(interval) + " days ago";
                    interval = seconds / 3600;
                    if (interval > 1) return Math.floor(interval) + " hours ago";
                    interval = seconds / 60;
                    if (interval > 1) return Math.floor(interval) + " minutes ago";
                    return Math.floor(seconds) + " seconds ago";
                },
                formatDuration(seconds) {
                    /* ... copy from controller or reuse if needed */ },
                formatDateTime(dateString) {
                    /* ... copy from controller or reuse if needed */ },
                getStatusColorClass(status) {
                    /* ... copy from controller or reuse */ },
                getIntegrationIcon(type) {
                    /* ... copy from controller or reuse */ }

            }));
        });
    </script>
@endpush
