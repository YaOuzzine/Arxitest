{{-- resources/views/dashboard/test-scripts/create.blade.php --}}
@extends('layouts.dashboard')

@section('title', "Create Test Script - {$testCase->title}")

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.show', $project->id) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">{{ $project->name }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">{{ $testCase->title }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id]) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">Test Scripts</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Create</span>
    </li>
@endsection

@section('content')
    <div class="h-full space-y-6" x-data="scriptEditor">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-zinc-800 dark:from-zinc-100 to-zinc-600 dark:to-zinc-300 bg-clip-text text-transparent tracking-tight">
                    Create Test Script
                </h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Create script for test case: {{ $testCase->title }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id]) }}" class="px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 shadow-sm transition-all">
                    <i data-lucide="x" class="w-4 h-4 inline mr-1"></i>
                    Cancel
                </a>
                <button type="button" @click="saveScript" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm hover:shadow-md transition-all">
                    <i data-lucide="save" class="w-4 h-4 inline mr-1"></i>
                    Save Script
                </button>
            </div>
        </div>

        <!-- Creation Mode Tabs -->
        <div class="flex justify-center">
            <div class="inline-flex bg-zinc-100 dark:bg-zinc-800 p-1 rounded-lg shadow-sm">
                <button @click="creationMode = 'manual'"
                    :class="{ 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-sm border-b-2 border-blue-500': creationMode === 'manual', 'text-zinc-600 dark:text-zinc-400 hover:bg-white/30 dark:hover:bg-zinc-700/30': creationMode !== 'manual' }"
                    class="px-6 py-3 rounded-md font-medium transition-all duration-200 flex items-center space-x-2">
                    <i data-lucide="pen-square" class="w-5 h-5"></i>
                    <span>Manual Entry</span>
                </button>
                <button @click="creationMode = 'ai'"
                    :class="{ 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-sm border-b-2 border-blue-500': creationMode === 'ai', 'text-zinc-600 dark:text-zinc-400 hover:bg-white/30 dark:hover:bg-zinc-700/30': creationMode !== 'ai' }"
                    class="px-6 py-3 rounded-md font-medium transition-all duration-200 flex items-center space-x-2">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                    <span>AI Generation</span>
                </button>
            </div>
        </div>

        <!-- AI Generation Form -->
        <div x-show="creationMode === 'ai'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 space-y-4">
                <div class="bg-gradient-to-br from-indigo-50/80 to-purple-50/80 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-4 border border-indigo-100/70 dark:border-indigo-800/50">
                    <h3 class="text-lg font-semibold text-indigo-800 dark:text-indigo-200 mb-2 flex items-center">
                        <i data-lucide="lightbulb" class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400"></i>
                        AI Generation Options
                    </h3>

                    <div class="mb-4">
                        <label for="ai-framework-type" class="block text-sm font-medium text-indigo-700 dark:text-indigo-300 mb-2">
                            Framework Type <span class="text-red-500">*</span>
                        </label>
                        <select id="ai-framework-type" x-model="aiFrameworkType" class="w-full px-4 py-2.5 rounded-lg border border-indigo-200/70 dark:border-indigo-800/50 bg-white/70 dark:bg-zinc-800/50 text-indigo-800 dark:text-indigo-200 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all">
                            <option value="selenium-python">Selenium Python</option>
                            <option value="cypress">Cypress</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-xl p-4 border border-zinc-200/70 dark:border-zinc-700/50">
                    <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-3 flex items-center">
                        <i data-lucide="info" class="w-5 h-5 mr-2 text-zinc-600 dark:text-zinc-400"></i>
                        Test Case Details
                    </h3>
                    <div class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                        <p><span class="font-medium">Title:</span> {{ $testCase->title }}</p>
                        @if($testCase->steps && is_array($testCase->steps))
                            <div>
                                <p class="font-medium">Steps:</p>
                                <ol class="ml-5 list-decimal">
                                    @foreach($testCase->steps as $step)
                                        <li>{{ $step }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        @endif
                        <p><span class="font-medium">Expected Results:</span> {{ $testCase->expected_results }}</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white dark:bg-zinc-800 rounded-xl p-4 border border-zinc-200/70 dark:border-zinc-700/50">
                    <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-3 flex items-center">
                        <i data-lucide="message-square-plus" class="w-5 h-5 mr-2 text-zinc-600 dark:text-zinc-400"></i>
                        Prompt Builder
                    </h3>
                    <textarea
                        x-model="aiPrompt"
                        rows="5"
                        placeholder="Describe how you want the test script to be created. For example: 'Create a test that navigates to the login page, enters credentials, and verifies successful login.'"
                        class="w-full px-4 py-3 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
                    ></textarea>

                    <div class="mt-3 flex justify-end">
                        <button @click="generateScript" :disabled="isGenerating || !aiPrompt.trim()" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg shadow-md hover:shadow-lg flex items-center transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                            <template x-if="!isGenerating">
                                <i data-lucide="sparkles" class="w-5 h-5 mr-2"></i>
                            </template>
                            <template x-if="isGenerating">
                                <svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <span x-text="isGenerating ? 'Generating...' : 'Generate'"></span>
                        </button>
                    </div>
                </div>

                <div x-show="generatedScript" x-transition class="bg-gradient-to-br from-indigo-50/80 to-purple-50/80 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-4 border border-indigo-200/70 dark:border-indigo-800/40 shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="text-lg font-semibold text-indigo-800 dark:text-indigo-200 flex items-center">
                            <i data-lucide="file-code" class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400"></i>
                            Generated Script
                        </h3>
                        <div class="flex items-center space-x-2">
                            <button @click="useGeneratedScript" class="p-2 rounded-lg text-green-600 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors" title="Use This Script">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                    <div class="bg-white/80 dark:bg-zinc-800/60 rounded-lg p-4 border border-indigo-100 dark:border-indigo-800/30 max-h-96 overflow-auto">
                        <pre x-text="generatedScript" class="whitespace-pre-wrap font-mono text-sm text-zinc-800 dark:text-zinc-200"></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Form -->
        <form x-show="creationMode === 'manual'" id="script-form" method="POST" action="{{ route('dashboard.projects.test-cases.scripts.store', [$project->id, $testCase->id]) }}" @submit.prevent="handleSubmit">
            @csrf

            <div class="grid grid-cols-12 gap-6">
                <!-- Left Column: Script Details -->
                <div class="col-span-12 md:col-span-3 space-y-6">
                    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
                            <h2 class="text-lg font-medium text-zinc-900 dark:text-white">Script Details</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- Script Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Script Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" x-model="formData.name" class="w-full px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:ring-indigo-500 focus:border-indigo-500" required>
                            </div>

                            <!-- Framework Type -->
                            <div>
                                <label for="framework_type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Framework Type <span class="text-red-500">*</span>
                                </label>
                                <select name="framework_type" id="framework_type" x-model="formData.framework_type" @change="updateSyntaxHighlighting" class="w-full px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:ring-indigo-500 focus:border-indigo-500" required>
                                    <option value="selenium-python">Selenium Python</option>
                                    <option value="cypress">Cypress</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Editor Tools -->
                    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
                            <h2 class="text-lg font-medium text-zinc-900 dark:text-white">Editor Tools</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <button type="button" @click="indent" class="w-full px-3 py-2 text-sm bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 text-zinc-800 dark:text-zinc-200 rounded-lg transition-colors">
                                <i data-lucide="indent" class="w-4 h-4 inline mr-1"></i>
                                Indent Selection
                            </button>
                            <button type="button" @click="outdent" class="w-full px-3 py-2 text-sm bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 text-zinc-800 dark:text-zinc-200 rounded-lg transition-colors">
                                <i data-lucide="outdent" class="w-4 h-4 inline mr-1"></i>
                                Outdent Selection
                            </button>
                            <button type="button" @click="commentToggle" class="w-full px-3 py-2 text-sm bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 text-zinc-800 dark:text-zinc-200 rounded-lg transition-colors">
                                <i data-lucide="message-square" class="w-4 h-4 inline mr-1"></i>
                                Toggle Comment
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Code Editor -->
                <div class="col-span-12 md:col-span-9">
                    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden h-full">
                        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 flex justify-between items-center">
                            <h2 class="text-lg font-medium text-zinc-900 dark:text-white">Script Content</h2>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-zinc-500 dark:text-zinc-400" x-text="'Line: ' + cursorPosition.line + ' Col: ' + cursorPosition.column"></span>
                            </div>
                        </div>

                        <!-- Custom Code Editor -->
                        <div class="editor-container h-[600px] flex" @click="focusEditor">
                            <!-- Line Numbers -->
                            <div class="line-numbers w-16 h-full overflow-hidden py-4 text-right bg-zinc-100 dark:bg-zinc-900 text-zinc-500 dark:text-zinc-500 select-none" x-ref="lineNumbers">
                                <!-- Line numbers will be generated by JavaScript -->
                            </div>

                            <!-- Editor Area -->
                            <div class="editor-wrapper relative flex-grow h-full overflow-auto">
                                <!-- Hidden textarea for capturing input -->
                                <textarea x-ref="hiddenTextarea" x-model="formData.script_content" name="script_content" @input="handleInput" @keydown="handleKeyDown" @click="updateCursorPosition" @select="updateCursorPosition" class="absolute top-0 left-0 opacity-0 h-full w-full"></textarea>

                                <!-- Visible, highlighted code display -->
                                <div x-ref="cursor" class="editor-cursor" :style="cursorStyle"></div>
                                <pre x-ref="codeDisplay" class="code-display min-h-full p-4 m-0 overflow-visible whitespace-pre font-mono text-zinc-800 dark:text-zinc-200" x-html="highlightedCode"></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Notification -->
        <div x-show="showNotification" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed bottom-6 right-6 z-50 max-w-sm w-full shadow-lg border rounded-xl p-4" :class="{
                'bg-green-50/80 border-green-200/50 dark:bg-green-900/30 dark:border-green-800/30': notificationType === 'success',
                'bg-red-50/80 border-red-200/50 dark:bg-red-900/30 dark:border-red-800/30': notificationType === 'error'
            }">
            <div class="flex items-start">
                <div x-show="notificationType === 'success'" class="flex-shrink-0 w-5 h-5 mr-3 text-green-600 dark:text-green-400">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                </div>
                <div x-show="notificationType === 'error'" class="flex-shrink-0 w-5 h-5 mr-3 text-red-600 dark:text-red-400">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-medium mb-1" :class="{
                            'text-green-800 dark:text-green-200': notificationType === 'success',
                            'text-red-800 dark:text-red-200': notificationType === 'error'
                        }" x-text="notificationTitle"></h4>
                    <p class="text-sm" :class="{
                            'text-green-700/90 dark:text-green-300/90': notificationType === 'success',
                            'text-red-700/90 dark:text-red-300/90': notificationType === 'error'
                        }" x-text="notificationMessage"></p>
                </div>
                <button @click="hideNotification" class="ml-4 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .editor-cursor {
            position: absolute;
            width: 2px;
            background-color: #007bff;
            animation: blink 1s step-end infinite;
            height: 1.5em;
            pointer-events: none;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }

        .code-display {
            tab-size: 4;
            position: relative;
            cursor: text;
        }

        .editor-container {
            font-family: 'Menlo', 'Monaco', 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Syntax Highlighting Styles */
        .token-keyword { color: #569CD6; }
        .token-string { color: #CE9178; }
        .token-comment { color: #6A9955; font-style: italic; }
        .token-function { color: #DCDCAA; }
        .token-number { color: #B5CEA8; }
        .token-operator { color: #D4D4D4; }
        .token-class { color: #4EC9B0; }
        .token-parameter { color: #9CDCFE; }
        .token-punctuation { color: #D4D4D4; }
        .token-variable { color: #9CDCFE; }
        .token-selector { color: #D7BA7D; }

        /* Dark mode colors */
        .dark .token-keyword { color: #C586C0; }
        .dark .token-string { color: #CE9178; }
        .dark .token-comment { color: #6A9955; }
        .dark .token-function { color: #DCDCAA; }
        .dark .token-number { color: #B5CEA8; }
        .dark .token-operator { color: #D4D4D4; }
        .dark .token-class { color: #4EC9B0; }
        .dark .token-parameter { color: #9CDCFE; }
        .dark .token-punctuation { color: #D4D4D4; }
        .dark .token-variable { color: #9CDCFE; }
        .dark .token-selector { color: #D7BA7D; }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('scriptEditor', () => ({
                formData: {
                    name: '',
                    framework_type: 'selenium-python',
                    script_content: ''
                },
                highlightedCode: '',
                currentCaretPosition: 0,
                cursorPosition: {
                    line: 1,
                    column: 1
                },
                creationMode: 'manual',
                aiPrompt: '',
                aiFrameworkType: 'selenium-python',
                generatedScript: null,
                isGenerating: false,
                showNotification: false,
                notificationType: 'success',
                notificationTitle: '',
                notificationMessage: '',
                isSubmitting: false,
                cursorStyle: {
                    left: '0.5rem',
                    top: '0.5rem',
                    display: 'block'
                },
                lineHeight: 24, // Approx line height in pixels
                charWidth: 8.4, // Approx character width in pixels
                isFocused: false,

                init() {
                    this.updateHighlightedCode();
                    this.updateLineNumbers();
                    this.calculateFontMetrics();

                    // Focus/blur tracking
                    this.$refs.hiddenTextarea?.addEventListener('focus', () => {
                        this.isFocused = true;
                        this.updateCursorStyle();
                    });

                    this.$refs.hiddenTextarea?.addEventListener('blur', () => {
                        this.isFocused = false;
                        this.updateCursorStyle();
                    });

                    // Handle click on code display to position cursor
                    this.$refs.codeDisplay?.addEventListener('mousedown', this.handleCodeDisplayClick.bind(this));
                },

                focusEditor() {
                    this.$refs.hiddenTextarea?.focus();
                    this.isFocused = true;
                    this.updateCursorStyle();
                },

                calculateFontMetrics() {
                    // Create a temporary span to measure character width
                    const span = document.createElement('span');
                    span.style.visibility = 'hidden';
                    span.style.position = 'absolute';
                    span.style.whiteSpace = 'pre';
                    span.style.font = window.getComputedStyle(this.$refs.codeDisplay).font;
                    span.textContent = 'X'.repeat(100); // Measure 100 characters for better average

                    document.body.appendChild(span);
                    this.charWidth = span.getBoundingClientRect().width / 100;
                    this.lineHeight = parseInt(window.getComputedStyle(this.$refs.codeDisplay).lineHeight, 10);
                    if (isNaN(this.lineHeight)) {
                        // If line-height is 'normal', estimate based on font size
                        const fontSize = parseInt(window.getComputedStyle(this.$refs.codeDisplay).fontSize, 10);
                        this.lineHeight = Math.floor(fontSize * 1.5);
                    }
                    document.body.removeChild(span);
                },

                handleCodeDisplayClick(e) {
                    // Get click position relative to code display
                    const rect = this.$refs.codeDisplay.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;

                    // Calculate approximate line number
                    const clickedLine = Math.floor(y / this.lineHeight) + 1;

                    // Calculate approximate column
                    const clickedColumn = Math.floor(x / this.charWidth) + 1;

                    // Now find the actual position in the text
                    const lines = this.formData.script_content.split('\n');

                    // Ensure line is within bounds
                    const targetLine = Math.min(clickedLine, lines.length);
                    const targetLineContent = lines[targetLine - 1] || '';

                    // Ensure column is within bounds
                    const targetColumn = Math.min(clickedColumn, targetLineContent.length + 1);

                    // Calculate the absolute position in the text
                    let position = 0;
                    for (let i = 0; i < targetLine - 1; i++) {
                        position += lines[i].length + 1; // +1 for the newline
                    }
                    position += targetColumn - 1;

                    // Set cursor position in the hidden textarea
                    this.$refs.hiddenTextarea.focus();
                    this.$refs.hiddenTextarea.setSelectionRange(position, position);

                    // Update UI
                    this.updateCursorPosition();
                    this.updateCursorStyle();

                    // Prevent default to ensure focus is maintained
                    e.preventDefault();
                },

                updateCursorPosition() {
                    const textarea = this.$refs.hiddenTextarea;
                    if (!textarea) return;

                    const content = textarea.value;

                    // Calculate line and column
                    const textBeforeCursor = content.substring(0, textarea.selectionStart);
                    const lines = textBeforeCursor.split('\n');
                    const line = lines.length;
                    const column = lines[lines.length - 1].length + 1;

                    this.cursorPosition = { line, column };
                    this.currentCaretPosition = textarea.selectionStart;
                    this.updateCursorStyle();
                },

                updateCursorStyle() {
                    // Only show cursor when textarea is focused
                    if (!this.isFocused) {
                        this.cursorStyle = { display: 'none' };
                        return;
                    }

                    // Calculate cursor position based on line and column
                    const top = (this.cursorPosition.line - 1) * this.lineHeight + 0.5;
                    const left = (this.cursorPosition.column - 1) * this.charWidth + 0.5;

                    // Add padding offset of the code display
                    const paddingLeft = parseInt(window.getComputedStyle(this.$refs.codeDisplay).paddingLeft, 10) || 0;
                    const paddingTop = parseInt(window.getComputedStyle(this.$refs.codeDisplay).paddingTop, 10) || 0;

                    this.cursorStyle = {
                        display: 'block',
                        left: `${left + paddingLeft}px`,
                        top: `${top + paddingTop}px`
                    };
                },

                handleKeyDown(e) {
                    // Save the cursor position
                    this.updateCursorPosition();

                    // Handle special key combinations here if needed
                    // For example, Ctrl+S for save
                    if (e.ctrlKey && e.key === 's') {
                        e.preventDefault();
                        this.saveScript();
                    }
                    // Handle tab key for indentation
                    if (e.key === 'Tab') {
                        e.preventDefault();

                        const textarea = this.$refs.hiddenTextarea;
                        const start = textarea.selectionStart;
                        const end = textarea.selectionEnd;

                        if (start === end) {
                            // No selection, just insert a tab
                            const newText = textarea.value.substring(0, start) + '    ' + textarea.value.substring(end);
                            textarea.value = newText;
                            textarea.selectionStart = textarea.selectionEnd = start + 4;
                        } else {
                            // With selection, indent every line in the selection
                            const selectedText = textarea.value.substring(start, end);
                            const lines = selectedText.split('\n');
                            const indentedText = lines.map(line => '    ' + line).join('\n');

                            const newText = textarea.value.substring(0, start) + indentedText + textarea.value.substring(end);
                            textarea.value = newText;
                            textarea.selectionStart = start;
                            textarea.selectionEnd = start + indentedText.length;
                        }

                        // Update the model and highlighted code
                        this.formData.script_content = textarea.value;
                        this.updateHighlightedCode();
                        this.updateLineNumbers();
                    }
                },

                handleInput() {
                    this.updateHighlightedCode();
                    this.updateLineNumbers();
                    this.updateCursorPosition();
                },

                indent() {
                    const textarea = this.$refs.hiddenTextarea;
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;

                    if (start === end) {
                        // No selection, just insert spaces
                        const newText = textarea.value.substring(0, start) + '    ' + textarea.value.substring(end);
                        textarea.value = newText;
                        textarea.selectionStart = textarea.selectionEnd = start + 4;
                    } else {
                        // With selection, indent every line
                        const selectedText = textarea.value.substring(start, end);
                        const lines = selectedText.split('\n');
                        const indentedText = lines.map(line => '    ' + line).join('\n');

                        const newText = textarea.value.substring(0, start) + indentedText + textarea.value.substring(end);
                        textarea.value = newText;
                        textarea.selectionStart = start;
                        textarea.selectionEnd = start + indentedText.length;
                    }

                    // Update the model and highlighted code
                    this.formData.script_content = textarea.value;
                    this.updateHighlightedCode();
                    this.updateLineNumbers();
                    this.focusEditor();
                },

                outdent() {
                    const textarea = this.$refs.hiddenTextarea;
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;

                    if (start !== end) {
                        // With selection, outdent every line if possible
                        const selectedText = textarea.value.substring(start, end);
                        const lines = selectedText.split('\n');
                        const outdentedText = lines.map(line => {
                            if (line.startsWith('    ')) {
                                return line.substring(4);
                            } else if (line.startsWith('\t')) {
                                return line.substring(1);
                            }
                            return line;
                        }).join('\n');

                        const newText = textarea.value.substring(0, start) + outdentedText + textarea.value.substring(end);
                        textarea.value = newText;
                        textarea.selectionStart = start;
                        textarea.selectionEnd = start + outdentedText.length;

                        // Update the model and highlighted code
                        this.formData.script_content = textarea.value;
                        this.updateHighlightedCode();
                        this.updateLineNumbers();
                    }

                    this.focusEditor();
                },

                commentToggle() {
                    const textarea = this.$refs.hiddenTextarea;
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;

                    // Get selected text
                    const selectedText = textarea.value.substring(start, end);
                    const lines = selectedText.split('\n');

                    // Determine if we should comment or uncomment
                    const allCommented = lines.every(line => {
                        return this.formData.framework_type === 'cypress' ?
                            line.trimStart().startsWith('//') :
                            line.trimStart().startsWith('#');
                    });

                    // Toggle comments
                    const commentChar = this.formData.framework_type === 'cypress' ? '//' : '#';
                    const toggledText = lines.map(line => {
                        if (allCommented) {
                            // Uncomment
                            if (this.formData.framework_type === 'cypress' && line.trimStart().startsWith('//')) {
                                const lineStart = line.search('//');
                                return line.substring(0, lineStart) + line.substring(lineStart + 2);
                            } else if (line.trimStart().startsWith('#')) {
                                const lineStart = line.search('#');
                                return line.substring(0, lineStart) + line.substring(lineStart + 1);
                            }
                            return line;
                        } else {
                            // Comment
                            return commentChar + ' ' + line;
                        }
                    }).join('\n');

                    // Update the textarea
                    const newText = textarea.value.substring(0, start) + toggledText + textarea.value.substring(end);
                    textarea.value = newText;
                    textarea.selectionStart = start;
                    textarea.selectionEnd = start + toggledText.length;

                    // Update the model and highlighted code
                    this.formData.script_content = textarea.value;
                    this.updateHighlightedCode();
                    this.updateLineNumbers();
                    this.focusEditor();
                },

                updateHighlightedCode() {
                    this.highlightedCode = this.highlightCode(this.formData.script_content);
                },

                updateLineNumbers() {
                    if (!this.$refs.lineNumbers) return;

                    const lines = this.formData.script_content.split('\n');
                    const lineCount = lines.length;

                    // Generate line numbers HTML
                    let lineNumbersHtml = '';
                    for (let i = 1; i <= lineCount; i++) {
                        lineNumbersHtml += `<div class="line-number px-4">${i}</div>`;
                    }

                    this.$refs.lineNumbers.innerHTML = lineNumbersHtml;
                },

                updateSyntaxHighlighting() {
                    this.updateHighlightedCode();
                },

                highlightCode(code) {
                    if (!code) return '';

                    // Choose tokenizer based on framework type
                    if (this.formData.framework_type === 'cypress') {
                        return this.highlightJavaScript(code);
                    } else {
                        return this.highlightPython(code);
                    }
                },

                highlightPython(code) {
                    // Python keywords
                    const keywords = [
                        'and', 'as', 'assert', 'async', 'await', 'break', 'class', 'continue',
                        'def', 'del', 'elif', 'else', 'except', 'False', 'finally', 'for',
                        'from', 'global', 'if', 'import', 'in', 'is', 'lambda', 'None',
                        'nonlocal', 'not', 'or', 'pass', 'raise', 'return', 'True', 'try',
                        'while', 'with', 'yield', 'self'
                    ];

                    // Common Python builtins and Selenium functions
                    const functions = [
                        'print', 'len', 'str', 'int', 'float', 'list', 'dict', 'set', 'tuple',
                        'find_element', 'find_elements', 'click', 'send_keys', 'get',
                        'implicitly_wait', 'execute_script', 'maximize_window', 'switch_to',
                        'select_by_visible_text'
                    ];

                    // Simple Python tokenizer
                    let result = '';
                    let inString = false;
                    let stringChar = '';
                    let inComment = false;
                    let current = '';

                    for (let i = 0; i < code.length; i++) {
                        const char = code[i];

                        // Handle comments
                        if (char === '#' && !inString) {
                            inComment = true;
                            if (current) {
                                result += this.tokenize(current, keywords, functions);
                                current = '';
                            }
                            result += '<span class="token-comment">#';
                            continue;
                        }

                        if (inComment) {
                            if (char === '\n') {
                                inComment = false;
                                result += '</span>\n';
                            } else {
                                result += this.escapeHtml(char);
                            }
                            continue;
                        }

                        // Handle strings
                        if ((char === '"' || char === "'") && (i === 0 || code[i - 1] !== '\\')) {
                            if (inString) {
                                if (char === stringChar) {
                                    inString = false;
                                    result += this.escapeHtml(char) + '</span>';
                                } else {
                                    result += this.escapeHtml(char);
                                }
                            } else {
                                if (current) {
                                    result += this.tokenize(current, keywords, functions);
                                    current = '';
                                }
                                inString = true;
                                stringChar = char;
                                result += '<span class="token-string">' + this.escapeHtml(char);
                            }
                            continue;
                        }

                        if (inString) {
                            result += this.escapeHtml(char);
                            continue;
                        }

                        // Handle operators and punctuation
                        if (/[+\-*/%=<>!&|^~:;,.?()[\]{}]/.test(char)) {
                            if (current) {
                                result += this.tokenize(current, keywords, functions);
                                current = '';
                            }
                            result += '<span class="token-operator">' + this.escapeHtml(char) + '</span>';
                            continue;
                        }

                        // Handle whitespace and word boundaries
                        if (/\s/.test(char) || i === code.length - 1) {
                            if (i === code.length - 1 && !/\s/.test(char)) {
                                current += char;
                            }

                            if (current) {
                                result += this.tokenize(current, keywords, functions);
                                current = '';
                            }

                            if (char === '\n') {
                                result += '\n';
                            } else if (/\s/.test(char)) {
                                result += char;
                            }
                            continue;
                        }

                        current += char;
                    }

                    // Handle any remaining text
                    if (current) {
                        result += this.tokenize(current, keywords, functions);
                    }

                    return result;
                },

                highlightJavaScript(code) {
                    // JavaScript keywords
                    const keywords = [
                        'await', 'break', 'case', 'catch', 'class', 'const', 'continue',
                        'debugger', 'default', 'delete', 'do', 'else', 'export', 'extends',
                        'false', 'finally', 'for', 'function', 'if', 'import', 'in', 'instanceof',
                        'new', 'null', 'return', 'super', 'switch', 'this', 'throw', 'true',
                        'try', 'typeof', 'var', 'void', 'while', 'with', 'yield', 'let', 'static',
                        'async', 'of'
                    ];

                    // Common JavaScript functions and Cypress methods
                    const functions = [
                        'console', 'log', 'document', 'window', 'setTimeout', 'clearTimeout',
                        'cy', 'visit', 'get', 'contains', 'click', 'type', 'should', 'and',
                        'expect', 'find', 'eq', 'first', 'last', 'wait', 'its', 'invoke'
                    ];

                    // Simple JavaScript tokenizer
                    let result = '';
                    let inString = false;
                    let stringChar = '';
                    let inComment = false;
                    let inMultilineComment = false;
                    let current = '';

                    for (let i = 0; i < code.length; i++) {
                        const char = code[i];
                        const nextChar = i < code.length - 1 ? code[i + 1] : '';

                        // Handle comments
                        if (!inString && !inComment && !inMultilineComment && char === '/' && nextChar === '/') {
                            inComment = true;
                            if (current) {
                                result += this.tokenize(current, keywords, functions);
                                current = '';
                            }
                            result += '<span class="token-comment">//';
                            i++; // Skip the next character
                            continue;
                        }

                        if (!inString && !inComment && !inMultilineComment && char === '/' && nextChar === '*') {
                            inMultilineComment = true;
                            if (current) {
                                result += this.tokenize(current, keywords, functions);
                                current = '';
                            }
                            result += '<span class="token-comment">/*';
                            i++; // Skip the next character
                            continue;
                        }

                        if (inComment) {
                            if (char === '\n') {
                                inComment = false;
                                result += '</span>\n';
                            } else {
                                result += this.escapeHtml(char);
                            }
                            continue;
                        }

                        if (inMultilineComment) {
                            if (char === '*' && nextChar === '/') {
                                inMultilineComment = false;
                                result += '*/</span>';
                                i++; // Skip the next character
                            } else {
                                result += this.escapeHtml(char);
                            }
                            continue;
                        }

                        // Handle strings
                        if ((char === '"' || char === "'" || char === '`') && (i === 0 || code[i - 1] !== '\\')) {
                            if (inString) {
                                if (char === stringChar) {
                                    inString = false;
                                    result += this.escapeHtml(char) + '</span>';
                                } else {
                                    result += this.escapeHtml(char);
                                }
                            } else {
                                if (current) {
                                    result += this.tokenize(current, keywords, functions);
                                    current = '';
                                }
                                inString = true;
                                stringChar = char;
                                result += '<span class="token-string">' + this.escapeHtml(char);
                            }
                            continue;
                        }

                        if (inString) {
                            result += this.escapeHtml(char);
                            continue;
                        }

                        // Handle operators and punctuation
                        if (/[+\-*/%=<>!&|^~:;,.?()[\]{}]/.test(char)) {
                            if (current) {
                                result += this.tokenize(current, keywords, functions);
                                current = '';
                            }
                            result += '<span class="token-operator">' + this.escapeHtml(char) + '</span>';
                            continue;
                        }

                        // Handle whitespace and word boundaries
                        if (/\s/.test(char) || i === code.length - 1) {
                            if (i === code.length - 1 && !/\s/.test(char)) {
                                current += char;
                            }

                            if (current) {
                                result += this.tokenize(current, keywords, functions);
                                current = '';
                            }

                            if (char === '\n') {
                                result += '\n';
                            } else if (/\s/.test(char)) {
                                result += char;
                            }
                            continue;
                        }

                        current += char;
                    }

                    // Handle any remaining text
                    if (current) {
                        result += this.tokenize(current, keywords, functions);
                    }

                    return result;
                },

                tokenize(word, keywords, functions) {
                    // Check if the word is a keyword, function or number
                    if (keywords.includes(word)) {
                        return `<span class="token-keyword">${this.escapeHtml(word)}</span>`;
                    } else if (functions.includes(word)) {
                        return `<span class="token-function">${this.escapeHtml(word)}</span>`;
                    } else if (/^\d+(\.\d+)?$/.test(word)) {
                        return `<span class="token-number">${this.escapeHtml(word)}</span>`;
                    } else if (word.includes('.')) {
                        // Handle possible method calls or properties
                        const parts = word.split('.');
                        let result = '';

                        for (let i = 0; i < parts.length; i++) {
                            if (i > 0) {
                                result += '<span class="token-punctuation">.</span>';
                            }

                            if (functions.includes(parts[i])) {
                                result += `<span class="token-function">${this.escapeHtml(parts[i])}</span>`;
                            } else if (keywords.includes(parts[i])) {
                                result += `<span class="token-keyword">${this.escapeHtml(parts[i])}</span>`;
                            } else {
                                result += `<span class="token-variable">${this.escapeHtml(parts[i])}</span>`;
                            }
                        }

                        return result;
                    }

                    return this.escapeHtml(word);
                },

                escapeHtml(text) {
                    const map = {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    };
                    return text.replace(/[&<>"']/g, function(m) {
                        return map[m];
                    });
                },

                showNotificationMessage(type, title, message) {
                    this.notificationType = type;
                    this.notificationTitle = title;
                    this.notificationMessage = message;
                    this.showNotification = true;

                    // Hide after 5 seconds
                    setTimeout(() => {
                        this.hideNotification();
                    }, 5000);
                },

                hideNotification() {
                    this.showNotification = false;
                },

                async generateScript() {
                    if (!this.aiPrompt.trim() || this.isGenerating) {
                        return;
                    }

                    try {
                        this.isGenerating = true;

                        const response = await fetch('{{ route("api.ai.generate", "test-script") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                prompt: this.aiPrompt,
                                context: {
                                    project_id: '{{ $project->id }}',
                                    test_case_id: '{{ $testCase->id }}',
                                    framework_type: this.aiFrameworkType
                                }
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.generatedScript = result.data.content;
                            this.showNotificationMessage('success', 'Success', 'Script generated successfully');
                        } else {
                            throw new Error(result.message || 'Failed to generate script');
                        }
                    } catch (error) {
                        console.error('Error generating script:', error);
                        this.showNotificationMessage('error', 'Error', error.message || 'An error occurred during generation');
                    } finally {
                        this.isGenerating = false;
                    }
                },

                useGeneratedScript() {
                    if (!this.generatedScript) return;

                    this.formData.name = this.formData.name || `${this.aiFrameworkType} Script for ${this.testCase?.title || 'Test Case'}`;
                    this.formData.framework_type = this.aiFrameworkType;
                    this.formData.script_content = this.generatedScript;

                    this.updateHighlightedCode();
                    this.updateLineNumbers();
                    this.creationMode = 'manual';

                    this.showNotificationMessage('success', 'Content Applied', 'Generated script applied to the editor');
                },

                async saveScript() {
                    try {
                        this.isSubmitting = true;

                        // Validate form
                        if (!this.formData.name.trim()) {
                            this.showNotificationMessage('error', 'Validation Error', 'Script name is required');
                            return;
                        }

                        if (this.creationMode === 'manual' && !this.formData.script_content.trim()) {
                            this.showNotificationMessage('error', 'Validation Error', 'Script content is required');
                            return;
                        }

                        // If in AI mode and there's a generated script, use it
                        if (this.creationMode === 'ai' && this.generatedScript) {
                            this.useGeneratedScript();
                        }

                        // Submit form
                        document.getElementById('script-form').submit();
                    } catch (error) {
                        console.error('Error saving script:', error);
                        this.showNotificationMessage('error', 'Error', error.message || 'An error occurred while saving');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                handleSubmit() {
                    this.saveScript();
                }
            }));
        });
    </script>
@endpush
