@extends('layouts.app')

@section('title', 'Create or Join a Team')

@section('content')
<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Create or Join a Team</h1>
        <p class="mt-2 text-gray-600">Teams allow you to collaborate with others on test automation projects.</p>
    </div>

    <div class="md:grid md:grid-cols-2 md:gap-8">
        <!-- Create Team Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-8 md:mb-0">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-blue-100 rounded-full p-2 mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900">Create a New Team</h2>
                </div>

                <p class="mb-4 text-gray-600">Start fresh with a new team and invite others to collaborate.</p>

                @if ($errors->has('name') || $errors->has('description'))
                <div class="bg-red-50 text-red-700 p-3 rounded-md mb-4">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->get('name') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        @foreach ($errors->get('description') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('teams.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Team Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            placeholder="Enter team name" required>
                        <p class="mt-1 text-xs text-gray-500">Choose a clear, recognizable name for your team.</p>
                    </div>

                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                        <textarea name="description" id="description" rows="3"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            placeholder="Describe the purpose of your team">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                            Create Team
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Join Team Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-green-100 rounded-full p-2 mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900">Join an Existing Team</h2>
                </div>

                <p class="mb-4 text-gray-600">Join a team using an invitation code from a team member.</p>

                @if ($errors->has('invitation_code'))
                <div class="bg-red-50 text-red-700 p-3 rounded-md mb-4">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->get('invitation_code') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('teams.join') }}" method="POST">
                    @csrf

                    <div class="mb-6">
                        <label for="invitation_code" class="block text-sm font-medium text-gray-700 mb-1">Invitation Code</label>
                        <div class="relative">
                            <input type="text" name="invitation_code" id="invitation_code" value="{{ old('invitation_code') }}"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 uppercase"
                                placeholder="Enter 8-character code" maxlength="8" required>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Enter the 8-character invitation code provided by a team member.</p>
                    </div>

                    <div>
                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150">
                            Join Team
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Current Teams Section -->
    @if(count($userTeams) > 0)
    <div class="mt-12">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Your Current Teams</h2>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="divide-y divide-gray-200">
                @foreach($userTeams as $team)
                <a href="{{ route('teams.show', $team) }}" class="block hover:bg-gray-50 transition-colors duration-150">
                    <div class="p-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ $team->name }}</h3>
                                @if($team->description)
                                <p class="mt-1 text-sm text-gray-600 line-clamp-1">{{ $team->description }}</p>
                                @endif
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <div class="flex items-center text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $team->projects->count() }} {{ Str::plural('Project', $team->projects->count()) }}
                                    </span>
                                    <svg class="ml-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    // Script to uppercase invitation code input
    document.addEventListener('DOMContentLoaded', function() {
        const invitationCodeInput = document.getElementById('invitation_code');

        if (invitationCodeInput) {
            invitationCodeInput.addEventListener('input', function(e) {
                this.value = this.value.toUpperCase();
            });
        }
    });
</script>
@endpush
