@extends('layouts.app')

@section('title', $team->name)

@section('content')
<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <!-- Team Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $team->name }}</h1>
            @if($team->description)
                <p class="mt-1 text-gray-600">{{ $team->description }}</p>
            @endif
        </div>

        <div class="mt-4 md:mt-0 flex space-x-3">
            <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                New Project
            </a>

            <a href="{{ route('teams.edit', $team) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                Edit Team
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Left Column: Team Projects -->
        <div class="md:col-span-2">
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">Projects</h2>
                    <a href="{{ route('projects.create') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500 transition-colors duration-150">
                        Add Project
                    </a>
                </div>

                @if(count($projects) > 0)
                    <ul class="divide-y divide-gray-200">
                        @foreach($projects as $project)
                        <li>
                            <a href="{{ route('projects.show', $project) }}" class="block hover:bg-gray-50 transition-colors duration-150">
                                <div class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-900">{{ $project->name }}</h3>
                                            @if(isset($project->description))
                                                <p class="mt-1 text-xs text-gray-500">{{ $project->description }}</p>
                                            @endif
                                        </div>
                                        <div class="ml-2 flex-shrink-0 flex">
                                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <div class="px-6 py-12 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No projects</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new project.</p>
                        <div class="mt-6">
                            <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                New Project
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Team Details and Members -->
        <div class="space-y-6">
            <!-- Subscription Info -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-medium text-gray-900">Subscription</h2>
                </div>
                <div class="px-6 py-4">
                    @if($subscription)
                        <div class="flex items-center justify-between py-2">
                            <div class="text-sm font-medium text-gray-500">Plan</div>
                            <div class="text-sm font-medium text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ ucfirst(str_replace('_', ' ', $subscription->plan_type)) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <div class="text-sm font-medium text-gray-500">Status</div>
                            <div class="text-sm font-medium text-gray-900">
                                @if($subscription->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <div class="text-sm font-medium text-gray-500">Expires</div>
                            <div class="text-sm font-medium text-gray-900">{{ $subscription->end_date->format('M d, Y') }}</div>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <div class="text-sm font-medium text-gray-500">Containers</div>
                            <div class="text-sm font-medium text-gray-900">{{ $subscription->max_parallel_runs }} max parallel</div>
                        </div>
                    @else
                        <div class="py-2 text-center">
                            <p class="text-sm text-gray-500">No active subscription</p>
                            <a href="#" class="mt-2 inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500 transition-colors duration-150">
                                View plans
                                <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Team Invitation -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-medium text-gray-900">Invite Members</h2>
                </div>
                <div class="px-6 py-4">
                    @if(isset($team->settings['invitation_code']))
                        <div class="mb-4">
                            <label for="invitation_code" class="block text-sm font-medium text-gray-700 mb-1">Invitation Code</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input type="text" id="invitation_code" value="{{ $team->settings['invitation_code'] }}" readonly
                                    class="flex-1 block w-full rounded-none rounded-l-md border-gray-300 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                                <button type="button" onclick="copyInvitationCode()" class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 active:bg-gray-200 transition-colors duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Share this code with people you want to invite to your team.</p>

                            @if(isset($team->settings['invitation_generated_at']))
                                <p class="mt-2 text-xs text-gray-500">Generated: {{ \Carbon\Carbon::parse($team->settings['invitation_generated_at'])->diffForHumans() }}</p>
                            @endif
                        </div>

                        <form action="{{ route('teams.generateInviteCode', $team) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                Generate New Code
                            </button>
                        </form>
                    @else
                        <form action="{{ route('teams.generateInviteCode', $team) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                Generate Invitation Code
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Team Members -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">Team Members</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ count($teamMembers) }}
                    </span>
                </div>

                <ul class="divide-y divide-gray-200">
                    @foreach($teamMembers as $member)
                    <li class="px-6 py-4 flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-600">{{ strtoupper(substr($member->name, 0, 2)) }}</span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                            <p class="text-sm text-gray-500">{{ $member->email }}</p>
                        </div>
                        @if($member->pivot->created_at)
                            <div class="ml-auto text-xs text-gray-500">
                                Joined {{ $member->pivot->created_at->diffForHumans() }}
                            </div>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Copied message toast (initially hidden) -->
<div id="copied-toast" class="fixed bottom-4 right-4 z-50 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md hidden">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3">
            <p class="text-sm">Invitation code copied to clipboard!</p>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function copyInvitationCode() {
        const codeInput = document.getElementById('invitation_code');
        codeInput.select();
        document.execCommand('copy');

        // Show toast notification
        const toast = document.getElementById('copied-toast');
        toast.classList.remove('hidden');

        // Hide toast after 3 seconds
        setTimeout(function() {
            toast.classList.add('hidden');
        }, 3000);
    }
</script>
@endpush
