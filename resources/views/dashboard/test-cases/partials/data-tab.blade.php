{{-- resources/views/dashboard/test-cases/partials/data-tab.blade.php --}}

<div x-show="activeTab === 'testdata'" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <!-- Actions -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Test Data</h3>

        <!-- Search and Filters -->
        <div class="flex sm:flex-row gap-3 w-full max-w-xl">
            <div class="relative flex-grow">
                <input type="text" x-model="dataSearchTerm"
                    class="form-input pl-9 w-full rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                    placeholder="Search test data...">
                <div class="absolute left-0 flex items-center pl-3 pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-zinc-500 dark:text-zinc-400"></i>
                </div>
            </div>

            <div class="relative">
                <select x-model="dataFilterFormat" class="form-select w-full rounded-lg h-10 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600">
                    <option value="">All Formats</option>
                    <option value="json">JSON</option>
                    <option value="csv">CSV</option>
                    <option value="xml">XML</option>
                    <option value="plain">Plain Text</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>

        <div>
            <button @click="openDataModal()" class="btn-primary">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Create Test Data
            </button>
        </div>
    </div>

    <!-- Data List -->
    <div x-show="testData.length > 0" class="space-y-4">
        <template x-for="data in filteredData" :key="data.id">
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200"
                :class="{ 'shadow-md': expandedData === data.id }">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-700/30 gap-3">
                    <div>
                        <h4 class="text-md font-medium text-zinc-900 dark:text-white mb-1" x-text="data.name"></h4>
                        <div
                            class="flex flex-wrap items-center text-sm text-zinc-500 dark:text-zinc-400 gap-x-3 gap-y-1">
                            <span
                                class="px-2 py-0.5 text-xs font-medium rounded-md bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800/30"
                                x-text="data.format.toUpperCase()">
                            </span>
                            <span x-show="data.is_sensitive"
                                class="px-2 py-0.5 text-xs font-medium rounded-md bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800/30">
                                Sensitive
                            </span>
                            <span class="flex items-center">
                                <i data-lucide="clock" class="w-3.5 h-3.5 mr-1"></i>
                                <span x-text="formatDate(data.created_at)"></span>
                            </span>
                            <span x-show="data.metadata && data.metadata.created_through === 'ai'"
                                class="px-2 py-0.5 text-xs font-medium rounded-md bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/30">
                                AI Generated
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button @click="toggleData(data.id)"
                            class="px-2 py-1 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded">
                            <span x-show="expandedData !== data.id">View Data</span>
                            <span x-show="expandedData === data.id">Hide Data</span>
                        </button>
                        <button @click="editData(data)"
                            class="px-2 py-1 text-sm text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded">
                            Edit
                        </button>
                        <button @click="confirmDeleteData(data.id)"
                            class="px-2 py-1 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded">
                            Remove
                        </button>
                    </div>
                </div>
                <div x-show="expandedData === data.id" x-collapse>
                    <div class="relative p-4 bg-zinc-50 dark:bg-zinc-900">
                        <button
                            @click="copyToClipboard(data.content, 'Data')"
                            class="absolute top-2 right-2 px-2 py-1 text-xs text-zinc-500 dark:text-zinc-400 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 rounded">
                            Copy
                        </button>
                        <pre class="language-auto max-h-96 overflow-y-auto !m-0 !p-0 !bg-transparent"><code x-html="highlightCode(data.content, data.format)"></code></pre>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="testData.length === 0"
        class="bg-zinc-50 dark:bg-zinc-700/30 border border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg p-8 text-center">
        <div
            class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400 mb-3">
            <i data-lucide="database" class="w-6 h-6"></i>
        </div>
        <h4 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No Test Data Yet</h4>
        <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-4">
            Test data provides input values for this test case. Add data manually or generate with AI assistance.
        </p>
        <div class="flex justify-center">
            <button @click="openDataModal()" class="btn-primary">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Create Test Data
            </button>
        </div>
    </div>
</div>
