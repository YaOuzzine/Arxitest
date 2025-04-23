@extends('layouts.dashboard')

@section('title', isset($project) ? "{$project->name} - Stories" : "All Stories")

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.stories.indexAll') }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors duration-200">Stories</a>
    </li>
    @if(isset($project))
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.show', $project->id) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors duration-200">{{ $project->name }}</a>
    </li>
    @endif
@endsection

@section('content')
<div class="h-full space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                @if(isset($project))
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
            <a href="{{ route('dashboard.stories.create') }}" class="btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white shadow-lg hover:shadow-indigo-500/30 transition-all duration-200">
                <i data-lucide="plus" class="w-4 h-4"></i> New Story
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-zinc-800/50 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 p-4 backdrop-blur-sm relative z-10">
        <form action="{{ isset($project) ? route('dashboard.projects.stories.index', $project->id) : route('dashboard.stories.indexAll') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Project Filter -->
@if(!isset($project))
<div>
    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Project</label>
    <div x-data="{ open: false, selected: '{{ request('project_id') ? $projects->firstWhere('id', request('project_id'))->name : 'All Projects' }}' }" class="relative">
        <input type="hidden" name="project_id" :value="{{ request('project_id') ?? '' }}">
        <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl border border-zinc-300/80 dark:border-zinc-600 bg-white/50 dark:bg-zinc-800/50 text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors duration-200">
            <span x-text="selected"></span>
            <i data-lucide="chevron-down" class="h-4 w-4 ml-2 transform transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
        </button>

        <div x-show="open" @click.away="open = false" class="dropdown-container"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95">
            <div class="dropdown-menu w-full">
                <div @click="selected = 'All Projects'; $event.target.closest('form').submit(); open = false"
                     class="cursor-pointer px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100/50 dark:hover:bg-zinc-700/30 transition-colors duration-200">
                    All Projects
                </div>
                @foreach($projects as $p)
                <div @click="selected = '{{ $p->name }}'; $event.target.closest('form').submit(); open = false"
                     class="cursor-pointer px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100/50 dark:hover:bg-zinc-700/30 transition-colors duration-200"
                     :class="{ 'bg-indigo-50/50 dark:bg-indigo-900/20': {{ $p->id }} == {{ request('project_id') ?? 'null' }} }">
                    {{ $p->name }}
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- Source Filter -->
<div>
    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Source</label>
    <div x-data="{ open: false, selected: '{{ request('source') ? ucfirst(request('source')) : 'All Sources' }}' }" class="relative">
        <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl border border-zinc-300/80 dark:border-zinc-600 bg-white/50 dark:bg-zinc-800/50 text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors duration-200">
            <span x-text="selected"></span>
            <i data-lucide="chevron-down" class="h-4 w-4 ml-2 transform transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
        </button>

        <div x-show="open" @click.away="open = false" class="dropdown-container"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95">
            <div class="dropdown-menu w-full">
                <div @click="selected = 'All Sources'; $event.target.closest('form').submit(); open = false"
                     class="cursor-pointer px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100/50 dark:hover:bg-zinc-700/30 transition-colors duration-200">
                    All Sources
                </div>
                @foreach(['manual', 'jira', 'github', 'azure'] as $source)
                <div @click="selected = '{{ ucfirst($source) }}'; $event.target.closest('form').submit(); open = false"
                     class="cursor-pointer px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100/50 dark:hover:bg-zinc-700/30 transition-colors duration-200"
                     :class="{ 'bg-indigo-50/50 dark:bg-indigo-900/20': '{{ $source }}' === '{{ request('source') }}' }">
                    {{ ucfirst($source) }}
                </div>
                @endforeach
            </div>
        </div>
        <input type="hidden" name="source" :value="selected.toLowerCase() === 'all sources' ? '' : selected.toLowerCase()">
    </div>
