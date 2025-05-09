{{-- resources/views/dashboard/test-scripts/index.blade.php --}}
@extends('layouts.dashboard')

@section('title', "{$testCase->title} - Test Scripts")

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
        <span class="text-zinc-700 dark:text-zinc-300">Test Scripts</span>
    </li>
@endsection

@section('content')
    <div class="h-full space-y-6" x-data="testScriptsManager">
        <!-- Header -->
        <x-index-header
            title="Test Scripts"
            description="Manage automated test scripts for {{ $testCase->title }}"
            :createRoute="route('dashboard.projects.test-cases.scripts.create', [$project->id, $testCase->id])"
            createText="New Test Script"
            createIcon="plus" />

        <!-- Scripts List -->
        <x-list-view
            :items="$testScripts"
            :columns="[
                'name' => 'Name',
                'framework_type' => 'Framework',
                'created_at' => 'Created',
                'updated_at' => 'Updated',
                'actions' => 'Actions',
            ]"
            :sortField="request('sort', 'updated_at')"
            :sortDirection="request('direction', 'desc')"
            entityName="Test Script"
            emptyStateTitle="No test scripts found"
            emptyStateDescription="Create your first test script to automate testing for this test case."
            emptyStateIcon="code"
            :createRoute="route('dashboard.projects.test-cases.scripts.create', [$project->id, $testCase->id])"
            createLabel="Create Test Script">

            @foreach ($testScripts as $script)
                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/20 transition-colors duration-200">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-zinc-900 dark:text-white">
                            <a href="{{ route('dashboard.projects.test-cases.scripts.show', [$project->id, $testCase->id, $script->id]) }}"
                               class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200 group">
                                {{ $script->name }}
                                <i data-lucide="arrow-up-right" class="h-3 w-3 ml-1 inline-block opacity-0 group-hover:opacity-100 transition-opacity duration-200"></i>
                            </a>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ match($script->framework_type) {
                                'selenium-python' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                'cypress' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700/30 dark:text-zinc-300',
                            } }}">
                            {{ match($script->framework_type) {
                                'selenium-python' => 'Selenium Python',
                                'cypress' => 'Cypress',
                                'other' => 'Other',
                                default => $script->framework_type,
                            } }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $script->created_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $script->updated_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('dashboard.projects.test-cases.scripts.show', [$project->id, $testCase->id, $script->id]) }}"
                               class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 p-1 rounded-full hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                            <button type="button"
                                    @click="openDeleteModal('{{ $script->id }}', '{{ addslashes($script->name) }}')"
                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors duration-200 relative group"
                                    title="Delete">
                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach

            @if ($testScripts instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <x-slot name="pagination">
                    {{ $testScripts->onEachSide(1)->links() }}
                </x-slot>
            @endif
        </x-list-view>

        <x-modals.delete-confirmation
            title="Delete Test Script"
            message="Are you sure you want to delete the test script"
            itemName="deleteScriptName"
            dangerText="This action cannot be undone."
            confirmText="Delete Script" />
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('testScriptsManager', () => ({
            showDeleteModal: false,
            deleteConfirmed: false,
            isDeleting: false,
            deleteScriptId: null,
            deleteScriptName: '',
            requireConfirmation: true,

            openDeleteModal(id, name) {
                this.deleteScriptId = id;
                this.deleteScriptName = name;
                this.deleteConfirmed = false;
                this.showDeleteModal = true;
            },

            closeDeleteModal() {
                this.showDeleteModal = false;
                this.deleteScriptId = null;
                this.deleteScriptName = '';
                this.deleteConfirmed = false;
            },

            async confirmDelete() {
                if (!this.deleteScriptId) return;
                this.isDeleting = true;

                try {
                    const url = `{{ route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id]) }}/${this.deleteScriptId}`;
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
                        throw new Error(result.message || 'Failed to delete test script');
                    }
                } catch (error) {
                    console.error(error);
                    this.showNotification('error', error.message || 'An error occurred');
                } finally {
                    this.isDeleting = false;
                    this.closeDeleteModal();
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
