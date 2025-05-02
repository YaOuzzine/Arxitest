@props([
    'modalId' => 'code-editor-modal', // Unique ID for the modal
    'title' => 'Edit Code', // Modal title
    'buttonIcon' => 'save', // Icon for action button
    'buttonText' => 'Save Changes', // Text for action button
    'editorType' => 'code', // 'code' or 'data'
    'extraFields' => [], // Additional form fields
    'initialMode' => 'javascript', // Initial editor mode
    'type' => 'code', // For differentiation in CSS/JS
])

<!-- Code Editor Modal -->
<div x-cloak x-show="show{{ ucfirst($modalId) }}" @keydown.escape.window="show{{ ucfirst($modalId) }} = false"
    class="fixed inset-0 overflow-y-auto z-50" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" x-data="codeEditorModal({
        type: '{{ $type }}',
        initialMode: '{{ $initialMode }}',
        saveCallback: typeof {{ $attributes->get('x-on:save') ?? 'null' }} === 'function' ?
            {{ $attributes->get('x-on:save') ?? 'null' }} : null
    })">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-zinc-900/70 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
        @click="show{{ ucfirst($modalId) }} = false"></div>

    <!-- Modal Panel -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative w-full max-w-6xl bg-white dark:bg-zinc-800 rounded-xl shadow-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-8">

            <!-- Header -->
            <div
                class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-zinc-800 dark:to-zinc-900">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                        <div class="flex items-center gap-2">
                            <i x-bind:data-lucide="iconForType" class="w-5 h-5" x-bind:class="colorForType"></i>
                            <span>{{ $title }}</span>
                        </div>
                    </h3>
                    <button @click="show{{ ucfirst($modalId) }} = false"
                        class="rounded-md p-1 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="flex flex-col h-[calc(100vh-12rem)] max-h-[800px]">
                <!-- Settings Fields -->
                <div class="p-4 bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Name Field -->
                        <div>
                            <label for="{{ $modalId }}-name"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="{{ $modalId }}-name" x-model="formData.name"
                                class="form-input w-full rounded-lg h-10 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-zinc-600"
                                placeholder="Enter a name">
                        </div>

                        <!-- Format/Framework Field -->
                        <div>
                            <label for="{{ $modalId }}-format"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $editorType === 'code' ? 'Framework' : 'Format' }} <span
                                    class="text-red-500">*</span>
                            </label>
                            <select id="{{ $modalId }}-format" x-model="formData.format"
                                @change="updateEditorMode"
                                class="form-select w-full rounded-lg h-10 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-zinc-600">

                                @if ($editorType === 'code')
                                    <option value="selenium-python">Selenium (Python)</option>
                                    <option value="cypress">Cypress (JavaScript)</option>
                                    <option value="other">Other Framework</option>
                                @else
                                    <option value="json">JSON</option>
                                    <option value="csv">CSV</option>
                                    <option value="xml">XML</option>
                                    <option value="plain">Plain Text</option>
                                    <option value="other">Other Format</option>
                                @endif
                            </select>
                        </div>

                        <!-- Slot for extra fields -->
                        {{ $slot }}
                    </div>
                </div>

                <!-- Code Editor Area -->
                <div class="flex-1 flex flex-col overflow-hidden">
                    <!-- Editor Toolbar -->
                    <div
                        class="flex items-center px-2 py-1.5 bg-gray-100 dark:bg-zinc-700 border-b border-gray-300 dark:border-zinc-600">
                        <div class="flex items-center space-x-1">
                            <button @click="editorAction('undo')"
                                class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors"
                                title="Undo">
                                <i data-lucide="undo-2" class="w-4 h-4 text-gray-700 dark:text-gray-300"></i>
                            </button>
                            <button @click="editorAction('redo')"
                                class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors"
                                title="Redo">
                                <i data-lucide="redo-2" class="w-4 h-4 text-gray-700 dark:text-gray-300"></i>
                            </button>

                            <div class="mx-1 h-5 border-r border-gray-300 dark:border-zinc-500"></div>

                            <button @click="editorAction('indent')"
                                class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors"
                                title="Indent">
                                <i data-lucide="indent" class="w-4 h-4 text-gray-700 dark:text-gray-300"></i>
                            </button>
                            <button @click="editorAction('outdent')"
                                class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors"
                                title="Outdent">
                                <i data-lucide="outdent" class="w-4 h-4 text-gray-700 dark:text-gray-300"></i>
                            </button>

                            <div class="mx-1 h-5 border-r border-gray-300 dark:border-zinc-500"></div>

                            <button @click="editorAction('comment')"
                                class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors"
                                title="Toggle Comment">
                                <i data-lucide="message-square" class="w-4 h-4 text-gray-700 dark:text-gray-300"></i>
                            </button>

                            @if ($editorType === 'code')
                                <button @click="editorAction('fold')"
                                    class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors"
                                    title="Fold Code">
                                    <i data-lucide="chevrons-down" class="w-4 h-4 text-gray-700 dark:text-gray-300"></i>
                                </button>
                            @endif

                            <div class="mx-1 h-5 border-r border-gray-300 dark:border-zinc-500"></div>

                            <button @click="editorAction('search')"
                                class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors"
                                title="Search">
                                <i data-lucide="search" class="w-4 h-4 text-gray-700 dark:text-gray-300"></i>
                            </button>
                            <button @click="editorAction('replace')"
                                class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors"
                                title="Replace">
                                <i data-lucide="replace" class="w-4 h-4 text-gray-700 dark:text-gray-300"></i>
                            </button>

                            @if ($editorType === 'data')
                                <div class="mx-1 h-5 border-r border-gray-300 dark:border-zinc-500"></div>

                                <button @click="editorAction('format')"
                                    class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors"
                                    title="Format Code">
                                    <i data-lucide="align-justify"
                                        class="w-4 h-4 text-gray-700 dark:text-gray-300"></i>
                                </button>
                                <button @click="editorAction('validate')"
                                    class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors"
                                    title="Validate">
                                    <i data-lucide="check-circle"
                                        class="w-4 h-4 text-gray-700 dark:text-gray-300"></i>
                                </button>
                            @endif
                        </div>

                        <div class="ml-auto flex items-center space-x-2">
                            <span class="text-xs text-gray-600 dark:text-gray-400">
                                <span class="hidden sm:inline">Line:</span> <span
                                    x-text="cursorPosition.line">1</span>,
                                <span class="hidden sm:inline">Col:</span> <span
                                    x-text="cursorPosition.column">1</span>
                            </span>
                            <button @click="toggleEditorTheme()"
                                class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors"
                                title="Toggle Theme">
                                <i data-lucide="sun" class="w-4 h-4 text-gray-700 dark:text-gray-300"
                                    x-show="!editorDarkMode"></i>
                                <i data-lucide="moon" class="w-4 h-4 text-gray-700 dark:text-gray-300"
                                    x-show="editorDarkMode"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Code Editor (CodeMirror) -->
                    <div class="flex-1 relative overflow-hidden">
                        <div id="{{ $modalId }}-editor" class="editor-container absolute inset-0 w-full h-full">
                        </div>
                    </div>
                </div>

                <!-- Footer with Save/Cancel -->
                <div class="p-4 bg-gray-50 dark:bg-zinc-800 border-t border-gray-200 dark:border-zinc-700">
                    <div class="flex justify-end gap-3">
                        <button @click="show{{ ucfirst($modalId) }} = false"
                            class="px-4 py-2 bg-white dark:bg-zinc-700 text-gray-700 dark:text-gray-200 rounded-lg border border-gray-300 dark:border-zinc-600 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            Cancel
                        </button>
                        <button @click="saveChanges()"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                            :disabled="!isValid">
                            <i :data-lucide="'{{ $buttonIcon }}'" class="w-4 h-4 mr-1.5"></i> {{ $buttonText }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <!-- CodeMirror CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/codemirror.min.css" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/theme/material-darker.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/theme/eclipse.min.css" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/hint/show-hint.min.css" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/fold/foldgutter.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/dialog/dialog.min.css" />
    <style>
        /* Modern CodeMirror styling */
        .CodeMirror {
            height: 100% !important;
            font-family: 'JetBrains Mono', 'Fira Code', 'SF Mono', Menlo, Monaco, 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            border-radius: 0;
        }

        .editor-container .CodeMirror {
            height: 100% !important;
        }

        /* Material theme adjustments */
        .cm-s-material-darker.CodeMirror {
            background-color: #212121;
        }

        .cm-s-material-darker .CodeMirror-gutters {
            background-color: #1a1a1a;
            border-right: 1px solid #333;
        }

        /* Better selection highlighting */
        .CodeMirror-focused .CodeMirror-selected {
            background-color: rgba(66, 150, 255, 0.3) !important;
        }

        .dark .CodeMirror-focused .CodeMirror-selected {
            background-color: rgba(79, 134, 198, 0.4) !important;
        }

        /* Better scrollbars */
        .CodeMirror-vscrollbar,
        .CodeMirror-hscrollbar {
            width: 10px;
        }

        .CodeMirror-vscrollbar::-webkit-scrollbar,
        .CodeMirror-hscrollbar::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        .CodeMirror-vscrollbar::-webkit-scrollbar-thumb,
        .CodeMirror-hscrollbar::-webkit-scrollbar-thumb {
            background: rgba(100, 100, 100, 0.5);
            border-radius: 5px;
        }

        .dark .CodeMirror-vscrollbar::-webkit-scrollbar-thumb,
        .dark .CodeMirror-hscrollbar::-webkit-scrollbar-thumb {
            background: rgba(80, 80, 80, 0.5);
        }

        /* Improved cursor visibility */
        .CodeMirror-cursor {
            border-left: 2px solid #1976D2 !important;
        }

        .dark .CodeMirror-cursor {
            border-left: 2px solid #90CAF9 !important;
        }

        /* Better active line highlighting */
        .CodeMirror-activeline-background {
            background: rgba(0, 0, 0, 0.05) !important;
        }

        .dark .CodeMirror-activeline-background {
            background: rgba(255, 255, 255, 0.05) !important;
        }

        /* Improved dialog styling for search/replace */
        .CodeMirror-dialog {
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
            padding: 8px;
        }

        .dark .CodeMirror-dialog {
            border-bottom: 1px solid #4b5563;
            background: #374151;
            color: #e5e7eb;
        }

        .CodeMirror-dialog input {
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 4px 8px;
        }

        .dark .CodeMirror-dialog input {
            border: 1px solid #6b7280;
            background: #1f2937;
            color: #e5e7eb;
        }
    </style>
@endpush

@push('scripts')
    <!-- CodeMirror JS and extensions -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/python/python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/edit/matchbrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/edit/closebrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/fold/foldcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/fold/foldgutter.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/fold/brace-fold.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/fold/indent-fold.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/fold/comment-fold.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/hint/show-hint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/hint/anyword-hint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/search/search.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/search/searchcursor.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/dialog/dialog.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/comment/comment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/selection/active-line.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/display/autorefresh.min.js"></script>

    <script>
        // Define the Alpine.js component for code editor modal
        document.addEventListener('alpine:init', () => {
            Alpine.data('codeEditorModal', ({
                type,
                initialMode,
                saveCallback
            }) => ({
                editor: null,
                editorDarkMode: document.documentElement.classList.contains('dark'),
                cursorPosition: {
                    line: 1,
                    column: 1
                },
                formData: {
                    id: null,
                    name: '',
                    format: initialMode || (type === 'code' ? 'selenium-python' : 'json'),
                    content: '',
                    // Additional fields that might be needed for specific types
                    usage_context: '',
                    is_sensitive: false
                },

                // Computed properties for UI elements
                get iconForType() {
                    return type === 'code' ? 'code' : 'database';
                },

                get colorForType() {
                    return type === 'code' ?
                        'text-indigo-600 dark:text-indigo-400' :
                        'text-teal-600 dark:text-teal-400';
                },

                get isValid() {
                    const requiredFields = ['name', 'content'];

                    // Add data-specific validation
                    if (type === 'data' && !this.formData.usage_context) {
                        return false;
                    }

                    return requiredFields.every(field => !!this.formData[field]);
                },

                // Initialize the component
                init() {

                    // Initialize form data from Alpine store if available
                    if (this.$store.editorData && this.$store.editorData.id) {
                        Object.keys(this.$store.editorData).forEach(key => {
                            if (this.formData.hasOwnProperty(key)) {
                                this.formData[key] = this.$store.editorData[key];
                            }
                        });
                    }
                    this.$nextTick(() => {
                        this.initializeEditor();

                        // Listen for events to update the editor data
                        this.$watch('formData.format', () => {
                            this.updateEditorMode();
                        });

                        // Refresh when modal is shown (important for CodeMirror)
                        this.$watch(
                            `show${this.$el.id.split('-')[0][0].toUpperCase() + this.$el.id.split('-')[0].slice(1)}`,
                            (value) => {
                                if (value && this.editor) {
                                    setTimeout(() => {
                                        this.editor.refresh();
                                    }, 10);
                                }
                            });
                    });
                },

                // Initialize the CodeMirror editor
                initializeEditor() {
                    const editorContainer = document.getElementById(
                        `${this.$el.id.split(' ')[0]}-editor`);
                    if (!editorContainer) return;

                    this.editor = CodeMirror(editorContainer, {
                        value: this.formData.content || '',
                        mode: this.getEditorMode(),
                        theme: this.editorDarkMode ? 'material-darker' : 'eclipse',
                        lineNumbers: true,
                        lineWrapping: true,
                        autoCloseBrackets: true,
                        matchBrackets: true,
                        styleActiveLine: true,
                        foldGutter: true,
                        autoRefresh: true,
                        gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
                        tabSize: 4,
                        indentUnit: 4,
                        extraKeys: {
                            "Ctrl-Space": "autocomplete",
                            "Tab": function(cm) {
                                if (cm.somethingSelected()) {
                                    cm.indentSelection("add");
                                } else {
                                    const spaces = Array(cm.getOption("indentUnit") + 1)
                                        .join(" ");
                                    cm.replaceSelection(spaces, "end", "+input");
                                }
                            }
                        }
                    });

                    // Set up events
                    this.editor.on("cursorActivity", () => {
                        const cursor = this.editor.getCursor();
                        this.cursorPosition = {
                            line: cursor.line + 1,
                            column: cursor.ch + 1
                        };
                    });

                    this.editor.on("change", () => {
                        this.formData.content = this.editor.getValue();
                    });

                    // Set editor content if formData.content already exists
                    if (this.formData.content) {
                        this.editor.setValue(this.formData.content);
                    }

                    // Important: set focus and refresh
                    this.editor.focus();
                    setTimeout(() => {
                        this.editor.refresh();
                    }, 50);
                },

                // Update the editor mode based on the selected format
                updateEditorMode() {
                    if (!this.editor) return;
                    this.editor.setOption('mode', this.getEditorMode());
                },

                // Get the appropriate CodeMirror mode for the current format
                getEditorMode() {
                    if (type === 'code') {
                        return {
                            'selenium-python': 'python',
                            'cypress': 'javascript',
                            'other': 'text/plain'
                        } [this.formData.format] || 'text/plain';
                    } else {
                        return {
                            'json': 'application/json',
                            'xml': 'application/xml',
                            'html': 'text/html',
                            'css': 'text/css',
                            'csv': 'text/plain',
                            'plain': 'text/plain',
                            'other': 'text/plain'
                        } [this.formData.format] || 'text/plain';
                    }
                },

                // Toggle the editor theme
                toggleEditorTheme() {
                    this.editorDarkMode = !this.editorDarkMode;
                    if (this.editor) {
                        this.editor.setOption('theme', this.editorDarkMode ? 'material-darker' :
                            'eclipse');
                    }
                },

                // Save changes
                saveChanges() {
                    if (!this.isValid) return;

                    // Call the provided save callback if available
                    if (typeof saveCallback === 'function') {
                        saveCallback(this.formData);
                    } else {
                        // Default behavior: dispatch a save event for the parent to handle
                        this.$dispatch('editor:save', this.formData);
                    }

                    // Close the modal
                    this.$el.dispatchEvent(new CustomEvent('close'));
                },

                // Format content (primarily for data)
                formatContent() {
                    if (!this.editor) return;

                    try {
                        const format = this.formData.format;
                        const content = this.editor.getValue();
                        let formattedContent = content;

                        if (format === 'json') {
                            // Format JSON
                            const jsonObj = JSON.parse(content);
                            formattedContent = JSON.stringify(jsonObj, null, 2);
                        } else if (format === 'xml') {
                            // Basic XML formatting
                            formattedContent = content
                                .replace(/><(?!\/)/g, '>\n<')
                                .replace(/></g, '>\n<')
                                .replace(/>\s+</g, '>\n<');
                        }

                        this.editor.setValue(formattedContent);
                        this.notifyUser('Content formatted successfully', 'success');
                    } catch (error) {
                        this.notifyUser(`Formatting failed: ${error.message}`, 'error');
                    }
                },

                // Validate content (primarily for data)
                validateContent() {
                    if (!this.editor) return;

                    try {
                        const format = this.formData.format;
                        const content = this.editor.getValue();

                        if (format === 'json') {
                            // Validate JSON
                            JSON.parse(content);
                            this.notifyUser('JSON is valid!', 'success');
                        } else if (format === 'xml') {
                            // Basic XML validation
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(content, 'text/xml');
                            const errorNode = doc.querySelector('parsererror');
                            if (errorNode) {
                                throw new Error('Invalid XML');
                            }
                            this.notifyUser('XML is valid!', 'success');
                        } else {
                            this.notifyUser(
                                `Validation for ${format.toUpperCase()} format is not supported.`,
                                'info');
                        }
                    } catch (error) {
                        this.notifyUser(`Validation failed: ${error.message}`, 'error');
                    }
                },

                // Helper to send notifications
                notifyUser(message, type = 'info') {
                    // Dispatch a notification event for the parent to handle
                    this.$dispatch('notify', {
                        message,
                        type
                    });
                },

                // Handle editor toolbar actions
                editorAction(action) {
                    if (!this.editor) return;

                    switch (action) {
                        case 'undo':
                            this.editor.undo();
                            break;
                        case 'redo':
                            this.editor.redo();
                            break;
                        case 'indent':
                            this.editor.execCommand('indentMore');
                            break;
                        case 'outdent':
                            this.editor.execCommand('indentLess');
                            break;
                        case 'comment':
                            this.editor.execCommand('toggleComment');
                            break;
                        case 'fold':
                            this.editor.execCommand('foldAll');
                            break;
                        case 'search':
                            this.editor.execCommand('find');
                            break;
                        case 'replace':
                            this.editor.execCommand('replace');
                            break;
                        case 'format':
                            this.formatContent();
                            break;
                        case 'validate':
                            this.validateContent();
                            break;
                    }
                }
            }));
        });
    </script>
@endpush
