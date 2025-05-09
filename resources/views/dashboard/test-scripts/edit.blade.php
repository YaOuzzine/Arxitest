{{-- resources/views/dashboard/test-scripts/edit.blade.php --}}
@extends('layouts.dashboard')

@section('title', "Edit Test Script - {$testScript->name}")

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
        <a href="{{ route('dashboard.projects.test-cases.scripts.show', [$project->id, $testCase->id, $testScript->id]) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">{{ $testScript->name }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Edit</span>
    </li>
@endsection

@section('content')
    <div class="h-full space-y-6" x-data="testScriptEditor" x-init="initEditor()">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-zinc-800 dark:from-zinc-100 to-zinc-600 dark:to-zinc-300 bg-clip-text text-transparent tracking-tight">
                    Edit: {{ $testScript->name }}
                </h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Update test script for {{ $testCase->title }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('dashboard.projects.test-cases.scripts.show', [$project->id, $testCase->id, $testScript->id]) }}"
                   class="btn-secondary px-4 py-2 rounded-lg flex items-center">
                    <i data-lucide="x" class="w-4 h-4 mr-2"></i>
                    Cancel
                </a>
                <button @click="saveScript" type="button"
                   class="btn-primary px-4 py-2 rounded-lg flex items-center">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                    <span x-text="isSaving ? 'Saving...' : 'Save Changes'"></span>
                </button>
            </div>
        </div>

        <!-- Editor Container -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Script Metadata -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/50 dark:bg-zinc-800/50">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-white">Script Details</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Script Name -->
                        <div>
                            <label for="script-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Script Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                x-model="formData.name"
                                type="text"
                                id="script-name"
                                class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                                required
                            >
                        </div>

                        <!-- Framework Type -->
                        <div>
                            <label for="framework-type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Framework Type <span class="text-red-500">*</span>
                            </label>
                            <select
                                x-model="formData.framework_type"
                                id="framework-type"
                                class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                                @change="updateEditorMode"
                                required
                            >
                                <option value="selenium-python">Selenium Python</option>
                                <option value="cypress">Cypress</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-3">Editor Settings</h3>

                            <!-- Theme Selection -->
                            <div class="mb-3">
                                <label for="editor-theme" class="block text-xs text-zinc-500 dark:text-zinc-400 mb-1">
                                    Editor Theme
                                </label>
                                <select
                                    x-model="editorSettings.theme"
                                    id="editor-theme"
                                    class="w-full px-3 py-1.5 text-xs border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                                    @change="updateEditorTheme"
                                >
                                    <option value="default">Light</option>
                                    <option value="dracula">Dark</option>
                                    <option value="material">Material</option>
                                    <option value="monokai">Monokai</option>
                                </select>
                            </div>

                            <!-- Font Size -->
                            <div class="mb-3">
                                <label for="font-size" class="block text-xs text-zinc-500 dark:text-zinc-400 mb-1">
                                    Font Size
                                </label>
                                <div class="flex items-center">
                                    <button
                                        @click="changeFontSize(-1)"
                                        class="px-2 py-1 border border-zinc-300 dark:border-zinc-600 rounded-l-lg bg-zinc-50 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300"
                                    >
                                        <i data-lucide="minus" class="w-3 h-3"></i>
                                    </button>
                                    <div class="px-3 py-1 border-t border-b border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-center text-xs min-w-[40px]">
                                        <span x-text="editorSettings.fontSize"></span>px
                                    </div>
                                    <button
                                        @click="changeFontSize(1)"
                                        class="px-2 py-1 border border-zinc-300 dark:border-zinc-600 rounded-r-lg bg-zinc-50 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300"
                                    >
                                        <i data-lucide="plus" class="w-3 h-3"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Indentation -->
                            <div class="mb-3">
                                <label for="tab-size" class="block text-xs text-zinc-500 dark:text-zinc-400 mb-1">
                                    Tab Size
                                </label>
                                <select
                                    x-model.number="editorSettings.tabSize"
                                    id="tab-size"
                                    class="w-full px-3 py-1.5 text-xs border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                                    @change="updateEditorTabSize"
                                >
                                    <option value="2">2 spaces</option>
                                    <option value="4">4 spaces</option>
                                    <option value="8">8 spaces</option>
                                </select>
                            </div>

                            <!-- Options -->
                            <div class="space-y-2">
                                <label class="flex items-center text-xs text-zinc-700 dark:text-zinc-300">
                                    <input
                                        type="checkbox"
                                        x-model="editorSettings.lineWrapping"
                                        @change="updateEditorWrapping"
                                        class="rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 mr-2"
                                    >
                                    Wrap long lines
                                </label>
                                <label class="flex items-center text-xs text-zinc-700 dark:text-zinc-300">
                                    <input
                                        type="checkbox"
                                        x-model="editorSettings.lineNumbers"
                                        @change="updateEditorLineNumbers"
                                        class="rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 mr-2"
                                    >
                                    Show line numbers
                                </label>
                                <label class="flex items-center text-xs text-zinc-700 dark:text-zinc-300">
                                    <input
                                        type="checkbox"
                                        x-model="editorSettings.autoCloseBrackets"
                                        @change="updateEditorBrackets"
                                        class="rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 mr-2"
                                    >
                                    Auto-close brackets
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Editor -->
            <div class="lg:col-span-3">
                <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 overflow-hidden h-full flex flex-col">
                    <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/50 dark:bg-zinc-800/50 flex justify-between items-center">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-white">Script Code</h2>
                        <div class="flex space-x-2">
                            <button
                                @click="formatCode"
                                class="px-2 py-1 text-xs rounded-md bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors"
                                title="Format code"
                            >
                                <i data-lucide="text-quote" class="w-3 h-3 inline-block mr-1"></i>
                                Format
                            </button>
                            <button
                                @click="toggleFullscreen"
                                class="px-2 py-1 text-xs rounded-md bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors"
                                title="Toggle fullscreen"
                            >
                                <i data-lucide="maximize-2" class="w-3 h-3 inline-block"></i>
                            </button>
                        </div>
                    </div>
                    <div class="relative flex-grow">
                        <!-- Loading indicator -->
                        <div
                            x-show="isLoading"
                            class="absolute inset-0 flex items-center justify-center bg-white/80 dark:bg-zinc-800/80 z-10"
                        >
                            <div class="flex flex-col items-center">
                                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Loading editor...</span>
                            </div>
                        </div>

                        <!-- Editor container -->
                        <div id="code-editor" class="h-full min-h-[500px]"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification -->
        <div
            x-show="notification.show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2"
            class="fixed bottom-6 right-6 z-50 max-w-sm w-full p-4 rounded-xl shadow-lg border"
            :class="{
                'bg-green-50/80 border-green-200/50 dark:bg-green-900/30 dark:border-green-800/30': notification.type === 'success',
                'bg-red-50/80 border-red-200/50 dark:bg-red-900/30 dark:border-red-800/30': notification.type === 'error',
                'bg-yellow-50/80 border-yellow-200/50 dark:bg-yellow-900/30 dark:border-yellow-800/30': notification.type === 'warning'
            }"
            style="display: none;"
        >
            <div class="flex items-start">
                <div class="flex-shrink-0 w-5 h-5 mr-3">
                    <template x-if="notification.type === 'success'">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400"></i>
                    </template>
                    <template x-if="notification.type === 'error'">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 dark:text-red-400"></i>
                    </template>
                    <template x-if="notification.type === 'warning'">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-600 dark:text-yellow-400"></i>
                    </template>
                </div>
                <div>
                    <h4 class="font-medium mb-1"
                        :class="{
                            'text-green-800 dark:text-green-200': notification.type === 'success',
                            'text-red-800 dark:text-red-200': notification.type === 'error',
                            'text-yellow-800 dark:text-yellow-200': notification.type === 'warning'
                        }"
                        x-text="notification.title"></h4>
                    <p class="text-sm"
                        :class="{
                            'text-green-700/90 dark:text-green-300/90': notification.type === 'success',
                            'text-red-700/90 dark:text-red-300/90': notification.type === 'error',
                            'text-yellow-700/90 dark:text-yellow-300/90': notification.type === 'warning'
                        }"
                        x-text="notification.message"></p>
                </div>
                <button @click="hideNotification" class="ml-auto -mt-1 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/dracula.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/material.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/monokai.min.css">
