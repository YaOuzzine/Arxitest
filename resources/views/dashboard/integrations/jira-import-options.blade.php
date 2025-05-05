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
    <!-- Animated Header -->
    <div class="mb-8 transform transition-all duration-300 ease-out">
        <div class="space-y-1">
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white bg-gradient-to-r from-zinc-900 dark:from-zinc-100 to-zinc-600 dark:to-zinc-400 bg-clip-text text-transparent animate-fade-in-down">
                Import from Jira
            </h1>
            <p class="mt-2 text-zinc-600 dark:text-zinc-400 text-lg transition-opacity duration-300">
                Import projects, epics, stories and create test cases from your Jira issues
            </p>
        </div>
    </div>

    <!-- Glassmorphism Form Container -->
    <div class="bg-white/70 dark:bg-zinc-800/50 rounded-2xl shadow-2xl border border-zinc-200/50 dark:border-zinc-700/30 backdrop-blur-lg transition-all duration-300 hover:shadow-2xl">
        <div class="p-8">
            <form id="jiraImportForm" action="{{ route('integrations.jira.import.project') }}" method="POST" class="space-y-8">
                @csrf

                <!-- Step 1: Select Jira Project -->
                <div class="space-y-6">
                    <div class="animate-fade-in-left">
                        <h2 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 mb-2 bg-gradient-to-r from-zinc-800 to-zinc-600 dark:from-zinc-200 dark:to-zinc-400 bg-clip-text text-transparent">
                            Step 1: Select Jira Project
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <div class="relative">
                                <select id="jira_project_key" name="jira_project_key" class="peer h-12 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 pl-4 pr-12 text-zinc-700 dark:text-zinc-200 placeholder-transparent focus:ring-2 focus:ring-indigo-500/50 transition-all duration-300" required>
                                    <option value="">Select a Jira project...</option>
                                    @foreach($jiraProjects as $project)
                                        <option value="{{ $project['key'] }}" data-name="{{ $project['name'] }}">
                                            {{ $project['name'] }} ({{ $project['key'] }})
                                        </option>
                                    @endforeach
                                </select>
                                <label class="absolute left-4 -top-2.5 px-1 bg-zinc-100/50 dark:bg-zinc-800/50 text-sm text-zinc-600 dark:text-zinc-400 transition-all duration-300">
                                    Jira Project <span class="text-red-400">*</span>
                                </label>
                            </div>
                            <input type="hidden" id="jira_project_name" name="jira_project_name" value="">
                        </div>

                        <div>
                            <div class="relative">
                                <input type="number" id="max_issues" name="max_issues" min="0" max="300" value="50"
                                    class="peer h-12 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 pl-4 pr-12 text-zinc-700 dark:text-zinc-200 placeholder-transparent focus:ring-2 focus:ring-indigo-500/50 transition-all duration-300">
                                <label class="absolute left-4 -top-2.5 px-1 bg-zinc-100/50 dark:bg-zinc-800/50 text-sm text-zinc-600 dark:text-zinc-400 transition-all duration-300">
                                    Max Issues
                                </label>
                            </div>
                            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                Set to 0 for unlimited (may take longer)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Gradient Divider -->
                <div class="my-8 h-px bg-gradient-to-r from-transparent via-zinc-300/70 dark:via-zinc-600/50 to-transparent animate-scale-in-x"></div>

                <!-- Step 2: Import Options -->
                <div class="space-y-6">
                    <div class="animate-fade-in-left delay-100">
                        <h2 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 mb-2 bg-gradient-to-r from-zinc-800 to-zinc-600 dark:from-zinc-200 dark:to-zinc-400 bg-clip-text text-transparent">
                            Step 2: Import Options
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Project Selection -->
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex items-center h-5 mr-3">
                                    <input type="hidden" name="create_new_project" value="0">
                                    <input id="create_new_project" name="create_new_project" type="checkbox" value="1" checked
                                        class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                </div>
                                <div class="flex-1">
                                    <label for="create_new_project" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Create new Arxitest project</label>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Create a new project in Arxitest for imported data</p>
                                </div>
                            </div>

                            <div id="new-project-container" class="ml-8 space-y-4">
                                <div class="relative">
                                    <input type="text" id="new_project_name" name="new_project_name"
                                        class="peer h-12 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 pl-4 pr-12 text-zinc-700 dark:text-zinc-200 placeholder-transparent focus:ring-2 focus:ring-indigo-500/50 transition-all duration-300">
                                    <label class="absolute left-4 -top-2.5 px-1 bg-zinc-100/50 dark:bg-zinc-800/50 text-sm text-zinc-600 dark:text-zinc-400 transition-all duration-300">
                                        New Project Name
                                    </label>
                                </div>
                            </div>

                            <div id="existing-project-container" class="ml-8 hidden">
                                <div class="relative">
                                    <select id="arxitest_project_id" name="arxitest_project_id"
                                        class="peer h-12 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 pl-4 pr-12 text-zinc-700 dark:text-zinc-200 placeholder-transparent focus:ring-2 focus:ring-indigo-500/50 transition-all duration-300">
                                        <option value="">Select a project...</option>
                                        @foreach($existingProjects as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="absolute left-4 -top-2.5 px-1 bg-zinc-100/50 dark:bg-zinc-800/50 text-sm text-zinc-600 dark:text-zinc-400 transition-all duration-300">
                                        Existing Project
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Import Options -->
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex items-center h-5 mr-3">
                                    <input type="hidden" name="import_epics" value="0">
                                    <input id="import_epics" name="import_epics" type="checkbox" value="1" checked
                                        class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="import_epics" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Import Epics</label>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Import epics as test suites</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex items-center h-5 mr-3">
                                    <input type="hidden" name="import_stories" value="0">
                                    <input id="import_stories" name="import_stories" type="checkbox" value="1" checked
                                        class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="import_stories" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Import Stories</label>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Import stories, tasks, and bugs</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex items-center h-5 mr-3">
                                    <input type="hidden" name="generate_test_scripts" value="0">
                                    <input id="generate_test_scripts" name="generate_test_scripts" type="checkbox" value="1"
                                        class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="generate_test_scripts" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Generate Test Scripts</label>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Automatically generate test scripts using AI</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gradient Divider -->
                <div class="my-8 h-px bg-gradient-to-r from-transparent via-zinc-300/70 dark:via-zinc-600/50 to-transparent animate-scale-in-x"></div>

                <!-- Step 3: Advanced Filters -->
                <div class="space-y-6">
                    <div class="animate-fade-in-left delay-200">
                        <h2 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 mb-2 bg-gradient-to-r from-zinc-800 to-zinc-600 dark:from-zinc-200 dark:to-zinc-400 bg-clip-text text-transparent">
                            Step 3: Advanced Filters
                        </h2>
                    </div>

                    <div class="relative">
                        <textarea id="jql_filter" name="jql_filter" rows="2"
                            class="peer h-24 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 pl-4 pr-12 text-zinc-700 dark:text-zinc-200 placeholder-transparent focus:ring-2 focus:ring-indigo-500/50 transition-all duration-300"
                            placeholder="status in (Open, 'In Progress') AND labels = 'frontend'"></textarea>
                        <label class="absolute left-4 -top-2.5 px-1 bg-zinc-100/50 dark:bg-zinc-800/50 text-sm text-zinc-600 dark:text-zinc-400 transition-all duration-300">
                            JQL Filter
                        </label>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="pt-8 flex justify-between items-center border-t border-zinc-200 dark:border-zinc-700">
                    <a href="{{ route('dashboard.integrations.index') }}"
                       class="group inline-flex items-center px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-200 bg-white/70 dark:bg-zinc-800/50 hover:bg-white dark:hover:bg-zinc-700/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                        <i data-lucide="arrow-left" class="mr-2 -ml-1 w-4 h-4 transition-transform group-hover:-translate-x-1"></i>
                        Back to Integrations
                    </a>

                    <div class="flex space-x-3">
                        <button type="submit" id="startImportBtn"
                                class="group relative px-6 py-2.5 text-white bg-gradient-to-r from-gray-600 to-gray-700 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                            <span class="relative z-10 flex items-center">
                                <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                                Start Import
                            </span>
                            <div class="absolute inset-3 bg-gradient-to-r from-gray-700 to-gray-600 rounded-xl blur-md group-hover:blur-lg transition-all duration-300 opacity-0 group-hover:opacity-100"></div>
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
    @keyframes fade-in-down {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fade-in-left {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes scale-in-x {
        from { transform: scaleX(0); }
        to { transform: scaleX(1); }
    }

    .animate-fade-in-down { animation: fade-in-down 0.6s ease-out; }
    .animate-fade-in-left { animation: fade-in-left 0.6s ease-out; }
    .animate-scale-in-x { animation: scale-in-x 0.6s ease-out; }

    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
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
            formData.set('create_new_project', document.getElementById('create_new_project').checked ? 1 : 0);

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
