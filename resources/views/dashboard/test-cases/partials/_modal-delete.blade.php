{{-- resources/views/dashboard/test-cases/partials/_modal-delete.blade.php --}}
{{-- This uses the confirmDelete flag and deleteItemTitle/deleteItemId from the main Alpine component --}}
<div x-show="confirmDelete" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {{-- Background overlay --}}
        <div class="fixed inset-0 bg-zinc-900/60 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity" @click="closeDeleteModal" aria-hidden="true"></div>

        {{-- Modal panel --}}
        <div x-show="confirmDelete" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-zinc-200 dark:border-zinc-700">
            {{-- Modal Content --}}
             <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                        <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600 dark:text-red-400"></i>
                    </div>
                     <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                         <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-white" id="modal-title">
                             Confirm Deletion
                         </h3>
                         <div class="mt-2">
                             <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                 Are you sure you want to delete the test case "<strong class="font-semibold text-zinc-700 dark:text-zinc-200" x-text="deleteItemTitle"></strong>"? This action cannot be undone.
                             </p>
                         </div>
                    </div>
                </div>
            </div>
            {{-- Modal Footer --}}
            <div class="bg-zinc-50 dark:bg-zinc-700/30 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                <button @click="confirmDelete()" type="button"
                        class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-zinc-800 sm:w-auto sm:text-sm disabled:opacity-50"
                        :disabled="isDeleting">
                    <span x-show="!isDeleting">Delete Test Case</span>
                    <span x-show="isDeleting" class="flex items-center">
                         <i data-lucide="loader" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"></i>
                        Deleting...
                    </span>
                </button>
                <button @click="closeDeleteModal()" type="button"
                         class="mt-3 w-full inline-flex justify-center rounded-lg border border-zinc-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-800 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