<style>
    .CodeMirror {
        height: 100%;
        font-family: "Fira Code", "Menlo", "Monaco", "Consolas", monospace;
    }

    .btn-secondary {
        @apply bg-white/50 dark:bg-zinc-700/50 border border-zinc-300/70 dark:border-zinc-600/50 hover:bg-zinc-50/70 dark:hover:bg-zinc-600/50 shadow-sm text-zinc-700 dark:text-zinc-300 transition-all;
    }

    .btn-primary {
        @apply bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm hover:shadow-md transition-all;
    }

    .editor-fullscreen {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        height: 100vh;
        width: 100vw;
        z-index: 9999;
        background: white;
    }

    .dark .editor-fullscreen {
        background: #1f2937;
    }

    .editor-fullscreen .CodeMirror {
        height: calc(100vh - 60px);
    }

    .editor-fullscreen-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 20px;
        background: #f3f4f6;
        border-bottom: 1px solid #e5e7eb;
    }

    .dark .editor-fullscreen-header {
        background: #374151;
        border-color: #4b5563;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/python/python.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/addon/edit/closebrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/addon/edit/matchbrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/addon/selection/active-line.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-beautify/1.14.9/beautify.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-beautify/1.14.9/beautify-html.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('testScriptEditor', () => ({
            editor: null,
            isLoading: true,
            isSaving: false,
            isFullscreen: false,
            notification: {
                show: false,
                type: 'success',
                title: '',
                message: '',
                timeout: null
            },
            formData: {
                name: @json($testScript->name),
                framework_type: @json($testScript->framework_type),
                script_content: @json($testScript->script_content)
            },
            editorSettings: {
                theme: 'default',
                fontSize: 14,
                tabSize: 4,
                lineWrapping: true,
                lineNumbers: true,
                autoCloseBrackets: true
            },

            initEditor() {
                // Set default theme based on dark mode
                this.editorSettings.theme = document.documentElement.classList.contains('dark') ? 'dracula' : 'default';

                // Wait for DOM to be ready
                setTimeout(() => {
                    const editorContainer = document.getElementById('code-editor');
                    if (!editorContainer) return;

                    // Initialize CodeMirror
                    this.editor = CodeMirror(editorContainer, {
                        value: this.formData.script_content,
                        mode: this.getEditorMode(),
                        theme: this.editorSettings.theme,
                        lineNumbers: this.editorSettings.lineNumbers,
                        lineWrapping: this.editorSettings.lineWrapping,
                        tabSize: this.editorSettings.tabSize,
                        indentUnit: this.editorSettings.tabSize,
                        matchBrackets: true,
                        autoCloseBrackets: this.editorSettings.autoCloseBrackets,
                        styleActiveLine: true,
                        extraKeys: {
                            "Ctrl-S": (cm) => this.saveScript(),
                            "Cmd-S": (cm) => this.saveScript(),
                            "F11": (cm) => this.toggleFullscreen(),
                            "Esc": (cm) => {
                                if (this.isFullscreen) this.toggleFullscreen();
                            }
                        }
                    });

                    // Set editor font size
                    const cmElement = editorContainer.querySelector('.CodeMirror');
                    if (cmElement) {
                        cmElement.style.fontSize = `${this.editorSettings.fontSize}px`;
                    }

                    // Update content on change
                    this.editor.on('change', (cm) => {
                        this.formData.script_content = cm.getValue();
                    });

                    // Hide loading indicator
                    this.isLoading = false;

                    // Watch for dark mode changes
                    const observer = new MutationObserver((mutations) => {
                        mutations.forEach((mutation) => {
                            if (mutation.attributeName === 'class') {
                                const isDark = document.documentElement.classList.contains('dark');
                                this.editorSettings.theme = isDark ? 'dracula' : 'default';
                                this.updateEditorTheme();
                            }
                        });
                    });

                    observer.observe(document.documentElement, { attributes: true });
                }, 100);
            },

            getEditorMode() {
                switch (this.formData.framework_type) {
                    case 'cypress':
                        return 'javascript';
                    case 'selenium-python':
                    default:
                        return 'python';
                }
            },

            updateEditorMode() {
                if (!this.editor) return;
                this.editor.setOption('mode', this.getEditorMode());
            },

            updateEditorTheme() {
                if (!this.editor) return;
                this.editor.setOption('theme', this.editorSettings.theme);
            },

            updateEditorWrapping() {
                if (!this.editor) return;
                this.editor.setOption('lineWrapping', this.editorSettings.lineWrapping);
            },

            updateEditorLineNumbers() {
                if (!this.editor) return;
                this.editor.setOption('lineNumbers', this.editorSettings.lineNumbers);
            },

            updateEditorTabSize() {
                if (!this.editor) return;
                this.editor.setOption('tabSize', this.editorSettings.tabSize);
                this.editor.setOption('indentUnit', this.editorSettings.tabSize);
            },

            updateEditorBrackets() {
                if (!this.editor) return;
                this.editor.setOption('autoCloseBrackets', this.editorSettings.autoCloseBrackets);
            },

            changeFontSize(delta) {
                this.editorSettings.fontSize = Math.max(10, Math.min(24, this.editorSettings.fontSize + delta));
                const cmElement = document.querySelector('.CodeMirror');
                if (cmElement) {
                    cmElement.style.fontSize = `${this.editorSettings.fontSize}px`;
                    // Force re-render
                    this.editor?.refresh();
                }
            },

            formatCode() {
                if (!this.editor) return;

                try {
                    let formatted;
                    const code = this.editor.getValue();

                    if (this.formData.framework_type === 'cypress') {
                        // JavaScript beautifier
                        formatted = js_beautify(code, {
                            indent_size: this.editorSettings.tabSize,
                            indent_with_tabs: false,
                            space_in_empty_paren: true
                        });
                    } else {
                        // For Python and others, do a simple indentation pass
                        // This is a simple approach - for production you might want a proper Python formatter
                        formatted = code;
                    }

                    // Replace the editor content
                    this.editor.setValue(formatted);

                    this.showNotification('success', 'Code formatted', 'Code formatting applied successfully.');
                } catch (error) {
                    this.showNotification('error', 'Format Error', 'Failed to format code: ' + error.message);
                    console.error('Formatting error:', error);
                }
            },

            toggleFullscreen() {
                if (!this.editor) return;

                const editorContainer = document.getElementById('code-editor');
                const parentElement = editorContainer.closest('.lg\\:col-span-3');

                if (this.isFullscreen) {
                    // Exit fullscreen
                    document.body.style.overflow = '';
                    editorContainer.classList.remove('editor-fullscreen');

                    // Remove the fullscreen header if it exists
                    const header = document.querySelector('.editor-fullscreen-header');
                    if (header) header.remove();

                    // Move the editor back to its original position
                    if (parentElement) {
                        parentElement.appendChild(editorContainer);
                    }
                } else {
                    // Enter fullscreen
                    document.body.style.overflow = 'hidden';

                    // Create fullscreen header
                    const header = document.createElement('div');
                    header.className = 'editor-fullscreen-header';
                    header.innerHTML = `
                        <div class="text-lg font-medium text-zinc-900 dark:text-white">Editing: ${this.formData.name}</div>
                        <div class="flex space-x-3">
                            <button id="fs-format-btn" class="px-3 py-1.5 text-sm rounded-md bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors">
                                <i data-lucide="text-quote" class="w-4 h-4 inline-block mr-1"></i>
                                Format
                            </button>
                            <button id="fs-save-btn" class="px-3 py-1.5 text-sm rounded-md bg-indigo-600 hover:bg-indigo-700 text-white transition-colors">
                                <i data-lucide="save" class="w-4 h-4 inline-block mr-1"></i>
                                Save
                            </button>
                            <button id="fs-exit-btn" class="px-3 py-1.5 text-sm rounded-md bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors">
                                <i data-lucide="minimize-2" class="w-4 h-4 inline-block mr-1"></i>
                                Exit Fullscreen
                            </button>
                        </div>
                    `;

                    // Place the editor at the body level
                    editorContainer.classList.add('editor-fullscreen');
                    editorContainer.prepend(header);
                    document.body.appendChild(editorContainer);

                    // Add event listeners to the fullscreen header buttons
                    document.getElementById('fs-format-btn').addEventListener('click', () => this.formatCode());
                    document.getElementById('fs-save-btn').addEventListener('click', () => this.saveScript());
                    document.getElementById('fs-exit-btn').addEventListener('click', () => this.toggleFullscreen());

                    // Initialize Lucide icons in the fullscreen header
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons({
                            attrs: {
                                'stroke-width': 2,
                                'class': 'w-4 h-4'
                            }
                        });
                    }
                }

                this.isFullscreen = !this.isFullscreen;

                // Force CodeMirror to refresh its size
                setTimeout(() => {
                    this.editor.refresh();
                }, 100);
            },

            async saveScript() {
                if (this.isSaving) return;

                // Check if we need to get the latest content from CodeMirror
                if (this.editor) {
                    this.formData.script_content = this.editor.getValue();
                }

                // Validation
                if (!this.formData.name || !this.formData.name.trim()) {
                    this.showNotification('error', 'Validation Error', 'Script name is required.');
                    return;
                }

                if (!this.formData.script_content || !this.formData.script_content.trim()) {
                    this.showNotification('error', 'Validation Error', 'Script content cannot be empty.');
                    return;
                }

                this.isSaving = true;

                try {
                    const response = await fetch('{{ route("dashboard.projects.test-cases.scripts.update", [$project->id, $testCase->id, $testScript->id]) }}', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.formData)
                    });

                    const result = await response.json();

                    if (response.ok) {
                        this.showNotification('success', 'Success', 'Test script saved successfully.');

                        // If we're in fullscreen mode, stay there; otherwise redirect
                        if (!this.isFullscreen) {
                            setTimeout(() => {
                                window.location.href = '{{ route("dashboard.projects.test-cases.scripts.show", [$project->id, $testCase->id, $testScript->id]) }}';
                            }, 1000);
                        }
                    } else {
                        throw new Error(result.message || 'Failed to save test script.');
                    }
                } catch (error) {
                    console.error('Save error:', error);
                    this.showNotification('error', 'Error', error.message || 'An error occurred while saving.');
                } finally {
                    this.isSaving = false;
                }
            },

            showNotification(type, title, message) {
                // Clear any existing timeout
                if (this.notification.timeout) {
                    clearTimeout(this.notification.timeout);
                }

                // Update notification data
                this.notification.type = type;
                this.notification.title = title;
                this.notification.message = message;
                this.notification.show = true;

                // Auto-hide after delay
                this.notification.timeout = setTimeout(() => {
                    this.hideNotification();
                }, 5000);
            },

            hideNotification() {
                this.notification.show = false;
            }
        }));
    });
</script>
@endpush
