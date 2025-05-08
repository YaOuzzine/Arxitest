@props([
    'id' => 'delete-modal',
    'title' => 'Delete Confirmation',
    'message' => 'Are you sure you want to delete this item?',
    'itemName' => null,
    'dangerText' => 'This action cannot be undone.',
    'confirmText' => 'Delete',
    'cancelText' => 'Cancel',
    'requireConfirmation' => true,
    'confirmationLabel' => 'I understand that this action is irreversible',
])

{{-- Inline Styles for Custom Checkbox --}}
<style>
    /* Using a unique prefix related to the component ID to minimize conflicts */
    #{{ $id }} .custom-checkbox-container {
        display: flex;
        align-items: flex-start;
        cursor: pointer;
        user-select: none;
    }

    #{{ $id }} .custom-checkbox-input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    #{{ $id }} .custom-checkbox-display {
        height: 20px;
        width: 20px;
        background-color: transparent;
        border: 2px solid #d1d5db; /* Tailwind zinc-300 */
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease-in-out;
        flex-shrink: 0;
    }

    /* Dark mode styles for display border */
    .dark #{{ $id }} .custom-checkbox-display {
        border-color: #52525b; /* Tailwind zinc-600 */
    }

    #{{ $id }} .custom-checkbox-input:checked ~ .custom-checkbox-display {
        background-color: #ef4444; /* Tailwind red-500 */
        border-color: #ef4444; /* Tailwind red-500 */
        transform: scale(1.05) rotate(-5deg);
    }

    /* Dark mode styles for checked display */
    .dark #{{ $id }} .custom-checkbox-input:checked ~ .custom-checkbox-display {
        background-color: #f87171; /* Tailwind red-400 */
        border-color: #f87171; /* Tailwind red-400 */
    }

    #{{ $id }} .custom-checkbox-display svg {
        fill: none;
        stroke: white;
        stroke-width: 3;
        stroke-linecap: round;
        stroke-linejoin: round;
        width: 12px;
        height: 12px;
        opacity: 0;
        transform: scale(0.5) rotate(-45deg);
        transition: opacity 0.2s ease-in-out 0.1s, transform 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55) 0.1s;
    }

    #{{ $id }} .custom-checkbox-input:checked ~ .custom-checkbox-display svg {
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }

    /* Focus styles */
    #{{ $id }} .custom-checkbox-input:focus-visible ~ .custom-checkbox-display {
        outline: 2px solid transparent;
        outline-offset: 2px;
        box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #ef4444; /* ring-white ring-red-500 */
    }
    .dark #{{ $id }} .custom-checkbox-input:focus-visible ~ .custom-checkbox-display {
        box-shadow: 0 0 0 2px #1f2937, 0 0 0 4px #f87171; /* ring-gray-800 ring-red-400 */
    }

    #{{ $id }} .custom-checkbox-label {
        margin-left: 0.75rem; /* Corresponds to space-x-3 */
        font-size: 0.875rem; /* Corresponds to text-sm */
        line-height: 1.25rem; /* Leading-5 */
        color: #3f3f46; /* Tailwind zinc-700 */
    }
    .dark #{{ $id }} .custom-checkbox-label {
        color: #e4e4e7; /* Tailwind zinc-200 */
    }
</style>

<div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 overflow-y-auto backdrop-blur-[2px]" aria-labelledby="{{ $id }}-title-heading" role="dialog"
    aria-modal="true" style="display: none;" id="{{ $id }}">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-zinc-900/60 dark:bg-zinc-900/80 transition-opacity" @click="closeDeleteModal"
            aria-hidden="true"></div>

        <!-- Modal panel -->
        <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-zinc-800 text-left shadow-xl transition-all sm:my-8 w-full max-w-lg border border-zinc-200/80 dark:border-zinc-700/60">
            <!-- Modal content -->
            <div class="p-6 sm:p-8">
                <div class="flex items-start gap-4">
                    <!-- Icon container -->
                    <div class="flex-shrink-0 p-3 rounded-full bg-red-100/80 dark:bg-red-900/30 animate-pulse">
                        <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600 dark:text-red-400"></i>
                    </div>

                    <!-- Text content -->
                    <div class="space-y-4">
                        <h3 class="text-2xl font-bold text-zinc-900 dark:text-white" id="{{ $id }}-title-heading">
                            {{ $title }}
                        </h3>

                        <div class="space-y-3">
                            <p class="text-zinc-600/90 dark:text-zinc-300/90 leading-relaxed">
                                {{ $message }}

                                @if ($itemName)
                                    <span
                                        class="block mt-2 text-lg font-bold bg-gradient-to-r from-red-600 to-orange-500 bg-clip-text text-transparent">
                                        {{-- Assuming itemName is an Alpine variable, x-text would be controlled by parent.
                                             If it's a Blade prop directly, then just {{ $itemName }} is fine.
                                             Given previous context, it's likely controlled by Alpine parent for dynamic updates.
                                        --}}
                                        "<span x-text="{{ $itemName === 'deleteProjectName' || str_starts_with($itemName, 'delete') ? $itemName : "'$itemName'" }}"></span>"
                                    </span>
                                @endif
                            </p>

                            <p class="text-sm text-red-600/90 dark:text-red-400/90 font-medium">
                                {{ $dangerText }}
                            </p>
                        </div>

                        @if ($requireConfirmation)
                            <div class="mt-6">
                                <label for="{{ $id }}-confirm-delete-input" class="custom-checkbox-container group">
                                    <input
                                        id="{{ $id }}-confirm-delete-input"
                                        type="checkbox"
                                        x-model="deleteConfirmed" {{-- This needs to match your Alpine data property --}}
                                        class="custom-checkbox-input"
                                    >
                                    <span class="custom-checkbox-display group-hover:border-red-400 dark:group-hover:border-red-500">
                                        <svg viewBox="0 0 24 24">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    </span>
                                    <span class="custom-checkbox-label">
                                        {{ $confirmationLabel }}
                                    </span>
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Modal footer -->
            <div class="p-6 sm:px-8 sm:pb-8 border-t border-zinc-100/50 dark:border-zinc-700/50">
                <div class="flex gap-3 justify-end">
                    <button @click="closeDeleteModal()" type="button"
                        class="px-5 py-2.5 rounded-xl font-medium text-zinc-700/90 dark:text-zinc-300/90 hover:bg-zinc-100/60 dark:hover:bg-zinc-700/50 transition-colors duration-150">
                        {{ $cancelText }}
                    </button>
                    <button @click="confirmDelete()" type="button"
                        class="px-5 py-2.5 rounded-xl font-semibold text-white bg-gradient-to-br from-red-500 to-orange-500 hover:from-red-600 hover:to-orange-600 shadow-sm shadow-red-200/50 dark:shadow-red-900/30 transition-all duration-200 disabled:opacity-50 disabled:pointer-events-none"
                        :disabled="requireConfirmation && !deleteConfirmed || isDeleting">
                        <span x-show="!isDeleting" class="flex items-center gap-2">
                            <i data-lucide="trash-2" class="w-4 h-4 text-white"></i>
                            {{ $confirmText }}
                        </span>
                        <span x-show="isDeleting" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
