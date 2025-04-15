{{-- resources/views/dashboard/projects/partials/_modal-delete-project.blade.php --}}
<div x-show="showDeleteProjectModal" class="fixed inset-0 z-[99] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div x-show="showDeleteProjectModal" @click="closeDeleteModal('project')" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-zinc-900/50 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

        <!-- Modal panel -->
        <div x-show="showDeleteProjectModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-zinc-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                        <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600 dark:text-red-400"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-white" id="modal-title">
                            Delete Project
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Are you sure you want to delete the project "<strong class="font-semibold text-zinc-700 dark:text-zinc-200" x-text="projectName"></strong>"? This action cannot be undone and will permanently delete the project and all its test suites and cases.
                            </p>
                        </div>
                        <div class="mt-4">
                            <label for="confirm-delete-project-text" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Type "<span x-text="projectName" class="font-semibold"></span>" to confirm:</label>
                            <input type="text" id="confirm-delete-project-text" x-model="deleteProjectConfirmText" class="mt-1 block w-full border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm py-2 px-3 focus:ring-red-500 focus:border-red-500 sm:text-sm dark:bg-zinc-700 dark:text-white">
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-zinc-50 dark:bg-zinc-700/30 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button @click="confirmDeleteProject()" type="button" class="btn-danger w-full sm:ml-3 sm:w-auto" :disabled="isDeletingProject || deleteProjectConfirmText !== projectName">
                    <span x-show="!isDeletingProject">Delete Project</span>
                    <span x-show="isDeletingProject" class="flex items-center"><i data-lucide="loader" class="animate-spin w-4 h-4 mr-2"></i>Deleting...</span>
                </button>
                <button @click="closeDeleteModal('project')" type="button" class="btn-secondary mt-3 w-full sm:mt-0 sm:w-auto">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
