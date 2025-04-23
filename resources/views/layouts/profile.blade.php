@extends('layouts.dashboard')

@section('title', 'Profile - ' . config('app.name'))

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.profile.show') }}" class="text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white">Profile</a>
    </li>
    @yield('profile-breadcrumbs')
@endsection

@section('content')
<div class="flex flex-col lg:flex-row gap-8">
    {{-- Sidebar Navigation --}}
    <div class="w-full lg:w-64 flex-shrink-0">
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <div class="p-6 text-center border-b border-zinc-200 dark:border-zinc-700">
                <div class="relative mx-auto w-24 h-24 mb-4">
                    <img src="{{ $user->avatar_path ? Storage::url($user->avatar_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF' }}"
                        alt="{{ $user->name }}"
                        class="w-full h-full object-cover rounded-full border-4 border-white dark:border-zinc-700 shadow-md">
                </div>
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white">{{ $user->name }}</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">{{ $user->email }}</p>
            </div>

            <nav class="p-4">
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('dashboard.profile.show') }}"
                            class="flex items-center px-4 py-2.5 rounded-lg {{ request()->routeIs('dashboard.profile.show') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700/50' }}">
                            <i data-lucide="user" class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard.profile.show') ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-400 dark:text-zinc-500' }}"></i>
                            Profile Overview
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard.profile.edit') }}"
                            class="flex items-center px-4 py-2.5 rounded-lg {{ request()->routeIs('dashboard.profile.edit') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700/50' }}">
                            <i data-lucide="edit-3" class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard.profile.edit') ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-400 dark:text-zinc-500' }}"></i>
                            Edit Profile
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard.profile.password') }}"
                            class="flex items-center px-4 py-2.5 rounded-lg {{ request()->routeIs('dashboard.profile.password') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700/50' }}">
                            <i data-lucide="key" class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard.profile.password') ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-400 dark:text-zinc-500' }}"></i>
                            Password
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard.profile.security') }}"
                            class="flex items-center px-4 py-2.5 rounded-lg {{ request()->routeIs('dashboard.profile.security') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700/50' }}">
                            <i data-lucide="shield" class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard.profile.security') ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-400 dark:text-zinc-500' }}"></i>
                            Security
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard.profile.connections') }}"
                            class="flex items-center px-4 py-2.5 rounded-lg {{ request()->routeIs('dashboard.profile.connections') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700/50' }}">
                            <i data-lucide="link" class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard.profile.connections') ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-400 dark:text-zinc-500' }}"></i>
                            Connected Accounts
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard.profile.notifications') }}"
                            class="flex items-center px-4 py-2.5 rounded-lg {{ request()->routeIs('dashboard.profile.notifications') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700/50' }}">
                            <i data-lucide="bell" class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard.profile.notifications') ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-400 dark:text-zinc-500' }}"></i>
                            Notifications
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="flex-1">
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6">@yield('profile-title')</h1>

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800/50 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800/50 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg mb-6">
                    {{ session('error') }}
                </div>
            @endif

            @yield('profile-content')
        </div>
    </div>
</div>
@endsection
