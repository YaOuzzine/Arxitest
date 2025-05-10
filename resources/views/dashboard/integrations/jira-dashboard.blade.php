@extends('layouts.dashboard')

@section('title', 'Jira Integration Dashboard')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.integrations.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Integrations</a>
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
            @if($jiraConnected)
                <div class="flex items-center space-x-2 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 py-2 px-4 rounded-md">
                    <div class="animate-pulse w-3 h-3 rounded-full bg-emerald-500"></div>
                    <span>Connected</span>
                </div>
                <form action="{{ route('dashboard.integrations.jira.disconnect') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-secondary text-red-500 dark:text-red-400 hover:text-red-600 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 py-2 px-4 rounded-md flex items-center gap-2">
                        <i data-lucide="plug-off" class="w-4 h-4"></i>
                        <span>Disconnect</span>
                    </button>
                </form>
            @else
                <div class="flex items-center space-x-2 bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 py-2 px-4 rounded-md">
                    <div class="w-3 h-3 rounded-full bg-zinc-400"></div>
                    <span>Not connected</span>
                </div>
                <a href="{{ route('dashboard.integrations.jira.redirect') }}" class="btn-primary py-2 px-4 rounded-md flex items-center gap-2">
                    <i data-lucide="plug" class="w-4 h-4"></i>
                    <span>Connect to Jira</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        @if(!$jiraConnected)
            <!-- Not Connected State -->
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
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
            <div class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/30 p-4 flex items-center gap-3">
                <div class="flex-1">
                    <select id="jira-project-select" class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select a Jira project...</option>
                        @foreach($jiraProjects as $jiraProject)
                            <option value="{{ $jiraProject['key'] }}">{{ $jiraProject['name'] }} ({{ $jiraProject['key'] }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button id="load-project-btn" disabled class="btn-primary py-2 px-4 rounded-md flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i data-lucide="folder-open" class="w-4 h-4"></i>
                        <span>Load Project</span>
                    </button>
                </div>
            </div>

            <!-- Project Explorer UI -->
            <div class="flex h-[calc(100vh-300px)] min-h-[500px]">
                <!-- Left Panel - Project Structure -->
                <div id="jira-project-structure" class="w-1/3 border-r border-zinc-200 dark:border-zinc-700 flex flex-col">
                    <!-- Loading State -->
                    <div id="structure-loading" class="hidden flex-1 flex items-center justify-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-600 dark:border-indigo-400"></div>
                    </div>

                    <!-- Empty State -->
                    <div id="structure-empty" class="flex-1 flex items-center justify-center p-6 text-center">
                        <div>
                            <div class="w-12 h-12 bg-zinc-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mx-auto mb-4">
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

                            <!-- Structure Tree -->
                            <div id="structure-tree" class="space-y-2">
                                <!-- Epics Section -->
                                <div class="category-section">
                                    <div class="flex items-center gap-2 py-2 px-1 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-700/50 rounded-md category-header" data-category="epics">
                                        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-500 dark:text-zinc-400 transition-transform"></i>
                                        <div class="w-5 h-5 bg-purple-100 dark:bg-purple-900/30 rounded-md flex items-center justify-center">
                                            <i data-lucide="mountain" class="w-3 h-3 text-purple-600 dark:text-purple-400"></i>
                                        </div>
                                        <span class="font-medium text-zinc-800 dark:text-zinc-200">Epics</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400 ml-auto" id="epics-count">0</span>
                                    </div>
                                    <div class="pl-8 space-y-1 hidden" id="epics-items"></div>
                                </div>

                                <!-- Unassigned Items Section -->
                                <div class="category-section">
                                    <div class="flex items-center gap-2 py-2 px-1 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-700/50 rounded-md category-header" data-category="unassigned">
                                        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-500 dark:text-zinc-400 transition-transform"></i>
                                        <div class="w-5 h-5 bg-amber-100 dark:bg-amber-900/30 rounded-md flex items-center justify-center">
                                            <i data-lucide="file-question" class="w-3 h-3 text-amber-600 dark:text-amber-400"></i>
                                        </div>
                                        <span class="font-medium text-zinc-800 dark:text-zinc-200">Unassigned Issues</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400 ml-auto" id="unassigned-count">0</span>
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
                            <div class="w-12 h-12 bg-zinc-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="move-right" class="w-6 h-6 text-zinc-500 dark:text-zinc-400"></i>
                            </div>
                            <p class="text-zinc-600 dark:text-zinc-400">
                                Select issues from the project structure to add them here
                            </p>
                        </div>
                    </div>

                    <!-- Import Panel Content -->
                    <div id="import-content" class="hidden flex-1 flex flex-col">
                        <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/30 flex items-center justify-between">
                            <h3 class="font-medium text-zinc-900 dark:text-white">Selected Issues (<span id="selected-count">0</span>)</h3>
                            <button id="clear-selection" class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-red-500 dark:hover:text-red-400">
                                Clear Selection
                            </button>
                        </div>

                        <!-- Selected Issues List -->
                        <div id="selected-issues" class="flex-1 overflow-auto p-4 space-y-2"></div>

                        <!-- Import Options -->
                        <div class="p-4 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/30">
                            <h4 class="font-medium text-zinc-900 dark:text-white mb-3">Import Options</h4>

                            <div class="space-y-4">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <input type="radio" id="create-new-project" name="project-option" value="new" checked>
                                        <label for="create-new-project" class="text-zinc-800 dark:text-zinc-200">Create a new project</label>
                                    </div>
                                    <div class="pl-6">
                                        <input type="text" id="new-project-name" placeholder="New project name" class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <input type="radio" id="use-existing-project" name="project-option" value="existing">
                                        <label for="use-existing-project" class="text-zinc-800 dark:text-zinc-200">Add to existing project</label>
                                    </div>
                                    <div class="pl-6">
                                        <select id="existing-project-select" disabled class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">Select a project...</option>
                                            @foreach($existingProjects as $existingProject)
                                                <option value="{{ $existingProject->id }}">{{ $existingProject->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5">
                                <button id="import-button" disabled class="w-full btn-primary py-2.5 px-4 rounded-md flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
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
                                <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-600 dark:border-indigo-400"></div>
                                </div>
                                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-1" id="progress-status">Importing issues...</h2>
                                <p class="text-zinc-600 dark:text-zinc-400" id="progress-detail">Please wait while we import your selected issues</p>
                            </div>

                            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2.5 mb-4">
                                <div id="progress-bar" class="bg-indigo-600 h-2.5 rounded-full" style="width: 0%"></div>
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
                            <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
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
    lucide.createIcons();

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

    // Functions
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
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                projectData = data.data;
                renderProjectStructure(projectData);
                structureLoading.classList.add('hidden');
                structureContent.classList.remove('hidden');
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

        // Render epics
        epicsCount.textContent = data.epics.length;
        epicsItems.innerHTML = '';

        data.epics.forEach(epic => {
            const epicItem = createIssueItem(epic, 'epic');
            epicsItems.appendChild(epicItem);
        });

        // Render unassigned issues
        unassignedCount.textContent = data.unassigned.length;
        unassignedItems.innerHTML = '';

        data.unassigned.forEach(issue => {
            const issueItem = createIssueItem(issue, issue.fields.issuetype.name.toLowerCase());
            unassignedItems.appendChild(issueItem);
        });
    }

    function createIssueItem(issue, type) {
        const container = document.createElement('div');

        // Determine icon based on issue type
        let typeIcon, typeColor;
        switch (type) {
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
        container.className = 'jira-issue-item';
        container.dataset.key = issueKey;
        container.innerHTML = `
            <div class="w-5 h-5 bg-${typeColor}-100 dark:bg-${typeColor}-900/30 rounded-md flex items-center justify-center mt-0.5">
                <i data-lucide="${typeIcon}" class="w-3 h-3 text-${typeColor}-600 dark:text-${typeColor}-400"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-medium text-sm truncate">${issueSummary}</div>
                <div class="text-xs text-zinc-500 dark:text-zinc-400 flex items-center gap-1">
                    ${issueKey}
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-${getStatusColor(issue.fields.status.name)}"></span>
                    ${issue.fields.status.name}
                </div>
            </div>
            <div class="flex-shrink-0">
                <button class="select-issue-btn w-5 h-5 rounded border border-zinc-300 dark:border-zinc-600 flex items-center justify-center">
                    <i data-lucide="plus" class="w-3 h-3 text-zinc-500 dark:text-zinc-400"></i>
                </button>
            </div>
        `;

        // Handle click event
        container.querySelector('.select-issue-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            toggleIssueSelection(issueKey, container);
        });

        // Refresh Lucide icons
        setTimeout(() => lucide.createIcons({ scope: container }), 0);

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

    function toggleIssueSelection(issueKey, itemElement) {
        if (selectedIssues.has(issueKey)) {
            selectedIssues.delete(issueKey);
            itemElement.classList.remove('selected');
            itemElement.querySelector('.select-issue-btn i').setAttribute('data-lucide', 'plus');
        } else {
            selectedIssues.add(issueKey);
            itemElement.classList.add('selected');
            itemElement.querySelector('.select-issue-btn i').setAttribute('data-lucide', 'check');
        }

        // Refresh Lucide icons
        lucide.createIcons({ scope: itemElement });

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
                issueItem.className = 'flex items-center gap-2 py-2 px-3 rounded-md bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700';
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
                const issueItem = document.querySelector(`.jira-issue-item[data-key="${key}"]`);
                if (issueItem) {
                    issueItem.classList.remove('selected');
                    const icon = issueItem.querySelector('.select-issue-btn i');
                    icon.setAttribute('data-lucide', 'plus');
                    lucide.createIcons({ scope: issueItem });
                }

                updateSelectedUI();
            });

            // Refresh Lucide icons
            setTimeout(() => lucide.createIcons({ scope: btn }), 0);
        });

        // Refresh Lucide icons
        setTimeout(() => lucide.createIcons({ scope: selectedIssuesList }), 0);
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
            lucide.createIcons({ scope: item });
        });

        updateSelectedUI();
    }

    function startImport() {
        if (selectedIssues.size === 0) return;

        const createNew = createNewProjectRadio.checked;
        const projectData = {
            project_key: currentProject,
            create_new_project: createNew,
            issues: Array.from(selectedIssues)
        };

        if (createNew) {
            projectData.new_project_name = newProjectNameInput.value;
            if (!projectData.new_project_name) {
                alert('Please enter a name for the new project');
                return;
            }
        } else {
            projectData.arxitest_project_id = existingProjectSelect.value;
            if (!projectData.arxitest_project_id) {
                alert('Please select an existing project');
                return;
            }
        }

        // Show progress UI
        importContent.classList.add('hidden');
        importProgress.classList.remove('hidden');

        // Simulate progress for now (in a real app, this would be updated via AJAX)
        let progress = 0;
        const totalIssues = selectedIssues.size;
        const interval = setInterval(() => {
            progress += 5;
            if (progress > 100) {
                clearInterval(interval);
                completeImport(projectData);
                return;
            }

            const processedIssues = Math.floor((progress / 100) * totalIssues);
            progressBar.style.width = `${progress}%`;
            progressPercent.textContent = `${progress}%`;
            progressCount.textContent = `${processedIssues}/${totalIssues}`;

            if (progress < 30) {
                progressStatus.textContent = 'Preparing import...';
                progressDetail.textContent = 'Setting up project structure';
            } else if (progress < 60) {
                progressStatus.textContent = 'Importing issues...';
                progressDetail.textContent = `Processing issue ${processedIssues} of ${totalIssues}`;
            } else {
                progressStatus.textContent = 'Finalizing import...';
                progressDetail.textContent = 'Creating relationships between issues';
            }
        }, 100);

        // In a real app, you would make an AJAX call here instead of the simulation above
        /*
        fetch('{{ route('dashboard.integrations.jira.import-issues') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(projectData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                completeImport(data.data);
            } else {
                throw new Error(data.message || 'Import failed');
            }
        })
        .catch(error => {
            console.error('Error importing issues:', error);
            alert('Error importing issues: ' + error.message);
            resetImportUI();
        });
        */
    }

    function completeImport(data) {
        // In a real app, 'data' would be the response from the server
        // For now, we'll use the projectData we prepared earlier

        importProgress.classList.add('hidden');
        importComplete.classList.remove('hidden');

        // Create a sample project ID (would come from the server in a real app)
        const projectId = '123e4567-e89b-12d3-a456-426614174000';

        // Update the summary text
        importSummary.textContent = `Successfully imported ${selectedIssues.size} issues to ${data.create_new_project ? 'new project' : 'existing project'}.`;

        // Set up the "View Project" link
        viewProjectLink.href = `/dashboard/projects/${projectId}`;
    }

    function resetImportUI() {
        importComplete.classList.add('hidden');

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
