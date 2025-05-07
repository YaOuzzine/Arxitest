<!-- resources/views/components/dashboard/index-header.blade.php -->
@props([
    'title',
    'description',
    'searchRoute' => null,
    'createRoute' => null,
    'createText' => 'Create New',
    'createIcon' => 'plus',
    'createDisabled' => false,
    'createDisabledText' => null,
    'filters' => null,
    'hasSearchInput' => false
])

<div class="bg-gradient-to-r from-zinc-50/60 to-indigo-50/30 dark:from-zinc-900/50 dark:to-indigo-900/10 rounded-2xl p-6 shadow-sm border border-zinc-100/70 dark:border-zinc-700/60 backdrop-blur-sm mb-6 transition-all hover:shadow-md">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <!-- Left: Title and Description -->
        <div class="space-y-1.5">
            <h1 class="text-3xl font-extrabold bg-gradient-to-r from-zinc-800 dark:from-zinc-100 to-zinc-600 dark:to-zinc-300 bg-clip-text text-transparent tracking-tight">
                {{ $title }}
            </h1>
            <p class="text-[0.95rem] text-zinc-600/90 dark:text-zinc-300/80 max-w-2xl leading-relaxed">
                {{ $description }}
            </p>
        </div>

        <!-- Right: Actions (Search and Create Button) -->
        <div class="flex items-center gap-3 flex-shrink-0">
            @if($hasSearchInput)
                <div class="relative">
                    <input
                        type="text"
                        id="search-projects"
                        placeholder="Search..."
                        class="w-full sm:w-72 pl-12 pr-5 py-2.5 text-sm rounded-xl border-0 ring-1 ring-zinc-200/80 dark:ring-zinc-600 bg-white/90 dark:bg-zinc-800/90 focus:ring-2 focus:ring-indigo-400/50 placeholder-zinc-400/80 dark:placeholder-zinc-500 transition-all duration-200 hover:ring-zinc-300 dark:hover:ring-zinc-500"
                    >
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-zinc-400/80 dark:text-zinc-500"></i>
                    </div>
                </div>
            @endif

            @if($createDisabled)
                <span class="inline-flex items-center px-5 py-2.5 bg-gradient-to-br from-zinc-100/60 to-zinc-200/40 dark:from-zinc-800/50 dark:to-zinc-700/60 text-zinc-400/90 dark:text-zinc-500 font-medium rounded-xl cursor-not-allowed shadow-inner border border-zinc-200/50 dark:border-zinc-700/50"
                      title="{{ $createDisabledText }}">
                    <i data-lucide="info" class="w-5 h-5 mr-2 text-zinc-400/80 dark:text-zinc-500 transition-transform hover:rotate-12"></i>
                    {{ $createDisabledText }}
                </span>
            @elseif($createRoute)
                <a href="{{ $createRoute }}"
                   class="btn-primary inline-flex items-center px-5 py-2.5 bg-gradient-to-br from-indigo-600 to-purple-500 hover:from-indigo-700 hover:to-purple-600 text-white font-semibold rounded-xl shadow-lg shadow-indigo-200/50 dark:shadow-indigo-900/20 transition-all duration-200 hover:scale-[1.02] hover:shadow-indigo-300/40 dark:hover:shadow-indigo-900/30 group">
                    <i data-lucide="{{ $createIcon }}"
                       class="mr-2 -ml-1 w-5 h-5 transition-transform group-hover:rotate-12"></i>
                    {{ $createText }}
                </a>
            @endif
        </div>
    </div>

    <!-- Optional Filters Section -->
    @if($filters)
        <div class="mt-6 pt-6 border-t border-zinc-100/50 dark:border-zinc-700/50">
            {{ $filters }}
        </div>
    @endif
</div>

<!-- Optional Filters Section -->
@if($filters)
    <div class="mb-6">
        {{ $filters }}
    </div>
@endif
