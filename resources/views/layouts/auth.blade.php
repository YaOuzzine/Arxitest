<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Arxitest') }} - @yield('title', 'Authentication')</title>

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

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/css/auth.css'])

    <style>
        [x-cloak] { display: none !important; }

        /* Enhanced background styles */
        #animated-background {
            z-index: -10;
            overflow: hidden;
        }

        .shape-element {
            pointer-events: none;
        }

        /* Force dark/light mode colors to ensure visibility */
        .dark .shape-element {
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            background-color: rgba(255, 255, 255, 0.03) !important;
        }

        html:not(.dark) .shape-element {
            border: 1px solid rgba(0, 0, 0, 0.1) !important;
            background-color: rgba(0, 0, 0, 0.03) !important;
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
    <div class="min-h-screen flex flex-col justify-between">
        <header class="py-6 px-6 md:px-12">
            <div class="container mx-auto flex justify-between items-center">
                <a href="/" class="flex items-center space-x-2">
                    <!-- Light mode logo (hidden in dark mode) -->
                    <img src="{{ asset('images/logo-icon.svg') }}" alt="Logo" class="w-25 h-20 block dark:hidden" id="logo-light">

                    <!-- Dark mode logo (hidden in light mode) -->
                    <img src="{{ asset('images/logo-icon-w.png') }}" alt="Logo" class="w-25 h-20 hidden dark:block" id="logo-dark">
                </a>

                <div class="flex items-center space-x-4">
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

                    @yield('header-actions')
                </div>
            </div>
        </header>

        <main class="flex-grow">
            <div class="container mx-auto px-6 py-8 md:py-12">
                @yield('content')
            </div>
        </main>

        <footer class="py-6 px-6 md:px-12 border-t border-zinc-200 dark:border-zinc-800">
            <div class="container mx-auto text-center text-sm text-zinc-500 dark:text-zinc-400">
                <p>&copy; {{ date('Y') }} Arxitest. All rights reserved.</p>
            </div>
        </footer>
    </div>

    <div id="animated-background" class="fixed inset-0"></div>

    <!-- Theme toggle and background scripts -->
    <script>
        // Theme toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    if (document.documentElement.classList.contains('dark')) {
                        document.documentElement.classList.remove('dark');
                        localStorage.theme = 'light';
                    } else {
                        document.documentElement.classList.add('dark');
                        localStorage.theme = 'dark';
                    }

                    // Recreate background shapes on theme change
                    setTimeout(function() {
                        recreateBackgroundShapes();
                    }, 100);
                });
            }

            // Create initial background
            createBackgroundShapes();
        });

        // Create animated background shapes
        function createBackgroundShapes() {
            const bg = document.getElementById('animated-background');
            if (!bg) return;

            // Clear existing shapes
            bg.innerHTML = '';

            // Create new shapes
            for (let i = 0; i < 15; i++) {
                createShape();
            }

            // Add new shapes periodically
            window.shapeInterval = setInterval(createShape, 3000);
        }

        // Recreate background shapes on theme change
        function recreateBackgroundShapes() {
            // Clear existing interval
            if (window.shapeInterval) {
                clearInterval(window.shapeInterval);
            }

            // Create new shapes
            createBackgroundShapes();
        }

        // Create a single shape
        function createShape() {
            const bg = document.getElementById('animated-background');
            if (!bg) return;

            const shape = document.createElement('div');
            const size = Math.random() * 80 + 40;
            const isSquare = Math.random() > 0.5;

            // Add shape-element class for styling in CSS
            shape.classList.add('shape-element');

            // Set common styles
            shape.style.position = 'absolute';
            shape.style.width = `${size}px`;
            shape.style.height = isSquare ? `${size}px` : `${size * 1.5}px`;
            shape.style.left = `${Math.random() * 100}%`;
            shape.style.top = `${Math.random() * 100}%`;
            shape.style.transform = `rotate(${Math.random() * 360}deg)`;
            shape.style.transition = 'all 30s linear';

            bg.appendChild(shape);

            // Start animation
            setTimeout(() => {
                shape.style.left = `${Math.random() * 100}%`;
                shape.style.top = `${Math.random() * 100}%`;
                shape.style.transform = `rotate(${Math.random() * 360}deg) scale(${Math.random() + 0.7})`;
            }, 100);

            // Remove after animation completes
            setTimeout(() => {
                if (bg.contains(shape)) {
                    bg.removeChild(shape);
                }
            }, 30000);
        }
    </script>

    <!-- Page-specific scripts -->
    @stack('scripts')
</body>
</html>
