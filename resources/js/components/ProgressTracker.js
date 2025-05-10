// Progress Tracker
class ProgressTracker {
    constructor() {
        this.jobs = {};
        this.pollIntervals = {};
        this.initialized = false;
        this.minimized = false;
    }

    initialize() {
        if (this.initialized) return;

        // Add event listeners
        document.getElementById('close-progress')?.addEventListener('click', () => {
            this.hide();
            this.clearAllJobs();
        });

        document.getElementById('minimize-progress')?.addEventListener('click', () => {
            this.minimize();
        });

        document.getElementById('restore-progress')?.addEventListener('click', () => {
            this.restore();
        });

        // Load saved jobs from localStorage
        this.loadSavedJobs();

        this.initialized = true;
    }

    loadSavedJobs() {
        const savedJobs = JSON.parse(localStorage.getItem('progressJobs') || '{}');
        const savedTimestamp = parseInt(localStorage.getItem('progressTimestamp') || '0');

        // Only restore jobs that are less than 30 minutes old
        if (Date.now() - savedTimestamp < 30 * 60 * 1000) {
            Object.keys(savedJobs).forEach(jobId => {
                this.addJob(jobId, savedJobs[jobId].name, savedJobs[jobId].type);
            });

            if (Object.keys(this.jobs).length > 0) {
                if (localStorage.getItem('progressMinimized') === 'true') {
                    this.minimize(false); // Don't save state again
                } else {
                    this.show();
                }
            }
        } else {
            // Clear old data
            localStorage.removeItem('progressJobs');
            localStorage.removeItem('progressTimestamp');
            localStorage.removeItem('progressMinimized');
        }
    }

    saveJobs() {
        localStorage.setItem('progressJobs', JSON.stringify(this.jobs));
        localStorage.setItem('progressTimestamp', Date.now().toString());
        localStorage.setItem('progressMinimized', this.minimized.toString());
    }

    addJob(jobId, name, type = 'jira') {
        if (!jobId) return;

        this.jobs[jobId] = {
            id: jobId,
            name: name || 'Background Job',
            progress: 0,
            status: 'Starting...',
            type: type,
            startTime: Date.now()
        };

        this.renderJob(jobId);
        this.saveJobs();
        this.startPolling(jobId);
        this.show();
    }

    updateJobProgress(jobId, data) {
        if (!this.jobs[jobId]) return;

        this.jobs[jobId].progress = data.percent || 0;
        this.jobs[jobId].status = data.message || data.status || 'Processing...';

        if (data.is_complete) {
            this.jobs[jobId].completed = true;
            this.jobs[jobId].success = data.is_success;
            this.stopPolling(jobId);

            // Auto-remove successful jobs after 10 seconds
            if (data.is_success) {
                setTimeout(() => {
                    this.removeJob(jobId);
                }, 10000);
            }
        }

        this.renderJob(jobId);
        this.saveJobs();
    }

    renderJob(jobId) {
        const jobsContainer = document.getElementById('progress-jobs');
        if (!jobsContainer) return;

        let jobElement = document.getElementById(`job-${jobId}`);
        const job = this.jobs[jobId];

        if (!jobElement) {
            jobElement = document.createElement('div');
            jobElement.id = `job-${jobId}`;
            jobElement.className = 'bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-3 border border-zinc-200 dark:border-zinc-700';
            jobsContainer.appendChild(jobElement);
        }

        const elapsed = this.formatTime(Math.floor((Date.now() - job.startTime) / 1000));

        // Choose icon based on job type
        let typeIcon = '';
        if (job.type === 'jira') {
            typeIcon = `<svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"></path></svg>`;
        } else if (job.type === 'github') {
            typeIcon = `<svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>`;
        }

        // Status indicator
        let statusIndicator = `<div class="animate-pulse w-2 h-2 rounded-full bg-blue-500 mr-2"></div>`;

        if (job.completed) {
            if (job.success) {
                statusIndicator = `<div class="w-2 h-2 rounded-full bg-green-500 mr-2"></div>`;
            } else {
                statusIndicator = `<div class="w-2 h-2 rounded-full bg-red-500 mr-2"></div>`;
            }
        }

        jobElement.innerHTML = `
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center text-sm font-medium text-zinc-800 dark:text-zinc-200">
                    ${statusIndicator}
                    ${typeIcon}
                    <span class="truncate max-w-[180px]">${job.name}</span>
                </div>
                <div class="text-xs text-zinc-500">${elapsed}</div>
            </div>
            <div class="text-xs text-zinc-600 dark:text-zinc-400 mb-1.5 truncate" title="${job.status}">${job.status}</div>
            <div class="relative pt-1">
                <div class="overflow-hidden h-1.5 text-xs flex rounded bg-zinc-200 dark:bg-zinc-700">
                    <div style="width:${job.progress}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center ${job.completed ? (job.success ? 'bg-green-500' : 'bg-red-500') : 'bg-blue-500'}"></div>
                </div>
            </div>
        `;

        if (job.completed) {
            const dismissBtn = document.createElement('button');
            dismissBtn.className = 'text-xs mt-2 px-2 py-1 bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600 transition-colors';
            dismissBtn.innerText = 'Dismiss';
            dismissBtn.addEventListener('click', () => this.removeJob(jobId));
            jobElement.appendChild(dismissBtn);
        }
    }

