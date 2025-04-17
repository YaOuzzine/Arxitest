<!DOCTYPE html>
{{-- Determine current theme for initial load --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full {{ (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') || (!isset($_COOKIE['theme']) && Illuminate\Support\Facades\Cookie::get('theme', 'light') === 'dark') /* Fallback if JS disabled */ ? 'dark' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Arxitest') }}</title>
    <meta name="description"
        content="Arxitest - Intelligent Test Automation Platform for seamless software testing with AI-assisted test generation and containerized execution.">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    {{-- Vite CSS --}}
    @vite(['resources/css/app.css'])

    {{-- Inline Styles --}}
    <style>
        [x-cloak] { display: none !important; }
        /* Transitions */
        .page-transition-enter-active { transition: opacity 0.3s ease-out, transform 0.3s ease-out; }
        .page-transition-enter-from { opacity: 0; transform: translateY(10px); }
        .page-transition-enter-to { opacity: 1; transform: translateY(0); }
        .sidebar-transition { transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out; }
        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background-color: rgba(0, 0, 0, 0.15); border-radius: 10px; }
        .dark ::-webkit-scrollbar-thumb { background-color: rgba(255, 255, 255, 0.15); }
        ::-webkit-scrollbar-thumb:hover { background-color: rgba(0, 0, 0, 0.25); }
        .dark ::-webkit-scrollbar-thumb:hover { background-color: rgba(255, 255, 255, 0.25); }
        /* Card Hover */
        .card-hover { transition: transform 0.2s ease-out, box-shadow 0.2s ease-out; }
        .card-hover:hover { transform: translateY(-3px); box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.08); }
        .dark .card-hover:hover { box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.25); }
        /* Dropdown */
        .dropdown-menu { @apply absolute right-0 mt-2 w-56 origin-top-right bg-white dark:bg-zinc-800 rounded-md shadow-xl ring-1 ring-black dark:ring-zinc-700 ring-opacity-5 focus:outline-none z-50; }
        .dropdown-item { @apply block px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors; }
         /* Animation */
         @keyframes popIn { from { opacity: 0; transform: scale(0.95) translateY(5px); } to { opacity: 1; transform: scale(1) translateY(0); } }
         .animate-pop-in { animation: popIn 0.3s ease-out forwards; opacity: 0; animation-delay: var(--delay, 0s); }
    </style>

    {{-- Vite JS & Alpine --}}
    @vite(['resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> {{-- Keep CDN for simplicity here --}}

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    @stack('meta')
    @stack('styles')
</head>

<body
    class="font-sans antialiased bg-gradient-to-br from-zinc-50 to-slate-100 dark:from-zinc-900 dark:to-slate-900 text-zinc-900 dark:text-zinc-200 h-full overflow-hidden">
    {{-- Main Alpine Component for Layout State --}}
    <div x-data="dashboardLayout({
            initialNotifications: {{ json_encode($layoutNotifications ?? []) }},
            initialUnreadStatus: {{ json_encode($layoutHasUnreadNotifications ?? false) }}
         })"
         x-init="init()"
         class="h-full flex bg-white dark:bg-zinc-900/80 backdrop-blur-sm">

        {{-- Sidebar --}}
        <aside x-show="sidebarOpen"
               x-transition:enter="sidebar-transition"
               x-transition:enter-start="opacity-0 transform -translate-x-full"
               x-transition:enter-end="opacity-100 transform translate-x-0"
               x-transition:leave="sidebar-transition"
               x-transition:leave-start="opacity-100 transform translate-x-0"
               x-transition:leave-end="opacity-0 transform -translate-x-full"
               @click.away="closeSidebarOnMobile()"
               @keydown.escape.window="closeSidebarOnMobile()"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gradient-to-b dark:from-zinc-800 dark:to-zinc-800/95 border-r border-zinc-200 dark:border-zinc-700/50 shadow-lg lg:relative lg:shadow-none lg:translate-x-0"
               aria-label="Sidebar">

            {{-- Sidebar Header with Original Logo --}}
            <div class="h-16 flex items-center justify-between px-4 border-b border-zinc-200 dark:border-zinc-700/50 flex-shrink-0">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center space-x-2 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-800 rounded">
                    {{-- Original Logo Images --}}
                    <img src="{{ asset('images/logo-icon.svg') }}" alt="Arxitest Logo" class="h-20 w-auto block dark:hidden">
                    <img src="{{ asset('images/logo-icon-w.png') }}" alt="Arxitest Logo" class="h-20 w-auto hidden dark:block">
                </a>
                <button @click="sidebarOpen = false"
                    class="lg:hidden p-1 rounded-md text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="sr-only">Close sidebar</span>
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Navigation --}}
            <div class="flex flex-col flex-grow overflow-y-auto custom-scrollbar">
                <nav class="flex-1 px-2 py-4 space-y-3"> {{-- Adjusted padding/spacing --}}
                    {{-- Main Section --}}
                    <div>
                        <h3 class="px-3 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-2" id="main-nav-heading">Main</h3>
                        <ul class="space-y-1" role="list" aria-labelledby="main-nav-heading">
                             {{-- Use the nav-link component --}}
                            <li> <x-nav-link :route="'dashboard'" icon="layout-dashboard">Dashboard</x-nav-link> </li>
                            <li> <x-nav-link :route="'dashboard.projects'" icon="folder-kanban">Projects</x-nav-link> </li>
                            <li> <x-nav-link :route="'dashboard.test-suites.indexAll'" icon="layers-3">Test Suites</x-nav-link> </li>
                            {{-- Links using query parameters (adjust route if dedicated routes exist) --}}
                            <li> <x-nav-link :route="'dashboard.test-cases.indexAll'" :params="['page' => 'test-cases']" icon="check-check" :checkQueryParam="true">Test Cases</x-nav-link> </li>
                            <li> <x-nav-link :route="'dashboard'" :params="['page' => 'executions']" icon="play-circle" :checkQueryParam="true">Executions</x-nav-link> </li>
                        </ul>
                    </div>
                    {{-- Settings Section --}}
                    <div>
                        <h3 class="px-3 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-2 mt-4" id="settings-nav-heading">Management</h3>
                        <ul class="space-y-1" role="list" aria-labelledby="settings-nav-heading">
                            {{-- Use the nav-link component --}}
                             <li>
                                 <x-nav-link :route="'dashboard.teams.index'" icon="users-round">
                                     Teams
                                     {{-- Invitation Badge - Use slot --}}
                                     @if($pendingInvitationCount > 0)
                                        <x-slot name="badge">
                                            <span class="ml-auto inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 text-[10px] font-semibold leading-none rounded-full bg-indigo-500 text-white">
                                                {{ $pendingInvitationCount }}
                                            </span>
                                        </x-slot>
                                    @endif
                                 </x-nav-link>
                             </li>
                             {{-- Links using query parameters (adjust route if dedicated routes exist) --}}
                             <li> <x-nav-link :route="'dashboard.integrations.index'" :params="['page' => 'integrations']" icon="puzzle" :checkQueryParam="true">Integrations</x-nav-link> </li>
                             <li> <x-nav-link :route="'dashboard'" :params="['page' => 'environments']" icon="server" :checkQueryParam="true">Environments</x-nav-link> </li>
                             <li> <x-nav-link :route="'dashboard'" :params="['page' => 'profile']" icon="user-cog" :checkQueryParam="true">Profile</x-nav-link> </li>
                             <li> <x-nav-link :route="'dashboard'" :params="['page' => 'settings']" icon="settings" :checkQueryParam="true">Settings</x-nav-link> </li>
                        </ul>
                    </div>
                </nav>

                {{-- Team Edition / Container Usage (Dynamic) --}}
                <div class="mt-auto px-4 pb-4 pt-2 animate-pop-in" style="--delay: 0.5s;">
                    {{-- This data ($activeSubscription, $activeContainerCount) comes from DashboardLayoutComposer --}}
                    @isset($activeSubscription)
                        @php
                            $maxContainers = $activeSubscription->max_containers ?? 0;
                            $currentUsage = $activeContainerCount ?? 0;
                            $usagePercentage = ($maxContainers > 0) ? min(100, round(($currentUsage / $maxContainers) * 100)) : ($maxContainers === 0 ? 0 : 100); // Handle unlimited (0) or errors
                            $usageColor = $usagePercentage >= 90 ? 'bg-red-500' : ($usagePercentage >= 70 ? 'bg-yellow-500' : 'bg-teal-500');
                            $planName = $activeSubscription->plan_type ? ucwords(str_replace('_', ' ', $activeSubscription->plan_type)) : 'Current';
                        @endphp
                        <div class="bg-gradient-to-br from-zinc-100 to-slate-100 dark:from-zinc-700/60 dark:to-slate-700/50 rounded-xl p-4 border border-zinc-200 dark:border-zinc-600/50 shadow-inner">
                            <div class="flex items-center justify-between mb-2.5">
                                <h3 class="text-sm font-semibold text-zinc-800 dark:text-white">
                                    {{ $planName }} Plan
                                </h3>
                                @if($activeSubscription->is_active ?? false)
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 border border-green-200 dark:border-green-700">
                                    <i data-lucide="check" class="w-3 h-3 mr-1"></i> Active
                                </span>
                                @else
                                 <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-zinc-100 text-zinc-800 dark:bg-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-500">
                                    Inactive
                                </span>
                                @endif
                            </div>

                            @if (!is_null($maxContainers)) {{-- Show usage only if max_containers is set --}}
                                <div class="flex justify-between items-center text-xs text-zinc-500 dark:text-zinc-400 mb-1.5">
                                    <span>Container Usage</span>
                                    <span class="font-medium">{{ $currentUsage }} / {{ $maxContainers === 0 ? 'Unlimited' : $maxContainers }}</span>
                                </div>
                                <div class="w-full bg-zinc-200 dark:bg-zinc-600 rounded-full h-1.5 overflow-hidden relative">
                                     {{-- Add subtle stripe pattern to background --}}
                                    <div class="absolute inset-0 opacity-10 dark:opacity-20" style="background-image: repeating-linear-gradient(-45deg, transparent, transparent 5px, rgba(0,0,0,0.1) 5px, rgba(0,0,0,0.1) 10px);"></div>
                                    <div class="{{ $usageColor }} h-full rounded-full transition-all duration-500 ease-out" style="width: {{ $usagePercentage }}%"></div>
                                </div>
                            @else
                                 {{-- If max_containers is NULL (e.g., truly unlimited or not applicable) --}}
                                 <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1.5">Containers: N/A</p>
                            @endif
                            {{-- Link to subscription page (update route if needed) --}}
                            <a href="{{ route('dashboard') }}?page=subscription"
                                class="mt-3 text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium inline-flex items-center group">
                                Manage Subscription
                                <i data-lucide="arrow-right" class="ml-1 w-3 h-3 group-hover:translate-x-0.5 transition-transform"></i>
                            </a>
                        </div>
                    @else
                         {{-- Fallback if no subscription data could be loaded --}}
                         <div class="bg-zinc-100 dark:bg-zinc-700/30 rounded-lg p-4 text-center border border-zinc-200 dark:border-zinc-600/50">
                             <p class="text-xs text-zinc-500 dark:text-zinc-400">Subscription details unavailable.</p>
                              <a href="#" {{-- Link to billing/plans page --}}
                                 class="mt-2 text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium inline-flex items-center group">
                                View Billing <i data-lucide="chevron-right" class="ml-1 w-3 h-3 group-hover:translate-x-0.5 transition-transform"></i>
                              </a>
                         </div>
                    @endisset
                </div>
            </div>
        </aside>

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col min-h-screen overflow-x-hidden">
            {{-- Top Header Bar --}}
            <header
                class="h-16 flex-shrink-0 flex items-center bg-white/90 dark:bg-zinc-800/90 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-700/50 z-30 sticky top-0 shadow-sm">
                <div class="px-4 sm:px-6 lg:px-8 w-full flex items-center justify-between">
                    {{-- Left Side: Hamburger + Breadcrumbs --}}
                    <div class="flex items-center space-x-3">
                        <button @click="sidebarOpen = !sidebarOpen"
                                class="lg:hidden p-2 -ml-2 rounded-md text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <span class="sr-only">Toggle sidebar</span>
                            <i data-lucide="menu" class="w-5 h-5"></i>
                        </button>

                        {{-- Breadcrumbs --}}
                        <nav class="hidden md:flex" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-1.5 text-sm">
                                <li>
                                    <a href="{{ route('dashboard') }}"
                                        class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 flex items-center transition-colors">
                                        <i data-lucide="home" class="w-4 h-4 mr-1.5 flex-shrink-0 text-zinc-400"></i>
                                        Home
                                    </a>
                                </li>
                                @yield('breadcrumbs') {{-- Page specific breadcrumbs go here --}}
                            </ol>
                        </nav>
                    </div>

                     {{-- Right Side: Theme, Notifications, User Menu --}}
                     {{-- These buttons use the main dashboardLayout's Alpine data --}}
                     <div class="flex items-center space-x-2 sm:space-x-3">
                        {{-- Theme Toggle --}}
                        <button @click="toggleTheme" aria-label="Toggle theme"
                                class="flex items-center justify-center w-9 h-9 rounded-full text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-800 transition-colors">
                            <i data-lucide="sun" class="w-5 h-5 transition-all duration-300 transform" :class="{ 'rotate-90 scale-0': isDarkMode }"></i>
                            <i data-lucide="moon" class="top-5 w-5 h-5 absolute transition-all duration-300 transform" :class="{ 'rotate-0 scale-100': isDarkMode, '-rotate-90 scale-0': !isDarkMode }"></i>
                        </button>

                         {{-- Notifications Dropdown --}}
                         {{-- Use a separate x-data for this dropdown's state --}}
                        <div class="relative" x-data="{ notificationsOpen: false }">
                            <button @click="notificationsOpen = !notificationsOpen" aria-label="View notifications"
                                    class="relative flex items-center justify-center w-9 h-9 rounded-full text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-800 transition-colors">
                                <i data-lucide="bell" class="w-5 h-5"></i>
                                {{-- Access global store for unread status --}}
                                <span x-show="$store.app.hasUnreadNotifications"
                                      class="absolute top-1 right-1 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white dark:ring-zinc-800 animate-pulse">
                                      <span class="sr-only">Unread notifications</span>
                                </span>
                            </button>

                            {{-- Dropdown Panel --}}
                            <div x-show="notificationsOpen" @click.outside="notificationsOpen = false" @keydown.escape.window="notificationsOpen = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="dropdown-menu w-72 sm:w-80 py-0 overflow-hidden" {{-- Removed py-1 --}}
                                 x-cloak>
                                {{-- Header --}}
                                <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center bg-zinc-50 dark:bg-zinc-800/50">
                                     <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">Notifications</h3>
                                     <button @click="$store.app.markAllNotificationsRead()" {{-- Call store method --}}
                                             class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                                             :disabled="!$store.app.hasUnreadNotifications">
                                         Mark all read
                                     </button>
                                </div>
                                {{-- List --}}
                                <div class="max-h-80 overflow-y-auto custom-scrollbar" x-ref="notificationList">
                                    <template x-if="!$store.app.notifications || $store.app.notifications.length === 0">
                                         <div class="px-4 py-10 text-center">
                                             <i data-lucide="bell-off" class="w-10 h-10 mx-auto text-zinc-400 dark:text-zinc-500 mb-2"></i>
                                             <p class="text-sm text-zinc-500 dark:text-zinc-400">No notifications yet.</p>
                                         </div>
                                    </template>
                                    <ul role="list" class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                                        <template x-for="notification in $store.app.notifications" :key="notification.id">
                                            <li>
                                                <a :href="notification.url || '#'"
                                                class="block px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors"
                                                :class="{ 'bg-indigo-50 dark:bg-indigo-900/20 font-medium': !notification.read }">
                                                    {{-- Add notification icons based on type maybe --}}
                                                    <p class="text-sm font-medium text-zinc-900 dark:text-white" x-text="notification.title"></p>
                                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5" x-html="notification.message"></p> {{-- Use x-html if message might contain simple tags --}}
                                                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1" x-text="notification.time_ago"></p>
                                                </a>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                                {{-- Footer link --}}
                                <div class="px-4 py-2 border-t border-zinc-200 dark:border-zinc-700 text-center bg-zinc-50 dark:bg-zinc-800/50">
                                    <a href="#" {{-- Link to all notifications page --}}
                                       class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">View All Notifications</a>
                                </div>
                            </div>
                        </div>

                        {{-- User Menu Dropdown --}}
                        {{-- Use a separate x-data --}}
                        <div class="relative" x-data="{ userMenuOpen: false }">
                            <button @click="userMenuOpen = !userMenuOpen" type="button"
                                class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-800"
                                id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                <span class="sr-only">Open user menu</span>
                                <img class="h-8 w-8 rounded-full object-cover border-2 border-transparent hover:border-indigo-400 dark:hover:border-indigo-500 transition-colors"
                                     src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'U') }}&background=random&color=fff&font-size=0.5&bold=true"
                                     alt="{{ Auth::user()->name ?? 'User' }} avatar">
                                {{-- Show name and chevron on medium screens and up --}}
                                <span class="hidden md:flex items-center ml-2">
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ Auth::user()->name ?? 'User' }}</span>
                                    <i data-lucide="chevron-down" class="ml-1 w-4 h-4 text-zinc-400"></i>
                                </span>
                            </button>

                             <div x-show="userMenuOpen" @click.outside="userMenuOpen = false" @keydown.escape.window="userMenuOpen = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="dropdown-menu w-48 py-1" {{-- Adjusted width and padding --}}
                                 role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1"
                                 x-cloak>
                                {{-- User menu items --}}
                                <a href="{{ route('dashboard') }}?page=profile" class="dropdown-item" role="menuitem" tabindex="-1">Your Profile</a>
                                <a href="{{ route('dashboard.teams.index') }}" class="dropdown-item" role="menuitem" tabindex="-1">Team Settings</a>
                                <a href="{{ route('dashboard.select-team') }}" class="dropdown-item" role="menuitem" tabindex="-1">Switch Team</a>
                                <div class="border-t border-zinc-200 dark:border-zinc-700 my-1"></div>
                                {{-- Logout Form --}}
                                <form method="POST" action="{{ route('logout') }}" role="none">
                                    @csrf
                                    <button type="submit" class="dropdown-item w-full text-left text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20" role="menuitem" tabindex="-1">
                                        Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Main Content --}}
            <main class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-6 lg:p-8">
                 {{-- Animated Content Area --}}
                <div x-transition:enter="page-transition-enter-active"
                     x-transition:enter-start="page-transition-enter-from"
                     x-transition:enter-end="page-transition-enter-to">
                    @yield('content')
                </div>
            </main>

             {{-- Footer --}}
             <footer class="flex-shrink-0 px-4 sm:px-6 lg:px-8 py-3 border-t border-zinc-200 dark:border-zinc-700/50 text-center text-xs text-zinc-500 dark:text-zinc-400">
                 © {{ date('Y') }} {{ config('app.name', 'Arxitest') }}. All rights reserved. | <a href="#" class="hover:underline">Privacy Policy</a> | <a href="#" class="hover:underline">Terms of Service</a>
             </footer>
        </div>
    </div>

    {{-- Global Notification Component (Flash Messages) --}}
    @include('components.flash-message')

    {{-- AlpineJS Initialisation and Layout Logic --}}
    <script>
        function dashboardLayout(config) {
            return {
                sidebarOpen: window.innerWidth >= 1024,
                isDarkMode: localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),

                // Initial state from backend (passed via config)
                initialNotifications: config.initialNotifications || [],
                initialUnreadStatus: config.initialUnreadStatus || false,

                // Alpine Store setup
                setupGlobalStore() {
                     if (!Alpine.store('app')) {
                        Alpine.store('app', {
                             // Initialize with data passed from PHP
                             hasUnreadNotifications: this.initialUnreadStatus,
                             notifications: this.initialNotifications,

                             // --- Methods to manage notifications ---
                             // Add methods here later to fetch new notifications, mark read, etc.
                             // Example:
                             markAllNotificationsRead() {
                                 this.notifications.forEach(n => n.read = false); // Simulate marking read
                                 this.hasUnreadNotifications = false;
                                 console.log('Marking all as read (API call needed)');
                                 // TODO: Add API call to backend to persist this state
                             },
                             // Example: Fetch new notifications (replace with actual API call)
                             fetchNotifications() {
                                console.log('Fetching notifications...');
                                // Simulating an API call
                                setTimeout(() => {
                                    // Replace with real data fetching
                                    // this.notifications = fetchedNotifications;
                                    // this.hasUnreadNotifications = newUnreadStatus;
                                }, 1500);
                             }
                         });
                     }
                 },

                init() {
                    this.setupGlobalStore(); // Initialize the store
                    // Set initial theme class based on calculated isDarkMode
                    this.applyThemeClass();
                    this.renderIcons();

                    window.addEventListener('resize', () => {
                        if (window.innerWidth < 1024) { this.sidebarOpen = false; }
                        else { this.sidebarOpen = true; }
                    });

                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
                        if (!('theme' in localStorage)) { this.setTheme(event.matches); }
                    });

                     // Example: Poll for notifications periodically (replace with websockets if possible)
                     // setInterval(() => {
                     //    if (Alpine.store('app')) {
                     //        Alpine.store('app').fetchNotifications();
                     //    }
                     // }, 60000); // Fetch every 60 seconds
                },
                closeSidebarOnMobile() {
                    if (window.innerWidth < 1024) { this.sidebarOpen = false; }
                },
                applyThemeClass() {
                     if (this.isDarkMode) { document.documentElement.classList.add('dark'); }
                     else { document.documentElement.classList.remove('dark'); }
                },
                toggleTheme() {
                    this.setTheme(!this.isDarkMode);
                },
                setTheme(darkMode) {
                    this.isDarkMode = darkMode;
                    localStorage.theme = darkMode ? 'dark' : 'light';
                    document.cookie = `theme=${darkMode ? 'dark' : 'light'}; path=/; max-age=31536000; SameSite=Lax`; // expires in 1 year
                    this.applyThemeClass();
                    this.renderIcons();
                    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { isDarkMode: this.isDarkMode } }));
                },
                renderIcons() {
                    requestAnimationFrame(() => {
                        if (typeof lucide !== 'undefined') { lucide.createIcons(); }
                        else { console.warn('Lucide icons not available.'); }
                    });
                }
            }
        }
         // Initial icon render on page load (important fallback)
         document.addEventListener('DOMContentLoaded', function() {
             if (typeof lucide !== 'undefined') { lucide.createIcons(); }
         });
    </script>

    {{-- Page Specific Scripts --}}
    @stack('scripts')
</body>
</html>
