<!-- components/button.blade.php -->
@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'iconPosition' => 'left',
    'fullWidth' => false,
    'disabled' => false,
    'loading' => false,
])

@php
    // Base classes
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-colors rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2';

    // Size classes
    $sizeClasses = [
        'xs' => 'px-2 py-1 text-xs',
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
        'xl' => 'px-6 py-3 text-lg',
    ][$size] ?? 'px-4 py-2 text-sm';

    // Variant classes
    $variantClasses = [
        'primary' => 'bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-white shadow-sm focus:ring-zinc-500 dark:focus:ring-offset-zinc-800',
        'secondary' => 'bg-white hover:bg-zinc-50 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 border border-zinc-300 dark:border-zinc-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-offset-zinc-800',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white shadow-sm focus:ring-red-500 dark:focus:ring-offset-zinc-800',
        'success' => 'bg-green-600 hover:bg-green-700 text-white shadow-sm focus:ring-green-500 dark:focus:ring-offset-zinc-800',
        'link' => 'text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 focus:ring-indigo-500 underline bg-transparent',
    ][$variant] ?? 'bg-zinc-800 hover:bg-zinc-700 text-white';

    // Width classes
    $widthClasses = $fullWidth ? 'w-full' : '';

    // State classes
    $stateClasses = ($disabled || $loading) ? 'opacity-50 cursor-not-allowed' : '';

    // Combine all classes
    $classes = trim("$baseClasses $sizeClasses $variantClasses $widthClasses $stateClasses");
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $classes]) }}
    {{ $disabled ? 'disabled' : '' }}
>
    @if($loading)
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>{{ $slot }}</span>
    @else
        @if($icon && $iconPosition === 'left')
            <i data-lucide="{{ $icon }}" class="w-{{ $size === 'xs' || $size === 'sm' ? '4' : '5' }} h-{{ $size === 'xs' || $size === 'sm' ? '4' : '5' }} mr-2"></i>
        @endif

        <span>{{ $slot }}</span>

        @if($icon && $iconPosition === 'right')
            <i data-lucide="{{ $icon }}" class="w-{{ $size === 'xs' || $size === 'sm' ? '4' : '5' }} h-{{ $size === 'xs' || $size === 'sm' ? '4' : '5' }} ml-2"></i>
        @endif
    @endif
</button>
