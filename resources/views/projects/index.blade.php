@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Projects</h1>
                <p class="text-gray-600">Manage your test automation projects</p>
            </div>
            <a href="{{ route('projects.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                        clip-rule="evenodd" />
                </svg>
                New Project
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <form action="{{ route('projects.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="w-full md:w-1/3">
                    <label for="team_id" class="block text-sm font-medium text-gray-700 mb-1">Team</label>
                    <select id="team_id" name="team_id" class="border border-gray-300 rounded-md w-full py-2 px-3">
                        <option value="">All Teams</option>
                        @foreach (Auth::user()->teams as $team)
                            <option value="{{ $team->id }}" {{ request('team_id') == $team->id ? 'selected' : '' }}>
                                {{ $team->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-2/3">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                            placeholder="Search by name or description..."
                            class="border border-gray-300 rounded-md w-full py-2 pl-10 pr-3">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Project Grid -->
        @if (isset($projects) && $projects->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($projects as $project)
                    <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-md transition-shadow duration-300">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex justify-between items-start">
                                <h2 class="text-lg font-medium text-gray-900 truncate" title="{{ $project->name }}">
                                    {{ $project->name }}
                                </h2>
                                <div class="flex items-center space-x-2">
                                    @if (isset($project->settings['ai_enabled']) && $project->settings['ai_enabled'])
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            AI Enabled
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 line-clamp-2" title="{{ $project->description }}">
                                {{ $project->description ?? 'No description provided' }}
                            </p>
                        </div>

                        <div class="px-6 py-4">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <div class="text-xl font-bold text-gray-900">{{ $project->testSuites->count() }}</div>
                                    <div class="text-xs text-gray-500">Suites</div>
                                </div>
                                <div>
                                    <div class="text-xl font-bold text-gray-900">{{ $project->test_scripts_count ?? 0 }}
                                    </div>
                                    <div class="text-xs text-gray-500">Scripts</div>
                                </div>
                                <div>
                                    <div class="text-xl font-bold text-gray-900">{{ $project->environments->count() }}
                                    </div>
                                    <div class="text-xs text-gray-500">Envs</div>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            <div class="flex justify-between">
                                <div class="text-xs text-gray-500">
                                    Team: {{ $project->team->name }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Updated {{ $project->updated_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex justify-between">
                            <a href="{{ route('projects.show', $project->id) }}"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Details
                            </a>
                            <div class="flex space-x-3">
                                <a href="{{ route('projects.edit', $project->id) }}"
                                    class="text-yellow-600 hover:text-yellow-800 text-sm">
                                    Edit
                                </a>
                                <form action="{{ route('projects.destroy', $project->id) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Are you sure you want to delete this project and all associated test suites and scripts?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if (isset($projects) && method_exists($projects, 'links'))
                <div class="mt-6">
                    {{ $projects->links() }}
                </div>
            @endif
        @else
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900">No projects found</h3>
                <p class="mt-1 text-gray-500">Get started by creating your first project.</p>
                <div class="mt-6">
                    <a href="{{ route('projects.create') }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                        Create Project
                    </a>
                </div>
            </div>
        @endif

        <!-- Quick Start Guide -->
        <div class="mt-8 bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-800">Quick Start Guide</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="flex flex-col items-center text-center p-4">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <h3 class="text-base font-medium text-gray-900 mb-1">Create a Project</h3>
                        <p class="text-sm text-gray-500">Start by creating a new project to organize your test suites</p>
                    </div>

                    <div class="flex flex-col items-center text-center p-4">
                        <div
                            class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <h3 class="text-base font-medium text-gray-900 mb-1">Add Test Suites</h3>
                        <p class="text-sm text-gray-500">Organize your tests into logical suites for better management</p>
                    </div>

                    <div class="flex flex-col items-center text-center p-4">
                        <div
                            class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <h3 class="text-base font-medium text-gray-900 mb-1">Create Test Scripts</h3>
                        <p class="text-sm text-gray-500">Write or generate test scripts to automate your testing process
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
