<!-- components/card.blade.php -->
@props([
    'variant' => 'default',   // default, hover, bordered
    'padding' => 'default',   // default, compact, none
])

@php
    // Base classes
    $baseClasses = 'bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden';

    // Variant specific classes
    $variantClasses = [
        'default' => '',
        'hover' => 'hover:shadow-md hover:-translate-y-1 transition-all duration-300',
        'bordered' => 'border-2',
    ][$variant] ?? '';

    // Padding for main content
    $paddingClasses = [
        'default' => 'p-6',
        'compact' => 'p-4',
        'none' => '',
    ][$padding] ?? 'p-6';
@endphp

<div {{ $attributes->merge(['class' => "$baseClasses $variantClasses"]) }}>
    @isset($header)
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
            {{ $header }}
        </div>
    @endisset

    <div class="{{ $paddingClasses }}">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
            {{ $footer }}
        </div>
    @endisset
</div>
