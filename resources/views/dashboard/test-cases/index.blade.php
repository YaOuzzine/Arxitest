@php
    /**
     * @var \App\Models\Project|null $project // Can be null for indexAll
     * @var \App\Models\TestSuite|null $testSuite // Can be null for indexAll or project-specific
     * @var \App\Models\Team|null $team // Should be present for indexAll
     * @var \Illuminate\Pagination\LengthAwarePaginator $testCases
     * @var \Illuminate\Database\Eloquent\Collection<\App\Models\Project>|null $projects // For filter in indexAll
     * @var \Illuminate\Database\Eloquent\Collection<\App\Models\TestSuite>|null $testSuites // For filter
     */

    // Determine context: All test cases, project-specific, or suite-specific
    $isGenericIndex = !isset($project);
    $isProjectIndex = isset($project) && !isset($testSuite);
    $isSuiteIndex = isset($project) && isset($testSuite);

    // Set Page Title based on context
    if ($isGenericIndex) {
        $pageTitle = 'All Test Cases';
    } elseif ($isProjectIndex) {
        $pageTitle = 'Test Cases for ' . $project->name;
    } else {
        $pageTitle = 'Test Cases in ' . $testSuite->name;
    }

    // Get current filter values
    $selectedProjectId = $selectedProjectId ?? '';
    $selectedSuiteId = $selectedSuiteId ?? '';
    $searchTerm = $searchTerm ?? '';

    // Get current sort parameters
    $sortField = $sortField ?? 'updated_at';
    $sortDirection = $sortDirection ?? 'desc';
@endphp

@extends('layouts.dashboard')

@section('title', $pageTitle)

@section('breadcrumbs')
    @if ($isGenericIndex)
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <span class="text-zinc-700 dark:text-zinc-300">Test Cases</span>
        </li>
    @elseif ($isProjectIndex)
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
            <span class="text-zinc-700 dark:text-zinc-300">Test Cases</span>
        </li>
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
            <a href="{{ route('dashboard.projects.test-suites.show', [$project->id, $testSuite->id]) }}"
                class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">{{ $testSuite->name }}</a>
        </li>
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <span class="text-zinc-700 dark:text-zinc-300">Test Cases</span>
        </li>
    @endif
@endsection

