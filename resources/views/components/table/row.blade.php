<!-- components/table/row.blade.php -->
@props([
    'hover' => true,
    'url' => null,
])

@php
    $classes = 'transition-colors ' . ($hover ? 'hover:bg-zinc-50 dark:hover:bg-zinc-700/30' : '');
    $attributes = $attributes->merge(['class' => $classes]);
@endphp

@if($url)
    <tr {{ $attributes }} onclick="window.location='{{ $url }}'" style="cursor: pointer;">
        {{ $slot }}
    </tr>
@else
    <tr {{ $attributes }}>
        {{ $slot }}
    </tr>
@endif
