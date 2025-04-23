@extends('layouts.dashboard')

@section('title', 'Test Executions')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Executions</span>
    </li>
@endsection

@section('content')
<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Test Executions</h1>
        <a href="{{ route('dashboard.executions.create') }}" class="btn-primary">
            <i data-lucide="play" class="w-5 h-5 mr-1.5"></i>
            Run New Test
        </a>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        @if($executions->isEmpty())
            <div class="p-8 text-center">
                <i data-lucide="file-question" class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-500 mb-3"></i>
                <h3 class="text-lg font-medium text-zinc-900 dark:text-white">No Test Executions Found</h3>
                <p class="mt-2 text-zinc-500 dark:text-zinc-400">Start by running a test script.</p>
                <a href="{{ route('dashboard.executions.create') }}" class="btn-primary mt-4 inline-flex">
                    <i data-lucide="play" class="w-5 h-5 mr-1.5"></i>
                    Run First Test
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-700/30">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Script</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Environment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Started</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($executions as $execution)
                            @php
                                $statusClass = match($execution->status->name ?? 'unknown') {
                                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                                    'running' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 animate-pulse',
                                    'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                    'failed' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                    'aborted' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
                                    'timeout' => 'bg-pink-100 text-pink-800 dark:bg-pink-900/40 dark:text-pink-300',
                                    default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
                                };

                                $duration = null;
                                if ($execution->start_time && $execution->end_time) {
                                    $duration = $execution->start_time->diffInSeconds($execution->end_time);
                                }
                            @endphp
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $execution->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $execution->testScript->name ?? 'Unknown' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $execution->environment->name ?? 'Unknown' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                        {{ ucfirst($execution->status->name ?? 'Unknown') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $execution->start_time ? $execution->start_time->diffForHumans() : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    @if($duration !== null)
                                        {{ gmdate('H:i:s', $duration) }}
                                    @else
                                        {{ $execution->isRunning() ? 'Running...' : 'N/A' }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('dashboard.executions.show', $execution->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
                {{ $executions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
