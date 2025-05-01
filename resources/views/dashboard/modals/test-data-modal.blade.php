@props(['testCase', 'project', 'testScripts'])

<!-- Create Test Data Modal -->
<div x-cloak x-show="showDataModal" @keydown.escape.window="showDataModal = false"
    class="fixed inset-0 overflow-y-auto z-50" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-zinc-900/70 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
        @click="showDataModal = false"></div>

    <!-- Modal Panel -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative w-full max-w-6xl bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-8">

            <!-- Header -->
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-gradient-to-r from-teal-50 to-emerald-50 dark:from-teal-900/20 dark:to-emerald-900/20">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-teal-900 dark:text-teal-100 flex items-center">
                        <div class="flex items-center gap-2">
                            <i data-lucide="database" class="w-5 h-5 text-teal-600 dark:text-teal-400"></i>
                            <span>Create Test Data</span>
                        </div>
                    </h3>
                    <button @click="showDataModal = false"
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
                                    <button @click="dataCreationMode = 'ai'"
                                        :class="{
                                            'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 font-medium': dataCreationMode === 'ai',
                                            'bg-white dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-700/50': dataCreationMode !== 'ai'
                                        }"
                                        class="flex-1 py-2.5 px-3 text-sm transition-colors">
                                        <i data-lucide="sparkles" class="w-4 h-4 inline-block mr-1"></i>
                                        AI-Assisted
                                    </button>
                                    <button @click="dataCreationMode = 'manual'"
                                        :class="{
                                            'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 font-medium': dataCreationMode === 'manual',
                                            'bg-white dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-700/50': dataCreationMode !== 'manual'
                                        }"
                                        class="flex-1 py-2.5 px-3 text-sm transition-colors">
                                        <i data-lucide="edit-3" class="w-4 h-4 inline-block mr-1"></i> Manual
                                    </button>
                                </div>
                            </div>

                            <!-- Format Selection -->
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Data Format <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select x-model="dataFormat" class="form-select w-full rounded-lg h-10 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600">
                                        <template x-for="option in dataFormatOptions">
                                            <option :value="option.value" x-text="option.label"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <!-- Context Information -->
                            <div x-show="dataCreationMode === 'ai'" class="bg-white dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-2 flex items-center">
                                    <i data-lucide="info" class="w-4 h-4 mr-2 text-teal-500 dark:text-teal-400"></i>
                                    Context Information
                                </h4>
                                <div class="text-sm text-zinc-600 dark:text-zinc-300 space-y-2">
                                    <div>
                                        <span class="font-medium">Test Case:</span> {{ $testCase->title }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Expected Results:</span>
                                        <div class="text-xs mt-1 line-clamp-2">{{ $testCase->expected_results }}</div>
                                    </div>
                                    @if ($testScripts->count() > 0)
                                        <div>
                                            <span class="font-medium">Test Scripts:</span> {{ $testScripts->count() }} available
                                        </div>
                                    @endif
                                </div>

                                <!-- Additional Context Toggle -->
                                <div x-data="{ showAdditionalContext: false }" class="mt-3">
                                    <button @click="showAdditionalContext = !showAdditionalContext"
                                        class="text-xs flex items-center text-teal-600 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-300">
                                        <i data-lucide="plus-circle" class="w-3.5 h-3.5 mr-1" x-show="!showAdditionalContext"></i>
                                        <i data-lucide="minus-circle" class="w-3.5 h-3.5 mr-1" x-show="showAdditionalContext"></i>
                                        <span x-text="showAdditionalContext ? 'Hide Additional Context' : 'Add Additional Context'"></span>
                                    </button>

                                    <div x-show="showAdditionalContext" x-collapse class="mt-3 space-y-3">
                                        <!-- Script Selection -->
                                        @if ($testScripts->count() > 0)
                                            <div>
                                                <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                    Reference Script
                                                </label>
                                                <select x-model="dataReferenceScript" class="form-select w-full rounded-lg h-8 text-xs bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600">
                                                    <option value="">None</option>
                                                    @foreach ($testScripts as $script)
                                                        <option value="{{ $script->id }}">{{ $script->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        <!-- Data Structure -->
                                        <div>
                                            <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                Data Structure <span class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional)</span>
                                            </label>
                                            <textarea x-model="dataStructure" rows="4"
                                                class="form-textarea w-full rounded-lg text-xs font-mono bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                                                placeholder="Describe the structure of the data you need (fields, expected types, constraints)"></textarea>
                                        </div>

                                        <!-- Example Data -->
                                        <div>
                                            <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                Example Data <span class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional)</span>
                                            </label>
                                            <textarea x-model="dataExample" rows="4"
                                                class="form-textarea w-full rounded-lg text-xs font-mono bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                                                placeholder="Paste examples of valid and/or invalid data here"></textarea>
                                        </div>

                                        <!-- File Upload System -->
                                        <div>
                                            <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                Reference Files <span class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional, up to 5)</span>
                                            </label>
                                            <div class="space-y-3">
                                                <div x-show="dataFiles.length < 5">
                                                    <input type="file" id="data-files"
                                                        class="block w-full text-xs text-zinc-600 dark:text-zinc-400
                                                        file:mr-3 file:py-1.5 file:px-3
                                                        file:text-xs file:font-medium
                                                        file:border file:border-zinc-200 dark:file:border-zinc-600
                                                        file:bg-white dark:file:bg-zinc-700
                                                        file:text-teal-600 dark:file:text-teal-400
                                                        hover:file:bg-zinc-50 dark:hover:file:bg-zinc-600
                                                        file:rounded-md"
                                                        @change="handleDataFileUpload($event)"
                                                        accept=".json,.csv,.xml,.txt,.py,.js">
                                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                        Upload related files to provide more context
                                                    </p>
                                                </div>

                                                <!-- File List -->
                                                <div class="space-y-2" x-show="dataFiles.length > 0">
                                                    <h5 class="text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                                        Uploaded Files (<span x-text="dataFiles.length"></span>/5)
                                                    </h5>
                                                    <ul class="space-y-1.5">
                                                        <template x-for="(file, index) in dataFiles" :key="index">
                                                            <li class="flex items-center justify-between px-3 py-2 text-xs bg-zinc-50 dark:bg-zinc-700/30 rounded-md border border-zinc-200 dark:border-zinc-700">
                                                                <div class="flex items-center truncate">
                                                                    <i data-lucide="file" class="w-3.5 h-3.5 mr-2 text-teal-500 dark:text-teal-400"></i>
                                                                    <span class="truncate" x-text="file.name"></span>
                                                                </div>
                                                                <button @click="removeDataFile(index)"
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
                            <div x-show="dataCreationMode === 'ai'">
                                <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-2 flex items-center">
                                    <i data-lucide="history" class="w-4 h-4 mr-2 text-teal-500 dark:text-teal-400"></i>
                                    Generation History
                                </h4>
                                <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
                                    <template x-for="(item, index) in dataGenerationHistory" :key="index">
                                        <div @click="useDataHistoryItem(index)"
                                            class="p-3 rounded-lg cursor-pointer bg-white dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-700 hover:bg-teal-50 dark:hover:bg-teal-900/20 transition-colors text-sm">
                                            <div class="flex justify-between items-start">
                                                <span class="font-medium text-zinc-900 dark:text-zinc-100" x-text="item.format || 'Data'"></span>
                                                <span class="text-xs text-zinc-500 dark:text-zinc-400" x-text="formatTime(item.timestamp)"></span>
                                            </div>
                                            <p class="mt-1 text-zinc-600 dark:text-zinc-400 line-clamp-2" x-text="item.prompt"></p>
                                        </div>
                                    </template>
                                    <div x-show="dataGenerationHistory.length === 0"
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
                            <template x-if="dataCreationMode === 'ai'">
                                <div class="flex">
                                    <button @click="dataTab = 'input'"
                                        class="px-4 py-2 font-medium text-sm border-b-2 -mb-px"
                                        :class="dataTab === 'input' ?
                                            'text-teal-600 dark:text-teal-400 border-teal-600 dark:border-teal-400' :
                                            'text-zinc-500 dark:text-zinc-400 border-transparent hover:text-zinc-700 dark:hover:text-zinc-300'">
                                        <i data-lucide="pencil" class="w-4 h-4 inline mr-1"></i> Input Prompt
                                    </button>
                                    <button @click="dataTab = 'output'"
                                        class="px-4 py-2 font-medium text-sm border-b-2 -mb-px"
                                        :class="dataTab === 'output' ?
                                            'text-teal-600 dark:text-teal-400 border-teal-600 dark:border-teal-400' :
                                            'text-zinc-500 dark:text-zinc-400 border-transparent hover:text-zinc-700 dark:hover:text-zinc-300'">
                                        <i data-lucide="database" class="w-4 h-4 inline mr-1"></i> Generated Data
                                    </button>
                                </div>
                            </template>

                            <!-- Manual Mode Tab -->
                            <template x-if="dataCreationMode === 'manual'">
                                <div class="flex">
                                    <div class="px-4 py-2 font-medium text-sm border-b-2 -mb-px text-teal-600 dark:text-teal-400 border-teal-600 dark:border-teal-400">
                                        <i data-lucide="edit-3" class="w-4 h-4 inline mr-1"></i> Manual Data
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- AI Input Tab -->
                        <div x-show="dataCreationMode === 'ai' && dataTab === 'input'"
                            class="p-6 overflow-y-auto flex-1">
                            <div class="space-y-4">
                                <!-- Prompt Templates -->
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Template <span class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional)</span>
                                    </label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button @click="useDataTemplate('valid')"
                                            class="flex items-center px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="check-circle" class="w-4 h-4 mr-1.5 text-green-500"></i>
                                            Valid Test Data
                                        </button>
                                        <button @click="useDataTemplate('invalid')"
                                            class="flex items-center px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="x-circle" class="w-4 h-4 mr-1.5 text-red-500"></i>
                                            Invalid Test Data
                                        </button>
                                        <button @click="useDataTemplate('mixed')"
                                            class="flex items-center px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="shuffle" class="w-4 h-4 mr-1.5 text-purple-500"></i>
                                            Mixed Data Set
                                        </button>
                                        <button @click="useDataTemplate('edge')"
                                            class="flex items-center px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="alert-triangle" class="w-4 h-4 mr-1.5 text-amber-500"></i>
                                            Edge Cases
                                        </button>
                                    </div>
                                </div>

                                <!-- Prompt Input -->
                                <div>
                                    <label for="data-prompt" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Prompt <span class="text-red-500">*</span>
                                    </label>
                                    <textarea x-model="dataPrompt" id="data-prompt" rows="12"
                                        placeholder="Describe what test data you need. Include details about data structure, required fields, and edge cases you want to test."
                                        class="form-textarea w-full rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                                        :class="{ 'border-red-500 dark:border-red-500': dataError }"></textarea>
                                    <p x-show="dataError" x-text="dataError" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                </div>

                                <!-- Generate Button -->
                                <div class="flex justify-center">
                                    <button @click="generateData"
                                        class="px-6 py-2.5 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white rounded-lg shadow-md hover:shadow-lg flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="dataLoading || !dataPrompt">
                                        <template x-if="!dataLoading">
                                            <i data-lucide="sparkles" class="w-5 h-5 mr-2"></i>
                                        </template>
                                        <template x-if="dataLoading">
                                            <svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </template>
                                        <span x-text="dataLoading ? 'Generating...' : 'Generate Data'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- AI Output Tab -->
                        <div x-show="dataCreationMode === 'ai' && dataTab === 'output'"
                            class="flex-1 flex flex-col p-6 overflow-hidden">
                            <div x-show="!dataResponse" class="flex-1 flex items-center justify-center">
                                <div class="text-center p-6">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-teal-50 dark:bg-teal-900/30 mb-4">
                                        <i data-lucide="database" class="w-8 h-8 text-teal-600 dark:text-teal-400"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No Data Generated Yet</h3>
                                    <p class="text-zinc-500 dark:text-zinc-400 max-w-md">
                                        Switch to the Input tab and provide a prompt to generate test data using AI.
                                    </p>
                                </div>
                            </div>

                            <div x-show="dataResponse" class="flex-1 flex flex-col h-full">
                                <!-- Data Header -->
                                <div class="mb-4 flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Generated Test Data</h3>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Review and edit the generated data before saving</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="regenerateData"
                                            class="p-2 rounded-lg text-teal-600 dark:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-900/30"
                                            :disabled="dataLoading">
                                            <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                                        </button>
                                        <button @click="copyDataToClipboard"
                                            class="p-2 rounded-lg text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/30">
                                            <i data-lucide="clipboard-copy" class="w-5 h-5"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Data Content Editor -->
                                <div class="flex-1 mb-4 overflow-hidden">
                                    <div class="h-full relative">
                                        <textarea x-model="dataContent" rows="15"
                                            class="w-full h-full px-4 py-3 font-mono text-sm bg-zinc-50 dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100"
                                            :class="{
                                                'language-json': dataFormat === 'json',
                                                'language-csv': dataFormat === 'csv',
                                                'language-xml': dataFormat === 'xml',
                                                'language-plaintext': dataFormat === 'plain'
                                            }"></textarea>
                                    </div>
                                </div>

                                <!-- Common Fields -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="data-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                            Data Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" id="data-name" x-model="dataName"
                                            class="form-input w-full rounded-lg h-10 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                                            placeholder="Enter a name for this test data">
                                    </div>
                                    <div>
                                        <label for="data-usage-context" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                            Usage Context <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" id="data-usage-context" x-model="dataUsageContext"
                                            class="form-input w-full rounded-lg h-10 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                                            placeholder="e.g., 'Valid input scenario' or 'Edge case testing'">
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" x-model="dataIsSensitive" class="form-checkbox">
                                        <span class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">
                                            Mark as sensitive data (contains private, personal, or confidential information)
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Entry Tab -->
                        <div x-show="dataCreationMode === 'manual'"
                            class="flex-1 flex flex-col p-6 overflow-hidden">
                            <div class="space-y-4 flex-1">
                                <!-- Data Name Input -->
                                <div>
                                    <label for="manual-data-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                        Data Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="manual-data-name" x-model="dataName"
                                        class="form-input w-full rounded-lg h-10 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                                        placeholder="Enter a name for this test data">
                                </div>

                                <!-- Data Content Editor -->
                                <div class="flex-1">
                                    <label for="manual-data-content" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                    </label>
                                    <textarea x-model="dataContent" id="manual-data-content" rows="15"
                                        placeholder="Enter your test data content here..."
                                        class="flex-1 w-full px-4 py-3 font-mono text-sm bg-zinc-50 dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100"
                                        :class="{
                                            'language-json': dataFormat === 'json',
                                            'language-csv': dataFormat === 'csv',
                                            'language-xml': dataFormat === 'xml',
                                            'language-plaintext': dataFormat === 'plain'
                                        }"></textarea>
                                </div>

                                <!-- Usage Context -->
                                <div>
                                    <label for="manual-data-usage-context" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                        Usage Context <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="manual-data-usage-context" x-model="dataUsageContext"
                                        class="form-input w-full rounded-lg h-10 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                                        placeholder="e.g., 'Valid input scenario' or 'Edge case testing'">
                                </div>

                                <!-- Sensitive Data Checkbox -->
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" x-model="dataIsSensitive" class="form-checkbox">
                                        <span class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">
                                            Mark as sensitive data (contains private, personal, or confidential information)
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Save Button Footer (for both modes) -->
                        <div class="p-6 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-200 dark:border-zinc-700">
                            <div class="flex justify-end space-x-3">
                                <button @click="showDataModal = false" class="btn-secondary">
                                    Cancel
                                </button>
                                <button @click="saveData" class="btn-primary"
                                    :disabled="!dataContent || !dataName || !dataUsageContext">
                                    <i data-lucide="save" class="w-4 h-4 mr-1.5"></i> Save Test Data
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
        /* Test Data Modal Specific Styles */

        /* Form controls with proper dark mode support */
        .test-data-modal .form-input,
        .test-data-modal .form-textarea,
        .test-data-modal .form-select {
            @apply w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600;
            @apply bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100;
            @apply rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500;
            min-height: 40px;
        }

        /* Textarea specific height */
        .test-data-modal .form-textarea {
            min-height: 100px;
        }

        /* Placeholder styling for dark mode */
        .dark .test-data-modal .form-input::placeholder,
        .dark .test-data-modal .form-textarea::placeholder {
            @apply text-zinc-400;
        }

        /* Select dropdown for data modal */
        .test-data-modal .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        .dark .test-data-modal .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%9ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        }

        /* Checkbox styling */
        .test-data-modal .form-checkbox {
            @apply shadow-sm focus:ring-teal-500 text-teal-600 border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 dark:checked:bg-teal-500 dark:focus:ring-offset-zinc-800;
        }

        /* Code/data preview styling */
        .test-data-modal .code-preview {
            @apply font-mono text-sm bg-zinc-50 dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg;
        }

        /* File input styling */
        .test-data-modal input[type="file"] {
            @apply text-zinc-500 dark:text-zinc-400;
        }

        /* Button overrides for this modal */
        .test-data-modal .btn-primary {
            @apply bg-teal-600 hover:bg-teal-700;
        }

        /* Template buttons */
        .test-data-modal .template-button {
            @apply flex items-center px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm;
            @apply hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors;
        }

        /* History items */
        .test-data-modal .history-item {
            @apply p-3 rounded-lg cursor-pointer bg-white dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-700;
            @apply hover:bg-teal-50 dark:hover:bg-teal-900/20 transition-colors;
        }
    </style>
@endpush
