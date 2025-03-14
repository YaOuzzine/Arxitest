@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Test Scripts</h1>
            <p class="text-gray-600">Manage your automated test scripts</p>
        </div>
        <a href="{{ route('test-scripts.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Create Test Script
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form action="{{ route('test-scripts.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="w-full md:w-1/4">
                <label for="suite_id" class="block text-sm font-medium text-gray-700 mb-1">Test Suite</label>
                <select id="suite_id" name="suite_id" class="border border-gray-300 rounded-md w-full py-2 px-3">
                    <option value="">All Suites</option>
                    @foreach(App\Models\TestSuite::all() as $suite)
                        <option value="{{ $suite->id }}" {{ request('suite_id') == $suite->id ? 'selected' : '' }}>
                            {{ $suite->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-1/4">
                <label for="framework_type" class="block text-sm font-medium text-gray-700 mb-1">Framework</label>
                <select id="framework_type" name="framework_type" class="border border-gray-300 rounded-md w-full py-2 px-3">
                    <option value="">All Frameworks</option>
                    <option value="selenium_python" {{ request('framework_type') == 'selenium_python' ? 'selected' : '' }}>Selenium (Python)</option>
                    <option value="cypress" {{ request('framework_type') == 'cypress' ? 'selected' : '' }}>Cypress</option>
                </select>
            </div>
            <div class="w-full md:w-2/4">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <div class="relative">
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name..." class="border border-gray-300 rounded-md w-full py-2 pl-10 pr-3">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Test Scripts Table -->
    @if($testScripts->isEmpty())
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900">No test scripts found</h3>
            <p class="mt-1 text-gray-500">Get started by creating your first test script.</p>
            <div class="mt-6">
                <a href="{{ route('test-scripts.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                    Create Test Script
                </a>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Suite</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Framework</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jira Story</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($testScripts as $script)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="ml-0">
                                        <div class="text-sm font-medium text-gray-900">
                                            <a href="{{ route('test-scripts.show', $script->id) }}" class="hover:text-blue-600">
                                                {{ $script->name }}
                                            </a>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            By {{ $script->creator->name ?? 'Unknown' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">{{ $script->testSuite->name ?? 'N/A' }}</span>
                                <p class="text-xs text-gray-500">{{ $script->testSuite->project->name ?? '' }}</p>
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
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('test-scripts.show', $script->id) }}" class="text-indigo-600 hover:text-indigo-900" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('test-scripts.edit', $script->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 0L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('test-scripts.destroy', $script->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this test script?');">
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

        <!-- Pagination -->
        <div class="mt-4">
            {{ $testScripts->links() }}
        </div>
    @endif
</div>
@endsection
