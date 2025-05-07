<!-- dashboard/stories/index.blade.php -->

@extends('layouts.dashboard')

@section('title', isset($project) ? "{$project->name} - Stories" : 'All Stories')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.stories.indexAll') }}"
            class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors duration-200">Stories</a>
    </li>
    @if (isset($project))
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <a href="{{ route('dashboard.projects.show', $project->id) }}"
                class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors duration-200">{{ $project->name }}</a>
        </li>
    @endif
@endsection

@section('content')
    <div class="h-full space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    @if (isset($project))
                        Stories for {{ $project->name }}
                    @else
                        All Stories
                    @endif
                </h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400 transition-colors duration-200">
                    Manage and view your user stories
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('dashboard.stories.create') }}"
                    class="btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white shadow-lg hover:shadow-indigo-500/30 transition-all duration-200">
                    <i data-lucide="plus" class="w-4 h-4"></i> New Story
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div
            class="bg-white dark:bg-zinc-800/50 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 p-4 backdrop-blur-sm relative z-10">
            <form
                action="{{ isset($project) ? route('dashboard.projects.stories.index', $project->id) : route('dashboard.stories.indexAll') }}"
                method="GET" class="space-y-4" id="filterForm">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                    <!-- Project Filter (only show if no project is set) -->
                    @if (!isset($project))
                        <div class="sm:col-span-3">
                            <div x-data="{
                                open: false,
                                selectedId: '{{ request('project_id', '') }}',
                                selectedName: '{{ request('project_id') ? $projects->firstWhere('id', request('project_id'))->name : 'All Projects' }}',
                                searchTerm: '',
                                get filteredProjects() {
                                    if (!this.searchTerm) return {{ json_encode($projects) }};
                                    return {{ json_encode($projects) }}.filter(p =>
                                        p.name.toLowerCase().includes(this.searchTerm.toLowerCase())
                                    );
                                },
                                selectProject(id, name) {
                                    this.selectedId = id;
                                    this.selectedName = name;
                                    document.getElementById('project_id_input').value = id;
                                    document.getElementById('project-filter-form').submit();
                                }
                            }">
                                <form id="project-filter-form"
                                    action="{{ isset($project) ? route('dashboard.projects.stories.index', $project->id) : route('dashboard.stories.indexAll') }}"
                                    method="GET">
                                    <input type="hidden" name="project_id" id="project_id_input" :value="selectedId">

                                    <label
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Project</label>

                                    <x-dropdown.search width="full" searchTerm="searchTerm"
                                        placeholder="Search projects..." noResultsMessage="No matching projects found"
                                        maxHeight="max-h-60" triggerClasses="w-full">
                                        <x-slot:trigger>
                                            <button type="button"
                                                class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 shadow-sm">
                                                <span x-text="selectedName" class="truncate"></span>
                                                <i data-lucide="chevron-down" class="w-4 h-4 ml-2 text-zinc-400"
                                                    :class="{ 'rotate-180': open }"></i>
                                            </button>
                                        </x-slot:trigger>

                                        <x-slot:content>
                                            <ul class="py-1">
                                                <li>
                                                    <button type="button" @click="selectProject('', 'All Projects')"
                                                        class="w-full text-left px-4 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                        :class="{ 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300': selectedId === '' }">
                                                        All Projects
                                                    </button>
                                                </li>
                                                <template x-for="project in filteredProjects" :key="project.id">
                                                    <li>
                                                        <button type="button"
                                                            @click="selectProject(project.id, project.name)"
                                                            class="w-full text-left px-4 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                            :class="{ 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300': selectedId ===
                                                                    project.id }">
                                                            <span x-text="project.name"></span>
                                                        </button>
                                                    </li>
                                                </template>
                                            </ul>
                                        </x-slot:content>
                                    </x-dropdown.search>
                                </form>
                            </div>
                        </div>
                    @endif

                    <!-- Sources Filter (Toggle Pills) -->
                    <div class="sm:col-span-{{ isset($project) ? '5' : '4' }}">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Source</label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" class="source-pill px-3 py-1.5 rounded-full text-sm font-medium border"
                                data-value="all">
                                All Sources
                            </button>

                            @foreach ($sources as $source)
                                <button type="button"
                                    class="source-pill px-3 py-1.5 rounded-full text-sm font-medium border"
                                    data-value="{{ $source }}">
                                    {{ ucfirst($source) }}
                                </button>
                            @endforeach
                        </div>
                        <div id="sources-container">
                            {{-- Hidden inputs for selected sources will be added here by JS --}}
                            {{-- We need to read the initial state from the request helper --}}
                            @foreach (request('sources', []) as $source)
                                <input type="hidden" name="sources[]" value="{{ $source }}">
                            @endforeach
                        </div>
                    </div>

                    <!-- Search Field -->
                    <div class="sm:col-span-5">
                        <div class="relative">
                            <div
                                class="absolute left-0 pl-3 flex items-center pointer-events-none text-zinc-400 dark:text-zinc-500">
                                <i data-lucide="search" class="h-4 w-4"></i>
                            </div>
                            <input type="text" name="search" value="{{ $searchTerm }}"
                                class="w-full pl-10 pr-12 py-2.5 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 placeholder-zinc-400 dark:placeholder-zinc-500 shadow-sm"
                                placeholder="Search title, description, or ID...">
                            <button type="submit"
                                class="absolute h-full right-0 px-4 flex items-center bg-indigo-600/10 dark:bg-indigo-400/10 border-l border-zinc-300/50 dark:border-zinc-600/50 hover:bg-indigo-600/20 dark:hover:bg-indigo-400/20 rounded-r-xl">
                                <i data-lucide="arrow-right" class="h-4 w-4 text-indigo-600 dark:text-indigo-400"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="sort" value="{{ $sortField }}">
                <input type="hidden" name="direction" value="{{ $sortDirection }}">
            </form>
        </div>

        <!-- Stories List -->
        <div
            class="bg-white dark:bg-zinc-800/50 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 backdrop-blur-sm overflow-hidden">
            @if ($stories->isEmpty())
                <div class="p-10 text-center">
                    <div
                        class="bg-zinc-100/50 dark:bg-zinc-700/20 p-6 rounded-xl inline-flex flex-col items-center justify-center mb-4">
                        <i data-lucide="file-question"
                            class="h-12 w-12 text-zinc-400 dark:text-zinc-500 mb-4 animate-pulse"></i>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-white">No stories found</h3>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            @if (request('search') || request('project_id') || !empty($selectedSources))
                                Try adjusting your search or filters.
                            @else
                                Get started by creating your first story.
                            @endif
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('dashboard.stories.create') }}"
                                class="btn-primary inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white shadow-lg hover:shadow-indigo-500/30 transition-all duration-200">
                                <i data-lucide="plus" class="h-4 w-4"></i>
                                <span>Create Story</span>
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200/50 dark:divide-zinc-700/50">
                        <thead class="bg-zinc-50/50 dark:bg-zinc-800/30">
                            <tr>
                                @foreach ([
            'title' => 'Title',
            'source' => 'Source',
            'external_id' => 'External ID',
            'updated_at' => 'Last Updated',
        ] as $field => $label)
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery([
                                            'sort' => $field,
                                            'direction' => request('sort') === $field && request('direction') === 'asc' ? 'desc' : 'asc',
                                        ]) }}"
                                            class="group inline-flex items-center hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors duration-200">
                                            {{ $label }}
                                            <span class="ml-1.5 relative">
                                                @if (request('sort') === $field)
                                                    <i data-lucide="{{ request('direction') === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                                        class="h-4 w-4 text-indigo-600 dark:text-indigo-400 animate-spring"></i>
                                                @else
                                                    <i data-lucide="chevrons-up-down"
                                                        class="h-4 w-4 text-zinc-400 dark:text-zinc-500 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></i>
                                                @endif
                                            </span>
                                        </a>
                                    </th>
                                @endforeach
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200/50 dark:divide-zinc-700/50">
                            @foreach ($stories as $story)
                                <tr class="hover:bg-zinc-50/30 dark:hover:bg-zinc-700/20 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                            <a href="{{ route('dashboard.stories.show', $story->id) }}"
                                                class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200 group">
                                                {{ \Illuminate\Support\Str::limit($story->title, 50) }}
                                                <i data-lucide="arrow-up-right"
                                                    class="h-3 w-3 ml-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></i>
                                            </a>
                                        </div>
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                                            {{ \Illuminate\Support\Str::limit($story->description, 70) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="inline-flex items-center space-x-1.5">
                                            @switch($story->source)
                                                @case('jira')
                                                    <i data-lucide="square" class="w-4 h-4 text-blue-500"></i>
                                                @break

                                                @case('github')
                                                    <i data-lucide="github" class="w-4 h-4 text-purple-500"></i>
                                                @break

                                                @case('azure')
                                                    <i data-lucide="microsoft" class="w-4 h-4 text-cyan-500"></i>
                                                @break

                                                @default
                                                    <i data-lucide="file-edit" class="w-4 h-4 text-zinc-500"></i>
                                            @endswitch
                                            <span
                                                class="px-2.5 py-1 text-xs font-medium rounded-full
                                    {{ match ($story->source) {
                                        'jira' => 'bg-blue-100/80 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                        'github' => 'bg-purple-100/80 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                        'azure' => 'bg-cyan-100/80 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300',
                                        'manual' => 'bg-zinc-100/80 text-zinc-800 dark:bg-zinc-700/30 dark:text-zinc-300',
                                        default => 'bg-zinc-100/80 text-zinc-800 dark:bg-zinc-700/30 dark:text-zinc-300',
                                    } }}">
                                                {{ ucfirst($story->source) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400 font-mono">
                                        {{ $story->external_id ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                        <div class="flex items-center">
                                            <i data-lucide="clock"
                                                class="w-4 h-4 mr-1.5 text-zinc-400 dark:text-zinc-500"></i>
                                            <span
                                                class="whitespace-nowrap">{{ $story->updated_at->diffForHumans() }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-3">
                                            <a href="{{ route('dashboard.stories.edit', $story->id) }}"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors duration-200 relative group"
                                                title="Edit">
                                                <i data-lucide="edit" class="h-4 w-4"></i>
                                                <span
                                                    class="absolute -top-5 left-1/2 -translate-x-1/2 px-2 py-1 text-xs bg-zinc-800 text-white rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow-lg">
                                                    Edit
                                                </span>
                                            </a>
                                            <button type="button"
                                                onclick="if(confirm('Are you sure you want to delete this story?')) { document.getElementById('delete-form-{{ $story->id }}').submit(); }"
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors duration-200 relative group"
                                                title="Delete">
                                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                <span
                                                    class="absolute -top-5 left-1/2 -translate-x-1/2 px-2 py-1 text-xs bg-zinc-800 text-white rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow-lg">
                                                    Delete
                                                </span>
                                            </button>
                                            <form id="delete-form-{{ $story->id }}"
                                                action="{{ route('dashboard.stories.destroy', $story->id) }}"
                                                method="POST" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-zinc-200/50 dark:border-zinc-700/50">
                    {{ $stories->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            const sourcePills = document.querySelectorAll('.source-pill');
            const sourcesContainer = document.getElementById('sources-container');
            const form = document.getElementById('filterForm');

            // Get initial selected sources from the hidden inputs rendered by Blade
            let selectedSourcesState = Array.from(sourcesContainer.querySelectorAll('input[name="sources[]"]'))
                .map(input => input.value);

            // Set initial visual state of pills
            updatePillVisuals();

            // Add click listeners to pills
            sourcePills.forEach(pill => {
                pill.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent default button behavior
                    const value = this.getAttribute('data-value');

                    if (value === 'all') {
                        selectedSourcesState = []; // Clear all selections
                    } else {
                        // Toggle the clicked source in the state array
                        const index = selectedSourcesState.indexOf(value);
                        if (index > -1) {
                            // Already selected, remove it
                            selectedSourcesState.splice(index, 1);
                        } else {
                            // Not selected, add it
                            selectedSourcesState.push(value);
                        }
                    }

                    // Update the visual state of ALL pills based on the new state
                    updatePillVisuals();

                    // Update the hidden inputs in the form
                    updateSourceInputsElements();

                    // Submit the form
                    form.submit();
                });
            });

            /**
             * Updates the CSS classes of the source pills based on the selectedSourcesState array.
             */
            function updatePillVisuals() {
                sourcePills.forEach(pill => {
                    const value = pill.getAttribute('data-value');
                    const isActive = (value === 'all' && selectedSourcesState.length === 0) ||
                        // 'All' is active if no specific sources are selected
                        (value !== 'all' && selectedSourcesState.includes(
                        value)); // Specific source is active if it's in the state array

                    if (isActive) {
                        pill.classList.add('bg-indigo-100', 'dark:bg-indigo-900/30', 'border-indigo-300',
                            'dark:border-indigo-700', 'text-indigo-700', 'dark:text-indigo-300');
                        pill.classList.remove('bg-white', 'dark:bg-zinc-800', 'border-zinc-300',
                            'dark:border-zinc-600', 'text-zinc-700', 'dark:text-zinc-300');
                    } else {
                        pill.classList.remove('bg-indigo-100', 'dark:bg-indigo-900/30', 'border-indigo-300',
                            'dark:border-indigo-700', 'text-indigo-700', 'dark:text-indigo-300');
                        pill.classList.add('bg-white', 'dark:bg-zinc-800', 'border-zinc-300',
                            'dark:border-zinc-600', 'text-zinc-700', 'dark:text-zinc-300');
                    }
                });
            }

            /**
             * Updates the hidden input elements in the sourcesContainer based on the selectedSourcesState array.
             */
            function updateSourceInputsElements() {
                // Clear existing inputs
                sourcesContainer.innerHTML = '';

                // Add new inputs for each selected source (only for specific sources)
                selectedSourcesState.forEach(source => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'sources[]';
                    input.value = source;
                    sourcesContainer.appendChild(input);
                });
                // If selectedSourcesState is empty, no 'sources[]' inputs are added,
                // which correctly signals "all" to the backend filter logic.
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        @keyframes spring {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
            }
        }

        .animate-spring {
            animation: spring 0.3s ease-in-out;
        }
    </style>
@endpush
