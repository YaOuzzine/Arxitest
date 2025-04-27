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
    <div x-data="testSuiteDetails({
        suiteId: '{{ $testSuite->id }}',
        suiteName: '{{ addslashes($testSuite->name) }}',
        projectId: '{{ $project->id }}',
        deleteUrl: '{{ route('dashboard.projects.test-suites.destroy', [$project->id, $testSuite->id]) }}',
        searchUrl: '{{ route('dashboard.projects.test-suites.available-test-cases', [$project->id, $testSuite->id]) }}',
        addCasesUrl: '{{ route('dashboard.projects.test-suites.add-test-cases', [$project->id, $testSuite->id]) }}',
        csrfToken: '{{ csrf_token() }}'
    })" x-init="initNotifications();
    initDragDrop();" class="relative">

        <!-- Split View Layout -->
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Main Column (Test Suite Details) -->
            <div class="flex-1">
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 mb-8">
                    <div class="flex items-center space-x-4">
                        <div
                            class="flex-shrink-0 p-3 bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-xl shadow-sm">
                            <i data-lucide="layers" class="w-8 h-8 text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-1">{{ $testSuite->name }}</h1>
                            <p class="text-zinc-600 dark:text-zinc-400 max-w-xl">
                                {{ $testSuite->description ?: 'No description provided.' }}</p>
                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-zinc-500 dark:text-zinc-400">
                                <span class="flex items-center"><i data-lucide="folder"
                                        class="w-3.5 h-3.5 mr-1.5"></i>Project: {{ $project->name }}</span>
                                <span class="flex items-center"><i data-lucide="clock"
                                        class="w-3.5 h-3.5 mr-1.5"></i>Updated:
                                    {{ $testSuite->updated_at->diffForHumans() }}</span>
                                <span class="flex items-center"><i data-lucide="settings"
                                        class="w-3.5 h-3.5 mr-1.5"></i>Priority:
                                    {{ ucfirst($testSuite->settings['default_priority'] ?? 'Medium') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3 flex-shrink-0">
                        <button class="btn-secondary inline-flex items-center px-4 py-2">
                            <i data-lucide="play" class="w-4 h-4 mr-2"></i> Run Suite
                        </button>
                        <a href="{{ route('dashboard.projects.test-suites.edit', [$project->id, $testSuite->id]) }}"
                            class="btn-outline inline-flex items-center px-4 py-2">
                            <i data-lucide="edit-3" class="w-4 h-4 mr-2"></i> Edit
                        </a>
                        <button @click="openDeleteModal()" class="btn-danger-outline inline-flex items-center px-4 py-2">
                            <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Delete
                        </button>
                    </div>
                </div>

                <!-- Test Cases Section -->
                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-zinc-200/50 dark:border-zinc-700/50 overflow-hidden">
                    <div class="p-6 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-zinc-800 dark:text-white">
                            Test Cases <span class="ml-2 text-zinc-500 dark:text-zinc-400 text-sm"
                                x-text="'(' + suiteTestCases.length + ')'"></span>
                        </h2>
                        <div class="flex items-center">
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 italic mr-3 hidden md:block">
                                <i data-lucide="info" class="w-4 h-4 inline mr-1"></i> Drag and drop test cases from the
                                sidebar
                            </p>
                            <button @click="toggleTestCaseSidebar"
                                class="btn-outline inline-flex items-center p-2 lg:hidden">
                                <i data-lucide="search" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <div id="suite-test-cases-container" class="min-h-[200px] max-h-[600px] overflow-y-auto p-2 relative"
                        x-bind:class="{ 'drop-target': isDragOver }" @dragover.prevent="isDragOver = true"
                        @dragleave.prevent="isDragOver = false" @drop.prevent="handleDrop($event)">

                        <!-- Test Cases Table -->
                        <table class="w-full divide-y divide-zinc-200 dark:divide-zinc-700"
                            x-show="suiteTestCases.length > 0">
                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        Title</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        Story</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                <template x-for="(testCase, index) in suiteTestCases" :key="testCase.id">
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors">
                                        <td class="px-4 py-3 text-sm">
                                            <span class="font-medium text-zinc-900 dark:text-white"
                                                x-text="testCase.title"></span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400"
                                            x-text="testCase.story ? testCase.story.title : 'N/A'"></td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex px-2 py-1 text-xs rounded-full"
                                                x-bind:class="{
                                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': testCase
                                                        .status === 'active',
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': testCase
                                                        .status === 'draft',
                                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': testCase
                                                        .status === 'deprecated' || testCase.status === 'archived'
                                                }"
                                                x-text="testCase.status.charAt(0).toUpperCase() + testCase.status.slice(1)"></span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right">
                                            <div class="flex items-center justify-end space-x-2">
                                                <a x-bind:href="`/dashboard/projects/${projectId}/test-cases/${testCase.id}`"
                                                    class="text-zinc-500 hover:text-indigo-600 dark:text-zinc-400 dark:hover:text-indigo-400 transition-colors">
                                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                                </a>
                                                <button @click="removeTestCase(index, testCase.id)"
                                                    class="text-zinc-500 hover:text-red-600 dark:text-zinc-400 dark:hover:text-red-400 transition-colors">
                                                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div x-show="suiteTestCases.length === 0" class="text-center py-12">
                            <div
                                class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-700 mb-4">
                                <i data-lucide="file-search" class="w-8 h-8 text-zinc-500 dark:text-zinc-400"></i>
                            </div>
                            <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200 mb-2">No Test Cases</h3>
                            <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-6">
                                This test suite doesn't have any test cases yet. Search for existing test cases in the
                                sidebar
                                and drag them here to add them to this suite.
                            </p>
                            <button @click="toggleTestCaseSidebar"
                                class="btn-primary inline-flex items-center px-4 py-2 lg:hidden">
                                <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                                Find Test Cases
                            </button>
                        </div>

                        <!-- Drop Overlay (Shows when dragging) -->
                        <div x-show="isDragOver"
                            class="">
                            <div
                                class="text-center bg-white/80 dark:bg-zinc-800/80 backdrop-blur-sm p-4 rounded-lg shadow-lg">
                                <i data-lucide="arrow-down-circle"
                                    class="w-10 h-10 text-indigo-600 dark:text-indigo-400 mx-auto mb-2"></i>
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Drop to Add</h3>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">Release to add this test case to the
                                    suite</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar (Test Case Search) -->
            <div x-ref="sidebar"
                class="w-full lg:w-[350px] xl:w-[400px] bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-zinc-200/50 dark:border-zinc-700/50 overflow-hidden"
                x-bind:class="{ 'hidden lg:block': !sidebarOpen, 'block': sidebarOpen }">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-medium text-zinc-800 dark:text-white">Find Test Cases</h3>
                        <button @click="toggleTestCaseSidebar"
                            class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 lg:hidden">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div class="relative">
                        <input type="text" x-model="searchQuery" @input="debounceSearch()"
                            placeholder="Search test cases..."
                            class="w-full pl-9 pr-3 py-2 bg-zinc-50 dark:bg-zinc-700/50 border border-zinc-200 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 text-sm">
                        <i data-lucide="search"
                            class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 transform -translate-y-1/2"></i>
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">
                        <i data-lucide="info" class="w-3 h-3 inline"></i>
                        Drag test cases to add them to the suite
                    </p>
                </div>

                <div class="p-1 max-h-[calc(100vh-220px)] overflow-y-auto">
                    <!-- Loading State -->
                    <div x-show="isSearching" class="py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        <i data-lucide="loader" class="w-5 h-5 mx-auto mb-2 animate-spin"></i>
                        Searching...
                    </div>

                    <!-- No Results State -->
                    <div x-show="!isSearching && availableTestCases.length === 0 && searchQuery" class="py-6 text-center">
                        <i data-lucide="search-x" class="w-8 h-8 text-zinc-400 mx-auto mb-2"></i>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">No test cases available to add. All test cases
                            are already in this suite or no test cases exist for this project.</p>
                    </div>

                    <!-- Empty Initial State -->
                    <div x-show="!isSearching && availableTestCases.length === 0 && !searchQuery"
                        class="py-6 text-center">
                        <i data-lucide="search" class="w-8 h-8 text-zinc-400 mx-auto mb-2"></i>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Start typing to search for test cases</p>
                    </div>

                    <!-- Test Case Results -->
                    <div x-show="!isSearching && availableTestCases.length > 0" class="space-y-2">
                        <template x-for="testCase in availableTestCases" :key="testCase.id">
                            <div class="p-3 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg cursor-move hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                draggable="true" @dragstart="handleDragStart($event, testCase)">
                                <div class="flex items-start">
                                    <div class="mr-2 mt-0.5 text-zinc-400 dark:text-zinc-500">
                                        <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-zinc-900 dark:text-white"
                                            x-text="testCase.title"></h4>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5"
                                            x-text="testCase.story ? `Story: ${testCase.story.title}` : 'No story'"></p>
                                        <div class="flex items-center mt-1">
                                            <span class="inline-flex px-1.5 py-0.5 text-xs rounded-full mr-2"
                                                x-bind:class="{
                                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': testCase
                                                        .status === 'active',
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': testCase
                                                        .status === 'draft',
                                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': testCase
                                                        .status === 'deprecated' || testCase.status === 'archived'
                                                }"
                                                x-text="testCase.status.charAt(0).toUpperCase() + testCase.status.slice(1)"></span>
                                            <span class="inline-flex px-1.5 py-0.5 text-xs rounded-full"
                                                x-bind:class="{
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400': testCase
                                                        .priority === 'high',
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': testCase
                                                        .priority === 'medium',
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-800/50 dark:text-gray-400': testCase
                                                        .priority === 'low'
                                                }"
                                                x-text="testCase.priority.charAt(0).toUpperCase() + testCase.priority.slice(1)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Pagination -->
                        <div
                            class="pt-2 pb-1 px-3 flex justify-between items-center text-xs text-zinc-500 dark:text-zinc-400 border-t border-zinc-200 dark:border-zinc-700">
                            <span x-text="`Showing ${availableTestCases.length} of ${pagination.total}`"></span>
                            <div class="flex items-center space-x-1">
                                <button @click="changePage(pagination.current_page - 1)"
                                    :disabled="pagination.current_page <= 1"
                                    :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page <= 1 }"
                                    class="p-1 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700">
                                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                                </button>
                                <span x-text="`Page ${pagination.current_page} of ${pagination.last_page}`"></span>
                                <button @click="changePage(pagination.current_page + 1)"
                                    :disabled="pagination.current_page >= pagination.last_page"
                                    :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page >= pagination.last_page }"
                                    class="p-1 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700">
                                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete confirmation modal -->
        <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-zinc-900/50 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
                    aria-hidden="true"></div>

                <!-- Modal panel -->
                <div
                    class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full opacity-100 translate-y-0 sm:scale-100 modal-content">
                    <div class="bg-white dark:bg-zinc-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600 dark:text-red-400"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-white" id="modal-title">
                                    Delete Test Suite
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        Are you sure you want to delete "<span x-text="suiteName"
                                            class="font-medium"></span>"? This action cannot be undone. All associated test
                                        cases will also be deleted.
                                    </p>
                                </div>
                                <div class="mt-4">
                                    <label for="confirm-delete-text"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Type "<span
                                            x-text="suiteName" class="font-semibold"></span>" to confirm:</label>
                                    <input type="text" id="confirm-delete-text" x-model="deleteConfirmText"
                                        class="mt-1 block w-full border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm py-2 px-3 focus:ring-red-500 focus:border-red-500 sm:text-sm dark:bg-zinc-700 dark:text-white">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="confirmDelete()" type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="isDeleting || deleteConfirmText !== suiteName">
                            <span x-show="!isDeleting">Delete</span>
                            <span x-show="isDeleting" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Deleting...
                            </span>
                        </button>
                        <button @click="closeDeleteModal()" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-zinc-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Area -->
        <div x-show="showNotification" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2" class="fixed bottom-6 right-6 z-50">
            <div class="min-w-[300px] bg-white dark:bg-zinc-800 rounded-xl shadow-xl border border-zinc-200 dark:border-zinc-700 flex items-start p-4 space-x-3"
                :class="{
                    'bg-green-50/90 border-green-200 dark:bg-green-900/30 dark:border-green-800': notificationType === 'success',
                    'bg-red-50/90 border-red-200 dark:bg-red-900/30 dark:border-red-800': notificationType === 'error'
                }">
                <i data-lucide="check-circle" x-show="notificationType === 'success'"
                    class="w-6 h-6 flex-shrink-0 text-green-600 dark:text-green-400"></i>
                <i data-lucide="alert-circle" x-show="notificationType === 'error'"
                    class="w-6 h-6 flex-shrink-0 text-red-600 dark:text-red-400"></i>
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
    </div>
