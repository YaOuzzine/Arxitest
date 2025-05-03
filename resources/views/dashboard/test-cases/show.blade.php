{{-- resources/views/dashboard/test-cases/show.blade.php --}}

@php
    /**
     * @var \App\Models\Project $project
     * @var \App\Models\TestSuite $testSuite
     * @var \App\Models\TestCase $testCase
     * @var \Illuminate\Database\Eloquent\Collection|\App\Models\TestScript[] $testScripts
     * @var \Illuminate\Database\Eloquent\Collection|\App\Models\TestData[] $testData
     */
    $pageTitle = $testCase->title;

    // Load relationships if not already loaded
    $testCase->loadMissing(['testSuite', 'testScripts', 'testData', 'story']);
    $testScripts = $testCase->testScripts ?? collect();
    $testData = $testCase->testData ?? collect();
    $testSuite = $testCase->testSuite;
    $story = $testCase->story;

    // Parse steps as array (handle both array and JSON string)
    $steps = $testCase->steps ?? [];
    if (is_string($steps)) {
        $decodedSteps = json_decode($steps, true);
        $steps = is_array($decodedSteps) ? $decodedSteps : [];
    } elseif (!is_array($steps)) {
        $steps = [];
    }

    // Parse tags as array
    $tags = $testCase->tags ?? [];
    if (is_string($tags)) {
        $decodedTags = json_decode($tags, true);
        $tags = is_array($decodedTags) ? $decodedTags : [];
    } elseif (!is_array($tags)) {
        $tags = [];
    }

    // Status & priority badges
    $statusColors = [
        'draft' =>
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 border-yellow-200 dark:border-yellow-800/40',
        'active' =>
            'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 border-green-200 dark:border-green-800/40',
        'deprecated' =>
            'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300 border-orange-200 dark:border-orange-800/40',
        'archived' =>
            'bg-zinc-100 text-zinc-800 dark:bg-zinc-700/40 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700/40',
    ];
    $statusColor = $statusColors[$testCase->status] ?? $statusColors['draft'];

    $priorityColors = [
        'low' =>
            'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border-blue-200 dark:border-blue-800/40',
        'medium' =>
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 border-yellow-200 dark:border-yellow-800/40',
        'high' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 border-red-200 dark:border-red-800/40',
    ];
    $priorityColor = $priorityColors[$testCase->priority] ?? $priorityColors['medium'];

    // Framework language mapping for PrismJS
    $frameworkLanguages = [
        'selenium-python' => 'python',
        'cypress' => 'javascript',
        'other' => 'markup',
    ];

    // Data format language mapping for PrismJS
    $dataFormatLanguages = [
        'json' => 'json',
        'csv' => 'csv',
        'xml' => 'xml',
        'plain' => 'plaintext',
        'other' => 'markup',
    ];
@endphp

@extends('layouts.dashboard')

@section('title', $pageTitle)

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects') }}"
            class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">Projects</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.show', $project->id) }}"
            class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">{{ $project->name }}</a>
    </li>
    @if ($testSuite)
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <a href="{{ route('dashboard.projects.test-suites.show', [$project->id, $testSuite->id]) }}"
                class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">{{ $testSuite->name }}</a>
        </li>
    @endif
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">{{ Str::limit($testCase->title, 30) }}</span>
    </li>
@endsection

