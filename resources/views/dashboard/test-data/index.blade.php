{{-- resources/views/dashboard/test-data/index.blade.php --}}
@extends('layouts.dashboard')

@section('title', "{$testCase->title} - Test Data")

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
        <span class="text-zinc-700 dark:text-zinc-300">Test Data</span>
    </li>
@endsection

@section('content')
    <div class="h-full space-y-6" x-data="testDataManager">
        <!-- Header -->
        <x-index-header
            title="Test Data"
            description="Manage test data for {{ $testCase->title }}"
            :createRoute="route('dashboard.projects.test-cases.data.create', [$project->id, $testCase->id])"
            createText="Add Test Data"
            createIcon="plus" />

        <!-- Test Data List -->
        <x-list-view
            :items="$testData"
            :columns="[
                'name' => 'Name',
                'format' => 'Format',
                'is_sensitive' => 'Sensitive',
                'created_at' => 'Created',
                'actions' => 'Actions',
            ]"
            :sortField="request('sort', 'created_at')"
            :sortDirection="request('direction', 'desc')"
            entityName="Test Data"
            emptyStateTitle="No test data found"
            emptyStateDescription="Create your first test data set to use with this test case."
            emptyStateIcon="database"
            :createRoute="route('dashboard.projects.test-cases.data.create', [$project->id, $testCase->id])"
            createLabel="Add Test Data">

            @foreach ($testData as $data)
                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/20 transition-colors duration-200">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-zinc-900 dark:text-white">
                            <a href="{{ route('dashboard.projects.test-cases.data.show', [$project->id, $testCase->id, $data->id]) }}"
                               class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200 group">
                                {{ $data->name }}
                                <i data-lucide="arrow-up-right" class="h-3 w-3 ml-1 inline-block opacity-0 group-hover:opacity-100 transition-opacity duration-200"></i>
                            </a>
                        </div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ \Illuminate\Support\Str::limit($data->pivot->usage_context ?? 'General purpose', 50) }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ match($data->format) {
                                'json' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                'csv' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                'xml' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                'plain' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700/30 dark:text-zinc-300',
                                default => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                            } }}">
                            {{ strtoupper($data->format) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                        @if($data->is_sensitive)
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
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $data->created_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-3">
                            <button type="button" @click="previewData('{{ $data->id }}', '{{ addslashes($data->name) }}', '{{ $data->format }}')"
                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 p-1 rounded-full hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                            <a href="{{ route('dashboard.projects.test-cases.data.edit', [$project->id, $testCase->id, $data->id]) }}"
                               class="text-amber-600 dark:text-amber-400 hover:text-amber-900 dark:hover:text-amber-300 p-1 rounded-full hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                            <button type="button"
                                    @click="openDeleteModal('{{ $data->id }}', '{{ addslashes($data->name) }}')"
                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors duration-200 relative group"
                                    title="Delete">
                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-list-view>

        <!-- Delete Confirmation Modal -->
        <x-modals.delete-confirmation
            title="Remove Test Data"
            message="Are you sure you want to remove this test data from the test case"
            itemName="deleteDataName"
            dangerText="This action will only remove the association. The test data will remain in the system if used elsewhere."
            confirmText="Remove" />

        <!-- Preview Modal -->
        <div x-show="showPreviewModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="preview-modal-title" role="dialog" aria-modal="true" style="display: none;" id="preview-modal">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
                <div class="fixed inset-0 bg-zinc-900/60 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
                    @click="closeModal"></div>
                <div
                    class="relative inline-block w-full max-w-4xl p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-zinc-800 shadow-xl rounded-2xl">
                    <div class="absolute top-0 right-0 pt-5 pr-5">
                        <button type="button" @click="closeModal" class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div>
                        <h3 class="text-xl font-medium text-zinc-900 dark:text-zinc-100" id="preview-modal-title"
                            x-text="'Preview: ' + previewTitle">Preview</h3>

                        <div class="mt-4">
                            <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 max-h-96 overflow-auto">
                                <pre x-html="previewContent" class="font-mono text-sm whitespace-pre-wrap break-words text-zinc-800 dark:text-zinc-200"></pre>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="button" @click="closeModal"
                            class="inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Close
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
        Alpine.data('testDataManager', () => ({
            showDeleteModal: false,
            showPreviewModal: false,
            deleteConfirmed: false,
            isDeleting: false,
            deleteDataId: null,
            deleteDataName: '',
            previewTitle: '',
            previewFormat: '',
            previewContent: '',
            requireConfirmation: true,

            openDeleteModal(id, name) {
                this.deleteDataId = id;
                this.deleteDataName = name;
                this.deleteConfirmed = false;
                this.showDeleteModal = true;
            },

            closeDeleteModal() {
                this.showDeleteModal = false;
                this.deleteDataId = null;
                this.deleteDataName = '';
                this.deleteConfirmed = false;
            },

            closeModal() {
                this.showPreviewModal = false;
                this.previewContent = '';
                this.previewTitle = '';
            },

            async previewData(id, name, format) {
                this.previewTitle = name;
                this.previewFormat = format;
                this.previewContent = 'Loading...';
                this.showPreviewModal = true;

                try {
                    const response = await fetch(`{{ url('/dashboard/projects/' . $project->id . '/test-cases/' . $testCase->id . '/data') }}/${id}/content`);

                    if (response.ok) {
                        const data = await response.json();
                        if (data.success) {
                            // Format and syntax highlight based on format
                            let content = data.content;

                            if (format === 'json') {
                                try {
                                    const parsed = JSON.parse(content);
                                    content = JSON.stringify(parsed, null, 4);
                                } catch (e) {
                                    // If parsing fails, just show raw content
                                    console.error('Error parsing JSON', e);
                                }
                            }

                            // Escape HTML to prevent XSS
                            content = this.escapeHtml(content);

                            // Apply basic syntax highlighting classes
                            if (format === 'json' || format === 'xml') {
                                content = this.highlightSyntax(content, format);
                            }

                            this.previewContent = content;
                        } else {
                            this.previewContent = 'Error: ' + (data.message || 'Unable to load content');
                        }
                    } else {
                        this.previewContent = 'Error: Unable to load content';
                    }
                } catch (error) {
                    console.error('Failed to fetch content:', error);
                    this.previewContent = 'Error: ' + error.message;
                }
            },

            async confirmDelete() {
                if (!this.deleteDataId) return;
                this.isDeleting = true;

                try {
                    const url = `{{ url('/dashboard/projects/' . $project->id . '/test-cases/' . $testCase->id . '/data') }}/${this.deleteDataId}`;
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        // Remove row from DOM or reload page
                        location.reload();
                    } else {
                        throw new Error(result.message || 'Failed to remove test data');
                    }
                } catch (error) {
                    console.error(error);
                    this.showNotification('error', error.message || 'An error occurred');
                } finally {
                    this.isDeleting = false;
                    this.closeDeleteModal();
                }
            },

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            },

            highlightSyntax(content, format) {
                // Very basic syntax highlighting
                if (format === 'json') {
                    return content
                        .replace(/"([^"]+)":/g, '<span class="text-purple-600 dark:text-purple-400">"$1"</span>:')
                        .replace(/"([^"]+)"(?!:)/g, '<span class="text-green-600 dark:text-green-400">"$1"</span>');
                } else if (format === 'xml') {
                    return content
                        .replace(/&lt;([\/\w]+)(?=[\s&gt;])/g, '&lt;<span class="text-purple-600 dark:text-purple-400">$1</span>')
                        .replace(/([^\s]+)="([^"]+)"/g, '<span class="text-blue-600 dark:text-blue-400">$1</span>="<span class="text-green-600 dark:text-green-400">$2</span>"');
                }
                return content;
            },

            showNotification(type, message) {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { type, message }
                }));
            }
        }));
    });
</script>
@endpush
