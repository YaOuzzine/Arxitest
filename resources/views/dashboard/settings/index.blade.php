@extends('layouts.dashboard')

@section('title', 'Settings')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Settings</span>
    </li>
@endsection

@section('content')
    <div x-data="settingsManager()" class="h-full space-y-6 animate-fade-in">
        <!-- Header with Stats Dashboard -->
        <div
            class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white flex items-center">
                    <i data-lucide="settings" class="w-6 h-6 mr-2 text-indigo-500"></i>
                    Settings Dashboard
                </h1>
                <p class="text-zinc-600 dark:text-zinc-400 mt-1">
                    Configure and customize Arxitest for your team: {{ $team->name }}
                </p>
            </div>

            <!-- Stats Cards - Using Real Data -->
            <div class="grid grid-cols-2 md:grid-cols-5 border-t border-zinc-200 dark:border-zinc-700">
                <div class="p-4 text-center border-r border-zinc-200 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Projects</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ $stats->projectCount }}</p>
                </div>
                <div class="p-4 text-center border-r border-zinc-200 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Test Suites</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $stats->testSuiteCount }}</p>
                </div>
                <div class="p-4 text-center border-r border-zinc-200 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Test Cases</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $stats->testCaseCount }}</p>
                </div>
                <div class="p-4 text-center border-r border-zinc-200 dark:border-zinc-700">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Executions</p>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $stats->executionsCount }}</p>
                </div>
                <div class="p-4 text-center">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Team Members</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $stats->teamUserCount }}</p>
                </div>
            </div>
        </div>

        <!-- Settings Navigation Tabs -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="border-b border-zinc-200 dark:border-zinc-700">
                <nav class="flex overflow-x-auto py-2 px-4" aria-label="Settings tabs">
                    <button @click="setActiveTab('app')"
                        :class="{
                            'text-indigo-600 dark:text-indigo-400 border-indigo-500': activeTab === 'app',
                            'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 border-transparent hover:border-zinc-300': activeTab !== 'app'
                        }"
                        class="px-4 py-2 font-medium text-sm border-b-2 transition-colors whitespace-nowrap">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 inline-block mr-1"></i>
                        App Settings
                    </button>
                    <button @click="setActiveTab('test-execution')"
                        :class="{
                            'text-indigo-600 dark:text-indigo-400 border-indigo-500': activeTab === 'test-execution',
                            'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 border-transparent hover:border-zinc-300': activeTab !== 'test-execution'
                        }"
                        class="px-4 py-2 font-medium text-sm border-b-2 transition-colors whitespace-nowrap">
                        <i data-lucide="play-circle" class="w-4 h-4 inline-block mr-1"></i>
                        Test Execution
                    </button>
                    <button @click="setActiveTab('ai-config')"
                        :class="{
                            'text-indigo-600 dark:text-indigo-400 border-indigo-500': activeTab === 'ai-config',
                            'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 border-transparent hover:border-zinc-300': activeTab !== 'ai-config'
                        }"
                        class="px-4 py-2 font-medium text-sm border-b-2 transition-colors whitespace-nowrap">
                        <i data-lucide="brain-circuit" class="w-4 h-4 inline-block mr-1"></i>
                        AI Configuration
                    </button>
                </nav>
            </div>

            <!-- Tab Content Sections -->
            <div class="p-6">
                <!-- Success Message -->
                @if (session('success'))
                    <div
                        class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-lg border border-green-200 dark:border-green-800/50">
                        <div class="flex">
                            <i data-lucide="check-circle" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                <!-- App Settings Tab - Using Real Data -->
                <div x-show="activeTab === 'app'" x-cloak>
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4">Application Settings</h2>
                    <form action="{{ route('dashboard.settings.app') }}" method="POST" class="space-y-6"
                        id="app-settings-form">
                        @csrf

                        <!-- UI Preferences -->
                        <div
                            class="bg-zinc-50 dark:bg-zinc-800/60 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <h3 class="text-md font-medium text-zinc-900 dark:text-white mb-3">UI Preferences</h3>

                            <div class="grid md:grid-cols-2 gap-6">
                                <!-- Theme Selection -->
                                <div>
                                    <label for="theme"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Theme</label>
                                    <select id="theme" name="theme"
                                        class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="light" {{ $preferences['theme'] == 'light' ? 'selected' : '' }}>
                                            Light</option>
                                        <option value="dark" {{ $preferences['theme'] == 'dark' ? 'selected' : '' }}>Dark
                                        </option>
                                        <option value="system" {{ $preferences['theme'] == 'system' ? 'selected' : '' }}>
                                            System Default</option>
                                    </select>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Choose the appearance of the
                                        application interface</p>
                                </div>

                                <!-- AI Assistant -->
                                <div>
                                    <label for="aiEnabled"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">AI
                                        Assistant</label>
                                    <div class="flex items-center space-x-2">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="aiEnabled" value="1"
                                                class="h-4 w-4 text-indigo-600 border-zinc-300 dark:border-zinc-600 focus:ring-indigo-500"
                                                {{ $preferences['aiEnabled'] ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">Enabled</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="aiEnabled" value="0"
                                                class="h-4 w-4 text-indigo-600 border-zinc-300 dark:border-zinc-600 focus:ring-indigo-500"
                                                {{ !$preferences['aiEnabled'] ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">Disabled</span>
                                        </label>
                                    </div>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Enable or disable AI-assisted
                                        features throughout the application</p>
                                </div>
                            </div>
                        </div>

                        <!-- Default Settings -->
                        <div
                            class="bg-zinc-50 dark:bg-zinc-800/60 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <h3 class="text-md font-medium text-zinc-900 dark:text-white mb-3">Default Test Settings</h3>

                            <div class="grid md:grid-cols-2 gap-6">
                                <!-- Default Test Framework -->
                                <div>
                                    <label for="defaultTestFramework"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Default Test
                                        Framework</label>
                                    <select id="defaultTestFramework" name="defaultTestFramework"
                                        class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="selenium-python"
                                            {{ $preferences['defaultTestFramework'] == 'selenium-python' ? 'selected' : '' }}>
                                            Selenium (Python)</option>
                                        <option value="cypress"
                                            {{ $preferences['defaultTestFramework'] == 'cypress' ? 'selected' : '' }}>
                                            Cypress</option>
                                        <option value="other"
                                            {{ $preferences['defaultTestFramework'] == 'other' ? 'selected' : '' }}>Other
                                        </option>
                                    </select>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Default framework for new test
                                        script generation</p>
                                </div>

                                <!-- Default Test Priority -->
                                <div>
                                    <label for="defaultTestPriority"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Default
                                        Test Priority</label>
                                    <select id="defaultTestPriority" name="defaultTestPriority"
                                        class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="low"
                                            {{ $preferences['defaultTestPriority'] == 'low' ? 'selected' : '' }}>Low
                                        </option>
                                        <option value="medium"
                                            {{ $preferences['defaultTestPriority'] == 'medium' ? 'selected' : '' }}>Medium
                                        </option>
                                        <option value="high"
                                            {{ $preferences['defaultTestPriority'] == 'high' ? 'selected' : '' }}>High
                                        </option>
                                    </select>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Default priority level for new
                                        test cases</p>
                                </div>

                                <!-- Default Execution Mode -->
                                <div>
                                    <label for="defaultExecutionMode"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Default
                                        Execution Mode</label>
                                    <select id="defaultExecutionMode" name="defaultExecutionMode"
                                        class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="sequential"
                                            {{ $preferences['defaultExecutionMode'] == 'sequential' ? 'selected' : '' }}>
                                            Sequential</option>
                                        <option value="parallel"
                                            {{ $preferences['defaultExecutionMode'] == 'parallel' ? 'selected' : '' }}>
                                            Parallel</option>
                                    </select>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Default execution strategy for
                                        test runs</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary">
                                <i data-lucide="save" class="w-4 h-4 mr-1"></i>
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Test Execution Tab - Using Real Data -->
                <div x-show="activeTab === 'test-execution'" x-cloak>
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4">Test Execution Settings</h2>
                    <form action="{{ route('dashboard.settings.test-execution') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Execution Parameters -->
                        <div
                            class="bg-zinc-50 dark:bg-zinc-800/60 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <h3 class="text-md font-medium text-zinc-900 dark:text-white mb-3">Container & Timeout Settings
                            </h3>

                            <div class="grid md:grid-cols-2 gap-6">
                                <!-- Container Timeout -->
                                <div>
                                    <label for="containerTimeout"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Container
                                        Timeout (seconds)</label>
                                    <input type="number" id="containerTimeout" name="containerTimeout"
                                        value="{{ $preferences['containerTimeout'] }}" min="60" max="3600"
                                        class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Maximum time a container can
                                        run (60-3600 seconds)</p>
                                </div>

                                <!-- Default Page Timeout -->
                                <div>
                                    <label for="defaultPageTimeout"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Default
                                        Page Timeout (seconds)</label>
                                    <input type="number" id="defaultPageTimeout" name="defaultPageTimeout"
                                        value="{{ $preferences['defaultPageTimeout'] }}" min="5" max="300"
                                        class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Default page load timeout for
                                        tests (5-300 seconds)</p>
                                </div>
                            </div>
                        </div>

                        <!-- Screenshot Settings -->
                        <div
                            class="bg-zinc-50 dark:bg-zinc-800/60 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <h3 class="text-md font-medium text-zinc-900 dark:text-white mb-3">Screenshot Settings</h3>

                            <div class="grid md:grid-cols-2 gap-6">
                                <!-- Screenshot Capture -->
                                <div>
                                    <label for="screenshotCapture"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Screenshot
                                        Capture</label>
                                    <select id="screenshotCapture" name="screenshotCapture"
                                        class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="always"
                                            {{ $preferences['screenshotCapture'] == 'always' ? 'selected' : '' }}>Always
                                            (Every Step)</option>
                                        <option value="failures-only"
                                            {{ $preferences['screenshotCapture'] == 'failures-only' ? 'selected' : '' }}>
                                            Failures Only</option>
                                        <option value="never"
                                            {{ $preferences['screenshotCapture'] == 'never' ? 'selected' : '' }}>Never
                                        </option>
                                    </select>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">When to capture screenshots
                                        during test execution</p>
                                </div>

                                <!-- Default Environment -->
                                <div>
                                    <label for="defaultEnvironment"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Default
                                        Environment</label>
                                    <select id="defaultEnvironment" name="defaultEnvironment"
                                        class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="development"
                                            {{ $preferences['defaultEnvironment'] == 'development' ? 'selected' : '' }}>
                                            Development</option>
                                        <option value="staging"
                                            {{ $preferences['defaultEnvironment'] == 'staging' ? 'selected' : '' }}>Staging
                                        </option>
                                        <option value="production"
                                            {{ $preferences['defaultEnvironment'] == 'production' ? 'selected' : '' }}>
                                            Production</option>
                                    </select>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Default environment for test
                                        execution</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary">
                                <i data-lucide="save" class="w-4 h-4 mr-1"></i>
                                Save Test Execution Settings
                            </button>
                        </div>
                    </form>
                </div>

                <!-- AI Configuration Tab -->
                <div x-show="activeTab === 'ai-config'" x-cloak>
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4">AI Configuration</h2>
                    <p class="text-zinc-600 dark:text-zinc-400 mb-6">Configure the AI models and settings used for test
                        generation and assistance.</p>

                    <div class="p-12 text-center border border-dashed border-zinc-300 dark:border-zinc-700 rounded-lg">
                        <i data-lucide="brain-circuit"
                            class="w-12 h-12 mx-auto mb-4 text-zinc-400 dark:text-zinc-500"></i>
                        <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200 mb-2">AI Configuration Coming Soon
                        </h3>
                        <p class="text-zinc-600 dark:text-zinc-400 max-w-md mx-auto">
                            Advanced AI configuration options will be available in an upcoming update.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Animated popup effect */
        @keyframes popIn {
            0% {
                opacity: 0;
                transform: scale(0.95) translateY(10px);
            }

            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .animate-pop-in {
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            opacity: 0;
            animation-delay: var(--delay, 0s);
        }

        /* Settings tab transitions */
        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Alpine.js component for managing settings
        function settingsManager() {
            return {
                activeTab: 'app',

                setActiveTab(tabName) {
                    this.activeTab = tabName;
                },

                init() {
                    // Initialize components
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }

                    // Listen for the theme form submission
                    const themeForm = document.getElementById('app-settings-form');
                    if (themeForm) {
                        themeForm.addEventListener('submit', (e) => {
                            // Get the theme value
                            const themeSelect = document.getElementById('theme');
                            if (themeSelect) {
                                const theme = themeSelect.value;

                                // Apply theme immediately for better UX
                                if (theme === 'dark') {
                                    document.documentElement.classList.add('dark');
                                } else if (theme === 'light') {
                                    document.documentElement.classList.remove('dark');
                                } else if (theme === 'system') {
                                    // Use system preference
                                    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                                        document.documentElement.classList.add('dark');
                                    } else {
                                        document.documentElement.classList.remove('dark');
                                    }
                                }
                            }
                        });
                    }
                }
            }
        }
    </script>
@endpush
