@props([
    'id' => 'preview-modal',
    'title' => 'Preview',
    'contentClass' => 'text-zinc-800 dark:text-zinc-200',
    'cancelText' => 'Close',
])

<div x-show="showModal" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="{{ $id }}-title" role="dialog" aria-modal="true" style="display: none;" id="{{ $id }}">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
        <div class="fixed inset-0 bg-zinc-900/60 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
            @click="closeModal"></div>
        <div
            class="relative inline-block w-full max-w-4xl p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-zinc-800 shadow-xl rounded-2xl">
            <div class="absolute top-0 right-0 pt-5 pr-5">
                <button type="button" @click="closeModal" class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div>
                <h3 class="text-xl font-medium text-zinc-900 dark:text-zinc-100" id="{{ $id }}-title">
                    {{ $title }}
                </h3>

                <div class="mt-4">
                    <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 max-h-96 overflow-auto {{ $contentClass }}">
                        {{ $slot }}
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <button type="button" @click="closeModal"
                    class="inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ $cancelText }}
                </button>
                {{ $footer ?? '' }}
            </div>
        </div>
    </div>
</div>
