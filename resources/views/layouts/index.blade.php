<!-- components/layouts/index.blade.php -->
@props([
    'title',
    'subtitle' => null,
    'createRoute' => null,
    'createLabel' => 'Create New',
    'createIcon' => 'plus-circle',
])

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $subtitle }}</p>
            @endif
        </div>

        @if($createRoute)
            <div class="flex-shrink-0">
                <a href="{{ $createRoute }}" class="inline-flex items-center px-5 py-2.5 group bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                    <i data-lucide="{{ $createIcon }}" class="w-5 h-5 mr-2 transition-transform duration-200 group-hover:rotate-90"></i>
                    {{ $createLabel }}
                </a>
            </div>
        @endif
    </div>

    <!-- Filters (optional) -->
    @isset($filters)
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-md p-5 border border-zinc-200 dark:border-zinc-700">
            {{ $filters }}
        </div>
    @endisset

    <!-- Main Content -->
    {{ $slot }}
</div>