@section('content')
    <div class="space-y-8" x-data="testCaseView">
        <!-- Header with Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">{{ $testCase->title }}</h1>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 text-xs font-medium rounded-full border {{ $statusColor }}">
                        {{ ucfirst($testCase->status) }}
                    </span>
                    <span class="px-3 py-1 text-xs font-medium rounded-full border {{ $priorityColor }}">
                        {{ ucfirst($testCase->priority) }} Priority
                    </span>
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        <i data-lucide="clock" class="inline-block w-3.5 h-3.5 mr-1"></i>
                        Updated {{ $testCase->updated_at->diffForHumans() }}
                    </span>
                </div>
            </div>

            <div class="flex flex-shrink-0 gap-2">
                <a href="{{ route('dashboard.projects.test-cases.edit', [$project->id, $testCase->id]) }}"
                    class="btn-secondary">
                    <i data-lucide="edit-3" class="w-4 h-4 mr-1"></i> Edit
                </a>
                <button @click="confirmDelete = true" class="btn-danger">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Delete
                </button>
            </div>
        </div>

        <!-- Main Content Tabs -->
        <div
            class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <!-- Tab Navigation -->
            <div class="border-b border-zinc-200 dark:border-zinc-700">
                <nav class="flex overflow-x-auto" aria-label="Tabs">
                    <button @click="setActiveTab('details')"
                        :class="{
                            'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400': activeTab === 'details',
                            'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600': activeTab !== 'details'
                        }"
                        class="px-4 py-4 font-medium text-sm border-b-2 whitespace-nowrap">
                        <i data-lucide="clipboard-list" class="inline-block w-4 h-4 mr-1"></i>
                        Test Case Details
                    </button>
                    <button @click="setActiveTab('scripts')"
                        :class="{
                            'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400': activeTab === 'scripts',
                            'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600': activeTab !== 'scripts'
                        }"
                        class="px-4 py-4 font-medium text-sm border-b-2 whitespace-nowrap">
                        <i data-lucide="file-code" class="inline-block w-4 h-4 mr-1"></i>
                        Test Scripts <span
                            class="ml-1 px-2 py-0.5 rounded-full text-xs bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300">{{ $testScripts->count() }}</span>
                    </button>
                    <button @click="setActiveTab('testdata')"
                        :class="{
                            'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400': activeTab === 'testdata',
                            'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600': activeTab !== 'testdata'
                        }"
                        class="px-4 py-4 font-medium text-sm border-b-2 whitespace-nowrap">
                        <i data-lucide="database" class="inline-block w-4 h-4 mr-1"></i>
                        Test Data <span
                            class="ml-1 px-2 py-0.5 rounded-full text-xs bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300">{{ $testData->count() }}</span>
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Include the partials for each tab -->
                @include('dashboard.test-cases.partials.details-tab')
                @include('dashboard.test-cases.partials.scripts-tab')
                @include('dashboard.test-cases.partials.data-tab')
            </div>
        </div>

        <!-- Modals -->
        @include('dashboard.modals.script-edit-modal')
        @include('dashboard.modals.data-edit-modal')
        @include('dashboard.modals.test-script-modal', [
            'testCase' => $testCase,
            'project' => $project,
            'testScripts' => $testScripts,
        ])
        @include('dashboard.modals.test-data-modal', [
            'testCase' => $testCase,
            'project' => $project,
            'testData' => $testData,
        ])

        <!-- Delete Confirmation Modal -->
        <div x-show="confirmDelete" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-zinc-900/70 backdrop-blur-sm"
                    @click="confirmDelete = false"></div>
                <div class="relative w-full max-w-lg p-6 mx-auto bg-white dark:bg-zinc-800 rounded-lg shadow-xl"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    <div class="flex flex-col items-center">
                        <div
                            class="flex items-center justify-center w-12 h-12 mx-auto rounded-full bg-red-100 dark:bg-red-900/30">
                            <i data-lucide="trash-2" class="w-6 h-6 text-red-600 dark:text-red-400"></i>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-zinc-900 dark:text-white">Delete Test Case</h3>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                            Are you sure you want to delete this test case? This action cannot be undone.
                        </p>
                        <div class="flex items-center justify-center w-full mt-5 gap-3">
                            <button @click="confirmDelete = false"
                                class="inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 rounded-lg shadow-sm">
                                Cancel
                            </button>
                            <form method="POST"
                                action="{{ route('dashboard.projects.test-cases.destroy', [$project->id, $testCase->id]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg shadow-sm">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Area -->
        <div x-data="notification" x-show="show" x-cloak x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed bottom-4 right-4 w-full max-w-sm p-4 rounded-lg shadow-lg pointer-events-auto"
            :class="{
                'bg-green-50 dark:bg-green-800/90 border border-green-200 dark:border-green-700': type === 'success',
                'bg-red-50 dark:bg-red-800/90 border border-red-200 dark:border-red-700': type === 'error'
            }">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i data-lucide="check-circle" class="w-6 h-6 text-green-500" x-show="type === 'success'"></i>
                    <i data-lucide="alert-circle" class="w-6 h-6 text-red-500" x-show="type === 'error'"></i>
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium"
                        :class="{ 'text-green-800 dark:text-green-100': type === 'success', 'text-red-800 dark:text-red-100': type === 'error' }"
                        x-text="message"></p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button @click="show = false"
                        class="inline-flex rounded-md p-1 focus:outline-none focus:ring-2 focus:ring-offset-2"
                        :class="{
                            'text-green-500 hover:bg-green-100 dark:hover:bg-green-700 focus:ring-green-600 dark:focus:ring-offset-green-800': type === 'success',
                            'text-red-500 hover:bg-red-100 dark:hover:bg-red-700 focus:ring-red-600 dark:focus:ring-offset-red-800': type === 'error'
                        }">
                        <span class="sr-only">Close</span>
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    {{-- Include PrismJS theme --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css"
        integrity="sha512-vswe+cgvic/XBoF1OcM/TeJ2FW0OofqAVdCZiEYkd6dwGXuxGoVZSgoqvPKrG4+DingPYFKCZmHAIU5xyzY解答=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* CodeMirror specific styling */
        .CodeMirror {
            height: 100% !important;
            width: 100% !important;
            position: absolute !important;
            font-family: 'JetBrains Mono', 'Fira Code', monospace !important;
            font-size: 14px;
            line-height: 1.6;
        }

        /* Editor container styles */
        #script-editor-container,
        #data-editor-container {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            height: 100%;
            width: 100%;
        }

        /* Fix for flex container */
        .flex-1.relative.overflow-hidden {
            position: relative;
            flex: 1 1 auto;
            min-height: 400px;
            display: flex;
            flex-direction: column;
        }

        /* Ensure the modal content takes the full height */
        .flex.flex-col.h-\[calc\(100vh-12rem\)\] {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 12rem);
            max-height: 800px;
        }

        /* Force the editor container to expand */
        .flex-1 {
            flex: 1 1 0% !important;
        }

        /* Button Styles */
        .btn-primary {
            @apply inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed;
        }

        .btn-secondary {
            @apply inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 font-medium rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed;
        }

        .btn-danger {
            @apply inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed;
        }

        /* Form Styles */
        .form-input,
        .form-textarea,
        .form-select {
            @apply shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 dark:text-zinc-200 dark:placeholder-zinc-400;
        }

        .form-checkbox {
            @apply shadow-sm focus:ring-indigo-500 text-indigo-600 border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 dark:checked:bg-indigo-500 dark:focus:ring-offset-zinc-800;
        }

        /* Adjust PrismJS background and text for better contrast with the theme */
        :not(pre)>code[class*="language-"],
        pre[class*="language-"] {
            background: #f8fafc;
            /* Light background for light mode */
        }

        .dark :not(pre)>code[class*="language-"],
        .dark pre[class*="language-"] {
            background: #18181b;
            /* Dark background for dark mode */
        }

        /* Ensure padding is handled correctly within the pre block */
        pre[class*="language-"] code {
            display: block;
            padding: 1em;
        }

        [x-cloak] {
            display: none !important;
        }


        /* Monaco editor container styling */
        #monaco-script-editor,
        #monaco-data-editor {
            min-height: 400px;
            height: 100%;
            width: 100%;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        /* Dark mode adjustments */
        .dark #monaco-script-editor,
        .dark #monaco-data-editor {
            border-color: rgba(255, 255, 255, 0.1);
        }

        /* Make editor visible and properly sized */
        .monaco-editor {
            width: 100% !important;
            height: 100% !important;
        }
    </style>
