<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Arxitest') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50 transition-shadow duration-300">
        <div class="flex items-center justify-between px-4 py-2">
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2 hover:opacity-80 transition-opacity duration-300 cursor-pointer">
                    <img src="{{ asset('logo.svg') }}" alt="Logo" class="w-8 h-8">
                    <select class="text-sm font-medium border-0 focus:ring-0 cursor-pointer hover:bg-gray-50 rounded-md transition-colors duration-300">
                        <option>YasserOuzzine</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <nav class="flex space-x-4">
                    <a href="{{ route('home') }}"
                       class="px-3 py-2 text-sm font-medium relative group {{ Request::routeIs('home') ? 'text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                        Home
                        <div class="absolute bottom-0 left-0 w-full h-0.5 bg-green-600 transform origin-left transition-transform duration-300
                             {{ Request::routeIs('home') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></div>
                    </a>
                    <a href="#"
                       class="px-3 py-2 text-sm font-medium relative group text-gray-500 hover:text-gray-700">
                        Overview
                        <div class="absolute bottom-0 left-0 w-full h-0.5 bg-green-600 transform origin-left transition-transform duration-300 scale-x-0 group-hover:scale-x-100"></div>
                    </a>
                    <a href="#"
                       class="px-3 py-2 text-sm font-medium relative group text-gray-500 hover:text-gray-700">
                        Team dashboard
                        <div class="absolute bottom-0 left-0 w-full h-0.5 bg-green-600 transform origin-left transition-transform duration-300 scale-x-0 group-hover:scale-x-100"></div>
                    </a>
                </nav>

                <div class="flex items-center space-x-2">
                    <button class="p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 transition-all duration-300 group">
                        <i data-lucide="search" class="w-5 h-5 transition-transform duration-300 group-hover:scale-110"></i>
                    </button>
                    <button class="p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 transition-all duration-300 group">
                        <i data-lucide="help-circle" class="w-5 h-5 transition-transform duration-300 group-hover:scale-110"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Layout -->
    <div class="flex min-h-screen">
        <!-- Left Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 sticky top-[57px] h-[calc(100vh-57px)] overflow-y-auto">
            <div class="p-4">
                <button class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-all duration-300 hover:shadow-lg group">
                    <i data-lucide="plus" class="w-4 h-4 mr-2 transition-transform duration-300 group-hover:scale-110"></i>
                    Create new...
                </button>
            </div>

            <nav class="px-4 space-y-1">
                <a href="{{ route('home') }}"
                   class="flex items-center px-2 py-2 text-sm font-medium rounded-lg transition-all duration-300 group
                   {{ Request::routeIs('home') ? 'text-gray-900 bg-gray-100' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i data-lucide="home" class="w-5 h-5 mr-3 transition-transform duration-300 group-hover:scale-110"></i>
                    <span class="transition-colors duration-300 group-hover:text-gray-900">Home</span>
                </a>
                <a href="{{ route('inbox') }}"
                   class="flex items-center px-2 py-2 text-sm font-medium rounded-lg transition-all duration-300 group
                   {{ Request::routeIs('inbox') ? 'text-gray-900 bg-gray-100' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i data-lucide="inbox" class="w-5 h-5 mr-3 transition-transform duration-300 group-hover:scale-110"></i>
                    <span class="transition-colors duration-300 group-hover:text-gray-900">Inbox</span>
                </a>
                <a href="#" class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition-all duration-300 group">
                    <i data-lucide="bar-chart-2" class="w-5 h-5 mr-3 transition-transform duration-300 group-hover:scale-110"></i>
                    <span class="transition-colors duration-300 group-hover:text-gray-900">Reports</span>
                </a>
            </nav>

            <div class="px-4 py-4 mt-4">
                <h2 class="px-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">APPS</h2>
                <nav class="mt-2 space-y-1">
                    <a href="#" class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition-all duration-300 group">
                        <i data-lucide="file-text" class="w-5 h-5 mr-3 transition-transform duration-300 group-hover:scale-110"></i>
                        <span class="transition-colors duration-300 group-hover:text-gray-900">Documents</span>
                    </a>
                    <a href="#" class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition-all duration-300 group">
                        <i data-lucide="terminal" class="w-5 h-5 mr-3 transition-transform duration-300 group-hover:scale-110"></i>
                        <span class="transition-colors duration-300 group-hover:text-gray-900">Dev Center</span>
                    </a>
                    <a href="#" class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition-all duration-300 group">
                        <i data-lucide="users" class="w-5 h-5 mr-3 transition-transform duration-300 group-hover:scale-110"></i>
                        <span class="transition-colors duration-300 group-hover:text-gray-900">Contacts</span>
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden">
            @yield('content')
        </main>
    </div>
</body>
</html>
