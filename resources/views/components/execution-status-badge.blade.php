<!-- resources/views/components/execution-status-badge.blade.php -->
@props(['status' => 'unknown'])

@php
    $statusClasses = [
        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'running' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'failed' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        'aborted' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
        'timeout' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
    ];

    $baseClasses = 'px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full';
    $defaultClasses = 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300';

    $classes = $baseClasses . ' ' . ($statusClasses[strtolower($status)] ?? $defaultClasses);

    $displayStatus = ucfirst($status);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($status === 'running')
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @endif
    {{ $displayStatus }}
</span>