@endpush

@push('scripts')
    {{-- Include PrismJS Core & Components --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"
        integrity="sha512-7Z9J3l1+EYfeaPKcGXu3MS/7BLOQmLpoTsAbMTyog+Kmy8Џ1MLXMH4Q7mvN+6hQMER+7IUcudCLD7b/q+/mDQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"
        integrity="sha512-SkmBfuA2hqjzEVpmnMt/LINrjDhDHjXCqwsllmJNCDHEVLcwjDqfbYf9hPec6pvQO/+JiS9J7Gf6+mFk07kqBQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    {{-- Explicitly include common languages to potentially speed up initial load --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"
        integrity="sha512-AKaNmg/7cgoALCU5Ym9JbUSGTz0KXvuRcV5I9Ua/qOPGIMI/6nMCFCWJ78SMOE4YQEJjOsZyrV3/7urTGC9QkQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"
        integrity="sha512-jwrwRWZAbkLEMLrbzLytL9BIJM8/1MvSknYZLHI501BHP+2KqS6Kk3tL9CHJDsF5Lj49Xh87jTmT+AXW/1h0DQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"
        integrity="sha512-jBiL8rLpA/nR/fFN3h+Gk9x3jdgX9o8ZbbX5J7s+q+n1sQe5fMzy1b252b6E8v4v4BfX+HKfUUpiIXgmA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-xml-doc.min.js"
        integrity="sha512-PBrZ7p/w15J53sYyP4U81J81+M1L0jxqjF1Wp4z+3W8/94+5s0+Qd4h+biTXn7KAbwEB7GgX+ZNI7Q=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-csv.min.js"
        integrity="sha512-zVryyVVKpQW19+fJvljzY904/IFt0d41y5n1W1u3WsyN0f4o9s2Emtw0s44y+hAjX9t70y9b6qI+GvQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        document.addEventListener('alpine:init', () => {

            Alpine.store('editorData', {
                id: null,
                name: '',
                format: '',
                content: '',
                usage_context: '',
                is_sensitive: false
            });

            Alpine.data('testCaseView', () => ({
                // Tab Management
                activeTab: 'details',
                confirmDelete: false,

                // Script Management
                expandedScript: null,
                testScripts: @json($testScripts),
                scriptSearchTerm: '',
                scriptFilterFramework: '',
                showScriptModal: false,
                showScriptEditor: false,

                // Script Editor Settings
                currentScript: {
                    id: null,
                    name: '',
                    framework_type: 'selenium-python',
                    script_content: ''
                },
                scriptEditor: null,
                editorDarkMode: document.documentElement.classList.contains('dark'),

                // Data Management
                expandedData: null,
                testData: @json($testData),
                dataSearchTerm: '',
                dataFilterFormat: '',
                showDataModal: false,
                showDataEditor: false,

                // Data Editor Settings
                currentData: {
                    id: null,
                    name: '',
                    format: 'json',
                    content: '',
                    usage_context: '',
                    is_sensitive: false
                },
                dataEditor: null,
                dataEditorDarkMode: document.documentElement.classList.contains('dark'),

                /**
                 * Initialize component
                 */
                init() {
                    // Add CodeMirror CSS
                    this.addCodeMirrorStyles();

                    // Initialize UI elements after rendering
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                        this.highlightCode();
                    });
                },

                forceEditorRefresh() {
                    // Set a small delay to ensure the DOM has updated
                    setTimeout(() => {
                        if (this.scriptEditor) {
                            this.scriptEditor.refresh();
                        }
                        if (this.dataEditor) {
                            this.dataEditor.refresh();
                        }
                        // Also force a window resize event which often helps editor sizing
                        window.dispatchEvent(new Event('resize'));
                    }, 100);
                },

                /**
                 * Add CodeMirror styles to the document
                 */
                addCodeMirrorStyles() {
                    if (!document.getElementById('codemirror-styles')) {
                        const link = document.createElement('link');
                        link.id = 'codemirror-styles';
                        link.rel = 'stylesheet';
                        link.href =
                            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css';
                        document.head.appendChild(link);

                        // Add themes
                        const themeDark = document.createElement('link');
                        themeDark.rel = 'stylesheet';
                        themeDark.href =
                            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/dracula.min.css';
                        document.head.appendChild(themeDark);

                        const themeLight = document.createElement('link');
                        themeLight.rel = 'stylesheet';
                        themeLight.href =
                            'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/eclipse.min.css';
                        document.head.appendChild(themeLight);
                    }
                },

                /**
                 * Load CodeMirror if it's not already loaded
                 */
                loadCodeMirror(callback) {
                    if (typeof CodeMirror !== 'undefined') {
                        callback();
                        return;
                    }

                    // Load main script
                    const script = document.createElement('script');
                    script.src =
                        'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js';
                    script.onload = () => {
                        // Load modes
                        const modes = ['javascript', 'python', 'xml', 'htmlmixed',
                            'css'
                        ];
                        let loadedCount = 0;

                        modes.forEach(mode => {
                            const modeScript = document.createElement('script');
                            modeScript.src =
                                `https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/${mode}/${mode}.min.js`;
                            modeScript.onload = () => {
                                loadedCount++;
                                if (loadedCount === modes.length) {
                                    callback();
                                }
                            };
                            document.head.appendChild(modeScript);
                        });
                    };
                    document.head.appendChild(script);
                },

                /**
                 * Set active tab
                 */
                setActiveTab(tab) {
                    this.activeTab = tab;
                },

                // ------------------------------------------
                // SCRIPT MANAGEMENT FUNCTIONS
                // ------------------------------------------

                /**
                 * Computed property for filtered scripts
                 */
                get filteredScripts() {
                    return this.testScripts.filter(script => {
                        const nameMatch = script.name.toLowerCase().includes(
                            this.scriptSearchTerm.toLowerCase());
                        const frameworkMatch = !this.scriptFilterFramework ||
                            script.framework_type === this
                            .scriptFilterFramework;
                        return nameMatch && frameworkMatch;
                    });
                },

                /**
                 * Toggle script collapse/expand
                 */
                toggleScript(id) {
                    this.expandedScript = this.expandedScript === id ? null : id;
                    if (this.expandedScript === id) {
                        this.$nextTick(() => this.highlightCode());
                    }
                },

                /**
                 * Open script editing modal
                 */
                editScript(script) {
                    // Create a deep copy of the script to avoid modifying the original directly
                    this.currentScript = {
                        id: script.id,
                        name: script.name,
                        framework_type: script.framework_type,
                        script_content: script.script_content
                    };

                    this.showScriptEditor = true;

                    // Initialize the CodeMirror editor after the modal is shown
                    this.$nextTick(() => {
                        this.loadCodeMirror(() => {
                            this.initScriptEditor();
                            this.forceEditorRefresh(); // Add this line
                        });
                    });
                },

                /**
                 * Initialize the CodeMirror editor for scripts
                 */
                initScriptEditor() {
                    const container = document.getElementById('script-editor-container');
                    if (!container) return;

                    // If editor already exists, dispose it
                    if (this.scriptEditor) {
                        container.innerHTML = '';
                    }

                    // Create the editor
                    this.scriptEditor = CodeMirror(container, {
                        value: this.currentScript.script_content,
                        mode: this.getCodeMirrorMode(this.currentScript.framework_type),
                        theme: this.editorDarkMode ? 'dracula' : 'eclipse',
                        lineNumbers: true,
                        indentUnit: 4,
                        tabSize: 4,
                        autoCloseBrackets: true,
                        matchBrackets: true,
                        lineWrapping: true,
                        extraKeys: {
                            "Tab": function(cm) {
                                if (cm.somethingSelected()) {
                                    cm.indentSelection("add");
                                } else {
                                    cm.replaceSelection("    ", "end");
                                }
                            }
                        }
                    });

                    // Set up change handler
                    this.scriptEditor.on('change', () => {
                        this.currentScript.script_content = this.scriptEditor.getValue();
                    });

                    // Make sure editor resizes properly
                    this.$nextTick(() => {
                        this.scriptEditor.refresh();
                        window.dispatchEvent(new Event('resize'));
                    });
                },
                /**
                 * Get the CodeMirror mode for a framework
                 */
                getCodeMirrorMode(framework) {
                    switch (framework) {
                        case 'selenium-python':
                            return 'python';
                        case 'cypress':
                            return 'javascript';
                        default:
                            return 'text/plain';
                    }
                },

                /**
                 * Change editor mode when framework changes
                 */
                changeEditorMode() {
                    if (this.scriptEditor) {
                        this.scriptEditor.setOption('mode', this.getCodeMirrorMode(this
                            .currentScript.framework_type));
                    }
                },

                /**
                 * Toggle editor theme
                 */
                toggleEditorTheme() {
                    this.editorDarkMode = !this.editorDarkMode;
                    if (this.scriptEditor) {
                        this.scriptEditor.setOption('theme', this.editorDarkMode ?
                            'dracula' : 'eclipse');
                    }
                },

                /**
                 * Save the edited script
                 */
                saveEditedScript() {
                    if (!this.currentScript.name) return;

                    // Create payload
                    const payload = {
                        name: this.currentScript.name,
                        framework_type: this.currentScript.framework_type,
                        script_content: this.currentScript.script_content
                    };

                    // Get CSRF token
                    const token = document.querySelector('meta[name="csrf-token"]')
                        .getAttribute('content');

                    // Submit to server
                    fetch(`/dashboard/projects/{{ $project->id }}/test-cases/{{ $testCase->id }}/scripts/${this.currentScript.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update the script in the list
                                const index = this.testScripts.findIndex(s => s.id ===
                                    this.currentScript.id);
                                if (index !== -1) {
                                    this.testScripts[index] = {
                                        ...this.testScripts[index],
                                        name: payload.name,
                                        framework_type: payload.framework_type,
                                        script_content: payload.script_content
                                    };
                                }

                                // Show success notification and close modal
                                this.showNotificationMessage(
                                    'Script updated successfully!', 'success');
                                this.showScriptEditor = false;
                            } else {
                                throw new Error(data.message ||
                                    'Failed to update script');
                            }
                        })
                        .catch(error => {
                            console.error('Error updating script:', error);
                            this.showNotificationMessage('Failed to update script: ' +
                                error.message, 'error');
                        });
                },

                /**
                 * Confirm script deletion
                 */
                confirmDeleteScript(id) {
                    if (confirm(
                            'Are you sure you want to delete this script? This action cannot be undone.'
                        )) {
                        this.deleteScript(id);
                    }
                },

                /**
                 * Delete a script
                 */
                async deleteScript(id) {
                    try {
                        const csrfToken = document.querySelector(
                            'meta[name="csrf-token"]');
                        if (!csrfToken) {
                            throw new Error('CSRF token not found');
                        }

                        const response = await fetch(
                            `/dashboard/projects/{{ $project->id }}/test-cases/{{ $testCase->id }}/scripts/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken.getAttribute(
                                        'content'),
                                    'Accept': 'application/json',
                                }
                            });

                        if (response.ok) {
                            // Remove the script from the list
                            this.testScripts = this.testScripts.filter(s => s.id !==
                                id);
                            this.showNotificationMessage('Script deleted successfully!',
                                'success');
                        } else {
                            const errorData = await response.json();
                            throw new Error(errorData.message ||
                                'Failed to delete script');
                        }
                    } catch (error) {
                        console.error('Error deleting script:', error);
                        this.showNotificationMessage('Failed to delete script: ' + error
                            .message, 'error');
                    }
                },

                /**
                 * Get framework label
                 */
                getFrameworkLabel(frameworkType) {
                    const labels = {
                        'selenium-python': 'Selenium (Python)',
                        'cypress': 'Cypress (JavaScript)',
                        'other': 'Other Framework'
                    };
                    return labels[frameworkType] || frameworkType;
                },

                // ------------------------------------------
                // TEST DATA MANAGEMENT FUNCTIONS
                // ------------------------------------------

                /**
                 * Computed property for filtered test data
                 */
                get filteredData() {
                    return this.testData.filter(data => {
                        const nameMatch = data.name.toLowerCase().includes(this
                            .dataSearchTerm.toLowerCase());
                        const formatMatch = !this.dataFilterFormat || data
                            .format === this.dataFilterFormat;
                        return nameMatch && formatMatch;
                    });
                },

                /**
                 * Toggle data collapse/expand
                 */
                toggleData(id) {
                    this.expandedData = this.expandedData === id ? null : id;
                    if (this.expandedData === id) {
                        this.$nextTick(() => this.highlightCode());
                    }
                },

                /**
                 * Open data editing modal
                 */
                editData(data) {
                    // Create a deep copy of the data to avoid modifying the original directly
                    this.currentData = {
                        id: data.id,
                        name: data.name,
                        format: data.format,
                        content: data.content,
                        usage_context: data.pivot?.usage_context || '',
                        is_sensitive: data.is_sensitive
                    };

                    this.showDataEditor = true;

                    // Initialize the CodeMirror editor after the modal is shown
                    this.$nextTick(() => {
                        this.loadCodeMirror(() => {
                            this.initDataEditor();
                            this.forceEditorRefresh(); // Add this line
                        });
                    });
                },
                /**
                 * Initialize the CodeMirror editor for data
                 */
                initDataEditor() {
                    const container = document.getElementById('data-editor-container');
                    if (!container) return;

                    // If editor already exists, dispose it
                    if (this.dataEditor) {
                        container.innerHTML = '';
                    }

                    // Create the editor
                    this.dataEditor = CodeMirror(container, {
                        value: this.currentData.content,
                        mode: this.getDataEditorMode(this.currentData.format),
                        theme: this.dataEditorDarkMode ? 'dracula' : 'eclipse',
                        lineNumbers: true,
                        indentUnit: 2,
                        tabSize: 2,
                        autoCloseBrackets: true,
                        matchBrackets: true,
                        lineWrapping: true,
                        extraKeys: {
                            "Tab": function(cm) {
                                if (cm.somethingSelected()) {
                                    cm.indentSelection("add");
                                } else {
                                    cm.replaceSelection("  ", "end");
                                }
                            }
                        }
                    });

                    // Set up change handler
                    this.dataEditor.on('change', () => {
                        this.currentData.content = this.dataEditor.getValue();
                    });
                },

                /**
                 * Get the CodeMirror mode for a data format
                 */
                getDataEditorMode(format) {
                    switch (format) {
                        case 'json':
                            return 'application/json';
                        case 'xml':
                            return 'application/xml';
                        case 'csv':
                        case 'plain':
                        case 'other':
                        default:
                            return 'text/plain';
                    }
                },

                /**
                 * Change editor mode when data format changes
                 */
                changeDataEditorMode() {
                    if (this.dataEditor) {
                        this.dataEditor.setOption('mode', this.getDataEditorMode(this
                            .currentData.format));
                    }
                },

                /**
                 * Toggle data editor theme
                 */
                toggleDataEditorTheme() {
                    this.dataEditorDarkMode = !this.dataEditorDarkMode;
                    if (this.dataEditor) {
                        this.dataEditor.setOption('theme', this.dataEditorDarkMode ?
                            'dracula' : 'eclipse');
                    }
                },

                /**
                 * Format data content
                 */
                formatData() {
                    if (!this.dataEditor) return;

                    try {
                        const format = this.currentData.format;
                        const content = this.dataEditor.getValue();

                        if (format === 'json') {
                            // Format JSON
                            try {
                                const jsonObj = JSON.parse(content);
                                const formattedContent = JSON.stringify(jsonObj, null, 2);
                                this.dataEditor.setValue(formattedContent);
                                this.showNotificationMessage('JSON formatted successfully',
                                    'success');
                            } catch (e) {
                                throw new Error('Invalid JSON: ' + e.message);
                            }
                        } else if (format === 'xml') {
                            // XML formatting would be more complex, just notify for now
                            this.showNotificationMessage('XML formatting not implemented',
                                'info');
                        } else {
                            this.showNotificationMessage(
                                'Formatting not available for this format', 'info');
                        }
                    } catch (error) {
                        this.showNotificationMessage('Formatting failed: ' + error.message,
                            'error');
                    }
                },

                /**
                 * Save the edited data
                 */
                saveEditedData() {
                    if (!this.currentData.name || !this.currentData.usage_context) return;

                    // Create form data for submission
                    const form = new FormData();
                    form.append('_method', 'PUT');
                    form.append('name', this.currentData.name);
                    form.append('format', this.currentData.format);
                    form.append('content', this.currentData.content);
                    form.append('usage_context', this.currentData.usage_context);
                    form.append('is_sensitive', this.currentData.is_sensitive ? '1' : '0');
                    form.append('_token', document.querySelector('meta[name="csrf-token"]')
                        .getAttribute('content'));

                    // Submit to server
                    fetch(`/dashboard/projects/{{ $project->id }}/test-cases/{{ $testCase->id }}/data/${this.currentData.id}`, {
                            method: 'POST',
                            body: form
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update the data in the list
                                const index = this.testData.findIndex(d => d.id === this
                                    .currentData.id);
                                if (index !== -1) {
                                    this.testData[index] = {
                                        ...this.testData[index],
                                        name: this.currentData.name,
                                        format: this.currentData.format,
                                        content: this.currentData.content,
                                        is_sensitive: this.currentData.is_sensitive,
                                        pivot: {
                                            ...this.testData[index].pivot,
                                            usage_context: this.currentData
                                                .usage_context
                                        }
                                    };
                                }

                                // Show success notification and close modal
                                this.showNotificationMessage(
                                    'Test data updated successfully!', 'success');
                                this.showDataEditor = false;
                            } else {
                                throw new Error(data.message ||
                                    'Failed to update test data');
                            }
                        })
                        .catch(error => {
                            console.error('Error updating test data:', error);
                            this.showNotificationMessage(
                                'Failed to update test data: ' + error.message,
                                'error');
                        });
                },

                /**
                 * Confirm data deletion
                 */
                confirmDeleteData(id) {
                    if (confirm(
                            'Are you sure you want to remove this test data? This will only remove the association with this test case but won\'t delete the data itself.'
                        )) {
                        this.deleteData(id);
                    }
                },

                /**
                 * Delete data association
                 */
                async deleteData(id) {
                    try {
                        const csrfToken = document.querySelector(
                            'meta[name="csrf-token"]');
                        if (!csrfToken) {
                            throw new Error('CSRF token not found');
                        }

                        const response = await fetch(
                            `/dashboard/projects/{{ $project->id }}/test-cases/{{ $testCase->id }}/data/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken.getAttribute(
                                        'content'),
                                    'Accept': 'application/json',
                                }
                            });

                        if (response.ok) {
                            // Remove the data from the list
                            this.testData = this.testData.filter(d => d.id !== id);
                            this.showNotificationMessage(
                                'Test data removed successfully!', 'success');
                        } else {
                            const errorData = await response.json();
                            throw new Error(errorData.message ||
                                'Failed to remove test data');
                        }
                    } catch (error) {
                        console.error('Error removing test data:', error);
                        this.showNotificationMessage('Failed to remove test data: ' +
                            error.message, 'error');
                    }
                },

                // ------------------------------------------
                // UTILITY FUNCTIONS
                // ------------------------------------------

                /**
                 * Format date for display
                 */
                formatDate(dateStr) {
                    try {
                        const date = new Date(dateStr);
                        const now = new Date();
                        const diff = now - date;

                        if (diff < 24 * 60 * 60 * 1000) {
                            // Less than a day, show "X hours ago"
                            const hours = Math.floor(diff / (60 * 60 * 1000));
                            if (hours < 1) {
                                const minutes = Math.floor(diff / (60 * 1000));
                                return minutes <= 1 ? 'just now' : `${minutes}m ago`;
                            }
                            return `${hours}h ago`;
                        } else if (diff < 7 * 24 * 60 * 60 * 1000) {
                            // Less than a week, show day
                            const days = Math.floor(diff / (24 * 60 * 60 * 1000));
                            return `${days}d ago`;
                        } else {
                            // Format as date
                            return date.toLocaleDateString();
                        }
                    } catch (e) {
                        return dateStr || 'unknown';
                    }
                },

                /**
                 * Copy text to clipboard
                 */
                copyToClipboard(text, type = 'Content') {
                    navigator.clipboard.writeText(text).then(
                        () => this.showNotificationMessage(
                            `${type} copied to clipboard!`, 'success'),
                        (err) => this.showNotificationMessage(
                            `Failed to copy ${type}: ${err}`, 'error')
                    );
                },

                /**
                 * Highlight code syntax
                 */
                highlightCode() {
                    this.$nextTick(() => {
                        if (typeof Prism !== 'undefined') {
                            Prism.highlightAll();
                        }
                    });
                },

                /**
                 * Get the correct language for code highlighting
                 */
                highlightCode(content, format) {
                    const getLanguage = (format) => {
                        // For scripts
                        if (format === 'selenium-python') return 'python';
                        if (format === 'cypress') return 'javascript';

                        // For data
                        if (format === 'json') return 'json';
                        if (format === 'csv') return 'csv';
                        if (format === 'xml') return 'xml';

                        return 'plaintext';
                    };

                    const language = getLanguage(format);
                    const escapedContent = this.escapeHtml(content);

                    return `<code class="language-${language}">${escapedContent}</code>`;
                },

                /**
                 * Escape HTML for safe display
                 */
                escapeHtml(text) {
                    const map = {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    };
                    return text.replace(/[&<>"']/g, m => map[m]);
                },

                /**
                 * Show notification message
                 */
                showNotificationMessage(message, type = 'success') {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            message,
                            type
                        }
                    }));
                }
            }));

            // Notification component (unchanged)
            Alpine.data('notification', () => ({
                show: false,
                message: '',
                type: 'success',
                timeout: null,

                init() {
                    window.addEventListener('notify', event => {
                        this.message = event.detail.message;
                        this.type = event.detail.type || 'success';
                        this.show = true;

                        if (this.timeout) {
                            clearTimeout(this.timeout);
                        }
                        this.timeout = setTimeout(() => {
                            this.show = false;
                        }, 5000);

                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                        });
                    });
                }
            }));


            // Notification component
            Alpine.data('notification', () => ({
                show: false,
                message: '',
                type: 'success',
                timeout: null,

                init() {
                    window.addEventListener('notify', event => {
                        this.message = event.detail.message;
                        this.type = event.detail.type || 'success';
                        this.show = true;

                        if (this.timeout) {
                            clearTimeout(this.timeout);
                        }
                        this.timeout = setTimeout(() => {
                            this.show = false;
                        }, 5000);

                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                        });
                    });
                }
            }));
        });
    </script>
@endpush
