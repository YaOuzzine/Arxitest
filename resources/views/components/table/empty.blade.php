<!-- components/table/empty.blade.php -->
@props([
    'icon' => 'file-question',
    'title' => 'No items found',
    'description' => 'No matching items were found.',
    'columns' => 100,
])

<tr>
    <td colspan="{{ $columns }}">
        <div class="text-center py-16 px-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-700 mb-4">
                <i data-lucide="{{ $icon }}" class="w-8 h-8 text-zinc-400 dark:text-zinc-500"></i>
            </div>
            <h3 class="text-lg font-medium text-zinc-800 dark:text-white mb-2">{{ $title }}</h3>
            <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-6">{{ $description }}</p>

            @isset($action)
                {{ $action }}
            @endisset
        </div>
    </td>
</tr>
