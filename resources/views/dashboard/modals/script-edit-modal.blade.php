```php
{{-- resources/views/dashboard/test-cases/modals/script-edit-modal.blade.php --}}

<!-- Script Edit Modal -->
<div x-cloak x-show="showScriptEditModal" @keydown.escape.window="showScriptEditModal = false"
    class="fixed inset-0 overflow-y-auto z-50" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-zinc-900/70 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
        @click="showScriptEditModal = false"></div>

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
                        <i data-lucide="code" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i>
                        <span>Edit Test Script</span>
                    </div>
                </h3>
                <button @click="showScriptEditModal = false"
                    class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Content -->
            <div class="flex flex-col h-[calc(100vh-12rem)] max-h-[800px]">
                <!-- Script Settings -->
                <div class="p-4 bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="script-edit-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Script Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="script-edit-name" x-model="editingScript.name"
                                class="form-input w-full rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600"
                                placeholder="Enter a name for this script">
                        </div>
                        <div>
                            <label for="script-edit-framework" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Framework <span class="text-red-500">*</span>
                            </label>
                            <select id="script-edit-framework" x-model="editingScript.framework_type"
                                class="form-select w-full rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border-zinc-300 dark:border-zinc-600">
                                <option value="selenium-python">Selenium (Python)</option>
                                <option value="cypress">Cypress (JavaScript)</option>
                                <option value="other">Other Framework</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Code Editor Area -->
                <div class="flex-1 flex flex-col overflow-hidden">
                    <!-- Editor Toolbar -->
                    <div class="px-2 py-1 bg-zinc-200 dark:bg-zinc-700 border-b border-zinc-300 dark:border-zinc-600 flex items-center">
                        <div class="flex items-center mr-4">
                            <button @click="editorAction('undo')" class="p-1 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600" title="Undo">
                                <i data-lucide="undo" class="w-4 h-4 text-zinc-700 dark:text-zinc-300"></i>
                            </button>
                            <button @click="editorAction('redo')" class="p-1 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600" title="Redo">
                                <i data-lucide="redo" class="w-4 h-4 text-zinc-700 dark:text-zinc-300"></i>
                            </button>
                        </div>
                        <div class="h-4 border-r border-zinc-400 dark:border-zinc-500 mx-1"></div>
                        <div class="flex items-center mr-4">
                            <button @click="editorAction('indent')" class="p-1 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600" title="Indent">
                                <i data-lucide="indent" class="w-4 h-4 text-zinc-700 dark:text-zinc-300"></i>
                            </button>
                            <button @click="editorAction('outdent')" class="p-1 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600" title="Outdent">
                                <i data-lucide="outdent" class="w-4 h-4 text-zinc-700 dark:text-zinc-300"></i>
                            </button>
                        </div>
                        <div class="h-4 border-r border-zinc-400 dark:border-zinc-500 mx-1"></div>
                        <div class="flex items-center mr-4">
                            <button @click="editorAction('comment')" class="p-1 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600" title="Toggle Comment">
                                <i data-lucide="message-square" class="w-4 h-4 text-zinc-700 dark:text-zinc-300"></i>
                            </button>
                            <button @click="editorAction('fold')" class="p-1 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600" title="Fold Code">
                                <i data-lucide="chevrons-down" class="w-4 h-4 text-zinc-700 dark:text-zinc-300"></i>
                            </button>
                        </div>
                        <div class="h-4 border-r border-zinc-400 dark:border-zinc-500 mx-1"></div>
                        <div class="flex items-center">
                            <button @click="editorAction('search')" class="p-1 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600" title="Search">
                                <i data-lucide="search" class="w-4 h-4 text-zinc-700 dark:text-zinc-300"></i>
                            </button>
                            <button @click="editorAction('replace')" class="p-1 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600" title="Replace">
                                <i data-lucide="replace" class="w-4 h-4 text-zinc-700 dark:text-zinc-300"></i>
                            </button>
                        </div>
                        <div class="ml-auto flex items-center">
                            <span class="text-xs text-zinc-600 dark:text-zinc-400 mr-2">
                                Line: <span x-text="editorCursorPosition.line">1</span>,
                                Col: <span x-text="editorCursorPosition.column">1</span>
                            </span>
                            <button @click="toggleEditorTheme()" class="p-1 rounded hover:bg-zinc-300 dark:hover:bg-zinc-600" title="Toggle Theme">
                                <i data-lucide="sun" class="w-4 h-4 text-zinc-700 dark:text-zinc-300" x-show="!editorDarkMode"></i>
                                <i data-lucide="moon" class="w-4 h-4 text-zinc-700 dark:text-zinc-300" x-show="editorDarkMode"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Code Editor (CodeMirror) -->
                    <div class="flex-1 relative overflow-hidden bg-white dark:bg-zinc-900">
                        <div id="script-code-editor" class="absolute inset-0 w-full h-full font-mono text-sm"></div>
                    </div>
                </div>

                <!-- Footer with Save/Cancel -->
                <div class="p-4 bg-white dark:bg-zinc-800 border-t border-zinc-200 dark:border-zinc-700">
                    <div class="flex justify-end gap-3">
                        <button @click="showScriptEditModal = false" class="btn-secondary">
                            Cancel
                        </button>
                        <button @click="saveEditedScript()"
                            class="btn-primary"
                            :disabled="!editingScript.name || !editingScript.script_content">
                            <i data-lucide="save" class="w-4 h-4 mr-1.5"></i> Save Changes
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/theme/vscode-dark.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/theme/eclipse.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/hint/show-hint.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/fold/foldgutter.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/dialog/dialog.min.css" />
<style>
    /* Custom styles for CodeMirror to match VS Code aesthetic */
    .CodeMirror {
        height: 100%;
        font-family: 'JetBrains Mono', Menlo, Monaco, 'Courier New', monospace;
        font-size: 14px;
        line-height: 1.5;
    }

    .cm-s-vscode-dark .CodeMirror-gutters {
        background-color: #1e1e1e;
        border-right: 1px solid #333;
    }

    .cm-s-vscode-dark .CodeMirror-linenumber {
        color: #858585;
    }

    .CodeMirror-focused .CodeMirror-selected {
        background-color: rgba(33, 150, 243, 0.3) !important;
    }

    .CodeMirror-hint {
        font-family: 'JetBrains Mono', Menlo, Monaco, 'Courier New', monospace;
        font-size: 13px;
    }
</style>
@endpush

@push('scripts')
<!-- CodeMirror JS and extensions -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/python/python.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/xml/xml.min.js"></script>
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
@endpush
```
