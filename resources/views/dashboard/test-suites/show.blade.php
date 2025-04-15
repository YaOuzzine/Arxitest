@php
    /**
     * @var \App\Models\Project $project
     * @var \App\Models\TestSuite $testSuite
     */
    $pageTitle = $testSuite->name;
    $testCases = $testSuite->testCases; // Assuming loaded in controller
@endphp

@extends('layouts.dashboard')

@section('title', $pageTitle)

@section('breadcrumbs')
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
        <a href="{{ route('dashboard.projects.test-suites.index', $project->id) }}" class="...">Test Suites</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">{{ $testSuite->name }}</span>
    </li>
@endsection

@section('content')
<div class="h-full" x-data="testSuiteDetails({
    suiteId: '{{ $testSuite->id }}',
    suiteName: '{{ addslashes($testSuite->name) }}',
    projectId: '{{ $project->id }}',
    deleteUrl: '{{ route('dashboard.projects.test-suites.destroy', [$project->id, $testSuite->id]) }}',
    csrfToken: '{{ csrf_token() }}'
})" x-init="initNotifications()">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 mb-8">
        <div class="flex items-center space-x-4">
            <div class="flex-shrink-0 p-3 bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-xl shadow-sm">
                <i data-lucide="layers" class="w-8 h-8 text-indigo-600 dark:text-indigo-400"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-1">{{ $testSuite->name }}</h1>
                <p class="text-zinc-600 dark:text-zinc-400 max-w-xl">{{ $testSuite->description ?: 'No description provided.' }}</p>
                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-zinc-500 dark:text-zinc-400">
                    <span class="flex items-center"><i data-lucide="folder" class="w-3.5 h-3.5 mr-1.5"></i>Project: {{ $project->name }}</span>
                    <span class="flex items-center"><i data-lucide="clock" class="w-3.5 h-3.5 mr-1.5"></i>Updated: {{ $testSuite->updated_at->diffForHumans() }}</span>
                    <span class="flex items-center"><i data-lucide="settings" class="w-3.5 h-3.5 mr-1.5"></i>Priority: {{ ucfirst($testSuite->settings['default_priority'] ?? 'Medium') }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-3 flex-shrink-0">
            <button class="btn-secondary inline-flex items-center px-4 py-2">
                <i data-lucide="play" class="w-4 h-4 mr-2"></i> Run Suite
            </button>
             <a href="{{ route('dashboard.projects.test-suites.edit', [$project->id, $testSuite->id]) }}" class="btn-outline inline-flex items-center px-4 py-2">
                <i data-lucide="edit-3" class="w-4 h-4 mr-2"></i> Edit
            </a>
            <button @click="openDeleteModal()" class="btn-danger-outline inline-flex items-center px-4 py-2">
                <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Delete
            </button>
        </div>
    </div>

    <!-- Main Content Area (Test Cases List) -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-zinc-200/50 dark:border-zinc-700/50 overflow-hidden">
        <div class="p-6 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-zinc-800 dark:text-white">Test Cases</h2>
            <div class="flex items-center space-x-3">
                 <div class="relative">
                    <input type="text" placeholder="Search cases..." class="w-64 pl-9 pr-3 py-2 text-sm rounded-lg border border-zinc-300/80 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-900/30 focus:ring-1 focus:ring-blue-500 focus:border-blue-500/50">
                     <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                 </div>
                <button class="btn-primary inline-flex items-center px-4 py-2">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i> New Test Case
                </button>
            </div>
        </div>

        @if($testCases->isEmpty())
            <div class="p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-700 mb-4">
                    <i data-lucide="file-check-2" class="w-8 h-8 text-zinc-400 dark:text-zinc-500"></i>
                </div>
                <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200 mb-2">No Test Cases Yet</h3>
                <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-6">
                    Add test cases to this suite to start defining your test scenarios.
                </p>
                <button class="btn-primary">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    Create First Test Case
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-zinc-200/50 dark:divide-zinc-700/30">
                    <thead class="bg-zinc-50/50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-500/90 dark:text-zinc-400/90 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-500/90 dark:text-zinc-400/90 uppercase tracking-wider">Steps</th>
                             <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-500/90 dark:text-zinc-400/90 uppercase tracking-wider">Last Run</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-zinc-500/90 dark:text-zinc-400/90 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200/50 dark:divide-zinc-700/30 bg-white dark:bg-zinc-900/20">
                        @foreach($testCases as $case)
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-zinc-900/90 dark:text-white/90">{{ $case->title }}</div>
                                <div class="text-xs text-zinc-500/80 dark:text-zinc-400/80">{{ Str::limit($case->expected_results, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                {{ count($case->steps ?? []) }} {{ Str::plural('step', count($case->steps ?? [])) }}
                            </td>
                             <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $case->updated_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-3">
                                    <a href="#" class="text-zinc-500 hover:text-blue-600 dark:text-zinc-400 dark:hover:text-blue-400 transition-colors">
                                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                                    </a>
                                    <button class="text-zinc-500 hover:text-red-600 dark:text-zinc-400 dark:hover:text-red-400 transition-colors">
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

     <!-- Delete confirmation modal -->
    <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-zinc-900/50 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full opacity-100 translate-y-0 sm:scale-100 modal-content">
                <div class="bg-white dark:bg-zinc-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600 dark:text-red-400"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-white" id="modal-title">
                                Delete Test Suite
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    Are you sure you want to delete "<span x-text="suiteName" class="font-medium"></span>"? This action cannot be undone. All associated test cases will also be deleted.
                                </p>
                            </div>
                             <div class="mt-4">
                                <label for="confirm-delete-text" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Type "<span x-text="suiteName" class="font-semibold"></span>" to confirm:</label>
                                <input type="text" id="confirm-delete-text" x-model="deleteConfirmText" class="mt-1 block w-full border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm py-2 px-3 focus:ring-red-500 focus:border-red-500 sm:text-sm dark:bg-zinc-700 dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-zinc-50 dark:bg-zinc-700/30 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button
                        @click="confirmDelete()"
                        type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="isDeleting || deleteConfirmText !== suiteName"
                    >
                        <span x-show="!isDeleting">Delete</span>
                        <span x-show="isDeleting" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Deleting...
                        </span>
                    </button>
                    <button
                        @click="closeDeleteModal()"
                        type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-zinc-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Area -->
    <div x-show="showNotification" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2" class="fixed bottom-6 right-6 z-50">
        <div class="min-w-[300px] bg-white dark:bg-zinc-800 rounded-xl shadow-xl border border-zinc-200 dark:border-zinc-700 flex items-start p-4 space-x-3"
            :class="{
                'bg-green-50/90 border-green-200 dark:bg-green-900/30 dark:border-green-800': notificationType === 'success',
                'bg-red-50/90 border-red-200 dark:bg-red-900/30 dark:border-red-800': notificationType === 'error'
            }">
            <i data-lucide="check-circle" x-show="notificationType === 'success'" class="w-6 h-6 flex-shrink-0 text-green-600 dark:text-green-400"></i>
            <i data-lucide="alert-circle" x-show="notificationType === 'error'" class="w-6 h-6 flex-shrink-0 text-red-600 dark:text-red-400"></i>
            <div class="flex-1">
                <p class="text-sm font-medium"
                    :class="{
                        'text-green-800 dark:text-green-200': notificationType === 'success',
                        'text-red-800 dark:text-red-200': notificationType === 'error'
                    }"
                    x-text="notificationMessage"></p>
            </div>
            <button @click="hideNotification" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
    </div>

</div>

@push('styles')
<style>
    /* Custom button styles */
    .btn-primary { @apply inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200; }
    .btn-secondary { @apply inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200; }
    .btn-outline { @apply inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-transparent hover:bg-zinc-50 dark:hover:bg-zinc-700/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200; }
    .btn-danger-outline { @apply inline-flex items-center px-4 py-2 border border-red-300 dark:border-red-700 rounded-lg shadow-sm text-sm font-medium text-red-600 dark:text-red-400 bg-transparent hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('testSuiteDetails', (config) => ({
        suiteId: config.suiteId,
        suiteName: config.suiteName,
        projectId: config.projectId,
        deleteUrl: config.deleteUrl,
        csrfToken: config.csrfToken,

        showDeleteModal: false,
        deleteConfirmText: '',
        isDeleting: false,

        showNotification: false,
        notificationType: 'success',
        notificationMessage: '',

        initNotifications() {
            @if (session('success'))
                this.showSuccess('{{ session('success') }}')
            @endif
            @if (session('error'))
                this.showError('{{ session('error') }}')
            @endif
        },

        showSuccess(message) {
            this.notificationType = 'success';
            this.notificationMessage = message;
            this.showNotification = true;
            setTimeout(() => this.hideNotification(), 5000);
        },

        showError(message) {
            this.notificationType = 'error';
            this.notificationMessage = message;
            this.showNotification = true;
            setTimeout(() => this.hideNotification(), 7000); // Show errors longer
        },

        hideNotification() {
            this.showNotification = false;
        },

        openDeleteModal() {
            this.deleteConfirmText = ''; // Reset confirmation text
            this.isDeleting = false; // Reset deleting state
            this.showDeleteModal = true;
            // Delay focus to ensure modal is visible
            this.$nextTick(() => {
                 document.getElementById('confirm-delete-text')?.focus();
             });
        },

        closeDeleteModal() {
            if (!this.isDeleting) {
                this.showDeleteModal = false;
                this.deleteConfirmText = '';
            }
        },

        async confirmDelete() {
            if (this.isDeleting || this.deleteConfirmText !== this.suiteName) {
                return;
            }

            this.isDeleting = true;

            try {
                const response = await fetch(this.deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                     // Redirect to the project's test suite index page after successful deletion
                     window.location.href = `{{ route('dashboard.projects.test-suites.index', $project->id) }}`;
                     // Optionally store success message in session storage to show on next page load
                     // sessionStorage.setItem('flashSuccess', result.message);
                } else {
                    this.showError(result.message || 'Failed to delete the test suite.');
                    this.closeDeleteModal(); // Close modal on failure
                }
            } catch (error) {
                console.error('Delete Error:', error);
                this.showError('An unexpected error occurred while deleting.');
                this.closeDeleteModal();
            } finally {
                // isDeleting state might be irrelevant if redirecting, but good practice to reset if not redirecting
                 this.isDeleting = false;
            }
        }
    }));
});
</script>
@endpush
