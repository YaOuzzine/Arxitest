<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Arxitest') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    @stack('scripts')
    <style>
        /* Additional styles for transitions */
        .dropdown-transition {
            transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Flash Messages -->
    @if(session('success'))
    <div id="flash-message" class="fixed bottom-4 right-4 z-50 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm">{{ session('success') }}</p>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button onclick="document.getElementById('flash-message').style.display = 'none'" class="inline-flex text-green-500 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <span class="sr-only">Dismiss</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div id="flash-message" class="fixed bottom-4 right-4 z-50 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm">{{ session('error') }}</p>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button onclick="document.getElementById('flash-message').style.display = 'none'" class="inline-flex text-red-500 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <span class="sr-only">Dismiss</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(session('info'))
    <div id="flash-message" class="fixed bottom-4 right-4 z-50 bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded shadow-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm">{{ session('info') }}</p>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button onclick="document.getElementById('flash-message').style.display = 'none'" class="inline-flex text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="sr-only">Dismiss</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="flex items-center justify-between px-4 py-2">
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2 hover:opacity-80 transition-opacity duration-300 cursor-pointer">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="w-8 h-8">
                    </a>
                    <span class="text-lg font-semibold text-gray-900">Arxitest</span>
                </div>

                <!-- Team Selector -->
                <div class="relative ml-4">
                    <div class="flex items-center cursor-pointer" id="team-selector-trigger">
                        <span>{{ Auth::user()->teams->first()->name ?? 'Select Team' }}</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>
                    </div>
                    <div class="hidden absolute z-10 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 dropdown-transition" id="team-selector-menu">
                        @if(Auth::check() && Auth::user()->teams)
                            @foreach(Auth::user()->teams as $team)
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ $team->name }}</a>
                            @endforeach
                        @endif
                        <a href="{{ route('teams.create') }}" class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">+ Create New Team</a>
                    </div>
                </div>

                @if(session('jira_access_token'))
                <div class="ml-2 px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs flex items-center">
                    <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                    Jira: {{ session('jira_site_name') ?? 'Connected' }}
                </div>
                @endif
            </div>

            <div class="flex items-center space-x-4">
                <nav class="flex space-x-4">
                    <a href="{{ route('home') }}" class="px-3 py-2 text-sm font-medium relative group {{ Request::routeIs('home') ? 'text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                        Dashboard
                        <div class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600 transform origin-left transition-transform duration-300 {{ Request::routeIs('home') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></div>
                    </a>
                    <a href="{{ route('projects.index') }}" class="px-3 py-2 text-sm font-medium relative group {{ Request::routeIs('projects.*') ? 'text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                        Projects
                        <div class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600 transform origin-left transition-transform duration-300 {{ Request::routeIs('projects.*') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></div>
                    </a>
                    <a href="{{ route('test-scripts.index') }}" class="px-3 py-2 text-sm font-medium relative group {{ Request::routeIs('test-scripts.*') ? 'text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                        Tests
                        <div class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600 transform origin-left transition-transform duration-300 {{ Request::routeIs('test-scripts.*') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></div>
                    </a>
                    <a href="{{ route('test-executions.index') }}" class="px-3 py-2 text-sm font-medium relative group {{ Request::routeIs('test-executions.*') ? 'text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                        Executions
                        <div class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600 transform origin-left transition-transform duration-300 {{ Request::routeIs('test-executions.*') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></div>
                    </a>
                </nav>

                <!-- User Actions Area -->
                <div class="flex items-center space-x-2">
                    <!-- Search -->
                    <div class="relative">
                        <button id="search-button" class="p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 transition-all duration-300 group">
                            <i data-lucide="search" class="w-5 h-5 transition-transform duration-300 group-hover:scale-110"></i>
                        </button>

                        <!-- Search Dropdown (initially hidden) -->
                        <div id="search-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg p-4 z-50 ring-1 ring-black ring-opacity-5 dropdown-transition">
                            <form>
                                <div class="flex rounded-md shadow-sm">
                                    <input type="text" class="flex-1 block w-full rounded-l-md border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" placeholder="Search...">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 text-gray-500 hover:bg-gray-100">
                                        <i data-lucide="search" class="w-4 h-4"></i>
                                    </button>
                                </div>
                                <div class="mt-2">
                                    <div class="flex space-x-2">
                                        <button type="button" class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-700 hover:bg-gray-200">Projects</button>
                                        <button type="button" class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-700 hover:bg-gray-200">Tests</button>
                                        <button type="button" class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-700 hover:bg-gray-200">Results</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="relative">
                        <button id="notifications-button" class="p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 transition-all duration-300 group">
                            <i data-lucide="bell" class="w-5 h-5 transition-transform duration-300 group-hover:scale-110"></i>
                            <span class="absolute top-0 right-0 flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                            </span>
                        </button>

                        <!-- Notifications Dropdown (initially hidden) -->
                        <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50 ring-1 ring-black ring-opacity-5 dropdown-transition">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                                    <button class="text-xs text-blue-600 hover:text-blue-800">Mark all as read</button>
                                </div>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 bg-green-100 rounded-full p-1">
                                            <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                                        </div>
                                        <div class="ml-3 w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900">Test execution completed</p>
                                            <p class="text-xs text-gray-500 mt-1">Login page test passed successfully</p>
                                            <p class="text-xs text-gray-400 mt-1">2 minutes ago</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 bg-red-100 rounded-full p-1">
                                            <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
                                        </div>
                                        <div class="ml-3 w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900">Test execution failed</p>
                                            <p class="text-xs text-gray-500 mt-1">User registration test failed</p>
                                            <p class="text-xs text-gray-400 mt-1">15 minutes ago</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 bg-blue-100 rounded-full p-1">
                                            <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
                                        </div>
                                        <div class="ml-3 w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900">New team member added</p>
                                            <p class="text-xs text-gray-500 mt-1">Sarah Johnson joined your team</p>
                                            <p class="text-xs text-gray-400 mt-1">1 hour ago</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="#" class="block text-center text-sm font-medium text-blue-600 hover:text-blue-700 py-2 border-t border-gray-100">
                                View all notifications
                            </a>
                        </div>
                    </div>

                    <!-- User menu -->
                    <div class="relative">
                        <button id="user-menu-button" class="flex items-center space-x-1 p-1 rounded-full hover:bg-gray-100 transition-all duration-300">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white">
                                {{ Auth::user() ? substr(Auth::user()->name, 0, 1) : 'U' }}
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-500"></i>
                        </button>

                        <!-- User Dropdown Menu (initially hidden) -->
                        <div id="user-menu-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 ring-1 ring-black ring-opacity-5 dropdown-transition transform opacity-0 scale-95">
                            <!-- User Info -->
                            <div class="px-4 py-3 border-b border-gray-100">
                                <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name ?? 'User' }}</div>
                                <div class="text-xs text-gray-500">{{ Auth::user()->email ?? 'user@example.com' }}</div>
                            </div>

                            <!-- Profile -->
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                                <div class="flex items-center">
                                    <i data-lucide="user" class="w-4 h-4 mr-2 text-gray-500"></i>
                                    Profile
                                </div>
                            </a>

                            <!-- Account Settings -->
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                                <div class="flex items-center">
                                    <i data-lucide="settings" class="w-4 h-4 mr-2 text-gray-500"></i>
                                    Settings
                                </div>
                            </a>

                            <!-- Teams -->
                            <a href="{{ route('teams.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                                <div class="flex items-center">
                                    <i data-lucide="users" class="w-4 h-4 mr-2 text-gray-500"></i>
                                    My Teams
                                </div>
                            </a>

                            <!-- Help & Support -->
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                                <div class="flex items-center">
                                    <i data-lucide="help-circle" class="w-4 h-4 mr-2 text-gray-500"></i>
                                    Help & Support
                                </div>
                            </a>

                            <!-- Divider -->
                            <div class="border-t border-gray-100 my-1"></div>

                            <!-- Logout Form -->
                            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 transition-colors duration-150">
                                    <div class="flex items-center">
                                        <i data-lucide="log-out" class="w-4 h-4 mr-2 text-red-500"></i>
                                        Sign out
                                    </div>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Layout -->
    <div class="flex min-h-screen">
        <!-- Left Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 sticky top-[57px] h-[calc(100vh-57px)] overflow-y-auto">
            <div class="p-4">
                <!-- Create new dropdown -->
                <div class="relative" id="create-new-container">
                    <button id="create-new-button" class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-all duration-300 hover:shadow-lg">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        Create New
                    </button>
                    <div id="create-new-menu" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg dropdown-transition">
                        <a href="{{ route('projects.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <div class="flex items-center">
                                <i data-lucide="folder" class="w-4 h-4 mr-2 text-blue-500"></i>
                                New Project
                            </div>
                        </a>
                        <a href="{{ route('test-scripts.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <div class="flex items-center">
                                <i data-lucide="file-code" class="w-4 h-4 mr-2 text-green-500"></i>
                                New Test Script
                            </div>
                        </a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <div class="flex items-center">
                                <i data-lucide="zap" class="w-4 h-4 mr-2 text-yellow-500"></i>
                                AI-Generate Test
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Navigation -->
            <nav class="px-4 space-y-1">
                <a href="{{ route('home') }}" class="flex items-center px-2 py-2 text-sm font-medium rounded-lg transition-all duration-300 group {{ Request::routeIs('home') ? 'text-gray-900 bg-gray-100' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i data-lucide="home" class="w-5 h-5 mr-3"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('projects.index') }}" class="flex items-center px-2 py-2 text-sm font-medium rounded-lg transition-all duration-300 group {{ Request::routeIs('projects.*') ? 'text-gray-900 bg-gray-100' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i data-lucide="folder" class="w-5 h-5 mr-3"></i>
                    <span>Projects</span>
                </a>
                <a href="{{ route('test-scripts.index') }}" class="flex items-center px-2 py-2 text-sm font-medium rounded-lg transition-all duration-300 group {{ Request::routeIs('test-scripts.*') ? 'text-gray-900 bg-gray-100' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i data-lucide="file-code" class="w-5 h-5 mr-3"></i>
                    <span>Test Scripts</span>
                </a>
                <a href="{{ route('test-executions.index') }}" class="flex items-center px-2 py-2 text-sm font-medium rounded-lg transition-all duration-300 group {{ Request::routeIs('test-executions.*') ? 'text-gray-900 bg-gray-100' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i data-lucide="play" class="w-5 h-5 mr-3"></i>
                    <span>Executions</span>
                </a>
            </nav>

            <!-- Teams Navigation Section -->
            <div class="px-4 py-4 mt-2">
                <h2 class="px-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">TEAMS</h2>

                <a href="{{ route('teams.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('teams.index') ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="{{ request()->routeIs('teams.index') ? 'text-gray-500' : 'text-gray-400 group-hover:text-gray-500' }} flex-shrink-0 -ml-1 mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    My Teams
                </a>

                <a href="{{ route('teams.create') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('teams.create') ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="{{ request()->routeIs('teams.create') ? 'text-gray-500' : 'text-gray-400 group-hover:text-gray-500' }} flex-shrink-0 -ml-1 mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Create/Join Team
                </a>
            </div>

            <!-- Integrations Section -->
            <div class="px-4 py-4 mt-2">
                <h2 class="px-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">INTEGRATIONS</h2>
                <nav class="mt-2 space-y-1">
                    <a href="{{ session('jira_access_token') ? url('/jira/import') : url('/jira/oauth') }}"
                    class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition-all duration-300 group {{ Request::is('jira*') ? 'text-gray-900 bg-gray-100' : '' }}">
                        <i data-lucide="trello" class="w-5 h-5 mr-3"></i>
                        <span>Jira Stories</span>
                        @if(session('jira_access_token'))
                            <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                <i data-lucide="check" class="w-3 h-3 mr-1"></i>Connected
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('integrations.index') }}" class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition-all duration-300 group {{ Request::routeIs('integrations.*') ? 'text-gray-900 bg-gray-100' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i data-lucide="link" class="w-5 h-5 mr-3"></i>
                        <span>Manage Integrations</span>
                    </a>
                </nav>
            </div>

            <!-- Analytics Section -->
            <div class="px-4 py-2">
                <h2 class="px-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">ANALYTICS</h2>
                <nav class="mt-2 space-y-1">
                    <a href="#" class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition-all duration-300 group">
                        <i data-lucide="bar-chart-2" class="w-5 h-5 mr-3"></i>
                        <span>Test Reports</span>
                    </a>
                    <a href="#" class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition-all duration-300 group">
                        <i data-lucide="cpu" class="w-5 h-5 mr-3"></i>
                        <span>Resource Usage</span>
                    </a>
                </nav>
            </div>

            <!-- Settings Section -->
            <div class="px-4 py-2 mt-2">
                <h2 class="px-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">SETTINGS</h2>
                <nav class="mt-2 space-y-1">
                    <a href="#" class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition-all duration-300 group">
                        <i data-lucide="users" class="w-5 h-5 mr-3"></i>
                        <span>Team Management</span>
                    </a>
                    <a href="#" class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition-all duration-300 group">
                        <i data-lucide="credit-card" class="w-5 h-5 mr-3"></i>
                        <span>Subscription</span>
                    </a>
                    <a href="#" class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition-all duration-300 group">
                        <i data-lucide="user" class="w-5 h-5 mr-3"></i>
                        <span>Profile</span>
                    </a>
                </nav>
            </div>

            <!-- Help & Support -->
            <div class="px-4 py-2 mt-auto">
                <a href="#" class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition-all duration-300 group mt-4">
                    <i data-lucide="help-circle" class="w-5 h-5 mr-3"></i>
                    <span>Help & Support</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-2 py-2 mt-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition-all duration-300 group">
                        <i data-lucide="log-out" class="w-5 h-5 mr-3"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden">
            @yield('content')
        </main>
    </div>

    <!-- Initialization Scripts -->
    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Auto-dismiss flash messages after 5 seconds
        const flashMessage = document.getElementById('flash-message');
        if (flashMessage) {
            setTimeout(function() {
                flashMessage.style.display = 'none';
            }, 5000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // All dropdowns array for easy management
            const dropdowns = [
                { trigger: 'team-selector-trigger', menu: 'team-selector-menu' },
                { trigger: 'user-menu-button', menu: 'user-menu-dropdown' },
                { trigger: 'create-new-button', menu: 'create-new-menu' },
                { trigger: 'notifications-button', menu: 'notifications-dropdown' },
                { trigger: 'search-button', menu: 'search-dropdown' }
            ];

            // Setup toggle functionality for all dropdowns
            dropdowns.forEach(dropdown => {
                const triggerEl = document.getElementById(dropdown.trigger);
                const menuEl = document.getElementById(dropdown.menu);

                if (triggerEl && menuEl) {
                    // Toggle functionality
                    triggerEl.addEventListener('click', function(e) {
                        e.stopPropagation();

                        // Close all other dropdowns first
                        dropdowns.forEach(d => {
                            if (d.menu !== dropdown.menu) {
                                const otherMenu = document.getElementById(d.menu);
                                if (otherMenu) {
                                    if (d.menu === 'user-menu-dropdown') {
                                        // For user menu, use animation
                                        otherMenu.classList.remove('opacity-100', 'scale-100');
                                        otherMenu.classList.add('opacity-0', 'scale-95');
                                        setTimeout(() => {
                                            otherMenu.classList.add('hidden');
                                        }, 200);
                                    } else {
                                        otherMenu.classList.add('hidden');
                                    }
                                }
                            }
                        });

                        // Toggle this dropdown with animation if it's the user menu
                        if (dropdown.menu === 'user-menu-dropdown') {
                            if (menuEl.classList.contains('hidden')) {
                                menuEl.classList.remove('hidden');
                                // Force a reflow
                                void menuEl.offsetWidth;
                                menuEl.classList.remove('opacity-0', 'scale-95');
                                menuEl.classList.add('opacity-100', 'scale-100');
                            } else {
                                menuEl.classList.remove('opacity-100', 'scale-100');
                                menuEl.classList.add('opacity-0', 'scale-95');
                                setTimeout(() => {
                                    menuEl.classList.add('hidden');
                                }, 200);
                            }
                        } else {
                            // Simple toggle for other dropdowns
                            menuEl.classList.toggle('hidden');
                        }
                    });

                    // Prevent dropdown from closing when clicking inside
                    menuEl.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });
                }
            });

            // Close all dropdowns when clicking elsewhere on the page
            document.addEventListener('click', function() {
                dropdowns.forEach(dropdown => {
                    const menuEl = document.getElementById(dropdown.menu);
                    if (menuEl) {
                        if (dropdown.menu === 'user-menu-dropdown') {
                            menuEl.classList.remove('opacity-100', 'scale-100');
                            menuEl.classList.add('opacity-0', 'scale-95');
                            setTimeout(() => {
                                menuEl.classList.add('hidden');
                            }, 200);
                        } else {
                            menuEl.classList.add('hidden');
                        }
                    }
                });
            });

            // Handle logout confirmation for both logout forms
            const logoutForms = [
                document.getElementById('logout-form'),
                document.getElementById('sidebar-logout-form')
            ];

            logoutForms.forEach(form => {
                if (form) {
                    form.addEventListener('submit', function(e) {
                        if (!confirm('Are you sure you want to log out?')) {
                            e.preventDefault();
                        }
                    });
                }
            });
        });

        // Auto-include token in all fetch requests
        const originalFetch = window.fetch;
        window.fetch = function() {
            const token = localStorage.getItem('token');
            if (token && !arguments[1]?.headers?.Authorization) {
                if (!arguments[1]) {
                    arguments[1] = {};
                }
                if (!arguments[1].headers) {
                    arguments[1].headers = {};
                }
                arguments[1].headers.Authorization = `Bearer ${token}`;
            }
            return originalFetch.apply(this, arguments);
        };
    </script>

    @stack('scripts')
</body>
</html>
