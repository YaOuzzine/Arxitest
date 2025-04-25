@php
    /**
     * @var \App\Models\Project|null $project // Can be null for indexAll
     * @var \App\Models\Team|null $team       // Should be present for indexAll
     * @var \Illuminate\Database\Eloquent\Collection<\App\Models\TestSuite> $testSuites
     * @var \Illuminate\Database\Eloquent\Collection<\App\Models\Project>|null $projects // For filter in indexAll
     */

    // Determine context: Are we viewing all suites (generic) or suites for a specific project?
    $selectedProjectId = null;
    $isGenericIndex = true;
    if (isset($project)) {
        $isGenericIndex = false;
        $selectedProjectId = $project->id;
    }

    // Set Page Title based on context
    $pageTitle = $isGenericIndex ? 'All Test Suites' : 'Test Suites for: ' . $project->name;

    // Get current project filter ID if applicable (for generic index)
    $currentProjectId = request()->query('project_id', '');

    // Define the base URL for suite actions (depends on context)
    $baseSuiteUrl = $isGenericIndex
        ? url('dashboard/projects')
        : route('dashboard.projects.test-suites.index', $project->id);

@endphp

@extends('layouts.dashboard')

@section('title', $pageTitle)

@section('breadcrumbs')
    {{-- Generic Index Breadcrumbs --}}
    @if ($isGenericIndex)
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <span class="text-zinc-700 dark:text-zinc-300">Test Suites</span>
        </li>
        {{-- Project Specific Index Breadcrumbs --}}
    @else
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <a href="{{ route('dashboard.projects') }}"
                class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">Projects</a>
        </li>
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <a href="{{ route('dashboard.projects.show', $project->id) }}"
                class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">{{ $project->name }}</a>
        </li>
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <span class="text-zinc-700 dark:text-zinc-300">Test Suites</span>
        </li>
    @endif
@endsection

