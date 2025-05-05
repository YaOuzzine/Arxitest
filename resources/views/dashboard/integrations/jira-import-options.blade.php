@extends('layouts.dashboard')

@section('title', 'Import from Jira')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.integrations.index') }}" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white">
            Integrations
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Import from Jira</span>
    </li>
@endsection

@section('content')
<div class="container py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white tracking-tight mb-2">Import from Jira</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Import issues from Jira to create test suites, test cases, and more.</p>
        </div>

        <form action="{{ route('integrations.jira.import.project') }}" method="POST" class="space-y-8">
            @csrf

            <!-- Jira Project Selection -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 mr-2">1</span>
                        Select Jira Project
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label for="jira_project" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Jira Project
                            </label>
                            <select name="jira_project" id="jira_project" class="w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">Select a Jira project</option>
                                @foreach($jiraProjects as $project)
                                    <option value="{{ json_encode(['key' => $project['key'], 'name' => $project['name']]) }}">
                                        {{ $project['name'] }} ({{ $project['key'] }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="jira_project_key" id="jira_project_key">
                            <input type="hidden" name="jira_project_name" id="jira_project_name">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Destination -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 mr-2">2</span>
                        Select Destination
                    </h2>

                    <div class="space-y-6">
                        <!-- Project Selection Radio Buttons -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative border border-zinc-300 dark:border-zinc-600 rounded-lg p-4 hover:border-blue-500 dark:hover:border-blue-400 transition-colors duration-200">
                                <input type="radio" name="create_new_project" id="create_new" value="1" class="absolute top-4 right-4" checked>
                                <label for="create_new" class="block cursor-pointer">
                                    <div class="font-medium text-zinc-900 dark:text-white mb-1">Create New Project</div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Import into a brand new project</p>
                                </label>
                            </div>

                            <div class="relative border border-zinc-300 dark:border-zinc-600 rounded-lg p-4 hover:border-blue-500 dark:hover:border-blue-400 transition-colors duration-200">
                                <input type="radio" name="create_new_project" id="existing_project" value="0" class="absolute top-4 right-4">
                                <label for="existing_project" class="block cursor-pointer">
                                    <div class="font-medium text-zinc-900 dark:text-white mb-1">Use Existing Project</div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Import into one of your projects</p>
                                </label>
                            </div>
                        </div>

                        <!-- New Project Name (Conditional) -->
                        <div id="new_project_container">
                            <label for="new_project_name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                New Project Name
                            </label>
                            <input type="text" name="new_project_name" id="new_project_name" class="w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="My Jira Import">
                        </div>

                        <!-- Existing Project Dropdown (Conditional) -->
                        <div id="existing_project_container" class="hidden">
                            <label for="arxitest_project_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Select Project
                            </label>
                            <select name="arxitest_project_id" id="arxitest_project_id" class="w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($existingProjects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Options -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 mr-2">3</span>
                        Configure Import
                    </h2>

                    <div class="space-y-4">
                        <!-- Import Options Checkboxes -->
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="checkbox" name="import_epics" id="import_epics" class="rounded border-zinc-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500" checked>
                                <label for="import_epics" class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">
                                    Import Epics as Test Suites
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="import_stories" id="import_stories" class="rounded border-zinc-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500" checked>
                                <label for="import_stories" class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">
                                    Import Stories, Tasks & Bugs as Test Cases
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="generate_test_scripts" id="generate_test_scripts" class="rounded border-zinc-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <label for="generate_test_scripts" class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">
                                    Generate Test Scripts (using AI)
                                </label>
                            </div>
                        </div>

                        <!-- Advanced Options (Collapsible) -->
                        <div class="pt-4">
                            <button type="button" id="toggle_advanced" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center">
                                <i data-lucide="chevron-right" class="w-4 h-4 mr-1 transform transition-transform duration-200" id="advanced_chevron"></i>
                                Advanced Options
                            </button>

                            <div id="advanced_options" class="hidden pt-4 space-y-4">
                                <div>
                                    <label for="jql_filter" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                        Additional JQL Filter (Optional)
                                    </label>
                                    <input type="text" name="jql_filter" id="jql_filter" class="w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="status in (Open, 'In Progress')">
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                        Additional JQL to filter which issues are imported
                                    </p>
                                </div>

                                <div>
                                    <label for="max_issues" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                        Maximum Issues to Import
                                    </label>
                                    <input type="number" name="max_issues" id="max_issues" min="1" max="1000" class="w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="50" value="50">
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                        Limit how many issues to import (default: 50, max: 1000)
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <a href="{{ route('dashboard.integrations.index') }}" class="btn-secondary mr-2">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    Start Import
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-primary {
        @apply inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500;
    }

    .btn-secondary {
        @apply inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lucide icons
        lucide.createIcons();

        // Handle project selection
        const jiraProjectSelect = document.getElementById('jira_project');
        const jiraProjectKey = document.getElementById('jira_project_key');
        const jiraProjectName = document.getElementById('jira_project_name');
        const newProjectName = document.getElementById('new_project_name');

        jiraProjectSelect.addEventListener('change', function() {
            const selectedOption = this.value;

            if (selectedOption) {
                const projectData = JSON.parse(selectedOption);
                jiraProjectKey.value = projectData.key;
                jiraProjectName.value = projectData.name;

                // Auto-fill new project name
                if (!newProjectName.value) {
                    newProjectName.value = `${projectData.name} Tests`;
                }
            } else {
                jiraProjectKey.value = '';
                jiraProjectName.value = '';
            }
        });

        // Handle project type selection
        const createNewRadio = document.getElementById('create_new');
        const existingProjectRadio = document.getElementById('existing_project');
        const newProjectContainer = document.getElementById('new_project_container');
        const existingProjectContainer = document.getElementById('existing_project_container');

        function updateProjectContainers() {
            if (createNewRadio.checked) {
                newProjectContainer.classList.remove('hidden');
                existingProjectContainer.classList.add('hidden');
            } else {
                newProjectContainer.classList.add('hidden');
                existingProjectContainer.classList.remove('hidden');
            }
        }

        createNewRadio.addEventListener('change', updateProjectContainers);
        existingProjectRadio.addEventListener('change', updateProjectContainers);

        // Toggle advanced options
        const toggleAdvanced = document.getElementById('toggle_advanced');
        const advancedOptions = document.getElementById('advanced_options');
        const advancedChevron = document.getElementById('advanced_chevron');

        toggleAdvanced.addEventListener('click', function() {
            advancedOptions.classList.toggle('hidden');
            advancedChevron.classList.toggle('rotate-90');
        });

        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            let valid = true;

            // Validate Jira project
            if (!jiraProjectKey.value) {
                valid = false;
                alert('Please select a Jira project');
            }

            // Validate new project name if creating new
            if (createNewRadio.checked && !newProjectName.value) {
                valid = false;
                alert('Please enter a name for the new project');
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush
