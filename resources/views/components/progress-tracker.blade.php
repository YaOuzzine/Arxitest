<div id="progress-tracker" class="fixed bottom-6 left-6 z-50 bg-white dark:bg-zinc-800 shadow-lg rounded-lg p-3 max-w-md border border-zinc-200 dark:border-zinc-700 hidden">
    <div class="flex items-center justify-between w-full mb-2">
        <h3 class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
            Processing Jobs
        </h3>
        <div class="flex items-center space-x-2">
            <button id="minimize-progress" class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path>
                </svg>
            </button>
            <button id="close-progress" class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
    <div id="progress-jobs" class="space-y-3">
        <!-- Jobs will be inserted here dynamically -->
    </div>
</div>

<!-- Floating restore button (hidden by default) -->
<button id="restore-progress" class="fixed bottom-4 left-4 z-50 bg-indigo-600 text-white p-2 rounded-full shadow-lg hover:bg-indigo-700 hidden">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <polyline points="12 6 12 12 16 14"></polyline>
    </svg>
</button>
