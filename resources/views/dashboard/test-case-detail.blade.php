@extends('layouts.dashboard')

@section('title', 'Test Case Details')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard', ['page' => 'projects']) }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
            Projects
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard', ['project' => 'user-management']) }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
            User Management
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Login Authentication (TC-0012)</span>
    </li>
@endsection

@section('content')
<div class="h-full" x-data="{
    activeTab: 'details',
    testCase: {
        id: 'TC-0012',
        title: 'Login Authentication',
        status: 'passed',
        lastRun: '2 hours ago',
        description: 'Verify that users can log in with valid credentials and are redirected to the dashboard.',
        steps: [
            { id: 1, description: 'Navigate to login page', expected: 'Login form is displayed' },
            { id: 2, description: 'Enter valid username', expected: 'Username is accepted' },
            { id: 3, description: 'Enter valid password', expected: 'Password is masked and accepted' },
            { id: 4, description: 'Click Login button', expected: 'User is authenticated and redirected to dashboard' }
        ],
        tags: ['authentication', 'critical', 'smoke-test'],
        author: 'Sarah Johnson',
        created: '2025-03-15',
        modified: '2025-04-08',
        suite: 'User Authentication',
        history: [
            { date: '2025-04-10', result: 'passed', duration: '1.2s', environment: 'Production' },
            { date: '2025-04-08', result: 'passed', duration: '1.3s', environment: 'Staging' },
            { date: '2025-04-05', result: 'failed', duration: '1.5s', environment: 'Development', error: 'Timeout waiting for dashboard redirect', consoleOutput: `[2025-04-05T10:15:32Z] Starting test execution...
[2025-04-05T10:15:32Z] Navigating to http://example.com/login
[2025-04-05T10:15:33Z] Page loaded successfully
[2025-04-05T10:15:33Z] Entering username: testuser@example.com
[2025-04-05T10:15:33Z] Entering password: ********
[2025-04-05T10:15:34Z] Clicking login button
[2025-04-05T10:15:34Z] Waiting for navigation...
[2025-04-05T10:15:39Z] ERROR: Timed out after waiting 5 seconds for navigation to complete.
[2025-04-05T10:15:39Z] Test failed: Timeout waiting for dashboard redirect` },
            { date: '2025-04-01', result: 'passed', duration: '1.1s', environment: 'Development' }
        ]
    },
    get latestFailedTest() {
        return this.testCase.history.find(entry => entry.result === 'failed');
    },
    get successRate() {
        const passedCount = this.testCase.history.filter(entry => entry.result === 'passed').length;
        return this.testCase.history.length > 0 ? `${Math.round((passedCount / this.testCase.history.length) * 100)}% (${passedCount}/${this.testCase.history.length})` : 'N/A';
    },
    get averageDuration() {
        const totalDuration = this.testCase.history.reduce((sum, entry) => sum + parseFloat(entry.duration), 0);
        return this.testCase.history.length > 0 ? `${(totalDuration / this.testCase.history.length).toFixed(1)}s` : 'N/A';
    },
    get fastestRun() {
        const durations = this.testCase.history.map(entry => parseFloat(entry.duration));
        return this.testCase.history.length > 0 ? `${Math.min(...durations)}s` : 'N/A';
    },
    get slowestRun() {
        const durations = this.testCase.history.map(entry => parseFloat(entry.duration));
        return this.testCase.history.length > 0 ? `${Math.max(...durations)}s` : 'N/A';
    },
    get performanceChartData() {
        return {
            labels: this.testCase.history.map(entry => entry.date).reverse(),
            series: [{
                name: 'Duration (s)',
                data: this.testCase.history.map(entry => parseFloat(entry.duration)).reverse()
            }]
        };
    }
}">
    <div class="mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0 h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
                <div class="ml-4">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" x-text="testCase.title"></h1>
                        <span
                            class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                            :class="{
                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': testCase.status === 'passed',
                                'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': testCase.status === 'failed',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': testCase.status === 'pending',
                                'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300': testCase.status === 'skipped'
                            }"
                            x-text="testCase.status.charAt(0).toUpperCase() + testCase.status.slice(1)"
                        ></span>
                    </div>
                    <div class="mt-1 flex items-center text-sm text-zinc-500 dark:text-zinc-400">
                        <span x-text="testCase.id"></span>
                        <span class="mx-2">&bull;</span>
                        <span>Last run <span x-text="testCase.lastRun"></span></span>
                    </div>
                </div>
            </div>
            <div class="mt-4 lg:mt-0 flex space-x-3">
                <button class="btn-secondary inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                    <i data-lucide="edit" class="mr-2 -ml-1 w-4 h-4"></i>
                    Edit
                </button>
                <button class="btn-primary inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                    <i data-lucide="play" class="mr-2 -ml-1 w-4 h-4"></i>
                    Run Test
                </button>
            </div>
        </div>

        <div class="mt-6 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex space-x-8">
                <button
                    @click="activeTab = 'details'"
                    class="py-2 px-1 font-medium text-sm transition-colors duration-200 relative"
                    :class="activeTab === 'details' ? 'text-zinc-800 dark:text-white' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'"
                >
                    Details
                    <span
                        class="absolute bottom-0 inset-x-0 h-0.5 transition-transform duration-200 transform"
                        :class="activeTab === 'details' ? 'bg-zinc-800 dark:bg-white scale-x-100' : 'scale-x-0'"
                    ></span>
                </button>
                <button
                    @click="activeTab = 'history'"
                    class="py-2 px-1 font-medium text-sm transition-colors duration-200 relative"
                    :class="activeTab === 'history' ? 'text-zinc-800 dark:text-white' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'"
                >
                    Execution History
                    <span
                        class="absolute bottom-0 inset-x-0 h-0.5 transition-transform duration-200 transform"
                        :class="activeTab === 'history' ? 'bg-zinc-800 dark:bg-white scale-x-100' : 'scale-x-0'"
                    ></span>
                </button>
                <button
                    @click="activeTab = 'code'"
                    class="py-2 px-1 font-medium text-sm transition-colors duration-200 relative"
                    :class="activeTab === 'code' ? 'text-zinc-800 dark:text-white' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'"
                >
                    Test Script
                    <span
                        class="absolute bottom-0 inset-x-0 h-0.5 transition-transform duration-200 transform"
                        :class="activeTab === 'code' ? 'bg-zinc-800 dark:bg-white scale-x-100' : 'scale-x-0'"
                    ></span>
                </button>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'details'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <h3 class="text-base font-medium text-zinc-900 dark:text-white mb-4">Description</h3>
                    <p class="text-zinc-600 dark:text-zinc-400" x-text="testCase.description"></p>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <h3 class="text-base font-medium text-zinc-900 dark:text-white mb-4">Test Steps</h3>
                    <div class="overflow-hidden border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-700/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider w-12">
                                        #
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        Action
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        Expected Result
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                <template x-for="step in testCase.steps" :key="step.id">
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-medium text-zinc-900 dark:text-white" x-text="step.id"></td>
                                        <td class="px-6 py-4 text-sm text-zinc-600 dark:text-zinc-400" x-text="step.description"></td>
                                        <td class="px-6 py-4 text-sm text-zinc-600 dark:text-zinc-400" x-text="step.expected"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <h3 class="text-base font-medium text-zinc-900 dark:text-white mb-4">Test Information</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">Test Suite:</span>
                            <span class="text-sm font-medium text-zinc-900 dark:text-white" x-text="testCase.suite"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">Created By:</span>
                            <span class="text-sm font-medium text-zinc-900 dark:text-white" x-text="testCase.author"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">Created Date:</span>
                            <span class="text-sm font-medium text-zinc-900 dark:text-white" x-text="testCase.created"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">Last Modified:</span>
                            <span class="text-sm font-medium text-zinc-900 dark:text-white" x-text="testCase.modified"></span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <h3 class="text-base font-medium text-zinc-900 dark:text-white mb-4">Tags</h3>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="(tag, index) in testCase.tags" :key="index">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-300" x-text="tag"></span>
                        </template>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                    <h3 class="text-base font-medium text-zinc-900 dark:text-white mb-4">Related Items</h3>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i data-lucide="file-text" class="w-5 h-5 text-blue-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                    <a href="#" class="hover:underline">USER-123: Implement login functionality</a>
                                </p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Jira Story</p>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i data-lucide="git-pull-request" class="w-5 h-5 text-purple-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                    <a href="#" class="hover:underline">PR #42: Fix login redirect issue</a>
                                </p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">GitHub Pull Request</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'history'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-base font-medium text-zinc-900 dark:text-white">Execution History</h3>
                    <div class="flex items-center space-x-2">
                        <select class="text-sm rounded-md border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 shadow-sm focus:border-zinc-500 focus:ring-zinc-500">
                            <option>All Environments</option>
                            <option>Production</option>
                            <option>Staging</option>
                            <option>Development</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-700/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        Environment
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        Duration
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                        Result
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                <template x-for="(entry, index) in testCase.history" :key="index">
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400" x-text="entry.date"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400" x-text="entry.environment"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400" x-text="entry.duration"></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="{
                                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': entry.result === 'passed',
                                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': entry.result === 'failed',
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': entry.result === 'pending',
                                                    'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300': entry.result === 'skipped'
                                                }"
                                                x-text="entry.result.charAt(0).toUpperCase() + entry.result.slice(1)"
                                            ></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                <h3 class="text-base font-medium text-zinc-900 dark:text-white mb-6">Performance Trend</h3>

                <div class="w-full h-64" id="performance-trend-chart">
                    </div>

                <div class="mt-6">
                    <h4 class="text-sm font-medium text-zinc-900 dark:text-white mb-3">Statistics</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">Average Duration:</span>
                            <span class="text-sm font-medium text-zinc-900 dark:text-white" x-text="averageDuration"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">Fastest Run:</span>
                            <span class="text-sm font-medium text-zinc-900 dark:text-white" x-text="fastestRun"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">Slowest Run:</span>
                            <span class="text-sm font-medium text-zinc-900 dark:text-white" x-text="slowestRun"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">Success Rate:</span>
                            <span class="text-sm font-medium text-zinc-900 dark:text-white" x-text="successRate"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <template x-if="latestFailedTest">
            <div class="mt-6 bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                <h3 class="text-base font-medium text-zinc-900 dark:text-white mb-4">Failure Details (<span x-text="latestFailedTest.date"></span>)</h3>

                <div class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-900/20 rounded-lg p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i data-lucide="alert-circle" class="h-5 w-5 text-red-400 dark:text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-400">Error: <span x-text="latestFailedTest.error"></span></h3>
                            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                <p x-text="latestFailedTest.error"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                    <div class="bg-zinc-50 dark:bg-zinc-700/50 px-4 py-2 text-xs font-medium text-zinc-800 dark:text-zinc-300">
                        Console Output
                    </div>
                    <pre class="p-4 text-xs text-zinc-600 dark:text-zinc-400 overflow-x-auto" x-text="latestFailedTest.consoleOutput"></pre>
                </div>

                <div class="mt-4 flex justify-end">
                    <button class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 inline-flex items-center">
                        <i data-lucide="download" class="w-4 h-4 mr-1"></i>
                        Download Full Logs
                    </button>
                </div>
            </div>
        </template>
    </div>

    <div x-show="activeTab === 'code'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
                <div class="flex items-center">
                    <h3 class="text-base font-medium text-zinc-900 dark:text-white">
                        Selenium Python Test Script
                    </h3>
                    <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                        AI Generated
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    <button class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 inline-flex items-center">
                        <i data-lucide="copy" class="w-4 h-4 mr-1"></i>
                        Copy
                    </button>
                    <button class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 inline-flex items-center">
                        <i data-lucide="download" class="w-4 h-4 mr-1"></i>
                        Download
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <pre class="p-6 text-sm text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-900/50">
import time
import unittest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

