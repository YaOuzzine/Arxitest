<div x-cloak x-show="showScriptEditor" @keydown.escape.window="showScriptEditor = false"
    class="fixed inset-0 overflow-y-auto z-50" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-zinc-900/70 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
        @click="showScriptEditor = false"></div>

    <!-- Modal Panel -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative w-full max-w-6xl bg-white dark:bg-zinc-800 rounded-xl shadow-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-8">

            <!-- Header -->
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-indigo-900 dark:text-indigo-100 flex items-center">
                        <div class="flex items-center gap-2">
                            <i data-lucide="code" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i>
                            <span>Edit Test Script</span>
                        </div>
                    </h3>
                    <button @click="showScriptEditor = false"
                        class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="flex flex-col h-[calc(100vh-12rem)] max-h-[800px]">
                <!-- Settings Fields -->
                <div class="p-4 bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Name Field -->
                        <div>
                            <label for="script-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="script-name" x-model="currentScript.name"
                                class="form-input w-full rounded-lg h-10 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-zinc-600"
                                placeholder="Enter a name">
                        </div>

                        <!-- Framework Field -->
                        <div>
                            <label for="script-framework" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Framework <span class="text-red-500">*</span>
                            </label>
                            <select id="script-framework" x-model="currentScript.framework_type"
                                @change="changeEditorMode()"
                                class="form-select w-full rounded-lg h-10 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-zinc-600">
                                <option value="selenium-python">Selenium (Python)</option>
                                <option value="cypress">Cypress (JavaScript)</option>
                                <option value="other">Other Framework</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Editor Toolbar -->
                <div class="flex items-center px-2 py-1.5 bg-gray-100 dark:bg-zinc-700 border-b border-gray-300 dark:border-zinc-600">
                    <div class="flex items-center space-x-2">
                        <!-- Theme toggle -->
                        <button @click="toggleEditorTheme()"
                            class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-colors"
                            title="Toggle Theme">
                            <i data-lucide="sun" class="w-4 h-4 text-gray-700 dark:text-gray-300"
                                x-show="!editorDarkMode"></i>
                            <i data-lucide="moon" class="w-4 h-4 text-gray-700 dark:text-gray-300"
                                x-show="editorDarkMode"></i>
                        </button>
                    </div>
                </div>

                <!-- CodeMirror Editor Container -->
                <div class="flex-1 relative overflow-hidden" style="flex: 1 1 auto !important; min-height: 400px;">
                    <div id="script-editor-container" class="absolute inset-0 w-full h-full"></div>
                </div>

                <!-- Footer with Save/Cancel -->
                <div class="p-4 bg-gray-50 dark:bg-zinc-800 border-t border-gray-200 dark:border-zinc-700">
                    <div class="flex justify-end gap-3">
                        <button @click="showScriptEditor = false"
                            class="px-4 py-2 bg-white dark:bg-zinc-700 text-gray-700 dark:text-gray-200 rounded-lg border border-gray-300 dark:border-zinc-600 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                            Cancel
                        </button>
                        <button @click="saveEditedScript()"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                            :disabled="!currentScript.name">
                            <i data-lucide="save" class="w-4 h-4 mr-1.5"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
