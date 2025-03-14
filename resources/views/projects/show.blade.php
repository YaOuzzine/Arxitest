@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Navigation -->
    <div class="flex items-center text-sm text-gray-500 mb-4">
        <a href="{{ route('projects.index') }}" class="hover:text-blue-600">Projects</a>
        <svg class="h-4 w-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-gray-700">{{ $project->name }}</span>
    </div>

    <!-- Page Header -->
    <div class="flex justify-between items-start mb-6">
        <div>
            <div class="flex items-center">
                <h1 class="text-2xl font-bold text-gray-800">{{ $project->name }}</h1>
                @if(isset($project->settings['ai_enabled']) && $project->settings['ai_enabled'])
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        AI Enabled
                    </span>
                @endif
            </div>
            <p class="text-gray-600 mt-1">{{ $project->description }}</p>
            <div class="mt-2 text-sm text-gray-500">
                Team: {{ $project->team->name }} • Created {{ $project->created_at->format('M d, Y') }} • Last updated {{ $project->updated_at->diffForHumans() }}
            </div>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('projects.edit', $project->id) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Edit Project
            </a>
            <button id="create-suite-btn" onclick="showCreateSuiteModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                New Test Suite
            </button>
        </div>
    </div>

    <!-- Project Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Test Suites Card -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-800">Test Suites</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-gray-900">{{ $project->testSuites->count() }}</div>
                        <div class="mt-1 text-sm text-gray-500">Total Test Suites</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Scripts Card -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-800">Test Scripts</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-gray-900">{{ $project->testScripts->count() }}</div>
                        <div class="mt-1 text-sm text-gray-500">Total Test Scripts</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Executions Card -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-800">Test Executions</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-center">
                    <div class="text-center">
                        @php
                            // Make sure all required keys exist in $executionStats
                            $executionStats = $executionStats ?? [
                                'total' => 0,
                                'success' => 0,
                                'failed' => 0,
                                'running' => 0
                            ];

                            // Ensure the 'running' key exists
                            if (!isset($executionStats['running'])) {
                                $executionStats['running'] = 0;
                            }
                        @endphp
                        <div class="text-4xl font-bold text-gray-900">{{ $executionStats['total'] }}</div>
                        <div class="mt-1 text-sm text-gray-500">Total Executions</div>
                        <div class="mt-2 flex justify-center space-x-3 text-xs">
                            <span class="text-green-600">{{ $executionStats['success'] }} Passed</span>
                            <span class="text-red-600">{{ $executionStats['failed'] }} Failed</span>
                            <span class="text-blue-600">{{ $executionStats['running'] }} Running</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Suites Section -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-medium text-gray-800">Test Suites</h2>
            <button onclick="showCreateSuiteModal()" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Add Test Suite
            </button>
        </div>

        @if($project->testSuites->isEmpty())
            <div class="p-6 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900">No test suites yet</h3>
                <p class="mt-1 text-gray-500">Get started by creating your first test suite.</p>
                <div class="mt-6">
                    <button onclick="showCreateSuiteModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                        Create Test Suite
                    </button>
                </div>
            </div>
        @else
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scripts</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($project->testSuites as $suite)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $suite->name }}</div>
                                            <div class="text-sm text-gray-500 line-clamp-1" title="{{ $suite->description }}">
                                                {{ $suite->description ?? 'No description' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ $suite->testScripts->count() }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $suite->updated_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('test-suites.show', $suite->id) }}" class="text-indigo-600 hover:text-indigo-900" title="View">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('test-suites.edit', $suite->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 0L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('test-scripts.create', ['suite_id' => $suite->id]) }}" class="text-green-600 hover:text-green-900" title="Add Script">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('test-suites.destroy', $suite->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this test suite and all associated scripts?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Project Settings -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-800">Project Settings</h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Environments -->
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Environments</h3>
                <div class="bg-gray-50 p-4 rounded-md">
                    @if($project->environments->isEmpty())
                        <p class="text-sm text-gray-500">No environments configured</p>
                    @else
                        <ul class="space-y-2">
                            @foreach($project->environments as $env)
                                <li class="text-sm text-gray-700 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $env->name }}
                                    @if(isset($env->configuration['description']))
                                        <span class="text-xs text-gray-500 ml-2">{{ $env->configuration['description'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Integrations -->
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Integrations</h3>
                <div class="bg-gray-50 p-4 rounded-md">
                    @if($project->integrations->isEmpty())
                        <p class="text-sm text-gray-500">No integrations configured</p>
                    @else
                        <ul class="space-y-2">
                            @foreach($project->integrations as $integration)
                                <li class="text-sm text-gray-700 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $integration->integration->name }} ({{ $integration->integration->type }})
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- AI Configuration -->
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">AI Configuration</h3>
                <div class="bg-gray-50 p-4 rounded-md">
                    @if(isset($project->settings['ai_enabled']) && $project->settings['ai_enabled'])
                        <div class="flex items-center mb-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-blue-600 mr-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span class="text-sm font-medium text-gray-700">AI-Assisted Test Generation Enabled</span>
                        </div>
                        <ul class="space-y-1 pl-8 text-sm text-gray-600">
                            <li>Provider: {{ ucfirst($project->settings['ai_provider'] ?? 'default') }}</li>
                            <li>Template: {{ ucfirst($project->settings['ai_template'] ?? 'basic') }}</li>
                            @if(isset($project->settings['ai_custom_prompt']) && $project->settings['ai_custom_prompt'])
                                <li>Custom instructions: <span class="text-xs italic">{{ Str::limit($project->settings['ai_custom_prompt'], 50) }}</span></li>
                            @endif
                        </ul>
                    @else
                        <p class="text-sm text-gray-500">AI features are not enabled for this project</p>
                    @endif
                </div>
            </div>

            <!-- General Settings -->
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">General Settings</h3>
                <div class="bg-gray-50 p-4 rounded-md">
                    <ul class="space-y-2">
                        <li class="text-sm text-gray-700 flex items-center">
                            @if(isset($project->settings['version_control']) && $project->settings['version_control'])
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            @endif
                            Version Control
                        </li>
                        <li class="text-sm text-gray-700 flex items-center">
                            @if(isset($project->settings['notification_settings']['on_failure']) && $project->settings['notification_settings']['on_failure'])
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            @endif
                            Notify on Failure
                        </li>
                        <li class="text-sm text-gray-700 flex items-center">
                            @if(isset($project->settings['notification_settings']['on_completion']) && $project->settings['notification_settings']['on_completion'])
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            @endif
                            Notify on Completion
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Test Suite Modal -->
<div id="create-suite-modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Create Test Suite</h3>
        </div>
        <form action="{{ route('test-suites.store') }}" method="POST">
            @csrf
            <input type="hidden" name="project_id" value="{{ $project->id }}">

            <div class="px-6 py-4">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Suite Name</label>
                    <input type="text" name="name" id="name" class="border border-gray-300 rounded-md w-full py-2 px-3" required>
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                    <textarea name="description" id="description" rows="3" class="border border-gray-300 rounded-md w-full py-2 px-3"></textarea>
                </div>

                <div>
                    <label for="settings" class="block text-sm font-medium text-gray-700 mb-1">Settings</label>
                    <div class="bg-gray-50 p-3 rounded-md">
                        <div class="flex items-center">
                            <input type="checkbox" name="settings[parallel_execution]" id="parallel_execution" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="parallel_execution" class="ml-2 block text-sm text-gray-700">
                                Enable parallel script execution
                            </label>
                        </div>
                        <div class="flex items-center mt-2">
                            <input type="checkbox" name="settings[data_driven]" id="data_driven" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="data_driven" class="ml-2 block text-sm text-gray-700">
                                Enable data-driven testing
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 flex justify-end rounded-b-lg">
                <button type="button" onclick="hideCreateSuiteModal()" class="bg-white text-gray-700 font-medium py-2 px-4 border border-gray-300 rounded-lg mr-2">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                    Create Suite
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Modal functions
    function showCreateSuiteModal() {
        document.getElementById('create-suite-modal').classList.remove('hidden');
    }

    function hideCreateSuiteModal() {
        document.getElementById('create-suite-modal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('create-suite-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideCreateSuiteModal();
        }
    });
</script>
@endsection