</div>

                <!-- Search -->
                <div class="sm:col-span-2">
                    <label for="search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2 transition-colors duration-200">Search</label>
                    <div class="relative group">
                        <div class="absolute  left-0 pl-3 flex items-center pointer-events-none text-zinc-400 dark:text-zinc-500 transition-colors duration-200">
                            <i data-lucide="search" class="h-4 w-4 group-focus-within:text-indigo-600"></i>
                        </div>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                               class="w-full pl-10 pr-12 py-2.5 rounded-xl border border-zinc-300/80 dark:border-zinc-600 bg-white/50 dark:bg-zinc-800/50 text-zinc-800 dark:text-zinc-200 placeholder-zinc-400 dark:placeholder-zinc-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-all duration-200"
                               placeholder="Search title, description, or ID...">
                        <button type="submit" class="absolute h-full right-0 px-4 flex items-center bg-indigo-600/10 dark:bg-indigo-400/10 border-l border-zinc-300/50 dark:border-zinc-600/50 hover:bg-indigo-600/20 dark:hover:bg-indigo-400/20 transition-colors duration-200 rounded-r-xl">
                            <i data-lucide="arrow-right" class="h-4 w-4 text-indigo-600 dark:text-indigo-400"></i>
                        </button>
                    </div>
                </div>
            </div>

            <input type="hidden" name="sort" value="{{ request('sort', 'updated_at') }}">
            <input type="hidden" name="direction" value="{{ request('direction', 'desc') }}">
        </form>
    </div>

    <!-- Stories List -->
    <div class="bg-white dark:bg-zinc-800/50 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 backdrop-blur-sm overflow-hidden">
        @if($stories->isEmpty())
        <div class="p-10 text-center">
            <div class="bg-zinc-100/50 dark:bg-zinc-700/20 p-6 rounded-xl inline-flex flex-col items-center justify-center mb-4">
                <i data-lucide="file-question" class="h-12 w-12 text-zinc-400 dark:text-zinc-500 mb-4 animate-pulse"></i>
                <h3 class="text-lg font-medium text-zinc-900 dark:text-white">No stories found</h3>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    @if(request('search') || request('project_id') || request('source'))
                        Try adjusting your search or filters.
                    @else
                        Get started by creating your first story.
                    @endif
                </p>
                <div class="mt-6">
                    <a href="{{ route('dashboard.stories.create') }}" class="btn-primary inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white shadow-lg hover:shadow-indigo-500/30 transition-all duration-200">
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
                        @foreach([
                            'title' => 'Title',
                            'source' => 'Source',
                            'external_id' => 'External ID',
                            'updated_at' => 'Last Updated'
                        ] as $field => $label)
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery([
                                'sort' => $field,
                                'direction' => request('sort') === $field && request('direction') === 'asc' ? 'desc' : 'asc'
                            ]) }}" class="group inline-flex items-center hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors duration-200">
                                {{ $label }}
                                <span class="ml-1.5 relative">
                                    @if(request('sort') === $field)
                                    <i data-lucide="{{ request('direction') === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="h-4 w-4 text-indigo-600 dark:text-indigo-400 animate-spring"></i>
                                    @else
                                    <i data-lucide="chevrons-up-down" class="h-4 w-4 text-zinc-400 dark:text-zinc-500 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></i>
                                    @endif
                                </span>
                            </a>
                        </th>
                        @endforeach
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200/50 dark:divide-zinc-700/50">
                    @foreach($stories as $story)
                    <tr class="hover:bg-zinc-50/30 dark:hover:bg-zinc-700/20 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                <a href="{{ route('dashboard.stories.show', $story->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200 group">
                                    {{ \Illuminate\Support\Str::limit($story->title, 50) }}
                                    <i data-lucide="arrow-up-right" class="h-3 w-3 ml-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></i>
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
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full
                                    {{ match($story->source) {
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400 font-mono">
                            {{ $story->external_id ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            <div class="flex items-center">
                                <i data-lucide="clock" class="w-4 h-4 mr-1.5 text-zinc-400 dark:text-zinc-500"></i>
                                <span class="whitespace-nowrap">{{ $story->updated_at->diffForHumans() }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-3">
                                <a href="{{ route('dashboard.stories.edit', $story->id) }}"
                                   class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors duration-200 relative group"
                                   title="Edit">
                                    <i data-lucide="edit" class="h-4 w-4"></i>
                                    <span class="absolute -top-5 left-1/2 -translate-x-1/2 px-2 py-1 text-xs bg-zinc-800 text-white rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow-lg">
                                        Edit
                                    </span>
                                </a>
                                <button type="button"
                                    onclick="if(confirm('Are you sure you want to delete this story?')) { document.getElementById('delete-form-{{ $story->id }}').submit(); }"
                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors duration-200 relative group"
                                    title="Delete">
                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    <span class="absolute -top-5 left-1/2 -translate-x-1/2 px-2 py-1 text-xs bg-zinc-800 text-white rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow-lg">
                                        Delete
                                    </span>
                                </button>
                                <form id="delete-form-{{ $story->id }}" action="{{ route('dashboard.stories.destroy', $story->id) }}" method="POST" class="hidden">
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

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<style>
    .ts-control {
        @apply px-3 py-2 rounded-xl border border-zinc-300/80 dark:border-zinc-600 bg-white/50 dark:bg-zinc-800/50 text-zinc-800 dark:text-zinc-200 placeholder-zinc-400 dark:placeholder-zinc-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-all duration-200;
    }

    .ts-dropdown {
        @apply rounded-xl shadow-lg border border-zinc-200/70 dark:border-zinc-700 overflow-hidden;
    }

    .ts-dropdown .option {
        @apply px-3 py-2 hover:bg-zinc-100/50 dark:hover:bg-zinc-700/50 transition-colors duration-200;
    }

    .ts-dropdown .active {
        @apply bg-indigo-50/50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-300;
    }

    .dark .ts-control {
        @apply bg-zinc-800/50 border-zinc-600 text-zinc-200;
    }

    .dark .ts-dropdown {
        @apply bg-zinc-800 border-zinc-700;
    }

    @keyframes spring {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }

    .animate-spring {
        animation: spring 0.3s ease-in-out;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();

        new TomSelect('#project_id', {
            create: false,
            controlInput: null,
            render: {
                option: function(data, escape) {
                    return `<div class="p-2 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors duration-200">${escape(data.text)}</div>`;
                },
                item: function(data, escape) {
                    return `<div class="ts-item">${escape(data.text)}</div>`;
                }
            }
        });

        new TomSelect('#source', {
            create: false,
            controlInput: null,
            render: {
                option: function(data, escape) {
                    return `<div class="p-2 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors duration-200">${escape(data.text)}</div>`;
                },
                item: function(data, escape) {
                    return `<div class="ts-item">${escape(data.text)}</div>`;
                }
            }
        });
    });
</script>
@endpush