@section('content')
    {{-- AlpineJS component for interactivity (notifications, delete modal) --}}
    <div x-data="testSuiteEnhanced" x-init="initNotifications()" class="relative space-y-8">

        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $pageTitle }}</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    @if ($isGenericIndex)
                        Viewing all test suites for team '{{ $team->name ?? 'your team' }}'. Use the filter below.
                    @else
                        Manage and organize test suites within the '{{ $project->name }}' project.
                    @endif
                </p>
            </div>
            {{-- "New Test Suite" Button --}}
            <div class="flex-shrink-0">
                {{-- Link to create route only if project context exists, otherwise suggest selecting a project --}}
                @if ($currentProjectId || $selectedProjectId)
                    <a id="add-suite-button" href="{{ route('dashboard.projects.test-suites.create', [$currentProjectId ?: $selectedProjectId]) }}"
                        class="btn-primary">
                        Add Test Suite
                    </a>
                @else
                    <span
                        class="inline-flex items-center px-5 py-2.5 bg-zinc-200 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400 font-medium rounded-xl cursor-not-allowed"
                        title="Select a project from the filter below to add a suite">
                        <i data-lucide="info" class="w-5 h-5 mr-2"></i>
                        Select Project to Add Suite
                    </span>
                @endif
            </div>
        </div>

        <!-- Project Filter (Only for Generic Index View) -->
        @if ($isGenericIndex && isset($projects))
            {{-- Ensure $projects is passed for generic view --}}
            <div class="animate-fade-in-down dropdown-container" style="z-index: 10;" x-data="projectFilterDropdown({ currentProjectId: '{{ $currentProjectId }}', projects: {{ json_encode($projects->toArray()) }} })">
                <form method="GET" action="{{ route('dashboard.test-suites.indexAll') }}" id="project-filter-form">
                    <input type="hidden" name="project_id" x-model="selectedProjectId">
                    <label for="project-select-trigger" class="sr-only">Filter by Project</label>
                    <div class="relative dashboard-container" id="project-select-container">
                        {{-- Trigger Button --}}
                        <button type="button" @click="toggleDropdown" @keydown.escape.prevent="closeDropdown()"
                            id="project-select-trigger" aria-haspopup="listbox" :aria-expanded="isOpen.toString()"
                            class="w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200">
                            <div class="flex items-center space-x-3">
                                <i data-lucide="filter" class="w-5 h-5 text-zinc-400"></i>
                                <span x-text="selectedProjectName || 'Filter by Project...'"
                                    class="text-zinc-700 dark:text-zinc-200"></span>
                            </div>
                            <i data-lucide="chevron-down" class="w-5 h-5 text-zinc-400 transition-transform duration-200"
                                :class="{ 'rotate-180': isOpen }"></i>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="isOpen" @click.outside="closeDropdown"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="dropdown-menu w-full" style="z-index: 100;" role="listbox"
                            aria-labelledby="project-select-trigger" x-trap.inert.noscroll="isOpen">
                            {{-- Search Input --}}
                            <div class="p-2 border-b border-zinc-100 dark:border-zinc-700">
                                <div class="relative">
                                    <i data-lucide="search"
                                        class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 pointer-events-none"></i>
                                    <input type="search" x-model="searchTerm" @input="filterProjects"
                                        placeholder="Search projects..."
                                        class="w-full pl-9 pr-3 py-2 bg-zinc-50 dark:bg-zinc-700 border-transparent focus:border-transparent focus:ring-1 focus:ring-indigo-500 rounded-md text-sm">
                                </div>
                            </div>
                            {{-- Options List --}}
                            <div class="max-h-60 overflow-y-auto">
                                <ul>
                                    {{-- "All Projects" Option --}}
                                    <li role="option" :aria-selected="selectedProjectId === ''">
                                        <button type="button" @click="selectProject('', 'All Projects')"
                                            class="w-full px-4 py-2.5 text-left text-sm flex items-center justify-between hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                            :class="{ 'font-semibold text-indigo-600 dark:text-indigo-400': selectedProjectId === '' }">
                                            <span>All Projects</span>
                                            <i data-lucide="check" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"
                                                x-show="selectedProjectId === ''"></i>
                                        </button>
                                    </li>
                                    {{-- Filtered Project Options --}}
                                    <template x-for="project in filteredProjects" :key="project.id">
                                        <li role="option" :aria-selected="selectedProjectId === project.id">
                                            <button type="button" @click="selectProject(project.id, project.name)"
                                                class="w-full px-4 py-2.5 text-left text-sm flex items-center justify-between hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                                :class="{
                                                    'font-semibold text-indigo-600 dark:text-indigo-400': selectedProjectId ===
                                                        project.id
                                                }">
                                                <span x-text="project.name"></span>
                                                <i data-lucide="check" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"
                                                    x-show="selectedProjectId === project.id"></i>
                                            </button>
                                        </li>
                                    </template>
                                    {{-- No Results Message --}}
                                    <template x-if="filteredProjects.length === 0">
                                        <li class="px-4 py-3 text-sm text-center text-zinc-500 dark:text-zinc-400">No
                                            projects found.</li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        <!-- Test Suites List/Grid -->
        <div class="transition-opacity duration-500 ease-in-out">
            @if ($testSuites->isEmpty())
                {{-- Enhanced Empty State --}}
                <div
                    class="text-center py-16 px-6 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border-2 border-dashed border-zinc-200 dark:border-zinc-700/50">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 mb-5 text-indigo-600 dark:text-indigo-400">
                        <i data-lucide="layers-3" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-white mb-2">No Test Suites Found</h3>
                    <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-6">
                        @if ($isGenericIndex && $currentProjectId)
                            It looks like there are no test suites in the selected project yet.
                        @elseif ($isGenericIndex)
                            No test suites found for the current team or filter. Select a project to create one.
                        @else
                            Get started by creating the first test suite for this project.
                        @endif
                    </p>
                    @if (!$isGenericIndex)
                        <a href="{{ route('dashboard.projects.test-suites.create', $project->id) }}"
                            class="btn-primary inline-flex items-center group">
                            <i data-lucide="plus" class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform"></i>
                            Create First Test Suite
                        </a>
                    @endif
                </div>
            @else
                {{-- Test Suites List --}}
                <div class="space-y-4">
                    @foreach ($testSuites as $suite)
                        {{-- Individual Suite Card --}}
                        <div x-data="{ showActions: false }" @mouseenter="showActions = true" @mouseleave="showActions = false"
                            class="bg-white dark:bg-zinc-800 rounded-xl shadow-md hover:shadow-lg border border-zinc-200/80 dark:border-zinc-700/60 p-5 transition-all duration-300 transform hover:border-zinc-300 dark:hover:border-zinc-600">
                            <div class="flex items-start justify-between gap-4">
                                {{-- Suite Info --}}
                                <div class="flex-1 min-w-0">
                                    {{-- Suite Name (linked) --}}
                                    <a href="{{ route('dashboard.projects.test-suites.show', [$suite->project_id, $suite->id]) }}"
                                        class="text-lg font-semibold text-zinc-800 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200 line-clamp-1"
                                        title="{{ $suite->name }}">
                                        {{ $suite->name }}
                                    </a>
                                    {{-- Project Name (only on generic index) --}}
                                    @if ($isGenericIndex)
                                        <p class="text-xs text-indigo-600 dark:text-indigo-400 font-medium mt-1">
                                            Project: {{ $suite->project->name }}
                                        </p>
                                    @endif
                                    {{-- Description --}}
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1 line-clamp-2"
                                        title="{{ $suite->description }}">
                                        {{ $suite->description ?: 'No description provided.' }}
                                    </p>
                                    {{-- Metadata --}}
                                    <div
                                        class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-xs text-zinc-500 dark:text-zinc-400/80">
                                        <span class="inline-flex items-center">
                                            <i data-lucide="file-check-2" class="w-3.5 h-3.5 mr-1 text-green-500"></i>
                                            {{ $suite->test_cases_count }}
                                            {{ Str::plural('Case', $suite->test_cases_count) }}
                                        </span>
                                        <span class="inline-flex items-center">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 mr-1 text-blue-500"></i>
                                            Updated {{ $suite->updated_at->diffForHumans() }}
                                        </span>
                                        <span
                                            class="inline-flex items-center capitalize {{ match (strtolower($suite->settings['default_priority'] ?? 'medium')) {
                                                'high' => 'text-red-500',
                                                'low' => 'text-green-500',
                                                default => 'text-yellow-500',
                                            } }}">
                                            <i data-lucide="bar-chart" class="w-3.5 h-3.5 mr-1"></i>
                                            {{ $suite->settings['default_priority'] ?? 'Medium' }} Priority
                                        </span>
                                    </div>
                                </div>
                                {{-- Action Buttons --}}
                                <div class="flex flex-col items-end md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-2 flex-shrink-0 transition-opacity duration-200"
                                    :class="{ 'opacity-100': showActions, 'opacity-0 md:opacity-100': !showActions }">
                                    <a href="{{ route('dashboard.projects.test-suites.show', [$suite->project_id, $suite->id]) }}"
                                        class="action-btn text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30"
                                        title="View Details">
                                        <i data-lucide="eye" class="w-4 h-4"></i> <span
                                            class="hidden md:inline ml-1">View</span>
                                    </a>
                                    <a href="{{ route('dashboard.projects.test-suites.edit', [$suite->project_id, $suite->id]) }}"
                                        class="action-btn text-yellow-600 dark:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-900/30"
                                        title="Edit Suite">
                                        <i data-lucide="pencil" class="w-4 h-4"></i> <span
                                            class="hidden md:inline ml-1">Edit</span>
                                    </a>
                                    <button
                                        @click="openDeleteModal('{{ $suite->id }}', '{{ addslashes($suite->name) }}', '{{ $suite->project_id }}')"
                                        class="action-btn text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30"
                                        title="Delete Suite">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i> <span
                                            class="hidden md:inline ml-1">Delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                {{-- Pagination (if needed) --}}
                {{-- <div class="mt-6">
                    {{ $testSuites->links() }}
                </div> --}}
            @endif
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Background overlay --}}
                <div class="fixed inset-0 bg-zinc-900/60 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
                    @click="closeDeleteModal" aria-hidden="true"></div>
                {{-- Modal panel --}}
                <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-zinc-200 dark:border-zinc-700">
                    {{-- Modal Content --}}
                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600 dark:text-red-400"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-white" id="modal-title">
                                    Confirm Deletion
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        Are you sure you want to delete the test suite "<strong
                                            class="font-semibold text-zinc-700 dark:text-zinc-200"
                                            x-text="deleteSuiteName"></strong>"? This will also delete all associated test
                                        cases. This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Modal Footer --}}
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                        <button @click="confirmDelete()" type="button"
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-zinc-800 sm:w-auto sm:text-sm disabled:opacity-50"
                            :disabled="isDeleting">
                            <span x-show="!isDeleting">Delete Suite</span>
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
                        <button @click="closeDeleteModal()" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-zinc-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-800 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Notification Toast -->
        <div x-show="showNotification" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4" class="fixed bottom-6 right-6 z-[100] max-w-sm w-full"
            style="display: none;">
            <div class="shadow-lg border rounded-xl p-4 backdrop-blur-sm"
                :class="{
                    'bg-green-50/90 border-green-200/50 dark:bg-green-900/40 dark:border-green-800/30': notificationType === 'success',
                    'bg-red-50/90 border-red-200/50 dark:bg-red-900/40 dark:border-red-800/30': notificationType === 'error'
                }">
                <div class="flex items-start">
                    <i data-lucide="check-circle" x-show="notificationType === 'success'"
                        class="w-5 h-5 mr-3 text-green-600 dark:text-green-400 flex-shrink-0"></i>
                    <i data-lucide="alert-circle" x-show="notificationType === 'error'"
                        class="w-5 h-5 mr-3 text-red-600 dark:text-red-400 flex-shrink-0"></i>
                    <div class="flex-1">
                        <p class="text-sm font-medium"
                            :class="{
                                'text-green-800 dark:text-green-200': notificationType === 'success',
                                'text-red-800 dark:text-red-200': notificationType === 'error'
                            }"
                            x-text="notificationMessage"></p>
                    </div>
                    <button @click="hideNotification"
                        class="ml-3 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('styles')
    <style>
        .btn-primary {
            @apply bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition duration-150 ease-in-out disabled:opacity-50;
        }

        .action-btn {
            @apply p-1.5 rounded-md transition-colors duration-150;
        }

        /* Style for the dropdown menu */
        #project-select-dropdown {
            transform-origin: top center;
        }

        /* Scrollbar styling for dark mode */
        .dark #project-select-dropdown ul {
            scrollbar-width: thin;
            scrollbar-color: rgba(161, 161, 170, 0.5) rgba(63, 63, 70, 0.1);
        }

        .dark #project-select-dropdown ul::-webkit-scrollbar {
            width: 6px;
        }

        .dark #project-select-dropdown ul::-webkit-scrollbar-track {
            background: rgba(63, 63, 70, 0.1);
            border-radius: 3px;
        }

        .dark #project-select-dropdown ul::-webkit-scrollbar-thumb {
            background-color: rgba(161, 161, 170, 0.5);
            border-radius: 3px;
        }

        .dark #project-select-dropdown ul::-webkit-scrollbar-thumb:hover {
            background-color: rgba(161, 161, 170, 0.7);
        }

        /* Simple fade-in animation */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-down {
            animation: fadeInDown 0.5s ease-out forwards;
        }
    </style>
