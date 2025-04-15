{{-- resources/views/dashboard/projects/partials/_tab-settings.blade.php --}}
<div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
    <form @submit.prevent="saveSettings" class="space-y-8 max-w-3xl mx-auto">

        <div>
            <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-200 mb-1">Project Settings</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Configure general settings and preferences for '{{ $project->name }}'.
            </p>
        </div>

        <!-- General Settings -->
        <fieldset class="space-y-6">
             <legend class="text-lg font-medium text-zinc-900 dark:text-white pb-2 border-b border-zinc-200/50 dark:border-zinc-700/50 mb-4 w-full">General</legend>
            <div>
                <label for="setting-project-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Project Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="setting-project-name" name="name" required maxlength="100" value="{{ $project->name }}"
                    class="w-full px-4 py-2.5 rounded-lg border border-zinc-300/80 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-900/30 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/30 transition-all duration-200 shadow-sm">
                 {{-- Error display placeholder --}}
                 <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">The name of your project.</p>
            </div>

            <div>
                <label for="setting-project-description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Description
                </label>
                <textarea id="setting-project-description" name="description" rows="3" maxlength="255"
                    class="w-full px-4 py-2.5 rounded-lg border border-zinc-300/80 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-900/30 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/30 transition-all duration-200 shadow-sm resize-none">{{ $project->description }}</textarea>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">A brief description of the project.</p>
            </div>
        </fieldset>

        <!-- Testing Configuration -->
         <fieldset class="space-y-6 border-t border-zinc-200/50 dark:border-zinc-700/50 pt-6">
             <legend class="text-lg font-medium text-zinc-900 dark:text-white pb-2 border-b border-zinc-200/50 dark:border-zinc-700/50 mb-4 w-full">Testing Configuration</legend>

             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <div>
                     <label for="setting-default-framework" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                         Default Framework
                     </label>
                     <select id="setting-default-framework" name="settings[default_framework]"
                         class="w-full px-3 py-2.5 rounded-lg border border-zinc-300/80 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-900/30 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/30 text-sm appearance-none bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9ImN1cnJlbnRDb2xvciIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiPjxwb2x5bGluZSBwb2ludHM9IjYgOSAxMiAxNSAxOCA5Ij48L3BvbHlsaW5lPjwvc3ZnPg==')] bg-no-repeat bg-[right_0.75rem_center] bg-[length:1em]">
                         <option value="selenium-python" {{ ($project->settings['default_framework'] ?? '') === 'selenium-python' ? 'selected' : '' }}>Selenium (Python)</option>
                         <option value="cypress" {{ ($project->settings['default_framework'] ?? '') === 'cypress' ? 'selected' : '' }}>Cypress</option>
                         <option value="playwright" {{ ($project->settings['default_framework'] ?? '') === 'playwright' ? 'selected' : '' }}>Playwright</option>
                         <option value="rest-assured" {{ ($project->settings['default_framework'] ?? '') === 'rest-assured' ? 'selected' : '' }}>REST Assured</option>
                     </select>
                      <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Default framework for new test scripts.</p>
                 </div>
                  {{-- Add other settings like Container Timeout here if desired --}}
             </div>

            <div class="flex items-start">
                 <div class="flex items-center h-5">
                      <input id="setting-auto-generate-tests" name="settings[auto_generate_tests]" type="checkbox" {{ ($project->settings['auto_generate_tests'] ?? false) ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-zinc-300 dark:border-zinc-600 rounded bg-white/50 dark:bg-zinc-900/30 dark:checked:bg-blue-500">
                 </div>
                 <div class="ml-3 text-sm">
                      <label for="setting-auto-generate-tests" class="font-medium text-zinc-700 dark:text-zinc-300">Auto-Generate Tests</label>
                      <p class="text-zinc-500 dark:text-zinc-400 text-xs">Enable AI to automatically create tests from integrated sources (e.g., Jira stories).</p>
                 </div>
            </div>
        </fieldset>

        <!-- Save Button -->
        <div class="border-t border-zinc-200/50 dark:border-zinc-700/50 pt-6 flex justify-end">
             <button type="submit" class="btn-primary" :disabled="isSavingSettings">
                 <i data-lucide="loader" x-show="isSavingSettings" class="animate-spin w-4 h-4 mr-2"></i>
                 <i data-lucide="save" x-show="!isSavingSettings" class="w-4 h-4 mr-2"></i>
                 <span x-text="isSavingSettings ? 'Saving...' : 'Save Settings'"></span>
             </button>
        </div>

        <!-- Danger Zone -->
         <fieldset class="border-t border-red-300 dark:border-red-700 pt-6 mt-10">
             <legend class="text-lg font-medium text-red-600 dark:text-red-400 pb-2 border-b border-red-300 dark:border-red-700 mb-4 w-full">Danger Zone</legend>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 rounded-lg p-6">
                <h4 class="text-base font-semibold text-red-800 dark:text-red-300 mb-2">Delete this Project</h4>
                <p class="text-sm text-red-700/90 dark:text-red-400/90 mb-4">
                     Once deleted, all associated data including test suites, cases, and execution history will be permanently lost. This action cannot be undone.
                 </p>
                 <button type="button" @click="openDeleteModal('project')" class="btn-danger-outline">
                     <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
                    Delete Project
                 </button>
             </div>
         </fieldset>
    </form>
</div>
