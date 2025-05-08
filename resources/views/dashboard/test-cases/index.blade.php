{{-- resources/views/dashboard/test-cases/index.blade.php --}}
@php
    /**
     * @var \App\Models\Project|null $project
     * @var \App\Models\TestSuite|null $testSuite
     * @var \App\Models\Team|null $team
     * @var \Illuminate\Pagination\LengthAwarePaginator $testCases
     * @var \Illuminate\Database\Eloquent\Collection<\App\Models\Project>   $projectsForFilter
     * @var \Illuminate\Database\Eloquent\Collection<\App\Models\Story>     $storiesForFilter
     * @var \Illuminate\Database\Eloquent\Collection<\App\Models\TestSuite> $suitesForFilter
     */
    $isGenericIndex = !isset($project);
    $isProjectIndex = isset($project) && !isset($testSuite);
    $isSuiteIndex = isset($project) && isset($testSuite);

    if ($isGenericIndex) {
        $pageTitle = 'All Test Cases';
    } elseif ($isProjectIndex) {
        $pageTitle = 'Test Cases for ' . $project->name;
    } else {
        $pageTitle = 'Test Cases in ' . $testSuite->name;
    }

    $selectedProjectId = $selectedProjectId ?? '';
    $selectedStoryId = $selectedStoryId ?? '';
    $selectedSuiteId = $selectedSuiteId ?? '';
    $storyParam = $selectedStoryId ? '?story_id=' . $selectedStoryId : '';
    $searchTerm = $searchTerm ?? '';
    $sortField = $sortField ?? 'updated_at';
    $sortDirection = $sortDirection ?? 'desc';

    $headerDescription = $isGenericIndex
        ? 'Manage test cases across all projects. Use filters to narrow your view.'
        : ($isProjectIndex
            ? 'Manage test cases for the project.'
            : 'View and manage test cases in the test suite.');

    // Define the create route based on context
    $createButtonRoute = null;
    if ($isGenericIndex && $selectedProjectId) {
        $createButtonRoute = route('dashboard.projects.test-cases.create', $selectedProjectId) . ($storyParam ?? '');
    } elseif ($isProjectIndex && isset($project)) {
        $createButtonRoute = route('dashboard.projects.test-cases.create', $project->id) . ($storyParam ?? '');
    } elseif (isset($project) && isset($testSuite)) {
        $createButtonRoute =
            route('dashboard.projects.test-suites.test-cases.create', [$project->id, $testSuite->id]) .
            ($storyParam ?? '');
    }
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
        selectedStoryId: '{{ $selectedStoryId ?? '' }}',
        selectedSuiteId: '{{ $selectedSuiteId }}',
        searchTerm: '{{ $searchTerm }}',
        sortField: '{{ $sortField }}',
        sortDirection: '{{ $sortDirection }}',
        @if ($isGenericIndex) projects:   {{ json_encode($projectsForFilter->toArray()) }},
            stories:    {{ json_encode($storiesForFilter->toArray()) }},
            testSuites: {{ json_encode($suitesForFilter->toArray()) }},
        @else
            stories:    {{ json_encode($storiesForFilter->toArray()) }},
            testSuites: {{ json_encode($suitesForFilter->toArray()) }}, @endif
        projectId: '{{ $project->id ?? '' }}',
        suiteId: '{{ $testSuite->id ?? '' }}'
    })" x-init="init()" class="space-y-8">

        {{-- Header --}}
        <x-index-header title="{{ $pageTitle }}" description="{{ $headerDescription }}" :createDisabled="$isGenericIndex && !$selectedProjectId"
            createDisabledText="Select Project to Create" :createRoute="$createButtonRoute" createText="New Test Case"
            createIcon="plus-circle" />

        {{-- Filters --}}
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-md p-5 border border-zinc-200 dark:border-zinc-700">
            <form id="filter-form" method="GET"
                action="{{ $isGenericIndex
                    ? route('dashboard.test-cases.indexAll')
                    : ($isProjectIndex
                        ? route('dashboard.projects.test-cases.index', $project->id)
                        : route('dashboard.projects.test-suites.test-cases.index', [$project->id, $testSuite->id])) }}">
                <input type="hidden" name="story_id" value="{{ $selectedStoryId }}">
                <input type="hidden" name="suite_id" value="{{ $selectedSuiteId }}">
                <input type="hidden" name="sort" value="{{ $sortField }}">
                <input type="hidden" name="direction" value="{{ $sortDirection }}">

                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-zinc-800 dark:text-white mb-3">Filter Test Cases</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- Project Dropdown --}}
                        @if ($isGenericIndex)
                            <div>
                                <label
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
                                        <div class="p-2 border-b border-zinc-200 dark:border-zinc-700">
                                            <input type="search" x-model="projectSearchTerm"
                                                placeholder="Search projects..."
                                                class="w-full px-3 py-2 bg-zinc-50 dark:bg-zinc-700 border-transparent rounded-md text-sm focus:ring-indigo-500">
                                        </div>
                                        <ul class="py-1">
                                            <li>
                                                <button type="button" @click="selectProject('', 'All Projects')"
                                                    class="w-full text-left px-4 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                    :class="{ 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300': selectedProjectId === '' }">
                                                    All Projects
                                                </button>
                                            </li>
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
                                            <template x-if="filteredProjects.length === 0 && projectSearchTerm">
                                                <li class="px-4 py-2 text-zinc-500 dark:text-zinc-400 text-sm">
                                                    No matching projects found
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                    <input type="hidden" name="project_id" x-model="selectedProjectId">
                                </div>
                            </div>
                        @endif

                        {{-- Story Dropdown --}}
                        @if ($isGenericIndex || $isProjectIndex)
                            <div>
                                <label
                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Story</label>
                                <div class="dropdown-container" x-data="{ open: false }">
                                    <button type="button" @click="open = !open" @keydown.escape="open = false"
                                        :disabled="isGenericIndex && !selectedProjectId"
                                        class="w-full flex items-center justify-between px-4 py-2 bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span x-text="selectedStoryName || 'All Stories'" class="truncate"></span>
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
                                        <div class="p-2 border-b border-zinc-200 dark:border-zinc-700">
                                            <input type="search" x-model="storySearchTerm"
                                                placeholder="Search stories..."
                                                class="w-full px-3 py-2 bg-zinc-50 dark:bg-zinc-700 border-transparent rounded-md text-sm focus:ring-indigo-500">
                                        </div>
                                        <ul class="py-1">
                                            <li>
                                                <button type="button" @click="selectStory('', 'All Stories')"
                                                    class="w-full text-left px-4 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                    :class="{ 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300': selectedStoryId === '' }">
                                                    All Stories
                                                </button>
                                            </li>
                                            <template x-for="story in filteredStories" :key="story.id">
                                                <li>
                                                    <button type="button" @click="selectStory(story.id, story.title)"
                                                        class="w-full text-left px-4 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                        :class="{
                                                            'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300': selectedStoryId ===
                                                                story.id
                                                        }">
                                                        <span x-text="story.title"></span>
                                                    </button>
                                                </li>
                                            </template>
                                            <template x-if="filteredStories.length === 0 && storySearchTerm">
                                                <li class="px-4 py-2 text-zinc-500 dark:text-zinc-400 text-sm">
                                                    No matching stories found
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                    <input type="hidden" name="story_id" x-model="selectedStoryId">
                                </div>
                            </div>
                        @endif

                        {{-- Suite Dropdown --}}
                        @if ($isGenericIndex || $isProjectIndex)
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Test
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
                                        <div class="p-2 border-b border-zinc-200 dark:border-zinc-700">
                                            <input type="search" x-model="suiteSearchTerm"
                                                placeholder="Search test suites..."
                                                class="w-full px-3 py-2 bg-zinc-50 dark:bg-zinc-700 border-transparent rounded-md text-sm focus:ring-indigo-500">
                                        </div>
                                        <ul class="py-1">
                                            <li>
                                                <button type="button" @click="selectSuite('', 'All Suites')"
                                                    class="w-full text-left px-4 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                    :class="{ 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300': selectedSuiteId === '' }">
                                                    All Suites
                                                </button>
                                            </li>
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
                                            <template x-if="filteredSuites.length === 0 && suiteSearchTerm">
                                                <li class="px-4 py-2 text-zinc-500 dark:text-zinc-400 text-sm">
                                                    No matching test suites found
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                    <input type="hidden" name="suite_id" x-model="selectedSuiteId">
                                </div>
                            </div>
                        @endif

                        {{-- Search Input --}}
                        <div class="col-span-1 md:col-span-1">
                            <label for="search"
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Search</label>
                            <div class="relative">
                                <i data-lucide="search"
                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 text-zinc-400 w-4 h-4"></i>
                                <input type="search" id="search" name="search" x-model="searchTerm"
                                    value="{{ $searchTerm }}" placeholder="Search by title or content..."
                                    @keydown.enter.prevent="submitFilterForm()"
                                    class="w-full pl-10 pr-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-zinc-800">
                            </div>
                        </div>
                    </div>

                    {{-- Reset --}}
                    {{-- <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="resetFilters"
                            class="px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                            Reset Filters
                        </button>
                    </div> --}}
                </div>
            </form>
        </div>

        {{-- Test Cases List --}}
        <x-list-view :items="$testCases" :columns="[
            'title' => 'Title',
            'location' => 'Location',
            'steps' => 'Steps',
            'updated_at' => 'Updated',
            'actions' => 'Actions',
        ]" :sortField="$sortField" :sortDirection="$sortDirection" entityName="Test Case"
            emptyStateTitle="No Test Cases Found" :emptyStateDescription="$searchTerm
                ? 'No test cases match your search criteria. Try adjusting your filters.'
                : 'No test cases found. Create your first test case to get started.'" emptyStateIcon="file-check-2" :createRoute="$isGenericIndex && $selectedProjectId
                ? route('dashboard.projects.test-cases.create', $selectedProjectId)
                : (!$isGenericIndex
                    ? route('dashboard.projects.test-cases.create', $project->id)
                    : null)"
            createLabel="Create Test Case">
            @foreach ($testCases as $testCase)
                <tr id="test-case-{{ $testCase->id }}"
                    data-project-id="{{ $isGenericIndex ? $testCase->project_id : $project->id }}"
                    class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-zinc-900 dark:text-white">
                            <a href="{{ route('dashboard.projects.test-cases.show', [
                                'project' => $isGenericIndex ? $testCase->project_id ?? 'missing-project' : $project->id,
                                'test_case' => $testCase->id,
                            ]) }}"
                                class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200 group">
                                {{ $testCase->title }}
                                <i data-lucide="arrow-up-right"
                                    class="h-3 w-3 ml-1 inline-block opacity-0 group-hover:opacity-100 transition-opacity duration-200"></i>
                            </a>
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 line-clamp-1">
                            {{ Str::limit($testCase->expected_results, 50) }}
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
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                            {{ is_array($testCase->steps) ? count($testCase->steps) : 0 }}
                            {{ Str::plural('step', is_array($testCase->steps) ? count($testCase->steps) : 0) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $testCase->updated_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('dashboard.projects.test-cases.edit', [
                                'project' => $isGenericIndex ? $testCase->project_id : $project->id,
                                'test_case' => $testCase->id,
                            ]) }}"
                                class="text-amber-600 dark:text-amber-400 hover:text-amber-900 dark:hover:text-amber-300 p-1.5 rounded-full hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                            <button type="button"
                                @click="openDeleteModal('{{ $testCase->id }}','{{ addslashes($testCase->title) }}','{{ $isGenericIndex ? $testCase->project_id : $project->id }}')"
                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                <i data-lucide="trash" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach

            {{-- Pagination slot --}}
            @if ($testCases instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <x-slot name="pagination">
                    {{ $testCases->links() }}
                </x-slot>
            @endif
        </x-list-view>

        {{-- Delete Modal --}}
        <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-zinc-900/60 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
                    @click="closeDeleteModal" aria-hidden="true"></div>
                <x-modals.delete-confirmation title="Delete Test Case"
                    message="Are you sure you want to delete the test case" itemName="deleteItemTitle"
                    dangerText="This action cannot be undone." confirmText="Delete Test Case" />
            </div>
        </div>

        {{-- Notification Toast --}}
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
                isGenericIndex: config.isGenericIndex,
                isProjectIndex: config.isProjectIndex,
                isSuiteIndex: config.isSuiteIndex,
                projectId: config.projectId,
                suiteId: config.suiteId,

                selectedProjectId: config.selectedProjectId || '',
                selectedProjectName: '',
                selectedStoryId: config.selectedStoryId || '',
                selectedStoryName: '',
                selectedSuiteId: config.selectedSuiteId || '',
                selectedSuiteName: '',

                searchTerm: config.searchTerm || '',
                sortField: config.sortField || 'updated_at',
                sortDirection: config.sortDirection || 'desc',

                projects: config.projects || [],
                stories: config.stories || [],
                testSuites: config.testSuites || [],

                projectSearchTerm: '',
                storySearchTerm: '',
                suiteSearchTerm: '',

                showDeleteModal: false,
                deleteItemId: null,
                deleteItemTitle: '',
                deleteProjectId: null,
                isDeleting: false,
                deleteConfirmed: false,
                requireConfirmation: true,

                showNotification: false,
                notificationType: 'success',
                notificationMessage: '',

                init() {
                    this.setInitialSelections();
                    this.initNotificationsFromFlash();
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                setInitialSelections() {
                    // Project
                    if (this.selectedProjectId) {
                        const p = this.projects.find(x => x.id === this.selectedProjectId);
                        this.selectedProjectName = p ? p.name : 'All Projects';
                    } else {
                        this.selectedProjectName = 'All Projects';
                    }
                    // Story
                    if (this.selectedStoryId) {
                        const s = this.stories.find(x => x.id === this.selectedStoryId);
                        this.selectedStoryName = s ? s.title : 'All Stories';
                    } else {
                        this.selectedStoryName = 'All Stories';
                    }
                    // Suite
                    if (this.selectedSuiteId) {
                        const t = this.testSuites.find(x => x.id === this.selectedSuiteId);
                        this.selectedSuiteName = t ? t.name : 'All Suites';
                    } else {
                        this.selectedSuiteName = 'All Suites';
                    }
                },

                get filteredProjects() {
                    return this._filterByName(this.projects, this.projectSearchTerm);
                },
                get filteredStories() {
                    return this._filterByName(this.stories, this.storySearchTerm, 'title');
                },
                get filteredSuites() {
                    return this._filterByName(this.testSuites, this.suiteSearchTerm);
                },

                _filterByName(collection, term, key = 'name') {
                    if (!term) return collection;
                    return collection.filter(item =>
                        item[key].toLowerCase().includes(term.toLowerCase())
                    );
                },

                async selectProject(id, name) {
                    this.selectedProjectId = id;
                    this.selectedProjectName = name;

                    // clear sub-selections
                    this.selectedStoryId = '';
                    this.selectedStoryName = 'All Stories';
                    this.selectedSuiteId = '';
                    this.selectedSuiteName = 'All Suites';

                    if (id) {
                        await Promise.all([
                            this.fetchSuitesForProject(id),
                            this.fetchStoriesForProject(id)
                        ]);
                    } else {
                        this.testSuites = [];
                        this.stories = [];
                    }
                    this.submitFilterForm();
                },

                async fetchSuitesForProject(projectId) {
                    try {
                        const res = await fetch(`/dashboard/api/projects/${projectId}/test-suites`);
                        const json = await res.json();
                        if (json.success) this.testSuites = json.test_suites;
                    } catch (e) {
                        console.error(e);
                    }
                },
                clearSearch() {
                    this.searchTerm = '';
                    document.getElementById('search').value = '';
                    this.submitFilterForm();
                },


                async fetchStoriesForProject(projectId) {
                    try {
                        const res = await fetch(`/dashboard/api/projects/${projectId}/stories`);
                        const json = await res.json();
                        if (json.success) this.stories = json.stories;
                    } catch (e) {
                        console.error(e);
                    }
                },

                selectStory(id, name) {
                    this.selectedStoryId = id;
                    this.selectedStoryName = name;
                    // Make sure the hidden input is updated
                    document.querySelector('input[name="story_id"]').value = id;
                    this.submitFilterForm();
                },
                submitFilterForm() {
                    // Make sure all filter inputs are included in the form
                    const form = document.getElementById('filter-form');

                    // Make sure story_id is included even if unselected
                    let storyInput = form.querySelector('input[name="story_id"]');
                    if (!storyInput) {
                        storyInput = document.createElement('input');
                        storyInput.type = 'hidden';
                        storyInput.name = 'story_id';
                        form.appendChild(storyInput);
                    }
                    storyInput.value = this.selectedStoryId;

                    let searchInput = form.querySelector('input[name="search"]');
                    if (!searchInput) {
                        searchInput = document.createElement('input');
                        searchInput.type = 'hidden';
                        searchInput.name = 'search';
                        form.appendChild(searchInput);
                    }
                    searchInput.value = this.searchTerm;

                    // Submit the form
                    this.$nextTick(() => form.submit());
                },

                selectSuite(id, name) {
                    this.selectedSuiteId = id;
                    this.selectedSuiteName = name;
                    this.submitFilterForm();
                },

                clearSearch() {
                    this.searchTerm = '';
                    document.getElementById('search').value = '';
                },

                resetFilters() {
                    this.selectedProjectId = '';
                    this.selectedProjectName = 'All Projects';
                    this.selectedStoryId = '';
                    this.selectedStoryName = 'All Stories';
                    this.selectedSuiteId = '';
                    this.selectedSuiteName = 'All Suites';
                    this.searchTerm = '';
                    document.getElementById('search').value = '';
                    this.sortField = 'updated_at';
                    this.sortDirection = 'desc';
                    document.getElementById('filter-form').submit();
                },

                updateSort(event) {
                    this.sortField = event.target.value;
                    const form = document.getElementById('filter-form');
                    let sortInput = form.querySelector('input[name="sort"]');
                    if (!sortInput) {
                        sortInput = document.createElement('input');
                        sortInput.type = 'hidden';
                        sortInput.name = 'sort';
                        form.appendChild(sortInput);
                    }
                    sortInput.value = this.sortField;
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

                openDeleteModal(id, title, projectId = null) {
                    this.deleteItemId = id;
                    this.deleteItemTitle = title.replace(/&quot;/g, '"');
                    this.deleteProjectId = projectId;
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

                async confirmDelete() {
                    if (!this.deleteItemId) return;
                    this.isDeleting = true;
                    try {
                        let deleteUrl;
                        if (this.isSuiteIndex) {
                            deleteUrl =
                                `/dashboard/projects/${this.projectId}/test-suites/${this.suiteId}/test-cases/${this.deleteItemId}`;
                        } else if (this.isProjectIndex) {
                            deleteUrl =
                                `/dashboard/projects/${this.projectId}/test-cases/${this.deleteItemId}`;
                        } else {
                            if (!this.deleteProjectId) {
                                this.showError('Cannot delete: Unable to determine project ID.');
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
                            const row = document.getElementById(`test-case-${this.deleteItemId}`);
                            if (row) row.remove();
                            else window.location.reload();
                            this.showSuccess(result.message || 'Test case deleted.');
                        } else {
                            throw new Error(result.message || 'Failed to delete.');
                        }
                    } catch (error) {
                        console.error(error);
                        this.showError(error.message || 'Unexpected error.');
                    } finally {
                        this.isDeleting = false;
                        this.closeDeleteModal();
                    }
                },

                initNotificationsFromFlash() {
                    const flashSuccess = '{{ session('success') }}';
                    const flashError = '{{ session('error') }}';
                    if (flashSuccess) this.showSuccess(flashSuccess);
                    if (flashError) this.showError(flashError);
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
                    setTimeout(() => this.hideNotification(), 7000);
                },

                hideNotification() {
                    this.showNotification = false;
                },

                submitFilterForm() {
                    this.$nextTick(() => document.getElementById('filter-form').submit());
                }
            }));
        });
    </script>
@endpush

@push('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
