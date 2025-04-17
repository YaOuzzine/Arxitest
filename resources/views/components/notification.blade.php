@php
    // Determine initial state from session flash data
    $initialType = session('success') ? 'success' : (session('error') ? 'error' : (session('info') ? 'info' : null));
    $initialMessage = addslashes(session('success') ?? session('error') ?? session('info') ?? '');
    $showInitially = $initialType && $initialMessage;
@endphp

{{--
    Note: The outer div now always exists, but is hidden until triggered by session or event.
    This is necessary for the event listener to be present.
--}}
<div x-data="{
        show: {{ $showInitially ? 'true' : 'false' }},
        type: '{{ $initialType ?? 'info' }}',
        message: '{{ $initialMessage }}',
        timeout: null
     }"
     x-init="() => {
        if (show) { // If shown by session flash
            timeout = setTimeout(() => show = false, 5000);
        }
     }"
     {{-- **** ADD THIS EVENT LISTENER **** --}}
     x-on:notify.window="() => {
        clearTimeout(timeout); // Clear previous timeout if any
        type = $event.detail.type || 'info';
        message = $event.detail.message || 'Notification';
        show = true;
        timeout = setTimeout(() => show = false, 5000); // Set new timeout
     }"
     x-show="show"
     x-transition:enter="transform ease-out duration-300 transition"
     x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
     x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 flex items-end justify-center px-4 py-6 pointer-events-none sm:p-6 sm:items-start sm:justify-end z-[100]"
     style="display: none;"> {{-- Keep display:none --}}

    {{-- The rest of the notification structure remains the same --}}
    <div class="max-w-sm w-full bg-white dark:bg-zinc-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black dark:ring-zinc-700 ring-opacity-5 overflow-hidden border"
         :class="{
            'border-green-300 dark:border-green-700': type === 'success',
            'border-red-300 dark:border-red-700': type === 'error',
            'border-blue-300 dark:border-blue-700': type === 'info'
         }">
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                     <i data-lucide="check-circle" x-show="type === 'success'" class="h-6 w-6 text-green-500 dark:text-green-400"></i>
                     <i data-lucide="alert-circle" x-show="type === 'error'" class="h-6 w-6 text-red-500 dark:text-red-400"></i>
                     <i data-lucide="info" x-show="type === 'info'" class="h-6 w-6 text-blue-500 dark:text-blue-400"></i>
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium text-zinc-900 dark:text-white" x-text="message"></p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button @click="show = false; clearTimeout(timeout);" class="inline-flex text-zinc-400 dark:text-zinc-500 hover:text-zinc-500 dark:hover:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-800 rounded-md">
                        <span class="sr-only">Close</span>
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
