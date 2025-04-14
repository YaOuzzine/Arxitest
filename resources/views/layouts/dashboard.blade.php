<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Arxitest') }}</title>
    <meta name="description"
        content="Arxitest - Intelligent Test Automation Platform for seamless software testing with AI-assisted test generation and containerized execution.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script>
        // Immediately execute theme setup based on user preference
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Transitions for sidebar and content */
        .page-transition-enter {
            opacity: 0;
            transform: translateY(8px);
        }

        .page-transition-enter-active {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.2s ease-out, transform 0.2s ease-out;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        .dark ::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
        }

        ::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 20px;
        }

        /* Hover animations */
        .nav-item {
            transition: all 0.2s ease-in-out;
        }

        .nav-item:hover {
            transform: translateX(4px);
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1),
                0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        .dark .card-hover:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3),
                0 8px 10px -6px rgba(0, 0, 0, 0.2);
        }
    </style>

    @vite(['resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="font-sans antialiased bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 h-full">
    <div x-data="{
        sidebarOpen: window.innerWidth >= 1024,
        activePage: '{{ request()->segment(1) }}',
        activeSubPage: '{{ request()->segment(2) }}',
        userMenuOpen: false,
        notificationsOpen: false,
        notifications: [],
        hasUnreadNotifications: false
    }" class="h-full flex bg-white dark:bg-zinc-900">

        <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-x-20"
            x-transition:enter-end="opacity-100 transform translate-x-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform -translate-x-20"
            @click.away="if (window.innerWidth < 1024) sidebarOpen = false"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-zinc-800 border-r border-zinc-200 dark:border-zinc-700 shadow-sm lg:relative lg:shadow-none transition-all duration-300">
            <div class="h-16 flex items-center justify-between px-4 border-b border-zinc-200 dark:border-zinc-700">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                    <img src="{{ asset('images/logo-icon.svg') }}" alt="Arxitest Logo"
                        class="h-20 w-auto block dark:hidden">
                    <img src="{{ asset('images/logo-icon-w.png') }}" alt="Arxitest Logo"
                        class="h-20 w-auto hidden dark:block">
                </a>
                <button @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden p-2 rounded-md text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="py-4 flex flex-col h-[calc(100vh-4rem)] overflow-y-auto">
                <nav class="px-4 space-y-1 mb-6">
                    <div
                        class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider px-3 mb-2">
                        Main
                    </div>

                    <a href="{{ route('dashboard') }}"
                        class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                        :class="activePage === 'dashboard' || activePage === '' ?
                            'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-white' :
                            'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50'">
                        <i data-lucide="layout-dashboard" class="mr-3 flex-shrink-0 w-5 h-5"
                            :class="activePage === 'dashboard' || activePage === '' ? 'text-zinc-800 dark:text-white' :
                                'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-600 dark:group-hover:text-zinc-300'"></i>
                        Dashboard
                    </a>

                    <a href="{{ route('dashboard.projects') }}?page=projects"
                        class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                        :class="activePage === 'projects' ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-white' :
                            'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50'">
                        <i data-lucide="folder" class="mr-3 flex-shrink-0 w-5 h-5"
                            :class="activePage === 'projects' ? 'text-zinc-800 dark:text-white' :
                                'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-600 dark:group-hover:text-zinc-300'"></i>
                        Projects
                    </a>

                    <a href="{{ route('dashboard') }}?page=test-suites"
                        class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                        :class="activePage === 'test-suites' ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-white' :
                            'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50'">
                        <i data-lucide="layers" class="mr-3 flex-shrink-0 w-5 h-5"
                            :class="activePage === 'test-suites' ? 'text-zinc-800 dark:text-white' :
                                'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-600 dark:group-hover:text-zinc-300'"></i>
                        Test Suites
                    </a>

                    <a href="{{ route('dashboard') }}?page=test-cases"
                        class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                        :class="activePage === 'test-cases' ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-white' :
                            'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50'">
                        <i data-lucide="check-circle" class="mr-3 flex-shrink-0 w-5 h-5"
                            :class="activePage === 'test-cases' ? 'text-zinc-800 dark:text-white' :
                                'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-600 dark:group-hover:text-zinc-300'"></i>
                        Test Cases
                    </a>

                    <a href="{{ route('dashboard') }}?page=executions"
                        class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                        :class="activePage === 'executions' ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-white' :
                            'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50'">
                        <i data-lucide="play" class="mr-3 flex-shrink-0 w-5 h-5"
                            :class="activePage === 'executions' ? 'text-zinc-800 dark:text-white' :
                                'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-600 dark:group-hover:text-zinc-300'"></i>
                        Executions
                    </a>
                </nav>

                <nav class="px-4 space-y-1 mb-6">
                    <div
                        class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider px-3 mb-2">
                        Settings
                    </div>

                    <a href="{{ route('dashboard') }}?page=integrations"
                        class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                        :class="activePage === 'integrations' ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-white' :
                            'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50'">
                        <i data-lucide="puzzle" class="mr-3 flex-shrink-0 w-5 h-5"
                            :class="activePage === 'integrations' ? 'text-zinc-800 dark:text-white' :
                                'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-600 dark:group-hover:text-zinc-300'"></i>
                        Integrations
                    </a>

                    <a href="{{ route('dashboard') }}?page=environments"
                        class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                        :class="activePage === 'environments' ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-white' :
                            'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50'">
                        <i data-lucide="server" class="mr-3 flex-shrink-0 w-5 h-5"
                            :class="activePage === 'environments' ? 'text-zinc-800 dark:text-white' :
                                'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-600 dark:group-hover:text-zinc-300'"></i>
                        Environments
                    </a>

                    <a href="{{ route('dashboard.teams.index') }}?page=team"
                        class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                        :class="activePage === 'team' ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-white' :
                            'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50'">
                        <i data-lucide="users" class="mr-3 flex-shrink-0 w-5 h-5"
                            :class="activePage === 'team' ? 'text-zinc-800 dark:text-white' :
                                'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-600 dark:group-hover:text-zinc-300'"></i>
                        <span>Teams</span>
                        @include('components.invitation-badge')
                    </a>

                    <a href="{{ route('dashboard') }}?page=profile"
                        class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                        :class="activePage === 'profile' ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-white' :
                            'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50'">
                        <i data-lucide="user" class="mr-3 flex-shrink-0 w-5 h-5"
                            :class="activePage === 'profile' ? 'text-zinc-800 dark:text-white' :
                                'text-zinc-400 dark:text-zinc-500 group-hover:text-zinc-600 dark:group-hover:text-zinc-300'"></i>
                        Profile
                    </a>
                </nav>

                <div class="mt-auto px-4">
                    <div class="bg-zinc-50 dark:bg-zinc-700/50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium">Team Edition</h3>
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                Active
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-xs text-zinc-500 dark:text-zinc-400 mb-2">
                            <span>Container usage</span>
                            <span>3/5</span>
                        </div>
                        <div class="w-full bg-zinc-200 dark:bg-zinc-600 rounded-full h-2">
                            <div class="bg-zinc-800 dark:bg-zinc-300 h-2 rounded-full" style="width: 60%"></div>
                        </div>
                        <a href="{{ route('dashboard') }}?page=subscription"
                            class="mt-3 text-xs text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white inline-flex items-center">
                            View subscription details
                            <i data-lucide="chevron-right" class="ml-1 w-3 h-3"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-h-screen overflow-x-hidden">
            <header
                class="h-16 flex items-center bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700 z-30">
                <div class="px-4 sm:px-6 lg:px-8 w-full flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <button @click="sidebarOpen = !sidebarOpen"
                            class="p-2 rounded-md text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 focus:outline-none">
                            <i data-lucide="menu" x-show="!sidebarOpen" class="w-5 h-5"></i>
                            <i data-lucide="x" x-show="sidebarOpen" x-cloak class="w-5 h-5"></i>
                        </button>

                        <nav class="hidden md:flex" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-2">
                                <li>
                                    <a href="{{ route('dashboard') }}"
                                        class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                                        Dashboard
                                    </a>
                                </li>
                                @yield('breadcrumbs')
                            </ol>
                        </nav>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="hidden md:flex relative">
                            <input type="text" placeholder="Search..."
                                class="w-64 pl-10 pr-4 py-2 text-sm rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:focus:ring-zinc-400">
                            <i data-lucide="search"
                                class="absolute left-3 top-4.5 w-4 h-4 text-zinc-400 dark:text-zinc-500"></i>
                        </div>

                        <button id="theme-toggle"
                            class="p-2 rounded-md text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 focus:outline-none"
                            onclick="toggleTheme()">
                            <i data-lucide="sun" class="hidden dark:block w-5 h-5"></i>
                            <i data-lucide="moon" class="block dark:hidden w-5 h-5"></i>
                        </button>

                        <div class="dropdown-container">
                            <button @click="notificationsOpen = !notificationsOpen"
                                class="p-2 rounded-md text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 focus:outline-none relative">
                                <i data-lucide="bell" class="w-5 h-5"></i>
                                <span x-show="hasUnreadNotifications"
                                    class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                            </button>

                            <div x-show="notificationsOpen" @click.away="notificationsOpen = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" class="dropdown-menu" x-cloak>
                                <div
                                    class="p-3 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold">Notifications</h3>
                                    <button
                                        class="text-xs text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                                        Mark all as read
                                    </button>
                                </div>

                                <div class="max-h-80 overflow-y-auto">
                                    <div class="py-2 px-3 bg-zinc-50 dark:bg-zinc-700/30 border-l-4 border-red-500">
                                        <div class="flex items-start">
                                            <i data-lucide="alert-circle"
                                                class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0"></i>
                                            <div>
                                                <p class="text-sm font-medium text-zinc-900 dark:text-white">Test
                                                    execution failed</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Login test
                                                    failed in the production environment.</p>
                                                <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">10 minutes ago
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="py-2 px-3">
                                        <div class="flex items-start">
                                            <i data-lucide="check-circle"
                                                class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                                            <div>
                                                <p class="text-sm font-medium text-zinc-900 dark:text-white">Tests
                                                    completed</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">All tests in
                                                    "User Management" suite passed successfully.</p>
                                                <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">1 hour ago</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="py-2 px-3">
                                        <div class="flex items-start">
                                            <i data-lucide="users"
                                                class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0"></i>
                                            <div>
                                                <p class="text-sm font-medium text-zinc-900 dark:text-white">New team
                                                    member</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">John Smith
                                                    has joined your team.</p>
                                                <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">2 days ago</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-2 border-t border-zinc-200 dark:border-zinc-700 text-center">
                                    <a href="{{ route('dashboard') }}?page=notifications"
                                        class="text-xs text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white">
                                        View all notifications
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="dropdown-container">
                            <button @click="userMenuOpen = !userMenuOpen"
                                class="flex items-center text-sm rounded-full focus:outline-none">
                                <span class="sr-only">Open user menu</span>
                                <img class="h-8 w-8 rounded-full object-cover border border-zinc-200 dark:border-zinc-700"
                                    src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'User' }}&background=random"
                                    alt="User avatar">
                                <span class="hidden md:flex items-center ml-2">
                                    <span class="text-sm font-medium">{{ Auth::user()->name ?? 'User' }}</span>
                                    <i data-lucide="chevron-down" class="ml-1 w-4 h-4 text-zinc-400"></i>
                                </span>
                            </button>

                            <div x-show="userMenuOpen" @click.away="userMenuOpen = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" class="dropdown-menu" x-cloak>
                                <div class="py-1">
                                    <a href="{{ route('dashboard') }}?page=profile"
                                        class="block px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                        Your Profile
                                    </a>
                                    <a href="{{ route('dashboard') }}?page=settings"
                                        class="block px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                        Settings
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}"
                                        class="border-t border-zinc-200 dark:border-zinc-700 mt-1 pt-1">
                                        @csrf
                                        <button type="submit"
                                            class="block w-full text-left px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                            Sign out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto bg-zinc-50 dark:bg-zinc-900 p-4 sm:p-6 lg:p-8 mb-6">
                <div x-show="true" x-transition:enter="page-transition-enter page-transition-enter-active"
                    x-transition:enter-start="page-transition-enter" class="h-full">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });

        // Theme toggle functionality
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }

            // Re-render icons with the new theme
            lucide.createIcons();
        }
    </script>

    @stack('scripts')
    @include('components.flash-message')
</body>

</html>
