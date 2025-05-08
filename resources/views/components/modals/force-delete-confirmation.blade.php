@props([
    'id' => 'force-delete-modal',        // Modal ID
    'title' => 'Resource Has Dependencies', // Modal title
    'message' => 'This resource has associated items:', // Initial message
    'itemName' => null,                  // Name of item being deleted
    'dependencies' => [],                // Alpine variable for dependencies list
    'dependencyType' => 'items',         // Type of dependencies (e.g., "test cases")
    'dependencyAction' => 'detached',    // What happens to dependencies (e.g., "detached", "deleted")
    'confirmText' => 'Yes, Delete Anyway', // Confirm button text
    'cancelText' => 'Cancel',            // Cancel button text
])

<div
    x-show="showForceDeleteModal"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 overflow-y-auto backdrop-blur-[2px]"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
    style="display: none;"
    id="{{ $id }}"
>
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <!-- Background overlay -->
        <div
            class="fixed inset-0 bg-zinc-900/60 dark:bg-zinc-900/80 transition-opacity"
            @click="closeForceDeleteModal"
            aria-hidden="true"
        ></div>

        <!-- Modal panel -->
        <div
            x-show="showForceDeleteModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-zinc-800 text-left shadow-xl transition-all sm:my-8 w-full max-w-lg border border-zinc-200/80 dark:border-zinc-700/60"
        >
            <!-- Modal content -->
            <div class="p-6 sm:p-8">
                <div class="flex items-start gap-4">
                    <!-- Icon container -->
                    <div class="flex-shrink-0 p-3 rounded-full bg-amber-100/80 dark:bg-amber-900/30">
                        <i data-lucide="alert-circle" class="h-6 w-6 text-amber-600 dark:text-amber-400"></i>
                    </div>

                    <!-- Text content -->
                    <div class="space-y-4">
                        <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ $title }}
                        </h3>

                        <div class="space-y-3">
                            <p class="text-zinc-600/90 dark:text-zinc-300/90 leading-relaxed">
                                {{ $message }}

                                @if($itemName)
                                    <span class="block mt-2 text-lg font-bold bg-gradient-to-r from-amber-600 to-amber-500 bg-clip-text text-transparent">
                                        "<span x-text="{{ $itemName }}"></span>"
                                    </span>
                                @endif
                            </p>

                            <div class="mt-3 max-h-60 overflow-y-auto border border-zinc-200 dark:border-zinc-700 rounded-md">
                                <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    <template x-for="item in {{ $dependencies }}" :key="item.id">
                                        <li class="px-4 py-3 flex items-center text-sm">
                                            <i data-lucide="file-check-2" class="h-4 w-4 text-zinc-400 dark:text-zinc-500 mr-2"></i>
                                            <span class="text-zinc-700 dark:text-zinc-300" x-text="item.title"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>

                            <p class="mt-3 text-sm text-amber-600 dark:text-amber-400 font-medium">
                                These {{ $dependencyType }} will be {{ $dependencyAction }} but not deleted.
                                Are you sure you want to continue?
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal footer -->
            <div class="p-6 sm:px-8 sm:pb-8 border-t border-zinc-100/50 dark:border-zinc-700/50">
                <div class="flex gap-3 justify-end">
                    <button
                        @click="closeForceDeleteModal()"
                        type="button"
                        class="px-5 py-2.5 rounded-xl font-medium text-zinc-700/90 dark:text-zinc-300/90 hover:bg-zinc-100/60 dark:hover:bg-zinc-700/50 transition-colors duration-150"
                    >
                        {{ $cancelText }}
                    </button>
                    <button
                        @click="confirmDelete(true)"
                        type="button"
                        class="px-5 py-2.5 rounded-xl font-semibold text-white bg-gradient-to-br from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 shadow-sm shadow-amber-200/50 dark:shadow-amber-900/30 transition-all duration-200 disabled:opacity-50 disabled:pointer-events-none"
                        :disabled="isDeleting"
                    >
                        <span x-show="!isDeleting" class="flex items-center gap-2">
                            <i data-lucide="trash-2" class="w-4 h-4 text-white"></i>
                            {{ $confirmText }}
                        </span>
                        <span x-show="isDeleting" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
