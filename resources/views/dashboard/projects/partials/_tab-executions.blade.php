{{-- resources/views/dashboard/projects/partials/_tab-executions.blade.php --}}
<div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-200">Execution History</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                History of test runs for this project.
            </p>
        </div>
        <div class="flex items-center space-x-3">
            {{-- Filters for Environment, Status, Suite etc. --}}
             <div class="relative">
                 <select class="pl-4 pr-9 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-700 focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-400/50 appearance-none text-sm">
                     <option value="">All Statuses</option>
                     {{-- Populate statuses dynamically --}}
                     <option value="completed">Completed</option>
                     <option value="failed">Failed</option>
                     <option value="running">Running</option>
                     <option value="pending">Pending</option>
                 </select>
                 <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                     <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400 dark:text-zinc-500"></i>
                 </div>
             </div>
            <button class="btn-primary">
                <i data-lucide="play" class="w-4 h-4 mr-2"></i> Run All Project Tests
            </button>
        </div>
    </div>

    <div class="border border-zinc-200/50 dark:border-zinc-700/30 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full divide-y divide-zinc-200/50 dark:divide-zinc-700/30">
             <thead class="bg-zinc-50/50 dark:bg-zinc-800/30">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-500/90 dark:text-zinc-400/90 uppercase tracking-wider">ID / Script</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-500/90 dark:text-zinc-400/90 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-500/90 dark:text-zinc-400/90 uppercase tracking-wider">Environment</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-500/90 dark:text-zinc-400/90 uppercase tracking-wider">Initiator</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-500/90 dark:text-zinc-400/90 uppercase tracking-wider">Started</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-500/90 dark:text-zinc-400/90 uppercase tracking-wider">Duration</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-zinc-500/90 dark:text-zinc-400/90 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
             <tbody class="divide-y divide-zinc-200/50 dark:divide-zinc-700/30 bg-white dark:bg-zinc-900/20">
                <template x-if="executions.length === 0">
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-zinc-500 dark:text-zinc-400">
                            <div class="flex flex-col items-center">
                                <i data-lucide="clipboard-list" class="w-10 h-10 mb-3 text-zinc-400 dark:text-zinc-500"></i>
                                No executions recorded for this project yet.
                            </div>
                        </td>
                    </tr>
                </template>
                <template x-for="execution in executions" :key="execution.id">
                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-zinc-900/90 dark:text-white/90" x-text="'#' + execution.id.substring(0, 8)"></div>
                                <div class="text-xs text-zinc-500/80 dark:text-zinc-400/80" x-text="execution.test_script?.name || 'Unknown Script'"></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                             <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium capitalize" :class="getStatusColorClass(execution.status?.name)" x-text="execution.status?.name || 'Unknown'"></span>
                        </td>
                         <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400" x-text="execution.environment?.name || 'Default'"></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                 <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(execution.initiator?.name || '?')}&background=random`" alt="" class="h-8 w-8 rounded-full border border-white/50 dark:border-zinc-800/50 shadow-sm">
                                <span class="ml-3 text-sm text-zinc-800 dark:text-zinc-300" x-text="execution.initiator?.name || 'System'"></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400" x-text="formatDateTime(execution.start_time)"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400" x-text="formatDuration(execution.start_time ? (execution.end_time ? Math.round((new Date(execution.end_time) - new Date(execution.start_time)) / 1000) : null) : null)"></td>
                         <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="#" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                View Details
                            </a>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
     {{-- Add Pagination controls if needed --}}
</div>
