{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="relative py-6">
        <!-- Animated background elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div
                class="absolute -top-24 -right-24 w-96 h-96 bg-gradient-to-br from-blue-100/30 to-purple-100/30 rounded-full blur-3xl">
            </div>
            <div
                class="absolute top-1/2 -left-24 w-80 h-80 bg-gradient-to-tr from-green-100/20 to-blue-100/20 rounded-full blur-3xl">
            </div>
            <div
                class="absolute bottom-0 right-1/3 w-64 h-64 bg-gradient-to-tl from-yellow-100/20 to-green-100/20 rounded-full blur-3xl">
            </div>

            <!-- Animated dots -->
            <div class="dot-animation absolute top-20 left-1/4 w-2 h-2 bg-blue-400/40 rounded-full"></div>
            <div class="dot-animation delay-300 absolute top-40 right-1/3 w-2 h-2 bg-green-400/40 rounded-full"></div>
            <div class="dot-animation delay-700 absolute bottom-20 left-1/3 w-2 h-2 bg-purple-400/40 rounded-full"></div>
            <div class="dot-animation delay-1000 absolute bottom-40 right-1/4 w-3 h-3 bg-yellow-400/40 rounded-full"></div>
            <div class="dot-animation delay-1500 absolute top-1/2 left-20 w-2 h-2 bg-indigo-400/40 rounded-full"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <!-- Welcome Header with subtle animation -->
            <div class="mb-8 transition-all duration-500 hover:translate-y-[-2px]">
                <div class="bg-white/90 p-6 rounded-xl shadow-sm border border-gray-100 backdrop-blur-sm">
                    <div class="flex items-center">
                        <div class="mr-4 bg-gradient-to-br from-blue-500 to-purple-600 p-3 rounded-lg shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Welcome, {{ Auth::user()->name }}</h1>
                            <p class="mt-2 text-gray-600">
                                @if ($recentActivity && count($recentActivity) > 0)
                                    You've had {{ count($recentActivity) }} test activities in the last 7 days.
                                @else
                                    Get started with your test automation journey today.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions - Enhanced with animation and modern styling -->
            <div class="mb-8">
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('test-scripts.create') }}"
                        class="action-button group flex items-center px-6 py-4 bg-gradient-to-br from-green-500 to-emerald-600 text-white rounded-xl transition-all duration-300 shadow-md hover:shadow-lg hover:translate-y-[-2px] hover:shadow-green-200">
                        <div
                            class="flex items-center justify-center h-10 w-10 bg-white/20 rounded-lg mr-3 group-hover:scale-110 transition-transform duration-300">
                            <i data-lucide="plus-circle" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <span class="font-semibold block">Create New Test</span>
                            <span class="text-xs text-white/80">Generate test scripts automatically</span>
                        </div>
                        <div
                            class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 group-hover:translate-x-1">
                            <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </div>
                    </a>

                    <a href="{{ route('test-executions.create') }}"
                        class="action-button group flex items-center px-6 py-4 bg-gradient-to-br from-blue-500 to-indigo-600 text-white rounded-xl transition-all duration-300 shadow-md hover:shadow-lg hover:translate-y-[-2px] hover:shadow-blue-200">
                        <div
                            class="flex items-center justify-center h-10 w-10 bg-white/20 rounded-lg mr-3 group-hover:scale-110 transition-transform duration-300">
                            <i data-lucide="play" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <span class="font-semibold block">Run Tests</span>
                            <span class="text-xs text-white/80">Execute in containerized environments</span>
                        </div>
                        <div
                            class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 group-hover:translate-x-1">
                            <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </div>
                    </a>

                    <a href="{{ route('projects.index') }}"
                        class="action-button group flex items-center px-6 py-4 bg-white border border-gray-200 text-gray-800 rounded-xl transition-all duration-300 shadow-sm hover:shadow-md hover:translate-y-[-2px] hover:border-gray-300">
                        <div
                            class="flex items-center justify-center h-10 w-10 bg-gray-100 rounded-lg mr-3 group-hover:scale-110 transition-transform duration-300">
                            <i data-lucide="folder" class="w-6 h-6 text-gray-600"></i>
                        </div>
                        <div>
                            <span class="font-semibold block">My Projects</span>
                            <span class="text-xs text-gray-500">Manage test suites and scripts</span>
                        </div>
                        <div
                            class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 group-hover:translate-x-1">
                            <i data-lucide="chevron-right" class="w-5 h-5 text-gray-400"></i>
                        </div>
                    </a>

                    <a href="{{ route('integrations.index') }}"
                        class="action-button group flex items-center px-6 py-4 bg-white border border-gray-200 text-gray-800 rounded-xl transition-all duration-300 shadow-sm hover:shadow-md hover:translate-y-[-2px] hover:border-gray-300">
                        <div
                            class="flex items-center justify-center h-10 w-10 bg-gray-100 rounded-lg mr-3 group-hover:scale-110 transition-transform duration-300">
                            <i data-lucide="plug" class="w-6 h-6 text-gray-600"></i>
                        </div>
                        <div>
                            <span class="font-semibold block">Integrations</span>
                            <span class="text-xs text-gray-500">Connect with Jira and other tools</span>
                        </div>
                        <div
                            class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 group-hover:translate-x-1">
                            <i data-lucide="chevron-right" class="w-5 h-5 text-gray-400"></i>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Main Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Test Activity Summary - Enhanced card design -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2 transition-all duration-300 hover:shadow-md">
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-50 rounded-lg mr-3">
                                <i data-lucide="list-checks" class="w-5 h-5 text-blue-500"></i>
                            </div>
                            <h2 class="text-xl font-semibold text-gray-900">Recent Test Executions</h2>
                        </div>
                        <a href="{{ route('test-executions.index') }}"
                            class="text-sm text-blue-600 hover:text-blue-800 flex items-center hover:underline">
                            See all
                            <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                        </a>
                    </div>
                    <div class="px-6 py-4">
                        @if ($recentExecutions && count($recentExecutions) > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th
                                                class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-lg">
                                                Test Name</th>
                                            <th
                                                class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status</th>
                                            <th
                                                class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Duration</th>
                                            <th
                                                class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date</th>
                                            <th
                                                class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tr-lg">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($recentExecutions as $execution)
                                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $execution->testScript->name }}</div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $execution->testScript->testSuite->name }}</div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span
                                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if ($execution->executionStatus->name === 'Passed') bg-green-100 text-green-800 border border-green-200
                                                    @elseif($execution->executionStatus->name === 'Failed') bg-red-100 text-red-800 border border-red-200
                                                    @elseif($execution->executionStatus->name === 'Running') bg-blue-100 text-blue-800 border border-blue-200
                                                    @else bg-gray-100 text-gray-800 border border-gray-200 @endif">
                                                        <span class="flex items-center">
                                                            @if ($execution->executionStatus->name === 'Passed')
                                                                <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                                            @elseif($execution->executionStatus->name === 'Failed')
                                                                <i data-lucide="x-circle" class="w-3 h-3 mr-1"></i>
                                                            @elseif($execution->executionStatus->name === 'Running')
                                                                <i data-lucide="loader"
                                                                    class="w-3 h-3 mr-1 animate-spin"></i>
                                                            @else
                                                                <i data-lucide="circle" class="w-3 h-3 mr-1"></i>
                                                            @endif
                                                            {{ $execution->executionStatus->name }}
                                                        </span>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                    @if ($execution->start_time && $execution->end_time)
                                                        <span class="flex items-center">
                                                            <i data-lucide="clock" class="w-4 h-4 mr-1 text-gray-400"></i>
                                                            {{ gmdate('i:s', strtotime($execution->end_time) - strtotime($execution->start_time)) }}
                                                        </span>
                                                    @elseif($execution->start_time)
                                                        <span class="flex items-center text-blue-600">
                                                            <i data-lucide="loader" class="w-4 h-4 mr-1 animate-spin"></i>
                                                            Running...
                                                        </span>
                                                    @else
                                                        <span class="flex items-center text-gray-400">
                                                            <i data-lucide="clock" class="w-4 h-4 mr-1"></i>
                                                            Pending
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                    <span class="flex items-center">
                                                        <i data-lucide="calendar" class="w-4 h-4 mr-1 text-gray-400"></i>
                                                        {{ $execution->created_at->format('M d, Y H:i') }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('test-executions.show', $execution->id) }}"
                                                        class="text-blue-600 hover:text-blue-900 inline-flex items-center hover:underline">
                                                        <i data-lucide="eye" class="w-4 h-4 mr-1"></i>
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12 px-4">
                                <img src="{{ asset('images/empty-state.svg') }}" alt="No tests"
                                    class="w-40 h-40 mx-auto mb-4 opacity-80"
                                    onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNTAgMjUwIiBmaWxsPSJub25lIiBzdHJva2U9IiM5Q0EzQUYiIHN0cm9rZS13aWR0aD0iMiI+PGNpcmNsZSBjeD0iMTI1IiBjeT0iMTI1IiByPSI4MCIvPjxwYXRoIGQ9Ik04NSAxMDVjMTAtMjAgNDAtMzAgNzAtMTAgTTE4NSAxNzVjLTEwIDEwLTQwIDIwLTgwLTUiLz48cGF0aCBkPSJNODUgMTc1YzEwLTEwIDQwLTIwIDgwIDUgTTE4NSAxMDVjLTEwLTIwLTQwLTMwLTcwLTEwIi8+PC9zdmc+'" />
                                <h3 class="text-lg font-medium text-gray-900 mb-1">No test executions yet</h3>
                                <p class="text-gray-500 mb-6">Run your first test to see results here</p>
                                <a href="{{ route('test-executions.create') }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 hover:shadow-md hover:translate-y-[-1px]">
                                    <i data-lucide="play" class="w-4 h-4 mr-2"></i>
                                    Run a Test
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Projects Summary - Enhanced card design -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md">
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center">
                        <div class="p-2 bg-purple-50 rounded-lg mr-3">
                            <i data-lucide="folder" class="w-5 h-5 text-purple-500"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900">Your Projects</h2>
                    </div>
                    <div class="px-6 py-4">
                        @if ($projects && count($projects) > 0)
                            <ul class="divide-y divide-gray-100">
                                @foreach ($projects as $project)
                                    <li class="py-3">
                                        <a href="{{ route('projects.show', $project->id) }}"
                                            class="block hover:bg-gray-50 transition-all duration-300 -mx-6 px-6 py-2 rounded-md group">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <h3
                                                        class="text-sm font-medium text-gray-900 group-hover:text-indigo-600 transition-colors duration-300">
                                                        {{ $project->name }}</h3>
                                                    <p class="text-xs text-gray-500 mt-1 flex items-center">
                                                        <i data-lucide="layers" class="w-3 h-3 mr-1"></i>
                                                        {{ $project->test_suites_count ?? 0 }} test suites
                                                    </p>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <span
                                                        class="bg-blue-50 text-blue-700 text-xs px-2 py-1 rounded-full flex items-center border border-blue-100">
                                                        <i data-lucide="file-code" class="w-3 h-3 mr-1"></i>
                                                        {{ $project->test_scripts_count ?? 0 }} tests
                                                    </span>
                                                    <div
                                                        class="w-6 h-6 rounded-full flex items-center justify-center bg-gray-100 text-gray-400 opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all duration-300">
                                                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="pt-4 mt-2 border-t border-gray-100">
                                <a href="{{ route('projects.index') }}"
                                    class="text-sm text-blue-600 hover:text-blue-800 flex items-center hover:underline">
                                    View all projects
                                    <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                                </a>
                            </div>
                        @else
                            <div class="text-center py-10">
                                <img src="{{ asset('images/empty-folder.svg') }}" alt="No projects"
                                    class="w-32 h-32 mx-auto mb-4 opacity-60"
                                    onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMjAgMTIwIiBmaWxsPSJub25lIiBzdHJva2U9IiM5Q0EzQUYiIHN0cm9rZS13aWR0aD0iMiI+PHBhdGggZD0iTTIwIDMwaDMwbDEwIDEwaDE1YzUgMCAxMCA1IDEwIDEwdjQwYzAgNS01IDEwLTEwIDEwSDMwYy01IDAtMTAtNS0xMC0xMFY0MGMwLTUgNS0xMCAxMC0xMHoiLz48cGF0aCBkPSJNNDAgNjBsOCAxMCAxNi0yMCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+PHBhdGggZD0iTTcwIDYwbDgtMTAgMTYgMjAiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPjwvc3ZnPg=='" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No projects</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by creating a new project</p>
                                <div class="mt-4">
                                    <a href="{{ route('projects.create') }}"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-300 hover:shadow-md hover:translate-y-[-1px]">
                                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                        Create Project
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Test Execution Metrics - Enhanced card design -->
                {{-- Continue from Test Execution Metrics section --}}
            </div>

            <!-- Jira Stories - Enhanced card design -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-2 bg-orange-50 rounded-lg mr-3">
                            <i data-lucide="file-text" class="w-5 h-5 text-orange-500"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900">Jira Stories</h2>
                    </div>
                    @if ($jiraStories && count($jiraStories) > 0)
                        <a href="{{ route('jira-stories.index') }}"
                            class="text-sm text-blue-600 hover:text-blue-800 flex items-center hover:underline">
                            See all
                            <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                        </a>
                    @endif
                </div>
                <div class="px-6 py-4">
                    @if ($jiraStories && count($jiraStories) > 0)
                        <ul class="divide-y divide-gray-100">
                            @foreach ($jiraStories as $story)
                                <li class="py-3">
                                    <a href="{{ route('jira-stories.show', $story->id) }}"
                                        class="block hover:bg-gray-50 transition-all duration-300 -mx-6 px-6 py-2 rounded-md group">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <div class="flex items-center mb-1">
                                                    <span
                                                        class="bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full mr-2 border border-blue-200">{{ $story->jira_key }}</span>
                                                    <h3
                                                        class="text-sm font-medium text-gray-900 group-hover:text-orange-600 transition-colors duration-300 truncate">
                                                        {{ $story->title }}</h3>
                                                </div>
                                                <p class="text-xs text-gray-500 mt-1 flex items-center">
                                                    <i data-lucide="align-left" class="w-3 h-3 mr-1"></i>
                                                    {{ Str::limit($story->description, 60) }}
                                                </p>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs text-gray-500">
                                                    {{ $story->created_at->diffForHumans() }}
                                                </span>
                                                <div
                                                    class="w-6 h-6 rounded-full flex items-center justify-center bg-gray-100 text-gray-400 opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all duration-300">
                                                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-10">
                            <img src="{{ asset('images/jira-empty.svg') }}" alt="No Jira stories"
                                class="w-32 h-32 mx-auto mb-4 opacity-60"
                                onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMjAgMTIwIiBmaWxsPSJub25lIiBzdHJva2U9IiM5Q0EzQUYiIHN0cm9rZS13aWR0aD0iMiI+PHBhdGggZD0iTTMwIDIwaDYwbDEwIDEwaDIwYzUgMCAxMCA1IDEwIDEwdjQwYzAgNS01IDEwLTEwIDEwSDMwYy01IDAtMTAtNS0xMC0xMFY0MGMwLTUgNS0xMCAxMC0xMHoiLz48cGF0aCBkPSJNNDAgNjBsMTAtMTUgMTUgMjAgMjAtMjUgMTUgMjAiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPjwvc3ZnPg=='" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No Jira stories connected</h3>
                            <p class="mt-1 text-sm text-gray-500">Configure Jira integration to sync stories</p>
                            <div class="mt-4">
                                <a href="{{ route('integrations.create') }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-md hover:translate-y-[-1px]">
                                    <i data-lucide="plug" class="w-4 h-4 mr-2"></i>
                                    Connect Jira
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Resource Usage - Enhanced card design -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md lg:col-span-2">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center">
                    <div class="p-2 bg-cyan-50 rounded-lg mr-3">
                        <i data-lucide="server" class="w-5 h-5 text-cyan-500"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900">Resource Usage</h2>
                </div>
                <div class="px-6 py-4">
                    @if ($resourceStats && !empty($resourceStats))
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div
                                    class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-xl border border-blue-200">
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-sm font-medium text-gray-700 flex items-center">
                                            <i data-lucide="container" class="w-4 h-4 mr-2 text-blue-500"></i>
                                            Container Hours
                                        </span>
                                        <span class="text-sm text-blue-600 font-medium">
                                            {{ $resourceStats['container_hours'] ?? 0 }}/{{ $resourceStats['container_quota'] ?? '∞' }}
                                        </span>
                                    </div>
                                    <div class="h-2 bg-gray-200 rounded-full overflow-hidden shadow-inner">
                                        @php
                                            $usagePercent =
                                                $resourceStats['container_quota'] > 0
                                                    ? min(
                                                        100,
                                                        round(
                                                            ($resourceStats['container_hours'] /
                                                                $resourceStats['container_quota']) *
                                                                100,
                                                        ),
                                                    )
                                                    : 0;
                                        @endphp
                                        <div class="h-full bg-gradient-to-r from-blue-400 to-blue-500 rounded-full transition-all duration-1000"
                                            style="width: {{ $usagePercent }}%"></div>
                                    </div>
                                </div>

                                <div
                                    class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-xl border border-purple-200">
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-sm font-medium text-gray-700 flex items-center">
                                            <i data-lucide="database" class="w-4 h-4 mr-2 text-purple-500"></i>
                                            Storage Usage
                                        </span>
                                        <span class="text-sm text-purple-600 font-medium">
                                            {{ $resourceStats['storage_used'] ?? '0 GB' }}/{{ $resourceStats['storage_quota'] ?? 'Unlimited' }}
                                        </span>
                                    </div>
                                    <div class="h-2 bg-gray-200 rounded-full overflow-hidden shadow-inner">
                                        @php
                                            $storagePercent =
                                                $resourceStats['storage_quota_value'] > 0
                                                    ? min(
                                                        100,
                                                        round(
                                                            ($resourceStats['storage_used_value'] /
                                                                $resourceStats['storage_quota_value']) *
                                                                100,
                                                        ),
                                                    )
                                                    : 0;
                                        @endphp
                                        <div class="h-full bg-gradient-to-r from-purple-400 to-purple-500 rounded-full transition-all duration-1000"
                                            style="width: {{ $storagePercent }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-gradient-to-br from-gray-50 to-gray-100 p-6 rounded-xl border border-gray-200 flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-500 mb-1">Current Plan</div>
                                    <div class="text-2xl font-bold text-gray-900 mb-2">
                                        {{ $subscription->plan_type ?? 'Free Tier' }}</div>
                                    <a href="{{ route('subscriptions.index') }}"
                                        class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium transition-all duration-300 hover:underline">
                                        Upgrade Plan
                                        <i data-lucide="arrow-up-right" class="w-4 h-4 ml-1"></i>
                                    </a>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-500 mb-1">Next Billing Cycle</div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $subscription->next_billing_date ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-10">
                            <img src="{{ asset('images/server-empty.svg') }}" alt="No usage data"
                                class="w-32 h-32 mx-auto mb-4 opacity-60"
                                onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMjAgMTIwIiBmaWxsPSJub25lIiBzdHJva2U9IiM5Q0EzQUYiIHN0cm9rZS13aWR0aD0iMiI+PHBhdGggZD0iTTQwIDMwaDQwbDEwIDEwaDEwYzUgMCAxMCA1IDEwIDEwdjQwYzAgNS01IDEwLTEwIDEwSDQwYy01IDAtMTAtNS0xMC0xMFY1MGMwLTUgNS0xMCAxMC0xMHoiLz48Y2lyY2xlIGN4PSI2MCIgY3k9IjMwIiByPSI0Ii8+PGNpcmNsZSBjeD0iNjAiIGN5PSI3MCIgcj0iNCIvPjwvc3ZnPg=='" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No resource data available</h3>
                            <p class="mt-1 text-sm text-gray-500">Usage statistics will appear after running tests</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Getting Started Section - Enhanced design -->
        @if (!$hasActivity)
            <div
                class="mt-8 bg-gradient-to-br from-blue-50/80 to-purple-50/80 rounded-xl shadow-sm border border-gray-100 backdrop-blur-sm p-6 relative overflow-hidden">
                <!-- Decorative elements -->
                <div
                    class="absolute -top-12 -right-12 w-32 h-32 bg-gradient-to-br from-blue-200/30 to-purple-200/30 rounded-full blur-xl">
                </div>
                <div
                    class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-green-200/20 to-blue-200/20 rounded-full blur-xl">
                </div>

                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <span
                        class="bg-gradient-to-br from-blue-600 to-purple-600 text-white w-8 h-8 rounded-full flex items-center justify-center mr-3">!</span>
                    Get Started with Arxitest
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        class="bg-white/90 backdrop-blur-sm p-6 rounded-xl shadow-sm border border-gray-100 transition-all duration-300 hover:shadow-md hover:-translate-y-1">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                                <i data-lucide="folder-plus" class="w-5 h-5 text-blue-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Create Project</h3>
                        </div>
                        <p class="text-gray-600 text-sm mb-4">Organize your tests and scripts in dedicated projects for
                            better management.</p>
                        <a href="{{ route('projects.create') }}"
                            class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium group transition-all duration-300">
                            Start Creating
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform duration-300"></i>
                        </a>
                    </div>

                    <div
                        class="bg-white/90 backdrop-blur-sm p-6 rounded-xl shadow-sm border border-gray-100 transition-all duration-300 hover:shadow-md hover:-translate-y-1">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
                                <i data-lucide="git-pull-request" class="w-5 h-5 text-green-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Connect Jira</h3>
                        </div>
                        <p class="text-gray-600 text-sm mb-4">Sync your Jira stories and push test results directly to your
                            tickets.</p>
                        <a href="{{ route('integrations.create') }}"
                            class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium group transition-all duration-300">
                            Connect Now
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform duration-300"></i>
                        </a>
                    </div>

                    <div
                        class="bg-white/90 backdrop-blur-sm p-6 rounded-xl shadow-sm border border-gray-100 transition-all duration-300 hover:shadow-md hover:-translate-y-1">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center mr-3">
                                <i data-lucide="bot" class="w-5 h-5 text-purple-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Generate Tests</h3>
                        </div>
                        <p class="text-gray-600 text-sm mb-4">Use AI-powered test generation to create scripts from user
                            stories.</p>
                        <a href="{{ route('test-scripts.create') }}"
                            class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium group transition-all duration-300">
                            Try AI Builder
                            <i data-lucide="arrow-right"
                                class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform duration-300"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Custom animations */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .dot-animation {
            animation: float 4s ease-in-out infinite;
        }

        .action-button:hover {
            animation: pulse 1.5s ease-in-out infinite;
        }

        .parallax-element {
            transition: transform 0.3s ease-out;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Parallax effect for background elements
            const parallaxElements = document.querySelectorAll('.parallax-element');
            document.addEventListener('mousemove', (e) => {
                const x = (e.clientX / window.innerWidth - 0.5) * 20;
                const y = (e.clientY / window.innerHeight - 0.5) * 20;

                parallaxElements.forEach(element => {
                    element.style.transform = `translate(${x}px, ${y}px)`;
                });
            });

            // Animate progress bars on scroll
            const progressBars = document.querySelectorAll('.progress-bar');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const width = entry.target.getAttribute('data-width');
                        entry.target.style.width = width;
                    }
                });
            }, {
                threshold: 0.5
            });

            progressBars.forEach(bar => observer.observe(bar));
        });
    </script>
@endpush
