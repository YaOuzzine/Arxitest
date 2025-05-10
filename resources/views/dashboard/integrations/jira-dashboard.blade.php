@extends('layouts.dashboard')

@section('title', 'Jira Integration Dashboard')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.integrations.index') }}"
            class="text-indigo-600 dark:text-indigo-400 hover:underline">Integrations</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Jira Dashboard</span>
    </li>
@endsection

@section('content')
    <div class="h-full">
        <!-- Header with connection status -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white tracking-tight">Jira Integration Dashboard</h1>
                <p class="text-zinc-600 dark:text-zinc-400 text-lg">
                    Manage your Jira projects, import issues, and sync with existing projects
                </p>
            </div>
            <div class="flex items-center gap-3">
                @if ($jiraConnected)
                    <div
                        class="flex items-center space-x-2 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 py-2 px-4 rounded-md">
                        <div class="animate-pulse w-3 h-3 rounded-full bg-emerald-500"></div>
                        <span>Connected</span>
                    </div>
                    <form action="{{ route('dashboard.integrations.jira.disconnect') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="btn-secondary text-red-500 dark:text-red-400 hover:text-red-600 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 py-2 px-4 rounded-md flex items-center gap-2">
                            <i data-lucide="plug-off" class="w-4 h-4"></i>
                            <span>Disconnect</span>
                        </button>
                    </form>
                @else
                    <div
                        class="flex items-center space-x-2 bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 py-2 px-4 rounded-md">
                        <div class="w-3 h-3 rounded-full bg-zinc-400"></div>
                        <span>Not connected</span>
                    </div>
                    <a href="{{ route('dashboard.integrations.jira.redirect') }}"
                        class="btn-primary py-2 px-4 rounded-md flex items-center gap-2">
                        <i data-lucide="plug" class="w-4 h-4"></i>
                        <span>Connect to Jira</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Main Content Area -->
        <div
            class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            @if (!$jiraConnected)
                <!-- Not Connected State -->
                <div class="p-8 text-center">
                    <div
                        class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="link-2-off" class="w-8 h-8 text-indigo-500 dark:text-indigo-400"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">Connect to Jira</h2>
                    <p class="text-zinc-600 dark:text-zinc-400 max-w-md mx-auto mb-6">
                        To start importing issues from Jira, you'll need to connect your Jira account first.
                    </p>
                    <a href="{{ route('dashboard.integrations.jira.redirect') }}" class="btn-primary py-2 px-6 rounded-md">
                        Connect to Jira
                    </a>
                </div>
            @else
                <!-- Connected - Project Selection and Import UI -->
                <div
                    class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/30 p-4 flex items-center gap-3">
                    <div class="flex-1">
                        <select id="jira-project-select"
                            class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select a Jira project...</option>
                            @foreach ($jiraProjects as $jiraProject)
                                <option value="{{ $jiraProject['key'] }}">{{ $jiraProject['name'] }}
                                    ({{ $jiraProject['key'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button id="load-project-btn" disabled
                            class="btn-primary py-2 px-4 rounded-md flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i data-lucide="folder-open" class="w-4 h-4"></i>
                            <span>Load Project</span>
                        </button>
                    </div>
                </div>

                <!-- Project Explorer UI -->
                <div class="flex h-[calc(100vh-300px)] min-h-[500px]">
                    <!-- Left Panel - Project Structure -->
                    <div id="jira-project-structure"
                        class="w-1/3 border-r border-zinc-200 dark:border-zinc-700 flex flex-col">
                        <!-- Loading State -->
                        <div id="structure-loading" class="hidden flex-1 flex items-center justify-center">
                            <div
                                class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-600 dark:border-indigo-400">
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div id="structure-empty" class="flex-1 flex items-center justify-center p-6 text-center">
                            <div>
                                <div
                                    class="w-12 h-12 bg-zinc-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i data-lucide="folder" class="w-6 h-6 text-zinc-500 dark:text-zinc-400"></i>
                                </div>
                                <p class="text-zinc-600 dark:text-zinc-400">
                                    Select a Jira project to explore its structure
                                </p>
                            </div>
                        </div>

                        <!-- Project Structure Tree -->
                        <div id="structure-content" class="hidden flex-1 overflow-auto">
                            <div class="p-4">
                                <div id="project-info" class="mb-4 pb-3 border-b border-zinc-200 dark:border-zinc-700">
                                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white" id="project-name"></h3>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400" id="project-key"></p>
                                </div>

                                <div class="mb-4 flex justify-between items-center">
                                    <button id="select-all-btn"
                                        class="px-3 py-1 text-sm rounded-md bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-200 dark:hover:bg-indigo-800/30">
                                        Select All Issues
                                    </button>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400" id="total-issues-count">0
                                        issues</span>
                                </div>

                                <!-- Structure Tree -->
                                <div id="structure-tree" class="space-y-2">
                                    <!-- Epics Section -->
                                    <div class="category-section">
                                        <div class="flex items-center gap-2 py-2 px-1 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-700/50 rounded-md category-header"
                                            data-category="epics">
                                            <i data-lucide="chevron-right"
                                                class="w-4 h-4 text-zinc-500 dark:text-zinc-400 transition-transform"></i>
                                            <div
                                                class="w-5 h-5 bg-purple-100 dark:bg-purple-900/30 rounded-md flex items-center justify-center">
                                                <i data-lucide="mountain"
                                                    class="w-3 h-3 text-purple-600 dark:text-purple-400"></i>
                                            </div>
                                            <span class="font-medium text-zinc-800 dark:text-zinc-200">Epics</span>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400 ml-auto"
                                                id="epics-count">0</span>
                                        </div>
                                        <div class="pl-8 space-y-1 hidden" id="epics-items"></div>
                                    </div>

                                    <!-- Unassigned Items Section -->
                                    <div class="category-section">
                                        <div class="flex items-center gap-2 py-2 px-1 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-700/50 rounded-md category-header"
                                            data-category="unassigned">
                                            <i data-lucide="chevron-right"
                                                class="w-4 h-4 text-zinc-500 dark:text-zinc-400 transition-transform"></i>
                                            <div
                                                class="w-5 h-5 bg-amber-100 dark:bg-amber-900/30 rounded-md flex items-center justify-center">
                                                <i data-lucide="file-question"
                                                    class="w-3 h-3 text-amber-600 dark:text-amber-400"></i>
                                            </div>
                                            <span class="font-medium text-zinc-800 dark:text-zinc-200">Unassigned
                                                Issues</span>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400 ml-auto"
                                                id="unassigned-count">0</span>
                                        </div>
                                        <div class="pl-8 space-y-1 hidden" id="unassigned-items"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Panel - Selected Issues & Import Options -->
                    <div id="jira-import-panel" class="w-2/3 flex flex-col">
                        <!-- Import Panel Empty State -->
                        <div id="import-empty" class="flex-1 flex items-center justify-center p-6 text-center">
                            <div>
                                <div
                                    class="w-12 h-12 bg-zinc-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i data-lucide="move-right" class="w-6 h-6 text-zinc-500 dark:text-zinc-400"></i>
                                </div>
                                <p class="text-zinc-600 dark:text-zinc-400">
                                    Select issues from the project structure to add them here
                                </p>
                            </div>
                        </div>

                        <!-- Import Panel Content -->
                        <div id="import-content" class="hidden flex-1 flex flex-col">
                            <div
                                class="p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/30 flex items-center justify-between">
                                <h3 class="font-medium text-zinc-900 dark:text-white">Selected Issues (<span
                                        id="selected-count">0</span>)</h3>
                                <button id="clear-selection"
                                    class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-red-500 dark:hover:text-red-400">
                                    Clear Selection
                                </button>
                            </div>

                            <!-- Selected Issues List -->
                            <div id="selected-issues" class="flex-1 overflow-auto p-4 space-y-2"></div>

                            <!-- Import Options -->
                            <div
                                class="p-4 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/30">
                                <h4 class="font-medium text-zinc-900 dark:text-white mb-3">Import Options</h4>

                                <div class="space-y-4">
                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <input type="radio" id="create-new-project" name="project-option"
                                                value="new" checked>
                                            <label for="create-new-project"
                                                class="text-zinc-800 dark:text-zinc-200">Create a new project</label>
                                        </div>
                                        <div class="pl-6">
                                            <input type="text" id="new-project-name" placeholder="New project name"
                                                class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <input type="radio" id="use-existing-project" name="project-option"
                                                value="existing">
                                            <label for="use-existing-project" class="text-zinc-800 dark:text-zinc-200">Add
                                                to existing project</label>
                                        </div>
                                        <div class="pl-6">
                                            <select id="existing-project-select" disabled
                                                class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="">Select a project...</option>
                                                @foreach ($existingProjects as $existingProject)
                                                    <option value="{{ $existingProject->id }}">{{ $existingProject->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <button id="import-button" disabled
                                        class="w-full btn-primary py-2.5 px-4 rounded-md flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                        <span>Import Selected Issues</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Import Progress (Hidden initially) -->
                        <div id="import-progress" class="hidden flex-1 flex items-center justify-center p-6">
                            <div class="w-full max-w-md">
                                <div class="text-center mb-6">
                                    <div
                                        class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <div
                                            class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-600 dark:border-indigo-400">
                                        </div>
                                    </div>
                                    <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-1" id="progress-status">
                                        Importing issues...</h2>
                                    <p class="text-zinc-600 dark:text-zinc-400" id="progress-detail">Please wait while we
                                        import your selected issues</p>
                                </div>

                                <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2.5 mb-4">
                                    <div id="progress-bar" class="bg-indigo-600 h-2.5 rounded-full" style="width: 0%">
                                    </div>
                                </div>

                                <div class="flex justify-between text-sm text-zinc-500 dark:text-zinc-400">
                                    <span id="progress-percent">0%</span>
                                    <span id="progress-count">0/0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Import Complete (Hidden initially) -->
                        <div id="import-complete" class="hidden flex-1 flex items-center justify-center p-6">
                            <div class="text-center">
                                <div
                                    class="w-16 h-16 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i data-lucide="check" class="w-8 h-8 text-emerald-600 dark:text-emerald-400"></i>
                                </div>
                                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-1">Import Complete!</h2>
                                <p class="text-zinc-600 dark:text-zinc-400 mb-6" id="import-summary"></p>

                                <div class="flex gap-3 justify-center">
                                    <a href="#" id="view-project-link" class="btn-primary py-2 px-6 rounded-md">
                                        View Project
                                    </a>
                                    <button id="import-more-btn" class="btn-secondary py-2 px-6 rounded-md">
                                        Import More
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
        <style>
            .btn-primary {
                @apply inline-flex items-center px-4 py-2 rounded-lg font-medium bg-gradient-to-br from-zinc-800 to-zinc-700 dark:from-zinc-700 dark:to-zinc-600 text-white shadow-sm hover:shadow-md transition-all duration-200 hover:scale-[1.02];
            }

            .btn-secondary {
                @apply inline-flex items-center px-3 py-1.5 rounded-lg font-medium bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors duration-200;
            }

            .jira-issue-item {
                @apply flex items-start gap-2 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 text-zinc-800 dark:text-zinc-200 hover:border-indigo-300 dark:hover:border-indigo-700 transition-all;
            }

            .jira-issue-item.selected {
                @apply bg-indigo-50 dark:bg-indigo-900/20 border-indigo-300 dark:border-indigo-700;
            }

            .category-header[aria-expanded="true"] i:first-child {
                @apply rotate-90;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize Lucide icons


                // Global variables
                let currentProject = null;
                let projectData = null;
                let selectedIssues = new Set();

                // DOM Elements
                const projectSelect = document.getElementById('jira-project-select');
                const loadProjectBtn = document.getElementById('load-project-btn');
                const structureLoading = document.getElementById('structure-loading');
                const structureEmpty = document.getElementById('structure-empty');
                const structureContent = document.getElementById('structure-content');
                const projectInfo = document.getElementById('project-info');
                const projectName = document.getElementById('project-name');
                const projectKey = document.getElementById('project-key');
                const epicsCount = document.getElementById('epics-count');
                const epicsItems = document.getElementById('epics-items');
                const unassignedCount = document.getElementById('unassigned-count');
                const unassignedItems = document.getElementById('unassigned-items');
                const importEmpty = document.getElementById('import-empty');
                const importContent = document.getElementById('import-content');
                const selectedIssuesCount = document.getElementById('selected-count');
                const selectedIssuesList = document.getElementById('selected-issues');
                const clearSelectionBtn = document.getElementById('clear-selection');
                const createNewProjectRadio = document.getElementById('create-new-project');
                const useExistingProjectRadio = document.getElementById('use-existing-project');
                const newProjectNameInput = document.getElementById('new-project-name');
                const existingProjectSelect = document.getElementById('existing-project-select');
                const importButton = document.getElementById('import-button');
                const importProgress = document.getElementById('import-progress');
                const importComplete = document.getElementById('import-complete');
                const progressBar = document.getElementById('progress-bar');
                const progressPercent = document.getElementById('progress-percent');
                const progressCount = document.getElementById('progress-count');
                const progressStatus = document.getElementById('progress-status');
                const progressDetail = document.getElementById('progress-detail');
                const importSummary = document.getElementById('import-summary');
                const viewProjectLink = document.getElementById('view-project-link');
                const importMoreBtn = document.getElementById('import-more-btn');

                // Set up event listeners
                projectSelect.addEventListener('change', function() {
                    loadProjectBtn.disabled = !this.value;
                    if (this.value) {
                        newProjectNameInput.value = this.options[this.selectedIndex].text;
                    }
                });

                loadProjectBtn.addEventListener('click', function() {
                    loadProject(projectSelect.value);
                });

                // Set up toggle for the category headers
                document.querySelectorAll('.category-header').forEach(header => {
                    header.addEventListener('click', function() {
                        const category = this.dataset.category;
                        const itemsContainer = document.getElementById(`${category}-items`);
                        const expanded = this.getAttribute('aria-expanded') === 'true';

                        if (expanded) {
                            this.setAttribute('aria-expanded', 'false');
                            itemsContainer.classList.add('hidden');
                        } else {
                            this.setAttribute('aria-expanded', 'true');
                            itemsContainer.classList.remove('hidden');
                        }
                    });
                });

                document.getElementById('select-all-btn')?.addEventListener('click', function() {
                    if (!projectData) {
                        console.warn('No project data available for select all');
                        return;
                    }

                    // Collect all issue keys that should be added
                    const allIssueKeys = [];

                    // Add epics
                    if (projectData.epics && Array.isArray(projectData.epics)) {
                        projectData.epics.forEach(epic => {
                            if (epic && epic.key) allIssueKeys.push(epic.key);
                        });
                    }

                    // Add stories
                    if (projectData.stories && Array.isArray(projectData.stories)) {
                        projectData.stories.forEach(story => {
                            if (story && story.key) allIssueKeys.push(story.key);
                        });
                    }

                    // Add unassigned
                    if (projectData.unassigned && Array.isArray(projectData.unassigned)) {
                        projectData.unassigned.forEach(issue => {
                            if (issue && issue.key) allIssueKeys.push(issue.key);
                        });
                    }

                    console.log(`Attempting to select ${allIssueKeys.length} issues`);

                    // Expand all categories if they're not already expanded
                    document.querySelectorAll('.category-header').forEach(header => {
                        if (header) {
                            const category = header.dataset.category;
                            const itemsContainer = document.getElementById(`${category}-items`);
                            if (itemsContainer) {
                                header.setAttribute('aria-expanded', 'true');
                                itemsContainer.classList.remove('hidden');
                            }
                        }
                    });

                    // For each issue, find the element and toggle selection
                    allIssueKeys.forEach(key => {
                        const itemElement = document.querySelector(
                            `.jira-issue-item[data-key="${key}"]`);
                        if (itemElement && !selectedIssues.has(key)) {
                            // Add directly to selected issues set
                            selectedIssues.add(key);

                            // Update UI to reflect selection
                            itemElement.classList.add('selected');

                            const iconElement = itemElement.querySelector('.select-issue-btn i');
                            if (iconElement) {
                                iconElement.setAttribute('data-lucide', 'check');
                                lucide.createIcons({
                                    scope: itemElement
                                });
                            }
                        }
                    });

                    // Update selected items display
                    updateSelectedUI();
                });

                clearSelectionBtn.addEventListener('click', clearSelection);

                // Radio button event listeners
                createNewProjectRadio.addEventListener('change', function() {
                    if (this.checked) {
                        newProjectNameInput.disabled = false;
                        existingProjectSelect.disabled = true;
                    }
                });

                useExistingProjectRadio.addEventListener('change', function() {
                    if (this.checked) {
                        newProjectNameInput.disabled = true;
                        existingProjectSelect.disabled = false;
                    }
                });

                // Import button event listener
                importButton.addEventListener('click', function() {
                    startImport();
                });

                // Import more button event listener
                importMoreBtn.addEventListener('click', function() {
                    resetImportUI();
                });

                // Update the loadProject function in jira-dashboard.blade.php
                function loadProject(projectKey) {
                    currentProject = projectKey;
                    selectedIssues.clear();
                    updateSelectedUI();

                    // Show loading state
                    structureEmpty.classList.add('hidden');
                    structureContent.classList.add('hidden');
                    structureLoading.classList.remove('hidden');

                    // Fetch project data
                    fetch(`{{ route('dashboard.integrations.jira.project-details') }}?key=${projectKey}`, {
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`Server returned status: ${response.status}`);
                            }

                            // Check content type to avoid parsing HTML
                            const contentType = response.headers.get('content-type');
                            if (!contentType || !contentType.includes('application/json')) {
                                throw new Error('Response is not JSON');
                            }

                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                if (data.data.loading) {
                                    // Project is being loaded in background
                                    const progress = data.data.progress;

                                    // Add to progress tracker
                                    if (window.progressTracker) {
                                        window.progressTracker.addJob(progress.id, projectKey);
                                        window.progressTracker.show();
                                    }

                                    // Set up polling to check when data is ready
                                    const checkInterval = setInterval(() => {
                                        // Correctly construct the URL with the progress ID
                                        const progressUrl =
                                            `/dashboard/integrations/jira/import/progress/${progress.id}`;

                                        fetch(progressUrl, {
                                                headers: {
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json'
                                                }
                                            })
                                            .then(response => {
                                                if (!response.ok) {
                                                    throw new Error(
                                                        `Server returned status: ${response.status}`
                                                    );
                                                }

                                                // Check content type to avoid parsing HTML
                                                const contentType = response.headers.get(
                                                    'content-type');
                                                if (!contentType || !contentType.includes(
                                                        'application/json')) {
                                                    throw new Error('Response is not JSON');
                                                }

                                                return response.json();
                                            })
                                            .then(progressData => {
                                                if (progressData.success) {
                                                    const progressInfo = progressData.data;

                                                    // If complete and successful, reload the data
                                                    if (progressInfo.is_complete && progressInfo
                                                        .is_success) {
                                                        clearInterval(checkInterval);
                                                        loadProject(projectKey);
                                                    }

                                                    // If complete but failed, show error
                                                    if (progressInfo.is_complete && !progressInfo
                                                        .is_success) {
                                                        clearInterval(checkInterval);
                                                        structureLoading.classList.add('hidden');
                                                        structureEmpty.classList.remove('hidden');
                                                        alert('Error loading project: ' + progressInfo
                                                            .message);
                                                    }
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error checking progress:', error);
                                                // Don't clear interval on first error to keep trying
                                            });
                                    }, 3000);
                                } else {
                                    // Data is already available
                                    projectData = data.data;
                                    renderProjectStructure(projectData);
                                    structureLoading.classList.add('hidden');
                                    structureContent.classList.remove('hidden');
                                }
                            } else {
                                throw new Error(data.message || 'Failed to load project data');
                            }
                        })
                        .catch(error => {
                            console.error('Error loading project:', error);
                            structureLoading.classList.add('hidden');
                            structureEmpty.classList.remove('hidden');
                            alert('Error loading project: ' + error.message);
                        });
                }

                function renderProjectStructure(data) {
                    // Update project info
                    projectName.textContent = data.project.name;
                    projectKey.textContent = data.project.key;

                    // Count total issues
                    const totalIssues = (data.epics?.length || 0) + (data.stories?.length || 0) + (data.unassigned
                        ?.length || 0);
                    document.getElementById('total-issues-count').textContent = `${totalIssues} issues`;

                    // Render epics
                    epicsCount.textContent = data.epics?.length || 0;
                    epicsItems.innerHTML = '';

                    if (data.epics && data.epics.length) {
                        data.epics.forEach(epic => {
                            const epicItem = createIssueItem(epic, 'epic');
                            epicsItems.appendChild(epicItem);
                        });
                    }

                    // Render unassigned issues
                    unassignedCount.textContent = data.unassigned?.length || 0;
                    unassignedItems.innerHTML = '';

                    if (data.unassigned && data.unassigned.length) {
                        data.unassigned.forEach(issue => {
                            const issueItem = createIssueItem(issue, issue.fields.issuetype.name.toLowerCase());
                            unassignedItems.appendChild(issueItem);
                        });
                    }
                }

                // Replace the createIssueItem function with this improved version:
                function createIssueItem(issue, type) {
                    if (!issue || !issue.key || !issue.fields || !issue.fields.summary) {
                        console.warn('Invalid issue data:', issue);
                        return document.createElement('div'); // Return empty div to avoid errors
                    }

                    const container = document.createElement('div');

                    // Determine icon based on issue type
                    let typeIcon, typeColor;
                    const issueTypeLower = (type || '').toLowerCase();

                    switch (issueTypeLower) {
                        case 'epic':
                            typeIcon = 'mountain';
                            typeColor = 'purple';
                            break;
                        case 'story':
                            typeIcon = 'book-open';
                            typeColor = 'blue';
                            break;
                        case 'bug':
                            typeIcon = 'bug';
                            typeColor = 'red';
                            break;
                        case 'task':
                            typeIcon = 'check-square';
                            typeColor = 'green';
                            break;
                        default:
                            typeIcon = 'file-text';
                            typeColor = 'gray';
                    }

                    // Create issue item
                    const issueKey = issue.key;
                    const issueSummary = issue.fields.summary;
                    container.className = 'jira-issue-item cursor-pointer';
                    container.dataset.key = issueKey;

                    // Ensure status exists
                    const statusName = issue.fields.status?.name || 'Unknown';
                    const statusColor = getStatusColor(statusName);

                    container.innerHTML = `
        <div class="w-5 h-5 bg-${typeColor}-100 dark:bg-${typeColor}-900/30 rounded-md flex items-center justify-center mt-0.5">
            <i data-lucide="${typeIcon}" class="w-3 h-3 text-${typeColor}-600 dark:text-${typeColor}-400"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div class="font-medium text-sm truncate">${issueSummary}</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 flex items-center gap-1">
                ${issueKey}
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-${statusColor}"></span>
                ${statusName}
            </div>
        </div>
        <div class="flex-shrink-0">
            <button class="select-issue-btn w-5 h-5 rounded border border-zinc-300 dark:border-zinc-600 flex items-center justify-center">
                <i data-lucide="plus" class="w-3 h-3 text-zinc-500 dark:text-zinc-400"></i>
            </button>
        </div>
    `;

                    // Handle click event for the whole container
                    container.addEventListener('click', function(e) {
                        // Only handle click if not clicking on the button
                        if (!e.target.closest('.select-issue-btn')) {
                            toggleIssueSelection(issueKey, container);
                        }
                    });

                    // Handle click event for the button specifically
                    const selectBtn = container.querySelector('.select-issue-btn');
                    if (selectBtn) {
                        selectBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            toggleIssueSelection(issueKey, container);
                        });
                    }

                    // Refresh Lucide icons
                    setTimeout(() => {
                        try {
                            lucide.createIcons({
                                scope: container
                            });
                        } catch (error) {
                            console.error('Error creating icons:', error);
                        }
                    }, 0);

                    return container;
                }

                function getStatusColor(status) {
                    status = status.toLowerCase();
                    if (status.includes('done') || status.includes('complete')) {
                        return 'emerald-500';
                    } else if (status.includes('progress') || status.includes('review')) {
                        return 'blue-500';
                    } else if (status.includes('todo') || status.includes('backlog')) {
                        return 'zinc-500';
                    } else if (status.includes('block') || status.includes('hold')) {
                        return 'red-500';
                    } else {
                        return 'amber-500';
                    }
                }

                // Updated toggleIssueSelection function
                function toggleIssueSelection(issueKey, itemElement) {
                    // Create a safety check to prevent null reference errors
                    if (!itemElement) {
                        console.warn('Item element is null when trying to toggle selection for key:', issueKey);
                        return;
                    }

                    // Find the icon element first
                    const iconElement = itemElement.querySelector('.select-issue-btn i');
                    if (!iconElement) {
                        console.warn('Icon element not found in item:', issueKey);

                        // Try to auto-fix the element by recreating the button
                        const selectBtn = itemElement.querySelector('.select-issue-btn');
                        if (selectBtn) {
                            selectBtn.innerHTML =
                                '<i data-lucide="plus" class="w-3 h-3 text-zinc-500 dark:text-zinc-400"></i>';

                            // Reinitialize the icon
                            lucide.createIcons({
                                scope: selectBtn
                            });

                            // Now try again with the fixed element
                            toggleIssueSelection(issueKey, itemElement);
                            return;
                        }
                        return;
                    }

                    if (selectedIssues.has(issueKey)) {
                        selectedIssues.delete(issueKey);
                        itemElement.classList.remove('selected');
                        iconElement.setAttribute('data-lucide', 'plus');
                    } else {
                        selectedIssues.add(issueKey);
                        itemElement.classList.add('selected');
                        iconElement.setAttribute('data-lucide', 'check');
                    }

                    // Refresh Lucide icons with error handling
                    try {
                        lucide.createIcons({
                            scope: itemElement
                        });
                    } catch (error) {
                        console.error('Error refreshing icons:', error);
                    }

                    // Update selected issues UI
                    updateSelectedUI();
                }

                function updateSelectedUI() {
                    selectedIssuesCount.textContent = selectedIssues.size;

                    if (selectedIssues.size > 0) {
                        importEmpty.classList.add('hidden');
                        importContent.classList.remove('hidden');
                        importButton.disabled = false;

                        // Render selected issues list
                        renderSelectedIssues();
                    } else {
                        importEmpty.classList.remove('hidden');
                        importContent.classList.add('hidden');
                        importButton.disabled = true;
                    }
                }

                function renderSelectedIssues() {
                    selectedIssuesList.innerHTML = '';

                    // Group issues by type
                    const issuesByType = {
                        'epic': [],
                        'story': [],
                        'task': [],
                        'bug': [],
                        'other': []
                    };

                    selectedIssues.forEach(key => {
                        // Find issue in project data
                        let issue = findIssueByKey(key);
                        if (!issue) return;

                        const type = issue.fields.issuetype.name.toLowerCase();
                        if (type === 'epic') {
                            issuesByType.epic.push(issue);
                        } else if (type === 'story') {
                            issuesByType.story.push(issue);
                        } else if (type === 'task') {
                            issuesByType.task.push(issue);
                        } else if (type === 'bug') {
                            issuesByType.bug.push(issue);
                        } else {
                            issuesByType.other.push(issue);
                        }
                    });

                    // Create sections for each type
                    for (const [type, issues] of Object.entries(issuesByType)) {
                        if (issues.length === 0) continue;

                        // Create section header
                        const sectionHeader = document.createElement('div');
                        sectionHeader.className = 'mb-2 pb-1 border-b border-zinc-200 dark:border-zinc-700';
                        sectionHeader.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200 capitalize">${type}s</span>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">${issues.length}</span>
                </div>
            `;
                        selectedIssuesList.appendChild(sectionHeader);

                        // Create issues list
                        const issuesList = document.createElement('div');
                        issuesList.className = 'space-y-2 mb-4';

                        issues.forEach(issue => {
                            const issueItem = document.createElement('div');
                            issueItem.className =
                                'flex items-center gap-2 py-2 px-3 rounded-md bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700';
                            issueItem.innerHTML = `
                    <div class="flex-1 min-w-0">
                        <div class="text-sm truncate">${issue.fields.summary}</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">${issue.key}</div>
                    </div>
                    <button class="remove-issue-btn text-zinc-400 hover:text-red-500 dark:hover:text-red-400" data-key="${issue.key}">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                `;

                            issuesList.appendChild(issueItem);
                        });

                        selectedIssuesList.appendChild(issuesList);
                    }

                    // Set up remove buttons
                    document.querySelectorAll('.remove-issue-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const key = this.dataset.key;
                            selectedIssues.delete(key);

                            // Update UI for the issue in the structure tree
                            const issueItem = document.querySelector(
                                `.jira-issue-item[data-key="${key}"]`);
                            if (issueItem) {
                                issueItem.classList.remove('selected');
                                const icon = issueItem.querySelector('.select-issue-btn i');
                                icon.setAttribute('data-lucide', 'plus');
                                lucide.createIcons({
                                    scope: issueItem
                                });
                            }

                            updateSelectedUI();
                        });

                        // Refresh Lucide icons
                        setTimeout(() => lucide.createIcons({
                            scope: btn
                        }), 0);
                    });

                    // Refresh Lucide icons
                    setTimeout(() => lucide.createIcons({
                        scope: selectedIssuesList
                    }), 0);
                }

                function findIssueByKey(key) {
                    if (!projectData) return null;

                    // Search in epics
                    const epic = projectData.epics.find(e => e.key === key);
                    if (epic) return epic;

                    // Search in stories
                    const story = projectData.stories.find(s => s.key === key);
                    if (story) return story;

                    // Search in unassigned
                    const unassigned = projectData.unassigned.find(u => u.key === key);
                    if (unassigned) return unassigned;

                    return null;
                }

                function clearSelection() {
                    selectedIssues.clear();

                    // Reset UI in structure tree
                    document.querySelectorAll('.jira-issue-item.selected').forEach(item => {
                        item.classList.remove('selected');
                        const icon = item.querySelector('.select-issue-btn i');
                        icon.setAttribute('data-lucide', 'plus');
                        lucide.createIcons({
                            scope: item
                        });
                    });

                    updateSelectedUI();
                }

                function startImport() {
                    if (selectedIssues.size === 0) return;

                    const createNew = createNewProjectRadio.checked;
                    const importData = {
                        project_key: currentProject,
                        create_new_project: createNew ? 1 : 0,
                        issues: Array.from(selectedIssues)
                    };

                    if (createNew) {
                        importData.new_project_name = newProjectNameInput.value;
                        if (!importData.new_project_name) {
                            alert('Please enter a name for the new project');
                            return;
                        }
                    } else {
                        importData.arxitest_project_id = existingProjectSelect.value;
                        if (!importData.arxitest_project_id) {
                            alert('Please select an existing project');
                            return;
                        }
                    }

                    // Show progress UI
                    importContent.classList.add('hidden');
                    importProgress.classList.remove('hidden');

                    // Initial progress state
                    progressBar.style.width = '0%';
                    progressPercent.textContent = '0%';
                    progressCount.textContent = '0/' + selectedIssues.size;
                    progressStatus.textContent = 'Starting import...';
                    progressDetail.textContent = 'Initializing project';

                    // Make the actual API call to import the issues
                    fetch('{{ route('integrations.jira.import.project') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(importData)
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`Server returned ${response.status}: ${response.statusText}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Store project ID for progress tracking
                                const projectId = data.data.project_id;

                                // Check if there's a job ID for tracking
                                if (data.data.job_id) {
                                    trackImportProgress(data.data.job_id, projectId);
                                } else {
                                    // No job tracking, just complete the import
                                    completeImport(data.data);
                                }
                            } else {
                                throw new Error(data.message || 'Import failed');
                            }
                        })
                        .catch(error => {
                            console.error('Error importing issues:', error);
                            alert('Error importing issues: ' + error.message);
                            resetImportUI();
                        });
                }

                function trackImportProgress(jobId, projectId) {
                    // Add to global progress tracker if available
                    if (window.progressTracker) {
                        window.progressTracker.addJob(jobId, currentProject);
                        window.progressTracker.show();
                    }

                    // Set up local polling for UI updates
                    let checkCount = 0;
                    const maxChecks = 300; // Prevent infinite polling

                    const checkProgress = () => {
                        const progressUrl = `/api/jira/import/progress/${progress.id}`;
                        fetch(progressUrl, {
            headers: {
                'Accept': 'application/json'
            }
        })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Progress check failed');
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    const progress = data.data;

                                    // Update UI with progress info
                                    const percent = progress.percent || 0;
                                    progressBar.style.width = `${percent}%`;
                                    progressPercent.textContent = `${percent}%`;

                                    // Update message
                                    if (progress.message) {
                                        progressDetail.textContent = progress.message;
                                    }

                                    // Count stats if available
                                    if (progress.stats) {
                                        const total = progress.stats.epicCount + progress.stats.storyCount +
                                            progress.stats.testCaseCount;
                                        progressCount.textContent = `${total}/${selectedIssues.size}`;
                                    }

                                    // Check if complete
                                    if (progress.completed) {
                                        if (progress.success) {
                                            completeImport({
                                                project_id: projectId,
                                                imported: progress.stats || {},
                                                create_new_project: createNewProjectRadio.checked
                                            });
                                        } else {
                                            throw new Error(progress.error || 'Import failed');
                                        }
                                        return;
                                    }

                                    // Continue checking if not complete
                                    checkCount++;
                                    if (checkCount < maxChecks) {
                                        setTimeout(checkProgress, 2000);
                                    } else {
                                        console.warn('Max progress checks reached');
                                        // Just show complete anyway
                                        completeImport({
                                            project_id: projectId,
                                            warning: 'Import may still be processing in the background',
                                            create_new_project: createNewProjectRadio.checked
                                        });
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Error checking import progress:', error);
                                checkCount++;
                                // Keep trying a few times even with errors
                                if (checkCount < 5) {
                                    setTimeout(checkProgress, 2000);
                                } else {
                                    alert('Error tracking import progress: ' + error.message);
                                    resetImportUI();
                                }
                            });
                    };

                    // Start checking progress
                    checkProgress();
                }

                function completeImport(data) {
                    importProgress.classList.add('hidden');
                    importComplete.classList.remove('hidden');

                    // Get the actual project ID from the response
                    const projectId = data.project_id;

                    // Update the summary text with actual import stats
                    let summaryText =
                        `Successfully imported issues to ${data.create_new_project ? 'new project' : 'existing project'}.`;

                    // Add stats if available
                    if (data.imported) {
                        const stats = data.imported;
                        const totalItems =
                            (stats.epics || 0) +
                            (stats.stories || 0) +
                            (stats.test_cases || 0);

                        summaryText = `Successfully imported ${totalItems} items: `;

                        const details = [];
                        if (stats.epics) details.push(`${stats.epics} epics`);
                        if (stats.stories) details.push(`${stats.stories} stories`);
                        if (stats.test_cases) details.push(`${stats.test_cases} test cases`);

                        summaryText += details.join(', ') + '.';
                    }

                    // Add warning if any
                    if (data.warning) {
                        summaryText += ' ' + data.warning;
                    }

                    importSummary.textContent = summaryText;

                    // Set up the "View Project" link with actual project ID
                    if (projectId) {
                        viewProjectLink.href = `/dashboard/projects/${projectId}`;
                    } else {
                        // If no project ID, change button text to indicate issue
                        viewProjectLink.textContent = "Back to Dashboard";
                        viewProjectLink.href = "/dashboard";
                    }
                }

                function resetImportUI() {
                    importComplete.classList.add('hidden');
                    importProgress.classList.add('hidden');

                    if (selectedIssues.size > 0) {
                        importContent.classList.remove('hidden');
                    } else {
                        importEmpty.classList.remove('hidden');
                    }

                    // Reset progress elements
                    progressBar.style.width = '0%';
                    progressPercent.textContent = '0%';
                    progressCount.textContent = '0/0';
                }
            });
        </script>
    @endpush
@endsection
