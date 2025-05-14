@extends('layouts.dashboard')

@section('title', 'Teams')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Teams</span>
    </li>
@endsection

@section('content')
    <div class="h-full" x-data="teamsManager()">
        <!-- Header -->
        <x-index-header title="Your Teams" description="Manage your teams and collaborators" :createRoute="route('teams.create')"
            createText="New Team" createIcon="users-plus" :hasSearchInput="true">
        </x-index-header>

        <!-- Teams List -->
        <div class="grid gap-6">
            @if (count($teams) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($teams as $team)
                        <x-entity-card :title="$team->name" :description="$team->description" :logoPath="$team->logo_path" :isHighlighted="$team->id === $currentTeamId"
                            :isCurrentTeam="$team->id === $currentTeamId" :stats="[
                                [
                                    'icon' => 'users',
                                    'value' => $team->users_count,
                                    'label' => Str::plural('member', $team->users_count),
                                ],
                                [
                                    'icon' => 'folder',
                                    'value' => $team->projects_count,
                                    'label' => Str::plural('project', $team->projects_count),
                                ],
                            ]" :badge="$team->id === $currentTeamId" badgeLabel="Current" :viewRoute="route('teams.show', $team->id)"
                            :editRoute="route('teams.edit', $team->id)" :switchAction="'window.location.href=\'' . route('dashboard.select-team') . '?team_id=' . $team->id . '\''" :footer="'Updated ' . $team->updated_at->diffForHumans()">
                        </x-entity-card>
                    @endforeach
                </div>
            @else
                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-8 text-center">
                    <div
                        class="mx-auto h-12 w-12 rounded-full bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center mb-4">
                        <i data-lucide="users" class="h-6 w-6 text-zinc-500 dark:text-zinc-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No teams yet</h3>
                    <p class="text-zinc-500 dark:text-zinc-400 mb-6">Create your first team to start collaborating</p>
                    <a href="{{ route('teams.create') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i data-lucide="users-plus" class="mr-2 -ml-1 h-5 w-5"></i>
                        Create First Team
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('teamsManager', () => ({
                searchTerm: '',

                init() {
                    console.log('Teams Manager initialized');

                    // Initialize search functionality
                    const searchInput = document.getElementById('search-projects');
                    if (searchInput) {
                        searchInput.addEventListener('keyup', (e) => {
                            this.searchTerm = e.target.value.toLowerCase();
                            this.filterTeams();
                        });
                    }

                    // Initialize Lucide icons
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                },

                filterTeams() {
                    const teamCards = document.querySelectorAll('.grid-cols-1.md\\:grid-cols-2 > div');
                    teamCards.forEach(card => {
                        const teamName = card.querySelector('h3').textContent.toLowerCase();
                        const teamDesc = card.querySelector('p').textContent.toLowerCase();

                        if (teamName.includes(this.searchTerm) || teamDesc.includes(this
                                .searchTerm)) {
                            card.classList.remove('hidden');
                        } else {
                            card.classList.add('hidden');
                        }
                    });
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', function() {
            // This ensures Lucide icons are initialized after the DOM is loaded
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Check for flash messages
            @if (session('success'))
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: {
                        type: 'success',
                        message: "{{ session('success') }}"
                    }
                }));
            @endif

            @if (session('error'))
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: {
                        type: 'error',
                        message: "{{ session('error') }}"
                    }
                }));
            @endif
        });
    </script>
@endpush
