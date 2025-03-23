// resources/js/test-execution.js

document.addEventListener('DOMContentLoaded', function() {
    const executionStatusElement = document.getElementById('execution-status');
    if (!executionStatusElement) return;

    const executionId = executionStatusElement.dataset.executionId;
    const initialStatus = executionStatusElement.dataset.status;

    // Start polling for updates if the execution is running or pending
    if (initialStatus === 'Running' || initialStatus === 'Pending') {
        pollExecutionStatus(executionId);
    }

    // Handle container logs functionality
    setupContainerLogs();

    // Handle duration updates for running tests
    updateRunningDuration();
});

/**
 * Poll the server for execution status updates
 */
function pollExecutionStatus(executionId) {
    // Poll every 5 seconds
    const interval = setInterval(function() {
        fetch(`/api/test-executions/${executionId}/status`)
            .then(response => response.json())
            .then(data => {
                // Update the execution status in the UI
                updateExecutionStatus(data);

                // If execution is no longer running, stop polling and reload
                if (data.status !== 'Running' && data.status !== 'Pending') {
                    clearInterval(interval);

                    // Wait a moment then reload to show full results
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error polling execution status:', error);
            });
    }, 5000);
}

/**
 * Update the execution status display
 */
function updateExecutionStatus(data) {
    const statusElement = document.getElementById('status-badge');
    if (!statusElement) return;

    // Update the badge class
    statusElement.className = 'px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full';

    switch (data.status) {
        case 'Passed':
            statusElement.textContent = 'Passed';
            statusElement.classList.add('bg-green-100', 'text-green-800');
            break;
        case 'Failed':
            statusElement.textContent = 'Failed';
            statusElement.classList.add('bg-red-100', 'text-red-800');
            break;
        case 'Running':
            statusElement.textContent = 'Running';
            statusElement.classList.add('bg-blue-100', 'text-blue-800');
            break;
        case 'Pending':
            statusElement.textContent = 'Pending';
            statusElement.classList.add('bg-yellow-100', 'text-yellow-800');
            break;
        case 'Cancelled':
            statusElement.textContent = 'Cancelled';
            statusElement.classList.add('bg-gray-100', 'text-gray-800');
            break;
        default:
            statusElement.textContent = data.status;
            statusElement.classList.add('bg-gray-100', 'text-gray-800');
    }

    // Update running time if available
    if (data.running_time) {
        const runningTime = document.getElementById('running-duration');
        if (runningTime) {
            runningTime.textContent = formatDuration(data.running_time);
        }
    }
}

/**
 * Format seconds into a readable duration
 */
function formatDuration(seconds) {
    if (seconds < 60) {
        return seconds + ' seconds';
    } else if (seconds < 3600) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return minutes + ' min ' + remainingSeconds + ' sec';
    } else {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return hours + ' hr ' + minutes + ' min';
    }
}

/**
 * Setup container logs modal functionality
 */
function setupContainerLogs() {
    // Define global functions for the container logs modal
    window.showContainerLogs = function(containerId) {
        const modal = document.getElementById('logs-modal');
        const logsContainer = document.getElementById('container-logs');

        if (!modal || !logsContainer) return;

        modal.classList.remove('hidden');
        logsContainer.textContent = 'Loading logs...';

        fetch(`/api/containers/${containerId}/logs`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    logsContainer.textContent = 'Error loading logs: ' + data.error;
                } else {
                    logsContainer.textContent = data.logs || 'No logs available';
                }
            })
            .catch(error => {
                logsContainer.textContent = 'Error loading logs: ' + error.message;
            });
    };

    window.hideLogsModal = function() {
        const modal = document.getElementById('logs-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.downloadLogs = function() {
        const logs = document.getElementById('container-logs')?.textContent;
        if (!logs) return;

        const blob = new Blob([logs], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'container-logs.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };

    // Close modal when clicking outside
    const modal = document.getElementById('logs-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                hideLogsModal();
            }
        });
    }
}

/**
 * Update the running duration for active tests
 */
function updateRunningDuration() {
    const runningDuration = document.getElementById('running-duration');
    if (!runningDuration || !runningDuration.dataset.start) return;

    const startTime = parseInt(runningDuration.dataset.start);

    function updateDuration() {
        const now = Math.floor(Date.now() / 1000);
        const seconds = now - startTime;
        runningDuration.textContent = formatDuration(seconds);
    }

    // Initial update
    updateDuration();

    // Update every second
    setInterval(updateDuration, 1000);
}
