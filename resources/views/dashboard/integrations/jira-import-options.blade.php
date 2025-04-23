@extends('layouts.dashboard')

@section('title', 'Import from Jira')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.integrations.index') }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors duration-200">Integrations</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Import from Jira</span>
    </li>
@endsection

@section('content')
<div class="page-transition">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white tracking-tight mb-3">Import from Jira</h1>
        <p class="text-zinc-600 dark:text-zinc-400 text-lg leading-6">Import issues from your connected Jira projects to Arxitest</p>
    </div>

    <!-- Import Options Cards -->
    <div class="grid sm:grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- New Project Card -->
        <div class="group relative bg-white dark:bg-zinc-800 rounded-2xl shadow-lg hover:shadow-xl border border-zinc-200/80 dark:border-zinc-700/50 transition-all duration-300 ease-out hover:-translate-y-1">
            <div class="p-6">
                <div class="flex items-center mb-5">
                    <div class="p-3 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-500 shadow-lg transform group-hover:scale-105 transition-transform">
                        <i data-lucide="folder-plus" class="h-6 w-6 text-white"></i>
                    </div>
                    <h3 class="ml-4 text-xl font-semibold text-zinc-900 dark:text-white">Create New Project</h3>
                </div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6 leading-6">
                    Create a fresh Arxitest project from your Jira issues. This will import epics as test suites and stories or other issue types as test cases.
                </p>
                <div class="space-y-3 mb-6">
                    <div class="flex items-center">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 mr-3 shrink-0"></i>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Clean project structure</span>
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 mr-3 shrink-0"></i>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Import epics as test suites</span>
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 mr-3 shrink-0"></i>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Stories as test cases</span>
                    </div>
                </div>
                <a href="{{ route('integrations.jira.import.new') }}" class="block w-full py-3 px-6 rounded-xl text-center font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-500 hover:from-blue-700 hover:to-indigo-600 shadow-sm transition-all duration-200 transform hover:scale-[1.02]">
                    Create New Project
                </a>
            </div>
        </div>

        <!-- Existing Project Card -->
        <div class="group relative bg-white dark:bg-zinc-800 rounded-2xl shadow-lg hover:shadow-xl border border-zinc-200/80 dark:border-zinc-700/50 transition-all duration-300 ease-out hover:-translate-y-1">
            <div class="p-6">
                <div class="flex items-center mb-5">
                    <div class="p-3 rounded-xl bg-gradient-to-br from-purple-600 to-pink-500 shadow-lg transform group-hover:scale-105 transition-transform">
                        <i data-lucide="folder-input" class="h-6 w-6 text-white"></i>
                    </div>
                    <h3 class="ml-4 text-xl font-semibold text-zinc-900 dark:text-white">Use Existing Project</h3>
                </div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6 leading-6">
                    Import Jira issues into an existing Arxitest project. You can add new test suites or augment existing ones.
                </p>
                <div class="space-y-3 mb-6">
                    <div class="flex items-center">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 mr-3 shrink-0"></i>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Augment existing test suites</span>
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 mr-3 shrink-0"></i>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Create test cases from issues</span>
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 mr-3 shrink-0"></i>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Add to existing structure</span>
                    </div>
                </div>

                @if(count($existingProjects) > 0)
                    <form action="{{ route('integrations.jira.import.existing') }}" method="GET">
                        <div class="mb-5">
                            <label for="project-select" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Select Project</label>
                            <select id="project-select" name="project_id" class="js-project-select w-full">
                                @foreach($existingProjects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full py-3 px-6 rounded-xl text-center font-medium text-white bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 shadow-sm transition-all duration-200 transform hover:scale-[1.02]">
                            Continue to Import
                        </button>
                    </form>
                @else
                    <div class="rounded-xl bg-amber-50/80 dark:bg-amber-900/20 p-4 border border-amber-200 dark:border-amber-800/50">
                        <div class="flex items-center">
                            <i data-lucide="alert-triangle" class="w-5 h-5 mr-3 text-amber-600 dark:text-amber-400 shrink-0"></i>
                            <p class="text-sm text-amber-800 dark:text-amber-200">
                                No existing projects available. Create a project first or use the "Create New Project" option.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Team Information -->
    <div class="bg-blue-50/50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800/50">
        <div class="flex items-center text-blue-800 dark:text-blue-200">
            <i data-lucide="info" class="w-5 h-5 mr-3 text-blue-600 dark:text-blue-400 shrink-0"></i>
            <p class="text-sm">Importing into team: <span class="font-semibold">{{ $teamName }}</span></p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();

        // Initialize custom select
        new TomSelect('#project-select', {
            create: false,
            sortField: {
                field: 'text',
                direction: 'asc'
            },
            controlInput: null,
            render: {
                option: function(data, escape) {
                    return `<div class="flex items-center p-2 hover:bg-zinc-100 dark:hover:bg-zinc-700">${escape(data.text)}</div>`;
                },
                item: function(data, escape) {
                    return `<div class="ts-item">${escape(data.text)}</div>`;
                }
            }
        });
    });
</script>
@endpush
