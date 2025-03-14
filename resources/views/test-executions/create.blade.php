@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Navigation -->
    <div class="flex items-center text-sm text-gray-500 mb-4">
        <a href="{{ route('test-executions.index') }}" class="hover:text-blue-600">Test Executions</a>
        <svg class="h-4 w-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-gray-700">Run New Test</span>
    </div>

    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Run Test</h1>
        <p class="text-gray-600">Select a test script and environment to run</p>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <form action="{{ route('test-executions.store') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label for="script_id" class="block text-sm font-medium text-gray-700 mb-1">Test Script</label>

                    <!-- Script preselected if redirected from a test script page -->
                    @if(request()->has('script_id') && \App\Models\TestScript::find(request('script_id')))
                        @php
                            $selectedScript = \App\Models\TestScript::find(request('script_id'));
                        @endphp
                        <div class="flex items-center p-4 border border-gray-200 rounded-md bg-blue-50">
                            <input type="hidden" name="script_id" value="{{ $selectedScript->id }}">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $selectedScript->name }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $selectedScript->testSuite->name ?? '' }} /
                                    Framework: {{ $selectedScript->framework_type }}
                                </div>
                            </div>
                            <a href="{{ route('test-executions.create') }}" class="ml-auto text-sm text-blue-600 hover:text-blue-800">
                                Change
                            </a>
                        </div>
                    @else
                        <!-- Script selection -->
                        <div class="mb-4">
                            <div class="flex space-x-4 border-b border-gray-200">
                                <button type="button" id="tab-recent" onclick="switchTab('recent')" class="tab-btn active py-2 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600 focus:outline-none">
                                    Recent Scripts
                                </button>
                                <button type="button" id="tab-all" onclick="switchTab('all')" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                                    All Scripts
                                </button>
                                <button type="button" id="tab-search" onclick="switchTab('search')" class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                                    Search
                                </button>
                            </div>

                            <!-- Recent Scripts Tab -->
                            <div id="tab-content-recent" class="tab-content mt-4">
                                <div class="max-h-72 overflow-y-auto">
                                    <div class="space-y-2">
                                        @foreach(\App\Models\TestScript::latest()->take(5)->get() as $script)
                                            <label class="flex items-center p-3 border border-gray-200 rounded-md hover:bg-gray-50 cursor-pointer">
                                                <input type="radio" name="script_id" value="{{ $script->id }}" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">{{ $script->name }}</div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $script->testSuite->name ?? '' }} /
                                                        Framework: {{ $script->framework_type }}
                                                    </div>
                                                </div>
                                                <div class="ml-auto text-xs text-gray-500">
                                                    Last updated {{ $script->updated_at->diffForHumans() }}
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- All Scripts Tab -->
                            <div id="tab-content-all" class="tab-content mt-4 hidden">
                                <div class="mb-4">
                                    <label for="suite_filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Test Suite</label>
                                    <select id="suite_filter" class="border border-gray-300 rounded-md w-full py-2 px-3" onchange="filterScripts()">
                                        <option value="">All Test Suites</option>
                                        @foreach(\App\Models\TestSuite::all() as $suite)
                                            <option value="{{ $suite->id }}">
                                                {{ $suite->name }} ({{ $suite->project->name ?? '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="max-h-72 overflow-y-auto">
                                    <div id="all-scripts-container" class="space-y-2">
                                        @foreach(\App\Models\TestScript::all() as $script)
                                            <label class="script-item flex items-center p-3 border border-gray-200 rounded-md hover:bg-gray-50 cursor-pointer" data-suite="{{ $script->suite_id }}">
                                                <input type="radio" name="script_id" value="{{ $script->id }}" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">{{ $script->name }}</div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $script->testSuite->name ?? '' }} /
                                                        Framework: {{ $script->framework_type }}
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Search Tab -->
                            <div id="tab-content-search" class="tab-content mt-4 hidden">
                                <div class="mb-4">
                                    <label for="script_search" class="block text-sm font-medium text-gray-700 mb-1">Search Scripts</label>
                                    <div class="relative">
                                        <input type="text" id="script_search" placeholder="Search by name or framework..." class="border border-gray-300 rounded-md w-full py-2 pl-10 pr-3" oninput="searchScripts()">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="max-h-72 overflow-y-auto">
                                    <div id="search-results" class="space-y-2">
                                        <!-- Search results will be populated here via JS -->
                                        <div class="text-center py-4 text-gray-500">
                                            Enter search terms above to find test scripts
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @error('script_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="environment_id" class="block text-sm font-medium text-gray-700 mb-1">Environment</label>
                    <select name="environment_id" id="environment_id" class="border border-gray-300 rounded-md w-full py-2 px-3 @error('environment_id') border-red-500 @enderror" required>
                        <option value="">Select an environment</option>
                        @foreach(\App\Models\Environment::where('is_active', true)->get() as $env)
                            <option value="{{ $env->id }}" {{ (request('env_id') == $env->id) ? 'selected' : '' }}>
                                {{ $env->name }}
                                @if(isset($env->configuration['description']))
                                    - {{ $env->configuration['description'] }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('environment_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6 bg-gray-50 p-4 rounded-md">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Execution Options</h3>

                    <div class="mb-3">
                        <div class="flex items-center">
                            <input type="checkbox" name="notification" id="notification" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="notification" class="ml-2 block text-sm text-gray-700">
                                Notify me when execution completes
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="flex items-center">
                            <input type="checkbox" name="priority" id="priority" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="priority" class="ml-2 block text-sm text-gray-700">
                                High priority execution
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 ml-6">
                            This will run your test ahead of others in the queue (uses additional container quota)
                        </p>
                    </div>

                    <div class="mb-3">
                        <label for="run_notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea name="run_notes" id="run_notes" rows="2" class="border border-gray-300 rounded-md w-full py-2 px-3"></textarea>
                    </div>
                </div>

                <div class="flex justify-between">
                    <a href="{{ route('test-executions.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                        </svg>
                        Run Test
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resource Usage Info -->
    <div class="mt-6 bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-800">Resource Usage</h2>
        </div>
        <div class="p-6">
            @php
                // Get the authenticated user's team
                $user = auth()->user();
                $team = $user->teams()->first();

                // Get the team's subscription
                $subscription = $team ? \App\Models\Subscription::where('team_id', $team->id)->where('is_active', true)->first() : null;

                // Calculate usage
                $containerHours = 0;
                $storageUsedGB = 0;
                $maxContainers = 5;
                $maxParallelRuns = 5;

                if ($subscription) {
                    $maxContainers = $subscription->max_containers;
                    $maxParallelRuns = $subscription->max_parallel_runs;

                    // Calculate current usage (this would be more sophisticated in a real app)
                    $runningExecutions = \App\Models\TestExecution::whereHas('executionStatus', function($query) {
                        $query->where('name', 'Running');
                    })->count();
                }
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Container Hours Used</h3>
                    <div class="flex items-center">
                        <span class="text-2xl font-bold text-gray-900">{{ $containerHours }}/{{ $maxContainers * 24 }}</span>
                        <span class="ml-2 text-sm text-gray-500">hours</span>
                    </div>
                    <div class="mt-1 bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, ($containerHours / ($maxContainers * 24)) * 100) }}%"></div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Currently Running</h3>
                    <div class="flex items-center">
                        <span class="text-2xl font-bold text-gray-900">{{ $runningExecutions ?? 0 }}/{{ $maxParallelRuns }}</span>
                        <span class="ml-2 text-sm text-gray-500">parallel runs</span>
                    </div>
                    <div class="mt-1 bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, (($runningExecutions ?? 0) / $maxParallelRuns) * 100) }}%"></div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Storage Used</h3>
                    <div class="flex items-center">
                        <span class="text-2xl font-bold text-gray-900">{{ $storageUsedGB }}/10</span>
                        <span class="ml-2 text-sm text-gray-500">GB</span>
                    </div>
                    <div class="mt-1 bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, ($storageUsedGB / 10) * 100) }}%"></div>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-xs text-gray-500">
                <p>This execution will consume container resources according to your subscription plan. The actual resources used will depend on the test duration and complexity.</p>
            </div>
        </div>
    </div>
</div>

<script>
    // Tab switching
    function switchTab(tab) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });

        // Show the selected tab content
        document.getElementById('tab-content-' + tab).classList.remove('hidden');

        // Update tab styling
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500');
        });

        document.getElementById('tab-' + tab).classList.add('active', 'border-blue-500', 'text-blue-600');
    }

    // Filter scripts by test suite
    function filterScripts() {
        const suiteId = document.getElementById('suite_filter').value;
        const scriptItems = document.querySelectorAll('.script-item');

        scriptItems.forEach(item => {
            if (!suiteId || item.getAttribute('data-suite') === suiteId) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
            }
        });
    }

    // Search scripts
    function searchScripts() {
        const searchTerm = document.getElementById('script_search').value.toLowerCase();
        const searchResults = document.getElementById('search-results');

        if (!searchTerm) {
            searchResults.innerHTML = `
                <div class="text-center py-4 text-gray-500">
                    Enter search terms above to find test scripts
                </div>
            `;
            return;
        }

        // In a real application, you would make an AJAX request to search the scripts
        // For this example, we'll just search through the scripts that are already loaded
        const scripts = @json(\App\Models\TestScript::with('testSuite')->get());

        const filteredScripts = scripts.filter(script =>
            script.name.toLowerCase().includes(searchTerm) ||
            script.framework_type.toLowerCase().includes(searchTerm) ||
            (script.test_suite && script.test_suite.name.toLowerCase().includes(searchTerm))
        );

        if (filteredScripts.length === 0) {
            searchResults.innerHTML = `
                <div class="text-center py-4 text-gray-500">
                    No scripts found matching "${searchTerm}"
                </div>
            `;
            return;
        }

        searchResults.innerHTML = '';

        filteredScripts.forEach(script => {
            const scriptElement = document.createElement('label');
            scriptElement.className = 'flex items-center p-3 border border-gray-200 rounded-md hover:bg-gray-50 cursor-pointer';
            scriptElement.innerHTML = `
                <input type="radio" name="script_id" value="${script.id}" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">${script.name}</div>
                    <div class="text-xs text-gray-500">
                        ${script.test_suite ? script.test_suite.name : ''} /
                        Framework: ${script.framework_type}
                    </div>
                </div>
            `;
            searchResults.appendChild(scriptElement);
        });
    }
</script>
@endsection
