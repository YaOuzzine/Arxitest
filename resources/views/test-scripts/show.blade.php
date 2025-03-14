@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Navigation -->
    <div class="flex items-center text-sm text-gray-500 mb-4">
        <a href="{{ route('test-scripts.index') }}" class="hover:text-blue-600">Test Scripts</a>
        <svg class="h-4 w-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-gray-700">{{ $testScript->name }}</span>
    </div>

    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $testScript->name }}</h1>
            <p class="text-gray-600">
                Created by {{ $testScript->creator->name ?? 'Unknown' }} • Last updated {{ $testScript->updated_at->diffForHumans() }}
            </p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('test-scripts.edit', $testScript->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Edit
            </a>
            <button id="run-script-btn" onclick="showRunModal()" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                </svg>
                Run
            </button>
        </div>
    </div>

    <!-- Test Script Details -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-800">Script Details</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Test Suite</h3>
                    <p class="mt-1 text-sm text-gray-900">{{ $testScript->testSuite->name ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-500">{{ $testScript->testSuite->project->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Framework</h3>
                    <p class="mt-1 text-sm text-gray-900">
                        @if($testScript->framework_type == 'selenium_python')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Selenium (Python)
                            </span>
                        @elseif($testScript->framework_type == 'cypress')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                Cypress
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                {{ $testScript->framework_type }}
                            </span>
                        @endif
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Jira Story</h3>
                    <p class="mt-1 text-sm text-gray-900">
                        @if($testScript->jiraStory)
                            <a href="#" class="text-blue-600 hover:underline">
                                {{ $testScript->jiraStory->jira_key }}
                            </a>
                            <span class="block text-xs text-gray-500">{{ $testScript->jiraStory->title }}</span>
                        @else
                            <span class="text-gray-500">None</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Script Content -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-medium text-gray-800">Script Content</h2>
            <a href="{{ url('test-scripts/'. $testScript->id .'/versions') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Version History
            </a>
        </div>
        <div class="p-6 bg-gray-50">
            <pre id="script-content" class="border border-gray-200 bg-white rounded-md p-4 overflow-x-auto text-sm font-mono text-gray-800 h-96">{{ $testScript->script_content }}</pre>
        </div>
    </div>

    <!-- Latest Execution -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-800">Latest Execution</h2>
        </div>
        <div class="p-6">
            @if(isset($latestExecution))
                <div class="border border-gray-200 rounded-md overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 flex justify-between items-center">
                        <div class="flex items-center">
                            @if($latestExecution->executionStatus->name == 'Passed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    Passed
                                </span>
                            @elseif($latestExecution->executionStatus->name == 'Failed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-red-400" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    Failed
                                </span>
                            @elseif($latestExecution->executionStatus->name == 'Running')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-blue-400" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    Running
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-gray-400" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    {{ $latestExecution->executionStatus->name }}
                                </span>
                            @endif
                            <span class="ml-2 text-xs text-gray-500">
                                Run by {{ $latestExecution->initiator->name ?? 'Unknown' }}
                                on {{ $latestExecution->start_time->format('M d, Y \a\t h:i A') }}
                            </span>
                        </div>
                        <div>
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-800">View Details</a>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Environment:</span>
                            <span class="text-sm text-gray-700">{{ $latestExecution->environment->name ?? 'Unknown' }}</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Duration:</span>
                            <span class="text-sm text-gray-700">
                                @if($latestExecution->end_time)
                                    {{ $latestExecution->start_time->diffInSeconds($latestExecution->end_time) }} seconds
                                @else
                                    In progress
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Containers:</span>
                            <span class="text-sm text-gray-700">{{ $latestExecution->containers->count() }}</span>
                        </div>
                        @if($latestExecution->s3_results_key)
                            <div class="mt-4">
                                <a href="#" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download Results
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="text-center py-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900">No executions yet</h3>
                    <p class="mt-1 text-gray-500">Run this test script to see execution results.</p>
                    <div class="mt-6">
                        <button onclick="showRunModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                            Run Now
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Run Test Script Modal -->
<div id="run-modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Run Test Script</h3>
        </div>
        <form action="{{ route('test-executions.store') }}" method="POST">
            @csrf
            <input type="hidden" name="script_id" value="{{ $testScript->id }}">

            <div class="px-6 py-4">
                <div class="mb-4">
                    <label for="environment_id" class="block text-sm font-medium text-gray-700 mb-1">Environment</label>
                    <select name="environment_id" id="environment_id" class="border border-gray-300 rounded-md w-full py-2 px-3" required>
                        @foreach(App\Models\Environment::where('is_active', true)->get() as $env)
                            <option value="{{ $env->id }}">{{ $env->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="run_notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                    <textarea name="run_notes" id="run_notes" rows="2" class="border border-gray-300 rounded-md w-full py-2 px-3"></textarea>
                </div>

                <div class="mb-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="notification" id="notification" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="notification" class="ml-2 block text-sm text-gray-700">
                            Notify me when execution completes
                        </label>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 flex justify-end rounded-b-lg">
                <button type="button" onclick="hideRunModal()" class="bg-white text-gray-700 font-medium py-2 px-4 border border-gray-300 rounded-lg mr-2">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                    Run Test
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Modal functions
    function showRunModal() {
        document.getElementById('run-modal').classList.remove('hidden');
    }

    function hideRunModal() {
        document.getElementById('run-modal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('run-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideRunModal();
        }
    });
</script>
@endsection
