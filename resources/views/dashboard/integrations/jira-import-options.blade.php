@extends('layouts.dashboard')

@section('title', 'Import from Jira')

@section('breadcrumbs')
    <li>
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.integrations.index') }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">Integrations</a>
    </li>
    <li>
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Import from Jira</span>
    </li>
@endsection

@section('content')
<div class="page-transition">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white tracking-tight mb-2">Import from Jira</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Import issues from your connected Jira projects to Arxitest.</p>
    </div>

    <!-- Import Options Cards -->
    <div class="grid sm:grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- New Project Card -->
        <div class="card-hover bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="p-3 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-500 shadow-lg">
                        <i data-lucide="folder-plus" class="h-6 w-6 text-white"></i>
                    </div>
                    <h3 class="ml-4 text-xl font-semibold text-zinc-900 dark:text-white">Create New Project</h3>
                </div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">
                    Create a fresh Arxitest project from your Jira issues. This will import epics as test suites and stories or other issue types as test cases.
                </p>
                <div class="space-y-2 mb-6">
                    <div class="flex items-center">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mr-2"></i>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Clean project structure</span>
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mr-2"></i>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Import epics as test suites</span>
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mr-2"></i>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Import stories and other issues as test cases</span>
                    </div>
                </div>
                <a href="{{ route('integrations.jira.import.new') }}" class="block w-full py-2 px-4 rounded-lg text-center text-white font-medium bg-gradient-to-r from-blue-600 to-indigo-500 hover:from-blue-700 hover:to-indigo-600 shadow-sm transition-all duration-200">
                    Create New Project
                </a>
            </div>
        </div>

        <!-- Existing Project Card -->
        <div class="card-hover bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="p-3 rounded-xl bg-gradient-to-br from-purple-600 to-pink-500 shadow-lg">
                        <i data-lucide="folder-input" class="h-6 w-6 text-white"></i>
                    </div>
                    <h3 class="ml-4 text-xl font-semibold text-zinc-900 dark:text-white">Use Existing Project</h3>
                </div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">
                    Import Jira issues into an existing Arxitest project. You can add new test suites or augment existing ones.
                </p>
                <div class="space-y-2 mb-6">
                    <div class="flex items-center">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mr-2"></i>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Augment existing test suites</span>
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mr-2"></i>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Create test cases from Jira issues</span>
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mr-2"></i>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Add to your existing test structure</span>
                    </div>
                </div>

                @if(count($existingProjects) > 0)
                    <form action="{{ route('integrations.jira.import.existing') }}" method="GET">
                        <div class="mb-4">
                            <label for="project_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Select Project</label>
                            <select id="project_id" name="project_id" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($existingProjects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="block w-full py-2 px-4 rounded-lg text-center text-white font-medium bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 shadow-sm transition-all duration-200">
                            Continue
                        </button>
                    </form>
                @else
                    <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 p-4 border border-yellow-200 dark:border-yellow-800/50">
                        <p class="text-sm text-yellow-800 dark:text-yellow-300">
                            No existing projects available. Create a project first or use the "Create New Project" option.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Team Information -->
    <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl p-4 border border-zinc-200 dark:border-zinc-700/50">
        <div class="flex items-center text-zinc-600 dark:text-zinc-400">
            <i data-lucide="info" class="w-5 h-5 mr-2 text-blue-500 dark:text-blue-400"></i>
            <p class="text-sm">Importing into team: <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $teamName }}</span></p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
    });
</script>
@endpush