@endpush

@push('scripts')
    {{-- Include Axios if not already available globally --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script> --}}

    <script>
        document.addEventListener('alpine:init', () => {
            // Main Alpine component for the page
            Alpine.data('testSuiteEnhanced', () => ({
                showNotification: false,
                notificationType: 'success',
                notificationMessage: '',
                showDeleteModal: false,
                deleteSuiteId: null,
                deleteProjectId: null,
                deleteSuiteName: '',
                isDeleting: false,

                // Initialize notifications from session flash messages
                initNotifications() {
                    const flashSuccess = '{{ session('success') }}';
                    const flashError = '{{ session('error') }}';
                    if (flashSuccess) this.showSuccess(flashSuccess);
                    if (flashError) this.showError(flashError);

                    // Load Lucide icons after Alpine init
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });
                },

                // Show success notification
                showSuccess(message) {
                    this.notificationType = 'success';
                    this.notificationMessage = message;
                    this.showNotification = true;
                    setTimeout(() => this.hideNotification(), 5000);
                },

                // Show error notification
                showError(message) {
                    this.notificationType = 'error';
                    this.notificationMessage = message;
                    this.showNotification = true;
                    setTimeout(() => this.hideNotification(), 7000); // Show errors longer
                },

                // Hide notification
                hideNotification() {
                    this.showNotification = false;
                },

                // Open the delete confirmation modal
                openDeleteModal(id, name, projectId) {
                    this.deleteSuiteId = id;
                    this.deleteProjectId = projectId;
                    // Decode potential HTML entities in the name for display
                    const nameDecoder = document.createElement('textarea');
                    nameDecoder.innerHTML = name;
                    this.deleteSuiteName = nameDecoder.value;

                    this.isDeleting = false; // Reset deleting state
                    this.showDeleteModal = true;
                },

                // Close the delete confirmation modal
                closeDeleteModal() {
                    if (!this.isDeleting) {
                        this.showDeleteModal = false;
                        // Clear sensitive info after modal closes
                        this.$nextTick(() => {
                            this.deleteSuiteId = null;
                            this.deleteProjectId = null;
                            this.deleteSuiteName = '';
                        });
                    }
                },

                // Confirm and execute the deletion
                async confirmDelete() {
                    if (!this.deleteSuiteId || !this.deleteProjectId) {
                        this.showError('Cannot delete: Suite or Project ID missing.');
                        return;
                    }
                    this.isDeleting = true;
                    try {
                        // Use the correct route structure
                        const deleteUrl =
                            `/dashboard/projects/${this.deleteProjectId}/test-suites/${this.deleteSuiteId}`;

                        const response = await fetch(deleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            // Remove the suite element from the DOM
                            const suiteElement = document.getElementById(
                                `suite-card-${this.deleteSuiteId}`
                            ); // Assuming you add an ID like this to the card
                            if (suiteElement) {
                                suiteElement.remove();
                            }
                            this.showSuccess(result.message || 'Test suite deleted successfully.');
                            this.closeDeleteModal(); // Close modal on success

                            // Optionally update counts or check if list is empty
                            // e.g., if (document.querySelectorAll('.suite-card').length === 0) { ... }

                        } else {
                            throw new Error(result.message || 'Failed to delete the test suite.');
                        }
                    } catch (error) {
                        console.error('Delete Error:', error);
                        this.showError(error.message || 'An unexpected error occurred.');
                        // Don't close modal on error, let user retry or cancel
                    } finally {
                        this.isDeleting = false;
                    }
                }
            }));

            // Alpine component specifically for the project filter dropdown
            Alpine.data('projectFilterDropdown', (config) => ({
                isOpen: false,
                selectedProjectId: config.currentProjectId || '',
                selectedProjectName: '',
                searchTerm: '',
                allProjects: config.projects || [],
                filteredProjects: [],

                init() {
                    this.filteredProjects = this.allProjects;
                    // Set the selected project name on init
                    if (this.selectedProjectId) {
                        const currentProject = this.allProjects.find(p => p.id === this
                            .selectedProjectId);
                        this.selectedProjectName = currentProject ? currentProject.name :
                            'All Projects';
                    } else {
                        this.selectedProjectName = 'All Projects';
                    }

                    // Debug logs
                    // console.log('Filter initialized with project ID:', this.selectedProjectId);
                    // console.log('Available projects:', this.allProjects);
                },

                toggleDropdown() {
                    this.isOpen = !this.isOpen;
                    if (this.isOpen) {
                        this.searchTerm = ''; // Clear search on open
                        this.filterProjects(); // Show all projects initially
                    }
                },

                closeDropdown() {
                    this.isOpen = false;
                },

                filterProjects() {
                    if (!this.searchTerm) {
                        this.filteredProjects = this.allProjects;
                    } else {
                        const lowerSearchTerm = this.searchTerm.toLowerCase();
                        this.filteredProjects = this.allProjects.filter(project =>
                            project.name.toLowerCase().includes(lowerSearchTerm)
                        );
                    }
                },

                selectProject(projectId, projectName) {
                    console.log('Selecting project:', projectId, projectName);
                    this.selectedProjectId = projectId;
                    this.selectedProjectName = projectName;
                    this.closeDropdown();

                    // Explicitly set the form value
                    document.querySelector('input[name="project_id"]').value = projectId;

                    // Submit the form with a slight delay to ensure the value is set

                    document.getElementById('project-filter-form').submit();

                }
            }));
        });
    </script>
@endpush

@push('meta')
    {{-- Ensure CSRF token is available for AJAX requests (like delete) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
