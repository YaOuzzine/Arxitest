<div x-cloak x-show="showDataEditor" @keydown.escape.window="showDataEditor = false"
    class="fixed inset-0 overflow-y-auto z-50" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-zinc-900/70 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
        @click="showDataEditor = false"></div>

    <!-- Modal Panel -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative w-full max-w-6xl bg-white dark:bg-zinc-800 rounded-xl shadow-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-8">

            <!-- Modal Content - Using Grid Layout -->
            <div class="grid grid-rows-[auto_auto_auto_1fr_auto]" style="height: 80vh; max-height: 800px;">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-gradient-to-r from-teal-50 to-emerald-50 dark:from-teal-900/20 dark:to-emerald-900/20">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-teal-900 dark:text-teal-100 flex items-center">
                            <div class="flex items-center gap-2">
                                <i data-lucide="database" class="w-5 h-5 text-teal-600 dark:text-teal-400"></i>
                                <span>Edit Test Data</span>
                            </div>
                        </h3>
                        <button @click="showDataEditor = false"
                            class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <!-- Settings Fields -->
                <div class="p-6 bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Name Field -->
                        <div>
                            <label for="data-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="data-name" x-model="currentData.name"
                                class="form-input w-full rounded-lg py-2 px-3 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-zinc-600"
                                placeholder="Enter a name">
                        </div>

                        <!-- Format Field -->
                        <div>
                            <label for="data-format" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Format <span class="text-red-500">*</span>
                            </label>
                            <select id="data-format" x-model="currentData.format"
                                @change="changeDataEditorMode()"
                                class="form-select w-full rounded-lg py-2 px-3 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-zinc-600">
                                <option value="json">JSON</option>
                                <option value="csv">CSV</option>
                                <option value="xml">XML</option>
                                <option value="plain">Plain Text</option>
                                <option value="other">Other Format</option>
                            </select>
                        </div>

                        <!-- Usage Context -->
                        <div>
                            <label for="data-usage-context" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Usage Context <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="data-usage-context" x-model="currentData.usage_context"
                                class="form-input w-full rounded-lg py-2 px-3 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-zinc-600"
                                placeholder="e.g., 'Valid input scenario' or 'Edge case testing'">
                        </div>

                        <!-- Sensitive Data Checkbox -->
                        <div class="col-span-3 mt-2">
                            <label class="flex items-center">
                                <input type="checkbox" x-model="currentData.is_sensitive" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Mark as sensitive data (contains private, personal, or confidential information)
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Editor Toolbar -->
                <div class="flex items-center px-4 py-2 bg-gray-100 dark:bg-zinc-700 border-b border-gray-300 dark:border-zinc-600">
                    <div class="flex items-center space-x-2">
                        <!-- Theme toggle -->
                        <button @click="toggleDataEditorTheme()"
                            class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-teal-500 dark:focus:ring-teal-400 transition-colors"
                            title="Toggle Theme">
                            <i data-lucide="sun" class="w-4 h-4 text-gray-700 dark:text-gray-300"
                                x-show="!dataEditorDarkMode"></i>
                            <i data-lucide="moon" class="w-4 h-4 text-gray-700 dark:text-gray-300"
                                x-show="dataEditorDarkMode"></i>
                        </button>

                        <div class="h-5 border-r border-gray-300 dark:border-zinc-500"></div>

                        <!-- Format button -->
                        <button @click="formatData()"
                            class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-teal-500 dark:focus:ring-teal-400 transition-colors"
                            title="Format Code">
                            <i data-lucide="align-justify" class="w-4 h-4 text-gray-700 dark:text-gray-300"></i>
                        </button>
                    </div>
                </div>

                <!-- CodeMirror Editor Container - Using Grid Row -->
                <div class="w-full h-full overflow-hidden border border-gray-200 dark:border-gray-700">
                    <div id="data-editor-container" class="w-full h-full"></div>
                </div>

                <!-- Footer with Save/Cancel -->
                <div class="p-4 bg-gray-50 dark:bg-zinc-800 border-t border-gray-200 dark:border-zinc-700">
                    <div class="flex justify-end gap-3">
                        <button @click="showDataEditor = false"
                            class="px-4 py-2 bg-white dark:bg-zinc-700 text-gray-700 dark:text-gray-200 rounded-lg border border-gray-300 dark:border-zinc-600 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition-colors">
                            Cancel
                        </button>
                        <button @click="saveEditedData()"
                            class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                            :disabled="!currentData.name || !currentData.usage_context">
                            <i data-lucide="save" class="w-4 h-4 mr-1.5"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
