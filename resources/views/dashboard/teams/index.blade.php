```blade
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
    <!-- Header with action buttons -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Your Teams</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Manage your teams and create new ones
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative flex items-center">
                <input
                    type="text"
                    x-model="searchQuery"
                    placeholder="Search teams..."
                    class="w-60 pl-10 pr-4 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                >
                <i data-lucide="search" class="absolute left-3 text-zinc-400 dark:text-zinc-500 w-5 h-5"></i>
                <button
                    x-show="searchQuery"
                    @click="searchQuery = ''"
                    class="absolute right-2 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300"
                >
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <a href="{{ route('teams.create') }}" class="btn-primary flex items-center px-4 py-2 text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all duration-200 transform hover:scale-[1.02] shadow-md">
                <i data-lucide="users-plus" class="mr-2 w-5 h-5"></i>
                <span>Create Team</span>
            </a>
        </div>
    </div>

    <!-- Team Cards Container -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="(team, index) in filteredTeams" :key="team.id">
            <div
                class="team-card bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-lg hover:translate-y-[-4px]"
                :class="{'opacity-0 translate-y-8': !isTeamVisible(index)}"
                x-init="setTimeout(() => makeTeamVisible(index), 100 + (index * 100))"
            >
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <template x-if="team.logo_path">
                                <img :src="'/storage/' + team.logo_path" class="w-12 h-12 rounded-lg object-cover mr-4" :alt="team.name">
                            </template>
                            <template x-if="!team.logo_path">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xl font-bold mr-4">
                                    <span x-text="getInitials(team.name)"></span>
                                </div>
                            </template>
                            <div>
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white" x-text="team.name"></h3>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400" x-text="team.users_count + ' members'"></p>
                            </div>
                        </div>
                        <div x-data="{ menuOpen: false }" class="relative">
                            <button
                                @click="menuOpen = !menuOpen"
                                class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 p-1 rounded-full focus:outline-none"
                            >
                                <i data-lucide="more-horizontal" class="w-5 h-5"></i>
                            </button>

                            <div
                                x-show="menuOpen"
                                @click.away="menuOpen = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white dark:bg-zinc-700 border border-zinc-200 dark:border-zinc-600 z-10"
                                x-cloak
                            >
                                <div class="py-1">
                                    <a :href="'{{ url('/dashboard/teams') }}/' + team.id" class="block px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-600">
                                        <i data-lucide="eye" class="inline w-4 h-4 mr-2"></i> View Details
                                    </a>
                                    <template x-if="canEditTeam(team)">
                                        <a :href="'{{ url('/dashboard/teams') }}/' + team.id + '/edit'" class="block px-4 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-600">
                                            <i data-lucide="edit-3" class="inline w-4 h-4 mr-2"></i> Edit Team
                                        </a>
                                    </template>
                                    <template x-if="isTeamOwner(team)">
                                        <button @click="confirmDeleteTeam(team)" class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                            <i data-lucide="trash-2" class="inline w-4 h-4 mr-2"></i> Delete Team
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-zinc-600 dark:text-zinc-300 text-sm line-clamp-2 min-h-[40px] mb-4" x-text="team.description || 'No description provided'"></p>

                    <div class="flex items-center justify-between">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400 flex items-center">
                            <i data-lucide="folder" class="w-4 h-4 mr-1"></i>
                            <span x-text="team.projects_count + ' projects'"></span>
                        </div>
                        <div class="flex -space-x-2">
                            <template x-for="(user, userIndex) in team.users.slice(0, 3)" :key="userIndex">
                                <div class="w-8 h-8 rounded-full bg-zinc-200 dark:bg-zinc-700 border-2 border-white dark:border-zinc-800 flex items-center justify-center overflow-hidden" :title="user.name">
                                    <img :src="'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&background=random'" class="w-full h-full object-cover" :alt="user.name">
                                </div>
                            </template>
                            <template x-if="team.users.length > 3">
                                <div class="w-8 h-8 rounded-full bg-zinc-200 dark:bg-zinc-700 border-2 border-white dark:border-zinc-800 flex items-center justify-center text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                    <span x-text="'+' + (team.users.length - 3)"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50 flex justify-between items-center">
                    <span class="text-xs text-zinc-500 dark:text-zinc-400" x-text="formatDate(team.updated_at)"></span>
                    <form x-data="{ submitting: false }" @submit.prevent="selectTeam(team.id)">
                        <template x-if="isCurrentTeam(team)">
                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                <i data-lucide="check" class="w-3 h-3 mr-1"></i> Current Team
                            </span>
                        </template>
                        <template x-if="!isCurrentTeam(team)">
                            <button type="submit" class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/30 dark:hover:bg-blue-800/40 text-blue-800 dark:text-blue-400 transition-colors duration-200" :disabled="submitting">
                                <i data-lucide="log-in" class="w-3 h-3 mr-1"></i>
                                <span x-text="submitting ? 'Switching...' : 'Switch'"></span>
                            </button>
                        </template>
                    </form>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty state -->
    <div x-show="teams.length === 0" class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm p-8 text-center animate-fade-in">
        <div class="mx-auto w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center mb-4">
            <i data-lucide="users" class="w-8 h-8 text-zinc-400 dark:text-zinc-500"></i>
        </div>
        <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No teams yet</h3>
        <p class="text-zinc-500 dark:text-zinc-400 mb-6">Get started by creating your first team</p>
        <a href="{{ route('teams.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i data-lucide="plus" class="mr-2 -ml-1 w-4 h-4"></i>
            Create First Team
        </a>
    </div>

    <!-- Skeleton loader while teams are loading -->
    <div x-show="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="i in 3" :key="i">
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden animate-pulse">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-lg bg-zinc-200 dark:bg-zinc-700 mr-4"></div>
                        <div>
                            <div class="h-5 w-32 bg-zinc-200 dark:bg-zinc-700 rounded mb-2"></div>
                            <div class="h-4 w-20 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                        </div>
                    </div>
                    <div class="h-4 w-full bg-zinc-200 dark:bg-zinc-700 rounded mb-2"></div>
                    <div class="h-4 w-3/4 bg-zinc-200 dark:bg-zinc-700 rounded mb-4"></div>
                    <div class="flex items-center justify-between">
                        <div class="h-4 w-20 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-zinc-200 dark:bg-zinc-700"></div>
                            <div class="w-8 h-8 rounded-full bg-zinc-200 dark:bg-zinc-700"></div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                    <div class="flex justify-between items-center">
                        <div class="h-4 w-24 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                        <div class="h-6 w-20 bg-zinc-200 dark:bg-zinc-700 rounded-full"></div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Delete Team Confirmation Modal -->
    <div x-show="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div
                x-show="showDeleteModal"
                @click="showDeleteModal = false"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-zinc-500 dark:bg-zinc-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity"
                aria-hidden="true"
            ></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                x-show="showDeleteModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
            >
                <div>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30">
                        <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600 dark:text-red-400"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-white" id="modal-title">
                            Delete Team
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400" x-show="teamToDelete">
                                Are you sure you want to delete the team "<span class="font-medium text-zinc-800 dark:text-zinc-200" x-text="teamToDelete?.name"></span>"? This action cannot be undone and all team data will be permanently deleted.
                            </p>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center">
                                <input id="confirm-delete" name="confirm-delete" type="checkbox" x-model="deleteConfirmed" class="h-4 w-4 text-red-600 focus:ring-red-500 border-zinc-300 dark:border-zinc-600 rounded">
                                <label for="confirm-delete" class="ml-2 block text-sm text-zinc-900 dark:text-zinc-100">
                                    I understand that this action is irreversible
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button
                        type="button"
                        @click="deleteTeam()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-zinc-800 sm:col-start-2 sm:text-sm disabled:opacity-50"
                        :disabled="!deleteConfirmed || isDeleting"
                    >
                        <template x-if="isDeleting">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="isDeleting ? 'Deleting...' : 'Delete'"></span>
                    </button>
                    <button type="button" @click="cancelDelete()" class="mt-3 w-full inline-flex justify-center rounded-md border border-zinc-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div x-show="notification.show" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-x-4"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 translate-x-4"
    class="fixed bottom-6 right-6 z-50 max-w-sm w-full">
        <div class="p-4 rounded-xl shadow-lg border backdrop-blur-sm"
            :class="{
                'bg-green-50/80 border-green-200/50 dark:bg-green-900/30 dark:border-green-800/30': notification.type === 'success',
                'bg-red-50/80 border-red-200/50 dark:bg-red-900/30 dark:border-red-800/30': notification.type === 'error'
            }">
            <div class="flex items-start">
                <i data-lucide="check-circle" x-show="notification.type === 'success'"
                    class="flex-shrink-0 w-5 h-5 mt-0.5 mr-3 text-green-600 dark:text-green-400"></i>
                <i data-lucide="alert-circle" x-show="notification.type === 'error'"
                    class="flex-shrink-0 w-5 h-5 mt-0.5 mr-3 text-red-600 dark:text-red-400"></i>
                <div>
                    <h4 class="font-medium mb-1"
                        :class="{
                            'text-green-800 dark:text-green-200': notification.type === 'success',
                            'text-red-800 dark:text-red-200': notification.type === 'error'
                        }"
                        x-text="notification.title"></h4>
                    <p class="text-sm"
                        :class="{
                            'text-green-700/90 dark:text-green-300/90': notification.type === 'success',
                            'text-red-700/90 dark:text-red-300/90': notification.type === 'error'
                        }"
                        x-text="notification.message"></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function teamsManager() {
        return {
            teams: @json($teams),
            currentTeamId: '{{ session('current_team') }}',
            userId: '{{ Auth::id() }}',
            searchQuery: '',
            isLoading: false,
            visibleTeams: [],
            showDeleteModal: false,
            teamToDelete: null,
            deleteConfirmed: false,
            isDeleting: false,
            notification: {
                show: false,
                type: 'success',
                title: '',
                message: '',
                timeout: null
            },

            init() {
                this.$nextTick(() => {
                    lucide.createIcons();
                });
            },

            // Computed properties
            get filteredTeams() {
                if (!this.searchQuery.trim()) {
                    return this.teams;
                }

                const query = this.searchQuery.toLowerCase();
                return this.teams.filter(team =>
                    team.name.toLowerCase().includes(query) ||
                    (team.description && team.description.toLowerCase().includes(query))
                );
            },

            // Animation methods
            makeTeamVisible(index) {
                if (!this.visibleTeams.includes(index)) {
                    this.visibleTeams.push(index);
                }
            },

            isTeamVisible(index) {
                return this.visibleTeams.includes(index);
            },

            // Team utilities
            getInitials(name) {
                return name.split(' ')
                    .map(word => word.charAt(0).toUpperCase())
                    .join('')
                    .substring(0, 2);
            },

            formatDate(dateString) {
                const date = new Date(dateString);
                return new Intl.DateTimeFormat('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                }).format(date);
            },

            isCurrentTeam(team) {
                return team.id === this.currentTeamId;
            },

            isTeamOwner(team) {
                const currentUser = team.users.find(user => user.id === this.userId);
                return currentUser && currentUser.pivot.team_role === 'owner';
            },

            canEditTeam(team) {
                const currentUser = team.users.find(user => user.id === this.userId);
                return currentUser && ['owner', 'admin'].includes(currentUser.pivot.team_role);
            },

            // Team actions
            async selectTeam(teamId) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("dashboard.select-team") }}';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const teamIdInput = document.createElement('input');
                teamIdInput.type = 'hidden';
                teamIdInput.name = 'team_id';
                teamIdInput.value = teamId;

                form.appendChild(csrfToken);
                form.appendChild(teamIdInput);
                document.body.appendChild(form);
                form.submit();
            },

            confirmDeleteTeam(team) {
                this.teamToDelete = team;
                this.deleteConfirmed = false;
                this.showDeleteModal = true;
            },

            cancelDelete() {
                this.showDeleteModal = false;
                this.teamToDelete = null;
                this.deleteConfirmed = false;
            },

            async deleteTeam() {
                if (!this.teamToDelete || !this.deleteConfirmed) return;

                this.isDeleting = true;

                try {
                    const response = await fetch(`/teams/${this.teamToDelete.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // Remove the team from the list
                        this.teams = this.teams.filter(team => team.id !== this.teamToDelete.id);
                        this.showDeleteModal = false;
                        this.teamToDelete = null;
                        this.deleteConfirmed = false;

                        this.showNotification('success', 'Team deleted', 'The team has been successfully deleted');

                        // If deleted the current team, redirect to team selection
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    } else {
                        throw new Error(data.message || 'Failed to delete team');
                    }
                } catch (error) {
                    this.showNotification('error', 'Error', error.message || 'An error occurred while deleting the team');
                } finally {
                    this.isDeleting = false;
                }
            },

            showNotification(type, title, message) {
                this.notification.type = type;
                this.notification.title = title;
                this.notification.message = message;
                this.notification.show = true;

                if (this.notification.timeout) {
                    clearTimeout(this.notification.timeout);
                }

                this.notification.timeout = setTimeout(() => {
                    this.notification.show = false;
                }, 5000);
            }
        };
    }
</script>

<style>
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .animate-fade-in {
        animation: fadeIn 0.5s ease-out forwards;
    }

    /* Smooth entry animation for team cards */
    .team-card {
        transition: opacity 0.5s ease-out, transform 0.5s ease-out;
    }

    /* Hover effects for team cards */
    .team-card:hover {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }

    .dark .team-card:hover {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
    }

    /* Button animations */
    .btn-primary {
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
    }

    /* Team member avatars hover effect */
    .team-card .avatar-group img {
        transition: transform 0.2s ease;
    }

    .team-card:hover .avatar-group img {
        transform: translateY(-2px);
    }
</style>
@endpush
