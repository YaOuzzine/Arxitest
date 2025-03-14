@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Navigation -->
    <div class="flex items-center text-sm text-gray-500 mb-4">
        <a href="{{ route('projects.index') }}" class="hover:text-blue-600">Projects</a>
        <svg class="h-4 w-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
        <a href="{{ route('projects.show', $testSuite->project->id) }}" class="hover:text-blue-600">{{ $testSuite->project->name }}</a>
        <svg class="h-4 w-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-gray-700">{{ $testSuite->name }}</span>
    </div>

    <!-- Page Header -->
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $testSuite->name }}</h1>
            <p class="text-gray-600 mt-1">{{ $testSuite->description }}</p>
            <div class="mt-2 text-sm text-gray-500">
                Created {{ $testSuite->created_at->format('M d, Y') }} • Last updated {{ $testSuite->updated_at->diffForHumans() }}
            </div>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('test-suites.edit', $testSuite->id) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Edit Suite
            </a>
            <a href="{{ route('test-scripts.create', ['suite_id' => $testSuite->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Add Test Script
            </a>
        </div>
    </div>

    <!-- Suite Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Test Scripts Card -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-800">Test Scripts</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-gray-900">{{ $testSuite->testScripts->count() }}</div>
                        <div class="mt-1 text-sm text-gray-500">Total Test Scripts</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Framework Distribution -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-800">Frameworks</h2>
            </div>
            <div class="p-6">
                @php
                    $frameworks = $testSuite->testScripts->groupBy('framework_type')->map->count();
                @endphp

                @if($frameworks->isEmpty())
                    <div class="flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-gray-500">No scripts yet</div>
                        </div>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($frameworks as $framework => $count)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">
                                    @if($framework == 'selenium_python')
                                        Selenium (Python)
                                    @elseif($framework == 'cypress')
                                        Cypress
                                    @else
                                        {{ $framework }}
                                    @endif
                                </span>
                                <span class="text-sm font-medium text-gray-900">{{ $count }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($count / $testSuite->testScripts->count()) * 100 }}%"></div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Executions -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-800">Recent Executions</h2>
            </div>
            <div class="p-6">
                @php
                    // In a real app, you would fetch recent executions for this suite's scripts
                    $recentExecutions = collect();
                @endphp

                @if($recentExecutions->isEmpty())
                    <div class="flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-gray-500">No recent executions</div>
                            <a href="{{ route('test-executions.create') }}" class="text-sm text-blue-600 hover:underline mt-2 inline-block">Run tests</a>
                        </div>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($recentExecutions as $execution)
                            <div class="text-sm">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-900">{{ $execution->testScript->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $execution->start_time->diffForHumans() }}</span>
                                </div>
                                <div class="flex items-center mt-1">
                                    @if($execution->executionStatus->name == 'Passed')
                                        <span class="h-2 w-2 bg-green-500 rounded-full mr-1"></span>
                                        <span class="text-green-600">Passed</span>
                                    @elseif($execution->executionStatus->name == 'Failed')
                                        <span class="h-2 w-2 bg-red-500 rounded-full mr-1"></span>
                                        <span class="text-red-600">Failed</span>
                                    @else
                                        <span class="h-2 w-2 bg-blue-500 rounded-full mr-1"></span>
                                        <span class="text-blue-600">{{ $execution->executionStatus->name }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Test Scripts Section -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-medium text-gray-800">Test Scripts</h2>

            <div class="flex items-center space-x-2">
                <div class="relative">
                    <input id="script-search" type="text" placeholder="Search scripts..." class="border border-gray-300 rounded-md py-1 px-3 text-sm w-48 focus:ring-blue-500 focus:border-blue-500">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <a href="{{ route('test-scripts.create', ['suite_id' => $testSuite->id]) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Add Script
                </a>
            </div>
        </div>

        @if($testSuite->testScripts->isEmpty())
            <div class="p-6 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900">No test scripts yet</h3>
                <p class="mt-1 text-gray-500">Add scripts to this test suite to start automating your tests.</p>
                <div class="mt-6">
                    <a href="{{ route('test-scripts.create', ['suite_id' => $testSuite->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                        Add First Script
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Framework</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jira Story</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="scripts-table-body">
                        @foreach($testSuite->testScripts as $script)
                            <tr class="script-row hover:bg-gray-50" data-script-name="{{ strtolower($script->name) }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <a href="{{ route('test-scripts.show', $script->id) }}" class="hover:text-blue-600">
                                                    {{ $script->name }}
                                                </a>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                By {{ $script->creator->name ?? 'Unknown' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($script->framework_type == 'selenium_python')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Selenium (Python)
                                        </span>
                                    @elseif($script->framework_type == 'cypress')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Cypress
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ $script->framework_type }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($script->jiraStory)
                                        <a href="#" class="text-sm text-blue-600 hover:underline">
                                            {{ $script->jiraStory->jira_key }}
                                        </a>
                                    @else
                                        <span class="text-sm text-gray-500">None</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $script->updated_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('test-scripts.show', $script->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                            View
                                        </a>
                                        <a href="{{ route('test-scripts.edit', $script->id) }}" class="text-yellow-600 hover:text-yellow-900">
                                            Edit
                                        </a>
                                        <a href="{{ route('test-executions.create', ['script_id' => $script->id]) }}" class="text-green-600 hover:text-green-900">
                                            Run
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Empty Search Results -->
            <div id="no-results-message" class="hidden p-6 text-center">
                <p class="text-gray-500">No scripts match your search.</p>
            </div>
        @endif
    </div>

    <!-- Suite Settings -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-800">Suite Settings</h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- General Settings -->
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">General Settings</h3>
                <div class="bg-gray-50 p-4 rounded-md">
                    <ul class="space-y-2">
                        <li class="text-sm text-gray-700 flex items-center">
                            @if(isset($testSuite->settings['parallel_execution']) && $testSuite->settings['parallel_execution'])
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            @endif
                            Parallel Execution
                        </li>
                        <li class="text-sm text-gray-700 flex items-center">
                            @if(isset($testSuite->settings['data_driven']) && $testSuite->settings['data_driven'])
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            @endif
                            Data-Driven Testing
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Run Configuration -->
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Project Configuration</h3>
                <div class="bg-gray-50 p-4 rounded-md">
                    <div class="text-sm text-gray-700 mb-2">
                        <span class="font-medium">Project:</span> {{ $testSuite->project->name }}
                    </div>

                    @if($testSuite->project->environments->isNotEmpty())
                        <div class="text-sm text-gray-700">
                            <span class="font-medium">Available Environments:</span>
                            <div class="mt-1 space-x-1">
                                @foreach($testSuite->project->environments as $env)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $env->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-800">Bulk Actions</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('test-executions.create', ['suite_id' => $testSuite->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg text-center">
                    Run All Tests
                </a>

                <button onclick="exportTestSuite()" class="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg">
                    Export Scripts
                </button>

                <button onclick="showImportModal()" class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-3 px-4 rounded-lg">
                    Import Scripts
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Script search functionality
    document.getElementById('script-search').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const scriptRows = document.querySelectorAll('.script-row');
        const noResultsMessage = document.getElementById('no-results-message');

        let visibleCount = 0;

        scriptRows.forEach(row => {
            const scriptName = row.getAttribute('data-script-name');

            if (scriptName.includes(searchTerm)) {
                row.classList.remove('hidden');
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });

        if (visibleCount === 0) {
            noResultsMessage.classList.remove('hidden');
        } else {
            noResultsMessage.classList.add('hidden');
        }
    });

    // Placeholder functions for bulk actions
    function exportTestSuite() {
        alert('Export functionality would be implemented here. This would export all test scripts in this suite.');
    }

    function showImportModal() {
        alert('Import modal would be shown here. This would allow importing test scripts into this suite.');
    }
</script>
@endsection
