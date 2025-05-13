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
                            <div class="bg-indigo-50 dark:bg-indigo-900/30 rounded-lg p-4 text-center">
                                <div class="text-indigo-700 dark:text-indigo-300 font-semibold text-3xl mb-1">
                                    <span id="stat-success">0</span>
                                </div>
                                <div class="text-zinc-600 dark:text-zinc-400 text-sm">
                                    Successful Items
                                </div>
                            </div>
                            <div class="bg-red-50 dark:bg-red-900/30 rounded-lg p-4 text-center">
                                <div class="text-red-700 dark:text-red-300 font-semibold text-3xl mb-1">
                                    <span id="stat-failed">0</span>
                                </div>
                                <div class="text-zinc-600 dark:text-zinc-400 text-sm">
                                    Failed Items
                                </div>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/30 rounded-lg p-4 text-center">
                                <div class="text-green-700 dark:text-green-300 font-semibold text-3xl mb-1">
                                    <span id="stat-created">0</span>
                                </div>
                                <div class="text-zinc-600 dark:text-zinc-400 text-sm">
                                    Created Items
                                </div>
                            </div>
                            <div class="bg-yellow-50 dark:bg-yellow-900/30 rounded-lg p-4 text-center">
                                <div class="text-yellow-700 dark:text-yellow-300 font-semibold text-3xl mb-1">
                                    <span id="stat-updated">0</span>
                                </div>
                                <div class="text-zinc-600 dark:text-zinc-400 text-sm">
                                    Updated Items
                                </div>
                            </div>
                        </div>

                        <div id="sync-details" class="mt-6 border-t border-zinc-200 dark:border-zinc-700 pt-4 hidden">
                            <h4 class="text-md font-medium text-zinc-800 dark:text-zinc-200 mb-3">Details</h4>
                            <div class="space-y-4">
                                <div id="sync-log" class="bg-zinc-50 dark:bg-zinc-900/50 p-4 rounded-lg h-60 overflow-y-auto text-sm text-zinc-700 dark:text-zinc-300 font-mono">
                                    <!-- Logs will be inserted here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Section -->
            <div id="history-section" class="tab-content hidden">
                <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Sync History</h3>

                    @if (count($syncHistory) > 0)
                        <div class="flow-root">
                            <ul role="list" class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($syncHistory as $index => $sync)
                                    <li class="py-4">
                                        <div class="flex items-start space-x-4">
                                            <div class="flex-shrink-0">
                                                <span class="h-10 w-10 rounded-full flex items-center justify-center bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400">
                                                    @if ($sync['direction'] === 'pull')
                                                        <i data-lucide="download" class="h-5 w-5"></i>
                                                    @elseif ($sync['direction'] === 'push')
                                                        <i data-lucide="upload" class="h-5 w-5"></i>
                                                    @else
                                                        <i data-lucide="refresh-cw" class="h-5 w-5"></i>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ ucfirst($sync['direction']) }} Synchronization
                                                </p>
                                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ \Carbon\Carbon::parse($sync['completed_at'])->format('M d, Y h:i A') }}
                                                </p>
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    @foreach($sync['entity_types'] ?? [] as $type)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/50 text-indigo-800 dark:text-indigo-200">
                                                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0 self-center">
                                                <button type="button" class="history-details-toggle text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-200" data-index="{{ $index }}">
                                                    Show details
                                                </button>
                                            </div>
                                        </div>
                                        <div id="history-details-{{ $index }}" class="mt-3 ml-14 hidden">
                                            <div class="bg-zinc-50 dark:bg-zinc-900/50 p-4 rounded-lg text-sm">
                                                @if (isset($sync['pull_results']))
                                                    <div class="mb-2">
                                                        <h5 class="font-medium text-zinc-800 dark:text-zinc-200">Pull Results:</h5>
                                                        <p>
                                                            <span class="text-green-600 dark:text-green-400">{{ $sync['pull_results']['success'] ?? 0 }} successful</span>,
                                                            <span class="text-red-600 dark:text-red-400">{{ $sync['pull_results']['failed'] ?? 0 }} failed</span>,
                                                            <span class="text-green-600 dark:text-green-400">{{ $sync['pull_results']['created'] ?? 0 }} created</span>,
                                                            <span class="text-blue-600 dark:text-blue-400">{{ $sync['pull_results']['updated'] ?? 0 }} updated</span>,
                                                            <span class="text-zinc-500 dark:text-zinc-400">{{ $sync['pull_results']['skipped'] ?? 0 }} skipped</span>
                                                        </p>
                                                    </div>
                                                @endif
                                                @if (isset($sync['push_results']))
                                                    <div>
                                                        <h5 class="font-medium text-zinc-800 dark:text-zinc-200">Push Results:</h5>
                                                        <p>
                                                            <span class="text-green-600 dark:text-green-400">{{ $sync['push_results']['success'] ?? 0 }} successful</span>,
                                                            <span class="text-red-600 dark:text-red-400">{{ $sync['push_results']['failed'] ?? 0 }} failed</span>,
                                                            <span class="text-green-600 dark:text-green-400">{{ $sync['push_results']['created'] ?? 0 }} created</span>,
                                                            <span class="text-blue-600 dark:text-blue-400">{{ $sync['push_results']['updated'] ?? 0 }} updated</span>,
                                                            <span class="text-zinc-500 dark:text-zinc-400">{{ $sync['push_results']['skipped'] ?? 0 }} skipped</span>
                                                        </p>
                                                    </div>
                                                @endif
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
        @else
            <!-- Not Connected View -->
            <div class="bg-white dark:bg-zinc-800 p-8 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 text-center">
                <div class="flex flex-col items-center justify-center py-8">
                    <svg class="h-16 w-16 text-zinc-400 dark:text-zinc-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    <h2 class="text-xl font-bold text-zinc-800 dark:text-zinc-200 mb-2">Connect to Jira</h2>
                    <p class="text-zinc-600 dark:text-zinc-400 max-w-md mb-6">
                        Link your Jira account to enable two-way synchronization between Arxitest and Jira. Import stories, track test results, and streamline your workflow.
                    </p>
                    <a href="{{ route('dashboard.integrations.jira.redirect') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg flex items-center">
                        <i data-lucide="plug" class="w-5 h-5 mr-2"></i>
                        Connect to Jira
                    </a>
                </div>
            </div>
        @endif

        <!-- Sync Details Modal -->
        <div id="sync-details-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-zinc-900 dark:bg-opacity-80 transition-opacity" aria-hidden="true"></div>

                <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-zinc-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 dark:bg-indigo-900 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-zinc-100" id="modal-title">
                                    Sync Details
                                </h3>
                                <div class="mt-2">
                                    <div id="sync-modal-content" class="text-sm text-zinc-700 dark:text-zinc-300">
                                        <!-- Modal content will be populated dynamically -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" id="close-sync-details-modal" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Tab switching
        const tabLinks = document.querySelectorAll('.tab-link');
        const tabContents = document.querySelectorAll('.tab-content');

        tabLinks.forEach(link => {
            link.addEventListener('click', () => {
                // Remove active class from all links and contents
                tabLinks.forEach(l => l.classList.remove('active'));
                tabContents.forEach(c => c.classList.add('hidden'));

                // Add active class to clicked link and corresponding content
                link.classList.add('active');
                const targetId = link.dataset.target;
                document.getElementById(targetId).classList.remove('hidden');
            });
        });

        // Project configuration form
        const projectConfigForm = document.getElementById('jira-project-config-form');
        if (projectConfigForm) {
            projectConfigForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(projectConfigForm);
                const data = {};
                formData.forEach((value, key) => {
                    // Handle nested objects like sync_settings[auto_sync]
                    if (key.includes('[') && key.includes(']')) {
                        const mainKey = key.substring(0, key.indexOf('['));
                        const subKey = key.substring(key.indexOf('[') + 1, key.indexOf(']'));

                        if (!data[mainKey]) {
                            data[mainKey] = {};
                        }

                        // Convert checkbox values to boolean
                        if (value === 'on') {
                            data[mainKey][subKey] = true;
                        } else if (value === 'off') {
                            data[mainKey][subKey] = false;
                        } else {
                            data[mainKey][subKey] = value;
                        }
                    } else {
                        data[key] = value;
                    }
                });

                try {
                    const response = await fetch('/dashboard/integrations/jira/configure', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (result.success) {
                        showToast('Configuration saved successfully', 'success');
                    } else {
                        showToast(result.message || 'Failed to save configuration', 'error');
                    }
                } catch (error) {
                    showToast('An error occurred while saving configuration', 'error');
                    console.error('Error:', error);
                }
            });
        }

        // Toggle project sections in import form
        const createProjectRadio = document.getElementById('create-new-project');
        const useExistingProjectRadio = document.getElementById('use-existing-project');
        const existingProjectSection = document.getElementById('existing-project-section');
        const newProjectSection = document.getElementById('new-project-section');

        if (createProjectRadio && useExistingProjectRadio) {
            createProjectRadio.addEventListener('change', function() {
                newProjectSection.classList.remove('hidden');
                existingProjectSection.classList.add('hidden');
            });

            useExistingProjectRadio.addEventListener('change', function() {
                newProjectSection.classList.add('hidden');
                existingProjectSection.classList.remove('hidden');
            });
        }

        // Import form submission
        const importForm = document.getElementById('jira-import-form');
        if (importForm) {
            importForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(importForm);
                const data = {};

                formData.forEach((value, key) => {
                    if (key === 'import_epics' || key === 'import_stories' || key === 'generate_test_scripts') {
                        // These are checkboxes - they'll only be present if checked
                        data[key] = 'on';
                    } else {
                        data[key] = value;
                    }
                });

                const jiraProjectSelect = document.getElementById('import-jira-project');
                const selectedOption = jiraProjectSelect.options[jiraProjectSelect.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    // Extract the name part from "Project Name (KEY)"
                    data.jira_project_name = selectedOption.textContent.split(' (')[0];
                }

                // Show progress tracker
                showProgressTracker();

                try {
                    const response = await fetch('/dashboard/integrations/jira/import-project', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Start polling for progress updates
                        if (result.data && result.data.project_id) {
                            pollImportProgress(result.data.project_id);
                        }
                    } else {
                        hideProgressTracker();
                        showToast(result.message || 'Failed to start import', 'error');
                    }
                } catch (error) {
                    hideProgressTracker();
                    showToast('An error occurred while starting import', 'error');
                    console.error('Error:', error);
                }
            });
        }

        // Sync form submission
        const syncForm = document.getElementById('jira-sync-form');
        if (syncForm) {
            syncForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(syncForm);
                const data = {
                    project_id: formData.get('project_id'),
                    direction: formData.get('direction'),
                    entity_types: Array.from(formData.getAll('entity_types[]')),
                    sync_options: {}
                };

                // Process sync options
                if (formData.has('sync_options[sync_comments]')) {
                    data.sync_options.sync_comments = true;
                }
                if (formData.has('sync_options[sync_attachments]')) {
                    data.sync_options.sync_attachments = true;
                }
                if (formData.has('sync_options[dry_run]')) {
                    data.sync_options.dry_run = true;
                }

                // Show sync progress section
                document.getElementById('sync-progress-section').classList.remove('hidden');
                updateSyncProgress(0, 'Initializing synchronization...');

                try {
                    const response = await fetch('/dashboard/integrations/jira/start-sync', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Start polling for sync progress
                        if (result.data && result.data.progress_id) {
                            pollSyncProgress(result.data.progress_id);
                        }
                    } else {
                        showToast(result.message || 'Failed to start synchronization', 'error');
                    }
                } catch (error) {
                    showToast('An error occurred while starting synchronization', 'error');
                    console.error('Error:', error);
                }
            });
        }

        // Quick sync button
        const startSyncBtn = document.getElementById('start-sync-btn');
        if (startSyncBtn) {
            startSyncBtn.addEventListener('click', function() {
                // Switch to sync tab
                tabLinks.forEach(l => l.classList.remove('active'));
                tabContents.forEach(c => c.classList.add('hidden'));

                const syncTab = document.querySelector('.tab-link[data-target="sync-section"]');
                if (syncTab) {
                    syncTab.classList.add('active');
                    document.getElementById('sync-section').classList.remove('hidden');
                }
            });
        }

        // Quick import button
        const quickImportBtn = document.getElementById('quick-import-btn');
        if (quickImportBtn) {
            quickImportBtn.addEventListener('click', function() {
                // Switch to import tab
                tabLinks.forEach(l => l.classList.remove('active'));
                tabContents.forEach(c => c.classList.add('hidden'));

                const importTab = document.querySelector('.tab-link[data-target="import-section"]');
                if (importTab) {
                    importTab.classList.add('active');
                    document.getElementById('import-section').classList.remove('hidden');
                }
            });
        }

        // Categorize & Import button
        const categorizeBtn = document.getElementById('categorize-before-import');
        if (categorizeBtn) {
            categorizeBtn.addEventListener('click', async function() {
                const jiraProjectKey = document.getElementById('import-jira-project').value;

                if (!jiraProjectKey) {
                    showToast('Please select a Jira project', 'error');
                    return;
                }

                // Show loading state in the preview section
                const previewSection = document.getElementById('issue-preview-section');
                previewSection.classList.remove('hidden');

                try {
                    const response = await fetch('/dashboard/integrations/jira/categorization-options', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            project_id: document.getElementById('import-arxitest-project').value || null,
                            jira_project_key: jiraProjectKey
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        populateIssuePreview(result.data);
                    } else {
                        showToast(result.message || 'Failed to fetch issues', 'error');
                    }
                } catch (error) {
                    showToast('An error occurred while fetching issues', 'error');
                    console.error('Error:', error);
                }
            });
        }

        // History detail toggles
        const historyToggles = document.querySelectorAll('.history-details-toggle');
        historyToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const index = this.dataset.index;
                const detailsEl = document.getElementById(`history-details-${index}`);

                if (detailsEl.classList.contains('hidden')) {
                    detailsEl.classList.remove('hidden');
                    this.textContent = 'Hide details';
                } else {
                    detailsEl.classList.add('hidden');
                    this.textContent = 'Show details';
                }
            });
        });

        // Sync details modal
        const syncDetailsBtns = document.querySelectorAll('.view-sync-details');
        const syncDetailsModal = document.getElementById('sync-details-modal');
        const closeSyncDetailsBtn = document.getElementById('close-sync-details-modal');

        syncDetailsBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const syncId = this.dataset.syncId;
                const syncData = {{{ json_encode($syncHistory) }}}[syncId];

                if (syncData) {
                    populateSyncDetailsModal(syncData);
                    syncDetailsModal.classList.remove('hidden');
                }
            });
        });

        if (closeSyncDetailsBtn) {
            closeSyncDetailsBtn.addEventListener('click', () => {
                syncDetailsModal.classList.add('hidden');
            });
        }

        // Helper functions
        function showToast(message, type = 'success') {
            // Check if toast container exists, if not create it
            let toastContainer = document.getElementById('toast-container');

            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.className = 'fixed bottom-4 right-4 z-50 flex flex-col space-y-2';
                document.body.appendChild(toastContainer);
            }

            // Create toast element
            const toast = document.createElement('div');
            toast.className = `px-4 py-3 rounded-lg shadow-lg transform transition-all duration-300 flex items-center space-x-2 ${
                type === 'success'
                    ? 'bg-green-100 dark:bg-green-900/70 text-green-800 dark:text-green-200'
                    : 'bg-red-100 dark:bg-red-900/70 text-red-800 dark:text-red-200'
            }`;

            // Add icon based on type
            const icon = document.createElement('i');
            icon.setAttribute('data-lucide', type === 'success' ? 'check-circle' : 'alert-circle');
            icon.className = 'w-5 h-5';

            const messageEl = document.createElement('span');
            messageEl.textContent = message;

            toast.appendChild(icon);
            toast.appendChild(messageEl);

            toastContainer.appendChild(toast);

            // Initialize icon
            if (typeof lucide !== 'undefined') {
                lucide.createIcons({
                    icons: {
                        'check-circle': true,
                        'alert-circle': true
                    },
                    attrs: {
                        class: ['w-5', 'h-5']
                    }
                });
            }

            // Remove toast after delay
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-x-4');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }

        function showProgressTracker() {
            // Implementation similar to GitHub integration's progress tracker
            let progressTracker = document.getElementById('jira-progress-tracker');

            if (!progressTracker) {
                progressTracker = document.createElement('div');
                progressTracker.id = 'jira-progress-tracker';
                progressTracker.className = 'fixed bottom-6 right-6 bg-white dark:bg-zinc-800 shadow-lg rounded-lg p-3 flex flex-col items-start gap-2 border border-zinc-200 dark:border-zinc-700 animate-fade-in min-w-[300px] z-50';

                progressTracker.innerHTML = `
                    <div class="flex items-center justify-between w-full">
                        <h3 class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                            Jira Import
                        </h3>
                        <button id="close-jira-progress" class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="text-sm text-zinc-600 dark:text-zinc-400" id="jira-progress-text">Initializing...</div>
                    <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2.5 mb-1">
                        <div id="jira-progress-bar" class="bg-gradient-to-r from-indigo-600 to-purple-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <div class="flex items-center justify-between w-full text-xs text-zinc-500 dark:text-zinc-500">
                        <span id="jira-progress-percentage">0%</span>
                        <span id="jira-progress-time">00:00</span>
                    </div>
                    <div id="jira-progress-complete" class="hidden pt-2 mt-2 border-t border-zinc-200 dark:border-zinc-700 w-full">
                        <div id="jira-success-indicator" class="hidden flex items-center text-sm text-emerald-600 dark:text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-1.5">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            <span>Import completed successfully!</span>
                        </div>
                        <div id="jira-failure-indicator" class="hidden flex items-center text-sm text-red-600 dark:text-red-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-1.5">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            <span id="jira-error-message">An error occurred</span>
                        </div>
                        <div class="mt-2 flex justify-end">
                            <button id="view-project-btn" class="hidden text-xs px-3 py-1.5 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                                View Project
                            </button>
                            <button id="dismiss-jira-progress-btn" class="text-xs px-3 py-1.5 bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 rounded-md hover:bg-zinc-300 dark:hover:bg-zinc-600 transition-colors ml-2">
                                Dismiss
                            </button>
                        </div>
                    </div>
                `;

                document.body.appendChild(progressTracker);

                // Add event listeners for close and dismiss buttons
                document.getElementById('close-jira-progress').addEventListener('click', hideProgressTracker);
                document.getElementById('dismiss-jira-progress-btn').addEventListener('click', hideProgressTracker);
            } else {
                progressTracker.classList.remove('hidden');
            }
        }

        function hideProgressTracker() {
            const progressTracker = document.getElementById('jira-progress-tracker');
            if (progressTracker) {
                progressTracker.classList.add('hidden');
            }
        }

        function updateProgressTracker(percent, message, isComplete = false, success = null) {
            const progressBar = document.getElementById('jira-progress-bar');
            const progressText = document.getElementById('jira-progress-text');
            const progressPercent = document.getElementById('jira-progress-percentage');
            const progressComplete = document.getElementById('jira-progress-complete');
            const successIndicator = document.getElementById('jira-success-indicator');
            const failureIndicator = document.getElementById('jira-failure-indicator');
            const errorMessage = document.getElementById('jira-error-message');
            const viewProjectBtn = document.getElementById('view-project-btn');

            if (progressBar && progressText && progressPercent) {
                progressBar.style.width = `${percent}%`;
                progressText.textContent = message;
                progressPercent.textContent = `${percent}%`;

                if (isComplete) {
                    progressComplete.classList.remove('hidden');

                    if (success === true) {
                        successIndicator.classList.remove('hidden');
                        failureIndicator.classList.add('hidden');

                        // Show view project button if we have a project ID
                        if (window.projectId) {
                            viewProjectBtn.classList.remove('hidden');
                            viewProjectBtn.addEventListener('click', function() {
                                window.location.href = `/dashboard/projects/${window.projectId}`;
                            });
                        }
                    } else if (success === false) {
                        successIndicator.classList.add('hidden');
                        failureIndicator.classList.remove('hidden');

                        if (errorMessage && message.startsWith('Error:')) {
                            errorMessage.textContent = message;
                        }
                    }
                }
            }
        }

        // Function to poll import progress
        function pollImportProgress(projectId) {
            window.projectId = projectId; // Store for view project button

            const checkProgress = async () => {
                try {
                    const response = await fetch(`/dashboard/integrations/jira/import/progress/${projectId}?check_progress=1`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success && data.data.progress) {
                        const progress = data.data.progress;

                        // Update progress tracker
                        updateProgressTracker(
                            calculateProgressPercentage(progress),
                            progress.error || 'Processing...',
                            progress.completed,
                            progress.success
                        );

                        if (!progress.completed) {
                            // Continue polling
                            setTimeout(checkProgress, 2000);
                        } else {
                            // If completed successfully, update UI
                            if (progress.success) {
                                showToast('Import completed successfully', 'success');

                                // Refresh page after short delay
                                setTimeout(() => {
                                    window.location.reload();
                                }, 3000);
                            }
                        }
                    }
                } catch (error) {
                    console.error('Error polling progress:', error);
                    // Even on error, keep polling
                    setTimeout(checkProgress, 5000);
                }
            };

            // Start polling
            checkProgress();
        }

        // Function to poll sync progress
        function pollSyncProgress(progressId) {
            const checkProgress = async () => {
                try {
                    const response = await fetch(`/dashboard/integrations/jira/sync-status/${progressId}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        const progress = data.data;

                        // Update progress UI
                        updateSyncProgress(
                            progress.percent || 0,
                            progress.message || 'Processing...'
                        );

                        // Update stats if available
                        if (progress.pull_results || progress.push_results) {
                            updateSyncStats(progress);
                        }

                        if (!progress.is_complete) {
                            // Continue polling
                            setTimeout(checkProgress, 2000);
                        } else {
                            // If completed, update final state
                            updateSyncCompletion(progress);

                            // Show toast based on result
                            if (progress.is_success) {
                                showToast('Synchronization completed successfully', 'success');
                            } else {
                                showToast(progress.error || 'Synchronization failed', 'error');
                            }
                        }
                    }
                } catch (error) {
                    console.error('Error polling sync progress:', error);
                    // Keep polling even on error
                    setTimeout(checkProgress, 5000);
                }
            };

            // Start polling
            checkProgress();
        }

        function updateSyncProgress(percent, message) {
            const progressBar = document.getElementById('progress-bar');
            const progressMessage = document.getElementById('progress-message');
            const progressPercentage = document.getElementById('progress-percentage');

            if (progressBar && progressMessage && progressPercentage) {
                progressBar.style.width = `${percent}%`;
                progressMessage.textContent = message;
                progressPercentage.textContent = `${percent}%`;
            }
        }

        function updateSyncStats(data) {
            const statSuccess = document.getElementById('stat-success');
            const statFailed = document.getElementById('stat-failed');
            const statCreated = document.getElementById('stat-created');
            const statUpdated = document.getElementById('stat-updated');

            let success = 0, failed = 0, created = 0, updated = 0;

            if (data.pull_results) {
                success += data.pull_results.success || 0;
                failed += data.pull_results.failed || 0;
                created += data.pull_results.created || 0;
                updated += data.pull_results.updated || 0;
            }

            if (data.push_results) {
                success += data.push_results.success || 0;
                failed += data.push_results.failed || 0;
                created += data.push_results.created || 0;
                updated += data.push_results.updated || 0;
            }

            if (statSuccess) statSuccess.textContent = success;
            if (statFailed) statFailed.textContent = failed;
            if (statCreated) statCreated.textContent = created;
            if (statUpdated) statUpdated.textContent = updated;

            // Show details section if we have data
            if (success > 0 || failed > 0) {
                const syncDetails = document.getElementById('sync-details');
                if (syncDetails) {
                    syncDetails.classList.remove('hidden');
                }

                // Update sync log
                updateSyncLog(data);
            }
        }

        function updateSyncLog(data) {
            const syncLog = document.getElementById('sync-log');
            if (!syncLog) return;

            let logContent = '';

            // Add pull results to log
            if (data.pull_results) {
                logContent += `[PULL] ${data.pull_results.success || 0} successful, ${data.pull_results.failed || 0} failed\n`;

                if (data.pull_results.details && data.pull_results.details.length > 0) {
                    data.pull_results.details.forEach(detail => {
                        logContent += `  ${detail.status === 'success' ? '✓' : '✗'} ${detail.entity}: ${detail.id} ${detail.jira_key ? '(' + detail.jira_key + ')' : ''}\n`;
                        if (detail.error) {
                            logContent += `    Error: ${detail.error}\n`;
                        }
                    });
                }
            }

            // Add push results to log
            if (data.push_results) {
                logContent += `\n[PUSH] ${data.push_results.success || 0} successful, ${data.push_results.failed || 0} failed\n`;

                if (data.push_results.details && data.push_results.details.length > 0) {
                    data.push_results.details.forEach(detail => {
                        logContent += `  ${detail.status === 'success' ? '✓' : '✗'} ${detail.entity}: ${detail.id} ${detail.jira_key ? '(' + detail.jira_key + ')' : ''}\n`;
                        if (detail.error) {
                            logContent += `    Error: ${detail.error}\n`;
                        }
                    });
                }
            }

            syncLog.textContent = logContent;
        }

        function updateSyncCompletion(data) {
            // Update final state UI elements
            updateSyncProgress(100, data.is_success ? 'Synchronization completed successfully' : (data.error || 'Synchronization failed'));

            // Show completion message
            const progressMessage = document.getElementById('progress-message');
            if (progressMessage) {
                progressMessage.className = data.is_success
                    ? 'text-sm font-medium text-green-600 dark:text-green-400'
                    : 'text-sm font-medium text-red-600 dark:text-red-400';
            }
        }

        function calculateProgressPercentage(progress) {
            if (progress.completed) {
                return 100;
            }

            // Calculate based on counts of processed items
            const epics = progress.epics || 0;
            const stories = progress.stories || 0;
            const testCases = progress.testCases || 0;
            const testScripts = progress.testScripts || 0;

            const totalProcessed = epics + stories + testCases + testScripts;

            // Arbitrary thresholds: at least 10% at start, max 90% until completion
            return Math.min(90, Math.max(10, Math.floor(totalProcessed / 2)));
        }

        function populateIssuePreview(data) {
            const issueTypes = data.issue_types || [];
            const tableBody = document.getElementById('issues-table-body');
            const previewCount = document.getElementById('preview-count');

            // Clear existing content
            tableBody.innerHTML = '';

            if (issueTypes.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="p-4 text-center text-zinc-500 dark:text-zinc-400">
                            No issues found
                        </td>
                    </tr>
                `;
                if (previewCount) previewCount.textContent = '0 issues';
                return;
            }

            // Create rows for each issue type
            let issueCount = 0;

            data.issue_types.forEach(issueType => {
                const row = document.createElement('tr');
                row.className = 'issue-row hover:bg-zinc-50 dark:hover:bg-zinc-700/50';
                row.dataset.type = issueType.name.toLowerCase();

                row.innerHTML = `
                    <td class="px-3 py-3 whitespace-nowrap">
                        <input type="checkbox" class="issue-checkbox h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500" data-id="${issueType.id}">
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">
                        ${issueType.id}
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap text-sm">
                        <span class="px-2 py-1 text-xs rounded-full ${getIssueTypeColor(issueType.name)}">
                            ${issueType.name}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-sm text-zinc-700 dark:text-zinc-300">
                        ${issueType.description || issueType.name}
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">
                        N/A
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap text-sm">
                        <select class="import-as-select text-xs border-zinc-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            ${getImportOptionsForType(issueType.name)}
                        </select>
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                        <button class="preview-issue-btn text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-200" data-id="${issueType.id}">
                            Preview
                        </button>
                    </td>
                `;

                tableBody.appendChild(row);
                issueCount++;
            });

            if (previewCount) {
                previewCount.textContent = `${issueCount} issue${issueCount === 1 ? '' : 's'}`;
            }

            // Add event listeners for issue filters
            const issueFilters = document.querySelectorAll('.issue-filter');
            issueFilters.forEach(filter => {
                filter.addEventListener('click', function() {
                    issueFilters.forEach(f => f.classList.remove('active'));
                    this.classList.add('active');

                    const filterType = this.dataset.filter;
                    const rows = document.querySelectorAll('.issue-row');

                    rows.forEach(row => {
                        if (filterType === 'all' || row.dataset.type === filterType) {
                            row.classList.remove('hidden');
                        } else {
                            row.classList.add('hidden');
                        }
                    });
                });
            });

            // Add event listener for select all checkbox
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('.issue-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                    });
                });
            }

            // Add event listeners for select all/deselect all buttons
            const selectAllBtn = document.getElementById('select-all-issues');
            const deselectAllBtn = document.getElementById('deselect-all-issues');

            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    const checkboxes = document.querySelectorAll('.issue-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = true;
                    });
                    if (selectAllCheckbox) selectAllCheckbox.checked = true;
                });
            }

            if (deselectAllBtn) {
                deselectAllBtn.addEventListener('click', function() {
                    const checkboxes = document.querySelectorAll('.issue-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    if (selectAllCheckbox) selectAllCheckbox.checked = false;
                });
            }

            // Add event listener for issue search
            const issueSearch = document.getElementById('issue-search');
            if (issueSearch) {
                issueSearch.addEventListener('input', function() {
                    const searchValue = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.issue-row');

                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        const activeFilter = document.querySelector('.issue-filter.active').dataset.filter;

                        if (text.includes(searchValue) && (activeFilter === 'all' || row.dataset.type === activeFilter)) {
                            row.classList.remove('hidden');
                        } else {
                            row.classList.add('hidden');
                        }
                    });
                });
            }

            // Add event listener for process selected button
            const processSelectedBtn = document.getElementById('process-selected-btn');
            if (processSelectedBtn) {
                processSelectedBtn.addEventListener('click', async function() {
                    const selectedIssues = [];
                    const checkboxes = document.querySelectorAll('.issue-checkbox:checked');

                    checkboxes.forEach(checkbox => {
                        const row = checkbox.closest('tr');
                        const importSelect = row.querySelector('.import-as-select');

                        selectedIssues.push({
                            id: checkbox.dataset.id,
                            import_as: importSelect.value
                        });
                    });

                    if (selectedIssues.length === 0) {
                        showToast('Please select at least one issue', 'error');
                        return;
                    }

                    // Process the import with categorization
                    try {
                        const formData = new FormData(document.getElementById('jira-import-form'));
                        const data = {
                            jira_project_key: formData.get('jira_project_key'),
                            create_new_project: formData.get('create_new_project'),
                            issues: selectedIssues
                        };

                        if (data.create_new_project === '1') {
                            data.new_project_name = formData.get('new_project_name');
                        } else {
                            data.arxitest_project_id = formData.get('arxitest_project_id');
                        }

                        showProgressTracker();

                        const response = await fetch('/integrations/jira/import-categorized', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(data)
                        });

                        const result = await response.json();

                        if (result.success) {
                            if (result.data && result.data.project_id) {
                                pollImportProgress(result.data.project_id);
                            }
                        } else {
                            hideProgressTracker();
                            showToast(result.message || 'Failed to start import', 'error');
                        }
                    } catch (error) {
                        hideProgressTracker();
                        showToast('An error occurred while starting import', 'error');
                        console.error('Error:', error);
                    }
                });
            }
        }

        function populateSyncDetailsModal(syncData) {
            const modalContent = document.getElementById('sync-modal-content');
            if (!modalContent) return;

            let content = `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-1">Sync Details</h4>
                        <p class="text-zinc-600 dark:text-zinc-400">
                            Direction: <span class="font-medium text-zinc-800 dark:text-zinc-200">${ucfirst(syncData.direction)}</span>
                        </p>
                        <p class="text-zinc-600 dark:text-zinc-400">
                            Completed: <span class="font-medium text-zinc-800 dark:text-zinc-200">${formatDate(syncData.completed_at)}</span>
                        </p>
                        <p class="text-zinc-600 dark:text-zinc-400">
                            Entity Types:
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                ${syncData.entity_types ? syncData.entity_types.map(t => ucfirst(t.replace('_', ' '))).join(', ') : 'N/A'}
                            </span>
                        </p>
                    </div>
            `;

            if (syncData.pull_results) {
                content += `
                    <div>
                        <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-1">Pull Results</h4>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-zinc-600 dark:text-zinc-400">Successful:</span>
                                <span class="text-green-600 dark:text-green-400 font-medium">${syncData.pull_results.success || 0}</span>
                            </div>
                            <div>
                                <span class="text-zinc-600 dark:text-zinc-400">Failed:</span>
                                <span class="text-red-600 dark:text-red-400 font-medium">${syncData.pull_results.failed || 0}</span>
                            </div>
                            <div>
                                <span class="text-zinc-600 dark:text-zinc-400">Created:</span>
                                <span class="text-green-600 dark:text-green-400 font-medium">${syncData.pull_results.created || 0}</span>
                            </div>
                            <div>
                                <span class="text-zinc-600 dark:text-zinc-400">Updated:</span>
                                <span class="text-blue-600 dark:text-blue-400 font-medium">${syncData.pull_results.updated || 0}</span>
                            </div>
                        </div>
                    </div>
                `;
            }

            if (syncData.push_results) {
                content += `
                    <div>
                        <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-1">Push Results</h4>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-zinc-600 dark:text-zinc-400">Successful:</span>
                                <span class="text-green-600 dark:text-green-400 font-medium">${syncData.push_results.success || 0}</span>
                            </div>
                            <div>
                                <span class="text-zinc-600 dark:text-zinc-400">Failed:</span>
                                <span class="text-red-600 dark:text-red-400 font-medium">${syncData.push_results.failed || 0}</span>
                            </div>
                            <div>
                                <span class="text-zinc-600 dark:text-zinc-400">Created:</span>
                                <span class="text-green-600 dark:text-green-400 font-medium">${syncData.push_results.created || 0}</span>
                            </div>
                            <div>
                                <span class="text-zinc-600 dark:text-zinc-400">Updated:</span>
                                <span class="text-blue-600 dark:text-blue-400 font-medium">${syncData.push_results.updated || 0}</span>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Add detailed logs if available
            if ((syncData.pull_results && syncData.pull_results.details && syncData.pull_results.details.length > 0) ||
                (syncData.push_results && syncData.push_results.details && syncData.push_results.details.length > 0)) {

                content += `
                    <div>
                        <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-1">Detailed Logs</h4>
                        <div class="bg-zinc-50 dark:bg-zinc-900/50 p-3 rounded text-xs font-mono max-h-60 overflow-y-auto">
                `;

                if (syncData.pull_results && syncData.pull_results.details) {
                    content += `<p class="text-zinc-600 dark:text-zinc-400 font-semibold mb-1">Pull Operations:</p>`;

                    syncData.pull_results.details.forEach(detail => {
                        const statusClass = detail.status === 'success' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
                        content += `
                            <p class="${statusClass}">
                                ${detail.status === 'success' ? '✓' : '✗'} ${detail.entity}: ${detail.id} ${detail.jira_key ? '(' + detail.jira_key + ')' : ''}
                            </p>
                        `;

                        if (detail.error) {
                            content += `<p class="text-red-600 dark:text-red-400 ml-4">${detail.error}</p>`;
                        }
                    });
                }

                if (syncData.push_results && syncData.push_results.details) {
                    content += `<p class="text-zinc-600 dark:text-zinc-400 font-semibold mt-3 mb-1">Push Operations:</p>`;

                    syncData.push_results.details.forEach(detail => {
                        const statusClass = detail.status === 'success' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
                        content += `
                            <p class="${statusClass}">
                                ${detail.status === 'success' ? '✓' : '✗'} ${detail.entity}: ${detail.id} ${detail.jira_key ? '(' + detail.jira_key + ')' : ''}
                            </p>
                        `;

                        if (detail.error) {
                            content += `<p class="text-red-600 dark:text-red-400 ml-4">${detail.error}</p>`;
                        }
                    });
                }

                content += `
                        </div>
                    </div>
                `;
            }

            content += `</div>`;

            modalContent.innerHTML = content;
        }

        // Helper utility functions
        function getIssueTypeColor(type) {
            type = type.toLowerCase();

            switch (type) {
                case 'epic':
                    return 'bg-purple-100 dark:bg-purple-900/50 text-purple-800 dark:text-purple-200';
                case 'story':
                    return 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200';
                case 'task':
                    return 'bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200';
                case 'bug':
                    return 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200';
                default:
                    return 'bg-zinc-100 dark:bg-zinc-900/50 text-zinc-800 dark:text-zinc-200';
            }
        }

        function getImportOptionsForType(type) {
            type = type.toLowerCase();

            let options = '';

            if (type === 'epic') {
                options += '<option value="test_suite" selected>Test Suite</option>';
                options += '<option value="story">Story</option>';
                options += '<option value="skip">Skip</option>';
            } else if (type === 'story') {
                options += '<option value="story" selected>Story</option>';
                options += '<option value="test_case">Test Case</option>';
                options += '<option value="skip">Skip</option>';
            } else if (type === 'task') {
                options += '<option value="story" selected>Story</option>';
                options += '<option value="test_case">Test Case</option>';
                options += '<option value="skip">Skip</option>';
            } else if (type === 'bug') {
                options += '<option value="story">Story</option>';
                options += '<option value="test_case" selected>Test Case</option>';
                options += '<option value="skip">Skip</option>';
            } else {
                options += '<option value="story" selected>Story</option>';
                options += '<option value="test_case">Test Case</option>';
                options += '<option value="skip">Skip</option>';
            }

            return options;
        }

        function ucfirst(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString();
        }
    });
</script>

<style>
    /* Tab styling */
    .tab-link {
        @apply inline-flex items-center px-1 py-3 text-sm font-medium text-zinc-500 dark:text-zinc-400 border-b-2 border-transparent hover:text-zinc-800 dark:hover:text-zinc-200 hover:border-indigo-500;
    }

    .tab-link.active {
        @apply text-indigo-600 dark:text-indigo-400 border-indigo-500;
    }

    /* Filter styling */
    .issue-filter {
        @apply text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 pb-1 border-b-2 border-transparent transition-colors;
    }

    .issue-filter.active {
        @apply text-indigo-600 dark:text-indigo-400 border-indigo-500 font-medium;
    }
</style>
@endpush
