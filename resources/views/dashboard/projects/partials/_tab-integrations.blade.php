{{-- resources/views/dashboard/projects/partials/_tab-integrations.blade.php --}}
<div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-200">Integrations</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Connect project '{{ $project->name }}' with external services.
            </p>
        </div>
        <button class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Integration
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-if="integrations.length === 0">
            <div class="md:col-span-2 lg:col-span-3 text-center py-12 border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-700 mb-4">
                    <i data-lucide="link-2-off" class="w-8 h-8 text-zinc-400 dark:text-zinc-500"></i>
                </div>
                <h4 class="text-lg font-medium text-zinc-800 dark:text-zinc-200 mb-2">No Integrations Added</h4>
                <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-6">
                    Connect services like Jira, GitHub, or Slack to streamline your workflow.
                </p>
                <button class="btn-primary">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add First Integration
                </button>
            </div>
        </template>

         <template x-for="integration in integrations" :key="integration.id">
            <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/50 dark:border-zinc-700/30 p-5 flex flex-col justify-between transition-all duration-300 hover:shadow-md hover:border-zinc-300/50 dark:hover:border-zinc-600/50">
                <div>
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-white dark:bg-zinc-700 rounded-lg shadow-sm">
                                <i :data-lucide="getIntegrationIcon(integration.integration.type)" class="w-6 h-6 text-zinc-700 dark:text-zinc-300"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-zinc-800 dark:text-zinc-200" x-text="integration.integration.name"></h4>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 capitalize" x-text="integration.integration.type.replace('_', ' ')"></p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full"
                              :class="integration.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300'"
                              x-text="integration.is_active ? 'Active' : 'Inactive'">
                        </span>
                    </div>
                     <p class="text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2 mb-4" x-text="integration.integration.description || 'No description available.'">
                        {{-- Placeholder for potential description or config summary --}}
                        Base URL: <span x-text="integration.integration.base_url || 'N/A'"></span>
                     </p>
                </div>
                 <div class="mt-auto flex justify-end space-x-2">
                    <button class="text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white px-3 py-1 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">Configure</button>
                    <button class="text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 px-3 py-1 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">Remove</button>
                </div>
            </div>
        </template>

         {{-- "Add New" Card Placeholder --}}
         <button class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border-2 border-dashed border-zinc-300 dark:border-zinc-700 p-5 flex flex-col items-center justify-center text-center text-zinc-500 dark:text-zinc-400 hover:border-blue-500 hover:text-blue-600 dark:hover:border-blue-400 dark:hover:text-blue-400 transition-all duration-300 h-full min-h-[150px]">
             <i data-lucide="plus-circle" class="w-8 h-8 mb-2"></i>
             <span class="text-sm font-medium">Add Integration</span>
         </button>
    </div>
</div>
