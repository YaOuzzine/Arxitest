@extends('layouts.dashboard')

@section('title', 'Edit Team')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.teams.index') }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
            Teams
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Edit Team</span>
    </li>
@endsection

@section('content')
<div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden"
     x-data="editTeam({{ json_encode(['team' => $team, 'members' => $team->users]) }})"
     x-cloak>
    <div class="p-6 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">Edit Team</h2>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Update your team information and manage members
            </p>
        </div>
        <div class="flex items-center space-x-3" x-show="hasChanges">
            <span class="text-sm text-zinc-500 dark:text-zinc-400">You have unsaved changes</span>
            <span class="flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-purple-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-purple-500"></span>
            </span>
        </div>
    </div>

    <div class="p-6 space-y-10">
        <!-- Tab Navigation -->
        <div class="border-b border-zinc-200 dark:border-zinc-700">
            <nav class="flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'details'"
                        type="button"
                        class="py-4 px-1 inline-flex items-center border-b-2 font-medium text-sm whitespace-nowrap transition-colors duration-200"
                        :class="activeTab === 'details'
                            ? 'border-purple-500 text-purple-600 dark:text-purple-400'
                            : 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600'"
                        aria-current="page">
                    <i data-lucide="settings" class="mr-2 h-5 w-5"></i>
                    Team Details
                </button>

                <button @click="activeTab = 'members'"
                        type="button"
                        class="py-4 px-1 inline-flex items-center border-b-2 font-medium text-sm whitespace-nowrap transition-colors duration-200"
                        :class="activeTab === 'members'
                            ? 'border-purple-500 text-purple-600 dark:text-purple-400'
                            : 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600'">
                    <i data-lucide="users" class="mr-2 h-5 w-5"></i>
                    Team Members <span class="ml-1 text-xs rounded-full px-2 py-0.5" :class="activeTab === 'members' ? 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400'" x-text="members.length"></span>
                </button>

                <button @click="activeTab = 'danger'"
                        type="button"
                        class="py-4 px-1 inline-flex items-center border-b-2 font-medium text-sm whitespace-nowrap transition-colors duration-200"
                        :class="activeTab === 'danger'
                            ? 'border-red-500 text-red-600 dark:text-red-400'
                            : 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600'">
                    <i data-lucide="alert-triangle" class="mr-2 h-5 w-5"></i>
                    Danger Zone
                </button>
            </nav>
        </div>

        <!-- Team Details Tab -->
        <div x-show="activeTab === 'details'" x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <form @submit.prevent="saveTeamDetails" class="space-y-6">
                <div class="flex flex-col items-center justify-center sm:flex-row sm:items-start sm:justify-start sm:space-x-6">
                    <div class="relative group w-24 h-24 mb-4 sm:mb-0">
                        <div x-show="!logoPreview && !team.logo_path" class="w-24 h-24 rounded-full flex items-center justify-center text-3xl font-bold text-white bg-gradient-to-br from-blue-500 to-indigo-600 overflow-hidden">
                            <span x-text="team.name ? team.name.charAt(0).toUpperCase() : 'T'"></span>
                        </div>

                        <template x-if="logoPreview">
                            <img :src="logoPreview" class="w-24 h-24 rounded-full object-cover" alt="Team logo preview">
                        </template>

                        <template x-if="!logoPreview && team.logo_path">
                            <img :src="'/storage/' + team.logo_path" class="w-24 h-24 rounded-full object-cover" alt="Team logo">
                        </template>

                        <div class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                            <label for="logo-upload" class="cursor-pointer p-2 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </label>
                            <input id="logo-upload" type="file" class="hidden" accept="image/*" @change="handleLogoUpload">
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Team Logo</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Upload a team logo or let us generate one from your team name.</p>
                        <div class="mt-2 flex space-x-2">
                            <button type="button" class="inline-flex items-center px-3 py-1.5 border border-zinc-300 dark:border-zinc-600 text-xs font-medium rounded-md text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors duration-200" @click="document.getElementById('logo-upload').click()">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12" />
                                </svg>
                                Upload
                            </button>

                            <button x-show="logoPreview || team.logo_path" type="button" class="inline-flex items-center px-3 py-1.5 border border-zinc-300 dark:border-zinc-600 text-xs font-medium rounded-md text-red-600 dark:text-red-400 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors duration-200" @click="removeLogo">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Remove
                            </button>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 max-w-2xl">
                    <div>
                        <label for="team-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Team Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="team-name" x-model="team.name" class="mt-1 block w-full border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm py-2 px-3 text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent sm:text-sm transition-colors duration-200" required placeholder="e.g. Engineering Team">
                        <p x-show="errors.name" x-text="errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                    </div>

                    <div>
                        <label for="team-description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Description
                        </label>
                        <textarea id="team-description" x-model="team.description" rows="3" class="mt-1 block w-full border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm py-2 px-3 text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent sm:text-sm transition-colors duration-200" placeholder="Brief description of your team's purpose"></textarea>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            <span x-text="team.description ? team.description.length : 0"></span>/200 characters
                        </p>
                        <p x-show="errors.description" x-text="errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                    </div>
                </div>

                <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end space-x-3">
                    <a href="{{ route('teams.show', $team->id) }}" class="inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                        Cancel
                    </a>

                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200" :disabled="isSaving">
                        <template x-if="isSaving">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="isSaving ? 'Saving...' : 'Save Changes'"></span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Members Tab -->
        <div x-show="activeTab === 'members'" x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <!-- Add New Member Form -->
            <div class="mb-8 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4">
                <h3 class="text-base font-medium text-zinc-900 dark:text-white mb-4">Invite New Members</h3>

                <form @submit.prevent="sendInvitations" class="space-y-4">
                    <div>
                        <label for="invite-emails" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Email Addresses <span class="text-red-500">*</span>
                        </label>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-2">Enter one or more email addresses separated by commas</p>

                        <div>
                            <div class="flex space-x-3">
                                <div class="flex-grow">
                                    <input type="text" id="invite-emails" x-model="inviteEmails" class="block w-full border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm py-2 px-3 text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent sm:text-sm transition-colors duration-200" placeholder="email@example.com, another@example.com">
                                </div>

                                <div class="w-32">
                                    <select x-model="inviteRole" class="block w-full border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm py-2 px-3 text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent sm:text-sm transition-colors duration-200">
                                        <option value="member">Member</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                            <p x-show="inviteErrors.emails" x-text="inviteErrors.emails" class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200" :disabled="isSendingInvites">
                            <template x-if="isSendingInvites">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span x-text="isSendingInvites ? 'Sending...' : 'Send Invitations'"></span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Members List -->
            <div class="overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium text-zinc-900 dark:text-white">Team Members</h3>
                    <div class="relative">
                        <input type="text" x-model="memberSearch" placeholder="Search members..." class="pl-9 pr-4 py-2 text-sm border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm bg-white dark:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent transition-colors duration-200">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="h-4 w-4 text-zinc-400 dark:text-zinc-500"></i>
                        </div>
                    </div>
                </div>

                <div class="shadow overflow-hidden border border-zinc-200 dark:border-zinc-700 sm:rounded-lg">
                    <template x-if="filteredMembers.length === 0">
                        <div class="py-10 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400" x-text="memberSearch ? 'No members match your search.' : 'No members in this team yet.'"></p>
                        </div>
                    </template>

                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700" x-show="filteredMembers.length > 0">
                        <thead class="bg-zinc-50 dark:bg-zinc-700/30">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    User
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Role
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Joined
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            <template x-for="(member, index) in filteredMembers" :key="member.id">
                                <tr :class="{'bg-zinc-50 dark:bg-zinc-700/20': index % 2 === 0}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full flex items-center justify-center text-white bg-gradient-to-br from-blue-500 to-indigo-600 overflow-hidden">
                                                    <span x-text="member.name.charAt(0).toUpperCase()"></span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-zinc-900 dark:text-white" x-text="member.name"></div>
                                                <div class="text-sm text-zinc-500 dark:text-zinc-400" x-text="member.email"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <template x-if="member.id === userId">
                                            <span class="capitalize inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getRoleBadgeClass(member.pivot.team_role)" x-text="member.pivot.team_role"></span>
                                        </template>
                                        <template x-if="member.id !== userId && isOwner">
                                            <select x-model="member.pivot.team_role" @change="updateMemberRole(member.id, member.pivot.team_role)" class="text-sm border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm bg-white dark:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent transition-colors duration-200">
                                                <option value="member">Member</option>
                                                <option value="admin">Admin</option>
                                                <option value="owner">Owner</option>
                                            </select>
                                        </template>
                                        <template x-if="member.id !== userId && !isOwner">
                                            <span class="capitalize inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getRoleBadgeClass(member.pivot.team_role)" x-text="member.pivot.team_role"></span>
                                        </template>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400" x-text="formatDate(member.pivot.created_at)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <template x-if="member.id !== userId && (isOwner || isAdmin)">
                                            <button @click="confirmRemoveMember(member)" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors duration-200">
                                                Remove
                                            </button>
                                        </template>
                                        <template x-if="member.id === userId">
                                            <span class="text-zinc-400 dark:text-zinc-500">You</span>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Danger Zone Tab -->
        <div x-show="activeTab === 'danger'" x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-6 border border-red-200 dark:border-red-800">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400 dark:text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-red-800 dark:text-red-400">Danger Zone</h3>
                        <p class="mt-2 text-sm text-red-700 dark:text-red-300">
                            Once you delete a team, there is no going back. Please be certain.
                        </p>
                        <div class="mt-4">
                            <button type="button" @click="confirmDeleteTeam" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                            Delete Team
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Team Confirmation Modal -->
<div x-show="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showDeleteModal" @click="showDeleteModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-zinc-500 dark:bg-zinc-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-white" id="modal-title">
                        Delete Team
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Are you sure you want to delete the team "<span class="font-medium text-zinc-800 dark:text-zinc-200" x-text="team.name"></span>"? This action cannot be undone and all team data will be permanently deleted.
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button type="button" @click="deleteTeam" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-zinc-800 sm:col-start-2 sm:text-sm transition-colors duration-200" :disabled="isDeleting">
                    <template x-if="isDeleting">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="isDeleting ? 'Deleting...' : 'Delete'"></span>
                </button>
                <button type="button" @click="showDeleteModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-zinc-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 dark:focus:ring-offset-zinc-800 sm:mt-0 sm:col-start-1 sm:text-sm transition-colors duration-200">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Remove Member Confirmation Modal -->
