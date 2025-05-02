{{-- resources/views/dashboard/test-cases/partials/scripts-tab.blade.php --}}

<div x-show="activeTab === 'scripts'" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <!-- Actions -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Test Scripts</h3>

        <!-- Search and Filters -->
        <div class="flex sm:flex-row gap-3 w-full max-w-xl">
            <div class="relative flex-grow">
                <input type="text" x-model="scriptSearchTerm"
                    class="form-input pl-9 w-full rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                    placeholder="Search scripts...">
                <div class="absolute  left-0 flex items-center pl-3 pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-zinc-500 dark:text-zinc-400"></i>
                </div>
            </div>

            <div class="relative">
                <select x-model="scriptFilterFramework" class="form-select w-full rounded-lg h-10 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600">
                    <option value="">All Frameworks</option>
                    <option value="selenium-python">Selenium (Python)</option>
                    <option value="cypress">Cypress (JS)</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>

        <div>
            <button @click="openScriptModal()" class="btn-primary">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Create Script
            </button>
        </div>
    </div>

    <!-- Script List -->
    <div x-show="testScripts.length > 0" class="space-y-4">
        <template x-for="script in filteredScripts" :key="script.id">
            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200"
                :class="{ 'shadow-md': expandedScript === script.id }">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-700/30 gap-3">
                    <div>
                        <h4 class="text-md font-medium text-zinc-900 dark:text-white mb-1" x-text="script.name"></h4>
                        <div
                            class="flex flex-wrap items-center text-sm text-zinc-500 dark:text-zinc-400 gap-x-3 gap-y-1">
                            <span class="flex items-center">
                                <i data-lucide="code" class="w-3.5 h-3.5 mr-1"></i>
                                <span x-text="getFrameworkLabel(script.framework_type)"></span>
                            </span>
                            <span class="flex items-center">
                                <i data-lucide="clock" class="w-3.5 h-3.5 mr-1"></i>
                                <span x-text="formatDate(script.created_at)"></span>
                            </span>
                            <span x-show="script.metadata && script.metadata.created_through === 'ai'"
                                class="px-2 py-0.5 text-xs font-medium rounded-md bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/30">
                                AI Generated
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button @click="toggleScript(script.id)"
                            class="px-2 py-1 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded">
                            <span x-show="expandedScript !== script.id">View Code</span>
                            <span x-show="expandedScript === script.id">Hide Code</span>
                        </button>
                        <button @click="editScript(script)"
                            class="px-2 py-1 text-sm text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded">
                            Edit
                        </button>
                        <button @click="confirmDeleteScript(script.id)"
                            class="px-2 py-1 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded">
                            Delete
                        </button>
                    </div>
                </div>
                <div x-show="expandedScript === script.id" x-collapse>
                    <div class="relative p-4 bg-zinc-50 dark:bg-zinc-900">
                        <button
                            @click="copyToClipboard(script.script_content, 'Script')"
                            class="absolute top-2 right-2 px-2 py-1 text-xs text-zinc-500 dark:text-zinc-400 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 rounded">
                            Copy
                        </button>
                        <pre class="language-auto max-h-96 overflow-y-auto !m-0 !p-0 !bg-transparent"><code x-html="highlightCode(script.script_content, script.framework_type)"></code></pre>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="testScripts.length === 0"
        class="bg-zinc-50 dark:bg-zinc-700/30 border border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg p-8 text-center">
        <div
            class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400 mb-3">
            <i data-lucide="file-code" class="w-6 h-6"></i>
        </div>
        <h4 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No Test Scripts Yet</h4>
        <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-4">
            Test scripts help automate this test case. Add one manually or generate with AI assistance.
        </p>
        <div class="flex justify-center">
            <button @click="openScriptModal()" class="btn-primary">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Create Test Script
            </button>
        </div>
    </div>
</div>
