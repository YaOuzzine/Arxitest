<!-- resources/views/dashboard/executions/create.blade.php -->
@extends('layouts.dashboard')

@section('title', 'Create Test Execution')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.executions.index') }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
            Test Executions
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Create</span>
    </li>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto" x-data="executionCreate">
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700 bg-gradient-to-r from-zinc-50 to-blue-50/20 dark:from-zinc-800/50 dark:to-blue-900/10">
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                    Run Test Execution
                </h1>
                <p class="mt-1 text-zinc-500 dark:text-zinc-400">
                    Configure and run a new test execution
                </p>
            </div>

            <form method="POST" action="{{ route('dashboard.executions.store') }}" class="p-6 space-y-6">
                @csrf

                <!-- Script Selection -->
                <div>
                    <label for="script_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Test Script <span class="text-red-500">*</span>
                    </label>
                    <select id="script_id" name="script_id" required
                        class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-800 dark:text-zinc-200"
                        @change="updateScriptDetails">
                        <option value="">Select a script</option>
                        @foreach($scripts as $script)
                            <option value="{{ $script->id }}" data-framework="{{ $script->framework_type }}"
                                data-case-title="{{ $script->testCase ? $script->testCase->title : 'No test case' }}">
                                {{ $script->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('script_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    <!-- Script Details (hidden until script selected) -->
                    <div x-show="scriptDetails.id" x-cloak class="mt-3 p-3 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg border border-zinc-200 dark:border-zinc-600">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-md bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400">
                                    <i data-lucide="file-code" class="h-5 w-5"></i>
                                </span>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-zinc-900 dark:text-white" x-text="scriptDetails.name"></h3>
                                <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    <div><span class="font-medium">Framework:</span> <span x-text="scriptDetails.framework"></span></div>
                                    <div><span class="font-medium">Test Case:</span> <span x-text="scriptDetails.testCase"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Environment Selection -->
                <div>
                    <label for="environment_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Environment <span class="text-red-500">*</span>
                    </label>
                    <select id="environment_id" name="environment_id" required
                        class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-800 dark:text-zinc-200"
                        @change="updateEnvironmentDetails">
                        <option value="">Select an environment</option>
                        @foreach($environments as $env)
                            <option value="{{ $env->id }}" data-config="{{ json_encode($env->configuration) }}">
                                {{ $env->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('environment_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    <!-- Environment Details (hidden until environment selected) -->
                    <div x-show="environmentDetails.id" x-cloak class="mt-3 p-3 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg border border-zinc-200 dark:border-zinc-600">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-md bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400">
                                    <i data-lucide="server" class="h-5 w-5"></i>
                                </span>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-zinc-900 dark:text-white" x-text="environmentDetails.name"></h3>
                                <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    <template x-for="(value, key) in environmentDetails.config" :key="key">
                                        <div>
                                            <span class="font-medium" x-text="key"></span>:
                                            <span x-text="value"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Options (Collapsible) -->
                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="flex items-center text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200">
                        <i data-lucide="settings-2" class="w-4 h-4 mr-1"></i>
                        Advanced Options
                        <i data-lucide="chevron-down" class="w-4 h-4 ml-1 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="open" x-transition class="mt-3 space-y-4 bg-zinc-50 dark:bg-zinc-700/30 p-4 rounded-lg border border-zinc-200 dark:border-zinc-600">
                        <!-- Timeout -->
                        <div>
                            <label for="timeout" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Execution Timeout (minutes)
                            </label>
                            <input type="number" id="timeout" name="timeout" min="1" max="120" value="30"
                                class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-800 dark:text-zinc-200 text-sm">
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                Maximum duration before the execution is automatically terminated (1-120 minutes)
                            </p>
                        </div>

                        <!-- Retries -->
                        <div>
                            <label for="retries" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Retry Count
                            </label>
                            <input type="number" id="retries" name="retries" min="0" max="3" value="0"
                                class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-800 dark:text-zinc-200 text-sm">
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                Number of automatic retries if the execution fails (0-3)
                            </p>
                        </div>

                        <!-- Priority -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Execution Priority
                            </label>
                            <select id="priority" name="priority"
                                class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-zinc-800 dark:text-zinc-200 text-sm">
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="low">Low</option>
                            </select>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                Priority affects the order in which queued executions are processed
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <a href="{{ route('dashboard.executions.index') }}" class="px-4 py-2 bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-600 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow transition-colors flex items-center">
                        <i data-lucide="play" class="w-4 h-4 mr-1.5"></i>
                        Run Test
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('executionCreate', () => ({
            scriptDetails: {
                id: '',
                name: '',
                framework: '',
                testCase: ''
            },
            environmentDetails: {
                id: '',
                name: '',
                config: {}
            },

            updateScriptDetails() {
                const select = document.getElementById('script_id');
                const option = select.options[select.selectedIndex];

                if (select.value) {
                    this.scriptDetails = {
                        id: select.value,
                        name: option.textContent.trim(),
                        framework: option.dataset.framework || 'Unknown',
                        testCase: option.dataset.caseTitle || 'No test case'
                    };
                } else {
                    this.scriptDetails = { id: '', name: '', framework: '', testCase: '' };
                }
            },

            updateEnvironmentDetails() {
                const select = document.getElementById('environment_id');
                const option = select.options[select.selectedIndex];

                if (select.value) {
                    let config = {};
                    try {
                        config = JSON.parse(option.dataset.config || '{}');
                    } catch (e) {
                        console.error('Failed to parse environment config:', e);
                    }

                    this.environmentDetails = {
                        id: select.value,
                        name: option.textContent.trim(),
                        config: config
                    };
                } else {
                    this.environmentDetails = { id: '', name: '', config: {} };
                }
            }
        }));
    });
</script>
@endpush
