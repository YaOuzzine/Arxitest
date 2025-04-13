@extends('layouts.dashboard')

@section('title', 'Create Team')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.select-team') }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
            Teams
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Create</span>
    </li>
@endsection

@section('content')
<div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
    <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
        <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">Create a New Team</h2>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            Teams let you collaborate with others on projects and share resources.
        </p>
    </div>

    <div class="p-6" x-data="createTeam()">
        <form @submit.prevent="submitForm">
            <div class="space-y-6">
                <!-- Team Logo Upload -->
                <div class="flex flex-col items-center justify-center sm:flex-row sm:justify-start sm:space-x-6">
                    <div class="relative group w-24 h-24 mb-4 sm:mb-0">
                        <div
                            class="w-24 h-24 rounded-full flex items-center justify-center text-3xl font-bold text-white bg-gradient-to-br from-blue-500 to-indigo-600 overflow-hidden"
                            x-show="!logoPreview"
                        >
                            <span x-text="teamName ? teamName.charAt(0).toUpperCase() : 'T'"></span>
                        </div>
                        <template x-if="logoPreview">
                            <img :src="logoPreview" class="w-24 h-24 rounded-full object-cover" alt="Team logo preview">
                        </template>
                        <div class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                            <label for="logo-upload" class="cursor-pointer p-2 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </label>
                            <input
                                id="logo-upload"
                                type="file"
                                class="hidden"
                                accept="image/*"
                                @change="handleLogoUpload"
                            >
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Team Logo</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Upload a team logo or let us generate one from your team name.</p>
                        <button
                            type="button"
                            class="mt-2 inline-flex items-center px-3 py-1.5 border border-zinc-300 dark:border-zinc-600 text-xs font-medium rounded-md text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700"
                            @click="document.getElementById('logo-upload').click()"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12" />
                            </svg>
                            Upload
                        </button>
                        <button
                            x-show="logoPreview"
                            type="button"
                            class="mt-2 ml-2 inline-flex items-center px-3 py-1.5 border border-zinc-300 dark:border-zinc-600 text-xs font-medium rounded-md text-red-600 dark:text-red-400 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700"
                            @click="logoPreview = null; document.getElementById('logo-upload').value = ''"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Remove
                        </button>
                    </div>
                </div>

                <!-- Team Details -->
                <div class="space-y-4">
                    <div>
                        <label for="team-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Team Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="team-name"
                            name="team_name"
                            x-model="teamName"
                            class="mt-1 block w-full border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm py-2 px-3 text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:focus:ring-zinc-400 focus:border-transparent sm:text-sm"
                            required
                            placeholder="e.g. Engineering Team"
                        >
                    </div>
                    <div>
                        <label for="team-description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Description
                        </label>
                        <textarea
                            id="team-description"
                            name="team_description"
                            x-model="teamDescription"
                            rows="3"
                            class="mt-1 block w-full border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm py-2 px-3 text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:focus:ring-zinc-400 focus:border-transparent sm:text-sm"
                            placeholder="Brief description of your team's purpose"
                        ></textarea>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            <span x-text="teamDescription.length"></span>/200 characters
                        </p>
                    </div>
                </div>

                <!-- Invite Team Members -->
                <div>
                    <h3 class="text-base font-medium text-zinc-900 dark:text-white mb-3">Invite Team Members</h3>
                    <div class="space-y-4">
                        <div class="flex flex-col sm:flex-row sm:space-x-3">
                            <div class="flex-grow mb-2 sm:mb-0">
                                <label for="invite-email" class="sr-only">Email</label>
                                <input
                                    type="email"
                                    id="invite-email"
                                    x-model="newInviteEmail"
                                    @keydown.enter.prevent="addInvite"
                                    class="block w-full border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm py-2 px-3 text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:focus:ring-zinc-400 focus:border-transparent sm:text-sm"
                                    placeholder="Email address"
                                >
                            </div>
                            <div class="sm:w-1/4 mb-2 sm:mb-0">
                                <label for="invite-role" class="sr-only">Role</label>
                                <select
                                    id="invite-role"
                                    x-model="newInviteRole"
                                    class="block w-full border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm py-2 px-3 text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:focus:ring-zinc-400 focus:border-transparent sm:text-sm"
                                >
                                    <option value="member">Member</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div>
                                <button
                                    type="button"
                                    @click="addInvite"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Add
                                </button>
                            </div>
                        </div>

                        <!-- Invited members list -->
                        <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-md">
                            <div class="py-2 px-4 border-b border-zinc-200 dark:border-zinc-700 text-sm font-medium text-zinc-700 dark:text-zinc-300 flex justify-between items-center">
                                <span>Invites (<span x-text="invites.length"></span>)</span>
                                <button
                                    x-show="invites.length > 0"
                                    type="button"
                                    @click="invites = []"
                                    class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
                                >
                                    Clear all
                                </button>
                            </div>
                            <div class="p-4">
                                <template x-if="invites.length === 0">
                                    <div class="text-center py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                        </svg>
                                        <p>No team members invited yet</p>
                                        <p class="mt-1">Add emails above to invite colleagues to your team</p>
                                    </div>
                                </template>
                                <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    <template x-for="(invite, index) in invites" :key="index">
                                        <li class="py-3 flex justify-between items-center">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-sm font-bold">
                                                    <span x-text="invite.email.charAt(0).toUpperCase()"></span>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-zinc-900 dark:text-white" x-text="invite.email"></p>
                                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 capitalize" x-text="invite.role"></p>
                                                </div>
                                            </div>
                                            <button
                                                type="button"
                                                @click="removeInvite(index)"
                                                class="text-zinc-400 hover:text-red-600 dark:text-zinc-500 dark:hover:text-red-400"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Team members will receive an email invitation to join your team.
                        </p>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="border-t border-zinc-200 dark:border-zinc-700 pt-5 flex justify-between">
                    <a
                        href="{{ route('dashboard.select-team') }}"
                        class="inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="isSubmitting"
                    >
                        <template x-if="isSubmitting">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="isSubmitting ? 'Creating...' : 'Create Team'"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