<div x-show="showRemoveMemberModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showRemoveMemberModal" @click="showRemoveMemberModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-zinc-500 dark:bg-zinc-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showRemoveMemberModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-white" id="modal-title">
                        Remove Team Member
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400" x-show="selectedMember">
                            Are you sure you want to remove <span class="font-medium text-zinc-800 dark:text-zinc-200" x-text="selectedMember?.name"></span> from the team? They will lose access to all team resources.
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button type="button" @click="removeMember" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-zinc-800 sm:col-start-2 sm:text-sm transition-colors duration-200" :disabled="isRemoving">
                    <template x-if="isRemoving">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="isRemoving ? 'Removing...' : 'Remove'"></span>
                </button>
                <button type="button" @click="showRemoveMemberModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-zinc-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 dark:focus:ring-offset-zinc-800 sm:mt-0 sm:col-start-1 sm:text-sm transition-colors duration-200">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div x-show="showToast" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2" class="fixed bottom-4 right-4 z-50" @click="showToast = false" x-cloak>
    <div :class="toastType === 'success' ? 'bg-green-50 dark:bg-green-900/30 text-green-800 dark:text-green-300 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-300 border-red-200 dark:border-red-800'" class="rounded-lg py-3 px-4 border shadow-lg flex items-center">
        <div :class="toastType === 'success' ? 'bg-green-100 dark:bg-green-800 text-green-600 dark:text-green-400' : 'bg-red-100 dark:bg-red-800 text-red-600 dark:text-red-400'" class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center mr-3">
            <template x-if="toastType === 'success'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </template>
            <template x-if="toastType === 'error'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </template>
        </div>
        <div>
            <p class="font-medium text-sm" x-text="toastMessage"></p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function editTeam(teamData) {
        return {
            activeTab: 'details',
            team: teamData.team,
            members: teamData.members,
            originalTeam: JSON.parse(JSON.stringify(teamData.team)),
            errors: {},
            logoPreview: null,
            logoFile: null,
            removeLogoFlag: false,
            isSaving: false,
            hasChanges: false,
            userId: {{ auth()->id() }},
            isOwner: {{ json_encode(auth()->user()->teams()->where('team_id', $team->id)->first()->pivot->team_role === 'owner') }},
            isAdmin: {{ json_encode(in_array(auth()->user()->teams()->where('team_id', $team->id)->first()->pivot->team_role, ['owner', 'admin'])) }},

            // Member management
            memberSearch: '',
            selectedMember: null,
            showRemoveMemberModal: false,
            isRemoving: false,

            // Invitations
            inviteEmails: '',
            inviteRole: 'member',
            inviteErrors: {},
            isSendingInvites: false,

            // Team deletion
            showDeleteModal: false,
            isDeleting: false,

            // Toast notifications
            showToast: false,
            toastMessage: '',
            toastType: 'success',

            init() {
                this.$watch('team', (val) => {
                    this.hasChanges = this.checkForChanges();
                }, { deep: true });

                document.addEventListener('alpine:initialized', () => {
                    lucide.createIcons();
                });
            },

            // Check if form has unsaved changes
            checkForChanges() {
                if (this.logoPreview || this.removeLogoFlag) return true;

                return JSON.stringify(this.team) !== JSON.stringify(this.originalTeam);
            },

            // Logo upload handling
            handleLogoUpload(event) {
                const file = event.target.files[0];
                if (!file) return;

                this.logoFile = file;
                this.removeLogoFlag = false;

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.logoPreview = e.target.result;
                    this.hasChanges = true;
                };
                reader.readAsDataURL(file);
            },

            removeLogo() {
                this.logoPreview = null;
                this.logoFile = null;
                this.removeLogoFlag = true;
                document.getElementById('logo-upload').value = '';
                this.hasChanges = true;
            },

            // Team details save
            async saveTeamDetails() {
                this.errors = {};
                this.isSaving = true;

                try {
                    const formData = new FormData();
                    formData.append('name', this.team.name);
                    formData.append('description', this.team.description || '');
                    formData.append('_method', 'PUT'); // For Laravel method spoofing

                    if (this.logoFile) {
                        formData.append('logo', this.logoFile);
                    }

                    if (this.removeLogoFlag) {
                        formData.append('remove_logo', true);
                    }

                    const response = await fetch(`/teams/${this.team.id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.errors = data.errors || {};
                        this.showToastMessage('Failed to update team details', 'error');
                        return;
                    }

                    // Update local data
                    this.team = data.team;
                    this.originalTeam = JSON.parse(JSON.stringify(data.team));
                    this.logoPreview = null;
                    this.logoFile = null;
                    this.removeLogoFlag = false;
                    this.hasChanges = false;

                    this.showToastMessage('Team details updated successfully', 'success');
                } catch (error) {
                    console.error('Error updating team:', error);
                    this.showToastMessage('An unexpected error occurred', 'error');
                } finally {
                    this.isSaving = false;
                }
            },

            // Member management
            get filteredMembers() {
                if (!this.memberSearch.trim()) return this.members;

                const search = this.memberSearch.toLowerCase();
                return this.members.filter(member =>
                    member.name.toLowerCase().includes(search) ||
                    member.email.toLowerCase().includes(search)
                );
            },

            // Format date for display
            formatDate(dateString) {
                const date = new Date(dateString);
                return new Intl.DateTimeFormat('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                }).format(date);
            },

            // Get appropriate badge color class based on role
            getRoleBadgeClass(role) {
                switch(role) {
                    case 'owner':
                        return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400';
                    case 'admin':
                        return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
                    default:
                        return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
                }
            },

            // Update a member's role
            async updateMemberRole(memberId, newRole) {
                try {
                    const response = await fetch(`/teams/${this.team.id}/members/${memberId}/role`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ role: newRole })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.showToastMessage(data.message || 'Failed to update member role', 'error');
                        return;
                    }

                    this.showToastMessage('Member role updated successfully', 'success');
                } catch (error) {
                    console.error('Error updating member role:', error);
                    this.showToastMessage('An unexpected error occurred', 'error');
                }
            },

            // Confirm member removal modal
            confirmRemoveMember(member) {
                this.selectedMember = member;
                this.showRemoveMemberModal = true;
            },

            // Remove a member
            async removeMember() {
                if (!this.selectedMember) return;

                this.isRemoving = true;

                try {
                    const response = await fetch(`/teams/${this.team.id}/members/${this.selectedMember.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.showToastMessage(data.message || 'Failed to remove member', 'error');
                        return;
                    }

                    // Remove member from local array
                    this.members = this.members.filter(m => m.id !== this.selectedMember.id);
                    this.showRemoveMemberModal = false;
                    this.selectedMember = null;

                    this.showToastMessage('Member removed successfully', 'success');
                } catch (error) {
                    console.error('Error removing member:', error);
                    this.showToastMessage('An unexpected error occurred', 'error');
                } finally {
                    this.isRemoving = false;
                }
            },

            // Invite new members
            async sendInvitations() {
                this.inviteErrors = {};
                this.isSendingInvites = true;

                try {
                    // Parse email list
                    const emails = this.inviteEmails.split(',')
                        .map(email => email.trim())
                        .filter(email => email);

                    if (emails.length === 0) {
                        this.inviteErrors.emails = 'Please enter at least one email address';
                        return;
                    }

                    const response = await fetch(`/teams/${this.team.id}/invitations`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({
                            emails: emails,
                            role: this.inviteRole
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.inviteErrors = data.errors || {};
                        this.showToastMessage(data.message || 'Failed to send invitations', 'error');
                        return;
                    }

                    // Reset form
                    this.inviteEmails = '';
                    this.inviteRole = 'member';

                    this.showToastMessage(data.message || 'Invitations sent successfully', 'success');
                } catch (error) {
                    console.error('Error sending invitations:', error);
                    this.showToastMessage('An unexpected error occurred', 'error');
                } finally {
                    this.isSendingInvites = false;
                }
            },

            // Confirm team deletion
            confirmDeleteTeam() {
                this.showDeleteModal = true;
            },

            // Delete team
            async deleteTeam() {
                this.isDeleting = true;

                try {
                    const response = await fetch(`/teams/${this.team.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.showToastMessage(data.message || 'Failed to delete team', 'error');
                        return;
                    }

                    // Redirect to the teams page
                    window.location.href = data.redirect || '/dashboard/select-team';
                } catch (error) {
                    console.error('Error deleting team:', error);
                    this.showToastMessage('An unexpected error occurred', 'error');
                    this.isDeleting = false;
                    this.showDeleteModal = false;
                }
            },

            // Show toast notification
            showToastMessage(message, type = 'success') {
                this.toastMessage = message;
                this.toastType = type;
                this.showToast = true;

                // Auto-hide after 5 seconds
                setTimeout(() => {
                    this.showToast = false;
                }, 5000);
            }
        };
    }
</script>
@endpush
