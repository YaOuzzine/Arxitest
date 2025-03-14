@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Test Executions</h1>
            <p class="text-gray-600">View and manage your test execution history</p>
        </div>
        <a href="{{ route('test-executions.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
            </svg>
            Run New Test
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form action="{{ route('test-executions.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status" class="border border-gray-300 rounded-md w-full py-2 px-3">
                    <option value="">All Statuses</option>
                    @foreach(\App\Models\ExecutionStatus::all() as $status)
                        <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="environment" class="block text-sm font-medium text-gray-700 mb-1">Environment</label>
                <select id="environment" name="environment" class="border border-gray-300 rounded-md w-full py-2 px-3">
                    <option value="">All Environments</option>
                    @foreach(\App\Models\Environment::where('is_active', true)->get() as $env)
                        <option value="{{ $env->id }}" {{ request('environment') == $env->id ? 'selected' : '' }}>
                            {{ $env->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="date_range" class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                <select id="date_range" name="date_range" class="border border-gray-300 rounded-md w-full py-2 px-3">
                    <option value="">All Time</option>
                    <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="custom" {{ request('date_range') == 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>

            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <div class="relative">
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search test executions..." class="border border-gray-300 rounded-md w-full py-2 pl-10 pr-3">
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

    <!-- Custom Date Range (hidden by default) -->
    <div id="custom-date-container" class="bg-white rounded-lg shadow mb-6 p-4 {{ request('date_range') == 'custom' ? '' : 'hidden' }}">
        <form id="custom-date-form" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="border border-gray-300 rounded-md w-full py-2 px-3">
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="border border-gray-300 rounded-md w-full py-2 px-3">
            </div>

            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                    Apply Date Range
                </button>
            </div>
        </form>
    </div>

    <!-- Execution Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Total Executions</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
            <div class="mt-1 text-sm text-gray-500">
                @if(isset($stats['trend']) && $stats['trend'] > 0)
                    <span class="text-green-600">↑ {{ $stats['trend'] }}%</span> from previous period
                @elseif(isset($stats['trend']) && $stats['trend'] < 0)
                    <span class="text-red-600">↓ {{ abs($stats['trend']) }}%</span> from previous period
                @else
                    No change from previous period
                @endif
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Success Rate</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ $stats['passed'] ?? 0 }} / {{ $stats['total'] ?? 0 }}
                ({{ $stats['total'] > 0 ? round(($stats['passed'] / $stats['total']) * 100) : 0 }}%)
            </p>
            <div class="mt-1 text-sm text-gray-500">
                @if(isset($stats['success_trend']) && $stats['success_trend'] > 0)
                    <span class="text-green-600">↑ {{ $stats['success_trend'] }}%</span> better success rate
                @elseif(isset($stats['success_trend']) && $stats['success_trend'] < 0)
                    <span class="text-red-600">↓ {{ abs($stats['success_trend']) }}%</span> worse success rate
                @else
                    Stable success rate
                @endif
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Average Duration</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['avg_duration'] ?? '0s' }}</p>
            <div class="mt-1 text-sm text-gray-500">
                @if(isset($stats['duration_trend']) && $stats['duration_trend'] < 0)
                    <span class="text-green-600">↓ {{ abs($stats['duration_trend']) }}%</span> faster than before
                @elseif(isset($stats['duration_trend']) && $stats['duration_trend'] > 0)
                    <span class="text-red-600">↑ {{ $stats['duration_trend'] }}%</span> slower than before
                @else
                    No change in execution speed
                @endif
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Resource Usage</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['resource_usage'] ?? '0 hrs' }}</p>
            <div class="mt-1 text-sm text-gray-500">
                {{ $stats['resource_percentage'] ?? '0' }}% of subscription limit
            </div>
        </div>
    </div>

    <!-- Test Executions Table -->
    @if(isset($testExecutions) && count($testExecutions) > 0)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Script</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Environment</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($testExecutions as $execution)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="ml-0">
                                        <div class="text-sm font-medium text-gray-900">
                                            <a href="{{ route('test-scripts.show', $execution->testScript->id ?? '') }}" class="hover:text-blue-600">
                                                {{ $execution->testScript->name ?? 'Unknown Script' }}
                                            </a>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $execution->testScript->testSuite->name ?? '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($execution->executionStatus->name == 'Passed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Passed
                                    </span>
                                @elseif($execution->executionStatus->name == 'Failed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Failed
                                    </span>
                                @elseif($execution->executionStatus->name == 'Running')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Running
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $execution->executionStatus->name }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">{{ $execution->environment->name ?? 'Unknown' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">
                                    {{ $execution->start_time ? $execution->start_time->format('M d, Y H:i') : 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">
                                    @if($execution->start_time && $execution->end_time)
                                        {{ $execution->start_time->diffInSeconds($execution->end_time) }}s
                                    @elseif($execution->start_time && !$execution->end_time && $execution->executionStatus->name == 'Running')
                                        <span class="text-blue-600 animate-pulse">In progress</span>
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('test-executions.show', $execution->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">View</a>
                                @if($execution->executionStatus->name == 'Running')
                                    <form method="POST" action="{{ route('test-executions.destroy', $execution->id) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to cancel this execution?')">
                                            Cancel
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $testExecutions->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900">No executions found</h3>
            <p class="mt-1 text-gray-500">Looks like you haven't run any tests that match your filters.</p>
            <div class="mt-6">
                <a href="{{ route('test-executions.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                    Run a Test
                </a>
            </div>
        </div>
    @endif
</div>

<script>
    // Toggle custom date range fields
    document.getElementById('date_range').addEventListener('change', function() {
        const customDateContainer = document.getElementById('custom-date-container');
        if (this.value === 'custom') {
            customDateContainer.classList.remove('hidden');
        } else {
            customDateContainer.classList.add('hidden');
        }
    });

    // Handle custom date form submission
    document.getElementById('custom-date-form').addEventListener('submit', function(e) {
        e.preventDefault();

        // Get current URL parameters
        const urlParams = new URLSearchParams(window.location.search);

        // Update or add date parameters
        urlParams.set('date_range', 'custom');
        urlParams.set('start_date', document.getElementById('start_date').value);
        urlParams.set('end_date', document.getElementById('end_date').value);

        // Redirect with updated parameters
        window.location.href = window.location.pathname + '?' + urlParams.toString();
    });
</script>
@endsection