class LoginAuthenticationTest(unittest.TestCase):
    \"\"\"TC-0012: Verify that users can log in with valid credentials and are redirected to the dashboard.\"\"\"

    def setUp(self):
        \"\"\"Set up the test environment.\"\"\"
        self.driver = webdriver.Chrome()
        self.driver.maximize_window()
        self.wait = WebDriverWait(self.driver, 10)

    def tearDown(self):
        \"\"\"Clean up after the test.\"\"\"
        self.driver.quit()

    def test_login_with_valid_credentials(self):
        \"\"\"Test the login process with valid credentials.\"\"\"
        # Step 1: Navigate to login page
        self.driver.get("http://example.com/login")

        # Verify login form is displayed
        login_form = self.wait.until(
            EC.visibility_of_element_located((By.ID, "login-form"))
        )
        self.assertTrue(login_form.is_displayed(), "Login form should be visible")

        # Step 2: Enter valid username
        username_field = self.driver.find_element(By.ID, "username")
        username_field.send_keys("testuser@example.com")

        # Step 3: Enter valid password
        password_field = self.driver.find_element(By.ID, "password")
        password_field.send_keys("Password123!")

        # Step 4: Click Login button
        login_button = self.driver.find_element(By.ID, "login-button")
        login_button.click()

        # Verify user is redirected to dashboard
        self.wait.until(
            EC.url_contains("/dashboard")
        )

        # Additional verification: Check for dashboard elements
        dashboard_header = self.wait.until(
            EC.visibility_of_element_located((By.CSS_SELECTOR, ".dashboard-header"))
        )
        self.assertTrue(dashboard_header.is_displayed(), "Dashboard header should be visible")

        # Verify user info is displayed correctly
        user_info = self.driver.find_element(By.CSS_SELECTOR, ".user-info")
        self.assertIn("testuser", user_info.text.lower(), "Username should be displayed in user info")

