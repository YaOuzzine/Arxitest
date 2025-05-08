@props([
    'id' => 'delete-modal',             // Modal ID for targeting
    'title' => 'Delete Confirmation',   // Modal title
    'message' => 'Are you sure you want to delete this item?', // Default confirmation message
    'itemName' => null,                 // Name of item being deleted (optional)
    'dangerText' => 'This action cannot be undone.',  // Warning text
    'confirmText' => 'Delete',          // Confirm button text
    'cancelText' => 'Cancel',           // Cancel button text
    'requireConfirmation' => true,      // Whether to show the checkbox confirmation
    'confirmationLabel' => 'I understand that this action is irreversible', // Checkbox label
])

<div
    x-show="showDeleteModal"
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
            @click="closeDeleteModal"
            aria-hidden="true"
        ></div>

        <!-- Modal panel -->
        <div
            x-show="showDeleteModal"
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
                    <div class="flex-shrink-0 p-3 rounded-full bg-red-100/80 dark:bg-red-900/30 animate-pulse">
                        <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600 dark:text-red-400"></i>
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
                                    <span class="block mt-2 text-lg font-bold bg-gradient-to-r from-red-600 to-orange-500 bg-clip-text text-transparent">
                                        "{{ $itemName }}"
                                    </span>
                                @endif
                            </p>

                            <p class="text-sm text-red-600/90 dark:text-red-400/90 font-medium">
                                {{ $dangerText }}
                            </p>
                        </div>

                        @if($requireConfirmation)
                        <div
                            class="mt-4 group flex items-start gap-3 cursor-pointer"
                            @click="deleteConfirmed = !deleteConfirmed"
                        >
                            <div
                                class="relative flex-shrink-0 w-5 h-5 mt-0.5 transition-all duration-200 ease-out"
                                :class="{
                                    'bg-red-500/90 border-red-500/90': deleteConfirmed,
                                    'bg-white/90 dark:bg-zinc-700/90 border-zinc-300 dark:border-zinc-600': !deleteConfirmed
                                }"
                            >
                                <div
                                    class="absolute inset-0 flex items-center justify-center text-white transition-transform"
                                    :class="deleteConfirmed ? 'scale-100' : 'scale-0'"
                                >
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </div>
                            </div>
                            <label
                                for="confirm-delete"
                                class="text-sm text-zinc-700/90 dark:text-zinc-200/90 select-none"
                            >
                                {{ $confirmationLabel }}
                            </label>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Modal footer -->
            <div class="p-6 sm:px-8 sm:pb-8 border-t border-zinc-100/50 dark:border-zinc-700/50">
                <div class="flex gap-3 justify-end">
                    <button
                        @click="closeDeleteModal()"
                        type="button"
                        class="px-5 py-2.5 rounded-xl font-medium text-zinc-700/90 dark:text-zinc-300/90 hover:bg-zinc-100/60 dark:hover:bg-zinc-700/50 transition-colors duration-150"
                    >
                        {{ $cancelText }}
                    </button>
                    <button
                        @click="confirmDelete()"
                        type="button"
                        class="px-5 py-2.5 rounded-xl font-semibold text-white bg-gradient-to-br from-red-500 to-orange-500 hover:from-red-600 hover:to-orange-600 shadow-sm shadow-red-200/50 dark:shadow-red-900/30 transition-all duration-200 disabled:opacity-50 disabled:pointer-events-none"
                        :disabled="requireConfirmation && !deleteConfirmed || isDeleting"
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

