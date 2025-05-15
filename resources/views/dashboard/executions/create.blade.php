<!-- resources/views/dashboard/executions/create.blade.php -->
@extends('layouts.dashboard')

@section('title', 'Create Test Execution')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.executions.index') }}"
            class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors">
            Test Executions
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Create</span>
    </li>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto" x-data="createExecution()">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">Run Test Execution</h1>
            <p class="text-zinc-500 dark:text-zinc-400 transition-colors">
                Configure and start a new test execution run
            </p>
        </div>

        <!-- Form Card -->
        <div
            class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-zinc-200/50 dark:border-zinc-700/50 transition-all">
            <!-- Card Header -->
            <div
                class="px-8 py-6 border-b border-zinc-200/80 dark:border-zinc-700/50 bg-gradient-to-r from-indigo-50/20 to-purple-50/20 dark:from-zinc-800/50 dark:to-zinc-800/50">
                <h2 class="text-xl font-semibold text-zinc-800 dark:text-zinc-200 flex items-center gap-2">
                    <i data-lucide="settings-2" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i>
                    Execution Settings
                </h2>
            </div>

            <form action="{{ route('dashboard.executions.store') }}" method="POST" class="p-8 space-y-8">
                @csrf

                <!-- Project Selection -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Project <span
                            class="text-red-500">*</span></label>
                    <input type="hidden" name="project_id" x-model="selectedProject">

                    <x-dropdown.index width="full" triggerClasses="w-full" x-data="{ open: false }">
                        <x-slot:trigger>
                            <div
                                class="w-full flex items-center justify-between px-4 py-3 border border-zinc-300/80 dark:border-zinc-600 rounded-xl bg-white dark:bg-zinc-800/90 text-zinc-900 dark:text-zinc-200 shadow-sm cursor-pointer hover:border-indigo-400 dark:hover:border-indigo-400 transition-all duration-200">
                                <span x-text="selectedProjectName || 'Select a project'" class="truncate"></span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400 transition-transform"></i>
                            </div>
                        </x-slot:trigger>

                        <x-slot:content>
                            <div class="max-h-60 overflow-y-auto space-y-1">
                                @foreach ($projects as $project)
                                    <x-dropdown.item
                                        @click="selectProject('{{ $project->id }}', '{{ $project->name }}'); open = false"
                                        class="group hover:bg-indigo-50/50 dark:hover:bg-indigo-500/20 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-2 h-2 rounded-full bg-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                            </div>
                                            <span
                                                class="text-zinc-700 dark:text-zinc-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                                {{ $project->name }}
                                            </span>
                                        </div>
                                    </x-dropdown.item>
                                @endforeach
                            </div>
                        </x-slot:content>
                    </x-dropdown.index>

                    @error('project_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Test Script Selection -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Test Script <span
                            class="text-red-500">*</span></label>
                    <input type="hidden" name="script_id" x-model="selectedScript">

                    <x-dropdown.index width="full" triggerClasses="w-full">
                        <x-slot:trigger>
                            <div class="w-full flex items-center justify-between px-4 py-3 border border-zinc-300/80 dark:border-zinc-600 rounded-xl bg-white dark:bg-zinc-800/90 text-zinc-900 dark:text-zinc-200 shadow-sm cursor-pointer hover:border-indigo-400 dark:hover:border-indigo-400 transition-all duration-200"
                                :class="{ 'opacity-50': !selectedProject }">
                                <span x-text="selectedScriptName || 'Select a test script'" class="truncate"></span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400 transition-transform"></i>
                                <div x-show="isLoadingScripts" class="absolute inset-y-0 right-10 flex items-center">
                                    <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                        </x-slot:trigger>

                        <x-slot:content>
                            <div class="max-h-60 overflow-y-auto space-y-1">
                                <!-- No project selected message -->
                                <div x-show="!selectedProject"
                                    class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400 text-center">
                                    Please select a project first
                                </div>

                                <!-- Loading indicator -->
                                <div x-show="selectedProject && isLoadingScripts"
                                    class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400 text-center">
                                    <svg class="animate-spin h-5 w-5 mx-auto mb-2 text-indigo-500"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Loading scripts...
                                </div>

                                <!-- No scripts available message -->
                                <div x-show="selectedProject && !isLoadingScripts && scripts.length === 0"
                                    class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400 text-center">
                                    No scripts available for this project
                                </div>

                                <!-- Dynamic script list -->
                                <template x-for="script in scripts" :key="script.id">
                                    <div @click="selectScript(script.id, script.name, script.framework_type, script.test_case?.title || 'No Test Case'); $parent.open = false"
                                        class="px-4 py-2.5 cursor-pointer hover:bg-indigo-50/50 dark:hover:bg-indigo-500/20 transition-colors group">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-2 h-2 rounded-full bg-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                            </div>
                                            <div class="flex flex-col">
                                                <span
                                                    class="text-zinc-700 dark:text-zinc-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors"
                                                    x-text="script.name"></span>
                                                <span class="text-xs text-zinc-500 dark:text-zinc-400"
                                                    x-text="'Framework: ' + script.framework_type"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </x-slot:content>
                    </x-dropdown.index>

                    @error('script_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Script Details Preview -->
                <div x-show="selectedScript" x-collapse
                    class="bg-indigo-50/30 dark:bg-indigo-500/10 rounded-xl p-5 border border-indigo-200/50 dark:border-indigo-500/20 space-y-3 transition-all duration-300">
                    <h3 class="text-sm font-semibold text-indigo-700 dark:text-indigo-400 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        Script Details
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div
                            class="flex items-center gap-2 bg-white dark:bg-zinc-700/30 p-3 rounded-lg border border-zinc-200/50 dark:border-zinc-600/50">
                            <div class="flex-1">
                                <p class="text-zinc-500 dark:text-zinc-400 text-xs">Framework Type</p>
                                <p class="font-medium text-zinc-800 dark:text-zinc-200" x-text="selectedFramework"></p>
                            </div>
                            <i data-lucide="box" class="w-5 h-5 text-indigo-500"></i>
                        </div>
                        <div
                            class="flex items-center gap-2 bg-white dark:bg-zinc-700/30 p-3 rounded-lg border border-zinc-200/50 dark:border-zinc-600/50">
                            <div class="flex-1">
                                <p class="text-zinc-500 dark:text-zinc-400 text-xs">Test Case</p>
                                <p class="font-medium text-zinc-800 dark:text-zinc-200" x-text="selectedTestCase"></p>
                            </div>
                            <i data-lucide="list-checks" class="w-5 h-5 text-indigo-500"></i>
                        </div>
                    </div>
                </div>

                <!-- Environment Selection -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Environment <span
                            class="text-red-500">*</span></label>
                    <input type="hidden" name="environment_id" x-model="selectedEnvironment">

                    <x-dropdown.index width="full" triggerClasses="w-full" x-data="{ open: false }">
                        <x-slot:trigger>
                            <div
                                class="w-full flex items-center justify-between px-4 py-3 border border-zinc-300/80 dark:border-zinc-600 rounded-xl bg-white dark:bg-zinc-800/90 text-zinc-900 dark:text-zinc-200 shadow-sm cursor-pointer hover:border-indigo-400 dark:hover:border-indigo-400 transition-all duration-200">
                                <span x-text="selectedEnvironmentName || 'Select an environment'" class="truncate"></span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400 transition-transform"></i>
                            </div>
                        </x-slot:trigger>

                        <x-slot:content>
                            <div class="max-h-60 overflow-y-auto space-y-1">
                                @foreach ($environments as $environment)
                                    <x-dropdown.item
                                        @click="selectEnvironment('{{ $environment->id }}', '{{ $environment->name }}'); open = false"
                                        class="group hover:bg-indigo-50/50 dark:hover:bg-indigo-500/20 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-2 h-2 rounded-full bg-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                            </div>
                                            <span
                                                class="text-zinc-700 dark:text-zinc-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                                {{ $environment->name }} {{ $environment->is_global ? '(Global)' : '' }}
                                            </span>
                                        </div>
                                    </x-dropdown.item>
                                @endforeach
                            </div>
                        </x-slot:content>
                    </x-dropdown.index>

                    @error('environment_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Environment Details Preview -->
                <div x-show="selectedEnvironment" x-collapse
                    class="bg-emerald-50/30 dark:bg-emerald-500/10 rounded-xl p-5 border border-emerald-200/50 dark:border-emerald-500/20 transition-all duration-300">
                    <h3 class="text-sm font-semibold text-emerald-700 dark:text-emerald-400 flex items-center gap-2 mb-3">
                        <i data-lucide="server" class="w-4 h-4"></i>
                        Environment Variables
                    </h3>
                    <div class="overflow-x-auto rounded-lg border border-zinc-200/50 dark:border-zinc-600/50">
                        <table class="w-full divide-y divide-zinc-200/50 dark:divide-zinc-600/50">
                            <thead class="bg-zinc-50/50 dark:bg-zinc-700/30">
                                <tr class="text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">
                                    <th class="px-4 py-2.5">Key</th>
                                    <th class="px-4 py-2.5">Value</th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-zinc-200/50 dark:divide-zinc-600/50 bg-white dark:bg-zinc-800/30">
                                <template x-for="(value, key) in environmentVars" :key="key">
                                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-700/30 transition-colors">
                                        <td class="px-4 py-2.5 text-sm font-medium text-zinc-800 dark:text-zinc-200"
                                            x-text="key"></td>
                                        <td class="px-4 py-2.5 text-sm text-zinc-600 dark:text-zinc-400" x-text="value">
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="Object.keys(environmentVars).length === 0">
                                    <td colspan="2"
                                        class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400 text-center">
                                        No environment variables configured
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Execution Options -->
                <div class="space-y-6 pt-6 border-t border-zinc-200/50 dark:border-zinc-700/50">
                    <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200 flex items-center gap-2">
                        <i data-lucide="toggle-right" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        Execution Options
                    </h3>

                    <div class="flex flex-wrap gap-3">
                        <!-- Timeout Pill -->
                        <div class="relative">
                            <input type="hidden" name="enable_timeout" x-model="enableTimeout">
                            <button type="button" @click="enableTimeout = !enableTimeout"
                                :class="enableTimeout
                                    ?
                                    'bg-indigo-500/20 border-indigo-500/40 text-indigo-700 dark:text-indigo-300' :
                                    'bg-zinc-100/50 dark:bg-zinc-700/30 border-zinc-300/50 dark:border-zinc-600/50 text-zinc-600 dark:text-zinc-300'"
                                class="px-4 py-2 rounded-full border flex items-center gap-2 transition-all duration-200 hover:scale-[98%]">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                <span>Custom Timeout</span>
                                <div :class="enableTimeout ? 'bg-indigo-500' : 'bg-zinc-400 dark:bg-zinc-500'"
                                    class="w-2 h-2 rounded-full ml-1 transition-colors"></div>
                            </button>

                            <!-- Timeout Input -->
                            <div x-show="enableTimeout" x-collapse
                                class="absolute left-0 top-full mt-2 bg-white dark:bg-zinc-800 p-2 rounded-lg border border-zinc-200/50 dark:border-zinc-700/50 shadow-sm z-50">
                                <div class="flex items-center gap-2">
                                    <input type="number" name="timeout_minutes" id="timeout_minutes"
                                        class="w-20 px-3 py-1 rounded-lg border-zinc-300/50 dark:border-zinc-600/50 bg-transparent text-zinc-900 dark:text-zinc-200 shadow-sm focus:ring-1 focus:ring-indigo-500"
                                        min="1" max="60" value="10">
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">minutes</span>
                                </div>
                            </div>
                        </div>

                        <!-- Priority Pill -->
                        <input type="hidden" name="priority" x-model="highPriority">
                        <button type="button" @click="highPriority = !highPriority"
                            :class="highPriority
                                ?
                                'bg-red-500/20 border-red-500/40 text-red-700 dark:text-red-300' :
                                'bg-zinc-100/50 dark:bg-zinc-700/30 border-zinc-300/50 dark:border-zinc-600/50 text-zinc-600 dark:text-zinc-300'"
                            class="px-4 py-2 rounded-full border flex items-center gap-2 transition-all duration-200 hover:scale-[98%]">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                            <span>High Priority</span>
                            <div :class="highPriority ? 'bg-red-500' : 'bg-zinc-400 dark:bg-zinc-500'"
                                class="w-2 h-2 rounded-full ml-1 transition-colors"></div>
                        </button>

                        <!-- Notification Pill -->
                        <input type="hidden" name="notify_completion" x-model="notifyCompletion">
                        <button type="button" @click="notifyCompletion = !notifyCompletion"
                            :class="notifyCompletion
                                ?
                                'bg-emerald-500/20 border-emerald-500/40 text-emerald-700 dark:text-emerald-300' :
                                'bg-zinc-100/50 dark:bg-zinc-700/30 border-zinc-300/50 dark:border-zinc-600/50 text-zinc-600 dark:text-zinc-300'"
                            class="px-4 py-2 rounded-full border flex items-center gap-2 transition-all duration-200 hover:scale-[98%]">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                            <span>Notify on Completion</span>
                            <div :class="notifyCompletion ? 'bg-emerald-500' : 'bg-zinc-400 dark:bg-zinc-500'"
                                class="w-2 h-2 rounded-full ml-1 transition-colors"></div>
                        </button>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end gap-3 pt-8 border-t border-zinc-200/50 dark:border-zinc-700/50">
                    <a href="{{ route('dashboard.executions.index') }}"
                        class="px-5 py-2.5 bg-white dark:bg-zinc-700/30 border border-zinc-300/50 dark:border-zinc-600/50 rounded-xl text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50/50 dark:hover:bg-zinc-700/50 transition-colors duration-200 hover:scale-[98%]">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-xl hover:shadow-lg hover:shadow-indigo-500/20 transition-all duration-200 hover:scale-[98%] flex items-center gap-2">
                        <i data-lucide="play" class="w-4 h-4"></i>
                        Start Execution
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        @keyframes gradient-x {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .animate-gradient-x {
            background-size: 200% auto;
            animation: gradient-x 3s ease infinite;
        }

        [x-cloak] {
            display: none !important;
        }

        .shadow-xs {
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
    </style>
@endpush

@push('scripts')
    <script>
        function createExecution() {
            return {
                selectedProject: '{{ $selectedProjectId ?? '' }}',
                selectedProjectName: '',
                selectedScript: null,
                selectedScriptName: '',
                selectedFramework: '',
                selectedTestCase: '',
                selectedEnvironment: null,
                selectedEnvironmentName: '',
                environmentVars: {},
                enableTimeout: false,
                highPriority: false,
                notifyCompletion: true,
                scripts: [],
                isLoadingScripts: false,

                init() {
                    // If project is already selected (from query param), load its name
                    if (this.selectedProject) {
                        const projectSelect = document.querySelector('select[name="project_id"]');
                        if (projectSelect) {
                            const option = Array.from(projectSelect.options).find(opt => opt.value === this
                                .selectedProject);
                            if (option) {
                                this.selectedProjectName = option.textContent.trim();
                                this.loadScriptsForProject(this.selectedProject);
                            }
                        }
                    }
                },

                selectProject(id, name) {
                    this.selectedProject = id;
                    this.selectedProjectName = name;

                    // Reset script selection
                    this.selectedScript = null;
                    this.selectedScriptName = '';
                    this.selectedFramework = '';
                    this.selectedTestCase = '';

                    // Load scripts for this project
                    this.loadScriptsForProject(id);
                },

                async loadScriptsForProject(projectId) {
                    if (!projectId) return;

                    this.isLoadingScripts = true;
                    this.scripts = [];

                    try {
                        const response = await fetch(`/dashboard/api/projects/${projectId}/test-scripts`);
                        if (!response.ok) throw new Error('Failed to fetch scripts');

                        const data = await response.json();
                        if (data.success) {
                            this.scripts = data.scripts || [];
                        }
                    } catch (error) {
                        console.error('Error loading scripts:', error);
                        // Show error notification
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {
                                type: 'error',
                                message: `Failed to load scripts: ${error.message}`
                            }
                        }));
                    } finally {
                        this.isLoadingScripts = false;
                    }
                },

                selectScript(id, name, framework, testCase) {
                    this.selectedScript = id;
                    this.selectedScriptName = name;
                    this.selectedFramework = framework || 'Unknown';
                    // Don't display "No Test Case" in the UI
                    this.selectedTestCase = testCase && testCase !== 'No Test Case' ?
                        testCase :
                        ''; // Just leave it empty instead

                    console.log('Selected script:', {
                        id,
                        name,
                        framework,
                        testCase
                    });
                },

                selectEnvironment(id, name) {
                    this.selectedEnvironment = id;
                    this.selectedEnvironmentName = name;

                    // Load environment details when selected
                    if (id) {
                        this.loadEnvironmentDetails(id);
                    }
                },

                async loadScriptDetails(scriptId) {
                    if (!scriptId) {
                        this.selectedScript = null;
                        this.selectedScriptName = '';
                        this.selectedFramework = '';
                        this.selectedTestCase = '';
                        return;
                    }

                    try {
                        // Optional API call for additional details if needed
                        // const response = await fetch(`/api/test-scripts/${scriptId}`);
                        // const data = await response.json();
                        // if (data.success) {
                        //     // Update with additional script details
                        // }
                    } catch (error) {
                        console.error('Error loading script details:', error);
                    }
                },

                async loadEnvironmentDetails(environmentId) {
                    if (!environmentId) {
                        this.selectedEnvironment = null;
                        this.selectedEnvironmentName = '';
                        this.environmentVars = {};
                        return;
                    }

                    this.selectedEnvironment = environmentId;

                    try {
                        const response = await fetch(`/api/environments/${environmentId}`);
                        if (!response.ok) throw new Error('Failed to fetch environment details');

                        const data = await response.json();
                        if (data.success) {
                            this.environmentVars = data.data.configuration || {};
                        } else {
                            throw new Error(data.message || 'Failed to load environment details');
                        }
                    } catch (error) {
                        console.error('Error loading environment details:', error);
                        this.environmentVars = {};

                        // Show error notification
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {
                                type: 'error',
                                message: `Failed to load environment details: ${error.message}`
                            }
                        }));
                    }
                },
            };
        }


        // Initialize Alpine when everything is loaded
        document.addEventListener('alpine:init', () => {
            // You can add any Alpine store data or components here if needed
        });
    </script>
@endpush
