@extends('layouts.dashboard')

@section('title', $team->name)

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.teams.index') }}"
            class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
            Teams
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">{{ $team->name }}</span>
    </li>
@endsection

@section('content')
    <div class="h-full" x-data="teamDetails()">
        <!-- Team Header -->
        <div
            class="bg-white dark:bg-zinc-900 rounded-2xl shadow-lg border border-zinc-200/50 dark:border-zinc-700/30 overflow-hidden mb-8">
            <div class="flex flex-col md:flex-row md:items-center p-8">
                <div class="flex-shrink-0 mb-6 md:mb-0 md:mr-8">
                    @if ($team->logo_path)
                        <img src="{{ Storage::url($team->logo_path) }}" alt="{{ $team->name }}"
                            class="h-24 w-24 rounded-xl object-cover shadow-lg ring-2 ring-white/50 dark:ring-zinc-800/50">
                    @else
                        <div
                            class="h-24 w-24 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center shadow-lg">
                            <span class="text-white text-3xl font-bold">{{ substr($team->name, 0, 1) }}</span>
                        </div>
                    @endif
                </div>
                <div class="flex-grow">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between">
                        <div class="mb-4 md:mb-0">
                            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">{{ $team->name }}</h1>
                            <p class="text-zinc-600/90 dark:text-zinc-400 text-lg">
                                {{ $team->description ?: 'No description provided' }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            @if (Auth::user()->can('update', $team))
                                <a href="{{ route('teams.edit', $team->id) }}"
                                    class="inline-flex items-center px-5 py-2.5 rounded-xl bg-white/50 dark:bg-zinc-800/50 border border-zinc-300/50 dark:border-zinc-700/50 hover:border-zinc-400/50 dark:hover:border-zinc-600/50 text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white transition-all duration-200 shadow-sm hover:shadow-md">
                                    <i data-lucide="edit-3" class="mr-2 -ml-1 w-5 h-5"></i>
                                    Edit Team
                                </a>
                            @endif
                            <button @click="showInviteModal = true"
                                class="inline-flex items-center px-5 py-2.5 rounded-xl bg-gradient-to-br from-zinc-800 to-zinc-700 dark:from-zinc-700 dark:to-zinc-600 hover:from-zinc-700 hover:to-zinc-600 dark:hover:from-zinc-600 dark:hover:to-zinc-500 text-white shadow-lg hover:shadow-xl transition-all duration-200">
                                <i data-lucide="user-plus" class="mr-2 -ml-1 w-5 h-5"></i>
                                Invite Members
                            </button>
                        </div>
                    </div>
                </div>
            </div>



            <div class="px-6 py-3 bg-zinc-50 dark:bg-zinc-800 border-t border-zinc-200 dark:border-zinc-700">
                <div class="flex flex-wrap gap-4">
                    <div class="flex items-center">
                        <i data-lucide="users" class="mr-2 w-4 h-4 text-zinc-500 dark:text-zinc-400"></i>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $team->users->count() }} members</span>
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="folder" class="mr-2 w-4 h-4 text-zinc-500 dark:text-zinc-400"></i>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $team->projects->count() }}
                            projects</span>
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="clock" class="mr-2 w-4 h-4 text-zinc-500 dark:text-zinc-400"></i>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Created
                            {{ $team->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Content Tabs -->
        <div x-data="{ activeTab: 'members' }"
            class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <div class="border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex space-x-8 px-6">
                    <button @click="activeTab = 'members'"
                        class="py-4 px-1 relative transition-colors duration-200 font-medium text-sm"
                        :class="activeTab === 'members' ? 'text-zinc-800 dark:text-white' :
                            'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'">
                        Members
                        <span class="absolute bottom-0 left-0 w-full h-0.5 transform transition-transform duration-200"
                            :class="activeTab === 'members' ? 'bg-zinc-800 dark:bg-white scale-x-100' :
                                'bg-transparent scale-x-0'"></span>
                    </button>
                    <button @click="activeTab = 'projects'"
                        class="py-4 px-1 relative transition-colors duration-200 font-medium text-sm"
                        :class="activeTab === 'projects' ? 'text-zinc-800 dark:text-white' :
                            'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'">
                        Projects
                        <span class="absolute bottom-0 left-0 w-full h-0.5 transform transition-transform duration-200"
                            :class="activeTab === 'projects' ? 'bg-zinc-800 dark:bg-white scale-x-100' :
                                'bg-transparent scale-x-0'"></span>
                    </button>
                    <button @click="activeTab = 'settings'"
                        class="py-4 px-1 relative transition-colors duration-200 font-medium text-sm"
                        :class="activeTab === 'settings' ? 'text-zinc-800 dark:text-white' :
                            'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'">
                        Settings
                        <span class="absolute bottom-0 left-0 w-full h-0.5 transform transition-transform duration-200"
                            :class="activeTab === 'settings' ? 'bg-zinc-800 dark:bg-white scale-x-100' :
                                'bg-transparent scale-x-0'"></span>
                    </button>
                </div>
            </div>

            <!-- Members Tab -->
            <div x-show="activeTab === 'members'" x-transition:enter="transition ease-out duration-200" class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Team Members</h3>
                    <div class="relative w-72">
                        <input type="text" placeholder="Search members..." x-model="memberSearch"
                            class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-zinc-200/80 dark:border-zinc-700/50 bg-white/50 dark:bg-zinc-800/30 focus:ring-2 focus:ring-blue-500/50 focus:border-transparent placeholder-zinc-400/80 dark:placeholder-zinc-500/80 transition-all duration-200">
                        <i data-lucide="search"
                            class="absolute left-3 top-5 w-5 h-5 text-zinc-400/80 dark:text-zinc-500/80"></i>
                    </div>
                </div>

                <div class="border border-zinc-200/50 dark:border-zinc-700/30 rounded-xl overflow-hidden shadow-sm">
                    <table class="w-full divide-y divide-zinc-200/50 dark:divide-zinc-700/30">
                        <thead class="bg-zinc-50/50 dark:bg-zinc-800/30">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-zinc-500/90 dark:text-zinc-400/90 uppercase tracking-wider">
                                    Member</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-zinc-500/90 dark:text-zinc-400/90 uppercase tracking-wider">
                                    Role</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-zinc-500/90 dark:text-zinc-400/90 uppercase tracking-wider">
                                    Joined</th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-semibold text-zinc-500/90 dark:text-zinc-400/90 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200/50 dark:divide-zinc-700/30 bg-white dark:bg-zinc-900/20">
                            <template x-for="member in filteredMembers" :key="member.id">
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img :src="member.avatarUrl" alt=""
                                                class="h-10 w-10 rounded-lg object-cover border-2 border-white/50 dark:border-zinc-800/50 shadow-sm">
                                            <div class="ml-4">
                                                <div class="font-medium text-zinc-900/90 dark:text-white/90"
                                                    x-text="member.name"></div>
                                                <div class="text-sm text-zinc-500/80 dark:text-zinc-400/80"
                                                    x-text="member.email"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-green-100/50 text-green-800 dark:bg-green-900/20 dark:text-green-400': member
                                                    .role === 'owner',
                                                'bg-blue-100/50 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400': member
                                                    .role === 'admin',
                                                'bg-zinc-100/50 text-zinc-800 dark:bg-zinc-700/30 dark:text-zinc-300': member
                                                    .role === 'member'
                                            }">
                                            <span x-text="member.role"></span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500/80 dark:text-zinc-400/80"
                                        x-text="member.joined"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-3">
                                            <button x-show="canManageMembers && member.id !== currentUserId"
                                                @click="openEditRoleModal(member)"
                                                class="text-zinc-500/80 hover:text-blue-600 dark:text-zinc-400/80 dark:hover:text-blue-400 transition-colors duration-200">
                                                <i data-lucide="settings" class="w-5 h-5"></i>
                                            </button>
                                            <button x-show="canManageMembers && member.id !== currentUserId"
                                                @click="confirmRemoveMember(member)"
                                                class="text-zinc-500/80 hover:text-red-600 dark:text-zinc-400/80 dark:hover:text-red-400 transition-colors duration-200">
                                                <i data-lucide="user-x" class="w-5 h-5"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>


            <!-- Projects Tab -->
            <div x-show="activeTab === 'projects'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Team Projects</h3>
                    <a href="{{ route('dashboard.projects') }}?create=true"
                        class="btn-primary inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                        <i data-lucide="plus" class="mr-2 -ml-1 w-4 h-4"></i>
                        New Project
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="project in projects" :key="project.id">
                        <div
                            class="card-hover bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            <div class="p-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
                                        :class="project.iconBg">
                                        <i data-lucide="folder" class="w-5 h-5" :class="project.iconColor"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-lg font-medium text-zinc-900 dark:text-white"
                                            x-text="project.name"></h4>
                                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                                            x-text="project.description || 'No description'"></p>
                                    </div>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300">
                                        <span x-text="project.testSuites"></span> Test Suites
                                    </span>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300">
                                        <span x-text="project.testCases"></span> Test Cases
                                    </span>
                                </div>
                            </div>
                            <div
                                class="bg-zinc-50 dark:bg-zinc-700/30 px-6 py-3 border-t border-zinc-200 dark:border-zinc-700">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400"
                                        x-text="'Updated ' + project.updatedAt"></span>
                                    <a :href="project.url"
                                        class="text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white text-sm font-medium">
                                        View Project
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Empty state for no projects -->
                    <div x-show="projects.length === 0"
                        class="col-span-full bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-8 text-center">
                        <div
                            class="mx-auto w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center mb-4">
                            <i data-lucide="folder" class="w-8 h-8 text-zinc-400 dark:text-zinc-500"></i>
                        </div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No projects yet</h3>
                        <p class="text-zinc-500 dark:text-zinc-400 mb-6">Get started by creating your first project</p>
                        <a href="{{ route('dashboard.projects') }}?create=true"
                            class="btn-primary inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                            <i data-lucide="plus" class="mr-2 -ml-1 w-4 h-4"></i>
                            Create First Project
                        </a>
                    </div>
                </div>
            </div>

            <!-- Settings Tab -->
            <div x-show="activeTab === 'settings'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-4">Team Settings</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">Manage team settings and permissions.</p>
                </div>

                <div class="space-y-6">
                    <!-- Team Deletion Section (only for owners) -->
                    @if (Auth::user()->can('update', $team))
                        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6">
                            <h4 class="text-base font-medium text-red-600 dark:text-red-400 mb-4">Danger Zone</h4>
                            <div
                                class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i data-lucide="alert-triangle"
                                            class="h-5 w-5 text-red-600 dark:text-red-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h5 class="text-sm font-medium text-red-800 dark:text-red-300">Delete this team
                                        </h5>
                                        <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                                            Once you delete a team, there is no going back. All projects and data associated
                                            with this team will be permanently deleted.
                                        </p>
                                        <div class="mt-4">
                                            <button @click="confirmDeleteTeam"
                                                class="inline-flex items-center px-3 py-2 border border-red-300 dark:border-red-700 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 dark:text-red-400 bg-white dark:bg-zinc-800 hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-zinc-800">
                                                Delete Team
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Invite Member Modal -->
        <div x-show="showInviteModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div
                class="flex items-center justify-center min-h-screen px-4 text-center backdrop-blur-sm transition-all duration-300">
                <div class="fixed inset-0 bg-zinc-900/50 dark:bg-zinc-900/80 transition-opacity"
                    @click="showInviteModal = false" x-transition:enter="ease-out duration-300"
                    x-transition:leave="ease-in duration-200"></div>

                <div class="inline-block w-full max-w-md p-6 my-8 text-left align-middle transition-all transform bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-zinc-200/50 dark:border-zinc-700/30"
                    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4">

                    <div class="flex items-center space-x-4 mb-6">
                        <div class="p-3 bg-blue-100/50 dark:bg-blue-900/20 rounded-xl">
                            <i data-lucide="user-plus" class="w-6 h-6 text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Invite to Team</h3>
                            <p class="text-sm text-zinc-500/90 dark:text-zinc-400/90 mt-1">
                                Invite collaborators via email addresses
                            </p>
                        </div>
                    </div>

                    <form @submit.prevent="sendInvites">
                        <div class="space-y-5">
                            <div>
                                <label for="invite-emails"
                                    class="block text-sm font-medium text-zinc-700/90 dark:text-zinc-300/90 mb-2">
                                    Email Addresses <span class="text-red-500">*</span>
                                </label>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-2">Enter multiple email addresses
                                    separated by commas or on new lines</p>
                                <textarea id="invite-emails" x-model="inviteEmails" rows="4"
                                    class="w-full px-4 py-3 rounded-xl border border-zinc-200/80 dark:border-zinc-700/50 bg-white/50 dark:bg-zinc-800/30 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/30 placeholder-zinc-400/80 dark:placeholder-zinc-500/80 text-zinc-900/90 dark:text-zinc-100/90 resize-none transition-all duration-200"
                                    placeholder="john@example.com&#10;alice@company.com" required></textarea>
                                <div x-show="inviteError" class="mt-2 text-sm text-red-600 dark:text-red-400"
                                    x-text="inviteError"></div>
                            </div>

                            <div>
                                <label for="invite-role"
                                    class="block text-sm font-medium text-zinc-700/90 dark:text-zinc-300/90 mb-2">
                                    Role
                                </label>
                                <div class="relative">
                                    <select id="invite-role" x-model="inviteRole"
                                        class="w-full px-4 py-3 pr-10 rounded-xl border border-zinc-200/80 dark:border-zinc-700/50 bg-white/50 dark:bg-zinc-800/30 appearance-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/30 text-zinc-900/90 dark:text-zinc-100/90 transition-all duration-200">
                                        <option value="member">Member</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                    <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                        <i data-lucide="chevron-down"
                                            class="w-5 h-5 text-zinc-400/80 dark:text-zinc-500/80"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex space-x-3">
                            <button type="button" @click="showInviteModal = false"
                                class="flex-1 px-5 py-2.5 rounded-xl border border-zinc-200/80 dark:border-zinc-700/50 bg-transparent hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 text-zinc-700/90 dark:text-zinc-300/90 transition-all duration-200">
                                Cancel
                            </button>
                            <button type="submit"
                                class="flex items-center justify-center flex-1 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="inviteLoading">
                                <i data-lucide="loader" x-show="inviteLoading" class="animate-spin mr-2 w-4 h-4"></i>
                                <span x-text="inviteLoading ? 'Sending...' : 'Send Invites'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <!-- Edit Role Modal -->
        <div x-show="showEditRoleModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div class="fixed inset-0 transition-opacity" @click="showEditRoleModal = false">
                    <div class="absolute inset-0 bg-zinc-900 opacity-75 dark:opacity-90"></div>
                </div>

                <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95">
                    <div class="bg-white dark:bg-zinc-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="user-check" class="h-6 w-6 text-blue-600 dark:text-blue-400"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-white">
                                    Change Member Role
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        Update the role for <span x-text="selectedMember.name"
                                            class="font-medium text-zinc-700 dark:text-zinc-300"></span>.
                                    </p>
                                </div>

                                <div class="mt-4">
                                    <label for="member-role"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Role
                                    </label>
                                    <select id="member-role" x-model="selectedMemberRole"
                                        class="mt-1 block w-full border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm py-2 px-3 text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:focus:ring-zinc-400 focus:border-transparent sm:text-sm">
                                        <option value="member">Member</option>
                                        <option value="admin">Admin</option>
                                        <option value="owner">Owner</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="updateMemberRole" type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                            :disabled="roleUpdateLoading">
                            <template x-if="roleUpdateLoading">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </template>
                            <span x-text="roleUpdateLoading ? 'Updating...' : 'Update Role'"></span>
                        </button>
                        <button @click="showEditRoleModal = false" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-zinc-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remove Member Confirmation Modal -->
        <div x-show="showRemoveMemberModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div class="fixed inset-0 transition-opacity" @click="showRemoveMemberModal = false">
                    <div class="absolute inset-0 bg-zinc-900 opacity-75 dark:opacity-90"></div>
                </div>

                <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95">
                    <div class="bg-white dark:bg-zinc-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="user-minus" class="h-6 w-6 text-red-600 dark:text-red-400"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-white">
                                    Remove Team Member
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        Are you sure you want to remove <span x-text="selectedMember.name"
                                            class="font-medium text-zinc-700 dark:text-zinc-300"></span> from this team?
                                        This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="removeMember" type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                            :disabled="removeMemberLoading">
                            <template x-if="removeMemberLoading">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </template>
                            <span x-text="removeMemberLoading ? 'Removing...' : 'Remove Member'"></span>
                        </button>
                        <button @click="showRemoveMemberModal = false" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-zinc-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Team Confirmation Modal -->
        <div x-show="showDeleteTeamModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div class="fixed inset-0 transition-opacity" @click="showDeleteTeamModal = false">
                    <div class="absolute inset-0 bg-zinc-900 opacity-75 dark:opacity-90"></div>
                </div>

                <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95">
                    <div class="bg-white dark:bg-zinc-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600 dark:text-red-400"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-white">
                                    Delete Team
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        Are you absolutely sure you want to delete this team? All of the team's data
                                        including projects, test suites, and scripts will be permanently removed. This
                                        action cannot be undone.
                                    </p>
                                </div>
                                <div class="mt-4">
                                    <div class="flex items-center">
                                        <input id="confirm-delete" name="confirm-delete" type="checkbox"
                                            x-model="deleteConfirmed"
                                            class="h-4 w-4 text-red-600 focus:ring-red-500 border-zinc-300 dark:border-zinc-600 rounded">
                                        <label for="confirm-delete"
                                            class="ml-2 block text-sm text-zinc-900 dark:text-zinc-100">
                                            I understand that this action is irreversible
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-zinc-50 dark:bg-zinc-700/30 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="deleteTeam" type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="!deleteConfirmed || deleteTeamLoading">
                            <template x-if="deleteTeamLoading">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </template>
                            <span x-text="deleteTeamLoading ? 'Deleting...' : 'Delete Team'"></span>
                        </button>
                        <button @click="showDeleteTeamModal = false" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-zinc-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications (for team actions) -->
        <div x-show="notification.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-4" class="fixed bottom-6 right-6 z-50 max-w-sm w-full">
            <div class="p-4 rounded-xl shadow-lg border backdrop-blur-sm"
                :class="{
                    'bg-green-50/80 border-green-200/50 dark:bg-green-900/30 dark:border-green-800/30': notification
                        .type === 'success',
                    'bg-red-50/80 border-red-200/50 dark:bg-red-900/30 dark:border-red-800/30': notification
                        .type === 'error'
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
        document.addEventListener('alpine:init', () => {
            Alpine.data('teamDetails', () => ({
                // UI state
                memberSearch: '',
                showInviteModal: false,
                showEditRoleModal: false,
                showRemoveMemberModal: false,
                showDeleteTeamModal: false,
                inviteEmails: '',
                inviteRole: 'member',
                selectedMember: {},
                selectedMemberRole: '',
                deleteConfirmed: false,

                // API state
                inviteLoading: false,
                inviteError: '',
                roleUpdateLoading: false,
                removeMemberLoading: false,
                deleteTeamLoading: false,

                // Notification system
                notification: {
                    show: false,
                    type: 'success',
                    title: '',
                    message: '',
                    timeout: null
                },

                // Team data
                teamId: '{{ $team->id }}',
                canManageMembers: {{ Auth::user()->can('update', $team) ? 'true' : 'false' }},
                currentUserId: '{{ Auth::id() }}',

                // Mock data for UI demo (replace with API data in prod)
                members: [
                    @foreach ($team->users as $user)
                        {
                            id: '{{ $user->id }}',
                            name: @json($user->name),
                            email: @json($user->email),
                            role: '{{ $user->pivot->team_role }}',
                            joined: '{{ $user->pivot->created_at->diffForHumans() }}',
                            avatarUrl: 'https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random'
                        },
                    @endforeach
                ],

                projects: [
                    @foreach ($team->projects as $project)
                        {
                            id: '{{ $project->id }}',
                            name: @json($project->name),
                            description: @json($project->description),
                            testSuites: {{ $project->testSuites->count() }},
                            testCases: {{ $project->testSuites->flatMap->testCases->count() }},
                            updatedAt: '{{ $project->updated_at->diffForHumans() }}',
                            url: '{{ route('dashboard.project-details', $project->id) }}',
                            iconBg: 'bg-indigo-100 dark:bg-indigo-900/30',
                            iconColor: 'text-indigo-600 dark:text-indigo-400'
                        },
                    @endforeach
                ],

                init() {
                    // Lucide icons
                    this.$nextTick(() => lucide.createIcons());

                    // Auto-hide notification
                    this.$watch('notification.show', value => {
                        if (value) {
                            if (this.notification.timeout) clearTimeout(this.notification
                                .timeout);
                            this.notification.timeout = setTimeout(() => {
                                this.notification.show = false;
                            }, 5000);
                        }
                    });
                },

                // Computed properties
                get filteredMembers() {
                    const search = this.memberSearch.toLowerCase();
                    if (!search) return this.members;
                    return this.members.filter(member =>
                        member.name.toLowerCase().includes(search) ||
                        member.email.toLowerCase().includes(search) ||
                        member.role.toLowerCase().includes(search)
                    );
                },

                // UI Methods
                showNotification(type, title, message) {
                    this.notification.type = type;
                    this.notification.title = title;
                    this.notification.message = message;
                    this.notification.show = true;
                    if (this.notification.timeout) clearTimeout(this.notification.timeout);
                    this.notification.timeout = setTimeout(() => {
                        this.notification.show = false;
                    }, 5000);
                },

                openEditRoleModal(member) {
                    this.selectedMember = member;
                    this.selectedMemberRole = member.role;
                    this.showEditRoleModal = true;
                },
                confirmRemoveMember(member) {
                    this.selectedMember = member;
                    this.showRemoveMemberModal = true;
                },
                confirmDeleteTeam() {
                    this.deleteConfirmed = false;
                    this.showDeleteTeamModal = true;
                },

                // API Methods
                async sendInvites() {
                    if (!this.inviteEmails.trim()) {
                        this.inviteError = 'Please enter at least one email address';
                        return;
                    }

                    this.inviteError = '';
                    this.inviteLoading = true;

                    try {
                        // Process email addresses from textarea (split by commas or newlines)
                        const emails = this.inviteEmails
                            .split(/[,\n]/)
                            .map(email => email.trim())
                            .filter(email => email !== '');

                        // Validate emails
                        const invalidEmails = emails.filter(email => !this.isValidEmail(email));
                        if (invalidEmails.length > 0) {
                            this.inviteError =
                                `Invalid email address${invalidEmails.length > 1 ? 'es' : ''}: ${invalidEmails.join(', ')}`;
                            this.inviteLoading = false;
                            return;
                        }

                        const response = await fetch(`/teams/${this.teamId}/invite`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                emails: emails,
                                role: this.inviteRole
                            })
                        });

                        const result = await response.json();

                        if (response.ok) {
                            this.showInviteModal = false;
                            this.inviteEmails = '';
                            this.showNotification(
                                'success',
                                'Invitations Sent',
                                result.message ||
                                `${emails.length} invitation${emails.length !== 1 ? 's' : ''} sent successfully`
                            );
                        } else {
                            throw new Error(result.message || 'Failed to send invitations');
                        }
                    } catch (error) {
                        this.inviteError = error.message ||
                            'An error occurred while sending invitations';
                        this.showNotification('error', 'Error', this.inviteError);
                    } finally {
                        this.inviteLoading = false;
                    }
                },

                isValidEmail(email) {
                    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    return re.test(String(email).toLowerCase());
                },

                async updateMemberRole() {
                    this.roleUpdateLoading = true;
                    try {
                        const response = await fetch(
                            `/teams/${this.teamId}/members/${this.selectedMember.id}`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    role: this.selectedMemberRole
                                })
                            });
                        const data = await response.json();
                        if (response.ok) {
                            const memberIndex = this.members.findIndex(m => m.id === this
                                .selectedMember.id);
                            if (memberIndex !== -1) this.members[memberIndex].role = this
                                .selectedMemberRole;
                            this.showEditRoleModal = false;
                            this.showNotification('success', 'Role Updated',
                                `${this.selectedMember.name}'s role has been updated to ${this.selectedMemberRole}.`
                                );
                        } else {
                            throw new Error(data.message || 'Failed to update role');
                        }
                    } catch (error) {
                        this.showNotification('error', 'Error', error.message ||
                            'An error occurred while updating role');
                    } finally {
                        this.roleUpdateLoading = false;
                    }
                },

                async removeMember() {
                    this.removeMemberLoading = true;
                    try {
                        const response = await fetch(
                            `/teams/${this.teamId}/members/${this.selectedMember.id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute('content')
                                }
                            });
                        if (response.ok) {
                            this.members = this.members.filter(m => m.id !== this.selectedMember
                            .id);
                            this.showRemoveMemberModal = false;
                            this.showNotification('success', 'Member Removed',
                                `${this.selectedMember.name} has been removed from the team.`);
                        } else {
                            const data = await response.json();
                            throw new Error(data.message || 'Failed to remove member');
                        }
                    } catch (error) {
                        this.showNotification('error', 'Error', error.message ||
                            'An error occurred while removing member');
                    } finally {
                        this.removeMemberLoading = false;
                    }
                },

                async deleteTeam() {
                    if (!this.deleteConfirmed) return;
                    this.deleteTeamLoading = true;
                    try {
                        const response = await fetch(`/teams/${this.teamId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        if (response.ok) {
                            window.location.href = '{{ route('dashboard.select-team') }}';
                        } else {
                            const data = await response.json();
                            throw new Error(data.message || 'Failed to delete team');
                        }
                    } catch (error) {
                        this.showNotification('error', 'Error', error.message ||
                            'An error occurred while deleting the team');
                        this.showDeleteTeamModal = false;
                    } finally {
                        this.deleteTeamLoading = false;
                    }
                }
            }));
        });
    </script>
@endpush