@section('content')
    <div x-data="testCasesManager({
        isGenericIndex: {{ $isGenericIndex ? 'true' : 'false' }},
        isProjectIndex: {{ $isProjectIndex ? 'true' : 'false' }},
        isSuiteIndex: {{ $isSuiteIndex ? 'true' : 'false' }},
        selectedProjectId: '{{ $selectedProjectId }}',
        selectedSuiteId: '{{ $selectedSuiteId }}',
        searchTerm: '{{ $searchTerm }}',
        sortField: '{{ $sortField }}',
        sortDirection: '{{ $sortDirection }}',
        @if ($isGenericIndex) projects: {{ json_encode(isset($projects) ? $projects->toArray() : []) }},
            testSuites: {{ json_encode(isset($testSuites) ? $testSuites->toArray() : []) }}, @endif
        projectId: '{{ $project->id ?? '' }}',
        suiteId: '{{ $testSuite->id ?? '' }}'
    })" x-init="init()" class="space-y-8">

        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $pageTitle }}</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    @if ($isGenericIndex)
                        Manage test cases across all projects. Use filters to narrow your view.
                    @elseif ($isProjectIndex)
                        Manage test cases for the "{{ $project->name }}" project.
                    @else
                        View and manage test cases in the "{{ $testSuite->name }}" test suite.
                    @endif
                </p>
            </div>

            <!-- Create Test Case Button -->
            <div class="flex-shrink-0">
                @if ($isGenericIndex && !$selectedProjectId)
                    <button
                        class="inline-flex items-center px-5 py-2.5 bg-zinc-200 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400 font-medium rounded-lg cursor-not-allowed"
                        title="Select a project first">
                        <i data-lucide="info" class="w-5 h-5 mr-2"></i>
                        Select Project to Create
                    </button>
                @elseif ($selectedProjectId && !$selectedSuiteId)
                    <a href="{{route('dashboard.projects.test-cases.create', $selectedProjectId)}}"
                        class="btn-primary inline-flex items-center px-5 py-2.5 group">
                        <i data-lucide="plus-circle"
                            class="w-5 h-5 mr-2 transition-transform duration-200 group-hover:rotate-90"></i>
                        New Test Case
                    </a>
                @else
                    <a href="{{ route('dashboard.projects.test-suites.test-cases.create', [$selectedProjectId, $selectedSuiteId]) }}"
                        class="btn-primary inline-flex items-center px-5 py-2.5 group">
                        <i data-lucide="plus-circle"
                            class="w-5 h-5 mr-2 transition-transform duration-200 group-hover:rotate-90"></i>
                        New Test Case
                    </a>
                @endif
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-md p-5 border border-zinc-200 dark:border-zinc-700">
            <form id="filter-form" method="GET"
                action="{{ $isGenericIndex ? route('dashboard.test-cases.indexAll') : ($isProjectIndex ? route('dashboard.projects.test-cases.index', $project->id) : route('dashboard.projects.test-suites.test-cases.index', [$project->id, $testSuite->id])) }}">
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-zinc-800 dark:text-white mb-3">Filter Test Cases</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Project Filter Dropdown (Only for generic index) -->
                        @if ($isGenericIndex)
                            <div>
                                <label for="project-select"
                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Project</label>
                                <div class="dropdown-container" x-data="{ open: false }">
                                    <button type="button" @click="open = !open" @keydown.escape="open = false"
                                        class="w-full flex items-center justify-between px-4 py-2 bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <span x-text="selectedProjectName || 'All Projects'" class="truncate"></span>
                                        <i data-lucide="chevron-down"
                                            class="w-5 h-5 text-zinc-400 transition-transform duration-200"
                                            :class="{ 'rotate-180': open }"></i>
                                    </button>

                                    <div x-show="open" @click.outside="open = false"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="dropdown-menu max-h-60 overflow-y-auto">

                                        <!-- Search projects -->
                                        <div class="p-2 border-b border-zinc-200 dark:border-zinc-700">
                                            <input type="search" x-model="projectSearchTerm"
                                                placeholder="Search projects..."
                                                class="w-full px-3 py-2 bg-zinc-50 dark:bg-zinc-700 border-transparent rounded-md text-sm focus:ring-indigo-500">
                                        </div>

                                        <ul class="py-1">
                                            <!-- All Projects option -->
                                            <li>
                                                <button type="button" @click="selectProject('', 'All Projects')"
                                                    class="w-full text-left px-4 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                    :class="{ 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300': selectedProjectId === '' }">
                                                    All Projects
                                                </button>
                                            </li>

                                            <!-- Dynamic project options -->
                                            <template x-for="project in filteredProjects" :key="project.id">
                                                <li>
                                                    <button type="button" @click="selectProject(project.id, project.name)"
                                                        class="w-full text-left px-4 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                        :class="{
                                                            'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300': selectedProjectId ===
                                                                project.id
                                                        }">
                                                        <span x-text="project.name"></span>
                                                    </button>
                                                </li>
                                            </template>

                                            <!-- No results message -->
                                            <template x-if="filteredProjects.length === 0 && projectSearchTerm">
                                                <li class="px-4 py-2 text-zinc-500 dark:text-zinc-400 text-sm">
                                                    No matching projects found
                                                </li>
                                            </template>
                                        </ul>
                                    </div>

                                    <!-- Hidden input to submit with form -->
                                    <input type="hidden" name="project_id" x-model="selectedProjectId">
                                </div>
                            </div>
                        @endif

                        <!-- Test Suite Filter Dropdown (Generic or Project-specific) -->
                        @if ($isGenericIndex || $isProjectIndex)
                            <div>
                                <label for="suite-select"
                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Test
                                    Suite</label>
                                <div class="dropdown-container" x-data="{ open: false }">
                                    <button type="button" @click="open = !open" @keydown.escape="open = false"
                                        :disabled="isGenericIndex && !selectedProjectId"
                                        class="w-full flex items-center justify-between px-4 py-2 bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span x-text="selectedSuiteName || 'All Suites'" class="truncate"></span>
                                        <i data-lucide="chevron-down"
                                            class="w-5 h-5 text-zinc-400 transition-transform duration-200"
                                            :class="{ 'rotate-180': open }"></i>
                                    </button>

                                    <div x-show="open" @click.outside="open = false"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="dropdown-menu max-h-60 overflow-y-auto">

                                        <!-- Search test suites -->
                                        <div class="p-2 border-b border-zinc-200 dark:border-zinc-700">
                                            <input type="search" x-model="suiteSearchTerm"
                                                placeholder="Search test suites..."
                                                class="w-full px-3 py-2 bg-zinc-50 dark:bg-zinc-700 border-transparent rounded-md text-sm focus:ring-indigo-500">
                                        </div>

                                        <ul class="py-1">
                                            <!-- All Suites option -->
                                            <li>
                                                <button type="button" @click="selectSuite('', 'All Suites')"
                                                    class="w-full text-left px-4 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                    :class="{ 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300': selectedSuiteId === '' }">
                                                    All Suites
                                                </button>
                                            </li>

                                            <!-- Dynamic suite options -->
                                            <template x-for="suite in filteredSuites" :key="suite.id">
                                                <li>
                                                    <button type="button" @click="selectSuite(suite.id, suite.name)"
                                                        class="w-full text-left px-4 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                        :class="{
                                                            'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300': selectedSuiteId ===
                                                                suite.id
                                                        }">
                                                        <span x-text="suite.name"></span>
                                                    </button>
                                                </li>
                                            </template>

                                            <!-- No results message -->
                                            <template x-if="filteredSuites.length === 0 && suiteSearchTerm">
                                                <li class="px-4 py-2 text-zinc-500 dark:text-zinc-400 text-sm">
                                                    No matching test suites found
                                                </li>
                                            </template>
                                        </ul>
                                    </div>

                                    <!-- Hidden input to submit with form -->
                                    <input type="hidden" name="suite_id" x-model="selectedSuiteId">
                                </div>
                            </div>
                        @endif

                        <!-- Search Input -->
                        <div class="col-span-1 md:col-span-2">
                            <label for="search"
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Search</label>
                            <div class="relative">
                                <i data-lucide="search"
                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 text-zinc-400 w-4 h-4"></i>
                                <input type="search" id="search" name="search" value="{{ $searchTerm }}"
                                    placeholder="Search by title or content..."
                                    class="w-full pl-10 pr-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-zinc-800">
                                <button type="button" @click="clearSearch"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-zinc-400 hover:text-zinc-500"
                                    x-show="searchTerm">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit and Reset Buttons -->
                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="resetFilters"
                            class="px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                            Reset Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Test Cases List -->
        <div
            class="bg-white dark:bg-zinc-800 rounded-xl shadow-md border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-zinc-800 dark:text-white">
                    @if ($testCases->count() > 0)
                        {{ $testCases->count() }} {{ Str::plural('Test Case', $testCases->count()) }}
                    @else
                        Test Cases
                    @endif
                </h3>

                <!-- Sort Controls -->
                <div class="flex items-center space-x-2">
                    <label for="sort" class="text-sm text-zinc-600 dark:text-zinc-400">Sort by:</label>
                    <select id="sort" name="sort" @change="updateSort($event)"
                        class="border border-zinc-300 dark:border-zinc-600 rounded-lg text-sm py-1 pl-3 pr-8 bg-white dark:bg-zinc-800 focus:ring-indigo-500">
                        <option value="title" {{ $sortField === 'title' ? 'selected' : '' }}>Title</option>
                        <option value="updated_at" {{ $sortField === 'updated_at' ? 'selected' : '' }}>Last Updated
                        </option>
                        <option value="created_at" {{ $sortField === 'created_at' ? 'selected' : '' }}>Date Created
                        </option>
                    </select>

                    <button type="button" @click="toggleSortDirection"
                        class="p-1 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-700"
                        title="{{ $sortDirection === 'asc' ? 'Sort Descending' : 'Sort Ascending' }}">
                        <i data-lucide="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}"
                            class="w-5 h-5 text-zinc-500 dark:text-zinc-400"></i>
                    </button>
                </div>
            </div>

            @if ($testCases->isEmpty())
                <div class="text-center py-16 px-6">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-700 mb-4">
                        <i data-lucide="file-check-2" class="w-8 h-8 text-zinc-400 dark:text-zinc-500"></i>
                    </div>
                    <h3 class="text-lg font-medium text-zinc-800 dark:text-white mb-2">No Test Cases Found</h3>
                    <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-6">
                        @if ($searchTerm)
                            No test cases match your search criteria. Try adjusting your filters.
                        @elseif ($isGenericIndex && $selectedProjectId)
                            No test cases found for the selected project. Create your first test case to get started.
                        @elseif ($isProjectIndex && $selectedSuiteId)
                            No test cases found for the selected test suite. Create your first test case to get started.
                        @elseif ($isGenericIndex)
                            No test cases found across your projects. Select a project to create test cases.
                        @else
                            No test cases have been created yet. Create your first test case to get started.
                        @endif
                    </p>

                    @if (($isGenericIndex && $selectedProjectId) || ($isProjectIndex && !$selectedSuiteId) || $isSuiteIndex)
                        <a href="{{ $isGenericIndex
                            ? route('dashboard.projects.test-cases.create', $selectedProjectId)
                            : ($isProjectIndex
                                ? route('dashboard.projects.test-cases.create', $project->id)
                                : route('dashboard.projects.test-suites.test-cases.create', [$project->id, $testSuite->id])) }}"
                            class="btn-primary inline-flex items-center group">
                            <i data-lucide="plus" class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform"></i>
                            Create First Test Case
                        </a>
                    @endif
                </div>
            @else
                <!-- Test Cases Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/80">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Title & Description
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Location
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Updated
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($testCases as $testCase)
                                <tr id="test-case-{{ $testCase->id }}"
                                    data-project-id="{{ $isGenericIndex ? $testCase->project_id : $project->id }}"
                                    class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-white truncate max-w-xs">
                                            {{ $testCase->title }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate max-w-xs">
                                            {{ Str::limit($testCase->expected_results, 50) }}
                                        </div>
                                        <div class="mt-1 flex items-center">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                {{ is_array($testCase->steps) ? count($testCase->steps) : 0 }}
                                                {{ Str::plural('step', is_array($testCase->steps) ? count($testCase->steps) : 0) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            @if ($isGenericIndex)
                                                <div class="text-indigo-600 dark:text-indigo-400 font-medium">
                                                    {{ $testCase->project_name ?? 'Unknown Project' }}
                                                </div>
                                                <div class="text-zinc-500 dark:text-zinc-400 text-xs">
                                                    {{ $testCase->suite_name ?? 'Unknown Suite' }}
                                                </div>
                                            @elseif ($isProjectIndex)
                                                <div class="text-zinc-700 dark:text-zinc-300">
                                                    {{ $testCase->suite_name ?? 'Unknown Suite' }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $testCase->updated_at->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-3">
                                            <a href="{{ route('dashboard.projects.test-cases.show', [
                                                'project' => $isGenericIndex ? $testCase->project_id : $project->id,
                                                'test_case' => $testCase->id,
                                            ]) }}"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                                <i data-lucide="eye" class="w-5 h-5"></i>
                                            </a>

                                            {{-- Suggestion: Update Edit Link too --}}
                                            <a href="{{ route('dashboard.projects.test-cases.edit', [
                                                'project' => $isGenericIndex ? $testCase->project_id : $project->id,
                                                'test_case' => $testCase->id,
                                            ]) }}"
                                                class="text-amber-600 dark:text-amber-400 hover:text-amber-900 dark:hover:text-amber-300">
                                                <i data-lucide="edit-3" class="w-5 h-5"></i>
                                            </a>

                                            <button type="button"
                                                @click="openDeleteModal('{{ $testCase->id }}', '{{ addslashes($testCase->title) }}', '{{ $isGenericIndex ? $testCase->project_id : $project->id }}')"
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
                    {{ $testCases->links() }}
                </div>
            @endif
        </div>

        <!-- Delete Modal (Similar to test-suites) -->
        <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-zinc-900/60 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
                    @click="closeDeleteModal" aria-hidden="true"></div>
                <!-- Modal panel -->
                <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-zinc-200 dark:border-zinc-700">
                    <!-- Modal Content -->
                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600 dark:text-red-400"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-white" id="modal-title">
                                    Delete Test Case
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        Are you sure you want to delete the test case "<strong
                                            class="font-semibold text-zinc-700 dark:text-zinc-200"
                                            x-text="deleteItemTitle"></strong>"? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal Footer -->
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                        <button @click="confirmDelete()" type="button"
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-zinc-800 sm:w-auto sm:text-sm disabled:opacity-50"
                            :disabled="isDeleting">
                            <span x-show="!isDeleting">Delete Test Case</span>
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

        /* Dropdown animations */
        .dropdown-menu {
            transform-origin: top center;
        }

        /* Refined scrollbar styling */
        .dropdown-menu {
            scrollbar-width: thin;
            scrollbar-color: rgba(161, 161, 170, 0.5) rgba(63, 63, 70, 0.1);
        }

        .dropdown-menu::-webkit-scrollbar {
            width: 6px;
        }

        .dropdown-menu::-webkit-scrollbar-track {
            background: rgba(63, 63, 70, 0.1);
            border-radius: 3px;
        }

        .dropdown-menu::-webkit-scrollbar-thumb {
            background-color: rgba(161, 161, 170, 0.5);
            border-radius: 3px;
        }

        .dropdown-menu::-webkit-scrollbar-thumb:hover {
            background-color: rgba(161, 161, 170, 0.7);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('testCasesManager', (config) => ({
                // State variables from config
                isGenericIndex: config.isGenericIndex,
                isProjectIndex: config.isProjectIndex,
                isSuiteIndex: config.isSuiteIndex,
                projectId: config.projectId,
                suiteId: config.suiteId,

                // Sorting and filtering
                selectedProjectId: config.selectedProjectId || '',
                selectedProjectName: '',
                selectedSuiteId: config.selectedSuiteId || '',
                selectedSuiteName: '',
                searchTerm: config.searchTerm || '',
                sortField: config.sortField || 'updated_at',
                sortDirection: config.sortDirection || 'desc',

                // Projects and suites lists
                projects: config.projects || [],
                testSuites: config.testSuites || [],

                // Search filters
                projectSearchTerm: '',
                suiteSearchTerm: '',

                // Delete modal state
                showDeleteModal: false,
                deleteItemId: null,
                deleteItemTitle: '',
                isDeleting: false,

                // Notification state
                showNotification: false,
                notificationType: 'success',
                notificationMessage: '',

                // Initialize component
                init() {
                    this.setInitialSelections();
                    this.initNotificationsFromFlash();

                    this.$nextTick(() => {
                        // Make sure Lucide icons are rendered
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });
                },

                setInitialSelections() {
                    // Set project name if ID is present
                    if (this.selectedProjectId) {
                        const project = this.projects.find(p => p.id === this.selectedProjectId);
                        this.selectedProjectName = project ? project.name : 'All Projects';
                    } else {
                        this.selectedProjectName = 'All Projects';
                    }

                    // Set suite name if ID is present
                    if (this.selectedSuiteId) {
                        const suite = this.testSuites.find(s => s.id === this.selectedSuiteId);
                        this.selectedSuiteName = suite ? suite.name : 'All Suites';
                    } else {
                        this.selectedSuiteName = 'All Suites';
                    }
                },

                // Project filtering and selection
                get filteredProjects() {
                    if (!this.projectSearchTerm) {
                        return this.projects;
                    }

                    const searchTerm = this.projectSearchTerm.toLowerCase();
                    return this.projects.filter(project =>
                        project.name.toLowerCase().includes(searchTerm)
                    );
                },

                selectProject(id, name) {
                    this.selectedProjectId = id;
                    this.selectedProjectName = name;

                    // Clear suite selection when project changes
                    this.selectedSuiteId = '';
                    this.selectedSuiteName = 'All Suites';

                    // If this is a generic index, need to fetch suites for the selected project
                    if (this.isGenericIndex && id) {
                        this.fetchSuitesForProject(id);
                    } else if (this.isGenericIndex && !id) {
                        // Also clear local suites if 'All Projects' is selected
                        this.testSuites = [];
                    }

                    this.submitFilterForm();
                },

                async fetchSuitesForProject(projectId) {
                    try {
                        const response = await fetch(
                            `/dashboard/api/projects/${projectId}/test-suites`);
                        if (response.ok) {
                            const data = await response.json();
                            this.testSuites = data;
                        }
                    } catch (error) {
                        console.error('Error fetching test suites:', error);
                    }
                },

                // Suite filtering and selection
                get filteredSuites() {
                    if (!this.suiteSearchTerm) {
                        return this.testSuites;
                    }

                    const searchTerm = this.suiteSearchTerm.toLowerCase();
                    return this.testSuites.filter(suite =>
                        suite.name.toLowerCase().includes(searchTerm)
                    );
                },

                selectSuite(id, name) {
                    this.selectedSuiteId = id;
                    this.selectedSuiteName = name;

                    this.submitFilterForm();
                },

                // Form and filter controls
                clearSearch() {
                    this.searchTerm = '';
                    document.getElementById('search').value = '';
                },

                resetFilters() {
                    this.selectedProjectId = '';
                    this.selectedProjectName = 'All Projects';
                    this.selectedSuiteId = '';
                    this.selectedSuiteName = 'All Suites';
                    this.searchTerm = '';
                    document.getElementById('search').value = '';
                    this.sortField = 'updated_at';
                    this.sortDirection = 'desc';

                    // Submit the form
                    document.getElementById('filter-form').submit();
                },

                // Sorting controls
                updateSort(event) {
                    this.sortField = event.target.value;

                    // Add hidden inputs and submit form
                    const form = document.getElementById('filter-form');

                    // Create or update sort field input
                    let sortInput = form.querySelector('input[name="sort"]');
                    if (!sortInput) {
                        sortInput = document.createElement('input');
                        sortInput.type = 'hidden';
                        sortInput.name = 'sort';
                        form.appendChild(sortInput);
                    }
                    sortInput.value = this.sortField;

                    // Create or update direction input
                    let directionInput = form.querySelector('input[name="direction"]');
                    if (!directionInput) {
                        directionInput = document.createElement('input');
                        directionInput.type = 'hidden';
                        directionInput.name = 'direction';
                        form.appendChild(directionInput);
                    }
                    directionInput.value = this.sortDirection;

                    form.submit();
                },

                toggleSortDirection() {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';

                    // Add hidden input and submit form
                    const form = document.getElementById('filter-form');

                    let directionInput = form.querySelector('input[name="direction"]');
                    if (!directionInput) {
                        directionInput = document.createElement('input');
                        directionInput.type = 'hidden';
                        directionInput.name = 'direction';
                        form.appendChild(directionInput);
                    }
                    directionInput.value = this.sortDirection;

                    form.submit();
                },

                // Delete modal
                openDeleteModal(id, title, projectId = null) {
                    this.deleteItemId = id;
                    this.deleteItemTitle = title.replace(/&quot;/g, '"'); // Fix encoded quotes
                    this.deleteProjectId = projectId; // Store the project ID
                    this.isDeleting = false;
                    this.showDeleteModal = true;
                },

                closeDeleteModal() {
                    if (!this.isDeleting) {
                        this.showDeleteModal = false;
                        this.deleteItemId = null;
                        this.deleteItemTitle = '';
                    }
                },

                submitFilterForm() {
                    this.$nextTick(() => {
                        document.getElementById('filter-form').submit();
                    });
                },

                async confirmDelete() {
                    if (!this.deleteItemId) return;

                    this.isDeleting = true;

                    try {
                        // Determine the correct delete URL based on context
                        let deleteUrl;

                        if (this.isSuiteIndex) {
                            // For suite-specific view
                            deleteUrl =
                                `/dashboard/projects/${this.projectId}/test-suites/${this.suiteId}/test-cases/${this.deleteItemId}`;
                        } else if (this.isProjectIndex) {
                            // For project-specific view
                            deleteUrl =
                                `/dashboard/projects/${this.projectId}/test-cases/${this.deleteItemId}`;
                        } else {
                            // For generic index, use the project ID we captured when opening the modal
                            if (!this.deleteProjectId) {
                                this.showError(
                                    'Cannot delete: Unable to determine project ID. Please try from project view.'
                                );
                                this.isDeleting = false;
                                return;
                            }

                            deleteUrl =
                                `/dashboard/projects/${this.deleteProjectId}/test-cases/${this.deleteItemId}`;
                        }

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
                            // Remove the test case row
                            const caseRow = document.getElementById(
                                `test-case-${this.deleteItemId}`);
                            if (caseRow) {
                                caseRow.remove();
                            } else {
                                // If we can't find the row, just refresh
                                window.location.reload();
                            }

                            this.showSuccess(result.message || 'Test case deleted successfully.');
                        } else {
                            throw new Error(result.message || 'Failed to delete the test case.');
                        }
                    } catch (error) {
                        console.error('Delete Error:', error);
                        this.showError(error.message || 'An unexpected error occurred.');
                    } finally {
                        this.isDeleting = false;
                        this.closeDeleteModal();
                    }
                },

                // Notifications
                initNotificationsFromFlash() {
                    // Check for flash messages in the session
                    const flashSuccess = '{{ session('success') }}';
                    const flashError = '{{ session('error') }}';

                    if (flashSuccess) {
                        this.showSuccess(flashSuccess);
                    }

                    if (flashError) {
                        this.showError(flashError);
                    }
                },

                showSuccess(message) {
                    this.notificationType = 'success';
                    this.notificationMessage = message;
                    this.showNotification = true;
                    setTimeout(() => this.hideNotification(), 5000);
                },

                showError(message) {
                    this.notificationType = 'error';
                    this.notificationMessage = message;
                    this.showNotification = true;
                    setTimeout(() => this.hideNotification(), 7000); // Longer for errors
                },

                hideNotification() {
                    this.showNotification = false;
                }
            }));
        });
    </script>
@endpush

@push('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
