@props([
    'align' => 'right',
    'width' => '48',
    'contentClasses' => 'py-1 bg-white dark:bg-zinc-800 rounded-md shadow-lg ring-1 ring-black ring-opacity-5',
    'triggerClasses' => '',
    'dropdownClasses' => '',
])

@php
$alignmentClasses = match($align) {
    'left' => 'origin-top-left left-0',
    'right' => 'origin-top-right right-0',
    'center' => 'origin-top center left-1/2 transform -translate-x-1/2',
};

$widthClasses = match($width) {
    '48' => 'w-48',
    '56' => 'w-56',
    '64' => 'w-64',
    '72' => 'w-72',
    'full' => 'w-full',
    default => $width,
};
@endphp

<div class="dropdown-container" x-data="{ open: false }" @click.away="open = false" @keydown.escape.window="open = false">
    <div @click="open = !open" class="{{ $triggerClasses }}">
        {{ $trigger }}
    </div>

    <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute z-50 mt-2 {{ $widthClasses }} {{ $alignmentClasses }}"
            style="display: none;">
        <div class="dropdown-menu w-full">
            {{ $content }}
        </div>
    </div>
</div>
