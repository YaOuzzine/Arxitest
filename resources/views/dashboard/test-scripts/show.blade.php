{{-- resources/views/dashboard/test-scripts/show.blade.php --}}
@extends('layouts.dashboard')

@section('title', "{$testScript->name}")

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.show', $project->id) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">{{ $project->name }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">{{ $testCase->title }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id]) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">Test Scripts</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">{{ $testScript->name }}</span>
    </li>
@endsection

@section('content')
    <div class="h-full space-y-6" x-data="testScriptViewer">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-zinc-800 dark:from-zinc-100 to-zinc-600 dark:to-zinc-300 bg-clip-text text-transparent tracking-tight">
                    {{ $testScript->name }}
                </h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Test script for {{ $testCase->title }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id]) }}"
                   class="btn-secondary px-4 py-2 rounded-lg flex items-center">
                    <i data-lucide="chevron-left" class="w-4 h-4 mr-2"></i>
                    Back to Scripts
                </a>
                <button @click="openDeleteModal" type="button"
                   class="btn-danger px-4 py-2 rounded-lg flex items-center">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
                    Delete
                </button>
                <a href="#" @click.prevent="openEditModal"
                   class="btn-primary px-4 py-2 rounded-lg flex items-center">
                    <i data-lucide="pencil" class="w-4 h-4 mr-2"></i>
                    Edit
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Details Card -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/50 dark:bg-zinc-800/50">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-white">Details</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Framework Type</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ match($testScript->framework_type) {
                                        'selenium-python' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                        'cypress' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                        default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700/30 dark:text-zinc-300',
                                    } }}">
                                    {{ match($testScript->framework_type) {
                                        'selenium-python' => 'Selenium Python',
                                        'cypress' => 'Cypress',
                                        'other' => 'Other',
                                        default => $testScript->framework_type,
                                    } }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Created By</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $testScript->creator->name ?? 'Unknown' }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Created At</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $testScript->created_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Last Updated</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $testScript->updated_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Script Content -->
            <div class="lg:col-span-3">
                <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/50 dark:bg-zinc-800/50 flex justify-between items-center">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-white">Script Content</h2>
                        <div class="flex space-x-2">
                            <button @click="copyToClipboard" class="px-2 py-1 text-xs rounded-md bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors">
                                <i data-lucide="copy" class="w-3 h-3 inline-block mr-1"></i>
                                Copy
                            </button>
                        </div>
                    </div>
                    <div class="p-0">
                        <pre class="language-{{ $testScript->framework_type === 'cypress' ? 'javascript' : 'python' }} overflow-auto max-h-screen p-6 text-sm">{{ $testScript->script_content }}</pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <x-modals.delete-confirmation
            title="Delete Test Script"
            message="Are you sure you want to delete this test script?"
            itemName="'{{ addslashes($testScript->name) }}'"
            dangerText="This action cannot be undone."
            confirmText="Delete Script" />

        <div x-show="showEditModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="edit-modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
                <div class="fixed inset-0 bg-zinc-900/60 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
                    @click="showEditModal = false"></div>
                <div
                    class="relative inline-block w-full max-w-4xl p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-zinc-800 shadow-xl rounded-2xl">
                    <div class="absolute top-0 right-0 pt-5 pr-5">
                        <button type="button" @click="showEditModal = false" class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <form @submit.prevent="submitEditForm">
                        <div>
                            <h3 class="text-xl font-medium text-zinc-900 dark:text-zinc-100" id="edit-modal-title">
                                Edit Test Script
                            </h3>

                            <div class="mt-4 space-y-6">
                                <!-- Script Name -->
                                <div>
                                    <label for="edit-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Script Name <span class="text-red-500">*</span>
                                    </label>
                                    <input x-model="editForm.name" type="text" id="edit-name" class="w-full px-4 py-2.5 rounded-lg" required>
                                </div>

                                <!-- Framework Type -->
                                <div>
                                    <label for="edit-framework" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Framework Type <span class="text-red-500">*</span>
                                    </label>
                                    <select x-model="editForm.framework_type" id="edit-framework" class="w-full px-4 py-2.5 rounded-lg" required>
                                        <option value="">Select Framework</option>
                                        <option value="selenium-python">Selenium Python</option>
                                        <option value="cypress">Cypress</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>

                                <!-- Script Content -->
                                <div>
                                    <label for="edit-content" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Script Content <span class="text-red-500">*</span>
                                    </label>
                                    <textarea x-model="editForm.script_content" id="edit-content" rows="10"
                                        class="w-full px-4 py-2.5 rounded-lg font-mono text-sm" required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="showEditModal = false"
                                class="px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                Cancel
                            </button>
                            <button type="submit" :disabled="isSubmitting"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!isSubmitting">Save Changes</span>
                                <span x-show="isSubmitting" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .btn-secondary {
        @apply bg-white/50 dark:bg-zinc-700/50 border border-zinc-300/70 dark:border-zinc-600/50 hover:bg-zinc-50/70 dark:hover:bg-zinc-600/50 shadow-sm text-zinc-700 dark:text-zinc-300 transition-all;
    }

    .btn-primary {
        @apply bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm hover:shadow-md transition-all;
    }

    .btn-danger {
        @apply bg-red-500 hover:bg-red-600 text-white shadow-sm hover:shadow-md transition-all;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('testScriptViewer', () => ({
            showDeleteModal: false,
            showEditModal: false,
            deleteConfirmed: false,
            isDeleting: false,
            isSubmitting: false,
            requireConfirmation: true,

            editForm: {
                name: "{{ addslashes($testScript->name) }}",
                framework_type: "{{ $testScript->framework_type }}",
                script_content: `{{ addslashes($testScript->script_content) }}`
            },

            copyToClipboard() {
                const scriptContent = `{{ addslashes($testScript->script_content) }}`;
                navigator.clipboard.writeText(scriptContent).then(() => {
                    this.showNotification('info', 'Script copied to clipboard');
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                    this.showNotification('error', 'Failed to copy to clipboard');
                });
            },

            openDeleteModal() {
                this.showDeleteModal = true;
                this.deleteConfirmed = false;
            },

            openEditModal() {
                this.showEditModal = true;
            },

            async confirmDelete() {
                this.isDeleting = true;

                try {
                    const url = "{{ route('dashboard.projects.test-cases.scripts.destroy', [$project->id, $testCase->id, $testScript->id]) }}";
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        window.location.href = "{{ route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id]) }}";
                    } else {
                        throw new Error(result.message || 'Failed to delete test script');
                    }
                } catch (error) {
                    console.error(error);
                    this.showNotification('error', error.message || 'An error occurred');
                } finally {
                    this.isDeleting = false;
                    this.showDeleteModal = false;
                }
            },

            async submitEditForm() {
                this.isSubmitting = true;

                try {
                    const url = "{{ route('dashboard.projects.test-cases.scripts.update', [$project->id, $testCase->id, $testScript->id]) }}";
                    const response = await fetch(url, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.editForm)
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        window.location.reload();
                    } else {
                        throw new Error(result.message || 'Failed to update test script');
                    }
                } catch (error) {
                    console.error(error);
                    this.showNotification('error', error.message || 'An error occurred');
                } finally {
                    this.isSubmitting = false;
                }
            },

            showNotification(type, message) {
                // Dispatch event to notification system
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { type, message }
                }));
            }
        }));
    });
</script>
@endpush