@endsection

@push('styles')
    <style>
        /* Custom button styles */
        .btn-primary {
            @apply inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200;
        }

        .btn-secondary {
            @apply inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200;
        }

        .btn-outline {
            @apply inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-transparent hover:bg-zinc-50 dark:hover:bg-zinc-700/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200;
        }

        .btn-danger-outline {
            @apply inline-flex items-center px-4 py-2 border border-red-300 dark:border-red-700 rounded-lg shadow-sm text-sm font-medium text-red-600 dark:text-red-400 bg-transparent hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200;
        }

        /* Drop target styling */
        .drop-target {
            @apply border-2 border-dashed border-indigo-500/50 dark:border-indigo-500/50 bg-indigo-50/30 dark:bg-indigo-900/10 rounded-xl;
        }

        /* Draggable elements */
        [draggable="true"] {
            @apply cursor-grab active:cursor-grabbing;
        }

        /* Sidebar scrollable area */
        .sidebar-scroll {
            @apply scrollbar-thin scrollbar-thumb-zinc-300 dark:scrollbar-thumb-zinc-600 scrollbar-track-transparent;
        }

        /* Animation for drag-over state */
        @keyframes pulse-border {

            0%,
            100% {
                border-color: rgba(99, 102, 241, 0.3);
            }

            50% {
                border-color: rgba(99, 102, 241, 0.8);
            }
        }

        .animate-pulse-border {
            animation: pulse-border 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Animation for drop indicator */
        .drop-indicator {
            @apply opacity-0 scale-95 transition-all duration-200;
        }

        .drop-target .drop-indicator {
            @apply opacity-100 scale-100;
        }

        /* Mobile optimizations */
        @media (max-width: 1023px) {
            .sidebar-open-overlay {
                @apply fixed inset-0 bg-zinc-900/20 backdrop-blur-sm z-40;
            }
        }


        /* Updated styles for drop overlay */
        #suite-test-cases-container {
            position: relative;
        }

        #suite-test-cases-container>div[x-show="isDragOver"] {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('testSuiteDetails', (config) => ({
                // Configuration properties from backend
                suiteId: config.suiteId,
                suiteName: config.suiteName,
                projectId: config.projectId,
                deleteUrl: config.deleteUrl,
                searchUrl: config.searchUrl,
                addCasesUrl: config.addCasesUrl,
                csrfToken: config.csrfToken,

                // State variables
                showDeleteModal: false,
                deleteConfirmText: '',
                isDeleting: false,

                // Notification system
                showNotification: false,
                notificationType: 'success',
                notificationMessage: '',

                // Test case management
                suiteTestCases: [], // Current test cases in this suite
                availableTestCases: [], // Search results for available test cases
                searchQuery: '', // Current search query
                isSearching: false, // Search loading state
                searchTimeout: null, // For debouncing search

                // Drag and drop
                isDragOver: false, // Whether we're dragging over the drop area
                currentlyDragging: null, // The test case being dragged

                // Sidebar state
                sidebarOpen: false, // On mobile, whether the sidebar is open

                // Pagination for search results
                pagination: {
                    current_page: 1,
                    last_page: 1,
                    per_page: 10,
                    total: 0
                },

                // Initialization function
                initNotifications() {
                    @if (session('success'))
                        this.showSuccess('{{ session('success') }}')
                    @endif
                    @if (session('error'))
                        this.showError('{{ session('error') }}')
                    @endif

                    // Load initial test cases
                    this.loadSuiteTestCases();
                },

                // Initialize drag and drop (separate to avoid overwhelming init)
                initDragDrop() {
                    // Redundancy to ensure icons are loaded
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });
                },

                // Load test cases already in this suite
                async loadSuiteTestCases() {
                    try {
                        // In a real environment, you might fetch this from the server
                        // For this example, we'll use the initial load from the controller
                        this.suiteTestCases = @json($testCases) || [];

                        // Refresh icons after data loads
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                        });
                    } catch (error) {
                        console.error('Error loading suite test cases:', error);
                        this.showError(
                            'Failed to load test cases. Please try refreshing the page.');
                    }
                },

                // Search for available test cases
                async searchTestCases() {
                    this.isSearching = true;

                    try {
                        // Fix URL construction issue by ensuring no template ID is appended
                        const url = new URL(this.searchUrl, window.location.origin);
                        url.searchParams.append('search', this.searchQuery);
                        url.searchParams.append('page', this.pagination.current_page);
                        url.searchParams.append('per_page', this.pagination.per_page);

                        const response = await fetch(url.toString(), {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            }
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }

                        const result = await response.json();

                        if (result.success) {
                            this.availableTestCases = result.data.test_cases || [];
                            this.pagination = result.data.pagination || {
                                current_page: 1,
                                per_page: 10,
                                last_page: 1,
                                total: this.availableTestCases.length
                            };
                        } else {
                            throw new Error(result.message || 'Failed to fetch test cases');
                        }

                        // Refresh icons after results load
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                        });
                    } catch (error) {
                        console.error('Error searching test cases:', error);
                        this.showError(`Search failed: ${error.message}`);
                        this.availableTestCases = [];
                    } finally {
                        this.isSearching = false;
                    }
                },

                // Debounce the search function to prevent excessive API calls
                debounceSearch() {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.pagination.current_page = 1; // Reset to first page on new search
                        this.searchTestCases();
                    }, 300);
                },

                // Change page in search results pagination
                changePage(page) {
                    if (page < 1 || page > this.pagination.last_page) return;
                    this.pagination.current_page = page;
                    this.searchTestCases();
                },

                // Toggle the sidebar visibility (for mobile)
                toggleTestCaseSidebar() {
                    this.sidebarOpen = !this.sidebarOpen;
                    if (this.sidebarOpen && this.availableTestCases.length === 0 && !this.searchQuery) {
                        // Load some initial data if opening sidebar for first time
                        this.searchTestCases();
                    }
                },

                // Handle drag start
                handleDragStart(event, testCase) {
                    this.currentlyDragging = testCase;
                    // Set the drag data (required for Firefox)
                    event.dataTransfer.setData('text/plain', testCase.id);
                    // Set the drag effect
                    event.dataTransfer.effectAllowed = 'move';
                },

                // Handle drop of a test case to add it to the suite
                async handleDrop(event) {
                    this.isDragOver = false;

                    // Safety check in case drag data got lost
                    if (!this.currentlyDragging) {
                        const testCaseId = event.dataTransfer.getData('text/plain');
                        if (!testCaseId) {
                            this.showError("Couldn't add test case: drag data missing");
                            return;
                        }

                        // Try to find the test case in available cases
                        const testCase = this.availableTestCases.find(tc => tc.id === testCaseId);
                        if (!testCase) {
                            this.showError("Couldn't identify the dragged test case");
                            return;
                        }
                        this.currentlyDragging = testCase;
                    }

                    try {
                        // Check if test case is already in the suite
                        const isDuplicate = this.suiteTestCases.some(tc => tc.id === this
                            .currentlyDragging.id);
                        if (isDuplicate) {
                            this.showError(
                                `Test case "${this.currentlyDragging.title}" is already in this suite.`
                            );
                            return;
                        }

                        // Fix: Use the correct URL with the test suite ID included
                        const addCasesUrl =
                            `/dashboard/projects/${this.projectId}/test-suites/${this.suiteId}/add-test-cases`;

                        // Add the test case to the suite via API
                        const response = await fetch(addCasesUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify({
                                test_case_ids: [this.currentlyDragging.id]
                            })
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }

                        const result = await response.json();

                        if (result.success) {
                            // Add to our local array
                            this.suiteTestCases.push(this.currentlyDragging);

                            // Show success message
                            this.showSuccess(
                                `Added "${this.currentlyDragging.title}" to this suite`);

                            // Remove from available test cases list if found (to prevent re-adding)
                            this.availableTestCases = this.availableTestCases.filter(
                                tc => tc.id !== this.currentlyDragging.id
                            );
                        } else {
                            throw new Error(result.message || 'Failed to add test case to suite');
                        }
                    } catch (error) {
                        console.error('Error adding test case to suite:', error);
                        this.showError(`Failed to add test case: ${error.message}`);
                    } finally {
                        this.currentlyDragging = null;
                    }
                },

                // Remove a test case from the suite (update the test case to null suite_id)
                async removeTestCase(index, testCaseId) {
                    const testCase = this.suiteTestCases[index];
                    if (!testCase) return;

                    try {
                        // Construct the correct URL for removing a test case from a suite
                        const url =
                            `/dashboard/projects/${this.projectId}/test-cases/${testCaseId}/remove-from-suite`;

                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify({
                                suite_id: this.suiteId
                            })
                        });

                        if (!response.ok) {
                            const errorData = await response.json();
                            throw new Error(errorData.message ||
                                `HTTP error! Status: ${response.status}`);
                        }

                        const result = await response.json();

                        if (result.success) {
                            // Remove from our local array
                            this.suiteTestCases.splice(index, 1);

                            // Show success message
                            this.showSuccess(result.message ||
                                `Removed "${testCase.title}" from this suite`);


                            this.searchTestCases();
                        } else {
                            throw new Error(result.message ||
                                'Failed to remove test case from suite');
                        }
                    } catch (error) {
                        console.error('Error removing test case from suite:', error);
                        this.showError(`Failed to remove test case: ${error.message}`);
                    }
                },

                // Success notification
                showSuccess(message) {
                    this.notificationType = 'success';
                    this.notificationMessage = message;
                    this.showNotification = true;
                    setTimeout(() => this.hideNotification(), 5000);
                },

                // Error notification
                showError(message) {
                    this.notificationType = 'error';
                    this.notificationMessage = message;
                    this.showNotification = true;
                    setTimeout(() => this.hideNotification(), 7000); // Show errors longer
                },

                // Hide the notification
                hideNotification() {
                    this.showNotification = false;
                },

                // Open delete modal
                openDeleteModal() {
                    this.deleteConfirmText = ''; // Reset confirmation text
                    this.isDeleting = false; // Reset deleting state
                    this.showDeleteModal = true;
                    // Delay focus to ensure modal is visible
                    this.$nextTick(() => {
                        document.getElementById('confirm-delete-text')?.focus();
                    });
                },

                // Close delete modal
                closeDeleteModal() {
                    if (!this.isDeleting) {
                        this.showDeleteModal = false;
                        this.deleteConfirmText = '';
                    }
                },

                initDragDrop() {
                    // Redundancy to ensure icons are loaded
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });

                    // Load available test cases immediately after page loads
                    this.searchTestCases();
                },

                // Confirm delete action
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
                            window.location.href =
                                `{{ route('dashboard.projects.test-suites.index', $project->id) }}`;
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
