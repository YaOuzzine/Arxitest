{{-- resources/views/dashboard/test-scripts/edit.blade.php --}}
@extends('layouts.dashboard')

@section('title', "Edit Test Script - {$testScript->name}")

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.show', $project->id) }}"
            class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">{{ $project->name }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}"
            class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">{{ $testCase->title }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id]) }}"
            class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">Test
            Scripts</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.scripts.show', [$project->id, $testCase->id, $testScript->id]) }}"
            class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">{{ $testScript->name }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Edit</span>
    </li>
@endsection

@section('content')
    <div class="h-full space-y-6" x-data="scriptEditor({
        scriptId: '{{ $testScript->id }}',
        initialName: '{{ addslashes($testScript->name) }}',
        initialFramework: '{{ $testScript->framework_type }}',
        initialContent: {{ json_encode($testScript->script_content) }},
        updateUrl: '{{ route('dashboard.projects.test-cases.scripts.update', [$project->id, $testCase->id, $testScript->id]) }}',
        csrfToken: '{{ csrf_token() }}',
        testCaseSteps: {{ json_encode($testCase->steps) }},
        testCaseExpectedResults: {{ json_encode($testCase->expected_results) }}
    })">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1
                    class="text-2xl font-bold bg-gradient-to-r from-zinc-800 dark:from-zinc-100 to-zinc-600 dark:to-zinc-300 bg-clip-text text-transparent tracking-tight">
                    Edit Test Script
                </h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Modify the test script for {{ $testCase->title }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('dashboard.projects.test-cases.scripts.show', [$project->id, $testCase->id, $testScript->id]) }}"
                    class="btn-secondary px-4 py-2 rounded-lg flex items-center">
                    <i data-lucide="x" class="w-4 h-4 mr-2"></i>
                    Cancel
                </a>
                <button @click="saveScript" type="button" class="btn-primary px-5 py-2 rounded-lg flex items-center"
                    :disabled="isSubmitting">
                    <template x-if="!isSubmitting">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                    </template>
                    <template x-if="isSubmitting">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </template>
                    <span x-text="isSubmitting ? 'Saving...' : 'Save Changes'"></span>
                </button>
            </div>
        </div>

        <!-- Form Container -->
        <div
            class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 overflow-hidden">
            <!-- Basic Details Section -->
            <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/30 dark:bg-zinc-800/30">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Script Name -->
                    <div>
                        <label for="script-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Script Name <span class="text-red-500">*</span>
                        </label>
                        <input x-model="name" type="text" id="script-name" class="w-full px-4 py-2.5 rounded-lg"
                            required>
                    </div>

                    <!-- Framework Type -->
                    <div>
                        <label for="framework-type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Framework Type <span class="text-red-500">*</span>
                        </label>
                        <select x-model="framework" id="framework-type" class="w-full px-4 py-2.5 rounded-lg"
                            @change="updateMode" required>
                            <option value="">Select Framework</option>
                            <option value="selenium-python">Selenium Python</option>
                            <option value="cypress">Cypress</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Editor Toolbar -->
            <div
                class="px-6 py-2 bg-zinc-50 dark:bg-zinc-900/50 flex flex-wrap gap-2 items-center border-b border-zinc-200/50 dark:border-zinc-700/50">
                <button @click="toggleWordWrap"
                    class="p-1.5 text-sm rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors"
                    :class="wordWrap ? 'bg-zinc-200 dark:bg-zinc-700' : ''">
                    <i data-lucide="wrap-text" class="w-4 h-4"></i>
                    <span class="sr-only">Toggle Word Wrap</span>
                </button>

                <button @click="indentCode"
                    class="p-1.5 text-sm rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                    <i data-lucide="indent" class="w-4 h-4"></i>
                    <span class="sr-only">Indent</span>
                </button>

                <button @click="outdentCode"
                    class="p-1.5 text-sm rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                    <i data-lucide="outdent" class="w-4 h-4"></i>
                    <span class="sr-only">Outdent</span>
                </button>

                <div class="h-4 border-r border-zinc-300 dark:border-zinc-600 mx-1"></div>

                <button @click="insertSnippet('assert')"
                    class="p-1.5 text-sm rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span class="ml-1">Insert Assertion</span>
                </button>

                <button @click="insertSnippet('wait')"
                    class="p-1.5 text-sm rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                    <i data-lucide="hourglass" class="w-4 h-4"></i>
                    <span class="ml-1">Insert Wait</span>
                </button>

                <div class="h-4 border-r border-zinc-300 dark:border-zinc-600 mx-1"></div>

                <div>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Theme:</span>
                    <select x-model="editorTheme" @change="updateTheme"
                        class="ml-1 text-sm px-1.5 py-1 rounded bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600">
                        <option value="vs">Light</option>
                        <option value="vs-dark">Dark</option>
                    </select>
                </div>

                <div>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Font Size:</span>
                    <select x-model="fontSize" @change="updateFontSize"
                        class="ml-1 text-sm px-1.5 py-1 rounded bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600">
                        <option value="12">12px</option>
                        <option value="14">14px</option>
                        <option value="16">16px</option>
                        <option value="18">18px</option>
                    </select>
                </div>

                <div class="ml-auto">
                    <button @click="openHelpModal"
                        class="p-1.5 text-sm rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 flex items-center transition-colors">
                        <i data-lucide="help-circle" class="w-4 h-4 mr-1"></i>
                        <span>Help</span>
                    </button>
                </div>
            </div>

            <!-- Code Editor -->
            <div class="relative">
                <div class="border-0 w-full h-[600px]" id="monaco-editor"></div>
            </div>
        </div>

        <!-- Test Case Context -->
        <div
            class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 overflow-hidden">
            <div
                class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/30 dark:bg-zinc-800/30 flex justify-between items-center">
                <h2 class="text-lg font-medium text-zinc-900 dark:text-white">Test Case Context</h2>
                <button @click="toggleContextPanel"
                    class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200">
                    <i data-lucide="chevron-down" class="w-5 h-5" :class="{ 'rotate-180': !showContext }"></i>
                </button>
            </div>
            <div x-show="showContext" x-transition class="p-6 space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Test Steps:</h3>
                    <ul class="mt-2 list-decimal pl-5 space-y-1">
                        <template x-for="(step, index) in testCaseSteps" :key="index">
                            <li class="text-sm text-zinc-600 dark:text-zinc-400" x-text="step"></li>
                        </template>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Expected Results:</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400" x-text="testCaseExpectedResults"></p>
                </div>
            </div>
        </div>

        <!-- Help Modal -->
        <div x-show="showHelpModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="help-modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
                <div class="fixed inset-0 bg-zinc-900/60 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
                    @click="showHelpModal = false"></div>
                <div
                    class="relative inline-block w-full max-w-3xl p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-zinc-800 shadow-xl rounded-2xl">
                    <div class="absolute top-0 right-0 pt-5 pr-5">
                        <button type="button" @click="showHelpModal = false"
                            class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div>
                        <h3 class="text-xl font-medium text-zinc-900 dark:text-zinc-100" id="help-modal-title">
                            Editor Help & Keyboard Shortcuts
                        </h3>

                        <div class="mt-4 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">General Shortcuts
                                    </h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-zinc-600 dark:text-zinc-400">Save</span>
                                            <span
                                                class="font-mono text-xs bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 rounded">Ctrl+S</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-zinc-600 dark:text-zinc-400">Find</span>
                                            <span
                                                class="font-mono text-xs bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 rounded">Ctrl+F</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-zinc-600 dark:text-zinc-400">Replace</span>
                                            <span
                                                class="font-mono text-xs bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 rounded">Ctrl+H</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-zinc-600 dark:text-zinc-400">Undo</span>
                                            <span
                                                class="font-mono text-xs bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 rounded">Ctrl+Z</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-zinc-600 dark:text-zinc-400">Redo</span>
                                            <span
                                                class="font-mono text-xs bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 rounded">Ctrl+Y</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Code Formatting
                                    </h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-zinc-600 dark:text-zinc-400">Format Document</span>
                                            <span
                                                class="font-mono text-xs bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 rounded">Shift+Alt+F</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-zinc-600 dark:text-zinc-400">Indent Line</span>
                                            <span
                                                class="font-mono text-xs bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 rounded">Tab</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-zinc-600 dark:text-zinc-400">Outdent Line</span>
                                            <span
                                                class="font-mono text-xs bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 rounded">Shift+Tab</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-zinc-600 dark:text-zinc-400">Comment Line</span>
                                            <span
                                                class="font-mono text-xs bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 rounded">Ctrl+/</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-zinc-600 dark:text-zinc-400">Duplicate Line</span>
                                            <span
                                                class="font-mono text-xs bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 rounded">Shift+Alt+Down</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div x-show="framework === 'selenium-python'" class="mt-6">
                                <h4 class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Selenium Python
                                    Snippets</h4>
                                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 overflow-x-auto">
                                    <pre class="text-xs text-zinc-800 dark:text-zinc-200 font-mono">
