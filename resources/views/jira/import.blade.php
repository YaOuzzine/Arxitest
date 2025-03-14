@extends('layouts.app')

@section('title', 'Import Jira Projects')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">

                <!-- Header Section -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Import Jira Projects</h1>

                    <!-- Jira Connection Status -->
                    @if (session('jira_site_name'))
                        <div class="flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Connected to: {{ session('jira_site_name') }}</span>
                        </div>
                    @else
                        <a href="{{ url('/jira/oauth') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Connect to Jira
                        </a>
                    @endif
                </div>

                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                @if (!session('jira_access_token'))
                    <!-- Not Connected to Jira Message -->
                    <div class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Not Connected to Jira</h3>
                        <p class="text-gray-600 mb-6">You need to connect to Jira before importing projects.</p>
                        <a href="{{ url('/jira/oauth') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Connect to Jira
                        </a>
                    </div>
                @else
                    <!-- Import Form -->
                    <form action="{{ url('/jira/import') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Team Selection -->
                        <div class="mb-6">
                            <label for="team_id" class="block text-sm font-medium text-gray-700 mb-2">Select Team</label>
                            <select id="team_id" name="team_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">Select a team</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">The imported projects will be assigned to this team</p>
                            @error('team_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jira Projects List -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Jira Projects to Import</label>
                            <div class="bg-gray-50 rounded-md p-4 border border-gray-200 max-h-96 overflow-y-auto">
                                @if(count($jiraProjects ?? []) > 0)
                                    <div class="grid grid-cols-1 gap-4">
                                        @foreach($jiraProjects as $project)
                                            <div class="relative flex items-start p-4 border border-gray-200 rounded-md hover:bg-gray-50 @if(in_array($project['key'], $existingJiraKeys ?? []) || in_array($project['id'], $existingJiraIds ?? [])) border-blue-200 bg-blue-50 @endif">
                                                <div class="flex items-center h-5">
                                                    <input id="project-{{ $project['id'] }}" name="jira_projects[]" value="{{ $project['key'] }}" type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                        @if(in_array($project['key'], $existingJiraKeys ?? []) || in_array($project['id'], $existingJiraIds ?? [])) checked @endif>
                                                </div>
                                                <div class="ml-3 flex-1">
                                                    <label for="project-{{ $project['id'] }}" class="block font-medium text-gray-700">
                                                        {{ $project['name'] }} <span class="text-gray-500 text-sm ml-1">({{ $project['key'] }})</span>
                                                        @if(in_array($project['key'], $existingJiraKeys ?? []) || in_array($project['id'], $existingJiraIds ?? []))
                                                            <span class="inline-flex items-center px-2 py-0.5 ml-2 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                                <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 6a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2zm0 6a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z" clip-rule="evenodd" />
                                                                </svg>
                                                                Already Imported
                                                            </span>
                                                        @endif
                                                    </label>
                                                    <p class="text-sm text-gray-500">{{ $project['projectTypeKey'] ?? 'Unknown type' }} project</p>
                                                    <p class="text-sm text-gray-500 mt-1">
                                                        @if(isset($project['description']) && !empty($project['description']))
                                                            {{ Str::limit($project['description'], 150) }}
                                                        @else
                                                            No description available
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center p-4 text-gray-500">
                                        <p>No Jira projects found</p>
                                    </div>
                                @endif
                            </div>
                            @error('jira_projects')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="mt-2 flex justify-between">
                                <div class="text-sm text-gray-500">
                                    <span id="selected-count">0</span> projects selected
                                </div>
                                <div class="space-x-2">
                                    <button type="button" id="select-all" class="text-sm text-blue-600 hover:text-blue-800">Select All</button>
                                    <button type="button" id="deselect-all" class="text-sm text-blue-600 hover:text-blue-800">Deselect All</button>
                                </div>
                            </div>
                        </div>

                        <!-- Import Options -->
                        <div class="bg-gray-50 rounded-md p-4 border border-gray-200">
                            <h3 class="text-md font-medium text-gray-900 mb-2">Import Options</h3>

                            <div class="mt-4 space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="update_existing" name="update_existing" type="checkbox" checked class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="update_existing" class="font-medium text-gray-700">Update existing projects</label>
                                        <p class="text-gray-500">If unchecked, projects that have already been imported will be skipped</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="include_metadata" name="include_metadata" type="checkbox" checked class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="include_metadata" class="font-medium text-gray-700">Include additional metadata</label>
                                        <p class="text-gray-500">Import assignees, labels, and status information</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Import Button -->
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                Import Selected Projects
                            </button>
                        </div>
                    </form>

                    <!-- Information Section -->
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">About Jira Project Import</h3>
                        <div class="prose prose-blue text-gray-500">
                            <p>This tool imports Jira projects and their issues into Arxitest. For each selected project:</p>
                            <ul class="list-disc pl-5 mt-2 space-y-1">
                                <li>A new Arxitest project will be created with the same name</li>
                                <li>A default test suite will be created</li>
                                <li>All matching issues will be imported as Jira stories</li>
                                <li>Stories can then be used to generate test scripts</li>
                            </ul>
                            <p class="mt-4">For best results, ensure your Jira projects have detailed user stories with acceptance criteria.</p>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const projectCheckboxes = document.querySelectorAll('input[name="jira_projects[]"]');
        const selectAllBtn = document.getElementById('select-all');
        const deselectAllBtn = document.getElementById('deselect-all');
        const selectedCountEl = document.getElementById('selected-count');

        // Update selected count
        function updateSelectedCount() {
            const selectedCount = document.querySelectorAll('input[name="jira_projects[]"]:checked').length;
            selectedCountEl.textContent = selectedCount;
        }

        // Add event listener to each checkbox
        projectCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });

        // Select all button
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function() {
                projectCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
                updateSelectedCount();
            });
        }

        // Deselect all button
        if (deselectAllBtn) {
            deselectAllBtn.addEventListener('click', function() {
                projectCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                updateSelectedCount();
            });
        }

        // Initialize count
        updateSelectedCount();

        // Form validation
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const teamSelect = document.getElementById('team_id');
                if (!teamSelect.value) {
                    e.preventDefault();
                    alert('Please select a team');
                    teamSelect.focus();
                    return;
                }

                const selectedProjects = document.querySelectorAll('input[name="jira_projects[]"]:checked');
                if (selectedProjects.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one Jira project to import');
                    return;
                }
            });
        }
    });
</script>
@endsection
