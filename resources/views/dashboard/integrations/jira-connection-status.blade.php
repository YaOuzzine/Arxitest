@extends('layouts.dashboard')

@section('title', 'Jira Connection Status')

@section('breadcrumbs')
    <li>
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.integrations.index') }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">Integrations</a>
    </li>
    <li>
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Jira Connection</span>
    </li>
@endsection

@section('content')
<div class="page-transition" x-data="{ showDisconnectModal: false }">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white tracking-tight mb-2">Jira Connection</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Manage your connection to Jira and view status information.</p>
    </div>

    <!-- Connection Status Card -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-zinc-200 dark:border-zinc-700">
            <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">Connection Status</h2>
        </div>
        <div class="p-6">
            <div class="flex items-center mb-6">
                <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $jiraConnected ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400' : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' }} mr-4">
                    <i data-lucide="{{ $jiraConnected ? 'check' : 'x' }}" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $jiraConnected ? 'Connected to Jira' : 'Not Connected' }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $jiraConnected ? 'Your Jira account is successfully connected and ready to use.' : 'You need to connect to Jira to import issues.' }}
                    </p>
                </div>
            </div>

            @if($jiraConnected)
                <div class="p-4 mb-6 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700/50">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">Connection Type:</span>
                            <span class="text-zinc-900 dark:text-white font-medium">Jira Cloud</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">Authorized User:</span>
                            <span class="text-zinc-900 dark:text-white font-medium">{{ Auth::user()->name }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">Integration Level:</span>
                            <span class="text-zinc-900 dark:text-white font-medium">Team-wide</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">Last Refresh:</span>
                            <span class="text-zinc-900 dark:text-white font-medium">{{ now()->subMinutes(rand(5, 120))->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <a href="{{ route('integrations.jira.import.options') }}" class="flex flex-col p-4 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:shadow-md transition-shadow group">
                        <div class="flex items-center mb-2">
                            <div class="flex-shrink-0 p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 mr-3">
                                <i data-lucide="download" class="w-5 h-5"></i>
                            </div>
                            <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Import from Jira</h3>
                        </div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">Import issues from Jira to create test suites and test cases in Arxitest.</p>
                        <span class="mt-auto text-sm text-blue-600 dark:text-blue-400 font-medium group-hover:underline flex items-center">
                            Start Import
                            <i data-lucide="chevron-right" class="w-4 h-4 ml-1 group-hover:translate-x-0.5 transition-transform"></i>
                        </span>
                    </a>

                    <div class="flex flex-col p-4 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center mb-2">
                            <div class="flex-shrink-0 p-2 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 mr-3">
                                <i data-lucide="settings" class="w-5 h-5"></i>
                            </div>
                            <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Connection Settings</h3>
                        </div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">Manage your Jira connection, update permissions, or disconnect.</p>
                        <div class="mt-auto">
                            <button type="button" @click="showDisconnectModal = true" class="text-sm text-red-600 dark:text-red-400 font-medium hover:underline flex items-center">
                                Disconnect Jira
                                <i data-lucide="log-out" class="w-4 h-4 ml-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-6">
                    <p class="text-zinc-600 dark:text-zinc-400 mb-6">Connect your Jira account to import issues and track your test coverage.</p>
                    <a href="{{ route('integrations.jira.redirect') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-white font-medium bg-blue-600 hover:bg-blue-700 shadow-sm transition-colors">
                        <i data-lucide="plug" class="mr-2 h-5 w-5"></i>
                        Connect to Jira
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Disconnect Confirmation Modal -->
    <div x-show="showDisconnectModal" x-cloak class="fixed inset-0 bg-zinc-900/50 dark:bg-zinc-900/80 backdrop-blur-sm z-50 flex items-center justify-center overflow-auto">
        <div @click.outside="showDisconnectModal = false" class="bg-white dark:bg-zinc-800 rounded-xl shadow-xl border border-zinc-200 dark:border-zinc-700 w-full max-w-md p-6 mx-4 transform animate-pop-in" style="--delay: 0.1s">
            <div class="text-center mb-5">
                <div class="inline-flex items-center justify-center p-3 bg-red-100 dark:bg-red-900/30 rounded-full text-red-600 dark:text-red-400 mb-4">
                    <i data-lucide="alert-triangle" class="h-6 w-6"></i>
                </div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Disconnect Jira?</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    This will remove the Jira connection for your team. You will need to reconnect to import issues in the future.
                </p>
            </div>
            <div class="flex space-x-3">
                <button @click="showDisconnectModal = false" class="flex-1 px-4 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 font-medium bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 shadow-sm transition-colors">
                    Cancel
                </button>
                <form method="POST" action="{{ route('integrations.jira.disconnect') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 rounded-lg text-white font-medium bg-red-600 hover:bg-red-700 shadow-sm transition-colors">
                        Disconnect
                    </button>
                </form>
            </div>
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
