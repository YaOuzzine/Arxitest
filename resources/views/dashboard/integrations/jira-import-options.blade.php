@extends('layouts.dashboard')

@section('title', 'Jira Import Options')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.integrations.index') }}" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white">
            Integrations
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Jira Import</span>
    </li>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white tracking-tight">Import from Jira</h1>
        <p class="mt-2 text-zinc-600 dark:text-zinc-400 text-lg">
            Import projects, epics, stories and create test cases from your Jira issues
        </p>
    </div>

    <!-- Import Form -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <div class="p-6">
            <form id="jiraImportForm" action="{{ route('integrations.jira.import.project') }}" method="POST" class="space-y-6">
                @csrf

                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">Step 1: Select Jira Project</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="col-span-2">
                            <label for="jira_project_key" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Jira Project</label>
                            <select id="jira_project_key" name="jira_project_key" class="form-select w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Select a Jira project...</option>
                                @foreach($jiraProjects as $project)
                                    <option value="{{ $project['key'] }}" data-name="{{ $project['name'] }}">
                                        {{ $project['name'] }} ({{ $project['key'] }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" id="jira_project_name" name="jira_project_name" value="">
                        </div>

                        <div>
                            <label for="max_issues" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Max Issues</label>
                            <input type="number" id="max_issues" name="max_issues" min="0" max="300" value="50"
                                class="form-input w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                Set to 0 for unlimited (may take longer)
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">Step 2: Import Options</h2>

                    <div class="flex flex-col md:flex-row gap-6">
                        <div class="flex-1">
                            <div class="flex items-start mb-4">
                                <div class="flex items-center h-5">
                                    <input id="create_new_project" name="create_new_project" type="checkbox" checked
                                        class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="create_new_project" class="font-medium text-zinc-700 dark:text-zinc-300">Create new Arxitest project</label>
                                    <p class="text-zinc-500 dark:text-zinc-400">Create a new project in Arxitest for imported data</p>
                                </div>
                            </div>

                            <div id="new-project-container" class="mb-4 pl-7">
                                <label for="new_project_name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">New Project Name</label>
                                <input type="text" id="new_project_name" name="new_project_name"
                                    class="form-input w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div id="existing-project-container" class="mb-4 pl-7 hidden">
                                <label for="arxitest_project_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Existing Project</label>
                                <select id="arxitest_project_id" name="arxitest_project_id"
                                    class="form-select w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select a project...</option>
                                    @foreach($existingProjects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex-1">
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="import_epics" name="import_epics" type="checkbox" checked
                                            class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="import_epics" class="font-medium text-zinc-700 dark:text-zinc-300">Import Epics</label>
                                        <p class="text-zinc-500 dark:text-zinc-400">Import epics as test suites</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="import_stories" name="import_stories" type="checkbox" checked
                                            class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="import_stories" class="font-medium text-zinc-700 dark:text-zinc-300">Import Stories</label>
                                        <p class="text-zinc-500 dark:text-zinc-400">Import stories, tasks, and bugs</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="generate_test_scripts" name="generate_test_scripts" type="checkbox"
                                            class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="generate_test_scripts" class="font-medium text-zinc-700 dark:text-zinc-300">Generate Test Scripts</label>
                                        <p class="text-zinc-500 dark:text-zinc-400">Automatically generate test scripts using AI</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">Step 3: Advanced Filters (Optional)</h2>

                    <div>
                        <label for="jql_filter" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">JQL Filter</label>
                        <textarea id="jql_filter" name="jql_filter" rows="2"
                            class="form-textarea w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="status in (Open, 'In Progress') AND labels = 'frontend'"></textarea>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Optional JQL for filtering issues. Will be combined with project filter.
                        </p>
                    </div>
                </div>

                <div class="pt-6 flex justify-between border-t border-zinc-200 dark:border-zinc-700">
                    <a href="{{ route('dashboard.integrations.index') }}" class="btn-secondary">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
                        Back to Integrations
                    </a>

                    <div class="flex space-x-3">
                        <button type="button" id="previewImportBtn" class="btn-secondary">
                            <i data-lucide="eye" class="w-4 h-4 mr-1"></i>
                            Preview Import
                        </button>

                        <button type="submit" id="startImportBtn" class="btn-primary">
                            <i data-lucide="download" class="w-4 h-4 mr-1"></i>
                            Start Import
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Progress Modal (Hidden by default) -->
    <div id="importProgressModal" class="fixed inset-0 bg-zinc-900/70 dark:bg-zinc-900/80 backdrop-blur-sm flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-xl border border-zinc-200 dark:border-zinc-700 w-full max-w-xl p-6">
            <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-4">
                <span id="progressTitle">Importing from Jira...</span>
            </h3>

            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400 mb-1">
                        <span>Overall Progress</span>
                        <span id="overallProgressText">0%</span>
                    </div>
                    <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2.5">
                        <div id="overallProgressBar" class="bg-indigo-600 dark:bg-indigo-500 h-2.5 rounded-full" style="width: 0%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400 mb-1">
                            <span>Epics</span>
                            <span id="epicCount">0</span>
                        </div>
                        <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                            <div id="epicProgressBar" class="bg-emerald-600 dark:bg-emerald-500 h-2 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400 mb-1">
                            <span>Stories</span>
                            <span id="storyCount">0</span>
                        </div>
                        <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                            <div id="storyProgressBar" class="bg-sky-600 dark:bg-sky-500 h-2 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400 mb-1">
                            <span>Test Cases</span>
                            <span id="testCaseCount">0</span>
                        </div>
                        <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                            <div id="testCaseProgressBar" class="bg-amber-600 dark:bg-amber-500 h-2 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400 mb-1">
                            <span>Scripts</span>
                            <span id="scriptCount">0</span>
                        </div>
                        <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                            <div id="scriptProgressBar" class="bg-purple-600 dark:bg-purple-500 h-2 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <div id="importStatus" class="py-2 text-sm text-zinc-600 dark:text-zinc-400 italic">
                    Preparing import...
                </div>

                <div id="importError" class="p-3 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-800 dark:text-red-300 text-sm hidden">
                    Error message will appear here
                </div>

                <div id="importSuccess" class="p-3 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-300 text-sm hidden">
                    Import completed successfully!
                </div>

                <div class="flex justify-end pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <button type="button" id="cancelImportBtn" class="btn-secondary mr-3">
                        Cancel
                    </button>

                    <a href="#" id="viewProjectBtn" class="btn-primary hidden">
                        View Project
                    </a>

                    <button type="button" id="closeModalBtn" class="btn-primary hidden">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-primary {
        @apply inline-flex items-center px-4 py-2 rounded-lg font-medium bg-gradient-to-br from-indigo-600 to-indigo-700 text-white shadow-sm hover:shadow-md transition-all duration-200 hover:scale-[1.02];
    }

    .btn-secondary {
        @apply inline-flex items-center px-3 py-2 rounded-lg font-medium bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors duration-200;
    }

    /* Animations */
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }
    .animate-pulse-slow {
      animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();

        // Store form element references
        const importForm = document.getElementById('jiraImportForm');
        const createNewProject = document.getElementById('create_new_project');
        const newProjectContainer = document.getElementById('new-project-container');
        const existingProjectContainer = document.getElementById('existing-project-container');
        const jiraProjectSelect = document.getElementById('jira_project_key');
        const jiraProjectNameInput = document.getElementById('jira_project_name');
        const newProjectNameInput = document.getElementById('new_project_name');
        const importProgressModal = document.getElementById('importProgressModal');

        // Progress elements
        const progressTitle = document.getElementById('progressTitle');
        const overallProgressBar = document.getElementById('overallProgressBar');
        const overallProgressText = document.getElementById('overallProgressText');
        const epicCount = document.getElementById('epicCount');
        const storyCount = document.getElementById('storyCount');
        const testCaseCount = document.getElementById('testCaseCount');
        const scriptCount = document.getElementById('scriptCount');
        const epicProgressBar = document.getElementById('epicProgressBar');
        const storyProgressBar = document.getElementById('storyProgressBar');
        const testCaseProgressBar = document.getElementById('testCaseProgressBar');
        const scriptProgressBar = document.getElementById('scriptProgressBar');
        const importStatus = document.getElementById('importStatus');
        const importError = document.getElementById('importError');
        const importSuccess = document.getElementById('importSuccess');
        const viewProjectBtn = document.getElementById('viewProjectBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelImportBtn = document.getElementById('cancelImportBtn');

        // Handle project creation mode toggle
        createNewProject.addEventListener('change', function() {
            if (this.checked) {
                newProjectContainer.classList.remove('hidden');
                existingProjectContainer.classList.add('hidden');
                // Set default project name based on Jira project
                const selectedOption = jiraProjectSelect.options[jiraProjectSelect.selectedIndex];
                if (selectedOption.value) {
                    newProjectNameInput.value = selectedOption.dataset.name + " - Arxitest";
                }
            } else {
                newProjectContainer.classList.add('hidden');
                existingProjectContainer.classList.remove('hidden');
            }
        });

        // Update project name when Jira project changes
        jiraProjectSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                jiraProjectNameInput.value = selectedOption.dataset.name;

                // Update new project name input if create new project is checked
                if (createNewProject.checked) {
                    newProjectNameInput.value = selectedOption.dataset.name + " - Arxitest";
                }
            } else {
                jiraProjectNameInput.value = '';
                newProjectNameInput.value = '';
            }
        });

        // Form submission handler
        importForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validation
            if (!jiraProjectSelect.value) {
                alert('Please select a Jira project');
                return;
            }

            if (createNewProject.checked && !newProjectNameInput.value) {
                alert('Please enter a name for the new project');
                return;
            }

            if (!createNewProject.checked && !document.getElementById('arxitest_project_id').value) {
                alert('Please select an existing project');
                return;
            }

            // Show progress modal
            importProgressModal.classList.remove('hidden');

            // Start progress tracking
            startImportTracking();

            // Submit the form via AJAX
            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Import initiated:', data);

                if (!data.success) {
                    showImportError(data.message || 'Failed to start import');
                }
            })
            .catch(error => {
                console.error('Import error:', error);
                showImportError('Failed to start import: ' + error.message);
            });
        });

        // Preview import button (placeholder)
        document.getElementById('previewImportBtn').addEventListener('click', function() {
            alert('Preview functionality is not implemented yet.');
        });

        // Close modal buttons
        closeModalBtn.addEventListener('click', function() {
            importProgressModal.classList.add('hidden');
        });

        cancelImportBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to cancel the import?')) {
                importProgressModal.classList.add('hidden');
                stopImportTracking();
            }
        });

        // Progress tracking functions
        let trackingInterval;
        let projectId;

        function startImportTracking() {
            // Reset progress UI
            epicCount.textContent = '0';
            storyCount.textContent = '0';
            testCaseCount.textContent = '0';
            scriptCount.textContent = '0';
            epicProgressBar.style.width = '0%';
            storyProgressBar.style.width = '0%';
            testCaseProgressBar.style.width = '0%';
            scriptProgressBar.style.width = '0%';
            overallProgressBar.style.width = '0%';
            overallProgressText.textContent = '0%';
            importStatus.textContent = 'Starting import...';
            importError.classList.add('hidden');
            importSuccess.classList.add('hidden');
            viewProjectBtn.classList.add('hidden');
            closeModalBtn.classList.add('hidden');
            cancelImportBtn.classList.remove('hidden');

            // Start polling for progress
            trackingInterval = setInterval(checkImportProgress, 2000);
        }

        function stopImportTracking() {
            if (trackingInterval) {
                clearInterval(trackingInterval);
                trackingInterval = null;
            }
        }

        function checkImportProgress() {
            let url = '{{ route("integrations.jira.import.progress") }}';
            if (projectId) {
                url += '?project_id=' + projectId;
            }

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.progress) {
                    updateProgressUI(data.data.progress);

                    // If we have a project ID now, update the URL
                    if (data.data.progress.project_id && !projectId) {
                        projectId = data.data.progress.project_id;
                    }

                    // Check if import is completed
                    if (data.data.progress.completed) {
                        stopImportTracking();

                        if (data.data.progress.success) {
                            showImportSuccess(data.data.progress);
                        } else {
                            showImportError(data.data.progress.error || 'Import failed');
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Progress check error:', error);
                importStatus.textContent = 'Error checking progress: ' + error.message;
            });
        }

        function updateProgressUI(progress) {
            // Update counts
            epicCount.textContent = progress.epics || '0';
            storyCount.textContent = progress.stories || '0';
            testCaseCount.textContent = progress.testCases || '0';
            scriptCount.textContent = progress.testScripts || '0';

            // Update progress bars
            const totalItems = 100; // Estimate for calculation
            epicProgressBar.style.width = Math.min(100, ((progress.epics || 0) / totalItems * 100)) + '%';
            storyProgressBar.style.width = Math.min(100, ((progress.stories || 0) / totalItems * 100)) + '%';
            testCaseProgressBar.style.width = Math.min(100, ((progress.testCases || 0) / totalItems * 100)) + '%';
            scriptProgressBar.style.width = Math.min(100, ((progress.testScripts || 0) / totalItems * 100)) + '%';

            // Calculate overall progress (simplified)
            let overallPercent = 0;
            if (progress.completed) {
                overallPercent = 100;
            } else {
                const items = (progress.epics || 0) + (progress.stories || 0) +
                              (progress.testCases || 0) + (progress.testScripts || 0);
                overallPercent = Math.min(95, Math.floor(items / (totalItems * 2) * 100));
            }

            overallProgressBar.style.width = overallPercent + '%';
            overallProgressText.textContent = overallPercent + '%';

            // Update status message
            if (progress.completed) {
                importStatus.textContent = progress.success ? 'Import completed successfully!' : 'Import failed';
            } else if (progress.testScripts > 0) {
                importStatus.textContent = 'Generating test scripts...';
            } else if (progress.testCases > 0) {
                importStatus.textContent = 'Creating test cases...';
            } else if (progress.stories > 0) {
                importStatus.textContent = 'Importing stories...';
            } else if (progress.epics > 0) {
                importStatus.textContent = 'Importing epics...';
            } else {
                importStatus.textContent = 'Processing Jira data...';
            }
        }

        function showImportSuccess(progress) {
            progressTitle.textContent = 'Import Complete!';
            importStatus.textContent = `Successfully imported ${progress.epics || 0} epics, ${progress.stories || 0} stories, ${progress.testCases || 0} test cases, and ${progress.testScripts || 0} test scripts.`;
            importSuccess.classList.remove('hidden');
            cancelImportBtn.classList.add('hidden');
            closeModalBtn.classList.remove('hidden');

            if (projectId) {
                viewProjectBtn.href = '/dashboard/projects/' + projectId;
                viewProjectBtn.classList.remove('hidden');
            }
        }

        function showImportError(errorMessage) {
            progressTitle.textContent = 'Import Failed';
            importError.textContent = errorMessage;
            importError.classList.remove('hidden');
            cancelImportBtn.classList.add('hidden');
            closeModalBtn.classList.remove('hidden');
        }
    });
</script>
@endpush
