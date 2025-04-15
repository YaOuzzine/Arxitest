@php
    /**
     * @var \App\Models\Project $project
     * @var \Illuminate\Database\Eloquent\Collection<\App\Models\TestSuite> $testSuites
     */

    $isGenericIndex = !isset($project);
    // Define the title conditionally AFTER checking $isGenericIndex
    $pageTitle = $isGenericIndex ? 'All Test Suites' : 'Test Suites for: ' . $project->name;
    $currentProjectId = request()->query('project_id', '');
@endphp

@extends('layouts.dashboard')

@section('title', $pageTitle)

@section('breadcrumbs')
    {{-- Generic Index Breadcrumbs --}}
    @if ($isGenericIndex)
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <span class="text-zinc-700 dark:text-zinc-300">Test Suites</span>
        </li>
        {{-- Project Specific Index Breadcrumbs --}}
    @else
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <a href="{{ route('dashboard.projects') }}" class="...">Projects</a>
        </li>
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <a href="{{ route('dashboard.projects.show', $project->id) }}" class="...">{{ $project->name }}</a>
        </li>
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <span class="text-zinc-700 dark:text-zinc-300">Test Suites</span>
        </li>
    @endif
@endsection

@section('content')
    <div x-data="testSuiteEnhanced" x-init="initNotifications()" class="relative">
        <!-- Animated Header -->
        <div
            class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4 transition-opacity duration-300">
            <div class="mb-4 md:mb-0 space-y-2">
                <h1
                    class="text-3xl font-bold text-zinc-900 dark:text-white bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent animate-text">
                    {{ $pageTitle }}
                </h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 opacity-90 hover:opacity-100 transition-opacity">
                    @if ($isGenericIndex)
                        Viewing all test suites for team '{{ $team->name }}'
                    @else
                        Managing test suites in '{{ $project->name }}'
                    @endif
                </p>
            </div>
            <div class="group relative">
                <a href="{{ $isGenericIndex ? route('dashboard.projects') : route('dashboard.projects.test-suites.create', $project->id) }}"
                    class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl shadow-lg hover:shadow-indigo-500/30 transition-all duration-300 transform hover:-translate-y-0.5 {{ $isGenericIndex ? 'opacity-70 cursor-not-allowed' : '' }}"
                    @if ($isGenericIndex) disabled @endif>
                    <i data-lucide="plus" class="w-5 h-5 mr-2 -ml-1 animate-pulse"></i>
                    {{ $isGenericIndex ? 'Select Project' : 'New Test Suite' }}
                </a>
                @if ($isGenericIndex)
                    <div
                        class="absolute hidden group-hover:block -top-12 right-0 bg-zinc-800 text-white text-xs px-3 py-1.5 rounded-lg shadow-lg">
                        Choose a project to create suite
                    </div>
                @endif
            </div>
        </div>

        <!-- Project Filter (Generic Index Only) -->
        @if ($isGenericIndex)
            <div class="mb-6 animate-slide-down">
                <form method="GET" action="{{ route('dashboard.test-suites.indexAll') }}" id="project-filter-form">
                    <input type="hidden" name="project_id" id="selected-project-id" value="{{ $currentProjectId }}">

                    <!-- Custom Select Container -->
                    <div class="dashboard-container" id="project-select-container">
                        <!-- Trigger Button -->
                        <button type="button" id="project-select-trigger"
                            class="w-full flex items-center justify-between px-4 py-3.5 bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600 transition-all duration-200">
                            <div class="flex items-center space-x-3">
                                <i data-lucide="folder" class="w-5 h-5 text-zinc-400"></i>
                                <span id="selected-project-name" class="text-left">
                                    {{ $projects->find($currentProjectId)?->name ?? 'All Projects' }}
                                </span>
                            </div>
                            <i data-lucide="chevron-down"
                                class="w-5 h-5 text-zinc-400 transition-transform duration-200"></i>
                        </button>

                        <!-- Dropdown Content -->
                        <div class="dashboard-menu"
                            id="project-select-dropdown">
                            <!-- Search Input -->
                            <div class="p-3 border-b border-zinc-100 dark:border-zinc-700">
                                <div class="relative">
                                    <i data-lucide="search"
                                        class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400"></i>
                                    <input type="text" id="project-search" placeholder="Search projects..."
                                        class="w-full pl-10 pr-4 py-2.5 bg-zinc-50 dark:bg-zinc-800 rounded-lg focus:ring-2 focus:ring-indigo-500 border-0 text-sm">
                                </div>
                            </div>

                            <!-- Options List -->
                            <div class="divide-y divide-zinc-100 dark:divide-zinc-700 overflow-y-auto max-h-64">
                                <div class="project-option" data-project-id="">
                                    <button type="button"
                                        class="w-full px-4 py-3 text-left hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <i data-lucide="grid" class="w-5 h-5 text-zinc-400"></i>
                                            <span>All Projects</span>
                                        </div>
                                    </button>
                                </div>
                                @foreach ($projects as $project)
                                    <div class="project-option" data-project-id="{{ $project->id }}"
                                        data-project-name="{{ $project->name }}">
                                        <button type="button"
                                            class="w-full px-4 py-3 text-left hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <i data-lucide="folder" class="w-5 h-5 text-zinc-400"></i>
                                                <span>{{ $project->name }}</span>
                                            </div>
                                            @if ($project->id == $currentProjectId)
                                                <i data-lucide="check"
                                                    class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i>
                                            @endif
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Test Suites Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 group" x-data="{ loaded: false }"
                x-init="setTimeout(() => loaded = true, 100)" x-show="loaded" x-transition:enter.duration.500ms>
                @forelse($testSuites as $suite)
                    <div class="relative bg-white/80 dark:bg-zinc-800/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg hover:shadow-xl border border-zinc-200/50 dark:border-zinc-700/50 transition-all duration-300 transform hover:-translate-y-1.5 hover:scale-[1.02]"
                        x-data="{ showActions: false }" x-init="setTimeout(() => $el.classList.remove('opacity-0'), {{ $loop->index * 50 }})" class="opacity-0" @mouseenter="showActions = true"
                        @mouseleave="showActions = false">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white truncate">
                                    {{ $suite->name }}
                                    @if ($isGenericIndex)
                                        <span class="block text-sm font-medium text-indigo-600 dark:text-indigo-400 mt-1">
                                            {{ $suite->project->name }}
                                        </span>
                                    @endif
                                </h3>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">
                                    <i data-lucide="file-check-2" class="w-4 h-4 mr-1"></i>
                                    {{ $suite->test_cases_count }}
                                </span>
                            </div>
                        </div>

                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 line-clamp-2">
                            {{ $suite->description ?: 'No description provided' }}
                        </p>

                        <div class="flex items-center justify-between text-sm">
                            <div class="text-zinc-500 dark:text-zinc-400 flex items-center">
                                <i data-lucide="clock" class="w-4 h-4 mr-1.5"></i>
                                {{ $suite->updated_at->diffForHumans() }}
                            </div>
                            <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <a href="{{ route('dashboard.projects.test-suites.show', [$suite->project_id, $suite->id]) }}"
                                    class="text-zinc-600 hover:text-indigo-600 dark:text-zinc-300 dark:hover:text-indigo-400 transition-colors"
                                    title="View">
                                    <i data-lucide="eye" class="w-5 h-5"></i>
                                </a>
                                <a href="{{ route('dashboard.projects.test-suites.edit', [$suite->project_id, $suite->id]) }}"
                                    class="text-zinc-600 hover:text-yellow-600 dark:text-zinc-300 dark:hover:text-yellow-400 transition-colors"
                                    title="Edit">
                                    <i data-lucide="pencil" class="w-5 h-5"></i>
                                </a>
                                <button
                                    @click="openDeleteModal('{{ $suite->id }}', '{{ addslashes($suite->name) }}', '{{ $suite->project_id }}')"
                                    class="text-zinc-600 hover:text-red-600 dark:text-zinc-300 dark:hover:text-red-400 transition-colors"
                                    title="Delete">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center">
                        <div class="max-w-md mx-auto space-y-4">
                            <div
                                class="inline-block p-4 bg-zinc-100 dark:bg-zinc-700 rounded-full shadow-lg animate-bounce">
                                <i data-lucide="folder-x" class="w-12 h-12 text-zinc-600 dark:text-zinc-300"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">No Test Suites Found</h3>
                            <p class="text-zinc-600 dark:text-zinc-400">
                                @if ($isGenericIndex && $currentProjectId)
                                    No suites found for selected project
                                @elseif($isGenericIndex)
                                    Create test suites through projects
                                @else
                                    Create your first test suite
                                @endif
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Enhanced Notification Toast -->
            <div x-show="showNotification" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4" class="fixed bottom-6 right-6 z-50">
                <div class="min-w-[300px] bg-white dark:bg-zinc-800 rounded-xl shadow-xl border border-zinc-200 dark:border-zinc-700 flex items-start p-4 space-x-3"
                    :class="{
                        'bg-green-50/90 border-green-200 dark:bg-green-900/30 dark:border-green-800': notificationType === 'success',
                        'bg-red-50/90 border-red-200 dark:bg-red-900/30 dark:border-red-800': notificationType === 'error'
                    }">
                    <i data-lucide="check-circle" class="w-6 h-6 flex-shrink-0"
                        :class="{
                            'text-green-600 dark:text-green-400': notificationType === 'success',
                            'text-red-600 dark:text-red-400': notificationType === 'error'
                        }"></i>
                    <div class="flex-1">
                        <p class="text-sm font-medium"
                            :class="{
                                'text-green-800 dark:text-green-200': notificationType === 'success',
                                'text-red-800 dark:text-red-200': notificationType === 'error'
                            }"
                            x-text="notificationMessage"></p>
                    </div>
                    <button @click="hideNotification"
                        class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Modern Delete Modal -->
            <div x-show="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 text-center">
                    <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="inline-block w-full max-w-md p-6 my-8 text-left align-middle bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-700 transform transition-all">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                                <i data-lucide="alert-triangle" class="w-6 h-6 text-red-600 dark:text-red-400"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">
                                Delete Test Suite
                            </h3>
                        </div>

                        <p class="text-zinc-600 dark:text-zinc-400 mb-6">
                            Are you sure you want to delete "<span x-text="deleteSuiteName" class="font-medium"></span>"?
                            This action cannot be undone.
                        </p>

                        <div class="flex justify-end space-x-3">
                            <button @click="closeDeleteModal()"
                                class="px-4 py-2 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 rounded-lg transition-colors">
                                Cancel
                            </button>
                            <button @click="confirmDelete()" :disabled="isDeleting"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!isDeleting">Delete</span>
                                <span x-show="isDeleting" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" viewBox="0 0 24 24">
                                        <!-- ... (keep spinner SVG) ... -->
                                    </svg>
                                    Deleting...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('testSuiteEnhanced', () => ({
                showNotification: false,
                notificationType: 'success',
                notificationMessage: '',
                showDeleteModal: false,
                deleteSuiteId: null,
                deleteProjectId: null,
                deleteSuiteName: '',
                isDeleting: false,

                initNotifications() {
                    @if (session('success'))
                        this.showSuccess('{{ session('success') }}')
                    @endif
                    @if (session('error'))
                        this.showError('{{ session('error') }}')
                    @endif
                },

                showSuccess(message) {
                    this.notificationType = 'success'
                    this.notificationMessage = message
                    this.showNotification = true
                    setTimeout(() => this.hideNotification(), 5000)
                },

                showError(message) {
                    this.notificationType = 'error'
                    this.notificationMessage = message
                    this.showNotification = true
                    setTimeout(() => this.hideNotification(), 7000)
                },

                hideNotification() {
                    this.showNotification = false
                },

                openDeleteModal(id, name, projectId) {
                    this.deleteSuiteId = id
                    this.deleteProjectId = projectId
                    this.deleteSuiteName = name
                    this.showDeleteModal = true
                },

                closeDeleteModal() {
                    if (!this.isDeleting) {
                        this.showDeleteModal = false
                        this.deleteSuiteId = null
                        this.deleteProjectId = null
                        this.deleteSuiteName = ''
                    }
                },

                async confirmDelete() {
                    this.isDeleting = true
                    try {
                        const response = await axios.delete(
                            `/projects/${this.deleteProjectId}/test-suites/${this.deleteSuiteId}`, {
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                }
                            })

                        if (response.data.success) {
                            document.getElementById(`suite-row-${this.deleteSuiteId}`)?.remove()
                            this.showSuccess('Test suite deleted successfully')
                        }
                    } catch (error) {
                        this.showError(error.response?.data?.message || 'An error occurred')
                    } finally {
                        this.isDeleting = false
                        this.closeDeleteModal()
                    }
                }
            }))
        })
    </script>
