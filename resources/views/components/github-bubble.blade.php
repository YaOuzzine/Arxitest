@if (session('github_connected', false))
    <div id="github-bubble" class="fixed bottom-14 right-6 z-50">
        <!-- Bubble Button -->
        <button id="github-bubble-toggle"
            class="group bg-gradient-to-br from-green-600 to-green-700 dark:from-gray-700 dark:to-gray-800 text-white p-3.5 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center hover:scale-105 hover:rotate-3 ring-offset-2 ring-offset-white dark:ring-offset-zinc-900 hover:ring-2 ring-indigo-300">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="w-6 h-6 transform group-hover:scale-110 transition-transform">
                <path
                    d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22">
                </path>
            </svg>
        </button>

        <!-- Progress Indicator -->
        <div id="github-progress-indicator"
            class="hidden fixed bottom-6 right-20 bg-white dark:bg-zinc-800 shadow-lg rounded-lg p-3 flex flex-col items-start gap-2 border border-zinc-200 dark:border-zinc-700 animate-fade-in min-w-[300px]">
            <div class="flex items-center justify-between w-full">
                <h3 class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                    Creating Project
                </h3>
                <button id="close-progress"
                    class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="text-sm text-zinc-600 dark:text-zinc-400" id="progress-text">Initializing...</div>
            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2.5 mb-1">
                <div id="progress-bar"
                    class="bg-gradient-to-r from-indigo-600 to-purple-600 h-2.5 rounded-full transition-all duration-300"
                    style="width: 0%"></div>
            </div>
            <div class="flex items-center justify-between w-full text-xs text-zinc-500 dark:text-zinc-500">
                <span id="progress-percentage">0%</span>
                <span id="progress-time">00:00</span>
            </div>
            <!-- Success/Failure Indicator - Hidden by default -->
            <div id="progress-complete" class="hidden pt-2 mt-2 border-t border-zinc-200 dark:border-zinc-700 w-full">
                <div id="success-indicator"
                    class="hidden flex items-center text-sm text-emerald-600 dark:text-emerald-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="w-5 h-5 mr-1.5">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span>Project created successfully!</span>
                </div>
                <div id="failure-indicator" class="hidden flex items-center text-sm text-red-600 dark:text-red-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="w-5 h-5 mr-1.5">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <span id="error-message">An error occurred</span>
                </div>
                <div class="mt-2 flex justify-end">
                    <button id="view-project-btn"
                        class="hidden text-xs px-3 py-1.5 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                        View Project
                    </button>
                    <button id="dismiss-progress-btn"
                        class="text-xs px-3 py-1.5 bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 rounded-md hover:bg-zinc-300 dark:hover:bg-zinc-600 transition-colors ml-2">
                        Dismiss
                    </button>
                </div>
            </div>
        </div>

        <!-- Create Project Modal -->
        <div id="create-project-modal" class="hidden fixed inset-0 z-[60] overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-zinc-900 dark:bg-opacity-80 transition-opacity"
                    aria-hidden="true"></div>

                <!-- Modal panel -->
                <div
                    class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div
                        class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/30 dark:to-purple-900/30 px-4 py-3 sm:px-6 border-b border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <h3
                                class="text-lg leading-6 font-medium text-indigo-800 dark:text-indigo-200 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                Create Project from GitHub Repository
                            </h3>
                            <button type="button"
                                class="close-modal text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <div
                                    class="bg-zinc-50/70 dark:bg-zinc-700/30 rounded-lg p-4 mb-4 border border-zinc-200/70 dark:border-zinc-700/50">
                                    <div id="selected-repo-info" class="flex items-center">
                                        <div class="flex-shrink-0 mr-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="w-6 h-6 text-indigo-600 dark:text-indigo-400">
                                                <path
                                                    d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22">
                                                </path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 id="repo-name"
                                                class="text-base font-medium text-zinc-800 dark:text-zinc-200"></h4>
                                            <p id="repo-owner" class="text-sm text-zinc-600 dark:text-zinc-400"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="project-name"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Project Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="project-name"
                                        class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-zinc-800 dark:text-zinc-200"
                                        placeholder="Enter project name">
                                </div>

                                <div class="mb-4">
                                    <label for="max-file-size"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Maximum File Size (KB)
                                    </label>
                                    <input type="number" id="max-file-size"
                                        class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-zinc-800 dark:text-zinc-200"
                                        placeholder="Default: 1024 KB (1MB)" value="1024" min="1"
                                        max="5120">
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                        Files larger than this size will be skipped (1-5120 KB)
                                    </p>
                                </div>

                                <div class="mb-4">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="auto-generate-tests" type="checkbox"
                                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-zinc-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="auto-generate-tests"
                                                class="font-medium text-zinc-700 dark:text-zinc-200">Auto-generate
                                                tests</label>
                                            <p class="text-zinc-500 dark:text-zinc-400">Automatically generate test
                                                suites based on repository structure</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-zinc-50 dark:bg-zinc-800/80 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-zinc-200 dark:border-zinc-700/50">
                        <button id="create-project-confirm" type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Create Project
                        </button>
                        <button type="button"
                            class="close-modal mt-3 w-full inline-flex justify-center rounded-md border border-zinc-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Browser Panel with Fixed Height Structure -->
        <div id="github-browser"
            class="hidden fixed right-20 bottom-20 bg-white dark:bg-zinc-800 shadow-2xl rounded-xl w-96 border border-zinc-200 dark:border-zinc-700 transform transition-all duration-300 scale-95 opacity-0 flex flex-col h-[650px] max-h-[80vh]">
            <!-- Fixed Header -->
            <div
                class="flex-shrink-0 px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/30 dark:to-purple-900/30 flex justify-between items-center">
                <h3 class="text-lg font-medium text-indigo-800 dark:text-indigo-200 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="w-5 h-5">
                        <path
                            d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22">
                        </path>
                    </svg>
                    Repository Browser
                </h3>
                <button id="github-browser-close"
                    class="text-indigo-500 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-200 p-1 rounded-full hover:bg-indigo-100 dark:hover:bg-indigo-800/50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="w-5 h-5">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Repository Selector - Fixed -->
            <div class="flex-shrink-0 p-4 border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/80">
                <div class="relative">
                    <select id="github-repo-select"
                        class="w-full p-2.5 pl-4 pr-10 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-500/40 transition-all">
                        <option value="">Select a repository...</option>
                    </select>
                </div>
            </div>

            <!-- Selection Toolbar - Fixed when visible -->
            <div id="selection-toolbar"
                class="hidden flex-shrink-0 px-4 py-2 bg-indigo-50 dark:bg-indigo-900/30 border-b border-indigo-100 dark:border-indigo-800/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="select-all-files"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="select-all-files" class="text-sm text-indigo-700 dark:text-indigo-300">Select
                        All</label>
                </div>
                <button id="toggle-selection-mode"
                    class="text-xs px-2 py-1 rounded bg-indigo-100 dark:bg-indigo-800 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-200 dark:hover:bg-indigo-700 transition-colors">
                    Cancel Selection
                </button>
            </div>

            <!-- Scrollable Content Area -->
            <div class="flex-grow flex flex-col overflow-hidden">
                <!-- Breadcrumb - Fixed within scrollable area -->
                <div id="github-path-breadcrumb"
                    class="flex-shrink-0 px-4 py-2 text-sm text-indigo-600 dark:text-indigo-400 bg-white dark:bg-zinc-800 border-b border-zinc-100 dark:border-zinc-700/50 hidden overflow-x-auto whitespace-nowrap">
                </div>

                <!-- Loading Indicator -->
                <div id="github-loading"
                    class="hidden flex-grow flex flex-col items-center justify-center py-12 space-y-3">
                    <div
                        class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-indigo-600 dark:border-indigo-400">
                    </div>
                    <p class="text-sm text-indigo-600 dark:text-indigo-400">Loading content...</p>
                </div>

                <!-- Actual Scrollable Content -->
                <div class="flex-grow overflow-y-auto">
                    <!-- Directory Listing -->
                    <div id="github-content-listing" class="p-4"></div>

                    <!-- File Content View -->
                    <div id="github-file-content" class="hidden p-4">
                        <div
                            class="flex justify-between items-center pb-3 border-b border-zinc-200 dark:border-zinc-700/50 mb-3">
                            <button id="github-back-to-listing"
                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-200 flex items-center gap-1 hover:underline">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                    <path d="M19 12H5M12 19l-7-7 7-7"></path>
                                </svg>
                                Back to listing
                            </button>
                            <button id="github-use-file"
                                class="text-sm bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 px-3 py-1 rounded-md hover:bg-emerald-200 dark:hover:bg-emerald-800/50 transition-colors">
                                Use as context
                            </button>
                        </div>
                        <!-- File Preview -->
                        <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                            <div
                                class="px-3 py-2 bg-zinc-100 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 text-sm font-medium text-zinc-700 dark:text-zinc-300 flex items-center justify-between">
                                <span id="file-name">File Preview</span>
                                <button id="copy-file-content"
                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                        <rect x="9" y="9" width="13" height="13" rx="2"
                                            ry="2"></rect>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                    </svg>
                                </button>
                            </div>
                            <pre id="github-file-preview"
                                class="p-4 bg-white dark:bg-zinc-900 rounded-b-md overflow-x-auto text-sm text-zinc-800 dark:text-zinc-300 max-h-[300px]"></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selected Files Panel - Fixed -->
            <div id="selected-files-panel"
                class="hidden flex-shrink-0 border-t border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Selected Files (<span
                            id="selected-count">0</span>)</h4>
                    <button id="clear-selection"
                        class="text-xs text-zinc-600 dark:text-zinc-400 hover:text-red-600 dark:hover:text-red-400">
                        Clear
                    </button>
                </div>
                <div id="selected-files-list"
                    class="max-h-[100px] overflow-y-auto text-xs text-zinc-600 dark:text-zinc-400 mb-3"></div>
                <button id="add-selected-to-context"
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-sm py-2 px-4 rounded-md transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="w-4 h-4 mr-2">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Add Files to Context
                </button>
            </div>

            <!-- Actions Footer - Fixed -->
            <div id="github-actions"
                class="hidden flex-shrink-0 p-4 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
                <div class="grid grid-cols-2 gap-3">
                    <button id="github-create-project"
                        class="col-span-1 px-3 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-md hover:from-indigo-700 hover:to-indigo-800 transition-colors text-sm shadow flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="w-4 h-4 mr-2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        Create Project
                    </button>
                    <button id="enable-selection-mode"
                        class="col-span-1 px-3 py-2 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-md hover:from-emerald-700 hover:to-emerald-800 transition-colors text-sm shadow flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="w-4 h-4 mr-2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        Select Files
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Toast -->
        <div id="success-toast"
            class="hidden fixed bottom-24 right-6 bg-emerald-100 dark:bg-emerald-900/70 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 px-4 py-3 rounded-lg shadow-md transform translate-y-2 opacity-0 transition-all duration-300">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" class="w-5 h-5 mr-2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span id="toast-message">Files added to context</span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // DOM Element References
            const bubble = document.getElementById('github-bubble-toggle');
            const browser = document.getElementById('github-browser');
            const closeBtn = document.getElementById('github-browser-close');
            const repoSelect = document.getElementById('github-repo-select');
            const contentListing = document.getElementById('github-content-listing');
            const fileContent = document.getElementById('github-file-content');
            const filePreview = document.getElementById('github-file-preview');
            const fileName = document.getElementById('file-name');
            const backToListing = document.getElementById('github-back-to-listing');
            const useFileBtn = document.getElementById('github-use-file');
            const copyFileBtn = document.getElementById('copy-file-content');
            const breadcrumb = document.getElementById('github-path-breadcrumb');
            const loading = document.getElementById('github-loading');
            const actions = document.getElementById('github-actions');
            const successToast = document.getElementById('success-toast');
            const toastMessage = document.getElementById('toast-message');
            const progressIndicator = document.getElementById('github-progress-indicator');
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            const progressPercentage = document.getElementById('progress-percentage');
            const progressTime = document.getElementById('progress-time');
            const progressComplete = document.getElementById('progress-complete');
            const successIndicator = document.getElementById('success-indicator');
            const failureIndicator = document.getElementById('failure-indicator');
            const errorMessage = document.getElementById('error-message');
            const viewProjectBtn = document.getElementById('view-project-btn');
            const dismissProgressBtn = document.getElementById('dismiss-progress-btn');

            // Selection related elements
            const selectionToolbar = document.getElementById('selection-toolbar');
            const selectAllCheckbox = document.getElementById('select-all-files');
            const enableSelectionBtn = document.getElementById('enable-selection-mode');
            const toggleSelectionBtn = document.getElementById('toggle-selection-mode');
            const selectedFilesPanel = document.getElementById('selected-files-panel');
            const selectedFilesList = document.getElementById('selected-files-list');
            const selectedCount = document.getElementById('selected-count');
            const clearSelectionBtn = document.getElementById('clear-selection');
            const addSelectedBtn = document.getElementById('add-selected-to-context');

            // State
            let currentRepo = '';
            let currentOwner = '';
            let currentPath = '';
            let currentFileContent = '';
            let selectionMode = false;
            let selectedFiles = [];
            let currentJobId = null;
            let progressInterval = null;
            let projectId = null;
            let startTime = null;

            // Show/hide functions
            function showBrowser() {
                browser.classList.remove('hidden');
                // Start animation after unhiding
                setTimeout(() => {
                    browser.classList.remove('scale-95', 'opacity-0');
                    browser.classList.add('scale-100', 'opacity-100');
                }, 10);
            }

            function hideBrowser() {
                browser.classList.add('scale-95', 'opacity-0');
                // Wait for animation to complete before hiding
                setTimeout(() => {
                    browser.classList.add('hidden');
                }, 300);
            }

            // Progress tracking functions
            function startProgressTracking(jobId) {
                currentJobId = jobId;
                startTime = new Date();
                updateProgressTime();

                // Clear any existing interval
                if (progressInterval) {
                    clearInterval(progressInterval);
                }

                // Reset progress UI
                progressBar.style.width = '0%';
                progressPercentage.textContent = '0%';
                progressText.textContent = 'Initializing...';
                progressComplete.classList.add('hidden');
                successIndicator.classList.add('hidden');
                failureIndicator.classList.add('hidden');
                viewProjectBtn.classList.add('hidden');

                // Show progress indicator
                progressIndicator.classList.remove('hidden');

                // Set interval to check progress
                progressInterval = setInterval(checkJobProgress, 2000);
            }

            function updateProgressTime() {
                if (!startTime) return;

                const now = new Date();
                const diff = Math.floor((now - startTime) / 1000); // difference in seconds

                const minutes = Math.floor(diff / 60);
                const seconds = diff % 60;

                progressTime.textContent =
                    `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }

            function checkJobProgress() {
                if (!currentJobId) return;

                fetch(`/api/github/job-progress/${currentJobId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const progress = data.data.progress || 0;
                            const status = data.data.status || 'Processing...';

                            // Update progress UI
                            progressBar.style.width = `${progress}%`;
                            progressPercentage.textContent = `${progress}%`;
                            progressText.textContent = status;

                            // Update elapsed time
                            updateProgressTime();

                            // Check if job is completed
                            if (data.data.completed) {
                                clearInterval(progressInterval);
                                progressInterval = null;

                                // Show completion UI
                                progressComplete.classList.remove('hidden');

                                if (data.data.success) {
                                    successIndicator.classList.remove('hidden');

                                    // If project ID is available, show view project button
                                    if (data.data.project_id) {
                                        projectId = data.data.project_id;
                                        viewProjectBtn.classList.remove('hidden');
                                        viewProjectBtn.addEventListener('click', function() {
                                            window.location.href = `/dashboard/projects/${projectId}`;
                                        });
                                    }
                                } else {
                                    failureIndicator.classList.remove('hidden');
                                    errorMessage.textContent = data.data.status || 'An error occurred';
                                }
                            }
                        } else {
                            console.error('Error checking job progress:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error checking job progress:', error);
                    });
            }

            function stopProgressTracking() {
                if (progressInterval) {
                    clearInterval(progressInterval);
                    progressInterval = null;
                }

                progressIndicator.classList.add('hidden');
                currentJobId = null;
                startTime = null;
            }

            // Progress control button handlers
            document.getElementById('close-progress').addEventListener('click', function() {
                progressIndicator.classList.add('hidden');
            });

            dismissProgressBtn.addEventListener('click', function() {
                progressIndicator.classList.add('hidden');
                stopProgressTracking();
            });

            // Toggle browser visibility
            bubble.addEventListener('click', function() {
                if (browser.classList.contains('hidden')) {
                    showBrowser();
                    if (repoSelect.options.length <= 1) {
                        loadRepositories();
                    }
                } else {
                    hideBrowser();
                }
            });

            // Close browser
            closeBtn.addEventListener('click', function() {
                hideBrowser();
            });

            // Repository selection
            repoSelect.addEventListener('change', function() {
                const repoPath = repoSelect.value;
                if (!repoPath) return;

                const [owner, repo] = repoPath.split('/');
                currentOwner = owner;
                currentRepo = repo;
                currentPath = '';
                actions.classList.remove('hidden');

                // Clear selection when changing repos
                if (selectionMode) {
                    toggleSelectionMode();
                }

                loadContents(owner, repo, '');
            });

            // Back to listing
            backToListing.addEventListener('click', function() {
                fileContent.classList.add('hidden');
                contentListing.classList.remove('hidden');
            });

            // Copy file content
            copyFileBtn.addEventListener('click', function() {
                const textToCopy = filePreview.textContent;
                navigator.clipboard.writeText(textToCopy);
                showToast('File content copied to clipboard');
            });

            // Selection mode toggle
            enableSelectionBtn.addEventListener('click', function() {
                toggleSelectionMode(true);
            });

            toggleSelectionBtn.addEventListener('click', function() {
                toggleSelectionMode(false);
            });

            function toggleSelectionMode(enable = !selectionMode) {
                selectionMode = enable;

                if (selectionMode) {
                    // Enable selection mode
                    selectionToolbar.classList.remove('hidden');
                    selectedFilesPanel.classList.remove('hidden');
                    // Redraw content listing with checkboxes
                    loadContents(currentOwner, currentRepo, currentPath, true);
                } else {
                    // Disable selection mode
                    selectionToolbar.classList.add('hidden');
                    selectedFilesPanel.classList.add('hidden');
                    clearSelection();
                    // Redraw content listing without checkboxes
                    loadContents(currentOwner, currentRepo, currentPath, false);
                }
            }

            // Select all checkbox
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.file-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;

                    // Update selectedFiles array
                    const filePath = checkbox.dataset.path;
                    const fileName = checkbox.dataset.name;

                    if (selectAllCheckbox.checked && !selectedFiles.some(f => f.path ===
                            filePath)) {
                        selectedFiles.push({
                            path: filePath,
                            name: fileName,
                            type: checkbox.dataset.type
                        });
                    } else if (!selectAllCheckbox.checked) {
                        selectedFiles = selectedFiles.filter(f => f.path !== filePath);
                    }
                });

                updateSelectedFilesUI();
            });

            // Clear selection
            clearSelectionBtn.addEventListener('click', clearSelection);

            function clearSelection() {
                selectedFiles = [];
                selectAllCheckbox.checked = false;

                // Uncheck all checkboxes
                const checkboxes = document.querySelectorAll('.file-checkbox');
                checkboxes.forEach(checkbox => checkbox.checked = false);

                updateSelectedFilesUI();
            }

            // Update selected files UI
            function updateSelectedFilesUI() {
                selectedCount.textContent = selectedFiles.length;

                // Update the list
                selectedFilesList.innerHTML = '';

                if (selectedFiles.length === 0) {
                    selectedFilesList.innerHTML = '<div class="italic">No files selected</div>';
                    addSelectedBtn.disabled = true;
                } else {
                    addSelectedBtn.disabled = false;
                    selectedFiles.forEach((file, index) => {
                        const fileItem = document.createElement('div');
                        fileItem.className =
                            'flex items-center justify-between py-1 border-b border-zinc-100 dark:border-zinc-700 last:border-0';

                        // File icon based on type
                        const icon = file.type === 'dir' ?
                            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3 mr-1 text-blue-500"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>' :
                            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3 mr-1 text-zinc-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>';

                        fileItem.innerHTML = `
                    <div class="flex items-center overflow-hidden">
                        ${icon}
                        <span class="truncate">${file.name}</span>
                    </div>
                    <button class="remove-file text-zinc-400 hover:text-red-500 dark:hover:text-red-400 ml-2" data-index="${index}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                `;

                        selectedFilesList.appendChild(fileItem);
                    });

                    // Add event listeners to remove buttons
                    document.querySelectorAll('.remove-file').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const index = parseInt(this.dataset.index);
                            selectedFiles.splice(index, 1);
                            updateSelectedFilesUI();

                            // Uncheck the corresponding checkbox if it exists
                            const checkbox = document.querySelector(
                                `.file-checkbox[data-path="${selectedFiles[index]?.path}"]`);
                            if (checkbox) checkbox.checked = false;
                        });
                    });
                }
            }

            // Add selected files to context
            addSelectedBtn.addEventListener('click', async function() {
                if (selectedFiles.length === 0) return;

                // Filter out directories
                const fileOnlyPaths = selectedFiles.filter(file => file.type === 'file');

                if (fileOnlyPaths.length === 0) {
                    showToast('Please select at least one file (directories cannot be added)');
                    return;
                }

                // Show loading
                addSelectedBtn.disabled = true;
                addSelectedBtn.innerHTML =
                    '<svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Adding...';

                try {
                    // Fetch content for each file
                    const fileContents = [];

                    for (const file of fileOnlyPaths) {
                        const encodedPath = encodeURIComponent(file.path);
                        const response = await fetch(
                            `/api/github/file/${currentOwner}/${currentRepo}/${encodedPath}`);
                        const result = await response.json();

                        if (result.success) {
                            fileContents.push({
                                path: file.path,
                                name: file.name,
                                content: result.data.content
                            });
                        }
                    }

                    // Store in localStorage (or use your preferred method)
                    localStorage.setItem('github-context', JSON.stringify({
                        files: fileContents,
                        repo: currentRepo,
                        owner: currentOwner
                    }));

                    // Show success message
                    showToast(`${fileContents.length} file(s) added to context`);

                    // Reset selection mode
                    toggleSelectionMode(false);
                    hideBrowser();

                } catch (error) {
                    console.error('Error adding files to context:', error);
                    showToast('Error adding files to context', 'error');
                } finally {
                    addSelectedBtn.disabled = false;
                    addSelectedBtn.innerHTML =
                        '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M12 5v14M5 12h14"></path></svg> Add Files to Context';
                }
            });

            // Create project from repo button
            document.getElementById('github-create-project').addEventListener('click', function() {
                if (!currentRepo || !currentOwner) return;

                // Populate the modal with repository info
                document.getElementById('repo-name').textContent = currentRepo;
                document.getElementById('repo-owner').textContent = currentOwner;
                document.getElementById('project-name').value = currentRepo;

                // Show the modal
                const modal = document.getElementById('create-project-modal');
                modal.classList.remove('hidden');
            });

            document.querySelectorAll('.close-modal').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('create-project-modal').classList.add('hidden');
                });
            });

            document.getElementById('create-project-confirm').addEventListener('click', function() {
                const projectName = document.getElementById('project-name').value.trim();
                const maxFileSize = parseInt(document.getElementById('max-file-size').value) || 1024;
                const autoGenerateTests = document.getElementById('auto-generate-tests').checked;

                if (!projectName) {
                    alert('Please enter a project name');
                    return;
                }

                // Hide the modal
                document.getElementById('create-project-modal').classList.add('hidden');

                // Show the progress indicator
                progressIndicator.classList.remove('hidden');
                progressText.textContent = 'Initializing project...';
                progressBar.style.width = '0%';
                progressPercentage.textContent = '0%';
                progressComplete.classList.add('hidden');

                // Hide the browser
                hideBrowser();

                // Make the API call to create project
                fetch('/api/github/create-project', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            owner: currentOwner,
                            repo: currentRepo,
                            project_name: projectName,
                            max_file_size: maxFileSize,
                            auto_generate_tests: autoGenerateTests
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Start tracking progress
                            startProgressTracking(data.data.job_id);
                        } else {
                            // Show error
                            progressComplete.classList.remove('hidden');
                            failureIndicator.classList.remove('hidden');
                            errorMessage.textContent = data.message || 'An error occurred';
                            showToast('Error: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        // Hide progress indicator
                        progressComplete.classList.remove('hidden');
                        failureIndicator.classList.remove('hidden');
                        errorMessage.textContent = error.message || 'An unexpected error occurred';
                        showToast('Error: ' + error.message, 'error');
                    });
            });

            // Use file as context
            useFileBtn.addEventListener('click', function() {
                localStorage.setItem('github-context', JSON.stringify({
                    files: [{
                        path: currentPath,
                        name: fileName.textContent,
                        content: currentFileContent
                    }],
                    repo: currentRepo,
                    owner: currentOwner
                }));

                showToast(`File "${currentPath}" added to context`);
                hideBrowser();
            });

            // Toast function
            function showToast(message, type = 'success') {
                toastMessage.textContent = message;
                successToast.classList.remove('hidden', 'translate-y-2', 'opacity-0');

                if (type === 'error') {
                    successToast.classList.remove('bg-emerald-100', 'dark:bg-emerald-900/70', 'border-emerald-200',
                        'dark:border-emerald-800', 'text-emerald-800', 'dark:text-emerald-200');
                    successToast.classList.add('bg-red-100', 'dark:bg-red-900/70', 'border-red-200',
                        'dark:border-red-800', 'text-red-800', 'dark:text-red-200');
                } else {
                    successToast.classList.remove('bg-red-100', 'dark:bg-red-900/70', 'border-red-200',
                        'dark:border-red-800', 'text-red-800', 'dark:text-red-200');
                    successToast.classList.add('bg-emerald-100', 'dark:bg-emerald-900/70', 'border-emerald-200',
                        'dark:border-emerald-800', 'text-emerald-800', 'dark:text-emerald-200');
                }

                setTimeout(() => {
                    successToast.classList.add('translate-y-2', 'opacity-0');
                    setTimeout(() => {
                        successToast.classList.add('hidden');
                    }, 300);
                }, 3000);
            }

            // Load repositories
            function loadRepositories() {
                loading.classList.remove('hidden');

                fetch('/api/github/repositories')
                    .then(response => response.json())
                    .then(data => {
                        loading.classList.add('hidden');

                        if (data.success) {
                            repoSelect.innerHTML = '<option value="">Select a repository...</option>';

                            data.data.repositories.forEach(repo => {
                                const option = document.createElement('option');
                                option.value = `${repo.owner.login}/${repo.name}`;
                                option.textContent = `${repo.name} (${repo.owner.login})`;
                                repoSelect.appendChild(option);
                            });
                        } else {
                            showToast('Error loading repositories: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        loading.classList.add('hidden');
                        showToast('Error loading repositories: ' + error.message, 'error');
                    });
            }

            // Load repo contents
            function loadContents(owner, repo, path, forceSelectionMode = null) {
                loading.classList.remove('hidden');
                contentListing.classList.add('hidden');
                fileContent.classList.add('hidden');

                const encodedPath = encodeURIComponent(path);
                fetch(`/api/github/contents/${owner}/${repo}/${encodedPath}`)
                    .then(response => response.json())
                    .then(data => {
                        loading.classList.add('hidden');

                        if (data.success) {
                            // Update breadcrumb
                            updateBreadcrumb(path);

                            // If it's a directory, show listing
                            contentListing.innerHTML = '';

                            if (Array.isArray(data.data.contents)) {
                                // It's a directory
                                contentListing.classList.remove('hidden');

                                // Check if we should use selection mode
                                const useSelectionMode = forceSelectionMode !== null ? forceSelectionMode :
                                    selectionMode;

                                // Add parent directory if not in root
                                if (path) {
                                    const parentDir = document.createElement('div');
                                    parentDir.className =
                                        'flex items-center p-2.5 hover:bg-zinc-100 dark:hover:bg-zinc-700/50 rounded-md cursor-pointer group';

                                    let parentDirContent = `
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-2.5 text-zinc-400 group-hover:text-indigo-500 transition-colors">
                                    <path d="M9 18l-6-6 6-6"></path>
                                </svg>
                                <span class="text-zinc-600 dark:text-zinc-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Parent Directory</span>
                            `;

                                    // Add placeholder if in selection mode to maintain alignment
                                    if (useSelectionMode) {
                                        parentDirContent = `<div class="w-5 h-5 mr-3"></div>` +
                                            parentDirContent;
                                    }

                                    parentDir.innerHTML = parentDirContent;

                                    parentDir.addEventListener('click', function() {
                                        const parentPath = path.includes('/') ?
                                            path.substring(0, path.lastIndexOf('/')) :
                                            '';
                                        loadContents(owner, repo, parentPath);
                                    });
                                    contentListing.appendChild(parentDir);
                                }

                                // Sort directories first, then files
                                const contents = [...data.data.contents].sort((a, b) => {
                                    if (a.type === b.type) return a.name.localeCompare(b.name);
                                    return a.type === 'dir' ? -1 : 1;
                                });

                                contents.forEach(item => {
                                    const div = document.createElement('div');
                                    div.className =
                                        'flex items-center p-2.5 hover:bg-zinc-100 dark:hover:bg-zinc-700/50 rounded-md cursor-pointer group mb-1 last:mb-0 border border-transparent hover:border-zinc-200 dark:hover:border-zinc-700';
                                    div.draggable = true;
                                    div.dataset.path = item.path;
                                    div.dataset.type = item.type;
                                    div.dataset.name = item.name;

                                    // Choose icon based on file type
                                    let icon = '';
                                    if (item.type === 'dir') {
                                        icon =
                                            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-2.5 text-blue-500"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>';
                                    } else {
                                        // Determine file icon based on extension
                                        const ext = item.name.split('.').pop().toLowerCase();
                                        let iconColor = 'text-zinc-500';

                                        if (['js', 'jsx', 'ts', 'tsx'].includes(ext)) {
                                            iconColor = 'text-yellow-500';
                                        } else if (['py'].includes(ext)) {
                                            iconColor = 'text-blue-500';
                                        } else if (['php'].includes(ext)) {
                                            iconColor = 'text-purple-500';
                                        } else if (['json', 'yml', 'yaml'].includes(ext)) {
                                            iconColor = 'text-green-500';
                                        } else if (['html', 'css'].includes(ext)) {
                                            iconColor = 'text-orange-500';
                                        } else if (['md', 'txt'].includes(ext)) {
                                            iconColor = 'text-zinc-500';
                                        }

                                        icon =
                                            `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-2.5 ${iconColor}"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>`;
                                    }

                                    // Add checkbox if in selection mode
                                    if (useSelectionMode) {
                                        div.innerHTML = `
                                    <div class="w-5 h-5 mr-3 flex items-center justify-center">
                                        <input type="checkbox" class="file-checkbox h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" data-path="${item.path}" data-name="${item.name}" data-type="${item.type}">
                                    </div>
                                    ${icon}
                                    <span class="text-zinc-700 dark:text-zinc-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">${item.name}</span>
                                `;

                                        // Preselect if in selectedFiles array
                                        const isSelected = selectedFiles.some(f => f.path === item
                                            .path);
                                        setTimeout(() => {
                                            const checkbox = div.querySelector(
                                                '.file-checkbox');
                                            if (checkbox && isSelected) {
                                                checkbox.checked = true;
                                            }

                                            // Add change event to checkboxes
                                            checkbox.addEventListener('change', function() {
                                                if (this.checked) {
                                                    if (!selectedFiles.some(f => f
                                                            .path === item.path)) {
                                                        selectedFiles.push({
                                                            path: item.path,
                                                            name: item.name,
                                                            type: item.type
                                                        });
                                                    }
                                                } else {
                                                    selectedFiles = selectedFiles
                                                        .filter(f => f.path !== item
                                                            .path);
                                                }
                                                updateSelectedFilesUI();
                                            });
                                        }, 0);
                                    } else {
                                        div.innerHTML = `
                                    ${icon}
                                    <span class="text-zinc-700 dark:text-zinc-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">${item.name}</span>
                                `;
                                    }

                                    // Click handler
                                    div.addEventListener('click', function(e) {
                                        // Don't navigate if clicking a checkbox
                                        if (e.target.tagName === 'INPUT') return;

                                        if (item.type === 'dir') {
                                            loadContents(owner, repo, item.path);
                                        } else {
                                            loadFileContent(owner, repo, item.path);
                                        }
                                    });

                                    // Drag handler for files
                                    if (item.type === 'file') {
                                        div.addEventListener('dragstart', function(e) {
                                            e.dataTransfer.setData('text/plain', JSON
                                                .stringify({
                                                    type: 'github-file',
                                                    owner: owner,
                                                    repo: repo,
                                                    path: item.path,
                                                    name: item.name
                                                }));
                                        });
                                    }

                                    contentListing.appendChild(div);
                                });
                            } else {
                                // It's a file
                                fileContent.classList.remove('hidden');
                                filePreview.textContent = 'Loading file content...';
                                loadFileContent(owner, repo, path);
                            }
                        } else {
                            contentListing.classList.remove('hidden');
                            contentListing.innerHTML =
                                `<div class="p-4 text-red-500">Error: ${data.message}</div>`;
                        }
                    })
                    .catch(error => {
                        loading.classList.add('hidden');
                        contentListing.classList.remove('hidden');
                        contentListing.innerHTML =
                            `<div class="p-4 text-red-500">Error: ${error.message}</div>`;
                    });
            }

            // Load file content
            function loadFileContent(owner, repo, path) {
                loading.classList.remove('hidden');
                contentListing.classList.add('hidden');
                fileContent.classList.add('hidden');

                const encodedPath = encodeURIComponent(path);
                fetch(`/api/github/file/${owner}/${repo}/${encodedPath}`)
                    .then(response => response.json())
                    .then(data => {
                        loading.classList.add('hidden');
                        fileContent.classList.remove('hidden');

                        if (data.success) {
                            currentPath = path;
                            currentFileContent = data.data.content;
                            filePreview.textContent = data.data.content;
                            fileName.textContent = path.split('/').pop();

                            // Update breadcrumb
                            updateBreadcrumb(path);

                            // Set up the "Use as context" button
                            useFileBtn.dataset.content = currentFileContent;
                            useFileBtn.dataset.path = path;
                        } else {
                            filePreview.textContent = `Error: ${data.message}`;
                        }
                    })
                    .catch(error => {
                        loading.classList.add('hidden');
                        fileContent.classList.remove('hidden');
                        filePreview.textContent = `Error: ${error.message}`;
                    });
            }

            // Update breadcrumb
            function updateBreadcrumb(path) {
                if (!path) {
                    breadcrumb.classList.add('hidden');
                    return;
                }

                breadcrumb.classList.remove('hidden');

                const parts = path.split('/');
                breadcrumb.innerHTML =
                    '<span class="text-zinc-600 dark:text-zinc-400 hover:text-indigo-600 dark:hover:text-indigo-400 cursor-pointer transition-colors" data-path="">Root</span>';

                let currentPath = '';
                parts.forEach((part, index) => {
                    currentPath += (index === 0 ? '' : '/') + part;
                    breadcrumb.innerHTML += `
                <span class="mx-1 text-zinc-400">/</span>
                <span class="text-zinc-600 dark:text-zinc-400 cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                      data-path="${currentPath}">${part}</span>
            `;
                });

                // Add click handlers to breadcrumb parts
                breadcrumb.querySelectorAll('[data-path]').forEach(element => {
                    element.addEventListener('click', function() {
                        const pathToLoad = element.dataset.path;
                        loadContents(currentOwner, currentRepo, pathToLoad);
                    });
                });
            }

            // Set up drag and drop functionality on the entire page
            document.addEventListener('dragover', function(e) {
                e.preventDefault(); // Allow drop
            });

            document.addEventListener('drop', function(e) {
                if (e.target.closest('[data-dropzone]')) {
                    e.preventDefault();

                    try {
                        const data = JSON.parse(e.dataTransfer.getData('text/plain'));

                        if (data.type === 'github-file') {
                            // Handle the dropped file based on the target
                            const dropzone = e.target.closest('[data-dropzone]');
                            const dropzoneType = dropzone.dataset.dropzone;

                            // Load the file content
                            fetch(
                                    `/api/github/file/${data.owner}/${data.repo}/${encodeURIComponent(data.path)}`
                                )
                                .then(response => response.json())
                                .then(result => {
                                    if (result.success) {
                                        // Handle different dropzone types
                                        switch (dropzoneType) {
                                            case 'test-case':
                                                // Add file content to test case description or steps
                                                if (dropzone.querySelector('[name="description"]')) {
                                                    dropzone.querySelector('[name="description"]')
                                                        .value +=
                                                        `\n\nFrom GitHub file ${data.path}:\n${result.data.content}`;
                                                }
                                                break;
                                            case 'test-script':
                                                // Add file content to test script
                                                if (dropzone.querySelector('[name="script_content"]')) {
                                                    dropzone.querySelector('[name="script_content"]')
                                                        .value = result.data.content;
                                                }
                                                break;
                                                // Add more cases as needed
                                        }

                                        showToast(`File "${data.path}" has been added as context`);
                                    }
                                })
                                .catch(error => {
                                    showToast('Error loading file content: ' + error.message, 'error');
                                });
                        }
                    } catch (error) {
                        console.error('Error processing dropped data:', error);
                    }
                }
            });
        });
    </script>
@endif
