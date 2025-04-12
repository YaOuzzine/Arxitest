@extends('layouts.dashboard')

@section('title', 'Select Team')

@push('styles')
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .team-card {
            transition: all 0.3s ease;
        }

        .team-card:hover {
            transform: translateY(-4px);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .shine-effect {
            position: relative;
            overflow: hidden;
        }

        .shine-effect::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(to right, transparent 0%, rgba(255, 255, 255, 0.3) 50%, transparent 100%);
            transform: rotate(30deg);
            animation: shine 3s infinite linear;
            pointer-events: none;
        }

        @keyframes shine {
            from { transform: translateX(-100%) rotate(30deg); }
            to { transform: translateX(100%) rotate(30deg); }
        }

        .modal-transition {
            transition: all 0.3s ease-out;
        }
    </style>
@endpush

@section('content')
    <div class="min-h-screen flex flex-col" x-data="{ tab: 'my-teams'}">
        <main class="flex-grow py-8">
            <div class="max-w-9xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow overflow-hidden">
                    <div class="border-b border-gray-200 dark:border-zinc-700">
                        <nav class="flex -mb-px" aria-label="Tabs">
                            <button @click="tab = 'my-teams'" :class="{'border-blue-500 text-blue-600 dark:text-blue-400': tab === 'my-teams', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-500': tab !== 'my-teams'}" class="group inline-flex items-center py-4 px-6 border-b-2 font-medium text-sm" id="tab-my-teams" aria-controls="tab-panel-my-teams" :aria-selected="tab === 'my-teams'">
                                <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                My Teams
                            </button>
                            <button @click="tab = 'recent'" :class="{'border-blue-500 text-blue-600 dark:text-blue-400': tab === 'recent', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-500': tab !== 'recent'}" class="group inline-flex items-center py-4 px-6 border-b-2 font-medium text-sm" id="tab-recent" aria-controls="tab-panel-recent" :aria-selected="tab === 'recent'">
                                <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                </svg>
                                Recent
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">
                        <div x-show="tab === 'my-teams'" id="tab-panel-my-teams" class="tab-content active" aria-labelledby="tab-my-teams">
                            <div class="flex justify-between items-center mb-6">
                                <h2 class="text-xl font-semibold">Your Teams</h2>
                                <div class="flex space-x-3">
                                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-zinc-300 bg-white dark:bg-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="8.5" cy="7" r="4"></circle>
                                            <line x1="20" y1="8" x2="20" y2="14"></line>
                                            <line x1="23" y1="11" x2="17" y2="11"></line>
                                        </svg>
                                        Join Team
                                    </a>
                                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                        Create New Team
                                    </a>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                @forelse($teams as $team)
                                    <div class="team-card bg-white dark:bg-zinc-700 rounded-lg shadow-sm overflow-hidden border border-gray-200 dark:border-zinc-600">
                                        <div class="p-5">
                                            <div class="flex justify-between items-start">
                                                <div class="flex items-center mb-3">
                                                    {{-- Team Initials --}}
                                                    @php
                                                        $initials = collect(explode(' ', $team->name))->map(fn($p)=>mb_substr($p,0,1))->join('');
                                                    @endphp
                                                    <div class="h-12 w-12 rounded-md bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold text-xl">
                                                        {{ $initials }}
                                                    </div>
                                                    <div class="ml-3">
                                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $team->name }}</h3>
                                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ $team->members->count() }} member{{ $team->members->count() != 1 ? 's' : '' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        {{ $team->owner_id === $user->id
                                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                            : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' }}">
                                                    {{ $team->owner_id === $user->id ? 'Owner' : 'Member' }}
                                                </span>
                                            </div>
                                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                                {{ $team->description ?? 'No description provided.' }}
                                            </p>
                                            <div class="mt-4 flex justify-between items-center">
                                                <div class="flex -space-x-2 overflow-hidden">
                                                    @foreach($team->members->take(3) as $member)
                                                        @php
                                                            $minit = collect(explode(' ', $member->name))->map(fn($p)=>mb_substr($p,0,1))->join('');
                                                        @endphp
                                                        <div class="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-zinc-700
                                                            bg-gray-200 dark:bg-gray-700 text-xs flex items-center justify-center">
                                                            {{ $minit }}
                                                        </div>
                                                    @endforeach
                                                    @if($team->members->count() > 3)
                                                        <div class="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-zinc-700
                                                            bg-indigo-500 text-white text-xs flex items-center justify-center">
                                                            +{{ $team->members->count() - 3 }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <form action="/dashboard/set-team" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="team_id" value="{{ $team->id }}">
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                        Select Team
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-zinc-800 px-5 py-3 border-t border-gray-200 dark:border-zinc-600">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500 dark:text-gray-400">Last active: {{ $team->updated_at->diffForHumans() }}</span>
                                                <span class="text-gray-500 dark:text-gray-400">
                                                    {{ $team->projects->count() ?? 0 }} projects
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="col-span-full text-gray-500 dark:text-gray-400">
                                        You are not in any teams yet.
                                    </p>
                                @endforelse
                            </div>
                        </div>

                        <div x-show="tab === 'recent'" id="tab-panel-recent" class="tab-content" aria-labelledby="tab-recent">
                            <h2 class="text-xl font-semibold mb-6">Recently Accessed Teams</h2>
                            <div class="space-y-4">
                                <div class="bg-white dark:bg-zinc-700 rounded-lg shadow-sm overflow-hidden border border-gray-200 dark:border-zinc-600 flex items-center p-4">
                                    <div class="h-10 w-10 rounded-md bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold text-lg">
                                        DT
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h3 class="text-md font-medium text-gray-900 dark:text-white">DevTest Team</h3>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Accessed 2 hours ago</span>
                                    </div>
                                    <form action="/dashboard/set-team" method="POST">
                                        @csrf
                                        <input type="hidden" name="team_id" value="1">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Select
                                        </button>
                                    </form>
                                </div>

                                <div class="bg-white dark:bg-zinc-700 rounded-lg shadow-sm overflow-hidden border border-gray-200 dark:border-zinc-600 flex items-center p-4">
                                    <div class="h-10 w-10 rounded-md bg-purple-100 dark:bg-purple-900 flex items-center justify-center text-purple-600 dark:text-purple-300 font-bold text-lg">
                                        QA
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h3 class="text-md font-medium text-gray-900 dark:text-white">QA Analysis</h3>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Accessed yesterday</span>
                                    </div>
                                    <form action="/dashboard/set-team" method="POST">
                                        @csrf
                                        <input type="hidden" name="team_id" value="2">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Select
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest"></script>
    <script>
        // Dynamic content interaction
        document.addEventListener('DOMContentLoaded', function() {
            // Theme initialization
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            // Theme toggle functionality
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
                });
            }

            // Initialize Lucide icons
            lucide.createIcons();
        });

        // Tab switching functionality
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab
            document.querySelector(`.tab-content[data-tab="${tabName}"]`).classList.add('active');
        }
    </script>
@endpush
