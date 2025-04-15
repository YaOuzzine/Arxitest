{{-- resources/views/dashboard/projects/partials/_tab-overview.blade.php --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Test Suites Card -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 transition-all duration-300 hover:shadow-md hover:translate-y-[-2px]">
        <div class="flex items-center space-x-3 mb-4">
            <div class="p-2 bg-indigo-100/50 dark:bg-indigo-900/30 rounded-lg text-indigo-600 dark:text-indigo-400">
                <i data-lucide="layers" class="w-6 h-6"></i>
            </div>
            <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Test Suites</h3>
        </div>
        <div class="text-3xl font-bold text-zinc-900 dark:text-white" x-text="stats.totalTestSuites"></div>
        <a href="#" @click.prevent="setActiveTab('test-suites')" class="mt-1 text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View Suites</a>
    </div>

    <!-- Total Test Cases Card -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 transition-all duration-300 hover:shadow-md hover:translate-y-[-2px]">
        <div class="flex items-center space-x-3 mb-4">
            <div class="p-2 bg-blue-100/50 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                <i data-lucide="check-square" class="w-6 h-6"></i>
            </div>
            <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Test Cases</h3>
        </div>
        <div class="text-3xl font-bold text-zinc-900 dark:text-white" x-text="stats.totalTestCases"></div>
         {{-- <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400 flex items-center">
            <i data-lucide="arrow-up" class="w-4 h-4 mr-1 text-green-500"></i>
            <span>{{-- {{ $stats->testCasesGrowth }}% growth --}}</span>
        </p> --}}
    </div>

    <!-- Pass Rate Card -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 transition-all duration-300 hover:shadow-md hover:translate-y-[-2px]">
        <div class="flex items-center space-x-3 mb-4">
            <div class="p-2 bg-green-100/50 dark:bg-green-900/30 rounded-lg text-green-600 dark:text-green-400">
                <i data-lucide="percent" class="w-6 h-6"></i>
            </div>
            <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Pass Rate</h3>
        </div>
        <div class="text-3xl font-bold text-zinc-900 dark:text-white"><span x-text="stats.passRate"></span>%</div>
        <div class="mt-2 w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2.5">
            <div class="bg-gradient-to-r from-green-400 to-green-600 h-2.5 rounded-full transition-all duration-500" :style="`width: ${stats.passRate}%`"></div>
        </div>
         <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Based on last <span x-text="stats.totalExecutions"></span> executions (<span x-text="stats.passCount"></span> passed, <span x-text="stats.failCount"></span> failed)</p>
    </div>

    <!-- Avg Execution Time Card -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 transition-all duration-300 hover:shadow-md hover:translate-y-[-2px]">
        <div class="flex items-center space-x-3 mb-4">
            <div class="p-2 bg-orange-100/50 dark:bg-orange-900/30 rounded-lg text-orange-600 dark:text-orange-400">
                <i data-lucide="timer" class="w-6 h-6"></i>
            </div>
            <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Avg. Time</h3>
        </div>
        <div class="text-3xl font-bold text-zinc-900 dark:text-white" x-text="stats.avgExecutionTime"></div>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            per execution run
        </p>
    </div>
</div>

<!-- Test Execution History Chart & Recent Activity -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Test Execution History (Last 7 Days)</h3>
            {{-- Add filtering options if needed --}}
        </div>
        <div class="h-72">
             <canvas id="execution-history-chart"></canvas>
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200">Recent Activity</h3>
            <a href="#" @click.prevent="setActiveTab('executions')" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                View All
            </a>
        </div>
        <div class="space-y-5 max-h-72 overflow-y-auto pr-2">
            <template x-if="recentActivities.length === 0">
                 <p class="text-sm text-zinc-500 dark:text-zinc-400 text-center py-8">No recent activity found.</p>
            </template>
             <template x-for="activity in recentActivities" :key="activity.id || activity.description">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 mt-1">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br flex items-center justify-center text-white text-sm font-medium" :class="activity.type === 'execution' ? 'from-blue-500 to-indigo-600' : 'from-purple-500 to-pink-600'">
                             <i :data-lucide="activity.type === 'execution' ? 'play' : 'edit-3'" class="w-4 h-4"></i>
                        </div>
                        {{-- <img class="h-8 w-8 rounded-full" :src="activity.user?.avatar_url || 'https://ui-avatars.com/api/?name=?&background=random'" :alt="activity.user?.name || 'System'"> --}}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-zinc-600 dark:text-zinc-300" x-html="activity.description"></p>
                        <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                            <span x-text="timeAgo(activity.created_at)"></span>
                            <template x-if="activity.user">
                                <span x-text="' by ' + activity.user.name"></span>
                            </template>
                        </p>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
