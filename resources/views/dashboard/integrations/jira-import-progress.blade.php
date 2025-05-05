@extends('layouts.dashboard')

@section('title', 'Jira Import Progress')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.integrations.index') }}" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white">
            Integrations
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('integrations.jira.import.options') }}" class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white">
            Jira Import
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Import Progress</span>
    </li>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white tracking-tight">Import Progress</h1>
        <p class="mt-2 text-zinc-600 dark:text-zinc-400 text-lg">
            Tracking the status of your Jira data import
        </p>
    </div>

    <!-- Progress Card -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <div class="p-6">
            <div class="mb-6 flex items-center">
                <div id="status-indicator" class="w-3 h-3 rounded-full bg-amber-500 animate-pulse mr-2"></div>
                <h2 id="status-title" class="text-xl font-semibold text-zinc-900 dark:text-white">Import in progress...</h2>
            </div>

            <div class="space-y-6">
                <div class="space-y-2">
                    <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400 mb-1">
                        <span>Overall Progress</span>
                        <span id="overallProgressText">0%</span>
                    </div>
                    <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-3">
                        <div id="overallProgressBar" class="bg-indigo-600 dark:bg-indigo-500 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400 mb-1">
                                <span>Epics</span>
                                <span id="epicCount">0</span>
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                                <div id="epicProgressBar" class="bg-emerald-600 dark:bg-emerald-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400 mb-1">
                                <span>Stories</span>
                                <span id="storyCount">0</span>
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                                <div id="storyProgressBar" class="bg-sky-600 dark:bg-sky-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400 mb-1">
                                <span>Test Cases</span>
                                <span id="testCaseCount">0</span>
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                                <div id="testCaseProgressBar" class="bg-amber-600 dark:bg-amber-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400 mb-1">
                                <span>Scripts</span>
                                <span id="scriptCount">0</span>
                            </div>
                            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                                <div id="scriptProgressBar" class="bg-purple-600 dark:bg-purple-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="importStatus" class="p-4 bg-zinc-100 dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-700 dark:text-zinc-300 text-sm">
                    Preparing import...
                </div>

                <div id="importError" class="p-4 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-800 dark:text-red-300 text-sm hidden">
                    Error message will appear here
                </div>

                <div id="importSuccess" class="p-4 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-300 text-sm hidden">
                    <p class="mb-2 font-medium">Import completed successfully!</p>
                    <div id="importStats" class="text-sm"></div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('dashboard.integrations.index') }}" class="btn-secondary">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
                        Back to Integrations
                    </a>

                    <button id="cancelImportBtn" class="btn-secondary">
                        <i data-lucide="x" class="w-4 h-4 mr-1"></i>
                        Cancel Import
                    </button>

                    <a href="#" id="viewProjectBtn" class="btn-primary hidden">
                        <i data-lucide="external-link" class="w-4 h-4 mr-1"></i>
                        View Project
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Details (Shows when complete) -->
    <div id="importDetails" class="mt-8 hidden">
        <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4">Import Details</h2>

        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Count</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">Epics</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white" id="detailEpics">0</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span id="detailEpicsStatus" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                Complete
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">Stories</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white" id="detailStories">0</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span id="detailStoriesStatus" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                Complete
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">Test Cases</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white" id="detailTestCases">0</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span id="detailTestCasesStatus" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                Complete
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">Test Scripts</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white" id="detailScripts">0</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span id="detailScriptsStatus" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                Complete
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
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
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();

        // Get progress elements
        const statusIndicator = document.getElementById('status-indicator');
        const statusTitle = document.getElementById('status-title');
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
        const importStats = document.getElementById('importStats');
        const viewProjectBtn = document.getElementById('viewProjectBtn');
        const cancelImportBtn = document.getElementById('cancelImportBtn');
        const importDetails = document.getElementById('importDetails');

        // Detail table elements
        const detailEpics = document.getElementById('detailEpics');
        const detailStories = document.getElementById('detailStories');
        const detailTestCases = document.getElementById('detailTestCases');
        const detailScripts = document.getElementById('detailScripts');

        // Get project ID from URL parameter if available
        const urlParams = new URLSearchParams(window.location.search);
        let projectId = urlParams.get('project_id');

        // Start progress tracking
        let trackingInterval = setInterval(checkImportProgress, 2000);

        // Cancel import button
        cancelImportBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to cancel the import?')) {
                // TODO: Implement cancel endpoint
                alert('Cancellation not implemented yet');
                clearInterval(trackingInterval);
            }
        });

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

                    // If we have a project ID now, update the URL without refreshing
                    if (data.data.progress.project_id && !projectId) {
                        projectId = data.data.progress.project_id;
                        const newUrl = new URL(window.location);
                        newUrl.searchParams.set('project_id', projectId);
                        window.history.pushState({}, '', newUrl);
                    }

                    // Check if import is completed
                    if (data.data.progress.completed) {
                        clearInterval(trackingInterval);

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

            // Update detail counts as well
            detailEpics.textContent = progress.epics || '0';
            detailStories.textContent = progress.stories || '0';
            detailTestCases.textContent = progress.testCases || '0';
            detailScripts.textContent = progress.testScripts || '0';

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
                if (progress.success) {
                    importStatus.textContent = 'Import completed successfully!';
                    statusTitle.textContent = 'Import Completed';
                    statusIndicator.classList.remove('bg-amber-500', 'animate-pulse');
                    statusIndicator.classList.add('bg-green-500');
                } else {
                    importStatus.textContent = 'Import failed';
                    statusTitle.textContent = 'Import Failed';
                    statusIndicator.classList.remove('bg-amber-500', 'animate-pulse');
                    statusIndicator.classList.add('bg-red-500');
                }
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
            // Update status
            statusTitle.textContent = 'Import Complete!';
            importStatus.classList.add('hidden');
            importSuccess.classList.remove('hidden');
            cancelImportBtn.classList.add('hidden');

            // Update stats
            importStats.innerHTML = `
                Successfully imported <strong>${progress.epics || 0}</strong> epics,
                <strong>${progress.stories || 0}</strong> stories,
                <strong>${progress.testCases || 0}</strong> test cases, and
                <strong>${progress.testScripts || 0}</strong> test scripts.
            `;

            // Show details table
            importDetails.classList.remove('hidden');

            // Show view project button if we have a project ID
            if (projectId) {
                viewProjectBtn.href = '/dashboard/projects/' + projectId;
                viewProjectBtn.classList.remove('hidden');
            }
        }

        function showImportError(errorMessage) {
            statusTitle.textContent = 'Import Failed';
            statusIndicator.classList.remove('bg-amber-500', 'animate-pulse');
            statusIndicator.classList.add('bg-red-500');
            importStatus.classList.add('hidden');
            importError.textContent = errorMessage;
            importError.classList.remove('hidden');
            cancelImportBtn.classList.add('hidden');
        }
    });
</script>
@endpush
