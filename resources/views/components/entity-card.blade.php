<!-- resources/views/components/entity-card.blade.php -->
@props([
    'entity',
    'title',
    'description' => null,
    'logoPath' => null,
    'icon' => 'folder',
    'stats' => [],
    'badge' => null,
    'badgeLabel' => null,
    'viewRoute' => '#',
    'editRoute' => null,
    'deleteAction' => null,
    'switchAction' => null,
    'isCurrentTeam' => false,
    'isHighlighted' => false,
])

<div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden hover:shadow-md transition-all duration-200"
     {{ $attributes->merge(['class' => $isHighlighted ? 'ring-2 ring-indigo-500 dark:ring-indigo-400' : '']) }}>
    <div class="p-5">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                @if($logoPath)
                    <img src="{{ Storage::url($logoPath) }}" alt="{{ $title }}" class="h-12 w-12 rounded-lg object-cover">
                @else
                    <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                        <span class="text-white text-lg font-bold">{{ substr($title, 0, 1) }}</span>
                    </div>
                @endif
            </div>
            <div class="ml-4 flex-1">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-white truncate max-w-[160px]">{{ $title }}</h3>
                    <div class="flex space-x-1">
                        @if($editRoute)
                        <a href="{{ $editRoute }}" class="p-1.5 rounded-md text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors" title="Edit">
                            <i data-lucide="edit" class="w-4 h-4"></i>
                        </a>
                        @endif

                        @if($switchAction && !$isCurrentTeam)
                        <button type="button" onclick="{{ $switchAction }}" class="p-1.5 rounded-md text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors" title="Switch to this team">
                            <i data-lucide="log-in" class="w-4 h-4"></i>
                        </button>
                        @endif

                        @if($deleteAction)
                        <button type="button" {{ $deleteAction }} class="p-1.5 rounded-md text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Delete">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                        @endif
                    </div>
                </div>

                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2">
                    {{ $description ?: 'No description provided' }}
                </p>

                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center text-sm text-zinc-500 dark:text-zinc-400 space-x-3">
                        @foreach($stats as $stat)
                            <div class="flex items-center">
                                <i data-lucide="{{ $stat['icon'] }}" class="mr-1.5 h-4 w-4"></i>
                                <span>{{ $stat['value'] }} {{ $stat['label'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    @if($badge)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400">
                            {{ $badgeLabel }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="px-6 py-3 bg-zinc-50 dark:bg-zinc-700/30 border-t border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
        <div class="text-xs text-zinc-500 dark:text-zinc-400">
            {{ $footer ?? 'Updated recently' }}
        </div>
        <a href="{{ $viewRoute }}" class="flex items-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 group transition-all duration-200">
            View Details
            <i data-lucide="arrow-up-right" class="ml-1.5 w-4 h-4 transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform"></i>
        </a>
    </div>
</div>
