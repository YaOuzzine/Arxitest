<!-- components/empty-state.blade.php -->
@props([
    'icon' => 'file-question',
    'title' => 'No items found',
    'description' => 'No matching items were found.',
])

<div class="text-center py-16 px-6 bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-700 mb-4">
        <i data-lucide="{{ $icon }}" class="w-8 h-8 text-zinc-400 dark:text-zinc-500"></i>
    </div>
    <h3 class="text-lg font-medium text-zinc-800 dark:text-white mb-2">{{ $title }}</h3>
    <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-6">{{ $description }}</p>

    @isset($action)
        <div class="mt-4">
            {{ $action }}
        </div>
    @endisset
</div>
