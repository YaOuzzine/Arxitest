<!-- components/table/cell.blade.php -->
@props([
    'align' => 'left',
])

@php
    $alignClass = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ][$align] ?? 'text-left';
@endphp

<td {{ $attributes->merge(['class' => "px-6 py-4 whitespace-nowrap text-sm $alignClass"]) }}>
    {{ $slot }}
</td>
