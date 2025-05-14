@extends('layouts.dashboard')

@section('title', 'Create Team')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.teams.index') }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
            Teams
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Create Team</span>
    </li>
@endsection

@section('content')
<div class="max-w-3xl mx-auto" x-data="createTeamForm()">
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-zinc-200/50 dark:border-zinc-700/50 overflow-hidden transition-all duration-300 hover:shadow-xl">
        <div class="p-6 border-b border-zinc-200/60 dark:border-zinc-700/50 bg-gradient-to-r from-zinc-50/50 to-white/50 dark:from-zinc-900/30 dark:to-zinc-800/30">
            <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Create New Team</h2>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Collaborate with others on projects and share resources.
            </p>
        </div>

        <form @submit.prevent="submitForm" class="p-6 space-y-8">
            <!-- Team Logo Section -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                <div class="flex-shrink-0">
                    <div class="relative group cursor-pointer">
                        <div
                            x-show="!logoPreview"
                            class="h-24 w-24 rounded-full flex items-center justify-center text-3xl font-bold bg-zinc-100 dark:bg-zinc-700/80 text-zinc-800 dark:text-zinc-200 transition-all duration-300 group-hover:ring-4 group-hover:ring-zinc-200/50 dark:group-hover:ring-zinc-600/30"
                        >
                            <span x-text="teamName ? teamName.charAt(0).toUpperCase() : 'T'" class="transition-all duration-200"></span>
                        </div>
                        <template x-if="logoPreview">
                            <img :src="logoPreview" class="h-24 w-24 rounded-full object-cover border-2 border-white/50 shadow-lg transition-all duration-300 group-hover:scale-105">
                        </template>
                        <div
                            @click="$refs.logoInput.click()"
                            class="absolute left-8 flex items-center justify-center bg-black/40 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 backdrop-blur-sm"
                        >
                            <i data-lucide="camera" class="h-8 w-8 text-white/90 transform transition-transform duration-300 hover:scale-110"></i>
                        </div>
                        <input
                            x-ref="logoInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="handleLogoUpload"
                        >
                    </div>
                </div>

                <div class="flex-1">
                    <label for="team-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                        Team Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="team-name"
                        x-model="teamName"
                        class="w-full px-4 py-2.5 rounded-lg border border-zinc-300/80 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-800/50 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400/80 focus:ring-2 focus:ring-zinc-500 focus:border-transparent transition-all duration-200 shadow-sm"
                        placeholder="Enter team name"
                        required
                    >
                </div>
            </div>

            <!-- Updated textarea styling -->
            <div>
                <label for="team-description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                    Description
                </label>
                <textarea
                    id="team-description"
                    x-model="teamDescription"
                    rows="3"
                    class="w-full px-4 py-2.5 rounded-lg border border-zinc-300/80 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-800/50 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400/80 focus:ring-2 focus:ring-zinc-500 focus:border-transparent transition-all duration-200 shadow-sm"
                    placeholder="Brief description of your team's purpose"
                ></textarea>
            </div>

            <!-- Invite Team Members Section -->
            <div class="border-t border-zinc-200/50 dark:border-zinc-700/50 pt-6">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Invite Members</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">
                    Add collaborators by email and set their permissions
                </p>

                <div class="flex flex-col sm:flex-row gap-3 group">
                    <div class="flex-1 relative">
                        <input
                            type="email"
                            id="invite-email"
                            x-model="newInviteEmail"
                            @keydown.enter.prevent="addInvite"
                            class="w-full px-4 py-2.5 rounded-lg border border-zinc-300/80 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-800/50 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400/80 focus:ring-2 focus:ring-zinc-500 focus:border-transparent transition-all duration-200 shadow-sm peer"
                            placeholder="name@example.com"
                        >
                        <div class="absolute right-3 flex items-center pr-3 pointer-events-none">
                            <i data-lucide="at-sign" class="h-4 w-4 text-zinc-400/80"></i>
                        </div>
                    </div>

                    <select
                        id="invite-role"
                        x-model="newInviteRole"
                        class="py-2.5 pl-3 pr-8 rounded-lg border border-zinc-300/80 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-800/50 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-zinc-500 focus:border-transparent appearance-none transition-all duration-200 shadow-sm bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9ImN1cnJlbnRDb2xvciIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiPjxwb2x5bGluZSBwb2ludHM9IjYgOSAxMiAxNSAxOCA5Ij48L3BvbHlsaW5lPjwvc3ZnPg==')] bg-no-repeat bg-[right_0.5rem_center] bg-[length:1.2em]"
                    >
                        <option value="member">Member</option>
                        <option value="admin">Admin</option>
                    </select>

                    <button
                        type="button"
                        @click="addInvite"
                        class="flex items-center justify-center px-5 py-2.5 bg-zinc-800/90 hover:bg-zinc-700/90 dark:bg-zinc-700/90 dark:hover:bg-zinc-600/90 text-white rounded-lg font-medium transition-all duration-300 transform hover:scale-[1.02] active:scale-95 shadow-md hover:shadow-zinc-400/20 dark:hover:shadow-zinc-900/40"
                    >
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        Add
                    </button>
                </div>

                <!-- Invited Members List -->
                <div class="mt-6 space-y-3" x-cloak>
                    <template x-if="invites.length > 0">
                        <div class="rounded-xl border border-zinc-200/60 dark:border-zinc-700/50 bg-white/30 dark:bg-zinc-800/30 backdrop-blur-sm shadow-sm overflow-hidden">
                            <div class="px-4 py-3 bg-zinc-50/50 dark:bg-zinc-800/20 border-b border-zinc-200/50 dark:border-zinc-700/50 flex items-center justify-between">
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Pending Invites (<span x-text="invites.length"></span>)
                                </span>
                                <button
                                    @click="invites = []"
                                    class="text-xs text-zinc-500 hover:text-red-500 dark:text-zinc-400 dark:hover:text-red-400 transition-colors"
                                >
                                    Clear All
                                </button>
                            </div>

                            <ul class="divide-y divide-zinc-200/50 dark:divide-zinc-700/50 animate-slide-in">
                                <template x-for="(invite, index) in invites" :key="index">
                                    <li class="px-4 py-3 flex items-center justify-between group hover:bg-zinc-50/30 dark:hover:bg-zinc-800/20 transition-colors">
                                        <div class="flex items-center space-x-3">
                                            <div class="h-9 w-9 rounded-full bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-700 dark:to-zinc-800 flex items-center justify-center text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                                <span x-text="invite.email.charAt(0).toUpperCase()"></span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-zinc-900 dark:text-white" x-text="invite.email"></p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400 capitalize" x-text="invite.role"></p>
                                            </div>
                                        </div>
                                        <button
                                            @click="removeInvite(index)"
                                            class="opacity-0 group-hover:opacity-100 text-zinc-400 hover:text-red-500 dark:text-zinc-500 dark:hover:text-red-400 transition-opacity duration-200"
                                        >
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <template x-if="invites.length === 0">
                        <div class="border-2 border-dashed border-zinc-300/80 dark:border-zinc-600/50 rounded-xl p-6 text-center hover:border-zinc-400/50 dark:hover:border-zinc-500/50 transition-all duration-300">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100/80 dark:bg-zinc-800/50">
                                <i data-lucide="user-plus" class="h-5 w-5 text-zinc-400/80"></i>
                            </div>
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-white mb-1">No invites yet</h4>
                            <p class="text-sm text-zinc-500/90 dark:text-zinc-400/80">Start adding team members above</p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6 flex items-center justify-end gap-3">
                <span
                    x-show="isSubmitting"
                    class="text-sm text-zinc-500 dark:text-zinc-400 mr-auto"
                >
                    Creating team...
                </span>
                <div x-show="errorMessage" class="mr-auto text-sm text-red-500" x-text="errorMessage"></div>

                <a
                    href="{{ route('dashboard.select-team') }}"
                    class="inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800"
                >
                    Cancel
                </a>
                <button
                type="submit"
                class="w-full py-3 px-6 bg-gradient-to-r from-zinc-800 to-zinc-700 dark:from-zinc-700 dark:to-zinc-600 text-white rounded-lg font-medium hover:from-zinc-700 hover:to-zinc-600 dark:hover:from-zinc-600 dark:hover:to-zinc-500 transition-all duration-300 transform hover:scale-[1.02] shadow-md hover:shadow-zinc-400/20 dark:hover:shadow-zinc-900/40 disabled:opacity-50"
                :disabled="isSubmitting || !teamName"
            >
                Create Team
            </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function createTeamForm() {
        return {
            teamName: '',
            teamDescription: '',
            logoPreview: null,
            invites: [],
            newInviteEmail: '',
            newInviteRole: 'member',
            isSubmitting: false,
            errorMessage: '',

            handleLogoUpload(event) {
                const file = event.target.files[0];
                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    this.errorMessage = 'Please select an image file';
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.logoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            },

            addInvite() {
                if (!this.newInviteEmail) return;

                // Basic email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(this.newInviteEmail)) {
                    this.errorMessage = 'Please enter a valid email address';
                    return;
                }

                // Check for duplicates
                if (this.invites.some(invite => invite.email === this.newInviteEmail)) {
                    this.errorMessage = 'This email has already been invited';
                    return;
                }

                this.errorMessage = '';
                this.invites.push({
                    email: this.newInviteEmail,
                    role: this.newInviteRole
                });

                this.newInviteEmail = '';
                this.newInviteRole = 'member';
            },

            removeInvite(index) {
                this.invites.splice(index, 1);
            },

            async submitForm() {
                if (!this.teamName) {
                    this.errorMessage = 'Team name is required';
                    return;
                }

                this.isSubmitting = true;
                this.errorMessage = '';

                try {
                    // Prepare form data
                    const formData = new FormData();
                    formData.append('name', this.teamName);
                    formData.append('description', this.teamDescription);

                    if (this.$refs.logoInput.files[0]) {
                        formData.append('logo', this.$refs.logoInput.files[0]);
                    }

                    if (this.invites.length > 0) {
                        formData.append('invites', JSON.stringify(this.invites));
                    }

                    // Add CSRF token
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                    // Submit form data (you'll need to add the endpoint in your routes)
                    const response = await fetch('{{ route("teams.store") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Failed to create team');
                    }

                    // Redirect to the new team page or dashboard
                    window.location.href = result.redirect || '{{ route("dashboard") }}';

                } catch (error) {
                    this.errorMessage = error.message || 'An error occurred while creating the team';
                    this.isSubmitting = false;
                }
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
    });
</script>
@endpush
@endsection
