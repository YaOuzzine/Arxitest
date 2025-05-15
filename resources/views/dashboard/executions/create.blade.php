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
<div class="max-w-4xl mx-auto" x-data="createExecution()">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">Run Test Execution</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            Configure and start a new test execution run
        </p>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-zinc-800 shadow-md rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700">
        <div class="border-b border-zinc-200 dark:border-zinc-700 px-6 py-4 bg-zinc-50 dark:bg-zinc-800/50">
            <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">Execution Settings</h2>
        </div>

        <form action="{{ route('dashboard.executions.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Test Script Selection -->
            <div>
                <label for="script_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Test Script <span class="text-red-500">*</span></label>
                <select name="script_id" id="script_id" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required @change="loadScriptDetails($event.target.value)">
                    <option value="">Select a test script</option>
                    @foreach($scripts as $script)
                        <option value="{{ $script->id }}" data-framework="{{ $script->framework_type }}" data-test-case="{{ $script->testCase->title ?? 'Unknown' }}">
                            {{ $script->name }} ({{ $script->framework_type }})
                        </option>
                    @endforeach
                </select>
                @error('script_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Script Details Preview -->
            <div x-show="selectedScript" x-cloak class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-600/50">
                <h3 class="text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-2">Script Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-zinc-500 dark:text-zinc-400">Framework Type:</p>
                        <p class="font-medium text-zinc-800 dark:text-zinc-200" x-text="selectedFramework"></p>
                    </div>
                    <div>
                        <p class="text-zinc-500 dark:text-zinc-400">Test Case:</p>
                        <p class="font-medium text-zinc-800 dark:text-zinc-200" x-text="selectedTestCase"></p>
                    </div>
                </div>
            </div>

            <!-- Environment Selection -->
            <div>
                <label for="environment_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Environment <span class="text-red-500">*</span></label>
                <select name="environment_id" id="environment_id" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required @change="loadEnvironmentDetails($event.target.value)">
                    <option value="">Select an environment</option>
                    @foreach($environments as $environment)
                        <option value="{{ $environment->id }}">
                            {{ $environment->name }} {{ $environment->is_global ? '(Global)' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('environment_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Environment Details Preview -->
            <div x-show="selectedEnvironment" x-cloak class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-600/50">
                <h3 class="text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-2">Environment Variables</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead>
                            <tr class="text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                <th class="px-4 py-2">Key</th>
                                <th class="px-4 py-2">Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            <template x-for="(value, key) in environmentVars" :key="key">
                                <tr>
                                    <td class="px-4 py-2 text-sm font-medium text-zinc-900 dark:text-zinc-200" x-text="key"></td>
                                    <td class="px-4 py-2 text-sm text-zinc-500 dark:text-zinc-400" x-text="value"></td>
                                </tr>
                            </template>
                            <tr x-show="Object.keys(environmentVars).length === 0">
                                <td colspan="2" class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400 text-center">
                                    No environment variables configured
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Additional Options -->
            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6">
                <h3 class="text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-4">Execution Options</h3>

                <div class="space-y-3">
                    <!-- Timeout -->
                    <div class="flex items-center">
                        <input id="enable_timeout" name="enable_timeout" type="checkbox" x-model="enableTimeout" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-zinc-300 dark:border-zinc-600 rounded">
                        <label for="enable_timeout" class="ml-2 block text-sm text-zinc-700 dark:text-zinc-300">
                            Custom timeout
                        </label>
                    </div>

                    <div x-show="enableTimeout" x-cloak class="flex items-center pl-7">
                        <label for="timeout_minutes" class="block text-sm text-zinc-700 dark:text-zinc-300 mr-2">
                            Timeout after
                        </label>
                        <input type="number" name="timeout_minutes" id="timeout_minutes" class="w-20 rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" min="1" max="60" value="10">
                        <span class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">minutes</span>
                    </div>

                    <!-- Priority -->
                    <div class="flex items-center">
                        <input id="high_priority" name="priority" type="checkbox" value="high" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-zinc-300 dark:border-zinc-600 rounded">
                        <label for="high_priority" class="ml-2 block text-sm text-zinc-700 dark:text-zinc-300">
                            High priority execution
                        </label>
                    </div>

                    <!-- Notification -->
                    <div class="flex items-center">
                        <input id="notify_completion" name="notify_completion" type="checkbox" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-zinc-300 dark:border-zinc-600 rounded">
                        <label for="notify_completion" class="ml-2 block text-sm text-zinc-700 dark:text-zinc-300">
                            Notify me when execution completes
                        </label>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <a href="{{ route('dashboard.executions.index') }}" class="px-4 py-2 bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 rounded-md text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center">
                    <i data-lucide="play" class="w-4 h-4 mr-2"></i>
                    Start Execution
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function createExecution() {
        return {
            selectedScript: null,
            selectedFramework: '',
            selectedTestCase: '',
            selectedEnvironment: null,
            environmentVars: {},
            enableTimeout: false,

            async loadScriptDetails(scriptId) {
                if (!scriptId) {
                    this.selectedScript = null;
                    this.selectedFramework = '';
                    this.selectedTestCase = '';
                    return;
                }

                const option = document.querySelector(`option[value="${scriptId}"]`);
                if (option) {
                    this.selectedScript = scriptId;
                    this.selectedFramework = option.dataset.framework || 'Unknown';
                    this.selectedTestCase = option.dataset.testCase || 'Unknown';
                }

                try {
                    // You can add an API call here to get more detailed script info if needed
                    // const response = await fetch(`/api/test-scripts/${scriptId}`);
                    // const data = await response.json();
                    // if (data.success) {
                    //     // Update with additional details
                    // }
                } catch (error) {
                    console.error('Error loading script details:', error);
                }
            },

            async loadEnvironmentDetails(environmentId) {
                if (!environmentId) {
                    this.selectedEnvironment = null;
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
            }
        };
    }
</script>
@endpush
