@extends('layouts.app')

@section('title', 'My Teams')

@section('content')
<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">My Teams</h1>

        <a href="{{ route('teams.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Create or Join Team
        </a>
    </div>

    @if(count($teams) > 0)
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <ul class="divide-y divide-gray-200">
                @foreach($teams as $team)
                <li>
                    <a href="{{ route('teams.show', $team) }}" class="block hover:bg-gray-50 transition-colors duration-150">
                        <div class="p-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 bg-blue-100 rounded-md p-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h2 class="text-lg font-medium text-gray-900">{{ $team->name }}</h2>
                                        @if($team->description)
                                            <p class="mt-1 text-sm text-gray-600">{{ $team->description }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="ml-4 flex-shrink-0 flex items-center">
                                    <div class="hidden md:flex flex-col mr-8 text-right">
                                        <span class="text-sm font-medium text-gray-500">Projects</span>
                                        <span class="mt-1 text-lg font-semibold text-gray-900">{{ $team->projects->count() }}</span>
                                    </div>
                                    <div class="hidden md:flex flex-col mr-8 text-right">
                                        <span class="text-sm font-medium text-gray-500">Members</span>
                                        <span class="mt-1 text-lg font-semibold text-gray-900">{{ $team->users->count() }}</span>
                                    </div>
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
        </div>
    @else
        <div class="bg-white shadow-sm rounded-lg p-6 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">No teams yet</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new team or joining an existing one.</p>
            <div class="mt-6">
                <a href="{{ route('teams.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Create or Join Team
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
