{{-- resources/views/components/nav-link.blade.php --}}
@props([
    'route',
    'icon',
    'params' => [],
    'checkQueryParam' => false, // Keep this prop
    'badge' => null // Add this for the badge slot
])

@php
    $routeName = $route;

    // --- REVISED LOGIC ---
    // Check 1: Does the current route NAME *exactly* match the provided route name?
    $isActiveRoute = request()->routeIs($routeName);

    // Check 2: If checkQueryParam is true, does the 'page' query parameter match?
    $isActiveQuery = $checkQueryParam && isset($params['page']) && request()->query('page') === $params['page'];

    // Active state: EXACT route match OR specific query param match
    $isActive = $isActiveRoute || $isActiveQuery;

    // --- END REVISED LOGIC ---

    // Construct the URL
    // Use url() helper if route name doesn't exist or handle potential errors
    try {
        $url = route($routeName, $params);
    } catch (\Exception $e) {
        // Fallback or log error if route doesn't exist
        $url = '#'; // Default to '#' if route generation fails
        // Log::error("Route generation failed for '{$routeName}' in nav-link component: " . $e->getMessage());
    }

@endphp

<a href="{{ $url }}"
    {{ $attributes->class([
        'nav-link group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 ease-in-out relative overflow-hidden', // Added relative/overflow
        // Active State Styles - Use a more distinct active style
        'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-white font-semibold shadow-inner' => $isActive,
        // Inactive State Styles
        'text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 hover:bg-zinc-100 dark:hover:text-white dark:hover:bg-zinc-700/50' => !$isActive,
    ]) }}
    aria-current="{{ $isActive ? 'page' : 'false' }}">

    {{-- Active Indicator Bar --}}
    <span @class([
            'absolute left-0 top-1 bottom-1 w-1 bg-indigo-600 dark:bg-indigo-400 rounded-r-md transition-transform duration-300 ease-out origin-left', // Adjusted top/bottom and origin
            'scale-y-100' => $isActive,
            'scale-y-0 group-hover:scale-y-75' => !$isActive, // More subtle hover reveal
          ])></span>

    {{-- Icon (adjust margin for the indicator bar) --}}
    <i data-lucide="{{ $icon }}" class="ml-3 mr-3 flex-shrink-0 w-5 h-5 transition-colors duration-200 {{ $isActive ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-600 dark:group-hover:text-zinc-300' }}"></i> {{-- Increased ml --}}

    {{-- Text Content --}}
    <span class="flex-1">{{ $slot }}</span>

    {{-- Slot for potential badges (like the invitation badge) --}}
    @if ($badge) {{-- Check if the slot has content --}}
        {{ $badge }}
    @endif
</a>
