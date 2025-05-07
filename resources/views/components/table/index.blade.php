<!-- components/table/index.blade.php -->
@props(['hover' => true])

<div class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-zinc-200 dark:divide-zinc-700']) }}>
            {{ $slot }}
        </table>
    </div>

    @isset($pagination)
        <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700">
            {{ $pagination }}
        </div>
    @endisset
</div>
