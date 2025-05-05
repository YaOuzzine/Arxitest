@extends('layouts.dashboard')

@section('title', 'Import Progress')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.integrations.index') }}"
            class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white">
            Integrations
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Import Progress</span>
    </li>
@endsection

@section('content')
    <div class="container py-6">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white tracking-tight mb-2">Jira Import Progress</h1>
                <p class="text-zinc-600 dark:text-zinc-400 import-status">Importing data from Jira...</p>
            </div>

            <!-- Progress Card -->
            <div
                class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden mb-8">
                <div class="p-6">
                    <!-- Progress Bar -->
                    <div class="mb-6">
                        <div class="flex justify-between mb-2">
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Progress</span>
                            <span id="progress-percentage" class="text-sm text-zinc-600 dark:text-zinc-400">0%</span>
                        </div>
                        <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                            <div id="progress-bar" class="h-full bg-blue-600 rounded-full transition-all duration-500"
                                style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- Import Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-zinc-50 dark:bg-zinc-900 p-3 rounded-lg">
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">Epics</div>
                            <div id="epics-count" class="text-2xl font-semibold text-zinc-900 dark:text-white">0</div>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-900 p-3 rounded-lg">
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">Stories</div>
                            <div id="stories-count" class="text-2xl font-semibold text-zinc-900 dark:text-white">0</div>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-900 p-3 rounded-lg">
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">Test Cases</div>
                            <div id="test-cases-count" class="text-2xl font-semibold text-zinc-900 dark:text-white">0</div>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-900 p-3 rounded-lg">
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">Test Scripts</div>
                            <div id="test-scripts-count" class="text-2xl font-semibold text-zinc-900 dark:text-white">0
                            </div>
                        </div>
                    </div>

                    <!-- Time Information -->
                    <div class="flex flex-col sm:flex-row justify-between text-sm text-zinc-600 dark:text-zinc-400">
                        <div>Started: <span id="start-time">Just now</span></div>
                        <div>Time Elapsed: <span id="elapsed-time">00:00</span></div>
                    </div>
                </div>
            </div>

            <!-- Import Messages -->
            <div id="import-messages" class="space-y-4">
                <div class="flex items-center text-zinc-600 dark:text-zinc-400">
                    <div class="animate-pulse mr-3">
                        <i data-lucide="loader-2" class="w-5 h-5"></i>
                    </div>
                    <span>Preparing import process...</span>
                </div>
            </div>

            <!-- Success State (Hidden by default) -->
            <div id="success-state"
                class="hidden mt-8 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-6 text-center">
                <div class="mb-4">
                    <div
                        class="mx-auto w-16 h-16 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                        <i data-lucide="check" class="w-8 h-8 text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-green-700 dark:text-green-400 mb-2">Import Completed Successfully</h3>
                <p class="text-green-600 dark:text-green-300 mb-4" id="success-message">
                    All items have been imported successfully.
                </p>
                <div class="space-x-4">
                    <a href="#" id="view-project-link"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        View Project
                    </a>
                    <a href="{{ route('dashboard.integrations.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Back to Integrations
                    </a>
                </div>
            </div>

            <!-- Error State (Hidden by default) -->
            <div id="error-state"
                class="hidden mt-8 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6 text-center">
                <div class="mb-4">
                    <div class="mx-auto w-16 h-16 bg-red-100 dark:bg-red-800 rounded-full flex items-center justify-center">
                        <i data-lucide="alert-circle" class="w-8 h-8 text-red-600 dark:text-red-400"></i>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-red-700 dark:text-red-400 mb-2">Import Failed</h3>
                <p class="text-red-600 dark:text-red-300 mb-6" id="error-message">
                    An error occurred during the import process.
                </p>
                <div class="space-x-4">
                    <a href="{{ route('integrations.jira.import.options') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Try Again
                    </a>
                    <a href="{{ route('dashboard.integrations.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Back to Integrations
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden input with project ID from URL -->
    <input type="hidden" id="project-id" value="{{ $project_id ?? request()->query('project_id', '') }}">
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Lucide icons
            lucide.createIcons();

            // Elements
            const projectId = document.getElementById('project-id').value;
            const progressBar = document.getElementById('progress-bar');
            const progressPercentage = document.getElementById('progress-percentage');
            const epicsCount = document.getElementById('epics-count');
            const storiesCount = document.getElementById('stories-count');
            const testCasesCount = document.getElementById('test-cases-count');
            const testScriptsCount = document.getElementById('test-scripts-count');
            const startTime = document.getElementById('start-time');
            const elapsedTime = document.getElementById('elapsed-time');
            const importMessages = document.getElementById('import-messages');
            const successState = document.getElementById('success-state');
            const errorState = document.getElementById('error-state');
            const errorMessage = document.getElementById('error-message');
            const successMessage = document.getElementById('success-message');
            const viewProjectLink = document.getElementById('view-project-link');
            const importStatus = document.querySelector('.import-status');

            // Check if we have a project ID
            if (!projectId) {
                importStatus.textContent = 'Error: No project ID provided';
                addLogMessage('Error: No project ID provided', 'error');
                return;
            }

            // Update the view project link
            viewProjectLink.href = `/dashboard/projects/${projectId}`;

            // Set last message timestamp to track new messages
            let lastMessageTimestamp = 0;

            // Start polling for progress
            let pollInterval = 2000; // Start with 2 seconds
            let completed = false;
            let startedAt = new Date();
            let elapsedTimer;

            // Update elapsed time display
            function updateElapsedTime() {
                const now = new Date();
                const diff = Math.floor((now - startedAt) / 1000); // seconds

                const minutes = Math.floor(diff / 60);
                const seconds = diff % 60;
                elapsedTime.textContent =
                    `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }

            // Add a message to the log
            function addLogMessage(message, type = 'info') {
                const messageEl = document.createElement('div');
                messageEl.className = 'flex items-center';

                let iconName = 'info';
                let colorClass = 'text-blue-600 dark:text-blue-400';

                switch (type) {
                    case 'success':
                        iconName = 'check-circle';
                        colorClass = 'text-green-600 dark:text-green-400';
                        break;
                    case 'error':
                        iconName = 'alert-circle';
                        colorClass = 'text-red-600 dark:text-red-400';
                        break;
                    case 'warning':
                        iconName = 'alert-triangle';
                        colorClass = 'text-yellow-600 dark:text-yellow-400';
                        break;
                    case 'loading':
                        iconName = 'loader-2';
                        colorClass = 'text-zinc-600 dark:text-zinc-400';
                        messageEl.firstChild?.classList.add('animate-spin');
                        break;
                }

                messageEl.innerHTML = `
                <div class="mr-3 ${type === 'loading' ? 'animate-pulse' : ''}">
                    <i data-lucide="${iconName}" class="w-5 h-5 ${colorClass}"></i>
                </div>
                <span class="${colorClass}">${message}</span>
            `;

                importMessages.appendChild(messageEl);
                lucide.createIcons();

                // Auto-scroll to bottom
                messageEl.scrollIntoView({
                    behavior: 'smooth',
                    block: 'end'
                });
            }

            // Reset messages container
            importMessages.innerHTML = '';
            addLogMessage('Starting import process...', 'loading');

            // Initialize elapsed time updater
            elapsedTimer = setInterval(updateElapsedTime, 1000);

            // Function to check progress
            async function checkProgress() {
                if (completed) return;

                try {
                    const url = `/dashboard/integrations/jira/import/progress/${projectId}`;
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.json();

                    if (!data.success) {
                        throw new Error('Failed to fetch progress data');
                    }

                    const progress = data.data.progress;

                    // Update counters
                    epicsCount.textContent = progress.epics || 0;
                    storiesCount.textContent = progress.stories || 0;
                    testCasesCount.textContent = progress.testCases || 0;
                    testScriptsCount.textContent = progress.testScripts || 0;

                    // Calculate progress percentage (rough estimation)
                    const total = (progress.epics || 0) + (progress.stories || 0) +
                        (progress.testCases || 0) + (progress.testScripts || 0);

                    // Calculate percentage - we don't know the total in advance,
                    // so we'll just show progress but cap at 90% until completed
                    let percentage = progress.completed ? 100 : Math.min(90, total);
                    progressBar.style.width = `${percentage}%`;
                    progressPercentage.textContent = `${percentage}%`;

                    // Update status text
                    if (total === 0 && !progress.completed) {
                        importStatus.textContent = 'Preparing import...';
                    } else if (!progress.completed) {
                        importStatus.textContent = `Importing data from Jira... (${total} items so far)`;
                    }

                    // If we have elapsed time from the server, update the display
                    if (progress.elapsed_time) {
                        elapsedTime.textContent = progress.elapsed_time;
                    }

                    // If we have start time from the server
                    if (progress.start_time) {
                        const date = new Date(progress.start_time * 1000);
                        startTime.textContent = date.toLocaleTimeString();

                        // Update our local start time to match
                        if (!startedAt || startedAt > date) {
                            startedAt = date;
                        }
                    }

                    // Handle completion
                    if (progress.completed) {
                        completed = true;
                        clearInterval(elapsedTimer);

                        if (progress.success) {
                            // Show success state
                            successState.classList.remove('hidden');
                            importStatus.textContent = 'Import completed successfully!';
                            importStatus.className = 'text-green-600 dark:text-green-400 import-status';

                            // Update success message with stats
                            const epicStr = progress.stats?.epicCount === 1 ? 'epic' : 'epics';
                            const storyStr = progress.stats?.storyCount === 1 ? 'story' : 'stories';
                            const caseStr = progress.stats?.testCaseCount === 1 ? 'test case' : 'test cases';
                            const scriptStr = progress.stats?.testScriptCount === 1 ? 'test script' :
                                'test scripts';

                            const statsMsg =
                                `Successfully imported ${progress.stats?.epicCount || 0} ${epicStr}, ${progress.stats?.storyCount || 0} ${storyStr}, ${progress.stats?.testCaseCount || 0} ${caseStr}, and generated ${progress.stats?.testScriptCount || 0} ${scriptStr}.`;
                            successMessage.textContent = statsMsg;

                            // Add to log
                            addLogMessage('Import process complete!', 'success');
                            addLogMessage(statsMsg, 'success');
                        } else {
                            // Show error state
                            errorState.classList.remove('hidden');
                            importStatus.textContent = 'Import failed';
                            importStatus.className = 'text-red-600 dark:text-red-400 import-status';

                            // Set error message
                            if (progress.error) {
                                errorMessage.textContent = progress.error;
                                addLogMessage(`Error: ${progress.error}`, 'error');
                            }
                        }

                        // Stop polling
                        return;
                    }

                    // Adjust polling interval based on activity
                    // If we're seeing changes, poll more frequently
                    if (total > 0) {
                        pollInterval = 2000; // 2 seconds when active
                    } else {
                        pollInterval = Math.min(pollInterval + 1000,
                            10000); // Gradually increase up to 10 seconds
                    }

                    // Schedule next poll
                    setTimeout(checkProgress, pollInterval);

                } catch (error) {
                    console.error('Error checking progress:', error);
                    addLogMessage(`Error fetching progress: ${error.message}`, 'error');

                    // Continue polling despite errors, but slow down
                    pollInterval = Math.min(pollInterval + 2000, 15000);
                    setTimeout(checkProgress, pollInterval);
                }
            }

            // Start the progress check
            checkProgress();
        });
    </script>
@endpush
