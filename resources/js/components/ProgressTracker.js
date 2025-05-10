// resources/js/components/ProgressTracker.js
class ProgressTracker {
    constructor() {
        this.activeJobs = {};
        this.trackingIntervals = {};
        this.container = null;
        this.isDarkMode = document.documentElement.classList.contains('dark');
        this.retryLimits = {}; // Track retry attempts per job
        this.init();
    }

    init() {
        this.createContainer();
        this.loadActiveJobs();

        // Set up theme listener
        this.setupThemeListener();
    }

    setupThemeListener() {
        // Watch for theme changes using MutationObserver
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class') {
                    this.isDarkMode = document.documentElement.classList.contains('dark');
                    this.applyTheme();
                }
            });
        });

        observer.observe(document.documentElement, { attributes: true });
    }

    applyTheme() {
        if (!this.container) return;

        if (this.isDarkMode) {
            this.container.classList.add('dark-theme');
            this.container.classList.remove('light-theme');
        } else {
            this.container.classList.add('light-theme');
            this.container.classList.remove('dark-theme');
        }
    }

    createContainer() {
        // Remove existing container if any
        const existingContainer = document.getElementById('progress-tracker-container');
        if (existingContainer) {
            existingContainer.remove();
        }

        // Create new container
        this.container = document.createElement('div');
        this.container.id = 'progress-tracker-container';
        this.container.classList.add(this.isDarkMode ? 'dark-theme' : 'light-theme');

        // Container styles
        const containerStyles = document.createElement('style');
        containerStyles.textContent = `
            #progress-tracker-container {
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 300px;
                z-index: 9999;
                border-radius: 8px;
                overflow: hidden;
                font-family: sans-serif;
                transition: transform 0.3s ease, opacity 0.3s ease;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }

            #progress-tracker-container.minimized #progress-content {
                display: none;
            }

            #progress-tracker-container.hidden {
                transform: translateY(150%);
                opacity: 0;
            }

            #progress-tracker-container.light-theme {
                background: #ffffff;
                color: #1f2937;
                border: 1px solid #e5e7eb;
            }

            #progress-tracker-container.dark-theme {
                background: #1f2937;
                color: #e5e7eb;
                border: 1px solid #374151;
            }

            #progress-tracker-header {
                padding: 10px 15px;
                cursor: move;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            #progress-tracker-container.light-theme #progress-tracker-header {
                background: #f3f4f6;
                border-bottom: 1px solid #e5e7eb;
            }

            #progress-tracker-container.dark-theme #progress-tracker-header {
                background: #111827;
                border-bottom: 1px solid #374151;
            }

            #progress-content {
                padding: 15px;
                max-height: 300px;
                overflow-y: auto;
            }

            .progress-item {
                margin-bottom: 15px;
                padding-bottom: 15px;
            }

            #progress-tracker-container.light-theme .progress-item {
                border-bottom: 1px solid #e5e7eb;
            }

            #progress-tracker-container.dark-theme .progress-item {
                border-bottom: 1px solid #374151;
            }

            .progress-bar-bg {
                width: 100%;
                height: 8px;
                border-radius: 4px;
                overflow: hidden;
            }

            #progress-tracker-container.light-theme .progress-bar-bg {
                background: #e5e7eb;
            }

            #progress-tracker-container.dark-theme .progress-bar-bg {
                background: #374151;
            }

            .progress-bar {
                height: 100%;
                transition: width 0.3s ease;
            }

            .progress-bar.success {
                background: #10B981;
            }

            .progress-bar.error {
                background: #EF4444;
            }

            .progress-bar.loading {
                background: #3B82F6;
            }

            .progress-message {
                margin-top: 8px;
                font-size: 0.875rem;
            }

            #progress-tracker-container.light-theme .progress-message {
                color: #6B7280;
            }

            #progress-tracker-container.dark-theme .progress-message {
                color: #9CA3AF;
            }

            .tracker-btn {
                background: none;
                border: none;
                cursor: pointer;
                padding: 2px 5px;
                color: inherit;
                opacity: 0.7;
                transition: opacity 0.2s;
            }

            .tracker-btn:hover {
                opacity: 1;
            }
        `;
        document.head.appendChild(containerStyles);

        // Add header
        const header = document.createElement('div');
        header.id = 'progress-tracker-header';
        header.innerHTML = `
            <div style="font-weight: bold;">Jira Import Progress</div>
            <div>
                <button id="minimize-progress" class="tracker-btn">−</button>
                <button id="close-progress" class="tracker-btn">×</button>
            </div>
        `;
        this.container.appendChild(header);

        // Add content container
        const content = document.createElement('div');
        content.id = 'progress-content';
        this.container.appendChild(content);

        // Add to DOM
        document.body.appendChild(this.container);

        // Add event listeners
        document.getElementById('minimize-progress').addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleMinimize();
        });

        document.getElementById('close-progress').addEventListener('click', (e) => {
            e.stopPropagation();
            this.hide();
        });

        // Restore visibility state
        const isVisible = localStorage.getItem('progressTrackerVisible') !== 'false';
        const isMinimized = localStorage.getItem('progressTrackerMinimized') === 'true';

        if (isVisible) {
            this.show();
            if (isMinimized) {
                this.container.classList.add('minimized');
            }
        } else {
            this.container.classList.add('hidden');
        }

        // Make container draggable
        this.makeContainerDraggable();
    }

    makeContainerDraggable() {
        const header = document.getElementById('progress-tracker-header');
        let isDragging = false;
        let offsetX, offsetY;

        header.addEventListener('mousedown', (e) => {
            isDragging = true;
            offsetX = e.clientX - this.container.getBoundingClientRect().left;
            offsetY = e.clientY - this.container.getBoundingClientRect().top;
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;

            const x = e.clientX - offsetX;
            const y = e.clientY - offsetY;

            // Constrain to window
            const maxX = window.innerWidth - this.container.offsetWidth;
            const maxY = window.innerHeight - this.container.offsetHeight;

            this.container.style.left = `${Math.max(0, Math.min(x, maxX))}px`;
            this.container.style.top = `${Math.max(0, Math.min(y, maxY))}px`;
            this.container.style.right = 'auto';
            this.container.style.bottom = 'auto';
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;

            // Save position
            if (this.container.style.left) {
                const position = {
                    left: this.container.style.left,
                    top: this.container.style.top
                };
                localStorage.setItem('progressTrackerPosition', JSON.stringify(position));
            }
        });

        // Restore position if saved
        const savedPosition = JSON.parse(localStorage.getItem('progressTrackerPosition'));
        if (savedPosition) {
            this.container.style.left = savedPosition.left;
            this.container.style.top = savedPosition.top;
            this.container.style.right = 'auto';
            this.container.style.bottom = 'auto';
        }
    }

    toggleMinimize() {
        this.container.classList.toggle('minimized');
        localStorage.setItem('progressTrackerMinimized', this.container.classList.contains('minimized'));
    }

    show() {
        this.container.classList.remove('hidden');
        localStorage.setItem('progressTrackerVisible', 'true');
    }

    hide() {
        this.container.classList.add('hidden');
        localStorage.setItem('progressTrackerVisible', 'false');
    }

    loadActiveJobs() {
        // Check local storage for active jobs
        const activeJobs = JSON.parse(localStorage.getItem('activeJiraImportJobs') || '{}');
        this.activeJobs = activeJobs;

        // Update UI if there are active jobs
        if (Object.keys(this.activeJobs).length > 0) {
            this.show();
            this.updateJobsUI();

            // Start checking progress for any active jobs
            Object.keys(this.activeJobs).forEach(jobId => {
                if (!this.activeJobs[jobId].is_complete) {
                    this.fetchJobProgress(jobId);

                    // Set up interval for automatic checking
                    if (!this.trackingIntervals[jobId]) {
                        this.trackingIntervals[jobId] = setInterval(() => {
                            this.fetchJobProgress(jobId);
                        }, 3000);
                    }
                }
            });
        }
    }

    addJob(jobId, projectKey) {
        this.activeJobs[jobId] = {
            id: jobId,
            projectKey: projectKey,
            percent: 0,
            message: 'Starting...',
            is_complete: false,
            started_at: Date.now()
        };

        localStorage.setItem('activeJiraImportJobs', JSON.stringify(this.activeJobs));
        this.show();
        this.updateJobsUI();

        // Reset retry count for new job
        this.retryLimits[jobId] = 0;

        // Start tracking this job
        this.fetchJobProgress(jobId);

        // Set up interval for automatic checking
        if (!this.trackingIntervals[jobId]) {
            this.trackingIntervals[jobId] = setInterval(() => {
                this.fetchJobProgress(jobId);
            }, 3000);
        }
    }

    updateJobProgress(jobId, progress) {
        if (this.activeJobs[jobId]) {
            this.activeJobs[jobId] = { ...this.activeJobs[jobId], ...progress };
            localStorage.setItem('activeJiraImportJobs', JSON.stringify(this.activeJobs));
            this.updateJobsUI();

            // If job is complete, clear the interval
            if (progress.is_complete) {
                // Show notification
                this.showNotification(
                    progress.is_success ? 'Import Complete' : 'Import Failed',
                    progress.message,
                    progress.is_success ? 'success' : 'error'
                );

                // Clear the tracking interval
                if (this.trackingIntervals[jobId]) {
                    clearInterval(this.trackingIntervals[jobId]);
                    delete this.trackingIntervals[jobId];
                }
            }
        }
    }

    removeJob(jobId) {
        delete this.activeJobs[jobId];
        localStorage.setItem('activeJiraImportJobs', JSON.stringify(this.activeJobs));

        // Clear any tracking interval
        if (this.trackingIntervals[jobId]) {
            clearInterval(this.trackingIntervals[jobId]);
            delete this.trackingIntervals[jobId];
        }

        // Also clear retry counter
        if (this.retryLimits[jobId]) {
            delete this.retryLimits[jobId];
        }

        if (Object.keys(this.activeJobs).length === 0) {
            // Hide only if there are no jobs
            this.hide();
        } else {
            this.updateJobsUI();
        }
    }

    updateJobsUI() {
        const content = document.getElementById('progress-content');
        if (!content) return;

        content.innerHTML = '';

        Object.values(this.activeJobs).forEach(job => {
            const jobEl = document.createElement('div');
            jobEl.className = 'progress-item';

            const statusClass = job.is_complete
                ? (job.is_success ? 'success' : 'error')
                : 'loading';

            jobEl.innerHTML = `
                <div style="margin-bottom: 8px; display: flex; justify-content: space-between;">
                    <div style="font-weight: bold;">${job.projectKey}</div>
                    <div>${job.percent}%</div>
                </div>
                <div class="progress-bar-bg">
                    <div class="progress-bar ${statusClass}" style="width: ${job.percent}%;"></div>
                </div>
                <div class="progress-message">${job.message}</div>
                ${job.is_complete ? '<button class="remove-job tracker-btn" data-job-id="' + job.id + '">Clear</button>' : ''}
            `;

            content.appendChild(jobEl);
        });

        // Add event listeners to remove buttons
        document.querySelectorAll('.remove-job').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const jobId = e.target.dataset.jobId;
                this.removeJob(jobId);
            });
        });
    }

    fetchJobProgress(jobId) {
        // Check if we have an active job with this ID
        if (!this.activeJobs[jobId]) return;

        // Don't poll if the job is already complete
        if (this.activeJobs[jobId].is_complete) return;

        // Check if we've exceeded retry limit (5 attempts)
        if (this.retryLimits[jobId] >= 5) {
            console.warn(`Giving up on progress tracking for job ${jobId} after multiple failures`);

            // Mark as failed in UI
            this.updateJobProgress(jobId, {
                is_complete: true,
                is_success: false,
                message: 'Failed to track progress: the server is not responding',
                percent: 100
            });

            // Clear the interval to stop polling
            if (this.trackingIntervals[jobId]) {
                clearInterval(this.trackingIntervals[jobId]);
                delete this.trackingIntervals[jobId];
            }
            return;
        }

        // Use the API endpoint that guarantees JSON response
        const url = `/api/jira/import/progress/${encodeURIComponent(jobId)}`;

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                // First check if response is OK
                if (!response.ok) {
                    throw new Error(`Server returned status: ${response.status}`);
                }

                return response.json();
            })
            .then(data => {
                // Reset retry counter on success
                this.retryLimits[jobId] = 0;

                if (data.success) {
                    this.updateJobProgress(jobId, data.data);

                    // If job is complete, clear the interval
                    if (data.data.is_complete) {
                        console.log('Job completed, clearing interval', jobId);
                        if (this.trackingIntervals[jobId]) {
                            clearInterval(this.trackingIntervals[jobId]);
                            delete this.trackingIntervals[jobId];
                        }
                    }
                } else {
                    // Handle error response
                    console.warn('Error in progress data:', data.message);
                    this.retryLimits[jobId] = (this.retryLimits[jobId] || 0) + 1;

                    // If server says job is complete (even with error), stop polling
                    if (data.data && data.data.is_complete) {
                        this.updateJobProgress(jobId, data.data);

                        if (this.trackingIntervals[jobId]) {
                            clearInterval(this.trackingIntervals[jobId]);
                            delete this.trackingIntervals[jobId];
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching job progress:', error);

                // Increment retry counter
                this.retryLimits[jobId] = (this.retryLimits[jobId] || 0) + 1;

                // Update UI with error if we're nearing retry limit
                if (this.retryLimits[jobId] >= 3) {
                    this.updateJobProgress(jobId, {
                        message: 'Having trouble connecting to server...',
                    });
                }
            });
    }

    showNotification(title, message, type) {
        // Check if browser supports notifications
        if (!("Notification" in window)) {
            alert(`${title}: ${message}`);
            return;
        }

        // Check notification permission
        if (Notification.permission === "granted") {
            new Notification(title, { body: message });
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    new Notification(title, { body: message });
                }
            });
        }
    }

    // Format elapsed time for display
    formatDuration(seconds) {
        if (seconds < 60) {
            return `${seconds} seconds`;
        }

        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;

        if (minutes < 60) {
            return `${minutes}m ${remainingSeconds}s`;
        }

        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;

        return `${hours}h ${remainingMinutes}m ${remainingSeconds}s`;
    }
}

// Initialize the tracker
document.addEventListener('DOMContentLoaded', function () {
    // Initialize only if not already initialized
    if (!window.progressTracker) {
        window.progressTracker = new ProgressTracker();
    }
});

// Initialize immediately to ensure it's available even before DOMContentLoaded
if (typeof window !== 'undefined' && !window.progressTracker) {
    window.progressTracker = new ProgressTracker();
}
