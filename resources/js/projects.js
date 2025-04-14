document.addEventListener('alpine:init', () => {
    Alpine.data('projectDetails', () => ({
        activeTab: 'overview',
        showDeleteModal: false,
        isDeleting: false,
        showNotification: false,
        notificationType: 'success',
        notificationMessage: '',
        stats: {
            totalTestCases: 0,
            passRate: 0,
            lastExecution: '',
            avgExecutionTime: ''
        },
        init() {
            // Get the root element (assuming '#project-root' is the wrapper div)
            const el = document.getElementById('project-root');
            if (el) {
                this.stats.totalTestCases = parseInt(el.getAttribute('data-total-test-cases'), 10);
                this.stats.passRate = parseInt(el.getAttribute('data-pass-rate'), 10);
                this.stats.lastExecution = el.getAttribute('data-last-execution') || '';
                this.stats.avgExecutionTime = el.getAttribute('data-avg-execution-time') || '';
                // You can get other attributes as needed
            }
        },
        checkDeleteModal() {
            this.isDeleting = false;
            this.showDeleteModal = true;
        },
        async deleteProject() {
            if (this.isDeleting) return;
            this.isDeleting = true;
            try {
                const el = document.getElementById('project-root');
                const projectId = el ? el.getAttribute('data-project-id') : null;
                if (!projectId) throw new Error('No project ID');
                const response = await fetch('/dashboard/projects/' + projectId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json();
                if (response.ok) {
                    window.location.href = '/dashboard/projects';
                } else {
                    this.showNotificationMessage('error', result.message || 'Failed to delete project');
                    this.showDeleteModal = false;
                }
            } catch (error) {
                this.showNotificationMessage('error', 'An error occurred while deleting the project');
                this.showDeleteModal = false;
            } finally {
                this.isDeleting = false;
            }
        },
        showNotificationMessage(type, message) {
            this.notificationType = type;
            this.notificationMessage = message;
            this.showNotification = true;
            setTimeout(() => {
                this.showNotification = false;
            }, 5000);
        },
        hideNotification() {
            this.showNotification = false;
        }
    }));
});
