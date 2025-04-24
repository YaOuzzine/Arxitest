@php
    /**
     * @var \App\Models\User $user
     * @var \App\Models\Team $team
     * @var stdClass $stats // Contains projectCount, suiteCount, caseCount, executionHistory
     * @var \Illuminate\Support\Collection<\App\Models\TestExecution> $recentExecutions
     * @var \Illuminate\Support\Collection<\App\Models\Project> $recentProjects
     */

    // Format execution history for the chart
    $chartLabels = collect($stats->executionHistory ?? [])->keys()->map(fn($date) => \Carbon\Carbon::parse($date)->format('M d'))->toJson();
    $chartPassedData = collect($stats->executionHistory ?? [])->pluck('passed')->toJson();
    $chartFailedData = collect($stats->executionHistory ?? [])->pluck('failed')->toJson();

@endphp

@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Overview</span>
    </li>
@endsection

@section('content')
<div class="h-full space-y-8" x-data="dashboardData({
        executionChartLabels: {{ $chartLabels }},
        executionChartPassed: {{ $chartPassedData }},
        executionChartFailed: {{ $chartFailedData }}
    })" x-init="initCharts()">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="animate-fade-in-left">
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">Dashboard Overview</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Welcome back, {{ $user->name ?? 'User' }}! Overview for team: <span class="font-medium">{{ $team->name }}</span>.
            </p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3 animate-fade-in-right">
            <a href="{{ route('dashboard.projects.create') }}" class="btn-primary inline-flex items-center group">
                <i data-lucide="plus-circle" class="mr-2 -ml-1 w-5 h-5 transition-transform duration-200 group-hover:rotate-90"></i>
                New Project
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- Total Projects --}}
        <div class="stat-card animate-pop-in bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 p-5" style="--delay: 0.1s;">
            <div class="flex items-center justify-between mb-3">
                <h3 class="stat-card-title">Total Projects</h3>
                <div class="stat-card-icon bg-indigo-200/70 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 p-3 rounded-xl">
                    <i data-lucide="folder-kanban" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="stat-card-value text-4xl mb-4">{{ $stats->projectCount ?? 0 }}</p>
            <a href="{{ route('dashboard.projects') }}" class="stat-card-link group inline-flex items-center px-3 py-1.5 rounded-lg transition-colors duration-200 bg-indigo-100/50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300">
                View Projects <i data-lucide="arrow-right" class="ml-2 w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>

        {{-- Total Test Suites --}}
        <div class="stat-card animate-pop-in bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-5" style="--delay: 0.2s;">
            <div class="flex items-center justify-between mb-3">
                <h3 class="stat-card-title">Test Suites</h3>
                <div class="stat-card-icon bg-blue-200/70 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 p-3 rounded-xl">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="stat-card-value text-4xl mb-4">{{ $stats->suiteCount ?? 0 }}</p>
            <a href="{{ route('dashboard.test-suites.indexAll') }}" class="stat-card-link group inline-flex items-center px-3 py-1.5 rounded-lg transition-colors duration-200 bg-blue-100/50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/40 text-blue-600 dark:text-blue-300">
                View Suites <i data-lucide="arrow-right" class="ml-2 w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>

        {{-- Total Test Cases --}}
        <div class="stat-card animate-pop-in bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-5" style="--delay: 0.3s;">
            <div class="flex items-center justify-between mb-3">
                <h3 class="stat-card-title">Test Cases</h3>
                <div class="stat-card-icon bg-green-200/70 dark:bg-green-900/40 text-green-700 dark:text-green-300 p-3 rounded-xl">
                    <i data-lucide="check-square" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="stat-card-value text-4xl mb-4">{{ $stats->caseCount ?? 0 }}</p>
            <a href="{{ route('dashboard.test-cases.indexAll') }}" class="stat-card-link group inline-flex items-center px-3 py-1.5 rounded-lg transition-colors duration-200 bg-green-100/50 dark:bg-green-900/30 hover:bg-green-100 dark:hover:bg-green-900/40 text-green-600 dark:text-green-300">
                Explore Cases <i data-lucide="arrow-right" class="ml-2 w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>
    </div>

    {{-- Charts and Recent Executions --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Execution History Chart --}}
        <div class="xl:col-span-2 bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-zinc-200/50 dark:border-zinc-700/50 p-6 animate-fade-in" style="--delay: 0.4s;">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-zinc-800 dark:text-white">Executions (Last 7 Days)</h3>
                <div class="flex items-center space-x-3 text-xs">
                    <span class="inline-flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-green-500 mr-1.5"></span>Passed</span>
                    <span class="inline-flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-red-500 mr-1.5"></span>Failed</span>
                </div>
            </div>
            <div class="h-72 w-full relative">
                <canvas id="execution-history-chart"></canvas>
            </div>
        </div>

        {{-- Recent Executions List --}}
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-zinc-200/50 dark:border-zinc-700/50 p-6 animate-fade-in" style="--delay: 0.5s;">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-zinc-800 dark:text-white">Recent Executions</h3>
            </div>
            @if($recentExecutions->isEmpty())
                <div class="text-center py-10 text-zinc-500 dark:text-zinc-400">
                    <i data-lucide="clipboard-list" class="w-10 h-10 mx-auto mb-3 text-zinc-400 dark:text-zinc-500"></i>
                    No recent test executions found for this team.
                </div>
            @else
                <ul class="space-y-4 max-h-72 overflow-y-auto pr-2 custom-scrollbar">
                    @foreach($recentExecutions as $execution)
                        <li class="flex items-center space-x-3 group">
                            @php
                                $statusName = strtolower($execution->status?->name ?? 'unknown');
                                $statusColorClass = match ($statusName) {
                                    'completed', 'passed' => 'bg-green-500',
                                    'failed', 'aborted', 'error', 'timeout' => 'bg-red-500',
                                    'running', 'pending', 'queued' => 'bg-yellow-500 animate-pulse',
                                    default => 'bg-zinc-400',
                                };
                                $initiatorName = $execution->initiator?->name ?? 'System';
                                $scriptName = $execution->testScript?->name ?? 'Unknown Script';
                                $executionUrl = '#';
                            @endphp
                            <span class="flex-shrink-0 w-2 h-2 rounded-full {{ $statusColorClass }}"></span>
                            <div class="flex-1 min-w-0">
                                <a href="{{ $executionUrl }}" class="block hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-white truncate" title="{{ $scriptName }} - {{ $execution->testScript?->testCase?->testSuite?->project?->name ?? 'N/A' }}">
                                        {{ $scriptName }}
                                    </p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
                                        Ran {{ $execution->start_time ? $execution->start_time->diffForHumans() : $execution->created_at->diffForHumans() }} by {{ $initiatorName }}
                                    </p>
                                </a>
                            </div>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full flex-shrink-0
                                {{ match ($statusName) {
                                    'completed', 'passed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                    'failed', 'aborted', 'error', 'timeout' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                    'running', 'pending', 'queued' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                    default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
                                } }}">{{ ucfirst($statusName) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Recent Projects List --}}
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-zinc-200/50 dark:border-zinc-700/50 p-6 animate-fade-in" style="--delay: 0.6s;">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-zinc-800 dark:text-white">Recent Projects</h3>
            <a href="{{ route('dashboard.projects') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                View All Projects
            </a>
        </div>
        @if($recentProjects->isEmpty())
            <div class="text-center py-10 text-zinc-500 dark:text-zinc-400">
                <i data-lucide="folder-search" class="w-10 h-10 mx-auto mb-3 text-zinc-400 dark:text-zinc-500"></i>
                You haven't created any projects in this team yet.
                <a href="{{ route('dashboard.projects.create') }}" class="block mt-4 btn-secondary inline-flex items-center">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>Create Your First Project
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="pb-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Project</th>
                            <th class="pb-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Suites</th>
                            <th class="pb-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Cases</th>
                            <th class="pb-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Last Activity</th>
                            <th class="pb-3 text-right text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200/50 dark:divide-zinc-700/50">
                        @foreach($recentProjects as $project)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors duration-150">
                                <td class="py-3 pr-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-700 dark:to-zinc-600 flex items-center justify-center text-zinc-600 dark:text-zinc-300">
                                            <i data-lucide="folder-git-2" class="w-4 h-4"></i>
                                        </div>
                                        <div class="ml-3">
                                            <a href="{{ route('dashboard.projects.show', $project->id) }}" class="text-sm font-medium text-zinc-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400">
                                                {{ $project->name }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">{{ $project->test_suites_count }}</td>
                                <td class="py-3 px-3 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">{{ $project->test_cases_count }}</td>
                                <td class="py-3 px-3 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">{{ $project->updated_at->diffForHumans() }}</td>
                                <td class="py-3 pl-3 whitespace-nowrap text-right">
                                    <a href="{{ route('dashboard.projects.show', $project->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm font-medium">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection

@push('styles')
<style>
    .stat-card {
        @apply rounded-xl shadow-lg p-6 transition-all duration-300 hover:shadow-xl hover:-translate-y-1;
    }
    .stat-card-title {
        @apply text-sm font-medium text-zinc-600 dark:text-zinc-300 tracking-wide;
    }
    .stat-card-value {
        @apply font-bold text-zinc-900 dark:text-white;
    }

    .btn-primary { @apply bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition duration-150 ease-in-out; }
    .btn-secondary { @apply bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 font-medium py-2 px-4 rounded-lg shadow-sm transition duration-150 ease-in-out; }

    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(161, 161, 170, 0.3); border-radius: 3px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(113, 113, 122, 0.4); }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes fadeInLeft { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes fadeInRight { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes popIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }

    .animate-fade-in { animation: fadeIn 0.6s ease-out forwards; animation-delay: var(--delay, 0s); opacity: 0; }
    .animate-fade-in-left { animation: fadeInLeft 0.6s ease-out forwards; animation-delay: var(--delay, 0s); opacity: 0; }
    .animate-fade-in-right { animation: fadeInRight 0.6s ease-out forwards; animation-delay: var(--delay, 0s); opacity: 0; }
    .animate-pop-in { animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; animation-delay: var(--delay, 0s); opacity: 0; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboardData', (chartConfig) => ({
            executionChartInstance: null,
            executionChartLabels: chartConfig.executionChartLabels || [],
            executionChartPassed: chartConfig.executionChartPassed || [],
            executionChartFailed: chartConfig.executionChartFailed || [],

            initCharts() {
                this.$nextTick(() => {
                    this.renderExecutionHistoryChart();
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            },

            renderExecutionHistoryChart() {
                if (this.executionChartInstance) {
                    this.executionChartInstance.destroy();
                }
                const ctx = document.getElementById('execution-history-chart')?.getContext('2d');
                if (!ctx || !this.executionChartLabels.length) return;

                const isDarkMode = document.documentElement.classList.contains('dark');
                const gridColor = isDarkMode ? 'rgba(63, 63, 70, 0.3)' : 'rgba(229, 231, 235, 0.5)';
                const tickColor = isDarkMode ? '#a1a1aa' : '#71717a';

                this.executionChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.executionChartLabels,
                        datasets: [{
                            label: 'Passed',
                            data: this.executionChartPassed,
                            backgroundColor: 'rgba(34, 197, 94, 0.7)',
                            borderColor: 'rgba(22, 163, 74, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                            barThickness: 15,
                        },
                        {
                            label: 'Failed',
                            data: this.executionChartFailed,
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: 'rgba(220, 38, 38, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                            barThickness: 15,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                stacked: true,
                                grid: { display: false },
                                ticks: { color: tickColor }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: tickColor }
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: isDarkMode ? '#27272a' : '#ffffff',
                                titleColor: isDarkMode ? '#f4f4f5' : '#1f2937',
                                bodyColor: isDarkMode ? '#d4d4d8' : '#3f3f46',
                            }
                        },
                        animation: {
                            duration: 500,
                            easing: 'easeOutQuad'
                        }
                    }
                });
            },
        }));
    });
</script>
@endpush
