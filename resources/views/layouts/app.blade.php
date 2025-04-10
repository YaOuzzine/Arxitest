<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Arxitest'))</title>
    <meta name="description" content="Arxitest - Intelligent Test Automation Platform for seamless software testing with AI-assisted test generation and containerized execution.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">



    <!-- Theme initialization (prevents flash) -->
    <script>
        // Immediately execute theme setup based on user preference
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/css/welcome.css'])

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <!-- Scripts -->

    @vite(['resources/js/app.js', 'resources/js/welcome.js'])
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="sticky top-0 z-40 w-full bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
            <div class="container mx-auto px-6">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <a href="{{ url('/') }}" class="flex items-center space-x-2">
                        <!-- Light mode logo (hidden in dark mode) -->
                        <img src="{{ asset('images/logo-icon.svg') }}" alt="Arxitest Logo" class="h-12 w-auto block dark:hidden">

                        <!-- Dark mode logo (hidden in light mode) -->
                        <img src="{{ asset('images/logo-icon-w.png') }}" alt="Arxitest Logo" class="h-12 w-auto hidden dark:block">
                    </a>

                    <!-- Navigation - Desktop -->
                    <nav class="hidden md:flex items-center space-x-8">
                        <a href="#features" class="nav-link">Features</a>
                        <a href="#how-it-works" class="nav-link">How It Works</a>
                        <a href="#pricing" class="nav-link">Pricing</a>
                        <a href="#faq" class="nav-link">FAQ</a>
                    </nav>

                    <!-- Actions -->
                    <div class="flex items-center space-x-4">
                        <!-- Theme toggle -->
                        <button id="theme-toggle" type="button" class="rounded-md p-2 bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 transition duration-200 hover:bg-zinc-200 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-500">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden dark:block w-5 h-5">
                                <!-- sun icon -->
                                <circle cx="12" cy="12" r="5"></circle>
                                <line x1="12" y1="1" x2="12" y2="3"></line>
                                <line x1="12" y1="21" x2="12" y2="23"></line>
                                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                                <line x1="1" y1="12" x2="3" y2="12"></line>
                                <line x1="21" y1="12" x2="23" y2="12"></line>
                                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="block dark:hidden w-5 h-5">
                                <!-- moon icon -->
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                            </svg>
                        </button>

                        <!-- Auth links -->
                        @auth
                            <a href="{{ url('/dashboard') }}" class="nav-link">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="nav-link hover:text-zinc-800 dark:hover:text-white">Login</a>
                            <a href="{{ route('register') }}" class="btn-primary-sm">Register</a>
                        @endauth

                        <!-- Mobile menu button -->
                        <button id="mobile-menu-button" class="md:hidden flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobile-menu" class="md:hidden hidden bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
                <div class="container mx-auto px-6 py-4 space-y-3">
                    <a href="#features" class="mobile-nav-link">Features</a>
                    <a href="#how-it-works" class="mobile-nav-link">How It Works</a>
                    <a href="#pricing" class="mobile-nav-link">Pricing</a>
                    <a href="#faq" class="mobile-nav-link">FAQ</a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-zinc-900 text-white py-12">
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-8">
                    <!-- Logo and Description -->
                    <div class="col-span-2">
                        <a href="{{ url('/') }}" class="flex items-center space-x-2 mb-4">
                            <img src="{{ asset('images/logo-icon-w.png') }}" alt="Arxitest Logo" class="h-20 w-auto">
                        </a>
                        <p class="text-zinc-400 mb-4">Intelligent Test Automation Platform that streamlines your QA processes with AI-powered test generation and containerized execution.</p>
                        <div class="flex space-x-4">
                            <a href="#" class="text-zinc-400 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"></path>
                                </svg>
                            </a>
                            <a href="#" class="text-zinc-400 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"></path>
                                </svg>
                            </a>
                            <a href="#" class="text-zinc-400 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22.23 0H1.77C.8 0 0 .8 0 1.77v20.46C0 23.2.8 24 1.77 24h20.46c.98 0 1.77-.8 1.77-1.77V1.77C24 .8 23.2 0 22.23 0zM7.27 20.1H3.65V9.24h3.62V20.1zM5.47 7.76h-.03c-1.22 0-2-.84-2-1.88 0-1.06.8-1.87 2.05-1.87 1.24 0 2 .8 2.02 1.87 0 1.04-.78 1.88-2.05 1.88zM20.34 20.1h-3.63v-5.8c0-1.45-.52-2.45-1.83-2.45-1 0-1.6.67-1.87 1.32-.1.23-.11.55-.11.88v6.05H9.28s.05-9.82 0-10.84h3.63v1.54a3.6 3.6 0 0 1 3.26-1.8c2.39 0 4.18 1.56 4.18 4.89v6.21z"></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Links -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Product</h3>
                        <ul class="space-y-2">
                            <li><a href="#features" class="text-zinc-400 hover:text-white">Features</a></li>
                            <li><a href="#pricing" class="text-zinc-400 hover:text-white">Pricing</a></li>
                            <li><a href="#" class="text-zinc-400 hover:text-white">Case Studies</a></li>
                            <li><a href="#" class="text-zinc-400 hover:text-white">Documentation</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-4">Company</h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-zinc-400 hover:text-white">About Us</a></li>
                            <li><a href="#" class="text-zinc-400 hover:text-white">Careers</a></li>
                            <li><a href="#" class="text-zinc-400 hover:text-white">Blog</a></li>
                            <li><a href="#" class="text-zinc-400 hover:text-white">Contact</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-4">Legal</h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-zinc-400 hover:text-white">Terms of Service</a></li>
                            <li><a href="#" class="text-zinc-400 hover:text-white">Privacy Policy</a></li>
                            <li><a href="#" class="text-zinc-400 hover:text-white">Security</a></li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-zinc-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
                    <p class="text-zinc-400 text-sm">&copy; {{ date('Y') }} Arxitest. All rights reserved.</p>
                    <div class="mt-4 md:mt-0">
                        <a href="mailto:support@arxitest.com" class="text-zinc-400 hover:text-white text-sm">support@arxitest.com</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
