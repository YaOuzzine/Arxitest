<!-- resources/views/dashboard/modals/edit-test-script-modal.blade.php -->
<div x-cloak x-show="showEditScriptModal" @keydown.escape.window="showEditScriptModal = false"
    class="fixed inset-0 overflow-y-auto z-50" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-zinc-900/70 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
        @click="showEditScriptModal = false"></div>

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
                        <i data-lucide="edit-3" class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400"></i>
                        Edit Test Script
                    </h3>
                    <button @click="showEditScriptModal = false"
                        class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <div class="space-y-6">
                    <!-- Script Name -->
                    <div>
                        <label for="edit-script-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Script Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="edit-script-name" x-model="editScriptName"
                            class="form-input w-full rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                            placeholder="Enter script name">
                    </div>

                    <!-- Framework -->
                    <div>
                        <label for="edit-script-framework" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Framework <span class="text-red-500">*</span>
                        </label>
                        <select id="edit-script-framework" x-model="editScriptFramework"
                            class="form-select w-full rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600">
                            <option value="selenium-python">Selenium (Python)</option>
                            <option value="cypress">Cypress (JavaScript)</option>
                            <option value="other">Other Framework</option>
                        </select>
                    </div>

                    <!-- Script Editor -->
                    <div>
                        <label for="edit-script-content" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Script Content <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <textarea id="edit-script-content" rows="15" x-model="editScriptContent"
                                class="form-textarea w-full rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600 font-mono"
                                placeholder="Enter your script code here..."></textarea>
                            <!-- Syntax highlighting can be added here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-700/30 border-t border-zinc-200 dark:border-zinc-700">
                <div class="flex justify-end space-x-3">
                    <button @click="showEditScriptModal = false" class="btn-secondary">
                        Cancel
                    </button>
                    <button @click="updateScript" class="btn-primary"
                        :disabled="!editScriptName || !editScriptContent">
                        <i data-lucide="save" class="w-4 h-4 mr-1.5"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
