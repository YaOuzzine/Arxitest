@extends('layouts.dashboard')

@section('title', 'Execution Details')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.executions.index') }}" class="text-zinc-700 dark:text-zinc-300">Executions</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Execution #{{ $execution->id }}</span>
    </li>
@endsection

@section('content')
    <div x-data="{ activeTab: 'overview' }">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                    Test Execution #{{ $execution->id }}
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ $execution->testScript->name ?? 'Unknown script' }} on
                    {{ $execution->environment->name ?? 'Unknown environment' }}
                </p>
            </div>

            <div class="flex items-center space-x-3">
                @if ($execution->isRunning())
                    <form action="{{ route('dashboard.executions.abort', $execution->id) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to abort this execution?')">
                        @csrf
                        <button type="submit" class="btn-danger">
                            <i data-lucide="square" class="w-5 h-5 mr-1.5"></i>
                            Abort Execution
                        </button>
                    </form>
                @endif

                <a href="{{ route('dashboard.executions.create') }}" class="btn-primary">
                    <i data-lucide="play" class="w-5 h-5 mr-1.5"></i>
                    Run Another Test
                </a>
            </div>
        </div>

        <div
            class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden mb-6">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Status --}}
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Status</h3>
                        @php
                            $statusClass = match ($execution->status->name ?? 'unknown') {
                                'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                                'running'
                                    => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 animate-pulse',
                                'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                'failed' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                'aborted' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
                                'timeout' => 'bg-pink-100 text-pink-800 dark:bg-pink-900/40 dark:text-pink-300',
                                default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
                            };
                        @endphp
                        <span class="px-2.5 py-1 inline-flex text-sm font-semibold rounded-full {{ $statusClass }}">
                            {{ ucfirst($execution->status->name ?? 'Unknown') }}
                        </span>
                    </div>

                    {{-- Started --}}
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Started</h3>
                        <p class="text-lg font-medium text-zinc-900 dark:text-white">
                            {{ $execution->start_time ? $execution->start_time->format('M d, Y H:i:s') : 'Not started' }}
                        </p>
                    </div>

                    {{-- Duration --}}
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Duration</h3>
                        <p class="text-lg font-medium text-zinc-900 dark:text-white">
                            @if ($execution->start_time && $execution->end_time)
                                {{ $execution->start_time->diff($execution->end_time)->format('%H:%I:%S') }}
                            @elseif($execution->start_time && !$execution->end_time)
                                Running ({{ $execution->start_time->diffForHumans(null, true) }})
                            @else
                                Not available
                            @endif
                        </p>
                    </div>

                    {{-- Initiator --}}
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Initiated By</h3>
                        <p class="text-lg font-medium text-zinc-900 dark:text-white">
                            {{ $execution->initiator->name ?? 'System' }}
                        </p>
                    </div>

                    {{-- Framework --}}
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Framework</h3>
                        <p class="text-lg font-medium text-zinc-900 dark:text-white">
                            {{ ucfirst(str_replace('-', ' ', $execution->testScript->framework_type ?? 'Unknown')) }}
                        </p>
                    </div>

                    {{-- Container Count --}}
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Containers</h3>
                        <p class="text-lg font-medium text-zinc-900 dark:text-white">
                            {{ $execution->containers->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="mb-6 border-b border-zinc-200 dark:border-zinc-700">
            <ul class="flex flex-wrap -mb-px">
                <li class="mr-2">
                    <button @click="activeTab = 'overview'"
                        :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'overview', 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300': activeTab !== 'overview' }"
                        class="inline-block p-4 border-b-2 font-medium">
                        Overview
                    </button>
                </li>
                <li class="mr-2">
                    <button @click="activeTab = 'containers'"
                        :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'containers', 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300': activeTab !== 'containers' }"
                        class="inline-block p-4 border-b-2 font-medium">
                        Containers
                    </button>
                </li>
                <li class="mr-2">
                    <button @click="activeTab = 'logs'"
                        :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'logs', 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300': activeTab !== 'logs' }"
                        class="inline-block p-4 border-b-2 font-medium">
                        Logs
                    </button>
                </li>
                <li class="mr-2">
                    <button @click="activeTab = 'script'"
                        :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'script', 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300': activeTab !== 'script' }"
                        class="inline-block p-4 border-b-2 font-medium">
                        Script
                    </button>
                </li>
            </ul>
        </div>

        {{-- Tab Content --}}
        <div
            class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            {{-- Overview Tab --}}
            <div x-show="activeTab === 'overview'" class="p-6">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Execution Details</h2>

                <div class="mb-6">
                    <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Test Information</h3>
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4">
                        <dl class="divide-y divide-zinc-200 dark:divide-zinc-700/50">
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Test Script</dt>
                                <dd class="text-sm text-zinc-900 dark:text-white col-span-2">
                                    {{ $execution->testScript->name ?? 'Unknown' }}</dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Test Case</dt>
                                <dd class="text-sm text-zinc-900 dark:text-white col-span-2">
                                    {{ $execution->testScript->testCase->title ?? 'N/A' }}</dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Environment</dt>
                                <dd class="text-sm text-zinc-900 dark:text-white col-span-2">
                                    {{ $execution->environment->name ?? 'Unknown' }}</dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Framework</dt>
                                <dd class="text-sm text-zinc-900 dark:text-white col-span-2">
                                    {{ ucfirst(str_replace('-', ' ', $execution->testScript->framework_type ?? 'Unknown')) }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Timeline</h3>
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4">
                        <ol class="relative border-l border-zinc-300 dark:border-zinc-600 ml-3">
                            <li class="mb-6 ml-6">
                                <span
                                    class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -left-3 ring-8 ring-zinc-50 dark:ring-zinc-700/30 dark:bg-blue-900/30">
                                    <i data-lucide="file-plus" class="w-3 h-3 text-blue-600 dark:text-blue-400"></i>
                                </span>
                                <h3 class="flex items-center mb-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                    Execution Created
                                </h3>
                                <time class="block mb-2 text-xs font-normal text-zinc-500 dark:text-zinc-400">
                                    {{ $execution->created_at ? $execution->created_at->format('M d, Y h:i:s A') : 'N/A' }}
                                </time>
                            </li>

                            <li class="mb-6 ml-6">
                                <span
                                    class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -left-3 ring-8 ring-zinc-50 dark:ring-zinc-700/30 dark:bg-blue-900/30">
                                    <i data-lucide="play" class="w-3 h-3 text-blue-600 dark:text-blue-400"></i>
                                </span>
                                <h3 class="flex items-center mb-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                    Execution Started
                                </h3>
                                <time class="block mb-2 text-xs font-normal text-zinc-500 dark:text-zinc-400">
                                    {{ $execution->start_time ? $execution->start_time->format('M d, Y h:i:s A') : 'N/A' }}
                                </time>
                            </li>

                            @if ($execution->end_time)
                                <li class="ml-6">
                                    @php
                                        $iconClass = match ($execution->status->name ?? '') {
                                            'completed' => 'bg-green-100 dark:bg-green-900/30',
                                            'failed', 'aborted', 'timeout' => 'bg-red-100 dark:bg-red-900/30',
                                            default => 'bg-zinc-100 dark:bg-zinc-700/50',
                                        };

                                        $iconName = match ($execution->status->name ?? '') {
                                            'completed' => 'check',
                                            'failed' => 'x',
                                            'aborted' => 'square',
                                            'timeout' => 'clock',
                                            default => 'flag',
                                        };

                                        $iconColor = match ($execution->status->name ?? '') {
                                            'completed' => 'text-green-600 dark:text-green-400',
                                            'failed', 'aborted', 'timeout' => 'text-red-600 dark:text-red-400',
                                            default => 'text-zinc-600 dark:text-zinc-400',
                                        };
                                    @endphp
                                    <span
                                        class="absolute flex items-center justify-center w-6 h-6 {{ $iconClass }} rounded-full -left-3 ring-8 ring-zinc-50 dark:ring-zinc-700/30">
                                        <i data-lucide="{{ $iconName }}" class="w-3 h-3 {{ $iconColor }}"></i>
                                    </span>
                                    <h3 class="flex items-center mb-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                        Execution {{ ucfirst($execution->status->name ?? 'Completed') }}
                                    </h3>
                                    <time class="block mb-2 text-xs font-normal text-zinc-500 dark:text-zinc-400">
                                        {{ $execution->end_time->format('M d, Y h:i:s A') }}
                                    </time>
                                </li>
                            @endif
                        </ol>
                    </div>
                </div>
            </div>

            {{-- Containers Tab --}}
            <div x-show="activeTab === 'containers'" class="p-6">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Containers</h2>

                @if ($execution->containers->isEmpty())
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-6 text-center">
                        <i data-lucide="box" class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-500 mb-3"></i>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-white">No Containers Found</h3>
                        <p class="mt-2 text-zinc-500 dark:text-zinc-400">This execution hasn't created any containers yet.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-700/30">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        Container ID</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        Started</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        Duration</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($execution->containers as $container)
                                    @php
                                        $containerStatus = $container->status ?? 'unknown';
                                        $statusClass = match ($containerStatus) {
                                            'pending'
                                                => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                                            'running'
                                                => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 animate-pulse',
                                            'completed'
                                                => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                            'failed',
                                            'terminated'
                                                => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                            default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
                                        };

                                        $containerDuration = null;
                                        if ($container->start_time && $container->end_time) {
                                            $containerDuration = $container->start_time->diffInSeconds(
                                                $container->end_time,
                                            );
                                        }
                                    @endphp
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-white">
                                            {{ $container->container_id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                {{ ucfirst($containerStatus) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $container->start_time ? $container->start_time->format('M d, Y H:i:s') : 'Not started' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                            @if ($containerDuration !== null)
                                                {{ gmdate('H:i:s', $containerDuration) }}
                                            @elseif($container->start_time && !$container->end_time && $containerStatus === 'running')
                                                Running ({{ $container->start_time->diffForHumans(null, true) }})
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-3">Execution Logs</h3>

                <div class="overflow-x-auto">
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 p-4 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="font-medium text-zinc-700 dark:text-zinc-300">Test Output</h4>
                            <div>
                                <span
                                    class="px-2 py-1 text-xs rounded bg-indigo-100 dark:bg-indigo-900/20 text-indigo-800 dark:text-indigo-300">
                                    {{ $execution->status->name ?? 'Unknown' }}
                                </span>
                            </div>
                        </div>

                        <pre class="overflow-auto p-4 bg-zinc-800 text-zinc-100 rounded-lg h-96 text-sm font-mono">{{ $logs }}</pre>

                        <div class="mt-4">
                            <h4 class="font-medium text-zinc-700 dark:text-zinc-300 mb-2">Container Status</h4>
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead>
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                            Container ID</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                            DB Status</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                            Docker Status</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                            Runtime</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (is_array($containerStatus) || is_object($containerStatus))
                                        @foreach ($containerStatus as $id => $status)
                                            <tr>
                                                <td class="px-4 py-2 text-sm text-zinc-800 dark:text-zinc-200 font-mono">
                                                    {{ $id }}</td>
                                                <td class="px-4 py-2 text-sm text-zinc-800 dark:text-zinc-200">
                                                    {{ $status['db_status'] }}</td>
                                                <td class="px-4 py-2 text-sm text-zinc-800 dark:text-zinc-200">
                                                    {{ $status['docker_status'] }}</td>
                                                <td class="px-4 py-2 text-sm text-zinc-800 dark:text-zinc-200">
                                                    {{ $status['start_time'] ? $status['start_time']->diffForHumans() : 'N/A' }}
                                                    @if ($status['end_time'])
                                                        - {{ $status['end_time']->diffForHumans() }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="px-4 py-2 text-sm text-zinc-800 dark:text-zinc-200">
                                                No container status information available.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex space-x-4">
                    <button onclick="refreshPage()" class="btn-secondary">
                        <i data-lucide="refresh-cw" class="w-5 h-5 mr-1.5"></i>
                        Refresh Logs
                    </button>

                    @if ($execution->status->name === 'running' || $execution->status->name === 'pending')
                        @if (request()->has('refresh'))
                            <a href="{{ route('dashboard.executions.show', $execution->id) }}" class="btn-secondary">
                                <i data-lucide="pause" class="w-5 h-5 mr-1.5"></i>
                                Stop Auto-Refresh
                            </a>
                        @else
                            <a href="{{ route('dashboard.executions.show', ['execution' => $execution->id, 'refresh' => 1]) }}"
                                class="btn-secondary">
                                <i data-lucide="play" class="w-5 h-5 mr-1.5"></i>
                                Start Auto-Refresh
                            </a>
                        @endif
                    @endif

                    @if ($execution->status->name === 'running' || $execution->status->name === 'pending')
                        <form action="{{ route('dashboard.executions.abort', $execution->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-danger">
                                <i data-lucide="x-circle" class="w-5 h-5 mr-1.5"></i>
                                Abort Execution
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Logs Tab --}}
            <div x-show="activeTab === 'logs'" class="p-6">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Execution Logs</h2>

                @php
                    $logsAvailable = $execution->s3_results_key && Storage::exists($execution->s3_results_key);
                @endphp

                @if (!$logsAvailable && !$execution->isRunning())
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-6 text-center">
                        <i data-lucide="file-search" class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-500 mb-3"></i>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-white">No Logs Available</h3>
                        <p class="mt-2 text-zinc-500 dark:text-zinc-400">Logs for this execution are not available.</p>
                    </div>
                @elseif($execution->isRunning())
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-6 text-center">
                        <div
                            class="animate-spin mb-3 mx-auto w-12 h-12 border-4 border-indigo-500 dark:border-indigo-400 border-t-transparent dark:border-t-transparent rounded-full">
                        </div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Execution in Progress</h3>
                        <p class="mt-2 text-zinc-500 dark:text-zinc-400">Logs will be available when the execution
                            completes.</p>
                    </div>
                @else
                    <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg overflow-hidden">
                        <div class="flex items-center justify-between bg-zinc-100 dark:bg-zinc-700 px-4 py-2">
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Execution Logs</span>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('dashboard.executions.logs.download', $execution->id) }}"
                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm">
                                    <i data-lucide="download" class="w-4 h-4 inline-block"></i>
                                    Download
                                </a>
                            </div>
                        </div>
                        <div class="overflow-auto max-h-[600px] p-4 font-mono text-sm bg-zinc-950 text-zinc-300">
                            <pre>{{ Storage::get($execution->s3_results_key) }}</pre>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Script Tab --}}
            <div x-show="activeTab === 'script'" class="p-6">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Test Script</h2>

                @if ($execution->testScript)
                    <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg overflow-hidden">
                        <div class="flex items-center justify-between bg-zinc-100 dark:bg-zinc-700 px-4 py-2">
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                {{ $execution->testScript->name ?? 'Test Script' }}
                                ({{ ucfirst(str_replace('-', ' ', $execution->testScript->framework_type ?? 'Unknown')) }})
                            </span>
                        </div>
                        <div class="overflow-auto max-h-[600px] p-4 font-mono text-sm bg-zinc-950 text-zinc-300">
                            <pre>{{ $execution->testScript->script_content ?? 'Script content not available' }}</pre>
                        </div>
                    </div>
                @else
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-6 text-center">
                        <i data-lucide="file-code" class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-500 mb-3"></i>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Script Not Available</h3>
                        <p class="mt-2 text-zinc-500 dark:text-zinc-400">The test script content is not available.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function refreshPage() {
            window.location.href = "{{ route('dashboard.executions.show', $execution->id) }}";
        }

        // Auto-refresh only if the refresh parameter is set
        @if (request()->has('refresh') && ($execution->status->name === 'running' || $execution->status->name === 'pending'))
            setTimeout(function() {
                window.location.reload();
            }, 5000); // Refresh every 5 seconds
        @endif
    </script>
@endpush
