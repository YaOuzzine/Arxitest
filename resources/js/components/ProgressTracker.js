// resources/js/components/ProgressTracker.js
class ProgressTracker {
    constructor() {
        this.activeJobs = {};
        this.trackingIntervals = {};
        this.container = null;
        this.isDarkMode = document.documentElement.classList.contains('dark');
        this.init();
    }

    init() {
        this.createContainer();
        this.loadActiveJobs();

        // Poll for updates every 2 seconds
        setInterval(() => this.checkJobsProgress(), 2000);

        // Make draggable
        this.makeContainerDraggable();

        // Listen for theme changes
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
        }
    }

    addJob(jobId, projectKey) {
        this.activeJobs[jobId] = {
            id: jobId,
            projectKey: projectKey,
            percent: 0,
            message: 'Starting...',
            is_complete: false
        };

        localStorage.setItem('activeJiraImportJobs', JSON.stringify(this.activeJobs));
        this.show();
        this.updateJobsUI();
    }

    updateJobProgress(jobId, progress) {
        if (this.activeJobs[jobId]) {
            this.activeJobs[jobId] = {...this.activeJobs[jobId], ...progress};
            localStorage.setItem('activeJiraImportJobs', JSON.stringify(this.activeJobs));
            this.updateJobsUI();

            // Keep completed jobs visible - they'll be cleared on reload or when user clicks X
            if (progress.is_complete) {
                // Show notification
                this.showNotification(
                    progress.is_success ? 'Import Complete' : 'Import Failed',
                    progress.message,
                    progress.is_success ? 'success' : 'error'
                );
            }
        }
    }

    removeJob(jobId) {
        delete this.activeJobs[jobId];
        localStorage.setItem('activeJiraImportJobs', JSON.stringify(this.activeJobs));

        if (Object.keys(this.activeJobs).length === 0) {
            // Hide only if there are no jobs
            this.hide();
        } else {
            this.updateJobsUI();
        }
    }

    updateJobsUI() {
        const content = document.getElementById('progress-content');
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

    checkJobsProgress() {
        Object.keys(this.activeJobs).forEach(jobId => {
            if (!this.activeJobs[jobId].is_complete) {
                this.fetchJobProgress(jobId);
            }
        });
    }

    fetchJobProgress(jobId) {
        // Construct URL based on base path
        const basePath = window.location.origin;
        const url = `${basePath}/dashboard/integrations/jira/import-progress/${jobId}`;

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateJobProgress(jobId, data.data);
            }
        })
        .catch(error => console.error('Error fetching job progress:', error));
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
}

// Initialize the tracker
document.addEventListener('DOMContentLoaded', function() {
    // Initialize only if not already initialized
    if (!window.progressTracker) {
        window.progressTracker = new ProgressTracker();
    }
});

// Initialize immediately to ensure it's available even before DOMContentLoaded
if (!window.progressTracker) {
    window.progressTracker = new ProgressTracker();
}
