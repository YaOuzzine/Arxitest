document.addEventListener('alpine:init', () => {
    Alpine.data('test-suiteDetails', () => ({
        showDeleteModal: false,
        deleteSuiteId: null,
        deleteSuiteName: '',
        deleteUrl: '',
        isDeleting: false,
        showNotification: false,
        notificationMessage: '',
        notificationType: 'success', // 'success' or 'error'

        init() {
            // Submit form when selection changes
            this.$watch('selectedProjectId', (value) => {
                this.$el.closest('form').submit()
            })
        },

        toggleDropdown() {
            this.isOpen = !this.isOpen
            if(this.isOpen) {
                this.$nextTick(() => this.$refs.search?.focus())
            }
        },

        closeDropdown() {
            this.isOpen = false
        },

        selectProject(project) {
            this.selectedProjectId = project.id
            this.selectedProjectName = project.name
            this.closeDropdown()
        },

        get filteredProjects() {
            const allProjects = [{ id: '', name: 'All Projects' }, ...this.projects]
            return allProjects.filter(project =>
                project.name.toLowerCase().includes(this.searchQuery.toLowerCase())
            )
        },

        openDeleteModal(id, name, projectId) {
            this.deleteSuiteId = id;
            this.deleteSuiteName = name;
            this.deleteUrl = `/dashboard/projects/test-suites/${projectId}/${id}`;

            if (!projectId) {
                console.error('Project ID is missing for delete action.');
                this.showNotificationMessage('error', 'Cannot delete suite: Project context missing.');
                return;
            }
            this.showDeleteModal = true;
        },

        closeDeleteModal() {
            this.showDeleteModal = false;
            this.deleteSuiteId = null;
            this.deleteSuiteName = '';
            this.deleteUrl = '';
            this.isDeleting = false;
        },

        confirmDelete() {
            if (!this.deleteUrl) return;
            this.isDeleting = true;

            fetch(this.deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (response.ok) {
                    const row = document.getElementById('suite-row-' + this.deleteSuiteId);
                    if (row) {
                        row.style.transition = 'opacity 0.5s ease-out';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 500);
                    }
                    this.showNotificationMessage('success', data.message || 'Test Suite deleted successfully.');
                } else {
                    this.showNotificationMessage('error', data.message || 'Failed to delete Test Suite.');
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                this.showNotificationMessage('error', 'An error occurred while deleting the Test Suite.');
            })
            .finally(() => {
                this.closeDeleteModal();
            });
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
