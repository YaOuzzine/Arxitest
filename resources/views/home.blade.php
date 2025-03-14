@extends('layouts.app')

@section('content')
<div class="py-6 px-8">
    <!-- Welcome Section with Summary Cards -->
    <div class="mb-8">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}</h1>
                <p class="text-gray-600">Here's what's happening with your test automation today</p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex space-x-3">
                <a href="{{ route('test-scripts.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-lucide="file-plus" class="w-4 h-4 mr-2"></i>
                    New Test Script
                </a>
                <div class="relative inline-block text-left">
                    <button type="button" id="runTestDropdown" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i data-lucide="play" class="w-4 h-4 mr-2"></i>
                        Run Tests
                        <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>
                    </button>
                    <!-- Dropdown menu -->
                    <div id="runTestMenu" class="hidden absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                        <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="runTestDropdown">
                            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                <i data-lucide="repeat" class="w-4 h-4 mr-2 text-green-500"></i>
                                Re-run last test
                            </a>
                            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                <i data-lucide="folder" class="w-4 h-4 mr-2 text-blue-500"></i>
                                Run by project
                            </a>
                            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                <i data-lucide="trello" class="w-4 h-4 mr-2 text-purple-500"></i>
                                Run by Jira story
                            </a>
                            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2 text-gray-500"></i>
                                Select test scripts
                            </a>
                        </div>
                    </div>
                </div>
                <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Stats Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                            <!-- Test Status Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 bg-gradient-to-r from-blue-50 to-white border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-sm font-semibold text-gray-700">Test Status</h3>
                        <span class="text-xs text-gray-500">Last 30 days</span>
                    </div>
                </div>
                <div class="p-5">
                    <div class="flex justify-between items-center mb-2">
                        @php
                            $total = isset($executionStats) && is_array($executionStats) && isset($executionStats['total']) ? $executionStats['total'] : 0;
                            $passed = isset($executionStats) && is_array($executionStats) && isset($executionStats['passed']) ? $executionStats['passed'] : 0;
                            $failed = isset($executionStats) && is_array($executionStats) && isset($executionStats['failed']) ? $executionStats['failed'] : 0;
                            $running = isset($executionStats) && is_array($executionStats) && isset($executionStats['running']) ? $executionStats['running'] : 0;
                        @endphp
                        <span class="text-3xl font-bold text-gray-800">{{ $total }}</span>
                        <div class="flex space-x-1">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i data-lucide="check" class="w-3 h-3 mr-1"></i>
                                {{ $passed }}
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i data-lucide="x" class="w-3 h-3 mr-1"></i>
                                {{ $failed }}
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i data-lucide="loader" class="w-3 h-3 mr-1"></i>
                                {{ $running }}
                            </span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        @php
                            $passRate = $total > 0 ? round(($passed / $total) * 100) : 0;
                            $failRate = $total > 0 ? round(($failed / $total) * 100) : 0;
                            $runningRate = $total > 0 ? round(($running / $total) * 100) : 0;
                        @endphp
                        <div class="flex rounded-full h-2 overflow-hidden">
                            <div class="bg-green-500 h-2" style="width: {{ $passRate }}%"></div>
                            <div class="bg-red-500 h-2" style="width: {{ $failRate }}%"></div>
                            <div class="bg-blue-500 h-2" style="width: {{ $runningRate }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center">
                        View all test executions
                        <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                    </a>
                </div>
            </div>

            <!-- Resource Usage Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 bg-gradient-to-r from-purple-50 to-white border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-sm font-semibold text-gray-700">Resource Usage</h3>
                        <span class="text-xs text-gray-500">Current billing period</span>
                    </div>
                </div>
                <div class="p-5">
                    <div class="space-y-3">
                        <div>
                            @php
                                $containerHours = isset($resourceStats) && is_array($resourceStats) && isset($resourceStats['container_hours']) ? $resourceStats['container_hours'] : 0;
                                $containerQuota = isset($resourceStats) && is_array($resourceStats) && isset($resourceStats['container_quota']) ? $resourceStats['container_quota'] : '∞';

                                $storageUsed = isset($resourceStats) && is_array($resourceStats) && isset($resourceStats['storage_used']) ? $resourceStats['storage_used'] : '0 GB';
                                $storageQuota = isset($resourceStats) && is_array($resourceStats) && isset($resourceStats['storage_quota']) ? $resourceStats['storage_quota'] : '0 GB';

                                $storageUsedValue = isset($resourceStats) && is_array($resourceStats) && isset($resourceStats['storage_used_value']) ? $resourceStats['storage_used_value'] : 0;
                                $storageQuotaValue = isset($resourceStats) && is_array($resourceStats) && isset($resourceStats['storage_quota_value']) ? $resourceStats['storage_quota_value'] : 1;
                            @endphp
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-600">Container Hours</span>
                                <span class="text-gray-700">{{ $containerHours }} / {{ $containerQuota }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                @php
                                    $containerPercent = is_numeric($containerHours) && is_numeric($containerQuota) && $containerQuota > 0 ? min(100, round(($containerHours / $containerQuota) * 100)) : 0;
                                @endphp
                                <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $containerPercent }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-600">Storage</span>
                                <span class="text-gray-700">{{ $storageUsed }} / {{ $storageQuota }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                @php
                                    $storagePercent = $storageQuotaValue > 0 ? min(100, round(($storageUsedValue / $storageQuotaValue) * 100)) : 0;
                                @endphp
                                <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $storagePercent }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center">
                        View billing details
                        <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                    </a>
                </div>
            </div>

            <!-- Project Stats Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 bg-gradient-to-r from-green-50 to-white border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-sm font-semibold text-gray-700">Projects</h3>
                        <span class="text-xs text-gray-500">{{ is_array($projects ?? null) ? count($projects) : 0 }} total</span>
                    </div>
                </div>
                <div class="p-5">
                    @php
                        $projectsArray = is_array($projects ?? null) ? $projects : [];

                        // Safely calculate total test scripts
                        $totalTestScripts = 0;
                        foreach ($projectsArray as $project) {
                            if (isset($project->test_scripts_count)) {
                                $totalTestScripts += $project->test_scripts_count;
                            }
                        }

                        // Safely calculate total test suites
                        $totalTestSuites = 0;
                        foreach ($projectsArray as $project) {
                            if (isset($project->test_suites_count)) {
                                $totalTestSuites += $project->test_suites_count;
                            }
                        }
                    @endphp
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <span class="text-3xl font-bold text-gray-800">{{ $totalTestScripts }}</span>
                            <span class="text-sm text-gray-500 ml-1">test scripts</span>
                        </div>
                        <div>
                            <span class="text-3xl font-bold text-gray-800">{{ $totalTestSuites }}</span>
                            <span class="text-sm text-gray-500 ml-1">suites</span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="flex items-center justify-center">
                            <div class="w-full h-9 flex">
                                @foreach($projectsArray as $index => $project)
                                    @php
                                        $colors = ['bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 'bg-purple-500'];
                                        $color = $colors[$index % count($colors)];
                                        $width = count($projectsArray) > 0 ? (100 / count($projectsArray)) : 0;
                                    @endphp
                                    <div class="{{ $color }} h-9 rounded-lg mx-0.5" style="width: {{ $width }}%" title="{{ $project->name ?? 'Project' }}"></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
                    <a href="{{ route('projects.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center">
                        Manage projects
                        <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                    </a>
                </div>
            </div>

            <!-- AI Test Generation Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 bg-gradient-to-r from-yellow-50 to-white border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-sm font-semibold text-gray-700">AI Test Generation</h3>
                        <span class="text-xs px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">Beta</span>
                    </div>
                </div>
                <div class="p-5">
                    <p class="text-sm text-gray-600 mb-4">Generate test scripts automatically from your Jira stories using our AI-powered assistant.</p>
                    <div class="flex space-x-2">
                        <a href="{{ session('jira_access_token') ? url('/jira/import') : url('/jira/oauth') }}" class="flex-1 flex items-center justify-center px-3 py-2 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white text-sm rounded-lg hover:from-yellow-600 hover:to-yellow-700">
                            <i data-lucide="sparkles" class="w-4 h-4 mr-2"></i>
                            {{ session('jira_access_token') ? 'Generate from Jira' : 'Connect to Jira' }}
                        </a>
                        <button class="flex-1 flex items-center justify-center px-3 py-2 bg-white border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>
                            Custom script
                        </button>
                    </div>
                </div>
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center">
                        Learn about AI features
                        <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity and Current Status -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Recent Test Executions (2/3 width) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800">Recent Test Executions</h2>
                        <div class="flex items-center space-x-2">
                            <div class="relative">
                                <select class="text-sm border-gray-300 rounded-md pr-8 py-1.5 bg-white focus:ring-blue-500 focus:border-blue-500">
                                    <option>All Status</option>
                                    <option>Passed</option>
                                    <option>Failed</option>
                                    <option>Running</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                </div>
                            </div>
                            <a href="/test-executions" class="text-sm text-blue-600 hover:text-blue-700">View All</a>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Script</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Executed At</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentExecutions ?? [] as $execution)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($execution->testScript->framework_type == 'selenium_python')
                                                <i data-lucide="file-code" class="text-blue-500 w-5 h-5 mr-2"></i>
                                            @elseif($execution->testScript->framework_type == 'cypress')
                                                <i data-lucide="file-code" class="text-green-500 w-5 h-5 mr-2"></i>
                                            @else
                                                <i data-lucide="file-code" class="text-gray-500 w-5 h-5 mr-2"></i>
                                            @endif
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $execution->testScript->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $execution->testScript->testSuite->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($execution->executionStatus->name == 'Passed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                <i data-lucide="check" class="w-3 h-3 mr-1"></i> Passed
                                            </span>
                                        @elseif($execution->executionStatus->name == 'Failed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                <i data-lucide="x" class="w-3 h-3 mr-1"></i> Failed
                                            </span>
                                        @elseif($execution->executionStatus->name == 'Running')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                <i data-lucide="loader" class="w-3 h-3 mr-1 animate-spin"></i> Running
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ $execution->executionStatus->name }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($execution->start_time && $execution->end_time)
                                            {{ $execution->start_time->diffInSeconds($execution->end_time) }}s
                                        @elseif($execution->start_time)
                                            Running ({{ $execution->start_time->diffInSeconds(now()) }}s)
                                        @else
                                            --
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $execution->created_at ? $execution->created_at->format('M d, Y H:i') : '--' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button class="text-blue-600 hover:text-blue-900" title="View Details">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </button>
                                            <button class="text-green-600 hover:text-green-900" title="Re-run Test">
                                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-900" title="Delete">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i data-lucide="inbox" class="w-12 h-12 mb-4 text-gray-300"></i>
                                            <p>No recent test executions found</p>
                                            <a href="#" class="mt-2 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                                Run your first test
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-800">Recent Projects</h2>
                    <a href="{{ route('projects.index') }}" class="text-sm text-blue-600 hover:text-blue-700">View All Projects</a>
                </div>
                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Suites</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Scripts</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($projects ?? [] as $project)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-md bg-blue-100 text-blue-600">
                                                <i data-lucide="folder" class="w-6 h-6"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $project->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $project->description ?? 'No description' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $project->test_suites_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $project->test_scripts_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $project->updated_at->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('projects.show', $project->id) }}" class="text-blue-600 hover:text-blue-900" title="View Details">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>
                                            <a href="{{ route('projects.edit', $project->id) }}" class="text-green-600 hover:text-green-900" title="Edit">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                            </a>
                                            <button class="text-blue-600 hover:text-blue-900" title="Run Tests">
                                                <i data-lucide="play" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i data-lucide="folder" class="w-12 h-12 mb-4 text-gray-300"></i>
                                            <p>No projects found</p>
                                            <a href="{{ route('projects.create') }}" class="mt-2 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                                Create your first project
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Jira Stories and Team Activity (1/3 width) -->
        <div class="space-y-6">
            <!-- Quick Tips & Resources Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">Getting Started</h2>
                </div>
                <div class="p-5">
                    <ul class="space-y-3">
                        <li>
                            <a href="#" class="flex items-start hover:bg-gray-50 p-2 rounded-lg">
                                <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-md flex items-center justify-center mr-3 flex-shrink-0">
                                    <i data-lucide="book-open" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">Arxitest Documentation</h3>
                                    <p class="text-xs text-gray-500 mt-1">Learn how to use all features</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-start hover:bg-gray-50 p-2 rounded-lg">
                                <div class="w-8 h-8 bg-green-100 text-green-600 rounded-md flex items-center justify-center mr-3 flex-shrink-0">
                                    <i data-lucide="play" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">Video Tutorials</h3>
                                    <p class="text-xs text-gray-500 mt-1">Watch step-by-step guides</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-start hover:bg-gray-50 p-2 rounded-lg">
                                <div class="w-8 h-8 bg-yellow-100 text-yellow-600 rounded-md flex items-center justify-center mr-3 flex-shrink-0">
                                    <i data-lucide="message-square" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">Community Support</h3>
                                    <p class="text-xs text-gray-500 mt-1">Join our forums for help</p>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- Jira Stories Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-800">Recent Jira Stories</h2>
                    @if(session('jira_access_token'))
                        <a href="{{ url('/jira/import') }}" class="text-sm text-blue-600 hover:text-blue-700">Import More</a>
                    @else
                        <a href="{{ url('/jira/oauth') }}" class="text-sm text-blue-600 hover:text-blue-700">Connect to Jira</a>
                    @endif
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($jiraStories ?? [] as $story)
                        <div class="px-6 py-4 hover:bg-gray-50">
                            <div class="flex items-start">
                                <span class="flex-shrink-0 w-8 h-8 bg-purple-100 text-purple-600 rounded-md flex items-center justify-center mr-3">
                                    <i data-lucide="trello" class="w-5 h-5"></i>
                                </span>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">{{ $story->title }}</h3>
                                    <p class="text-xs text-gray-500 mt-1">{{ $story->jira_key }}</p>
                                    <div class="flex items-center mt-2 space-x-2">
                                        <a href="#" class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-700 hover:bg-blue-200">
                                            <i data-lucide="sparkles" class="w-3 h-3 mr-1"></i>
                                            Generate Tests
                                        </a>
                                        <a href="#" class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-700 hover:bg-gray-200">
                                            <i data-lucide="external-link" class="w-3 h-3 mr-1"></i>
                                            View in Jira
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="px-6 py-10 text-center text-gray-500">
                            <i data-lucide="trello" class="w-12 h-12 mb-4 mx-auto text-gray-300"></i>
                            @if(session('jira_access_token'))
                                <p>No Jira stories imported yet</p>
                                <a href="{{ url('/jira/import') }}" class="mt-2 inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                                    Import Jira Stories
                                </a>
                            @else
                                <p>Connect to Jira to import stories</p>
                                <a href="{{ url('/jira/oauth') }}" class="mt-2 inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                                    Connect to Jira
                                </a>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>


    <!-- First-time User Experience Section (conditionally shown) -->
    @if(!$hasActivity ?? true)
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl shadow-sm border border-blue-200 p-6 mb-8">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-xl font-bold text-blue-800 mb-2">Welcome to Arxitest!</h2>
                <p class="text-blue-700 mb-6">Let's get you started with test automation in a few simple steps:</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-5 rounded-lg shadow-sm border border-blue-100">
                        <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4">
                            <span class="font-bold">1</span>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Create your first project</h3>
                        <p class="text-sm text-gray-600 mb-4">Set up a project to organize your tests and connect with your development workflow.</p>
                        <a href="{{ route('projects.create') }}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                            Create a project
                            <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                        </a>
                    </div>

                    <div class="bg-white p-5 rounded-lg shadow-sm border border-blue-100">
                        <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4">
                            <span class="font-bold">2</span>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Connect to Jira</h3>
                        <p class="text-sm text-gray-600 mb-4">Import user stories and acceptance criteria to automatically generate tests.</p>
                        <a href="{{ session('jira_access_token') ? url('/jira/import') : url('/jira/oauth') }}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                            {{ session('jira_access_token') ? 'Import Stories' : 'Set up integration' }}
                            <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                        </a>
                    </div>

                    <div class="bg-white p-5 rounded-lg shadow-sm border border-blue-100">
                        <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4">
                            <span class="font-bold">3</span>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Run your first test</h3>
                        <p class="text-sm text-gray-600 mb-4">Create or generate a test script and execute it in our containerized environment.</p>
                        <a href="#" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                            Try it now
                            <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                        </a>
                    </div>
                </div>

                <div class="flex justify-center mt-6">
                    <a href="#" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <i data-lucide="video" class="w-4 h-4 mr-2"></i>
                        Watch getting started tutorial
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    // Dropdown toggle functionality for the Run Tests dropdown
    document.addEventListener('DOMContentLoaded', function() {
        const runTestDropdown = document.getElementById('runTestDropdown');
        const runTestMenu = document.getElementById('runTestMenu');

        if (runTestDropdown && runTestMenu) {
            // Toggle the dropdown when clicking the button
            runTestDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
                runTestMenu.classList.toggle('hidden');
            });

            // Close the dropdown when clicking outside
            document.addEventListener('click', function() {
                runTestMenu.classList.add('hidden');
            });

            // Prevent closing when clicking inside the dropdown
            runTestMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    });
</script>
@endsection