if __name__ == "__main__":
    unittest.main()
</pre>
            </div>
        </div>

        <div class="mt-6 bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
            <h3 class="text-base font-medium text-zinc-900 dark:text-white mb-4">Script Version History</h3>

            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-700/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Version
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Last Modified
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Modified By
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Change
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-white">
                                v1.2
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                2025-04-08 09:15 AM
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                Sarah Johnson
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                                Added additional dashboard verification steps
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-3">
                                    <button class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>
                                    <button class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                                        <i data-lucide="git-branch" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-white">
                                v1.1
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                2025-03-15 10:00 AM
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                Sarah Johnson
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                                Initial script creation
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-3">
                                    <button class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>
                                    <button class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                                        <i data-lucide="git-branch" class="w-4 h-4"></i>
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
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('performanceChart', () => ({
            chart: null,
            init() {
                const chartData = this.$data.performanceChartData;
                const chartElement = document.getElementById('performance-trend-chart');

                if (chartElement) {
                    this.chart = new ApexCharts(chartElement, {
                        chart: {
                            type: 'line',
                            height: 350,
                            toolbar: {
                                show: false
                            }
                        },
                        series: chartData.series,
                        xaxis: {
                            categories: chartData.labels
                        },
                        yaxis: {
                            title: {
                                text: 'Duration (s)'
                            }
                        },
                        colors: ['#6366F1'], // Tailwind indigo-500
                        stroke: {
                            curve: 'smooth'
                        },
                        markers: {
                            size: 4
                        },
                        grid: {
                            borderColor: '#e5e7eb', // Tailwind gray-200
                            strokeDashArray: 5
                        },
                        tooltip: {
                            x: {
                                format: 'yyyy-MM-dd'
                            }
                        },
                        dataLabels: {
                            enabled: false
                        }
                    });
                    this.chart.render();
                }
            },
            updateChart() {
                if (this.chart) {
                    const chartData = this.$data.performanceChartData;
                    this.chart.updateSeries(chartData.series);
                    this.chart.updateOptions({ xaxis: { categories: chartData.labels } });
                }
            }
        }));
        Alpine.data('testCaseDetails', () => ({
            ...Alpine.store('testCaseData'), // If you want to manage testCase data in a store
            activeTab: 'details',
            init() {
                // Initialize any component-specific logic here
            }
        }));
    });
</script>
@endsection
