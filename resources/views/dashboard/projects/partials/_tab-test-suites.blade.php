{{-- resources/views/dashboard/projects/partials/_tab-test-suites.blade.php --}}
<div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-200">Test Suites</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Manage test suites within the '{{ $project->name }}' project.
            </p>
        </div>
        <div class="flex items-center space-x-3">
            {{-- Add Search/Filter if needed --}}
            <a href="{{ route('dashboard.projects.test-suites.create', $project->id) }}" class="btn-primary">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i> New Test Suite
            </a>
        </div>
    </div>

    {{-- Test Suites List/Grid --}}
    <div class="space-y-4">
        <template x-if="testSuites.length === 0">
            <div class="text-center py-12 border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
                 <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-700 mb-4">
                     <i data-lucide="layers" class="w-8 h-8 text-zinc-400 dark:text-zinc-500"></i>
                 </div>
                 <h4 class="text-lg font-medium text-zinc-800 dark:text-zinc-200 mb-2">No Test Suites Yet</h4>
                 <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-6">
                     Create your first test suite to group related test cases.
                 </p>
                 <a href="{{ route('dashboard.projects.test-suites.create', $project->id) }}" class="btn-primary">
                     <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Create First Test Suite
                 </a>
            </div>
        </template>

        <template x-for="suite in testSuites" :key="suite.id">
             <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/50 dark:border-zinc-700/30 overflow-hidden transition-all duration-300 hover:shadow-md hover:border-zinc-300/50 dark:hover:border-zinc-600/50">
                <div class="p-4 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                         <div class="p-2 bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-lg shadow-sm">
                            <i data-lucide="layers" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                        <div>
                             <a :href="`/dashboard/projects/${projectId}/test-suites/${suite.id}`" class="text-base font-medium text-zinc-800 dark:text-zinc-200 hover:text-indigo-600 dark:hover:text-indigo-400" x-text="suite.name"></a>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                <span x-text="suite.test_cases_count"></span> {{ Str::plural('case', 1) }} • Updated <span x-text="timeAgo(suite.updated_at)"></span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button class="btn-secondary px-3 py-1 text-xs">
                            <i data-lucide="play" class="w-3 h-3 mr-1"></i> Run
                        </button>
                         <a :href="`/dashboard/projects/${projectId}/test-suites/${suite.id}/edit`" class="p-2 rounded-md text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </a>
                        <button @click="setSuiteToDelete({ id: suite.id, name: suite.name })" class="p-2 rounded-md text-zinc-500 dark:text-zinc-400 hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/20 dark:hover:text-red-400 transition-colors">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                 {{-- Optional: Expandable details or link to show page --}}
                 {{-- <div class="border-t border-zinc-200/50 dark:border-zinc-700/30 p-4 text-sm text-zinc-600 dark:text-zinc-400" x-text="suite.description || 'No description'"></div> --}}
             </div>
        </template>
    </div>
     {{-- Add Pagination if needed --}}
</div>
