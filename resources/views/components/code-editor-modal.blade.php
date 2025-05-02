@props([
    'modalId' => 'code-editor-modal',
    'title' => 'Edit Code',
    'buttonIcon' => 'save',
    'buttonText' => 'Save Changes',
    'editorType' => 'code',
    'initialMode' => 'javascript',
    'type' => 'code',
])

<!-- Code Editor Modal -->
<div x-cloak x-show="show{{ ucfirst($modalId) }}" @keydown.escape.window="show{{ ucfirst($modalId) }} = false"
    class="fixed inset-0 overflow-y-auto z-50" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" x-data="monacoEditorModal({
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
                                @change="updateEditorLanguage()"
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

                <!-- Editor Toolbar -->
                <div
                    class="flex items-center px-2 py-1.5 bg-gray-100 dark:bg-zinc-700 border-b border-gray-300 dark:border-zinc-600">
                    <div class="flex items-center space-x-2">
                        <!-- Theme toggle -->
                        <button @click="toggleEditorTheme()"
                            class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors"
                            title="Toggle Theme">
                            <i data-lucide="sun" class="w-4 h-4 text-gray-700 dark:text-gray-300"
                                x-show="!editorDarkMode"></i>
                            <i data-lucide="moon" class="w-4 h-4 text-gray-700 dark:text-gray-300"
                                x-show="editorDarkMode"></i>
                        </button>

                        <div class="h-5 border-r border-gray-300 dark:border-zinc-500"></div>

                        <!-- Format button for data -->
                        <template x-if="type === 'data'">
                            <button @click="formatContent()"
                                class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors"
                                title="Format Code">
                                <i data-lucide="align-justify" class="w-4 h-4 text-gray-700 dark:text-gray-300"></i>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Monaco Editor Container -->
                <div class="flex-1 relative overflow-hidden">
                    <div id="{{ $modalId }}-monaco-container" class="absolute inset-0 w-full h-full"></div>
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
    <style>
        /* Monaco editor container sizing */
        .monaco-editor {
            width: 100%;
            height: 100%;
        }

        /* Fix for editor height in flex container */
        .editor-container {
            flex-grow: 1;
        }

    </style>
@endpush

@push('scripts')
    <script>
        // Load Monaco Editor only if not already loaded
        if (typeof monaco === 'undefined') {
            // Load Monaco editor from CDN
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/monaco-editor@0.43.0/min/vs/loader.js';
            script.async = true;
            document.head.appendChild(script);

            script.onload = function() {
                require.config({
                    paths: {
                        'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@0.43.0/min/vs'
                    }
                });
            };
        }

        // Define the Alpine.js component for Monaco editor modal
        document.addEventListener('alpine:init', () => {
            Alpine.data('monacoEditorModal', ({
                type,
                initialMode,
                saveCallback
            }) => ({
                editor: null,
                editorDarkMode: document.documentElement.classList.contains('dark'),
                editorInitialized: false,

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

                    // Watch for modal visibility
                    this.$watch(
                        `show${this.$el.id.split('-')[0][0].toUpperCase() + this.$el.id.split('-')[0].slice(1)}`,
                        (value) => {
                            if (value) {
                                this.initMonacoEditor();
                            }
                        });
                },

                // Initialize Monaco Editor
                initMonacoEditor() {
                    const containerId = `${this.$el.id.split(' ')[0]}-monaco-container`;
                    const container = document.getElementById(containerId);

                    if (!container) return;

                    if (this.editorInitialized) {
                        // If editor already initialized, just update content and language
                        this.updateEditorContent();
                        this.updateEditorLanguage();
                        return;
                    }

                    // Initialize Monaco Editor
                    require(['vs/editor/editor.main'], () => {
                        // Set editor theme based on current mode
                        monaco.editor.defineTheme('myDarkTheme', {
                            base: 'vs-dark',
                            inherit: true,
                            rules: [],
                            colors: {
                                'editor.background': '#1e1e1e',
                            }
                        });

                        // Create the editor
                        this.editor = monaco.editor.create(container, {
                            value: this.formData.content || '',
                            language: this.getMonacoLanguage(),
                            theme: this.editorDarkMode ? 'myDarkTheme' : 'vs',
                            automaticLayout: true,
                            minimap: {
                                enabled: true
                            },
                            scrollBeyondLastLine: false,
                            lineNumbers: 'on',
                            renderLineHighlight: 'all',
                            tabSize: 4,
                            fontFamily: "'JetBrains Mono', 'Fira Code', monospace",
                            fontSize: 14,
                            formatOnType: true,
                            formatOnPaste: true,
                            autoIndent: 'full',
                            "bracketPairColorization.enabled": true,
                        });

                        // Set up content change listener
                        this.editor.onDidChangeModelContent(() => {
                            this.formData.content = this.editor.getValue();
                        });

                        // Mark as initialized
                        this.editorInitialized = true;

                        // Make editor resize properly
                        window.addEventListener('resize', () => {
                            if (this.editor) {
                                this.editor.layout();
                            }
                        });

                        // Focus editor
                        setTimeout(() => {
                            if (this.editor) {
                                this.editor.focus();
                            }
                        }, 100);
                    });
                },

                // Update editor content when formData changes
                updateEditorContent() {
                    if (this.editor) {
                        this.editor.setValue(this.formData.content || '');
                    }
                },

                // Update editor language when format changes
                updateEditorLanguage() {
                    if (this.editor) {
                        monaco.editor.setModelLanguage(this.editor.getModel(), this
                    .getMonacoLanguage());
                    }
                },

                // Get the appropriate Monaco language for the current format
                getMonacoLanguage() {
                    if (type === 'code') {
                        return {
                            'selenium-python': 'python',
                            'cypress': 'javascript',
                            'other': 'plaintext'
                        } [this.formData.format] || 'plaintext';
                    } else {
                        return {
                            'json': 'json',
                            'xml': 'xml',
                            'html': 'html',
                            'css': 'css',
                            'csv': 'plaintext',
                            'plain': 'plaintext',
                            'other': 'plaintext'
                        } [this.formData.format] || 'plaintext';
                    }
                },

                // Toggle editor theme
                toggleEditorTheme() {
                    this.editorDarkMode = !this.editorDarkMode;
                    if (this.editor) {
                        monaco.editor.setTheme(this.editorDarkMode ? 'myDarkTheme' : 'vs');
                    }
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
                            this.editor.setValue(formattedContent);
                            this.editor.getAction('editor.action.formatDocument').run();
                        } else if (format === 'xml') {
                            // For XML, use Monaco's built-in formatter
                            this.editor.getAction('editor.action.formatDocument').run();
                        } else {
                            // For other formats, try the built-in formatter
                            this.editor.getAction('editor.action.formatDocument').run();
                        }

                        this.notifyUser('Content formatted successfully', 'success');
                    } catch (error) {
                        this.notifyUser(`Formatting failed: ${error.message}`, 'error');
                    }
                },

                // Save the changes
                saveChanges() {
                    if (!this.isValid) return;

                    // Make sure content is up-to-date
                    if (this.editor) {
                        this.formData.content = this.editor.getValue();
                    }

                    // Call the provided save callback if available
                    if (typeof saveCallback === 'function') {
                        saveCallback(this.formData);
                    } else {
                        // Default behavior: dispatch a save event
                        this.$dispatch('editor:save', this.formData);
                    }

                    // Close the modal
                    this.$el.dispatchEvent(new CustomEvent('close'));
                    eval(
                        `show${this.$el.id.split('-')[0][0].toUpperCase() + this.$el.id.split('-')[0].slice(1)} = false`);
                },

                // Helper to send notifications
                notifyUser(message, type = 'info') {
                    // Dispatch a notification event
                    this.$dispatch('notify', {
                        message,
                        type
                    });
                }
            }));
        });
    </script>
@endpush
