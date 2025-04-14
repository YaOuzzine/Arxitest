@props(['type' => 'success', 'title', 'message'])

<div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 5000)"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform translate-y-2"
    class="fixed bottom-6 right-6 z-50 max-w-sm w-full shadow-lg border rounded-xl p-4"
    :class="{
        'bg-green-50/80 border-green-200/50 dark:bg-green-900/30 dark:border-green-800/30': '{{ $type }}' === 'success',
        'bg-red-50/80 border-red-200/50 dark:bg-red-900/30 dark:border-red-800/30': '{{ $type }}' === 'error',
        'bg-blue-50/80 border-blue-200/50 dark:bg-blue-900/30 dark:border-blue-800/30': '{{ $type }}' === 'info'
    }"
>
    <div class="flex items-start">
        @if($type === 'success')
            <i data-lucide="check-circle" class="flex-shrink-0 w-5 h-5 mt-0.5 mr-3 text-green-600 dark:text-green-400"></i>
        @elseif($type === 'error')
            <i data-lucide="alert-circle" class="flex-shrink-0 w-5 h-5 mt-0.5 mr-3 text-red-600 dark:text-red-400"></i>
        @else
            <i data-lucide="info" class="flex-shrink-0 w-5 h-5 mt-0.5 mr-3 text-blue-600 dark:text-blue-400"></i>
        @endif

        <div>
            <h4 class="font-medium mb-1
                @if($type === 'success') text-green-800 dark:text-green-200
                @elseif($type === 'error') text-red-800 dark:text-red-200
                @else text-blue-800 dark:text-blue-200 @endif">
                {{ $title }}
            </h4>
            <p class="text-sm
                @if($type === 'success') text-green-700/90 dark:text-green-300/90
                @elseif($type === 'error') text-red-700/90 dark:text-red-300/90
                @else text-blue-700/90 dark:text-blue-300/90 @endif">
                {{ $message }}
            </p>
        </div>

        <button
            @click="show = false"
            class="ml-auto -mt-1 -mr-1 p-1 rounded-full hover:bg-zinc-200/50 dark:hover:bg-zinc-700/50 transition-colors"
        >
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
</div>
