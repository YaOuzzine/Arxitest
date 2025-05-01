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
            @include('dashboard.test-cases.partials.tab-navigation', [
                'testScripts' => $testScripts,
                'testData' => $testData,
            ])

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Details Tab -->
                @include('dashboard.test-cases.partials.tabs.details', [
                    'testCase' => $testCase,
                    'project' => $project,
                    'testSuite' => $testSuite,
                    'story' => $story,
                ])

                <!-- Test Scripts Tab -->
                @include('dashboard.test-cases.partials.tabs.scripts', [
                    'testCase' => $testCase,
                    'project' => $project,
                    'testScripts' => $testScripts,
                ])

                <!-- Test Data Tab -->
                @include('dashboard.test-cases.partials.tabs.data', [
                    'testCase' => $testCase,
                    'project' => $project,
                    'testData' => $testData,
                ])
            </div>
        </div>

        <!-- Modals -->
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
        @include('dashboard.modals.edit-test-script-modal')
        @include('dashboard.modals.edit-test-data-modal')
        @include('dashboard.modals.delete-confirmation')

        <!-- Notification Area -->
        @include('dashboard.test-cases.partials.notification')
    </div>
@endsection

@push('styles')
    {{-- PrismJS theme --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css"
        integrity="sha512-vswe+cgvic/XBoF1OcM/TeJ2FW0OofqAVdCZiEYkd6dwGXuxGoVZSgoqvPKrG4+DingPYFKcCZmHAIU5xyzY解答=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
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

        /* Code Highlighting */
        pre[class*="language-"] {
            @apply text-sm leading-relaxed;
        }

        /* Adjust PrismJS background and text for better contrast with the theme */
        :not(pre)>code[class*="language-"],
        pre[class*="language-"] {
            background: #f8fafc;
        }

        .dark :not(pre)>code[class*="language-"],
        .dark pre[class*="language-"] {
            background: #18181b;
        }

        /* Ensure padding is handled correctly within the pre block */
        pre[class*="language-"] code {
            display: block;
            padding: 1em;
        }

        [x-cloak] {
            display: none !important;
        }

        .data-list-item:hover .data-actions {
            opacity: 1;
        }

        .data-actions {
            opacity: 0;
            transition: opacity 200ms ease-in-out;
        }

        /* Code Editor Enhancements */
        .code-editor {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', 'source-code-pro', monospace;
            tab-size: 4;
        }

        /* Modal Improvements */
        .modal-content {
            max-height: calc(100vh - 8rem);
            overflow-y: auto;
        }

        /* Search and Filter Styling */
        .search-filter-container {
            background: rgba(var(--zinc-50), 0.5);
            backdrop-filter: blur(8px);
        }

        .dark .search-filter-container {
            background: rgba(var(--zinc-800), 0.5);
        }

        /* Custom Scrollbar for Dark Mode */
        .dark .overflow-y-auto::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .dark .overflow-y-auto::-webkit-scrollbar-track {
            background: rgba(var(--zinc-700), 0.3);
        }

        .dark .overflow-y-auto::-webkit-scrollbar-thumb {
            background: rgba(var(--zinc-500), 0.5);
            border-radius: 4px;
        }

        .dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: rgba(var(--zinc-500), 0.7);
        }

        /* Code Block Syntax Highlighting */
        pre[class*="language-"] {
            margin: 0;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .dark pre[class*="language-"] {
            background: rgba(var(--zinc-900), 0.8) !important;
        }

        /* Form Elements */
        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            border-color: var(--indigo-500);
            box-shadow: 0 0 0 3px rgba(var(--indigo-100), 0.3);
        }

        .dark .form-input:focus,
        .dark .form-textarea:focus,
        .dark .form-select:focus {
            border-color: var(--indigo-400);
            box-shadow: 0 0 0 3px rgba(var(--indigo-400), 0.3);
        }

        /* Copy Success Animation */
        .copy-success {
            animation: fade-in-up 0.3s ease-out;
        }

        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Enhanced Delete Confirmation */
        .delete-confirm-modal {
            animation: scale-fade-in 0.2s ease-out;
        }

        @keyframes scale-fade-in {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
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
        integrity="sha512-jwrwRWZAbkLEMLrbzLytL9BIJM8/1MvSknYZLHI501BHP+2KqS6Kk3tL9CHJDsF5Lj49Xh87jTmT9AXW/1h0DQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"
        integrity="sha512-jBiL8rLpA/nR/fN3h+Gk9x3jdgX9o8ZbbX5J7s+q+n1sQe5fMzy1b252b6E8v4v4BfX+HKfUUpiIXgmA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-xml-doc.min.js"
        integrity="sha512-PBrZ7p/w15J53sYyP4U81J81+M1L0jxqjF1Wp4z+3W8/94+5s0+Qd4h+biTXn7KAbwEB7GgX+ZNI7Q=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-csv.min.js"
        integrity="sha512-zVryyVVKpQW19+fJvljzY904/IFt0d41y5n1W1u3WsyN0f4o9s2Emtw0s44y+hAjX9t70y9b6qI+GvQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        document.addEventListener('alpine:init', () => {

            Alpine.data('testCaseView', () => ({
                activeTab: 'details',
                expandedScript: null,
                expandedData: null,
                confirmDelete: false,

                // Script modal state
                showScriptModal: false,
                scriptCreationMode: 'ai',
                scriptTab: 'input',
                scriptFramework: 'selenium-python',
                scriptFrameworkOptions: [{
                        value: 'selenium-python',
                        label: 'Selenium (Python)'
                    },
                    {
                        value: 'cypress',
                        label: 'Cypress (JavaScript)'
                    },
                    {
                        value: 'other',
                        label: 'Other Framework'
                    }
                ],
                scriptPrompt: '',
                scriptCodeContext: '',
                scriptFiles: [],
                scriptLoading: false,
                scriptError: null,
                scriptResponse: null,
                scriptContent: '',
                scriptName: '',
                scriptGenerationHistory: [],

                // Data modal state
                showDataModal: false,
                dataCreationMode: 'ai',
                dataTab: 'input',
                dataFormat: 'json',
                dataFormatOptions: [{
                        value: 'json',
                        label: 'JSON'
                    },
                    {
                        value: 'csv',
                        label: 'CSV'
                    },
                    {
                        value: 'xml',
                        label: 'XML'
                    },
                    {
                        value: 'plain',
                        label: 'Plain Text'
                    },
                    {
                        value: 'other',
                        label: 'Other Format'
                    }
                ],
                dataPrompt: '',
                dataReferenceScript: '',
                dataStructure: '',
                dataExample: '',
                dataFiles: [],
                dataLoading: false,
                dataError: null,
                dataResponse: null,
                dataContent: '',
                dataName: '',
                dataUsageContext: '',
                dataIsSensitive: false,
                dataGenerationHistory: [],

                // Edit modal state
                showEditScriptModal: false,
                showEditDataModal: false,
                editScript: null,
                editData: null,
                editScriptName: '',
                editScriptContent: '',
                editScriptFramework: '',
                editDataName: '',
                editDataContent: '',
                editDataFormat: '',
                editDataUsageContext: '',
                editDataIsSensitive: false,

                init() {
                    // Load AI history from localStorage
                    this.loadGenerationHistory();

                    // Initialize the UI
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                        this.highlightCode();
                    });

                    // Fix form styling for modals
                    this.$watch('showScriptModal', (value) => {
                        if (value) {
                            this.$nextTick(() => {
                                document.querySelectorAll(
                                        '.form-input, .form-textarea, .form-select')
                                    .forEach(el => {
                                        el.classList.add('bg-white',
                                            'dark:bg-zinc-700', 'text-zinc-900',
                                            'dark:text-zinc-100');
                                    });
                            });
                        }
                    });

                    this.$watch('showDataModal', (value) => {
                        if (value) {
                            this.$nextTick(() => {
                                document.querySelectorAll(
                                        '.form-input, .form-textarea, .form-select')
                                    .forEach(el => {
                                        el.classList.add('bg-white',
                                            'dark:bg-zinc-700', 'text-zinc-900',
                                            'dark:text-zinc-100');
                                    });
                            });
                        }
                    });
                },

                openDataModal() {
                    this.showDataModal = true;
                    this.dataCreationMode = 'ai';
                    this.dataTab = 'input';
                    this.dataError = null;
                    this.dataResponse = null;
                    this.dataContent = '';
                    this.dataName = '';
                    this.dataUsageContext = '';
                    this.dataIsSensitive = false;
                    this.dataFiles = [];

                    // Ensure proper styling
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });
                },

                openScriptModal() {
                    this.showScriptModal = true;
                    this.scriptCreationMode = 'ai';
                    this.scriptTab = 'input';
                    this.scriptError = null;
                    this.scriptResponse = null;
                    this.scriptContent = '';
                    this.scriptName = '';
                    this.scriptFiles = [];

                    // Ensure proper styling
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });
                },

                // Edit modal methods
                openEditScriptModal() {
                    const scriptId = this.editScript;
                    const script = @json($testScripts).find(s => s.id === scriptId);
                    if (!script) return;

                    this.editScriptName = script.name;
                    this.editScriptContent = script.script_content;
                    this.editScriptFramework = script.framework_type;
                    this.showEditScriptModal = true;

                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });
                },

                openEditDataModal() {
                    const dataId = this.editData;
                    const data = @json($testData).find(d => d.id === dataId);
                    if (!data) return;

                    this.editDataName = data.name;
                    this.editDataContent = data.content;
                    this.editDataFormat = data.format;
                    this.editDataUsageContext = data.pivot?.usage_context || '';
                    this.editDataIsSensitive = data.is_sensitive;
                    this.showEditDataModal = true;

                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });
                },

                async updateScript() {
                    const scriptId = this.editScript;
                    if (!scriptId) return;

                    try {
                        const response = await fetch(
                            `{{ route('dashboard.projects.test-cases.show', ['project' => $project->id, 'test_case' => $testCase->id]) }}/scripts/${scriptId}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    _method: 'PUT',
                                    name: this.editScriptName,
                                    framework_type: this.editScriptFramework,
                                    script_content: this.editScriptContent,
                                })
                            });

                        if (response.ok) {
                            this.showNotificationMessage('Script updated successfully!', 'success');
                            this.showEditScriptModal = false;
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            const error = await response.json();
                            throw new Error(error.message || 'Failed to update script');
                        }
                    } catch (error) {
                        console.error('Error updating script:', error);
                        this.showNotificationMessage('Failed to update script: ' + error.message,
                            'error');
                    }
                },

                async updateData() {
                    const dataId = this.editData;
                    if (!dataId) return;

                    try {
                        const response = await fetch(
                            `{{ route('dashboard.projects.test-cases.show', ['project' => $project->id, 'test_case' => $testCase->id]) }}/data/${dataId}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    _method: 'PUT',
                                    name: this.editDataName,
                                    format: this.editDataFormat,
                                    content: this.editDataContent,
                                    usage_context: this.editDataUsageContext,
                                    is_sensitive: this.editDataIsSensitive ? '1' : '0',
                                })
                            });

                        if (response.ok) {
                            this.showNotificationMessage('Test data updated successfully!',
                                'success');
                            this.showEditDataModal = false;
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            const error = await response.json();
                            throw new Error(error.message || 'Failed to update test data');
                        }
                    } catch (error) {
                        console.error('Error updating test data:', error);
                        this.showNotificationMessage('Failed to update test data: ' + error.message,
                            'error');
                    }
                },

                // New methods for AI history
                loadGenerationHistory() {
                    try {
                        const savedScriptHistory = localStorage.getItem('script_generation_history');
                        if (savedScriptHistory) {
                            this.scriptGenerationHistory = JSON.parse(savedScriptHistory);
                        }
                    } catch (e) {
                        console.error('Failed to parse generation history:', e);
                        this.scriptGenerationHistory = [];
                    }
                },

                formatTime(timestamp) {
                    return new Date(timestamp).toLocaleString();
                },

                // Old helpers
                getLabel(optionsArray, value) {
                    const option = optionsArray.find(opt => opt.value === value);
                    return option ? option.label : 'Select...';
                },

                selectOption(optionType, value, dropdownFlag) {
                    this[optionType] = value;
                    this[dropdownFlag] = false;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                        this.highlightCode();
                    });
                },

                setActiveTab(tab) {
                    this.activeTab = tab;
                },

                highlightCode() {
                    this.$nextTick(() => {
                        if (typeof Prism !== 'undefined') {
                            Prism.highlightAll();
                        }
                    });
                },

                toggleScript(id) {
                    this.expandedScript = this.expandedScript === id ? null : id;
                    if (this.expandedScript === id) {
                        this.highlightCode();
                    }
                },

                toggleData(id) {
                    this.expandedData = this.expandedData === id ? null : id;
                    if (this.expandedData === id) {
                        this.highlightCode();
                    }
                },

                copyToClipboard(text, type = 'Content') {
                    navigator.clipboard.writeText(text).then(
                        () => this.showNotificationMessage(`${type} copied to clipboard!`,
                            'success'),
                        (err) => this.showNotificationMessage(`Failed to copy ${type}: ${err}`,
                            'error')
                    );
                },

                refreshPage() {
                    window.location.reload();
                },

                showNotificationMessage(message, type = 'success') {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            message,
                            type
                        }
                    }));
                },

                async generateScript() {
                    if (!this.scriptPrompt) {
                        this.scriptError = 'Please enter a prompt';
                        return;
                    }

                    this.scriptError = null;
                    this.scriptLoading = true;

                    try {
                        const context = {
                            project_id: '{{ $project->id }}',
                            test_case_id: '{{ $testCase->id }}',
                            framework_type: this.scriptFramework,
                            code: this.scriptCodeContext || undefined
                        };

                        const response = await fetch(
                            '{{ route('api.ai.generate', 'test-script') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    prompt: this.scriptPrompt,
                                    context
                                })
                            });

                        // Check if response is ok
                        if (!response.ok) {
                            const errorText = await response.text();
                            console.error('Server error:', errorText);
                            this.scriptError = `Server error: ${response.status}`;
                            throw new Error(`Server error: ${response.status}`);
                        }
                        // Try to parse JSON
                        const result = await response.json();

                        if (result.success) {
                            this.scriptResponse = result.data;
                            this.scriptContent = result.data.content || "";
                            this.scriptName = result.data.name ||
                                `{{ $testCase->title }} - ${this.getScriptFrameworkLabel()} Script`;
                            this.scriptTab = 'output';
                            this.$nextTick(() => this.highlightCode());
                        } else {
                            throw new Error(result.message || 'Failed to generate script');
                        }
                    } catch (error) {
                        console.error('Script generation error:', error);
                        // If it's a parsing error, get the actual response text
                        if (error.message.includes('is not valid JSON')) {
                            try {
                                const responseText = await response.text();
                                console.error('Raw response:', responseText);
                            } catch (e) {
                                console.error('Could not get response text:', e);
                            }
                        }
                        this.scriptError = error.message || 'An error occurred during generation';
                    } finally {
                        this.scriptLoading = false;
                    }
                },
                regenerateScript() {
                    this.scriptTab = 'input';
                    this.generateScript();
                },

                copyScriptToClipboard() {
                    navigator.clipboard.writeText(this.scriptContent).then(
                        () => this.showNotificationMessage('Script copied to clipboard!',
                            'success'),
                        (err) => this.showNotificationMessage('Failed to copy script: ' + err,
                            'error')
                    );
                },

                getScriptFrameworkLabel() {
                    const option = this.scriptFrameworkOptions.find(opt => opt.value === this
                        .scriptFramework);
                    return option ? option.label : 'Script';
                },

                // Add item to script generation history
                addToScriptHistory(item) {
                    this.scriptGenerationHistory = [item, ...this.scriptGenerationHistory].slice(0, 10);
                    localStorage.setItem('script_generation_history', JSON.stringify(this
                        .scriptGenerationHistory));
                },

                // Use a script history item
                useScriptHistoryItem(index) {
                    const item = this.scriptGenerationHistory[index];
                    if (!item) return;

                    this.scriptPrompt = item.prompt;
                    this.scriptContent = item.content;

                    const frameworkOption = this.scriptFrameworkOptions.find(opt => opt.label === item
                        .framework);
                    if (frameworkOption) {
                        this.scriptFramework = frameworkOption.value;
                    }

                    this.scriptResponse = {
                        content: item.content
                    };
                    this.scriptName = `{{ $testCase->title }} - ${item.framework || 'Script'}`;
                    this.scriptTab = 'output';

                    this.$nextTick(() => this.highlightCode());
                },

                // Use a script template
                useScriptTemplate(type) {
                    const frameworkLabel = this.getScriptFrameworkLabel();

                    switch (type) {
                        case 'basic':
                            this.scriptPrompt =
                                `Generate a simple ${frameworkLabel} test script that verifies all the basic functionality described in the test case steps. The script should handle setup, test execution, and teardown.`;
                            break;
                        case 'detailed':
                            this.scriptPrompt =
                                `Generate a comprehensive ${frameworkLabel} test script with detailed assertions, error handling, and documentation. Include proper setup and teardown, and add comments explaining the key parts of the code. Handle edge cases and potential failures.`;
                            break;
                    }
                },
                handleScriptFileUpload(event) {
                    const files = event.target.files;
                    if (!files || !files.length) return;

                    if (this.scriptFiles.length < 5) {
                        this.scriptFiles.push(files[0]);

                        if (files[0].type.includes('text') || files[0].name.match(
                                /\.(py|js|json|css|html|xml|csv|txt)$/i)) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                const fileName = files[0].name;
                                const fileContent = e.target.result;
                                this.scriptCodeContext +=
                                    `\n\n// File: ${fileName}\n${fileContent}`;
                            };
                            reader.readAsText(files[0]);
                        }
                        event.target.value = null;
                    }
                },
                // Remove a file from the list
                removeScriptFile(index) {
                    this.scriptFiles.splice(index, 1);
                },

                handleDataFileUpload(event) {
                    const files = event.target.files;
                    if (!files || !files.length) return;

                    // Add the file to our array (up to 5 files)
                    if (this.dataFiles.length < 5) {
                        this.dataFiles.push(files[0]);

                        // Read the file content if it's a text file
                        if (files[0].type.includes('text') || files[0].name.match(
                                /\.(json|csv|xml|txt|py|js)$/i)) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                // Add file content to context
                                const fileName = files[0].name;
                                const fileContent = e.target.result;
                                this.dataExample += `\n\n// File: ${fileName}\n${fileContent}`;
                            };
                            reader.readAsText(files[0]);
                        }

                        // Reset the file input to allow selecting the same file again
                        event.target.value = null;
                    }
                },

                // Remove a file from the list
                removeDataFile(index) {
                    this.dataFiles.splice(index, 1);
                },

                // --- AI Test Data Generation ---
                async generateData() {
                    if (!this.dataPrompt) {
                        this.dataError = 'Please enter a prompt';
                        return;
                    }

                    this.dataError = null;
                    this.dataLoading = true;

                    try {
                        const context = {
                            project_id: '{{ $project->id }}',
                            test_case_id: '{{ $testCase->id }}',
                            format: this.dataFormat
                        };

                        if (this.dataStructure) context.data_structure = this.dataStructure;
                        if (this.dataExample) context.example_data = this.dataExample;
                        if (this.dataReferenceScript) context.script_id = this.dataReferenceScript;

                        const response = await fetch('/api/ai/generate/test-data', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                prompt: this.dataPrompt,
                                context
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.dataResponse = result.data;
                            this.dataContent = result.data.content;
                            this.dataName =
                                `{{ $testCase->title }} - ${this.getFormatLabel()} Data`;
                            this.dataUsageContext = 'AI Generated Test Data';

                            this.dataTab = 'output';
                            this.$nextTick(() => this.highlightCode());
                        } else {
                            throw new Error(result.message || 'Failed to generate test data');
                        }
                    } catch (error) {
                        console.error('Data generation error:', error);
                        this.dataError = error.message || 'An error occurred during generation';
                    } finally {
                        this.dataLoading = false;
                    }
                },

                regenerateData() {
                    this.aiDataTab = 'input';
                    this.generateData();
                },

                copyDataToClipboard() {
                    navigator.clipboard.writeText(this.aiDataContent).then(
                        () => this.showNotificationMessage('Data copied to clipboard!', 'success'),
                        (err) => this.showNotificationMessage('Failed to copy data: ' + err,
                            'error')
                    );
                },

                getFormatLabel() {
                    const option = this.dataFormatOptions.find(opt => opt.value === this.dataFormat);
                    return option ? option.label : 'Data';
                },

                addToDataHistory(item) {
                    this.dataGenerationHistory = [item, ...this.dataGenerationHistory].slice(0, 10);
                    localStorage.setItem('data_generation_history', JSON.stringify(this
                        .dataGenerationHistory));
                },

                useDataHistoryItem(index) {
                    const item = this.dataGenerationHistory[index];
                    if (!item) return;
                    this.aiDataPrompt = item.prompt;
                    this.aiDataContent = item.content;
                    const formatOption = this.aiDataFormatOptions.find(opt => opt.label === item
                        .format);
                    if (formatOption) {
                        this.aiDataFormat = formatOption.value;
                    }
                    this.aiDataResponse = {
                        content: item.content
                    };
                    this.aiDataName = `{{ $testCase->title }} - ${item.format || 'Data'}`;
                    this.aiDataUsageContext = 'AI Generated Test Data';
                    this.aiDataTab = 'output';
                    this.$nextTick(() => this.highlightCode());
                },

                useDataTemplate(type) {
                    const formatName = this.getFormatLabel();
                    switch (type) {
                        case 'valid':
                            this.aiDataPrompt =
                                `Generate valid test data in ${formatName} format for this test case. Include all required fields and ensure the data matches the expected format.`;
                            break;
                        case 'invalid':
                            this.aiDataPrompt =
                                `Generate invalid test data in ${formatName} format for this test case. Create data that should fail validation with different types of errors (missing required fields, invalid formats, out of range values, etc.).`;
                            break;
                        case 'mixed':
                            this.aiDataPrompt =
                                `Generate a comprehensive set of test data in ${formatName} format, including both valid and invalid examples. Label or organize them clearly to distinguish between the different types.`;
                            break;
                        case 'edge':
                            this.aiDataPrompt =
                                `Generate edge case test data in ${formatName} format for this test case. Include boundary values, minimum/maximum values, empty strings, and other edge cases that should be tested.`;
                            break;
                    }
                },


                // Regenerate the data with the same parameters
                regenerateData() {
                    this.dataTab = 'input';
                    this.generateData();
                },

                // Copy the generated data to clipboard
                copyDataToClipboard() {
                    navigator.clipboard.writeText(this.dataContent).then(
                        () => this.showNotificationMessage('Data copied to clipboard!', 'success'),
                        (err) => this.showNotificationMessage('Failed to copy data: ' + err,
                            'error')
                    );
                },

                // Get the label of the current format
                getDataFormatLabel() {
                    const option = this.dataFormatOptions.find(opt => opt.value === this.dataFormat);
                    return option ? option.label : 'Data';
                },

                // Add item to data generation history
                addToDataHistory(item) {
                    this.dataGenerationHistory = [item, ...this.dataGenerationHistory].slice(0, 10);
                    localStorage.setItem('data_generation_history', JSON.stringify(this
                        .dataGenerationHistory));
                },

                // Use a data history item
                useDataHistoryItem(index) {
                    const item = this.dataGenerationHistory[index];
                    if (!item) return;

                    this.dataPrompt = item.prompt;
                    this.dataContent = item.content;

                    const formatOption = this.dataFormatOptions.find(opt => opt.label === item.format);
                    if (formatOption) {
                        this.dataFormat = formatOption.value;
                    }

                    this.dataResponse = {
                        content: item.content
                    };

                    this.dataName = `{{ $testCase->title }} - ${item.format || 'Data'}`;
                    this.dataUsageContext = 'AI Generated Test Data';
                    this.dataTab = 'output';

                    this.$nextTick(() => this.highlightCode());
                },

                // Use a data template
                useDataTemplate(type) {
                    const formatName = this.getDataFormatLabel();

                    switch (type) {
                        case 'valid':
                            this.dataPrompt =
                                `Generate valid test data in ${formatName} format for this test case. Include all required fields and ensure the data matches the expected format.`;
                            break;
                        case 'invalid':
                            this.dataPrompt =
                                `Generate invalid test data in ${formatName} format for this test case. Create data that should fail validation with different types of errors (missing required fields, invalid formats, out of range values, etc.).`;
                            break;
                        case 'mixed':
                            this.dataPrompt =
                                `Generate a comprehensive set of test data in ${formatName} format, including both valid and invalid examples. Label or organize them clearly to distinguish between the different types.`;
                            break;
                        case 'edge':
                            this.dataPrompt =
                                `Generate edge case test data in ${formatName} format for this test case. Include boundary values, minimum/maximum values, empty strings, and other edge cases that should be tested.`;
                            break;
                    }
                },

                async saveScript() {
                    if (!this.scriptContent || !this.scriptName) {
                        this.showNotificationMessage(
                            'Please provide a name and ensure the script has content', 'error');
                        return;
                    }

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]');
                        if (!csrfToken) {
                            throw new Error('CSRF token not found');
                        }

                        const payload = {
                            name: this.scriptName,
                            framework_type: this.scriptFramework,
                            script_content: this.scriptContent,
                            metadata: this.scriptCreationMode === 'ai' ? {
                                created_through: 'ai',
                                prompt: this.scriptPrompt
                            } : {
                                created_through: 'manual'
                            }
                        };

                        const response = await fetch(
                            '{{ route('dashboard.projects.test-cases.scripts.store', [$project->id, $testCase->id]) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify(payload)
                            });

                        if (response.ok) {
                            this.showNotificationMessage('Script saved successfully!', 'success');
                            this.showScriptModal = false;
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            const errorData = await response.json();
                            throw new Error(errorData.message || 'Failed to save script');
                        }
                    } catch (error) {
                        console.error('Error saving script:', error);
                        this.showNotificationMessage('Failed to save script: ' + error.message,
                            'error');
                    }
                },


                // Save the data (works for both AI-generated and manual)
                async saveData() {
                    if (!this.dataContent || !this.dataName || !this.dataUsageContext) {
                        this.showNotificationMessage(
                            'Please provide a name, usage context, and ensure the data has content',
                            'error');
                        return;
                    }

                    try {
                        const formData = new FormData();
                        formData.append('name', this.dataName);
                        formData.append('format', this.dataFormat);
                        formData.append('content', this.dataContent);
                        formData.append('usage_context', this.dataUsageContext);
                        formData.append('is_sensitive', this.dataIsSensitive ? '1' : '0');
                        formData.append('_token', '{{ csrf_token() }}');

                        if (this.dataCreationMode === 'ai') {
                            formData.append('metadata[created_through]', 'ai');
                            formData.append('metadata[prompt]', this.dataPrompt);
                        } else {
                            formData.append('metadata[created_through]', 'manual');
                        }

                        // Fix the route construction - same pattern as the script
                        const route =
                            "{{ route('dashboard.projects.test-cases.data.store', ['project' => '__PROJECT__', 'test_case' => '__TEST_CASE__']) }}";
                        const finalRoute = route
                            .replace('__PROJECT__', "{{ $project->id }}")
                            .replace('__TEST_CASE__', "{{ $testCase->id }}");

                        const response = await fetch(finalRoute, {
                            method: 'POST',
                            body: formData
                        });

                        if (response.ok) {
                            this.showNotificationMessage('Test data saved successfully!',
                                'success');
                            this.showDataModal = false;
                            window.location.reload();
                        } else {
                            const error = await response.json();
                            throw new Error(error.message || 'Failed to save test data');
                        }
                    } catch (error) {
                        console.error('Error saving test data:', error);
                        this.showNotificationMessage('Failed to save test data: ' + error.message,
                            'error');
                    }
                },


            }));

            // Notification component remains unchanged
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
