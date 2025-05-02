{{-- resources/views/dashboard/test-cases/modals/data-edit-modal.blade.php --}}

<!-- Data Edit Modal -->
<div x-cloak x-show="showDataEditModal" @keydown.escape.window="showDataEditModal = false"
    class="fixed inset-0 overflow-y-auto z-50" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-zinc-900/70 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
        @click="showDataEditModal = false"></div>

    <!-- Modal Panel -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative w-full max-w-6xl bg-zinc-100 dark:bg-zinc-900 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-8">

            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 flex items-center">
                    <div class="flex items-center gap-2">
                        <i data-lucide="database" class="w-5 h-5 text-teal-600 dark:text-teal-400"></i>
                        <span>Edit Test Data</span>
                    </div>
                </h3>
                <button @click="showDataEditModal = false"
                    class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Content -->
            <div class="flex flex-col h-[calc(100vh-12rem)] max-h-[800px]">
                <!-- Data Settings -->
                <div class="p-4 bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="data-edit-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Data Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="data-edit-name" x-model="editingData.name"
                                class="form-input w-full rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                                placeholder="Enter a name for this data">
                        </div>
                        <div>
                            <label for="data-edit-format" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Format <span class="text-red-500">*</span>
                            </label>
                            <select id="data-edit-format" x-model="editingData.format"
                                class="form-select w-full rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600">
                                <option value="json">JSON</option>
                                <option value="csv">CSV</option>
                                <option value="xml">XML</option>
                                <option value="plain">Plain Text</option>
                                <option value="other">Other Format</option>
                            </select>
                        </div>
                        <div>
                            <label for="data-edit-usage" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Usage Context <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="data-edit-usage" x-model="editingData.usage_context"
                                class="form-input w-full rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                                placeholder="e.g., 'Valid input scenario' or 'Edge case testing'">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="flex items-center">
                            <input type="checkbox" x-model="editingData.is_sensitive" class="form-checkbox">
                            <span class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">
                                Mark as sensitive data (contains private, personal, or confidential information)
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Data Editor Area -->
                <div class="flex-1 flex flex-col overflow-hidden">
                    <!-- Editor Toolbar -->
                    <div class="px-2 py-1 bg-zinc-200 dark:bg-zinc-700 border-b border-zinc-300 dark:border-zinc-600 flex items-center">
                        <div class="flex items-center mr-4">
                            <button @click="dataEditorAction('undo')" class="p-1 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600" title="Undo">
                                <i data-lucide="undo" class="w-4 h-4 text-zinc-700 dark:text-zinc-300"></i>
                            </button>
                            <button @click="dataEditorAction('redo')" class="p-1 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600" title="Redo">
                                <i data-lucide="redo" class="w-4 h-4 text-zinc-700 dark:text-zinc-300"></i>
                            </button>
                        </div>
                        <div class="h-4 border-r border-zinc-400 dark:border-zinc-500 mx-1"></div>
                        <div class="flex items-center mr-4">
                            <button @click="dataEditorAction('format')" class="p-1 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600" title="Format/Prettify">
                                <i data-lucide="align-justify" class="w-4 h-4 text-zinc-700 dark:text-zinc-300"></i>
                            </button>
                            <button @click="dataEditorAction('validate')" class="p-1 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600" title="Validate">
                                <i data-lucide="check-circle" class="w-4 h-4 text-zinc-700 dark:text-zinc-300"></i>
                            </button>
                        </div>
                        <div class="h-4 border-r border-zinc-400 dark:border-zinc-500 mx-1"></div>
                        <div class="flex items-center">
                            <button @click="dataEditorAction('search')" class="p-1 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600" title="Search">
                                <i data-lucide="search" class="w-4 h-4 text-zinc-700 dark:text-zinc-300"></i>
                            </button>
                            <button @click="dataEditorAction('replace')" class="p-1 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600" title="Replace">
                                <i data-lucide="replace" class="w-4 h-4 text-zinc-700 dark:text-zinc-300"></i>
                            </button>
                        </div>
                        <div class="ml-auto flex items-center">
                            <span class="text-xs text-zinc-600 dark:text-zinc-400 mr-2">
                                Line: <span x-text="dataEditorCursorPosition.line">1</span>,
                                Col: <span x-text="dataEditorCursorPosition.column">1</span>
                            </span>
                            <button @click="toggleDataEditorTheme()" class="p-1 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600" title="Toggle Theme">
                                <i data-lucide="sun" class="w-4 h-4 text-zinc-700 dark:text-zinc-300" x-show="!dataEditorDarkMode"></i>
                                <i data-lucide="moon" class="w-4 h-4 text-zinc-700 dark:text-zinc-300" x-show="dataEditorDarkMode"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Data Editor (CodeMirror) -->
                    <div class="flex-1 relative overflow-hidden bg-white dark:bg-zinc-900">
                        <div id="data-code-editor" class="absolute inset-0 w-full h-full font-mono text-sm"></div>
                    </div>
                </div>

                <!-- Footer with Save/Cancel -->
                <div class="p-4 bg-white dark:bg-zinc-800 border-t border-zinc-200 dark:border-zinc-700">
                    <div class="flex justify-end gap-3">
                        <button @click="showDataEditModal = false" class="btn-secondary">
                            Cancel
                        </button>
                        <button @click="saveEditedData()"
                            class="btn-primary"
                            :disabled="!editingData.name || !editingData.content || !editingData.usage_context">
                            <i data-lucide="save" class="w-4 h-4 mr-1.5"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