    removeJob(jobId) {
        this.stopPolling(jobId);
        const jobElement = document.getElementById(`job-${jobId}`);
        if (jobElement) {
            jobElement.remove();
        }

        delete this.jobs[jobId];
        this.saveJobs();

        // Hide tracker if no more jobs
        if (Object.keys(this.jobs).length === 0) {
            this.hide();
        }
    }

    clearAllJobs() {
        Object.keys(this.jobs).forEach(jobId => {
            this.stopPolling(jobId);
        });

        this.jobs = {};
        localStorage.removeItem('progressJobs');
        localStorage.removeItem('progressTimestamp');
        localStorage.removeItem('progressMinimized');

        const jobsContainer = document.getElementById('progress-jobs');
        if (jobsContainer) {
            jobsContainer.innerHTML = '';
        }
    }

    startPolling(jobId) {
        this.stopPolling(jobId); // Clear any existing interval

        // Poll different endpoints based on job type
        const jobType = this.jobs[jobId]?.type || 'jira';
        let endpointUrl = '';

        if (jobType === 'jira') {
            endpointUrl = `/dashboard/integrations/jira/import/progress/${jobId}`;
        } else if (jobType === 'github') {
            endpointUrl = `/api/github/job-progress/${jobId}`;
        }

        if (!endpointUrl) return;

        this.pollIntervals[jobId] = setInterval(() => {
            fetch(endpointUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.updateJobProgress(jobId, data.data);
                    }
                })
                .catch(error => console.error(`Error polling job ${jobId}:`, error));
        }, 2000);
    }

    stopPolling(jobId) {
        if (this.pollIntervals[jobId]) {
            clearInterval(this.pollIntervals[jobId]);
            delete this.pollIntervals[jobId];
        }
    }

    show() {
        const tracker = document.getElementById('progress-tracker');
        if (tracker) {
            tracker.classList.remove('hidden');
        }

        document.getElementById('restore-progress')?.classList.add('hidden');
        this.minimized = false;
        this.saveJobs();
    }

    hide() {
        const tracker = document.getElementById('progress-tracker');
        if (tracker) {
            tracker.classList.add('hidden');
        }

        document.getElementById('restore-progress')?.classList.add('hidden');
    }

    minimize(saveState = true) {
        const tracker = document.getElementById('progress-tracker');
        if (tracker) {
            tracker.classList.add('hidden');
        }

        document.getElementById('restore-progress')?.classList.remove('hidden');
        this.minimized = true;

        if (saveState) {
            this.saveJobs();
        }
    }

    restore() {
        const tracker = document.getElementById('progress-tracker');
        if (tracker) {
            tracker.classList.remove('hidden');
        }

        document.getElementById('restore-progress')?.classList.add('hidden');
        this.minimized = false;
        this.saveJobs();
    }

    formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;

        if (minutes < 60) {
            return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
        }

        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;

        return `${hours}:${remainingMinutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
    }
}

// Initialize and expose globally
window.progressTracker = new ProgressTracker();
document.addEventListener('DOMContentLoaded', () => {
    window.progressTracker.initialize();
});
