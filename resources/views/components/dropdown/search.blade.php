@props([
    'align' => 'right',
    'width' => '48',
    'contentClasses' => 'py-1 bg-white dark:bg-zinc-800 rounded-md shadow-lg ring-1 ring-black ring-opacity-5',
    'triggerClasses' => '',
    'dropdownClasses' => '',
    'placeholder' => 'Search...',
    'searchTerm' => '',
    'noResultsMessage' => 'No results found',
    'maxHeight' => 'max-h-60'
])

<x-dropdown.index
    :align="$align"
    :width="$width"
    :content-classes="$contentClasses"
    :trigger-classes="$triggerClasses"
    :dropdown-classes="$dropdownClasses">
    <x-slot:trigger>
        {{ $trigger }}
    </x-slot:trigger>

    <x-slot:content>
        <div class="p-2 border-b border-zinc-200 dark:border-zinc-700">
            <input
                type="text"
                {{ $attributes->merge(['class' => 'w-full px-3 py-2 bg-zinc-50 dark:bg-zinc-700 border-transparent rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500']) }}
                placeholder="{{ $placeholder }}"
                x-model="{{ $searchTerm }}"
                @click.stop
            >
        </div>

        <div class="overflow-y-auto {{ $maxHeight }}">
            {{ $content }}
        </div>

        <template x-if="(typeof filteredItems !== 'undefined' && filteredItems.length === 0) || (typeof filteredProjects !== 'undefined' && filteredProjects.length === 0 && searchTerm)">
            <div class="px-4 py-2 text-sm text-zinc-500 dark:text-zinc-400 text-center">
                {{ $noResultsMessage }}
            </div>
        </template>
    </x-slot:content>
</x-dropdown.index>
