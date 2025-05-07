<!-- resources/views/components/list-view.blade.php -->
@props([
    'items' => [],
    'columns' => [],
    'sortField' => 'updated_at',
    'sortDirection' => 'desc',
    'searchTerm' => '',
    'baseRoute' => '',
    'entityName' => 'Items',
    'emptyStateTitle' => 'No items found',
    'emptyStateDescription' => 'No matching items were found.',
    'emptyStateIcon' => 'file-question',
    'createRoute' => null,
    'createLabel' => 'Create New',
    'itemViewRoute' => null, // Callback function to generate view route for each item
    'itemEditRoute' => null, // Callback function to generate edit route for each item
])

<div
    class="bg-white dark:bg-zinc-900/70 shadow-lg rounded-2xl border border-zinc-100 dark:border-zinc-700/60 backdrop-blur-sm overflow-hidden transition-all duration-200 hover:shadow-xl">
    <div
        class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-700/60 flex items-center justify-between bg-gradient-to-r from-zinc-50/50 to-indigo-50/20 dark:from-zinc-800/50 dark:to-indigo-900/10">
        <div class="flex items-center space-x-3">
            <h3 class="text-xl font-bold text-zinc-900 dark:text-white tracking-tight">
                @if (count($items) > 0)
                    <span class="text-indigo-600 dark:text-indigo-400">{{ count($items) }}</span>
                    {{ Str::plural($entityName, count($items)) }}
                @else
                    {{ $entityName }}
                @endif
            </h3>
        </div>

        {{-- Sort Controls --}}
        <div class="flex items-center space-x-3">
            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Sort by:</label>
            <div class="relative">
                <select id="sort-field"
                    class="pl-4 pr-10 py-2 rounded-xl border-0 bg-white/90 dark:bg-zinc-800/90 ring-1 ring-zinc-200 dark:ring-zinc-700 focus:ring-2 focus:ring-indigo-500 text-zinc-700 dark:text-zinc-200 font-medium appearance-none transition-all">
                    @foreach ($columns as $field => $label)
                        @if (isset($field) && $field !== 'actions')
                            <option value="{{ $field }}" {{ $sortField === $field ? 'selected' : '' }}>
                                {{ $label }}</option>
                        @endif
                    @endforeach
                </select>
                <div class="absolute right-0 flex items-center pr-3 pointer-events-none">
                    <i data-lucide="chevron-down" class="w-5 h-5 text-zinc-400 dark:text-zinc-500"></i>
                </div>
            </div>
            <button type="button" id="sort-direction-btn"
                class="p-2 rounded-xl bg-white dark:bg-zinc-800 shadow-sm ring-1 ring-zinc-200 dark:ring-zinc-700 hover:ring-indigo-500 dark:hover:ring-indigo-400 transition-all"
                title="{{ $sortDirection === 'asc' ? 'Sort Descending' : 'Sort Ascending' }}">
                <i data-lucide="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}"
                    class="w-5 h-5 text-indigo-600 dark:text-indigo-400 transition-transform hover:scale-110"></i>
            </button>
        </div>
    </div>

    @if (count($items) === 0)
        <div
            class="text-center py-16 px-6 bg-gradient-to-b from-white/30 to-zinc-50/50 dark:from-zinc-900/30 dark:to-zinc-800/50">
            <div
                class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-indigo-100/80 to-purple-100/80 dark:from-indigo-900/30 dark:to-purple-900/30 mb-4 shadow-inner">
                <i data-lucide="{{ $emptyStateIcon }}"
                    class="w-10 h-10 text-indigo-500/80 dark:text-indigo-400/80"></i>
            </div>
            <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mb-3 tracking-tight">{{ $emptyStateTitle }}
            </h3>
            <p class="text-zinc-500/90 dark:text-zinc-400/80 max-w-md mx-auto mb-6 text-lg leading-relaxed">
                {{ $emptyStateDescription }}
            </p>
        </div>
    @else
        <div class="overflow-x-auto relative">
            <table class="min-w-full divide-y divide-zinc-100 dark:divide-zinc-700/60">
                <thead class="bg-zinc-50/80 dark:bg-zinc-800/80 backdrop-blur-sm sticky top-0">
                    <tr>
                        @foreach ($columns as $field => $label)
                            <th scope="col"
                                class="px-6 py-4 text-left text-sm font-semibold text-zinc-700 dark:text-zinc-300 tracking-wide">
                                <a href="{{ request()->fullUrlWithQuery([
                                    'sort' => $field,
                                    'direction' => request('sort') === $field && request('direction') === 'asc' ? 'desc' : 'asc',
                                ]) }}"
                                    class="group inline-flex items-center hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    {{ $label }}
                                    <span class="ml-1.5 relative transition-transform hover:scale-125">
                                        @if (request('sort') === $field)
                                            <i data-lucide="{{ request('direction') === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                                class="h-4 w-4 text-indigo-600 dark:text-indigo-400 animate-bounce"></i>
                                        @else
                                            <i data-lucide="chevrons-up-down"
                                                class="h-4 w-4 text-zinc-400 dark:text-zinc-500 opacity-60 group-hover:opacity-100 transition-opacity"></i>
                                        @endif
                                    </span>
                                </a>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100/50 dark:divide-zinc-700/50 bg-white/30 dark:bg-zinc-900/30">
                    {{ $slot }}
                </tbody>
            </table>
        </div>

        @if (isset($pagination))
            <div
                class="px-6 py-4 border-t border-zinc-100/50 dark:border-zinc-700/50 bg-zinc-50/30 dark:bg-zinc-800/30">
                {{ $pagination }}
            </div>
        @endif
    @endif
</div>

<script>
    // Original script remains unchanged as per requirements
    document.getElementById('sort-field')?.addEventListener('change', function() {
        const url = new URL(window.location);
        url.searchParams.set('sort', this.value);
        window.location = url.toString();
    });

    document.getElementById('sort-direction-btn')?.addEventListener('click', function() {
        const url = new URL(window.location);
        const currentDirection = url.searchParams.get('direction') || 'desc';
        url.searchParams.set('direction', currentDirection === 'asc' ? 'desc' : 'asc');
        window.location = url.toString();
    });
</script>
