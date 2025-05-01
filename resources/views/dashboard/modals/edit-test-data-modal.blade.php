<!-- resources/views/dashboard/modals/edit-test-data-modal.blade.php -->
<div x-cloak x-show="showEditDataModal" @keydown.escape.window="showEditDataModal = false"
    class="fixed inset-0 overflow-y-auto z-50" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-zinc-900/70 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
        @click="showEditDataModal = false"></div>

    <!-- Modal Panel -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative w-full max-w-5xl bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-8">

            <!-- Header -->
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-700/30">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center">
                        <i data-lucide="edit-3" class="w-5 h-5 mr-2 text-teal-600 dark:text-teal-400"></i>
                        Edit Test Data
                    </h3>
                    <button @click="showEditDataModal = false"
                        class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <div class="space-y-6">
                    <!-- Data Name -->
                    <div>
                        <label for="edit-data-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Data Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="edit-data-name" x-model="editDataName"
                            class="form-input w-full rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                            placeholder="Enter data name">
                    </div>

                    <!-- Format -->
                    <div>
                        <label for="edit-data-format" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Format <span class="text-red-500">*</span>
                        </label>
                        <select id="edit-data-format" x-model="editDataFormat"
                            class="form-select w-full rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600">
                            <option value="json">JSON</option>
                            <option value="csv">CSV</option>
                            <option value="xml">XML</option>
                            <option value="plain">Plain Text</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Usage Context -->
                    <div>
                        <label for="edit-data-usage-context" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Usage Context <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="edit-data-usage-context" x-model="editDataUsageContext"
                            class="form-input w-full rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                            placeholder="e.g., 'Valid input scenario' or 'Edge case testing'">
                    </div>

                    <!-- Data Editor -->
                    <div>
                        <label for="edit-data-content" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Data Content <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <textarea id="edit-data-content" rows="15" x-model="editDataContent"
                                class="form-textarea w-full rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600 font-mono"
                                placeholder="Enter your test data here..."></textarea>
                            <!-- Syntax highlighting can be added here -->
                        </div>
                    </div>

                    <!-- Sensitive Data Flag -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="editDataIsSensitive" class="form-checkbox">
                            <span class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">
                                Mark as sensitive data (contains private, personal, or confidential information)
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-700/30 border-t border-zinc-200 dark:border-zinc-700">
                <div class="flex justify-end space-x-3">
                    <button @click="showEditDataModal = false" class="btn-secondary">
                        Cancel
                    </button>
                    <button @click="updateData" class="btn-primary"
                        :disabled="!editDataName || !editDataContent || !editDataUsageContext">
                        <i data-lucide="save" class="w-4 h-4 mr-1.5"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