@endpush

@push('scripts')
<script>
(function() {
    const container = document.getElementById('project-select-container');
    const trigger = document.getElementById('project-select-trigger');
    const dropdown = document.getElementById('project-select-dropdown');
    const searchInput = document.getElementById('project-search');
    const options = document.querySelectorAll('.project-option');
    const selectedProjectId = document.getElementById('selected-project-id');
    const selectedProjectName = document.getElementById('selected-project-name');
    const form = document.getElementById('project-filter-form');

    // Toggle dropdown
    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = dropdown.classList.toggle('hidden');
        trigger.querySelector('i').style.transform = isOpen ? 'rotate(180deg)' : '';
        container.classList.toggle('is-open', !isOpen);
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!container.contains(e.target)) {
            dropdown.classList.add('hidden');
            trigger.querySelector('i').style.transform = '';
            container.classList.remove('is-open');
        }
    });

    // Search functionality
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        options.forEach(option => {
            const name = option.dataset.projectName?.toLowerCase() || '';
            const matches = name.includes(searchTerm);
            option.style.display = matches ? '' : 'none';
        });
    });

    // Handle option selection
    options.forEach(option => {
        option.querySelector('button').addEventListener('click', (e) => {
            const projectId = option.dataset.projectId;
            const projectName = option.dataset.projectName || 'All Projects';

            // Update selected values
            selectedProjectId.value = projectId;
            selectedProjectName.textContent = projectName;

            // Update checkmarks
            options.forEach(opt => {
                const check = opt.querySelector('[data-lucide="check"]');
                if (check) check.remove();
            });

            if (projectId) {
                const newCheck = document.createElement('i');
                newCheck.setAttribute('data-lucide', 'check');
                newCheck.className = 'w-5 h-5 text-indigo-600 dark:text-indigo-400';
                option.querySelector('button').appendChild(newCheck);
                lucide.createIcons();
            }

            // Submit form
            form.submit();
        });
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !dropdown.classList.contains('hidden')) {
            dropdown.classList.add('hidden');
            trigger.querySelector('i').style.transform = '';
            container.classList.remove('is-open');
        }
    });
})();
</script>
@endpush

<style>
#project-select-dropdown {
    transform-origin: top center;
    animation: dropdownOpen 0.2s ease-out;
}

@keyframes dropdownOpen {
    0% {
        opacity: 0;
        transform: translateY(-10px) scale(0.95);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.dark #project-select-dropdown::-webkit-scrollbar {
    width: 6px;
}

.dark #project-select-dropdown::-webkit-scrollbar-track {
    background: rgba(63, 63, 70, 0.1);
}

.dark #project-select-dropdown::-webkit-scrollbar-thumb {
    background: rgba(161, 161, 170, 0.5);
    border-radius: 4px;
}

.dark #project-select-dropdown::-webkit-scrollbar-thumb:hover {
    background: rgba(161, 161, 170, 0.7);
}

.project-option button {
    transition: background-color 0.15s ease, transform 0.15s ease;
}

.project-option button:active {
    transform: scale(0.98);
}
</style>
@endif

@push('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
