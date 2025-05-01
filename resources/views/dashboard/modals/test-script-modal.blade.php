@props(['testCase', 'project', 'testScripts'])

<!-- Create Script Modal -->
<div x-cloak x-show="showScriptModal" @keydown.escape.window="showScriptModal = false"
    class="fixed inset-0 overflow-y-auto z-50" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-zinc-900/70 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
        @click="showScriptModal = false"></div>

    <!-- Modal Panel -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative w-full max-w-6xl bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-8">

            <!-- Header -->
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-indigo-900 dark:text-indigo-100 flex items-center">
                        <div class="flex items-center gap-2">
                            <i data-lucide="code" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i>
                            <span>Create Test Script</span>
                        </div>
                    </h3>
                    <button @click="showScriptModal = false"
                        class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-0">
                <div class="grid grid-cols-1 lg:grid-cols-3 h-[calc(100vh-12rem)] max-h-[800px]">
                    <!-- Left Column: Context & Options -->
                    <div class="lg:col-span-1 p-6 border-r border-zinc-200 dark:border-zinc-700/70 bg-zinc-50 dark:bg-zinc-800/50 overflow-y-auto">
                        <div class="space-y-6">
                            <!-- Creation Mode Tabs -->
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Creation Mode</label>
                                <div class="flex rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                                    <button @click="scriptCreationMode = 'ai'"
                                        :class="{
                                            'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium': scriptCreationMode === 'ai',
                                            'bg-white dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-700/50': scriptCreationMode !== 'ai'
                                        }"
                                        class="flex-1 py-2.5 px-3 text-sm transition-colors">
                                        <i data-lucide="sparkles" class="w-4 h-4 inline-block mr-1"></i>
                                        AI-Assisted
                                    </button>
                                    <button @click="scriptCreationMode = 'manual'"
                                        :class="{
                                            'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium': scriptCreationMode === 'manual',
                                            'bg-white dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-700/50': scriptCreationMode !== 'manual'
                                        }"
                                        class="flex-1 py-2.5 px-3 text-sm transition-colors">
                                        <i data-lucide="edit-3" class="w-4 h-4 inline-block mr-1"></i> Manual
                                    </button>
                                </div>
                            </div>

                            <!-- Framework Selection -->
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Framework <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select x-model="scriptFramework" class="form-select w-full rounded-lg h-10 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600">
                                        <template x-for="option in scriptFrameworkOptions">
                                            <option :value="option.value" x-text="option.label"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <!-- Context Information -->
                            <div x-show="scriptCreationMode === 'ai'" class="bg-white dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-2 flex items-center">
                                    <i data-lucide="info" class="w-4 h-4 mr-2 text-indigo-500 dark:text-indigo-400"></i>
                                    Context Information
                                </h4>
                                <div class="text-sm text-zinc-600 dark:text-zinc-300 space-y-2">
                                    <div>
                                        <span class="font-medium">Test Case:</span> {{ $testCase->title }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Steps:</span> {{ count($testCase->steps ?? []) }} steps defined
                                    </div>
                                    @if($testCase->story)
                                        <div>
                                            <span class="font-medium">Related Story:</span> {{ $testCase->story->title }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Additional Context Toggle -->
                                <div x-data="{ showAdditionalContext: false }" class="mt-3">
                                    <button @click="showAdditionalContext = !showAdditionalContext"
                                        class="text-xs flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                        <i data-lucide="plus-circle" class="w-3.5 h-3.5 mr-1" x-show="!showAdditionalContext"></i>
                                        <i data-lucide="minus-circle" class="w-3.5 h-3.5 mr-1" x-show="showAdditionalContext"></i>
                                        <span x-text="showAdditionalContext ? 'Hide Additional Context' : 'Add Additional Context'"></span>
                                    </button>

                                    <div x-show="showAdditionalContext" x-collapse class="mt-3 space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                Code Context <span class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional)</span>
                                            </label>
                                            <textarea x-model="scriptCodeContext" rows="4"
                                                class="form-textarea w-full rounded-lg text-xs font-mono bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                                                placeholder="Paste relevant code, API specifications, or other technical details here"></textarea>
                                        </div>

                                        <!-- File Upload System -->
                                        <div>
                                            <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                Reference Files <span class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional, up to 5)</span>
                                            </label>
                                            <div class="space-y-3">
                                                <div x-show="scriptFiles.length < 5">
                                                    <input type="file" id="script-files"
                                                        class="block w-full text-xs text-zinc-600 dark:text-zinc-400
                                                        file:mr-3 file:py-1.5 file:px-3
                                                        file:text-xs file:font-medium
                                                        file:border file:border-zinc-200 dark:file:border-zinc-600
                                                        file:bg-white dark:file:bg-zinc-700
                                                        file:text-indigo-600 dark:file:text-indigo-400
                                                        hover:file:bg-zinc-50 dark:hover:file:bg-zinc-600
                                                        file:rounded-md"
                                                        @change="handleScriptFileUpload($event)"
                                                        accept=".py,.js,.ts,.json,.txt,.csv,.xml,.html,.css">
                                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                        Upload related files to provide more context
                                                    </p>
                                                </div>

                                                <!-- File List -->
                                                <div class="space-y-2" x-show="scriptFiles.length > 0">
                                                    <h5 class="text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                                        Uploaded Files (<span x-text="scriptFiles.length"></span>/5)
                                                    </h5>
                                                    <ul class="space-y-1.5">
                                                        <template x-for="(file, index) in scriptFiles" :key="index">
                                                            <li class="flex items-center justify-between px-3 py-2 text-xs bg-zinc-50 dark:bg-zinc-700/30 rounded-md border border-zinc-200 dark:border-zinc-700">
                                                                <div class="flex items-center truncate">
                                                                    <i data-lucide="file" class="w-3.5 h-3.5 mr-2 text-indigo-500 dark:text-indigo-400"></i>
                                                                    <span class="truncate" x-text="file.name"></span>
                                                                </div>
                                                                <button @click="removeScriptFile(index)"
                                                                    class="text-zinc-400 hover:text-red-500 dark:hover:text-red-400">
                                                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                                </button>
                                                            </li>
                                                        </template>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Generation History (AI Mode Only) -->
                            <div x-show="scriptCreationMode === 'ai'">
                                <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-2 flex items-center">
                                    <i data-lucide="history" class="w-4 h-4 mr-2 text-indigo-500 dark:text-indigo-400"></i>
                                    Generation History
                                </h4>
                                <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
                                    <template x-for="(item, index) in scriptGenerationHistory" :key="index">
                                        <div @click="useScriptHistoryItem(index)"
                                            class="p-3 rounded-lg cursor-pointer bg-white dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors text-sm">
                                            <div class="flex justify-between items-start">
                                                <span class="font-medium text-zinc-900 dark:text-zinc-100" x-text="item.framework || 'Script'"></span>
                                                <span class="text-xs text-zinc-500 dark:text-zinc-400" x-text="formatTime(item.timestamp)"></span>
                                            </div>
                                            <p class="mt-1 text-zinc-600 dark:text-zinc-400 line-clamp-2" x-text="item.prompt"></p>
                                        </div>
                                    </template>
                                    <div x-show="scriptGenerationHistory.length === 0"
                                        class="p-4 text-center text-sm text-zinc-500 dark:text-zinc-400 italic">
                                        No generation history yet
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Input/Output -->
                    <div class="lg:col-span-2 flex flex-col h-full">
                        <!-- Tabs for Input/Output -->
                        <div class="px-6 pt-6 pb-0 flex border-b border-zinc-200 dark:border-zinc-700">
                            <!-- AI Mode Tabs -->
                            <template x-if="scriptCreationMode === 'ai'">
                                <div class="flex">
                                    <button @click="scriptTab = 'input'"
                                        class="px-4 py-2 font-medium text-sm border-b-2 -mb-px"
                                        :class="scriptTab === 'input' ?
                                            'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400' :
                                            'text-zinc-500 dark:text-zinc-400 border-transparent hover:text-zinc-700 dark:hover:text-zinc-300'">
                                        <i data-lucide="pencil" class="w-4 h-4 inline mr-1"></i> Input Prompt
                                    </button>
                                    <button @click="scriptTab = 'output'"
                                        class="px-4 py-2 font-medium text-sm border-b-2 -mb-px"
                                        :class="scriptTab === 'output' ?
                                            'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400' :
                                            'text-zinc-500 dark:text-zinc-400 border-transparent hover:text-zinc-700 dark:hover:text-zinc-300'">
                                        <i data-lucide="code" class="w-4 h-4 inline mr-1"></i> Generated Script
                                    </button>
                                </div>
                            </template>

                            <!-- Manual Mode Tab -->
                            <template x-if="scriptCreationMode === 'manual'">
                                <div class="flex">
                                    <div class="px-4 py-2 font-medium text-sm border-b-2 -mb-px text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400">
                                        <i data-lucide="edit-3" class="w-4 h-4 inline mr-1"></i> Manual Script
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- AI Input Tab -->
                        <div x-show="scriptCreationMode === 'ai' && scriptTab === 'input'"
                            class="p-6 overflow-y-auto flex-1">
                            <div class="space-y-4">
                                <!-- Prompt Templates -->
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Template <span class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional)</span>
                                    </label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button @click="useScriptTemplate('basic')"
                                            class="flex items-center px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="layout-template" class="w-4 h-4 mr-1.5 text-indigo-500"></i>
                                            Basic Test
                                        </button>
                                        <button @click="useScriptTemplate('detailed')"
                                            class="flex items-center px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="list-checks" class="w-4 h-4 mr-1.5 text-green-500"></i>
                                            Detailed Test
                                        </button>
                                    </div>
                                </div>

                                <!-- Prompt Input -->
                                <div>
                                    <label for="script-prompt" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Prompt <span class="text-red-500">*</span>
                                    </label>
                                    <textarea x-model="scriptPrompt" id="script-prompt" rows="12"
                                        placeholder="Describe what you want the test script to do. Be specific about testing scenarios, assertions, and edge cases."
                                        class="form-textarea w-full rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                                        :class="{ 'border-red-500 dark:border-red-500': scriptError }"></textarea>
                                    <p x-show="scriptError" x-text="scriptError" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                </div>

                                <!-- Generate Button -->
                                <div class="flex justify-center">
                                    <button @click="generateScript"
                                        class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg shadow-md hover:shadow-lg flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="scriptLoading || !scriptPrompt">
                                        <template x-if="!scriptLoading">
                                            <i data-lucide="sparkles" class="w-5 h-5 mr-2"></i>
                                        </template>
                                        <template x-if="scriptLoading">
                                            <svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </template>
                                        <span x-text="scriptLoading ? 'Generating...' : 'Generate Script'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- AI Output Tab -->
                        <div x-show="scriptCreationMode === 'ai' && scriptTab === 'output'"
                            class="flex-1 flex flex-col p-6 overflow-hidden">
                            <div x-show="!scriptResponse" class="flex-1 flex items-center justify-center">
                                <div class="text-center p-6">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-900/30 mb-4">
                                        <i data-lucide="code" class="w-8 h-8 text-indigo-600 dark:text-indigo-400"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No Script Generated Yet</h3>
                                    <p class="text-zinc-500 dark:text-zinc-400 max-w-md">
                                        Switch to the Input tab and provide a prompt to generate a script using AI.
                                    </p>
                                </div>
                            </div>

                            <div x-show="scriptResponse" class="flex-1 flex flex-col h-full">
                                <!-- Script Header -->
                                <div class="mb-4 flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Generated Script</h3>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Review and edit the generated script before saving</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="regenerateScript"
                                            class="p-2 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30"
                                            :disabled="scriptLoading">
                                            <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                                        </button>
                                        <button @click="copyScriptToClipboard"
                                            class="p-2 rounded-lg text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/30">
                                            <i data-lucide="clipboard-copy" class="w-5 h-5"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Script Content Editor -->
                                <div class="flex-1 mb-4 overflow-hidden">
                                    <div class="h-full relative">
                                        <textarea x-model="scriptContent" rows="15"
                                            class="w-full h-full px-4 py-3 font-mono text-sm bg-zinc-50 dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100"
                                            :class="{ 'language-python': scriptFramework === 'selenium-python', 'language-javascript': scriptFramework === 'cypress' }"></textarea>
                                    </div>
                                </div>

                                <!-- Script Name Input -->
                                <div class="mb-4">
                                    <label for="script-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                        Script Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="script-name" x-model="scriptName"
                                        class="form-input w-full rounded-lg h-10 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                                        placeholder="Enter a name for this script">
                                </div>
                            </div>
                        </div>

                        <!-- Manual Entry Tab -->
                        <div x-show="scriptCreationMode === 'manual'"
                            class="flex-1 flex flex-col p-6 overflow-hidden">
                            <div class="space-y-4 flex-1 flex flex-col">
                                <!-- Script Name Input -->
                                <div>
                                    <label for="manual-script-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                        Script Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="manual-script-name" x-model="scriptName"
                                        class="form-input w-full rounded-lg h-10 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                                        placeholder="Enter a name for this script">
                                </div>

                                <!-- Script Content Editor -->
                                <div class="flex-1 flex flex-col">
                                    <label for="manual-script-content" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                        Script Content <span class="text-red-500">*</span>
                                    </label>
                                    <textarea x-model="scriptContent" id="manual-script-content" rows="15"
                                        placeholder="Enter your test script code here..."
                                        class="flex-1 w-full px-4 py-3 font-mono text-sm bg-zinc-50 dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100"
                                        :class="{ 'language-python': scriptFramework === 'selenium-python', 'language-javascript': scriptFramework === 'cypress' }"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Save Button Footer (for both modes) -->
                        <div class="p-6 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-200 dark:border-zinc-700">
                            <div class="flex justify-end space-x-3">
                                <button @click="showScriptModal = false" class="btn-secondary">
                                    Cancel
                                </button>
                                <button @click="saveScript" class="btn-primary"
                                    :disabled="!scriptContent || !scriptName">
                                    <i data-lucide="save" class="w-4 h-4 mr-1.5"></i> Save Script
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        /* Form Styling Fixes for Modal */
        .form-input,
        .form-textarea,
        .form-select {
            @apply bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600;
            @apply focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500;
            min-height: 40px; /* Better height */
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            @apply dark:bg-zinc-700 dark:text-zinc-100;
        }

        /* Dark mode input text color */
        .dark .form-input::placeholder,
        .dark .form-textarea::placeholder {
            @apply text-zinc-400;
        }

        .dark .form-input,
        .dark .form-textarea,
        .dark .form-select {
            @apply bg-zinc-700 text-zinc-100 border-zinc-600;
        }

        /* Fix for textarea height */
        .form-textarea {
            min-height: 100px;
        }

        /* Ensure proper select styling in dark mode */
        .dark select {
            @apply bg-zinc-700 text-zinc-100 border-zinc-600;
        }

        /* Fix for dropdown arrow in dark mode */
        .dark select option {
            @apply bg-zinc-700 text-zinc-100;
        }

        /* Modal content text color */
        .modal-text {
            @apply text-zinc-700 dark:text-zinc-300;
        }

        /* File upload styling */
        .dark input[type="file"] {
            @apply text-zinc-100;
        }
    </style>
@endpush
