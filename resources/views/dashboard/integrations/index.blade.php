@extends('layouts.dashboard')

@section('title', 'Integrations')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Integrations</span>
    </li>
@endsection

@section('content')
<div class="h-full">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Connect Your Tools</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Integrate Arxitest with your favorite development and project management tools.
            </p>
        </div>
    </div>

    <!-- Available Integrations -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <!-- Jira Card -->
        <div class="integration-card bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden transition-all duration-300 hover:shadow-xl hover:scale-[1.02]">
            <div class="p-6">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="p-3 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                        {{-- Simple placeholder - replace with actual Jira SVG/Icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                           <path d="M11.09 1.78C12.08.79 13.6 1.27 14 2.59l.24 1.22a10.13 10.13 0 017.76 7.76l1.22.24c1.32.39 1.8 1.91.81 2.9l-.78.79a10.13 10.13 0 010 10.84l.78.79c.99.99.51 2.51-.8 2.9l-1.22.24a10.13 10.13 0 01-7.76 7.76l-.24 1.22c-.39 1.32-1.91 1.8-2.9.81l-.79-.78a10.13 10.13 0 01-10.84 0l-.79.78c-.99.99-2.51.51-2.9-.8l-.24-1.22A10.13 10.13 0 011 12.24l-1.22-.24c-1.32-.39-1.8-1.91-.81-2.9l.78-.79a10.13 10.13 0 010-10.84l-.78-.79C-.5 5.69 0 4.17 1.32 3.78l1.22-.24a10.13 10.13 0 017.76-7.76l.24-1.22z"></path>
                           <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Jira Software</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Issue Tracking & Project Management</p>
                    </div>
                </div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-5">
                    Connect Jira to automatically generate test cases from user stories and synchronize test results. Requires Atlassian account authorization.
                </p>
            </div>
            <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                @if($jiraConnected)
                    <span class="text-sm font-medium text-green-600 dark:text-green-400 flex items-center">
                        <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Connected
                    </span>
                    <div class="flex space-x-2">
                         {{-- Link to import options, pass current project ID --}}
                        <a href="{{ route('integrations.jira.import.options', ['project_id' => $currentProjectId ?? request('project_id')]) }}" {{-- Adjust how you get current project ID --}}
                           class="btn-secondary text-xs px-3 py-1.5">
                           Import Project
                        </a>
                        <form action="{{ route('integrations.jira.disconnect') }}" method="POST" onsubmit="return confirm('Are you sure you want to disconnect Jira?');">
                            @csrf
                            <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium bg-transparent border-none p-0">
                                Disconnect
                            </button>
                        </form>
                    </div>
                @else
                    <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Not Connected</span>
                    <a href="{{ route('integrations.jira.redirect') }}" class="btn-secondary text-xs px-3 py-1.5">
                        Connect
                    </a>
                @endif
            </div>
        </div>

        <!-- GitHub Card -->
        <div class="integration-card bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden transition-all duration-300 hover:shadow-xl hover:scale-[1.02]">
            <div class="p-6">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="p-3 rounded-lg bg-zinc-100 dark:bg-zinc-900/30">
                         <i data-lucide="github" class="h-8 w-8 text-zinc-800 dark:text-zinc-300"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">GitHub</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Version Control & Collaboration</p>
                    </div>
                </div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-5">
                    Link your GitHub repositories to manage test scripts, track changes, and potentially trigger test runs on commits (coming soon).
                </p>
            </div>
            <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                @if($githubConnected)
                    <span class="text-sm font-medium text-green-600 dark:text-green-400 flex items-center">
                         <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Connected
                    </span>
                    <button type="button" class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium">
                        Disconnect
                    </button>
                @else
                    <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Not Connected</span>
                     {{-- <a href="{{ route('integrations.github.redirect') }}" class="btn-secondary text-xs px-3 py-1.5"> --}}
                    <a href="#" class="btn-secondary text-xs px-3 py-1.5 opacity-50 cursor-not-allowed" title="Coming Soon">
                        Connect
                    </a>
                @endif
            </div>
        </div>

        <!-- Placeholder for more integrations -->
        <div class="integration-card bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border-2 border-dashed border-zinc-300 dark:border-zinc-700 flex flex-col items-center justify-center p-6 text-center transition-colors duration-300 hover:border-zinc-400 dark:hover:border-zinc-600">
             <div class="p-3 rounded-lg bg-zinc-100 dark:bg-zinc-700 mb-4">
                <i data-lucide="puzzle" class="h-8 w-8 text-zinc-500 dark:text-zinc-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-zinc-700 dark:text-zinc-300">More Integrations</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-2">
                GitLab, Slack, Jenkins, and more coming soon!
            </p>
             <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-4">Let us know what you'd like to see next.</p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .integration-card {
        min-height: 250px; /* Adjust as needed */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
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

        // Example: Add subtle animations to cards on load
        const cards = document.querySelectorAll('.integration-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    });
</script>
@endpush
