@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Overview</span>
    </li>
@endsection

@section('content')
<div class="h-full" x-data="{
    activeTab: 'overview',
    chartData: [
        { date: 'Mon', passed: 12, failed: 3 },
        { date: 'Tue', passed: 15, failed: 2 },
        { date: 'Wed', passed: 10, failed: 5 },
        { date: 'Thu', passed: 18, failed: 1 },
        { date: 'Fri', passed: 14, failed: 4 },
        { date: 'Sat', passed: 8, failed: 2 },
        { date: 'Sun', passed: 5, failed: 1 }
    ],
    recentExecutions: [
        { id: 'EXE-1234', name: 'User Authentication Tests', status: 'completed', date: '10 min ago', environment: 'Production', result: 'passed', duration: '2m 34s' },
        { id: 'EXE-1233', name: 'Payment Processing Tests', status: 'failed', date: '1 hour ago', environment: 'Staging', result: 'failed', duration: '3m 12s' },
        { id: 'EXE-1232', name: 'Registration Flow Tests', status: 'completed', date: '3 hours ago', environment: 'Development', result: 'passed', duration: '1m 45s' },
        { id: 'EXE-1231', name: 'API Integration Tests', status: 'completed', date: 'Yesterday', environment: 'Production', result: 'passed', duration: '5m 02s' },
        { id: 'EXE-1230', name: 'Performance Tests', status: 'aborted', date: 'Yesterday', environment: 'Production', result: 'aborted', duration: '8m 17s' }
    ]
}">
    <div class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Dashboard</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Welcome back, {{ Auth::user()->name ?? 'User' }}! Here's what's happening with your tests.
                </p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <button class="btn-secondary inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                    <i data-lucide="refresh-cw" class="mr-2 -ml-1 w-4 h-4"></i>
                    Refresh
                </button>
                <button class="btn-primary inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                    <i data-lucide="plus" class="mr-2 -ml-1 w-4 h-4"></i>
                    New Project
                </button>
            </div>
        </div>

        <div class="mt-6 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex space-x-8">
                <button
                    @click="activeTab = 'overview'"
                    class="py-2 px-1 font-medium text-sm transition-colors duration-200 relative"
                    :class="activeTab === 'overview' ? 'text-zinc-800 dark:text-white' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'"
                >
                    Overview
                    <span
                        class="absolute bottom-0 inset-x-0 h-0.5 transition-transform duration-200 transform"
                        :class="activeTab === 'overview' ? 'bg-zinc-800 dark:bg-white scale-x-100' : 'scale-x-0'"
                    ></span>
                </button>
                <button
                    @click="activeTab = 'analytics'"
                    class="py-2 px-1 font-medium text-sm transition-colors duration-200 relative"
                    :class="activeTab === 'analytics' ? 'text-zinc-800 dark:text-white' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'"
                >
                    Analytics
                    <span
                        class="absolute bottom-0 inset-x-0 h-0.5 transition-transform duration-200 transform"
                        :class="activeTab === 'analytics' ? 'bg-zinc-800 dark:bg-white scale-x-100' : 'scale-x-0'"
                    ></span>
                </button>
                <button
                    @click="activeTab = 'reports'"
                    class="py-2 px-1 font-medium text-sm transition-colors duration-200 relative"
                    :class="activeTab === 'reports' ? 'text-zinc-800 dark:text-white' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'"
                >
                    Reports
                    <span
                        class="absolute bottom-0 inset-x-0 h-0.5 transition-transform duration-200 transform"
                        :class="activeTab === 'reports' ? 'bg-zinc-800 dark:bg-white scale-x-100' : 'scale-x-0'"
                    ></span>
                </button>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="card-hover bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Projects</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                        +12%
                    </span>
                </div>
                <div class="mt-2 flex items-baseline">
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        8
                    </p>
                    <p class="ml-2 text-sm text-zinc-500 dark:text-zinc-400">
                        from 7 last month
                    </p>
                </div>
                <div class="mt-4">
                    <a href="{{ route('dashboard.projects') }}?page=projects" class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 inline-flex items-center">
                        View all projects
                        <i data-lucide="arrow-right" class="ml-1 w-4 h-4"></i>
                    </a>
                </div>
            </div>

            <div class="card-hover bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Test Cases</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                        +24%
                    </span>
                </div>
                <div class="mt-2 flex items-baseline">
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        156
                    </p>
                    <p class="ml-2 text-sm text-zinc-500 dark:text-zinc-400">
                        from 126 last month
                    </p>
                </div>
                <div class="mt-4">
                    <a href="{{ route('dashboard') }}?page=test-cases" class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 inline-flex items-center">
                        View all test cases
                        <i data-lucide="arrow-right" class="ml-1 w-4 h-4"></i>
                    </a>
                </div>
            </div>

            <div class="card-hover bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pass Rate</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                        +5%
                    </span>
                </div>
                <div class="mt-2 flex items-baseline">
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        92%
                    </p>
                    <p class="ml-2 text-sm text-zinc-500 dark:text-zinc-400">
                        from 87% last month
                    </p>
                </div>
                <div class="mt-2">
                    <div class="w-full bg-zinc-200 dark:bg-zinc-600 rounded-full h-2.5">
                        <div class="bg-green-500 h-2.5 rounded-full" style="width: 92%"></div>
                    </div>
                </div>
            </div>

            <div class="card-hover bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Container Usage</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                        60%
                    </span>
                </div>
                <div class="mt-2 flex items-baseline">
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        3/5
                    </p>
                    <p class="ml-2 text-sm text-zinc-500 dark:text-zinc-400">
                        active containers
                    </p>
                </div>
                <div class="mt-2">
                    <div class="w-full bg-zinc-200 dark:bg-zinc-600 rounded-full h-2.5">
                        <div class="bg-yellow-500 h-2.5 rounded-full" style="width: 60%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-base font-medium text-zinc-900 dark:text-white">Test Executions (Last 7 days)</h3>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-full bg-green-500 mr-1"></span>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">Passed</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-full bg-red-500 mr-1"></span>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">Failed</span>
                        </div>
                    </div>
                </div>

                <div class="w-full h-64">
                    <canvas id="test-execution-chart-canvas"></canvas>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-base font-medium text-zinc-900 dark:text-white">Recent Executions</h3>
                    <a href="{{ route('dashboard') }}?page=executions" class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                        View all
                    </a>
                </div>

                <div class="overflow-hidden">
                    <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        <template x-for="execution in recentExecutions" :key="execution.id">
                            <li class="py-3 flex items-center justify-between hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors duration-150 -mx-6 px-6">
                                <div class="flex items-center min-w-0">
                                    <span
                                        class="flex-shrink-0 w-2 h-2 rounded-full mr-3"
                                        :class="{
                                            'bg-green-500': execution.result === 'passed',
                                            'bg-red-500': execution.result === 'failed',
                                            'bg-yellow-500': execution.result === 'pending',
                                            'bg-zinc-500': execution.result === 'aborted'
                                        }"
                                    ></span>
                                    <div class="truncate">
                                        <div class="flex items-center">
                                            <span class="font-medium text-zinc-900 dark:text-white truncate" x-text="execution.name"></span>
                                            <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-300" x-text="execution.id"></span>
                                        </div>
                                        <div class="mt-1 flex items-center text-xs text-zinc-500 dark:text-zinc-400 space-x-2">
                                            <span x-text="execution.environment"></span>
                                            <span>&bull;</span>
                                            <span x-text="execution.duration"></span>
                                            <span>&bull;</span>
                                            <span x-text="execution.date"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': execution.result === 'passed',
                                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': execution.result === 'failed',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': execution.result === 'pending',
                                            'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300': execution.result === 'aborted'
                                        }"
                                        x-text="execution.result.charAt(0).toUpperCase() + execution.result.slice(1)"
                                    ></span>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            <div class="lg:col-span-2 bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-base font-medium text-zinc-900 dark:text-white">Projects</h3>
                    <a href="{{ route('dashboard.projects') }}?page=projects" class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                        View all
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead>
                            <tr>
                                <th class="px-3 py-3.5 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Name
                                </th>
                                <th class="px-3 py-3.5 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Test Suites
                                </th>
                                <th class="px-3 py-3.5 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Last Run
                                </th>
                                <th class="px-3 py-3.5 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors duration-150">
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 flex items-center justify-center">
                                            <i data-lucide="layers" class="w-4 h-4"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                User Management
                                            </div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                12 test cases
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="text-sm text-zinc-900 dark:text-white">
                                        3
                                    </div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                        Today, 10:42 AM
                                    </div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Active
                                    </span>
                                </td>
                            </tr>

                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors duration-150">
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                            <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                Payment Processing
                                            </div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                8 test cases
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="text-sm text-zinc-900 dark:text-white">
                                        2
                                    </div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                        Yesterday, 3:15 PM
                                    </div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                        In Progress
                                    </span>
                                </td>
                            </tr>

                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors duration-150">
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 flex items-center justify-center">
                                            <i data-lucide="mail" class="w-4 h-4"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                Notifications
                                            </div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                5 test cases
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="text-sm text-zinc-900 dark:text-white">
                                        1
                                    </div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                        3 days ago
                                    </div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Active
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-base font-medium text-zinc-900 dark:text-white">Team Activity</h3>
                    <a href="{{ route('dashboard') }}?page=team" class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                        View all
                    </a>
                </div>

                <div class="space-y-5">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=Sarah+Johnson&background=random" alt="Sarah Johnson">
                        </div>
                        <div class="ml-3 min-w-0 flex-1">
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                Sarah Johnson
                            </p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Created a new test suite <span class="font-medium text-zinc-900 dark:text-white">API Authentication</span>
                            </p>
                            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                                2 hours ago
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=Michael+Chen&background=random" alt="Michael Chen">
                        </div>
                        <div class="ml-3 min-w-0 flex-1">
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                Michael Chen
                            </p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Run 12 tests in <span class="font-medium text-zinc-900 dark:text-white">Payment Processing</span> project
                            </p>
                            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                                Yesterday
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=Alex+Williams&background=random" alt="Alex Williams">
                        </div>
                        <div class="ml-3 min-w-0 flex-1">
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                Alex Williams
                            </p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Added 5 new test cases to <span class="font-medium text-zinc-900 dark:text-white">User Management</span>
                            </p>
                            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                                2 days ago
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=Emily+Rodriguez&background=random" alt="Emily Rodriguez">
                        </div>
                        <div class="ml-3 min-w-0 flex-1">
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                Emily Rodriguez
                            </p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Set up new <span class="font-medium text-zinc-900 dark:text-white">GitHub</span> integration
                            </p>
                            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                                3 days ago
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'analytics'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
            <h3 class="text-base font-medium text-zinc-900 dark:text-white mb-4">Test Results Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="p-4 rounded-lg bg-zinc-100 dark:bg-zinc-900/30 border border-zinc-200 dark:border-zinc-700">
                    <h4 class="font-semibold text-zinc-700 dark:text-zinc-300">Total Tests</h4>
                    <p class="text-xl font-bold text-zinc-900 dark:text-white">250</p>
                </div>
                <div class="p-4 rounded-lg bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-700">
                    <h4 class="font-semibold text-green-700 dark:text-green-300">Passed Tests</h4>
                    <p class="text-xl font-bold text-zinc-900 dark:text-white">230</p>
                </div>
                <div class="p-4 rounded-lg bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-700">
                    <h4 class="font-semibold text-red-700 dark:text-red-300">Failed Tests</h4>
                    <p class="text-xl font-bold text-zinc-900 dark:text-white">20</p>
                </div>
            </div>
            <div class="mt-6">
                <canvas id="overall-test-results-chart"></canvas>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'reports'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
            <h3 class="text-base font-medium text-zinc-900 dark:text-white mb-4">Test Reports</h3>
            <p class="text-zinc-600 dark:text-zinc-400">Here you can view and download your test reports.</p>
            <div class="mt-4">
                <button class="btn-primary inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                    <i data-lucide="download" class="mr-2 -ml-1 w-4 h-4"></i>
                    Generate New Report
                </button>
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead>
                            <tr>
                                <th class="px-3 py-3.5 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Report Name
                                </th>
                                <th class="px-3 py-3.5 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Generated At
                                </th>
                                <th class="px-3 py-3.5 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            <tr>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="text-sm text-zinc-900 dark:text-white">Weekly Test Report</div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">2025-04-09</div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <button class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </button>
                                        <button class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                                            <i data-lucide="download" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="text-sm text-zinc-900 dark:text-white">Monthly Performance Report</div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">2025-04-01</div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <button class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </button>
                                        <button class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                                            <i data-lucide="download" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboard', () => ({
            activeTab: 'overview',
            chartData: [
                { date: 'Mon', passed: 12, failed: 3 },
                { date: 'Tue', passed: 15, failed: 2 },
                { date: 'Wed', passed: 10, failed: 5 },
                { date: 'Thu', passed: 18, failed: 1 },
                { date: 'Fri', passed: 14, failed: 4 },
                { date: 'Sat', passed: 8, failed: 2 },
                { date: 'Sun', passed: 5, failed: 1 }
            ],
            recentExecutions: [
                { id: 'EXE-1234', name: 'User Authentication Tests', status: 'completed', date: '10 min ago', environment: 'Production', result: 'passed', duration: '2m 34s' },
                { id: 'EXE-1233', name: 'Payment Processing Tests', status: 'failed', date: '1 hour ago', environment: 'Staging', result: 'failed', duration: '3m 12s' },
                { id: 'EXE-1232', name: 'Registration Flow Tests', status: 'completed', date: '3 hours ago', environment: 'Development', result: 'passed', duration: '1m 45s' },
                { id: 'EXE-1231', name: 'API Integration Tests', status: 'completed', date: 'Yesterday', environment: 'Production', result: 'passed', duration: '5m 02s' },
                { id: 'EXE-1230', name: 'Performance Tests', status: 'aborted', date: 'Yesterday', environment: 'Production', result: 'aborted', duration: '8m 17s' }
            ],
            init() {
                this.renderTestExecutionChart();
                this.renderOverallTestResultsChart();
            },
            renderTestExecutionChart() {
                const chartElement = document.getElementById('test-execution-chart-canvas');
                if (!chartElement) return;
                const dates = this.chartData.map(item => item.date);
                const passedData = this.chartData.map(item => item.passed);
                const failedData = this.chartData.map(item => item.failed);

                new Chart(chartElement, {
                    type: 'bar',
                    data: {
                        labels: dates,
                        datasets: [
                            {
                                label: 'Passed',
                                data: passedData,
                                backgroundColor: 'rgba(56, 161, 105, 0.7)', // Green
                                borderColor: 'rgba(56, 161, 105, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Failed',
                                data: failedData,
                                backgroundColor: 'rgba(220, 38, 38, 0.7)', // Red
                                borderColor: 'rgba(220, 38, 38, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Number of Tests'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Day'
                                }
                            }
                        }
                    }
                });
            },
            renderOverallTestResultsChart() {
                const chartElement = document.getElementById('overall-test-results-chart');
                if (!chartElement) return;

                new Chart(chartElement, {
                    type: 'pie',
                    data: {
                        labels: ['Passed', 'Failed'],
                        datasets: [{
                            label: 'Test Results',
                            data: [230, 20], // Replace with dynamic data if available
                            backgroundColor: [
                                'rgba(56, 161, 105, 0.7)', // Green
                                'rgba(220, 38, 38, 0.7)',   // Red
                            ],
                            borderColor: [
                                'rgba(56, 161, 105, 1)',
                                'rgba(220, 38, 38, 1)',
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                    }
                });
            }
        }));
    });
</script>
@endpush
