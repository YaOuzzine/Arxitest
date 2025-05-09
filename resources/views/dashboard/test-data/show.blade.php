{{-- resources/views/dashboard/test-data/show.blade.php --}}
@extends('layouts.dashboard')

@section('title', "{$testData->name}")

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
        <a href="{{ route('dashboard.projects.test-cases.data.index', [$project->id, $testCase->id]) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">Test Data</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">{{ $testData->name }}</span>
    </li>
@endsection

@section('content')
    <div class="h-full space-y-6" x-data="testDataViewer">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-zinc-800 dark:from-zinc-100 to-zinc-600 dark:to-zinc-300 bg-clip-text text-transparent tracking-tight">
                    {{ $testData->name }}
                </h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Test data for {{ $testCase->title }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('dashboard.projects.test-cases.data.index', [$project->id, $testCase->id]) }}"
                   class="btn-secondary px-4 py-2 rounded-lg flex items-center">
                    <i data-lucide="chevron-left" class="w-4 h-4 mr-2"></i>
                    Back to Test Data
                </a>
                <button @click="openDeleteModal" type="button"
                   class="btn-danger px-4 py-2 rounded-lg flex items-center">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
                    Remove
                </button>
                <a href="{{ route('dashboard.projects.test-cases.data.edit', [$project->id, $testCase->id, $testData->id]) }}"
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
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Format</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ match($testData->format) {
                                        'json' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                        'csv' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                        'xml' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                        'plain' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700/30 dark:text-zinc-300',
                                        default => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                                    } }}">
                                    {{ strtoupper($testData->format) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Usage Context</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $usageContext ?? 'General purpose' }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Sensitive Data</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                @if($testData->is_sensitive)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                        <i data-lucide="shield-alert" class="w-3 h-3 mr-1"></i>
                                        Sensitive
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-700/30 dark:text-zinc-300">
                                        <i data-lucide="shield" class="w-3 h-3 mr-1"></i>
                                        Not Sensitive
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Created At</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $testData->created_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Last Updated</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $testData->updated_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Content -->
            <div class="lg:col-span-3">
                <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/50 dark:bg-zinc-800/50 flex justify-between items-center">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-white">Test Data Content</h2>
                        <div class="flex space-x-2">
                            <button @click="copyToClipboard" class="px-2 py-1 text-xs rounded-md bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors">
                                <i data-lucide="copy" class="w-3 h-3 inline-block mr-1"></i>
                                Copy
                            </button>
                        </div>
                    </div>
                    <div class="p-0">
                        <pre class="overflow-auto max-h-screen p-6 text-sm font-mono text-zinc-800 dark:text-zinc-200 whitespace-pre-wrap break-words">{{ $testData->content }}</pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <x-modals.delete-confirmation
            title="Remove Test Data"
            message="Are you sure you want to remove this test data from the test case"
            itemName="'{{ addslashes($testData->name) }}'"
            dangerText="This action will only remove the association. The test data will remain in the system if used elsewhere."
            confirmText="Remove" />
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
        Alpine.data('testDataViewer', () => ({
            showDeleteModal: false,
            deleteConfirmed: false,
            isDeleting: false,
            requireConfirmation: true,

            copyToClipboard() {
                const content = `{{ addslashes($testData->content) }}`;
                navigator.clipboard.writeText(content).then(() => {
                    this.showNotification('info', 'Content copied to clipboard');
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                    this.showNotification('error', 'Failed to copy to clipboard');
                });
            },

            openDeleteModal() {
                this.showDeleteModal = true;
                this.deleteConfirmed = false;
            },

            async confirmDelete() {
                this.isDeleting = true;

                try {
                    const url = "{{ route('dashboard.projects.test-cases.data.detach', [$project->id, $testCase->id, $testData->id]) }}";
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        window.location.href = "{{ route('dashboard.projects.test-cases.data.index', [$project->id, $testCase->id]) }}";
                    } else {
                        throw new Error(result.message || 'Failed to remove test data');
                    }
                } catch (error) {
                    console.error(error);
                    this.showNotification('error', error.message || 'An error occurred');
                } finally {
                    this.isDeleting = false;
                    this.showDeleteModal = false;
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
