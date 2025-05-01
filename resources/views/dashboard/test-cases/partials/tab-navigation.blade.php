<!-- resources/views/dashboard/test-cases/partials/tab-navigation.blade.php -->
<div class="border-b border-zinc-200 dark:border-zinc-700">
    <nav class="flex overflow-x-auto" aria-label="Tabs">
        <button @click="setActiveTab('details')"
            :class="{
                'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400': activeTab === 'details',
                'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600': activeTab !== 'details'
            }"
            class="px-4 py-4 font-medium text-sm border-b-2 whitespace-nowrap">
            <i data-lucide="clipboard-list" class="inline-block w-4 h-4 mr-1"></i>
            Test Case Details
        </button>
        <button @click="setActiveTab('scripts')"
            :class="{
                'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400': activeTab === 'scripts',
                'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600': activeTab !== 'scripts'
            }"
            class="px-4 py-4 font-medium text-sm border-b-2 whitespace-nowrap">
            <i data-lucide="file-code" class="inline-block w-4 h-4 mr-1"></i>
            Test Scripts <span
                class="ml-1 px-2 py-0.5 rounded-full text-xs bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300">{{ $testScripts->count() }}</span>
        </button>
        <button @click="setActiveTab('testdata')"
            :class="{
                'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400': activeTab === 'testdata',
                'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600': activeTab !== 'testdata'
            }"
            class="px-4 py-4 font-medium text-sm border-b-2 whitespace-nowrap">
            <i data-lucide="database" class="inline-block w-4 h-4 mr-1"></i>
            Test Data <span
                class="ml-1 px-2 py-0.5 rounded-full text-xs bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300">{{ $testData->count() }}</span>
        </button>
    </nav>
</div>
