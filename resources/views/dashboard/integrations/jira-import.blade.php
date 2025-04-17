@extends('layouts.dashboard')

@section('title', 'Import from Jira')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.integrations.index') }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">Integrations</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Import Jira Project</span>
    </li>
@endsection

@section('content')
<div class="h-full" x-data="{ isLoading: false }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Select Jira Project to Import</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Choose a Jira project to import its Epics and Stories into your Arxitest project.
            </p>
        </div>
        <div>
            <a href="{{ route('dashboard.integrations.index') }}" class="btn-secondary">
                 <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Integrations
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
         <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
             <h3 class="text-lg font-semibold text-zinc-800 dark:text-white">Available Jira Projects</h3>
             <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Select a project below to import its structure.</p>
         </div>

        <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
            @forelse ($jiraProjects as $project)
                <li class="px-6 py-4 flex items-center justify-between hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors duration-150">
                    <div class="flex items-center space-x-4">
                         <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                            {{-- Basic Jira-like icon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.414-1.414L11 10.586V6z" />
                            </svg>
                         </div>
                        <div>
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $project['name'] }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Key: {{ $project['key'] }}</p>
                        </div>
                    </div>
                    <form action="{{ route('integrations.jira.import.project') }}" method="POST" @submit="isLoading = true">
                        @csrf
                        <input type="hidden" name="jira_project_key" value="{{ $project['key'] }}">
                        <input type="hidden" name="jira_project_name" value="{{ $project['name'] }}">
                        <input type="hidden" name="arxitest_project_id" value="{{ $arxitestProjectId }}"> {{-- Pass target project ID --}}
                        <button type="submit"
                                class="btn-primary text-xs px-3 py-1.5 flex items-center disabled:opacity-50"
                                :disabled="isLoading">
                             <i data-lucide="download-cloud" class="w-4 h-4 mr-1"></i>
                            <span x-show="!isLoading">Import</span>
                             <span x-show="isLoading" x-cloak>Importing...</span>
                        </button>
                    </form>
                </li>
            @empty
                <li class="px-6 py-10 text-center text-zinc-500 dark:text-zinc-400">
                    No accessible Jira projects found, or there was an error fetching them.
                </li>
            @endforelse
        </ul>
    </div>
</div>
@endsection

@push('styles')
 <style>
     .btn-primary {
         @apply inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200;
     }
      .btn-secondary {
         @apply inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200;
     }
 </style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        lucide.createIcons();
    });
</script>
@endpush