# Example Selenium Python structure
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

class TestExample:
    def setup_method(self):
        self.driver = webdriver.Chrome()
        self.driver.maximize_window()

    def teardown_method(self):
        self.driver.quit()

    def test_example(self):
        # Navigate to URL
        self.driver.get("https://example.com")

        # Wait for element to be visible
        element = WebDriverWait(self.driver, 10).until(
            EC.visibility_of_element_located((By.ID, "example-id"))
        )

        # Click element
        element.click()

        # Assert page title
        assert "Expected Title" in self.driver.title
                                </pre>
                                </div>
                            </div>

                            <div x-show="framework === 'cypress'" class="mt-6">
                                <h4 class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Cypress Snippets</h4>
                                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 overflow-x-auto">
                                    <pre class="text-xs text-zinc-800 dark:text-zinc-200 font-mono">
// Example Cypress test structure
describe('Example Test', () => {
  beforeEach(() => {
    cy.visit('https://example.com')
  })

  it('should perform an action and verify result', () => {
    // Click on element
    cy.get('#example-id').click()

    // Type text
    cy.get('#username').type('testuser')

    // Assert element contains text
    cy.get('h1').should('contain', 'Welcome')

    // Wait for API response
    cy.intercept('GET', '/api/users').as('getUsers')
    cy.wait('@getUsers')

    // Verify URL
    cy.url().should('include', '/dashboard')
  })
})
                                </pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .btn-secondary {
            @apply bg-white/50 dark:bg-zinc-700/50 border border-zinc-300/70 dark:border-zinc-600/50 hover:bg-zinc-50/70 dark:hover:bg-zinc-600/50 shadow-sm text-zinc-700 dark:text-zinc-300 transition-all;
        }

        .btn-primary {
            @apply bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm hover:shadow-md transition-all;
        }

        /* Fix for Monaco editor in dark mode */
        .dark .monaco-editor .margin {
            background-color: #2d3748 !important;
        }

        .dark .monaco-editor .monaco-scrollable-element {
            background-color: #1a202c !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('scriptEditor', (config) => ({
                scriptId: config.scriptId,
                name: config.initialName,
                framework: config.initialFramework,
                content: config.initialContent,
                updateUrl: config.updateUrl,
                csrfToken: config.csrfToken,
                testCaseSteps: config.testCaseSteps || [],
                testCaseExpectedResults: config.testCaseExpectedResults || '',

                editor: null,
                wordWrap: true,
                editorTheme: 'vs',
                fontSize: 14,
                showHelpModal: false,
                showContext: true,
                isSubmitting: false,

                init() {
                    this.setupEditor();

                    // Initialize theme based on current color mode
                    this.editorTheme = document.documentElement.classList.contains('dark') ? 'vs-dark' :
                        'vs';

                    // Set up keyboard shortcut for save
                    document.addEventListener('keydown', (e) => {
                        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                            e.preventDefault();
                            this.saveScript();
                        }
                    });

                    // Listen for dark mode changes
                    const observer = new MutationObserver((mutations) => {
                        mutations.forEach((mutation) => {
                            if (mutation.attributeName === 'class') {
                                const isDark = document.documentElement.classList
                                    .contains('dark');
                                this.editorTheme = isDark ? 'vs-dark' : 'vs';
                                this.updateTheme();
                            }
                        });
                    });
                    observer.observe(document.documentElement, {
                        attributes: true
                    });
                },

                setupEditor() {
                    require.config({
                        paths: {
                            'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs'
                        }
                    });

                    require(['vs/editor/editor.main'], () => {
                        // Determine language based on framework
                        const language = this.framework === 'cypress' ? 'javascript' : 'python';

                        // Create editor
                        this.editor = monaco.editor.create(document.getElementById(
                            'monaco-editor'), {
                            value: this.content,
                            language: language,
                            theme: this.editorTheme,
                            wordWrap: this.wordWrap ? 'on' : 'off',
                            lineNumbers: 'on',
                            minimap: {
                                enabled: true
                            },
                            scrollBeyondLastLine: false,
                            automaticLayout: true,
                            fontSize: this.fontSize,
                            tabSize: 4,
                            insertSpaces: true,
                            formatOnPaste: true,
                            formatOnType: true,
                            scrollbar: {
                                verticalScrollbarSize: 12,
                                horizontalScrollbarSize: 12
                            },
                            renderLineHighlight: 'all',
                            bracketPairColorization: {
                                enabled: true
                            }
                        });

                        // Set up change handler to update content
                        this.editor.onDidChangeModelContent(() => {
                            this.content = this.editor.getValue();
                        });

                        // Add command for saving with Ctrl+S
                        this.editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyS,
                        () => {
                                this.saveScript();
                            });
                    });
                },

                saveScript() {
                    if (this.isSubmitting) return;

                    if (!this.name.trim()) {
                        this.showNotification('error', 'Please enter a script name');
                        return;
                    }

                    if (!this.framework) {
                        this.showNotification('error', 'Please select a framework type');
                        return;
                    }

                    const content = this.editor.getValue();
                    if (!content.trim()) {
                        this.showNotification('error', 'Script content cannot be empty');
                        return;
                    }

                    this.isSubmitting = true;

                    fetch(this.updateUrl, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                name: this.name,
                                framework_type: this.framework,
                                script_content: content
                            })
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                this.showNotification('success',
                                'Test script updated successfully');

                                // Redirect back to the test script details page after successful save
                                setTimeout(() => {
                                    window.location.href = window.location.href.replace(
                                        '/edit', '');
                                }, 1000);
                            } else {
                                throw new Error(result.message || 'Failed to update script');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            this.showNotification('error', error.message || 'An error occurred');
                        })
                        .finally(() => {
                            this.isSubmitting = false;
                        });
                },

                updateMode() {
                    // Update editor language mode when framework changes
                    if (!this.editor) return;

                    const language = this.framework === 'cypress' ? 'javascript' : 'python';
                    const model = this.editor.getModel();
                    monaco.editor.setModelLanguage(model, language);
                },

                toggleWordWrap() {
                    this.wordWrap = !this.wordWrap;
                    if (this.editor) {
                        this.editor.updateOptions({
                            wordWrap: this.wordWrap ? 'on' : 'off'
                        });
                    }
                },

                indentCode() {
                    if (!this.editor) return;
                    this.editor.getAction('editor.action.indentLines').run();
                },

                outdentCode() {
                    if (!this.editor) return;
                    this.editor.getAction('editor.action.outdentLines').run();
                },

                updateTheme() {
                    if (!this.editor) return;
                    monaco.editor.setTheme(this.editorTheme);
                },

                updateFontSize() {
                    if (!this.editor) return;
                    this.editor.updateOptions({
                        fontSize: parseInt(this.fontSize)
                    });
                },

                insertSnippet(type) {
                    if (!this.editor) return;

                    const selection = this.editor.getSelection();
                    const id = monaco.editor.createModel('temp').id;

                    let snippet = '';

                    if (this.framework === 'selenium-python') {
                        if (type === 'assert') {
                            snippet =
                                'assert ${1:expected} == ${2:actual}, "Assertion failed: values do not match"';
                        } else if (type === 'wait') {
                            snippet =
                                'element = WebDriverWait(self.driver, ${1:10}).until(\n    EC.visibility_of_element_located((By.${2:ID}, "${3:element_id}"))\n)';
                        }
                    } else if (this.framework === 'cypress') {
                        if (type === 'assert') {
                            snippet =
                                "cy.get('${1:selector}').should('${2:contain}', '${3:expected value}')";
                        } else if (type === 'wait') {
                            snippet = "cy.wait('${1:@aliasName}', { timeout: ${2:5000} })";
                        }
                    } else {
                        // Generic snippets
                        if (type === 'assert') {
                            snippet = "// Assert that ${1:expected} equals ${2:actual}\n";
                        } else if (type === 'wait') {
                            snippet = "// Wait for element with ${1:selector}\n";
                        }
                    }

                    this.editor.executeEdits('', [{
                        identifier: {
                            major: 1,
                            minor: 1
                        },
                        range: selection,
                        text: snippet,
                        forceMoveMarkers: true
                    }]);

                    this.editor.focus();
                },

                openHelpModal() {
                    this.showHelpModal = true;
                },

                toggleContextPanel() {
                    this.showContext = !this.showContext;
                },

                showNotification(type, message) {
                    // Dispatch event to notification system
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            type,
                            message
                        }
                    }));
                }
            }));
        });
    </script>
@endpush
