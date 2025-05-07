<!-- components/table/heading.blade.php -->
@props([
    'sortable' => false,
    'direction' => null,
    'field' => null,
    'align' => 'left'
])

@php
    $alignClass = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ][$align] ?? 'text-left';
@endphp

<th {{ $attributes->merge(['class' => "px-6 py-3 $alignClass text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider"]) }}>
    @if($sortable)
        <a href="?sort={{ $field }}&direction={{ $direction === 'asc' ? 'desc' : 'asc' }}" class="group inline-flex items-center">
            {{ $slot }}
            <span class="ml-1.5 relative">
                @if($direction)
                    <i data-lucide="{{ $direction === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="h-4 w-4 text-indigo-600 dark:text-indigo-400"></i>
                @else
                    <i data-lucide="chevrons-up-down" class="h-4 w-4 text-zinc-400 dark:text-zinc-500 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                @endif
            </span>
        </a>
    @else
        {{ $slot }}
    @endif
</th>
