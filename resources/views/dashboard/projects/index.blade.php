@extends('layouts.dashboard')

@section('title', 'Projects')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Projects</span>
    </li>
@endsection

@section('content')
    <div class="h-full" x-data="projectsManager">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Projects</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Manage your test automation projects for {{ $team->name }}
                </p>
            </div>

            <div class="flex items-center space-x-2">
                <div class="relative">
                    <input type="text" id="search-projects" placeholder="Search projects..."
                        class="w-full sm:w-64 pl-10 pr-4 py-2 text-sm rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-zinc-500/30 dark:focus:ring-zinc-500/50 focus:border-zinc-500 dark:focus:border-zinc-500/50">
                    <div class="absolute left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-4 w-4 text-zinc-400 dark:text-zinc-500"></i>
                    </div>
                </div>

                <button id="toggle-view-btn"
                    class="p-2 rounded-md text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700/50 transition-colors">
                    <i data-lucide="layout-grid" class="h-5 w-5"></i>
                </button>

                <a href="{{ route('dashboard.projects.create') }}"
                    class="btn-primary inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                    <i data-lucide="plus" class="mr-2 -ml-1 w-4 h-4"></i>
                    New Project
                </a>
            </div>
        </div>

        <!-- Projects Grid/List Container -->
        <div id="projects-container">
            @if (count($projects) > 0)
                <!-- Grid View (default) -->
                <div id="grid-view" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($projects as $project)
                        <div class="project-card bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden hover:shadow-md hover:-translate-y-1 transition-all duration-300"
                            data-project-id="{{ $project->id }}">
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-lg
                                    @if (substr($project->name, 0, 1) === 'A') bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400
                                    @elseif(substr($project->name, 0, 1) === 'B')
                                        bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400
                                    @elseif(substr($project->name, 0, 1) === 'C')
                                        bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400
                                    @elseif(substr($project->name, 0, 1) === 'D')
                                        bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400
                                    @elseif(substr($project->name, 0, 1) === 'E')
                                        bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400
                                    @else
                                        bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400 @endif
                                ">
                                            <i data-lucide="folder" class="h-5 w-5"></i>
                                        </div>
                                        <h3 class="ml-3 text-lg font-medium text-zinc-900 dark:text-white truncate">
                                            {{ $project->name }}</h3>
                                    </div>

                                    <x-dropdown.index align="right" width="48">
                                        <x-slot:trigger>
                                            <button @click.stop="open = !open" type="button"
                                                class="text-zinc-400 dark:text-zinc-500 hover:text-zinc-500 dark:hover:text-zinc-400 p-1 rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                                <i data-lucide="more-vertical" class="h-5 w-5"></i>
                                            </button>
                                        </x-slot:trigger>

                                        <x-slot:content>
                                            <x-dropdown.item :href="route('dashboard.projects.show', $project->id)">
                                                View Project
                                            </x-dropdown.item>

                                            <x-dropdown.item :href="route('dashboard.projects.edit', $project->id)">
                                                Edit Project
                                            </x-dropdown.item>

                                            <x-dropdown.divider />

                                            <button type="button"
                                                class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:bg-red-50 dark:focus:bg-red-900/20 transition-colors"
                                                @click="openDeleteModal('{{ $project->id }}', '{{ addslashes($project->name) }}')">
                                                Delete Project
                                            </button>
                                        </x-slot:content>
                                    </x-dropdown.index>
                                </div>

                                <p class="mt-3 text-zinc-600 dark:text-zinc-400 text-sm line-clamp-2">
                                    {{ $project->description ?: 'No description provided' }}
                                </p>

                                <div class="mt-5 flex flex-wrap gap-2">
                                    <div
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-300">
                                        <span>{{ $project->test_suites_count ?? 0 }}</span>
                                        <span
                                            class="ml-1">{{ Str::plural('Suite', $project->test_suites_count ?? 0) }}</span>
                                    </div>
                                    <div
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-300">
                                        <span>{{ $project->test_cases_count ?? 0 }}</span>
                                        <span
                                            class="ml-1">{{ Str::plural('Test', $project->test_cases_count ?? 0) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="px-6 py-3 bg-zinc-50 dark:bg-zinc-800/40 border-t border-zinc-200 dark:border-zinc-700">
                                <div class="flex justify-between items-center">
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        Updated {{ $project->updated_at->diffForHumans() }}
                                    </div>
                                    <a href="{{ route('dashboard.projects.show', $project->id) }}"
                                        class="inline-flex items-center text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white">
                                        View
                                        <i data-lucide="chevron-right" class="ml-1 w-4 h-4"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- List View (hidden by default) -->
                <div id="list-view"
                    class="hidden bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button class="flex items-center space-x-1 sort-btn" data-sort="name">
                                        <span>Name</span>
                                        <i data-lucide="chevron-up" class="w-4 h-4 sort-icon hidden"></i>
                                    </button>
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button class="flex items-center space-x-1 sort-btn" data-sort="suites">
                                        <span>Test Suites</span>
                                        <i data-lucide="chevron-up" class="w-4 h-4 sort-icon hidden"></i>
                                    </button>
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button class="flex items-center space-x-1 sort-btn" data-sort="cases">
                                        <span>Test Cases</span>
                                        <i data-lucide="chevron-up" class="w-4 h-4 sort-icon hidden"></i>
                                    </button>
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    <button class="flex items-center space-x-1 sort-btn" data-sort="updated">
                                        <span>Last Updated</span>
                                        <i data-lucide="chevron-up" class="w-4 h-4 sort-icon hidden"></i>
                                    </button>
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($projects as $project)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors cursor-pointer project-row"
                                    data-href="{{ route('dashboard.projects.show', $project->id) }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-lg
                                            @if (substr($project->name, 0, 1) === 'A') bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400
                                            @elseif(substr($project->name, 0, 1) === 'B')
                                                bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400
                                            @elseif(substr($project->name, 0, 1) === 'C')
                                                bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400
                                            @elseif(substr($project->name, 0, 1) === 'D')
                                                bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400
                                            @elseif(substr($project->name, 0, 1) === 'E')
                                                bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400
                                            @else
                                                bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400 @endif
                                        ">
                                                <i data-lucide="folder" class="h-5 w-5"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                    {{ $project->name }}</div>
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate max-w-xs">
                                                    {{ $project->description ?: 'No description' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ $project->test_suites_count ?? 0 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ $project->test_cases_count ?? 0 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ $project->updated_at->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <a href="{{ route('dashboard.projects.edit', $project->id) }}"
                                                class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                                                onclick="event.stopPropagation()">
                                                <i data-lucide="edit-2" class="h-5 w-5"></i>
                                            </a>
                                            <button
                                                @click="openDeleteModal('{{ $project->id }}', '{{ addslashes($project->name) }}')"
                                                class="text-zinc-500 dark:text-zinc-400 hover:text-red-600 dark:hover:text-red-400"
                                                onclick="event.stopPropagation()">
                                                <i data-lucide="trash-2" class="h-5 w-5"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <!-- Empty state -->
                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-10 text-center">
                    <div
                        class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-zinc-100 dark:bg-zinc-700">
                        <i data-lucide="folder" class="h-8 w-8 text-zinc-600 dark:text-zinc-400"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-zinc-900 dark:text-white">No projects yet</h3>
                    <p class="mt-2 text-zinc-600 dark:text-zinc-400">
                        Get started by creating your first project to organize your test suites.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('dashboard.projects.create') }}"
                            class="btn-primary inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                            <i data-lucide="plus" class="mr-2 -ml-1 w-4 h-4"></i>
                            Create Project
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Delete confirmation modal -->
        <x-modals.delete-confirmation title="Delete Project" message="Are you sure you want to delete the project"
            itemName="deleteProjectName"
            dangerText="This action cannot be undone and will permanently delete the project and all its test suites and cases."
            confirmText="Delete Project" />
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();

            // Toggle between grid and list view
            const toggleViewBtn = document.getElementById('toggle-view-btn');
            const gridView = document.getElementById('grid-view');
            const listView = document.getElementById('list-view');
            const viewIcon = toggleViewBtn.querySelector('i');

            // Check localStorage for preferred view
            const preferredView = localStorage.getItem('projectView') || 'grid';
            if (preferredView === 'list') {
                gridView.classList.add('hidden');
                listView.classList.remove('hidden');
                viewIcon.setAttribute('name', 'grid');
            }

            toggleViewBtn.addEventListener('click', function() {
                const isGridVisible = !gridView.classList.contains('hidden');

                if (isGridVisible) {
                    // Switch to list view
                    gridView.classList.add('hidden');
                    listView.classList.remove('hidden');
                    viewIcon.setAttribute('icon', 'layout-grid');
                    localStorage.setItem('projectView', 'list');
                } else {
                    // Switch to grid view
                    listView.classList.add('hidden');
                    gridView.classList.remove('hidden');
                    viewIcon.setAttribute('icon', 'list');
                    localStorage.setItem('projectView', 'grid');
                }

                // Re-initialize icons
                lucide.createIcons();
            });

            // Project row click to navigate
            document.querySelectorAll('.project-row').forEach(row => {
                row.addEventListener('click', function() {
                    window.location.href = this.dataset.href;
                });
            });

            // Search functionality
            const searchInput = document.getElementById('search-projects');
            const projectCards = document.querySelectorAll('.project-card');
            const projectRows = document.querySelectorAll('.project-row');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();

                // Filter grid view
                projectCards.forEach(card => {
                    const projectName = card.querySelector('h3').textContent.toLowerCase();
                    const projectDesc = card.querySelector('p').textContent.toLowerCase();

                    if (projectName.includes(searchTerm) || projectDesc.includes(searchTerm)) {
                        card.classList.remove('hidden');
                    } else {
                        card.classList.add('hidden');
                    }
                });

                // Filter list view
                projectRows.forEach(row => {
                    const projectName = row.querySelector('.text-sm.font-medium').textContent
                        .toLowerCase();
                    const projectDesc = row.querySelector('.text-xs.text-zinc-500').textContent
                        .toLowerCase();

                    if (projectName.includes(searchTerm) || projectDesc.includes(searchTerm)) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                });
            });

            // Sorting functionality for list view
            document.querySelectorAll('.sort-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sortType = this.dataset.sort;
                    const allSortIcons = document.querySelectorAll('.sort-icon');
                    const thisSortIcon = this.querySelector('.sort-icon');

                    // Reset all icons
                    allSortIcons.forEach(icon => {
                        icon.classList.add('hidden');
                    });

                    // Toggle this icon visibility and direction
                    thisSortIcon.classList.remove('hidden');

                    // Check if we're switching direction
                    if (thisSortIcon.getAttribute('data-direction') === 'asc') {
                        thisSortIcon.setAttribute('data-direction', 'desc');
                        thisSortIcon.setAttribute('icon', 'chevron-down');
                    } else {
                        thisSortIcon.setAttribute('data-direction', 'asc');
                        thisSortIcon.setAttribute('icon', 'chevron-up');
                    }

                    // Get sort direction
                    const sortDirection = thisSortIcon.getAttribute('data-direction');

                    // Sort the rows
                    const tbody = document.querySelector('tbody');
                    const rows = Array.from(tbody.querySelectorAll('tr'));

                    rows.sort((a, b) => {
                        let aValue, bValue;

                        if (sortType === 'name') {
                            aValue = a.querySelector('.text-sm.font-medium').textContent
                                .toLowerCase();
                            bValue = b.querySelector('.text-sm.font-medium').textContent
                                .toLowerCase();
                        } else if (sortType === 'suites' || sortType === 'cases') {
                            const index = sortType === 'suites' ? 1 : 2;
                            aValue = parseInt(a.querySelectorAll('td')[index].textContent
                                .trim());
                            bValue = parseInt(b.querySelectorAll('td')[index].textContent
                                .trim());
                        } else if (sortType === 'updated') {
                            aValue = a.querySelectorAll('td')[3].textContent.trim();
                            bValue = b.querySelectorAll('td')[3].textContent.trim();
                        }

                        if (sortDirection === 'asc') {
                            return aValue > bValue ? 1 : -1;
                        } else {
                            return aValue < bValue ? 1 : -1;
                        }
                    });

                    // Reapply the sorted rows
                    rows.forEach(row => tbody.appendChild(row));

                    // Re-initialize icons
                    lucide.createIcons();
                });
            });

            // Delete confirmation modal
            let projectToDelete = null;
            const deleteButtons = document.querySelectorAll('.delete-project-btn');
            const modal = document.getElementById('delete-modal');
            const modalContent = document.querySelector('.modal-content');
            const projectNameSpan = document.getElementById('delete-project-name');
            const confirmCheck = document.getElementById('confirm-delete');
            const confirmBtn = document.getElementById('confirm-delete-btn');
            const cancelBtn = document.getElementById('cancel-delete-btn');

            document.addEventListener('alpine:init', () => {
                Alpine.data('projectsManager', () => ({
                    showDeleteModal: false,
                    deleteConfirmed: false,
                    isDeleting: false,
                    deleteProjectId: null,
                    deleteProjectName: '',

                    showNotification: false,
                    notificationType: 'success',
                    notificationMessage: '',

                    openDeleteModal(id, name) {
                        this.deleteProjectId = id;
                        this.deleteProjectName = name;
                        this.deleteConfirmed = false;
                        this.showDeleteModal = true;

                        // Close any open dropdown
                        document.querySelectorAll('[x-data="{ open: true }"]').forEach(
                            dropdown => {
                                dropdown.__x.$data.open = false;
                            });
                    },

                    closeDeleteModal() {
                        if (!this.isDeleting) {
                            this.showDeleteModal = false;
                            setTimeout(() => {
                                this.deleteProjectId = null;
                                this.deleteProjectName = '';
                                this.deleteConfirmed = false;
                            }, 300);
                        }
                    },

                    async confirmDelete() {
                        if (!this.deleteProjectId) return;
                        this.isDeleting = true;

                        try {
                            const response = await fetch(
                                `/dashboard/projects/${this.deleteProjectId}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').content,
                                        'Accept': 'application/json'
                                    }
                                });

                            const result = await response.json();

                            if (response.ok) {
                                this.closeDeleteModal();

                                // Show success notification
                                this.notificationType = 'success';
                                this.notificationMessage =
                                    `Project "${this.deleteProjectName}" was deleted successfully`;
                                this.showNotification = true;
                                setTimeout(() => {
                                    this.showNotification = false;
                                }, 5000);

                                // Remove project from DOM
                                const projectCard = document.getElementById(
                                    `project-card-${this.deleteProjectId}`);
                                const projectRow = document.getElementById(
                                    `project-row-${this.deleteProjectId}`);

                                if (projectCard) projectCard.remove();
                                if (projectRow) projectRow.remove();

                                // If no projects left, refresh page to show empty state
                                const remainingProjects = document.querySelectorAll(
                                    '.project-card, .project-row');
                                if (remainingProjects.length === 0) {
                                    location.reload();
                                }
                            } else {
                                throw new Error(result.message ||
                                    'Failed to delete project');
                            }
                        } catch (error) {
                            console.error('Error deleting project:', error);

                            // Show error notification
                            this.notificationType = 'error';
                            this.notificationMessage =
                                'Failed to delete project. Please try again.';
                            this.showNotification = true;
                            setTimeout(() => {
                                this.showNotification = false;
                            }, 5000);
                        } finally {
                            this.isDeleting = false;
                        }
                    },

                    hideNotification() {
                        this.showNotification = false;
                    }
                }));
            });

            // Dropdown menu for project cards
            document.querySelectorAll('.project-menu-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();

                    // Close all other dropdowns
                    document.querySelectorAll('.dropdown-menu').forEach(menu => {
                        if (menu !== this.nextElementSibling) {
                            menu.classList.add('hidden');
                        }
                    });

                    // Toggle this dropdown
                    const dropdown = this.nextElementSibling;
                    dropdown.classList.toggle('hidden');

                    // Add event listener to close dropdown when clicking outside
                    function closeDropdown(e) {
                        if (!dropdown.contains(e.target) && e.target !== btn) {
                            dropdown.classList.add('hidden');
                            document.removeEventListener('click', closeDropdown);
                        }
                    }

                    if (!dropdown.classList.contains('hidden')) {
                        document.addEventListener('click', closeDropdown);
                    }
                });
            });

            // Animation for project cards
            document.querySelectorAll('.project-card').forEach((card, index) => {
                // Add animation delay based on index
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 50 + (index * 50));
            });

            // Add animation to table rows as well
            document.querySelectorAll('.project-row').forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(10px)';

                setTimeout(() => {
                    row.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, 50 + (index * 30));
            });

            // Notification function
            window.showNotification = function(type, title, message) {
                const notificationContainer = document.createElement('div');
                notificationContainer.innerHTML = `
                <div class="fixed bottom-6 right-6 z-50 max-w-sm w-full shadow-lg border rounded-xl p-4 notification-element
                    ${type === 'success' ? 'bg-green-50/80 border-green-200/50 dark:bg-green-900/30 dark:border-green-800/30' :
                    type === 'error' ? 'bg-red-50/80 border-red-200/50 dark:bg-red-900/30 dark:border-red-800/30' :
                    'bg-blue-50/80 border-blue-200/50 dark:bg-blue-900/30 dark:border-blue-800/30'}">
                    <div class="flex items-start">
                        ${type === 'success' ?
                            '<i data-lucide="check-circle" class="flex-shrink-0 w-5 h-5 mt-0.5 mr-3 text-green-600 dark:text-green-400"></i>' :
                            type === 'error' ?
                            '<i data-lucide="alert-circle" class="flex-shrink-0 w-5 h-5 mt-0.5 mr-3 text-red-600 dark:text-red-400"></i>' :
                            '<i data-lucide="info" class="flex-shrink-0 w-5 h-5 mt-0.5 mr-3 text-blue-600 dark:text-blue-400"></i>'
                        }

                        <div>
                            <h4 class="font-medium mb-1
                                ${type === 'success' ? 'text-green-800 dark:text-green-200' :
                                type === 'error' ? 'text-red-800 dark:text-red-200' :
                                'text-blue-800 dark:text-blue-200'}">
                                ${title}
                            </h4>
                            <p class="text-sm
                                ${type === 'success' ? 'text-green-700/90 dark:text-green-300/90' :
                                type === 'error' ? 'text-red-700/90 dark:text-red-300/90' :
                                'text-blue-700/90 dark:text-blue-300/90'}">
                                ${message}
                            </p>
                        </div>

                        <button class="ml-auto -mt-1 -mr-1 p-1 rounded-full hover:bg-zinc-200/50 dark:hover:bg-zinc-700/50 transition-colors close-notification">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            `;

                const notification = notificationContainer.firstElementChild;
                document.body.appendChild(notification);

                // Initialize icons in the notification
                lucide.createIcons({
                    elements: [notification]
                });

                // Add closing functionality
                notification.querySelector('.close-notification').addEventListener('click', () => {
                    notification.classList.add('opacity-0', 'translate-y-2');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                });

                // Automatically remove after 5 seconds
                setTimeout(() => {
                    notification.classList.add('opacity-0', 'translate-y-2');
                    notification.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 5000);

                // Add animation to show notification
                requestAnimationFrame(() => {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateY(20px)';
                    notification.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

                    requestAnimationFrame(() => {
                        notification.style.opacity = '1';
                        notification.style.transform = 'translateY(0)';
                    });
                });
            };

            // Check for flash messages from the server
            @if (session('success'))
                showNotification('success', 'Success', "{{ session('success') }}");
            @endif

            @if (session('error'))
                showNotification('error', 'Error', "{{ session('error') }}");
            @endif
        });
    </script>

    <style>
        /* Animations for project cards and modals */
        .project-card,
        .project-row {
            backface-visibility: hidden;
        }

        .modal-content {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        /* Notification animation */
        .notification-element {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        /* Dropdown positioning */
        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            z-index: 10;
        }
    </style>
@endpush
