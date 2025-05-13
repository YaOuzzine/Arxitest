<!-- resources/views/dashboard/integrations/jira-dashboard.blade.php -->
@extends('layouts.dashboard')

@section('title', 'Jira Integration')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.integrations.index') }}" class="text-zinc-700 dark:text-zinc-300 hover:text-indigo-600 dark:hover:text-indigo-400">Integrations</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Jira</span>
    </li>
@endsection

@section('content')
    <div class="container mx-auto py-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white tracking-tight">Jira Integration</h1>
                <p class="text-zinc-600 dark:text-zinc-400 text-lg">
                    Sync your Arxitest projects with Jira
                </p>
            </div>
            <div class="flex items-center space-x-2">
                @if ($jiraConnected)
                    <div class="flex items-center space-x-2">
                        <div class="animate-pulse w-3 h-3 rounded-full bg-green-500"></div>
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">Connected</span>
                    </div>
                    <form action="{{ route('dashboard.integrations.jira.disconnect') }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to disconnect Jira?');">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 ml-4">
                            Disconnect
                        </button>
                    </form>
                @else
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">Not connected</span>
                    </div>
                    <a href="{{ route('dashboard.integrations.jira.redirect') }}"
                        class="btn-primary px-4 py-1.5 text-sm flex items-center space-x-2 transition-transform hover:scale-105">
                        <i data-lucide="plug" class="w-4 h-4"></i>
                        <span>Connect to Jira</span>
                    </a>
                @endif
            </div>
        </div>

        @if ($jiraConnected)
            <!-- Tabs -->
            <div class="border-b border-zinc-200 dark:border-zinc-700 mb-8">
                <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                    <button class="tab-link active" data-target="dashboard-section">
                        Dashboard
                    </button>
                    <button class="tab-link" data-target="project-config-section">
                        Project Configuration
                    </button>
                    <button class="tab-link" data-target="import-section">
                        Import from Jira
                    </button>
                    <button class="tab-link" data-target="sync-section">
                        Synchronization
                    </button>
                    <button class="tab-link" data-target="history-section">
                        History
                    </button>
                </nav>
            </div>

            <!-- Dashboard Section -->
            <div id="dashboard-section" class="tab-content active">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Status Card -->
                    <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Integration Status</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-600 dark:text-zinc-400">Status:</span>
                                <span class="text-green-600 dark:text-green-400 font-medium">Connected</span>
                            </div>
                            @if ($lastSync)
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">Last Sync:</span>
                                    <span class="text-zinc-800 dark:text-zinc-200">{{ \Carbon\Carbon::parse($lastSync['completed_at'])->diffForHumans() }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">Sync Direction:</span>
                                    <span class="text-zinc-800 dark:text-zinc-200">{{ ucfirst($lastSync['direction']) }}</span>
                                </div>
                            @else
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">Last Sync:</span>
                                    <span class="text-zinc-800 dark:text-zinc-200">Never</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions Card -->
                    <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <button id="start-sync-btn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-3 rounded-lg flex items-center justify-center">
                                <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                                Sync Now
                            </button>
                            <button id="quick-import-btn" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-3 rounded-lg flex items-center justify-center">
                                <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                                Quick Import
                            </button>
                        </div>
                    </div>

                    <!-- Statistics Card -->
                    <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Statistics</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-600 dark:text-zinc-400">Linked Projects:</span>
                                <span class="text-zinc-800 dark:text-zinc-200">{{ count($existingProjects) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-600 dark:text-zinc-400">Available Jira Projects:</span>
                                <span class="text-zinc-800 dark:text-zinc-200">{{ count($jiraProjects) }}</span>
                            </div>
                            @if ($lastSync)
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">Last Sync Items:</span>
                                    <span class="text-zinc-800 dark:text-zinc-200">
                                        @if (isset($lastSync['pull_results']))
                                            {{ $lastSync['pull_results']['success'] ?? 0 }} pulled,
                                        @endif
                                        @if (isset($lastSync['push_results']))
                                            {{ $lastSync['push_results']['success'] ?? 0 }} pushed
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Timeline -->
                <div class="mt-8 bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Recent Activity</h3>

                    @if (count($syncHistory) > 0)
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                @foreach($syncHistory as $index => $sync)
                                    <li>
                                        <div class="relative pb-8">
                                            @if ($index < count($syncHistory) - 1)
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-zinc-200 dark:bg-zinc-700" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full flex items-center justify-center bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400">
                                                        @if ($sync['direction'] === 'pull')
                                                            <i data-lucide="download" class="h-4 w-4"></i>
                                                        @elseif ($sync['direction'] === 'push')
                                                            <i data-lucide="upload" class="h-4 w-4"></i>
                                                        @else
                                                            <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div>
                                                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                            {{ ucfirst($sync['direction']) }} Synchronization
                                                        </div>
                                                        <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                                                            {{ \Carbon\Carbon::parse($sync['completed_at'])->diffForHumans() }}
                                                        </p>
                                                    </div>
                                                    <div class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">
                                                        <p>
                                                            @if (isset($sync['pull_results']) && $sync['pull_results'])
                                                                <span class="text-zinc-600 dark:text-zinc-400">Pulled:</span>
                                                                <span class="text-green-600 dark:text-green-400">{{ $sync['pull_results']['success'] ?? 0 }} successful</span>,
                                                                <span class="text-red-600 dark:text-red-400">{{ $sync['pull_results']['failed'] ?? 0 }} failed</span>
                                                                <br>
                                                            @endif
                                                            @if (isset($sync['push_results']) && $sync['push_results'])
                                                                <span class="text-zinc-600 dark:text-zinc-400">Pushed:</span>
                                                                <span class="text-green-600 dark:text-green-400">{{ $sync['push_results']['success'] ?? 0 }} successful</span>,
                                                                <span class="text-red-600 dark:text-red-400">{{ $sync['push_results']['failed'] ?? 0 }} failed</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                                <div>
                                                    <button class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-200 text-sm view-sync-details" data-sync-id="{{ $index }}">
                                                        View details
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                            <i data-lucide="history" class="h-12 w-12 mx-auto mb-3 text-zinc-300 dark:text-zinc-600"></i>
                            <p>No sync history available yet</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Project Configuration Section -->
            <div id="project-config-section" class="tab-content hidden">
                <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Project Configuration</h3>

                    <form id="jira-project-config-form" class="space-y-6">
                        <div>
                            <label for="project-select" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Arxitest Project</label>
                            <select id="project-select" name="project_id" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 text-zinc-900 dark:text-zinc-100 shadow-sm">
                                <option value="">Select a project</option>
                                @foreach($existingProjects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Select the Arxitest project to configure.</p>
                        </div>

                        <div>
                            <label for="jira-project-select" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Jira Project</label>
                            <select id="jira-project-select" name="jira_project_key" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 text-zinc-900 dark:text-zinc-100 shadow-sm">
                                <option value="">Select a Jira project</option>
                                @foreach($jiraProjects as $project)
                                    <option value="{{ $project['key'] }}">{{ $project['name'] }} ({{ $project['key'] }})</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Select the Jira project to link with.</p>
                        </div>

                        <div class="border-t border-b border-zinc-200 dark:border-zinc-700 py-4 my-4">
                            <h4 class="text-md font-medium text-zinc-800 dark:text-zinc-200 mb-3">Sync Settings</h4>

                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="auto-sync" name="sync_settings[auto_sync]" type="checkbox" class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="auto-sync" class="font-medium text-zinc-700 dark:text-zinc-300">Enable Automatic Sync</label>
                                        <p class="text-zinc-500 dark:text-zinc-400">Automatically synchronize changes between Arxitest and Jira.</p>
                                    </div>
                                </div>

                                <div class="pl-7">
                                    <label for="sync-interval" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Sync Interval (minutes)</label>
                                    <input type="number" id="sync-interval" name="sync_settings[sync_interval]" min="15" value="60" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 text-zinc-900 dark:text-zinc-100 shadow-sm">
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">How often to check for changes (minimum 15 minutes).</p>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="sync-test-cases" name="sync_settings[sync_test_cases]" type="checkbox" class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="sync-test-cases" class="font-medium text-zinc-700 dark:text-zinc-300">Sync Test Cases</label>
                                        <p class="text-zinc-500 dark:text-zinc-400">Include test cases in synchronization (as subtasks in Jira).</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="sync-comments" name="sync_settings[sync_comments]" type="checkbox" class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="sync-comments" class="font-medium text-zinc-700 dark:text-zinc-300">Sync Comments</label>
                                        <p class="text-zinc-500 dark:text-zinc-400">Synchronize comments between Arxitest and Jira.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
                                Save Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Import Section -->
            <div id="import-section" class="tab-content hidden">
                <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 mb-6">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Import from Jira</h3>

                    <form id="jira-import-form" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="import-jira-project" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Jira Project</label>
                                <select id="import-jira-project" name="jira_project_key" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 text-zinc-900 dark:text-zinc-100 shadow-sm">
                                    <option value="">Select a Jira project</option>
                                    @foreach($jiraProjects as $project)
                                        <option value="{{ $project['key'] }}">{{ $project['name'] }} ({{ $project['key'] }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="create-project-toggle" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Import Destination</label>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="flex items-center">
                                        <input type="radio" id="use-existing-project" name="create_new_project" value="0" class="h-4 w-4 border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                        <label for="use-existing-project" class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">Existing Project</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" id="create-new-project" name="create_new_project" value="1" class="h-4 w-4 border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500" checked>
                                        <label for="create-new-project" class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">New Project</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="existing-project-section" class="hidden">
                            <label for="import-arxitest-project" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Arxitest Project</label>
                            <select id="import-arxitest-project" name="arxitest_project_id" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 text-zinc-900 dark:text-zinc-100 shadow-sm">
                                <option value="">Select a project</option>
                                @foreach($existingProjects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="new-project-section">
                            <label for="new-project-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">New Project Name</label>
                            <input type="text" id="new-project-name" name="new_project_name" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 text-zinc-900 dark:text-zinc-100 shadow-sm">
                        </div>

                        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                            <h4 class="text-md font-medium text-zinc-800 dark:text-zinc-200 mb-3">Import Options</h4>

                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="import-epics" name="import_epics" type="checkbox" class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500" checked>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="import-epics" class="font-medium text-zinc-700 dark:text-zinc-300">Import Epics</label>
                                        <p class="text-zinc-500 dark:text-zinc-400">Import epics as test suites.</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="import-stories" name="import_stories" type="checkbox" class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500" checked>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="import-stories" class="font-medium text-zinc-700 dark:text-zinc-300">Import Stories, Tasks & Bugs</label>
                                        <p class="text-zinc-500 dark:text-zinc-400">Import stories, tasks and bugs as Arxitest stories.</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="generate-test-scripts" name="generate_test_scripts" type="checkbox" class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="generate-test-scripts" class="font-medium text-zinc-700 dark:text-zinc-300">Generate Test Scripts</label>
                                        <p class="text-zinc-500 dark:text-zinc-400">Auto-generate test scripts for imported stories.</p>
                                    </div>
                                </div>

                                <div>
                                    <label for="jql-filter" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">JQL Filter (Optional)</label>
                                    <input type="text" id="jql-filter" name="jql_filter" placeholder="e.g. status = 'In Progress'" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 text-zinc-900 dark:text-zinc-100 shadow-sm">
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Filter issues to import with JQL.</p>
                                </div>

                                <div>
                                    <label for="max-issues" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Max Issues to Import</label>
                                    <input type="number" id="max-issues" name="max_issues" placeholder="50" min="1" max="500" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 text-zinc-900 dark:text-zinc-100 shadow-sm">
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Limit the number of issues to import (max 500).</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" id="categorize-before-import" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
                                Categorize & Import
                            </button>
                            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                                Import Now
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Issue Preview Section (hidden by default) -->
                <div id="issue-preview-section" class="hidden bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Issue Categorization</h3>
                        <span id="preview-count" class="text-sm px-2.5 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
                            0 issues
                        </span>
                    </div>

                    <div class="mb-4 flex justify-between items-center">
                        <div class="text-sm text-zinc-600 dark:text-zinc-400">
                            Customize how Jira issues are imported into Arxitest.
                        </div>
                        <div class="space-x-2">
                            <button id="select-all-issues" class="text-xs px-2 py-1 rounded bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50">
                                Select All
                            </button>
                            <button id="deselect-all-issues" class="text-xs px-2 py-1 rounded bg-zinc-50 dark:bg-zinc-900/30 text-zinc-700 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900/50">
                                Deselect All
                            </button>
                        </div>
                    </div>

                    <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                        <div class="flex justify-between mb-3">
                            <div class="flex space-x-4">
                                <button data-filter="all" class="issue-filter active text-sm font-medium">
                                    All
                                </button>
                                <button data-filter="epic" class="issue-filter text-sm">
                                    Epics
                                </button>
                                <button data-filter="story" class="issue-filter text-sm">
                                    Stories
                                </button>
                                <button data-filter="task" class="issue-filter text-sm">
                                    Tasks
                                </button>
                                <button data-filter="bug" class="issue-filter text-sm">
                                    Bugs
                                </button>
                            </div>
                            <div>
                                <input type="text" id="issue-search" placeholder="Search issues..." class="text-sm px-3 py-1 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            </div>
                        </div>

                        <!-- Issues Table -->
                        <div class="overflow-x-auto shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider w-10">
                                            <input type="checkbox" id="select-all-checkbox" class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                        </th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Key
                                        </th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Summary
                                        </th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Import As
                                        </th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider w-20">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="issues-table-body" class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    <!-- Issues will be populated here -->
                                    <tr>
                                        <td colspan="7" class="p-4 text-center text-zinc-500 dark:text-zinc-400">
                                            <div class="py-6">
                                                <i data-lucide="loader" class="h-8 w-8 mx-auto mb-3 animate-spin text-indigo-500"></i>
                                                <p>Loading issues...</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <button id="process-selected-btn" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                                Import Selected Issues
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Synchronization Section -->
            <div id="sync-section" class="tab-content hidden">
                <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Synchronization Settings</h3>

                    <form id="jira-sync-form" class="space-y-6">
                        <div>
                            <label for="sync-project-select" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Project to Sync</label>
                            <select id="sync-project-select" name="project_id" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 text-zinc-900 dark:text-zinc-100 shadow-sm">
                                <option value="">Select a project</option>
                                @foreach($existingProjects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Sync Direction</label>
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center">
                                    <input type="radio" id="sync-direction-pull" name="direction" value="pull" class="h-4 w-4 border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                    <label for="sync-direction-pull" class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">Pull from Jira</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" id="sync-direction-push" name="direction" value="push" class="h-4 w-4 border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                    <label for="sync-direction-push" class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">Push to Jira</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" id="sync-direction-both" name="direction" value="both" class="h-4 w-4 border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500" checked>
                                    <label for="sync-direction-both" class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">Bidirectional</label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Entity Types</label>
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <input type="checkbox" id="sync-entity-stories" name="entity_types[]" value="story" class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500" checked>
                                    <label for="sync-entity-stories" class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">Stories</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="sync-entity-test-cases" name="entity_types[]" value="test_case" class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                    <label for="sync-entity-test-cases" class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">Test Cases</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="sync-entity-test-suites" name="entity_types[]" value="test_suite" class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                    <label for="sync-entity-test-suites" class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">Test Suites</label>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                            <h4 class="text-md font-medium text-zinc-800 dark:text-zinc-200 mb-3">Advanced Options</h4>

                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="sync-comments-option" name="sync_options[sync_comments]" type="checkbox" class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="sync-comments-option" class="font-medium text-zinc-700 dark:text-zinc-300">Sync Comments</label>
                                        <p class="text-zinc-500 dark:text-zinc-400">Synchronize comments between systems.</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="sync-attachments" name="sync_options[sync_attachments]" type="checkbox" class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="sync-attachments" class="font-medium text-zinc-700 dark:text-zinc-300">Sync Attachments</label>
                                        <p class="text-zinc-500 dark:text-zinc-400">Include file attachments in synchronization.</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="dry-run" name="sync_options[dry_run]" type="checkbox" class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="dry-run" class="font-medium text-zinc-700 dark:text-zinc-300">Dry Run</label>
                                        <p class="text-zinc-500 dark:text-zinc-400">Simulate sync without making changes.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
                                Start Synchronization
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Sync Progress Section (initially hidden) -->
                <div id="sync-progress-section" class="hidden mt-6 bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Sync Progress</h3>

                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span id="progress-message" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Initializing...</span>
                                <span id="progress-percentage" class="text-sm text-zinc-500 dark:text-zinc-400">0%</span>
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2.5">
                                <div id="progress-bar" class="bg-indigo-600 h-2.5 rounded-full" style="width: 0%"></div>
                            </div>
                        </div>

                        <div id="sync-stats" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-indigo-50 dark:bg-indigo-900/30
