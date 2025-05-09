{{-- resources/views/dashboard/test-scripts/show.blade.php --}}
@extends('layouts.dashboard')

@section('title', "{$testScript->name}")

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.show', $project->id) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">{{ $project->name }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">{{ $testCase->title }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id]) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">Test Scripts</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">{{ $testScript->name }}</span>
    </li>
@endsection

@section('content')
    <div class="h-full space-y-6" x-data="testScriptViewer">
        <!-- Top Action Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200/70 dark:border-zinc-700/50 p-4">
            <div class="flex-1">
                <h1 class="text-2xl font-bold bg-gradient-to-r from-zinc-800 dark:from-zinc-100 to-zinc-600 dark:to-zinc-300 bg-clip-text text-transparent tracking-tight flex items-center gap-2">
                    <i data-lucide="code" class="h-6 w-6 text-indigo-500"></i>
                    {{ $testScript->name }}
                </h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Test script for <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $testCase->title }}</span>
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id]) }}"
                   class="px-3 py-2 rounded-lg text-sm bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-600 flex items-center gap-1.5 transition-colors">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    Back
                </a>

                <button @click="copyToClipboard"
                   class="px-3 py-2 rounded-lg text-sm bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-600 flex items-center gap-1.5 transition-colors">
                    <i data-lucide="copy" class="w-4 h-4"></i>
                    Copy
                </button>

                <button @click="downloadScript"
                   class="px-3 py-2 rounded-lg text-sm bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-600 flex items-center gap-1.5 transition-colors">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Download
                </button>

                <a href="{{ route('dashboard.projects.test-cases.scripts.edit', [$project->id, $testCase->id, $testScript->id]) }}"
                   class="px-3 py-2 rounded-lg text-sm bg-indigo-600 hover:bg-indigo-700 text-white flex items-center gap-1.5 transition-colors">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                    Edit
                </a>

                <button @click="openDeleteModal"
                   class="px-3 py-2 rounded-lg text-sm bg-red-500 hover:bg-red-600 text-white flex items-center gap-1.5 transition-colors">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Delete
                </button>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Sidebar: Details and Metadata -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Script Info Card -->
                <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/50 dark:bg-zinc-800/50">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="info" class="w-5 h-5 text-zinc-500 dark:text-zinc-400"></i>
                            Script Details
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Framework Type -->
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Framework Type</h3>
                            <div class="mt-1.5 flex items-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                {{ match($testScript->framework_type) {
                                    'selenium-python' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                    'cypress' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                    default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700/30 dark:text-zinc-300',
                                } }}">
                                    {{ match($testScript->framework_type) {
                                        'selenium-python' => 'Selenium Python',
                                        'cypress' => 'Cypress',
                                        'other' => 'Other',
                                        default => $testScript->framework_type,
                                    } }}
                                </span>

                                <a href="#" @click="showFrameworkInfo" class="ml-1.5 text-indigo-500 hover:text-indigo-600 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    <i data-lucide="help-circle" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Creator -->
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Created By</h3>
                            <p class="mt-1.5 text-zinc-900 dark:text-zinc-100 flex items-center">
                                <i data-lucide="user" class="w-4 h-4 mr-1.5 text-zinc-400 dark:text-zinc-500"></i>
                                {{ $testScript->creator->name ?? 'Unknown' }}
                            </p>
                        </div>

                        <!-- Created At -->
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Created</h3>
                            <p class="mt-1.5 text-zinc-900 dark:text-zinc-100 flex items-center">
                                <i data-lucide="calendar" class="w-4 h-4 mr-1.5 text-zinc-400 dark:text-zinc-500"></i>
                                {{ $testScript->created_at->format('M d, Y H:i') }}
                            </p>
                        </div>

                        <!-- Updated At -->
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Last Updated</h3>
                            <p class="mt-1.5 text-zinc-900 dark:text-zinc-100 flex items-center">
                                <i data-lucide="clock" class="w-4 h-4 mr-1.5 text-zinc-400 dark:text-zinc-500"></i>
                                {{ $testScript->updated_at->format('M d, Y H:i') }}
                                <span class="ml-1.5 text-xs text-zinc-500 dark:text-zinc-400">({{ $testScript->updated_at->diffForHumans() }})</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Test Case Info Card -->
                <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/50 dark:bg-zinc-800/50">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="clipboard-list" class="w-5 h-5 text-zinc-500 dark:text-zinc-400"></i>
                            Test Case Details
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Test Case</h3>
                            <p class="mt-1.5 text-zinc-900 dark:text-zinc-100">
                                <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}" class="hover:underline hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ $testCase->title }}
                                </a>
                            </p>
                        </div>

                        @if($testCase->testSuite)
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Test Suite</h3>
                            <p class="mt-1.5 text-zinc-900 dark:text-zinc-100">
                                <a href="{{ route('dashboard.projects.test-suites.show', [$project->id, $testCase->testSuite->id]) }}" class="hover:underline hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ $testCase->testSuite->name }}
                                </a>
                            </p>
                        </div>
                        @endif

                        <!-- Priority & Status -->
                        <div class="flex space-x-4">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Priority</h3>
                                <div class="mt-1.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                    {{ match($testCase->priority) {
                                        'high' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                        'medium' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                                        'low' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                        default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700/30 dark:text-zinc-300',
                                    } }}">
                                        {{ ucfirst($testCase->priority) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Status</h3>
                                <div class="mt-1.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                    {{ match($testCase->status) {
                                        'active' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                        'draft' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                        'deprecated' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                                        'archived' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700/30 dark:text-zinc-300',
                                        default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700/30 dark:text-zinc-300',
                                    } }}">
                                        {{ ucfirst($testCase->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Tips Card -->
                <div class="bg-indigo-50/50 dark:bg-indigo-900/10 shadow-sm rounded-xl border border-indigo-200/50 dark:border-indigo-800/30 overflow-hidden">
                    <div class="px-6 py-4 border-b border-indigo-200/70 dark:border-indigo-800/50 bg-indigo-100/30 dark:bg-indigo-900/20">
                        <h2 class="text-lg font-medium text-indigo-900 dark:text-indigo-200 flex items-center gap-2">
                            <i data-lucide="lightbulb" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i>
                            Quick Tips
                        </h2>
                    </div>
                    <div class="p-6 space-y-3 text-sm">
                        <div x-show="tips.selenium && formData.framework_type === 'selenium-python'">
                            <p class="text-indigo-700 dark:text-indigo-300">
                                <span class="font-medium">Selenium Tips:</span> Use explicit waits instead of time.sleep() for more reliable tests.
                            </p>
                            <a href="https://www.selenium.dev/documentation/webdriver/" target="_blank" class="mt-1 inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:underline">
                                Selenium Documentation
                                <i data-lucide="external-link" class="ml-1 w-3 h-3"></i>
                            </a>
                        </div>

                        <div x-show="tips.cypress && formData.framework_type === 'cypress'">
                            <p class="text-indigo-700 dark:text-indigo-300">
                                <span class="font-medium">Cypress Tips:</span> Use cy.contains() to find elements by their text content.
                            </p>
                            <a href="https://docs.cypress.io/guides/overview/why-cypress" target="_blank" class="mt-1 inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:underline">
                                Cypress Documentation
                                <i data-lucide="external-link" class="ml-1 w-3 h-3"></i>
                            </a>
                        </div>

                        <div>
                            <p class="text-indigo-700 dark:text-indigo-300">
                                <i data-lucide="info" class="inline-block w-4 h-4 mr-1 relative -top-px"></i>
                                You can download this script to run it locally or edit it in your preferred IDE.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Script Content with helpful features -->
            <div class="lg:col-span-9">
                <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/50 dark:bg-zinc-800/50 flex justify-between items-center">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="file-code" class="w-5 h-5 text-zinc-500 dark:text-zinc-400"></i>
                            Script Content
                        </h2>
                        <div class="flex items-center space-x-2 text-xs text-zinc-500 dark:text-zinc-400">
                            <template x-if="formData.framework_type === 'selenium-python'">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="file-type" class="w-4 h-4"></i>
                                    Python
                                </span>
                            </template>
                            <template x-if="formData.framework_type === 'cypress'">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="file-type" class="w-4 h-4"></i>
                                    JavaScript
                                </span>
                            </template>
                        </div>
                    </div>

                    <div class="code-container relative">
                        <!-- Code preview with line numbers -->
                        <div class="flex overflow-auto">
                            <!-- Line numbers -->
                            <div class="line-numbers py-4 px-3 text-right text-xs font-mono bg-zinc-100 dark:bg-zinc-900 text-zinc-500 dark:text-zinc-600 select-none whitespace-nowrap"
                                 x-html="generateLineNumbers(formData.script_content)">
                            </div>

                            <!-- Code block with syntax highlighting-->
                            <pre class="language-{{ $testScript->framework_type === 'cypress' ? 'javascript' : 'python' }} flex-1 p-4 text-sm overflow-auto leading-relaxed">{{ $testScript->script_content }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <x-modals.delete-confirmation
            title="Delete Test Script"
            message="Are you sure you want to delete this test script? This action cannot be undone."
            itemName="'{{ addslashes($testScript->name) }}'"
            dangerText="Once deleted, you cannot recover this script."
            confirmText="Delete Script" />

        <!-- Framework Info Modal -->
        <div x-show="showFrameworkInfoModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto backdrop-blur-[2px]"
            aria-labelledby="framework-info-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-center justify-center min-h-screen p-4 text-center">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-zinc-900/60 dark:bg-zinc-900/80 transition-opacity" @click="showFrameworkInfoModal = false"></div>

                <!-- Modal panel -->
                <div x-show="showFrameworkInfoModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-zinc-800 text-left shadow-xl transition-all sm:my-8 w-full max-w-lg border border-zinc-200/80 dark:border-zinc-700/60">
                    <!-- Modal content -->
                    <div class="p-6 sm:p-8">
                        <div class="absolute top-4 right-4">
                            <button @click="showFrameworkInfoModal = false" class="p-1 rounded-full text-zinc-400 hover:text-zinc-500 dark:text-zinc-500 dark:hover:text-zinc-400">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <template x-if="formData.framework_type === 'selenium-python'">
                            <div>
                                <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900/50">
                                        <i data-lucide="code" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i>
                                    </div>
                                    Selenium with Python
                                </h3>

                                <div class="prose prose-sm dark:prose-invert max-w-none">
                                    <p>Selenium is a powerful tool for controlling web browsers through programs and automating browser tasks. Selenium Python bindings provide a convenient API to access Selenium WebDrivers.</p>

                                    <h4 class="text-sm font-medium mt-4 mb-2">Key Features:</h4>
                                    <ul class="space-y-1">
                                        <li>Cross-browser testing</li>
                                        <li>Supports Python's unittest framework</li>
                                        <li>Can interact with elements using various locators</li>
                                        <li>Supports explicit and implicit waits</li>
                                    </ul>

                                    <h4 class="text-sm font-medium mt-4 mb-2">Common Imports:</h4>
                                    <pre class="bg-zinc-100 dark:bg-zinc-900 p-2 rounded text-xs">from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC</pre>

                                    <div class="mt-4">
                                        <a href="https://www.selenium.dev/documentation/webdriver/" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center">
                                            <i data-lucide="external-link" class="w-3.5 h-3.5 mr-1"></i>
                                            Official Documentation
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="formData.framework_type === 'cypress'">
                            <div>
                                <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900/50">
                                        <i data-lucide="code" class="w-4 h-4 text-green-600 dark:text-green-400"></i>
                                    </div>
                                    Cypress
                                </h3>

                                <div class="prose prose-sm dark:prose-invert max-w-none">
                                    <p>Cypress is a next-generation front end testing tool built for the modern web. It addresses the key pain points developers face when testing modern applications.</p>

                                    <h4 class="text-sm font-medium mt-4 mb-2">Key Features:</h4>
                                    <ul class="space-y-1">
                                        <li>Time Travel: Cypress takes snapshots as your tests run</li>
                                        <li>Real-time reloads: Tests are automatically rerun as you edit</li>
                                        <li>Automatic waiting: No need for sleep or wait commands</li>
                                        <li>Network traffic control: Easily stub network responses</li>
                                    </ul>

                                    <h4 class="text-sm font-medium mt-4 mb-2">Common Structure:</h4>
                                    <pre class="bg-zinc-100 dark:bg-zinc-900 p-2 rounded text-xs">describe('My First Test', () => {
  it('Does something', () => {
    cy.visit('https://example.com')
    cy.get('.selector').click()
    cy.contains('Expected Text')
  })
})</pre>

                                    <div class="mt-4">
                                        <a href="https://docs.cypress.io/" target="_blank" class="text-green-600 dark:text-green-400 hover:underline inline-flex items-center">
                                            <i data-lucide="external-link" class="w-3.5 h-3.5 mr-1"></i>
                                            Official Documentation
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Modal footer -->
                    <div class="p-4 sm:px-8 sm:pb-8 border-t border-zinc-100/50 dark:border-zinc-700/50 bg-zinc-50/50 dark:bg-zinc-800/50">
                        <div class="flex justify-end">
                            <button @click="showFrameworkInfoModal = false" class="px-4 py-2 text-sm rounded-lg bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-600">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('testScriptViewer', () => ({
            showDeleteModal: false,
            showFrameworkInfoModal: false,
            deleteConfirmed: false,
            isDeleting: false,
            requireConfirmation: true,
            showNotification: false,
            notificationType: 'success',
            notificationTitle: '',
            notificationMessage: '',
            tips: {
                selenium: true,
                cypress: true
            },

            formData: {
                name: "{{ addslashes($testScript->name) }}",
                framework_type: "{{ $testScript->framework_type }}",
                script_content: `{{ addslashes($testScript->script_content) }}`
            },

            init() {
                if (typeof hljs !== 'undefined') {
                    document.querySelectorAll('pre').forEach(block => {
                        hljs.highlightElement(block);
                    });
                }
            },

            generateLineNumbers(code) {
                if (!code) return '';
                const lines = code.split('\n');
                return lines.map((_, i) => `<div class="line-number">${i + 1}</div>`).join('');
            },

            copyToClipboard() {
                navigator.clipboard.writeText(this.formData.script_content).then(() => {
                    this.showNotificationMessage('success', 'Copied!', 'Script copied to clipboard');
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                    this.showNotificationMessage('error', 'Error', 'Failed to copy to clipboard');
                });
            },

            downloadScript() {
                const scriptType = this.formData.framework_type === 'cypress' ? 'js' : 'py';
                const filename = `${this.formData.name.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.${scriptType}`;

                const blob = new Blob([this.formData.script_content], { type: 'text/plain' });
                const url = URL.createObjectURL(blob);

                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);

                this.showNotificationMessage('success', 'Downloaded!', `Script downloaded as ${filename}`);
            },

            showFrameworkInfo() {
                this.showFrameworkInfoModal = true;
            },

            openDeleteModal() {
                this.showDeleteModal = true;
                this.deleteConfirmed = false;
            },

            closeDeleteModal() {
                this.showDeleteModal = false;
                this.deleteConfirmed = false;
            },

            async confirmDelete() {
                this.isDeleting = true;

                try {
                    const url = "{{ route('dashboard.projects.test-cases.scripts.destroy', [$project->id, $testCase->id, $testScript->id]) }}";
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        window.location.href = "{{ route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id]) }}";
                    } else {
                        throw new Error(result.message || 'Failed to delete test script');
                    }
                } catch (error) {
                    console.error(error);
                    this.showNotificationMessage('error', 'Error', error.message || 'An error occurred');
                } finally {
                    this.isDeleting = false;
                    this.closeDeleteModal();
                }
            },

            showNotificationMessage(type, title, message) {
                this.notificationType = type;
                this.notificationTitle = title;
                this.notificationMessage = message;
                this.showNotification = true;

                // Dispatch event to notification system
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { type, message: title + ': ' + message }
                }));

                // Hide notification after 5 seconds
                setTimeout(() => {
                    this.showNotification = false;
                }, 5000);
            }
        }));
    });
</script>
@endpush

@push('styles')
<style>
    .code-container {
        max-height: 70vh;
    }

    .line-numbers {
        min-width: 3rem;
        text-align: right;
        font-size: 0.75rem;
        user-select: none;
    }

    .line-number {
        padding: 0 0.75rem;
        counter-increment: line;
        line-height: 1.5rem;
    }

    pre {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        tab-size: 4;
        line-height: 1.5rem;
    }

    /* Add some animations */
    @keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }

    .animate-fadeIn {
        animation: fadeIn 0.3s ease-in-out;
    }
</style>
@endpush
