@props(['active' => false, 'disabled' => false])

@php
$classes = 'block w-full text-left px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 focus:outline-none focus:bg-zinc-100 dark:focus:bg-zinc-700 transition-colors';

if ($active) {
    $classes .= ' bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300';
}

if ($disabled) {
    $classes .= ' opacity-50 cursor-not-allowed';
}
@endphp

<a {{ $disabled ? '' : $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
