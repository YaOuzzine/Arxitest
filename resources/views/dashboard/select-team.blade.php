@extends('layouts.dashboard')

@section('title', 'Select Team')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Select Team</span>
    </li>
@endsection

@section('content')
    <div class="h-full">
        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Select a Team</h1>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        Choose a team to continue to the dashboard
                    </p>
                </div>
                <div class="mt-4 md:mt-0 flex space-x-3">
                    <a href="{{ route('teams.create') }}"
                        class="btn-primary inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                        <i data-lucide="plus" class="mr-2 -ml-1 w-4 h-4"></i>
                        Create New Team
                    </a>
                </div>
            </div>
        </div>

        @if ($errors->has('team_id'))
            <div
                class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 rounded-md p-4">
                <div class="flex">
                    <i data-lucide="alert-circle" class="h-5 w-5 text-red-400 dark:text-red-500 mr-2"></i>
                    <span>{{ $errors->first('team_id') }}</span>
                </div>
            </div>
        @endif

        <div
            class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <div class="px-4 py-5 sm:px-6 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-medium leading-6 text-zinc-900 dark:text-white">Your Teams</h3>
                <p class="mt-1 max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">Select a team to access the dashboard</p>
            </div>

            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($teams as $team)
                    <div class="px-4 py-5 sm:p-6 hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors duration-150">
                        <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 h-12 w-12 rounded-md bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center text-zinc-800 dark:text-zinc-200 font-bold text-xl">
                                    {{ substr($team->name, 0, 1) }}
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-medium text-zinc-900 dark:text-white">{{ $team->name }}</h4>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $team->description ?? 'No description' }} •
                                        {{ $team->users->count() }} {{ Str::plural('member', $team->users->count()) }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 sm:mt-0">
                                <form action="{{ route('dashboard.select-team') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="team_id" value="{{ $team->id }}">
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                                        Select Team
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-12 sm:px-6 text-center">
                        <i data-lucide="users" class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-500"></i>
                        <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">No teams</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">You don't belong to any teams yet.</p>
                        <div class="mt-6">
                            <a href="{{ route('teams.create') }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                                <i data-lucide="plus" class="mr-2 -ml-1 w-4 h-4"></i>
                                Create a team
                            </a>
                        </div>
                    </div>
                @endforelse
                @if (count($pendingInvitations) > 0)
                    <div class="mt-8">
                        <div
                            class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div
                                class="px-4 py-5 sm:px-6 border-b border-zinc-200 dark:border-zinc-700 bg-gradient-to-r from-blue-50 to-blue-100/50 dark:from-blue-900/30 dark:to-blue-800/20">
                                <h3 class="text-lg font-medium leading-6 text-zinc-900 dark:text-white">Pending Invitations
                                </h3>
                                <p class="mt-1 max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">Teams you've been invited
                                    to join</p>
                            </div>

                            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @forelse($pendingInvitations as $invitation)
                                    <div
                                        class="px-4 py-5 sm:p-6 hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors duration-150">
                                        <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">
                                            <div class="flex items-center">
                                                <div
                                                    class="flex-shrink-0 h-12 w-12 rounded-md bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-xl">
                                                    {{ substr($invitation->team->name, 0, 1) }}
                                                </div>
                                                <div class="ml-4">
                                                    <h4 class="text-lg font-medium text-zinc-900 dark:text-white">
                                                        {{ $invitation->team->name }}</h4>
                                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                                        {{ $invitation->team->description ?? 'No description' }} •
                                                        You've been invited as <span
                                                            class="font-medium text-zinc-700 dark:text-zinc-300">{{ ucfirst($invitation->role) }}</span>
                                                    </p>
                                                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">
                                                        Expires {{ $invitation->expires_at->diffForHumans() }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="mt-4 sm:mt-0 flex space-x-3">
                                                <form action="{{ route('invitations.reject', $invitation->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                                                        Decline
                                                    </button>
                                                </form>
                                                <form
                                                    action="{{ route('invitations.accept-directly', $invitation->token) }}"
                                                    method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                        class="inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                                                        Accept Invitation
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-5 sm:p-6 text-center">
                                        <p class="text-zinc-500 dark:text-zinc-400">No pending invitations</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
@endpush
