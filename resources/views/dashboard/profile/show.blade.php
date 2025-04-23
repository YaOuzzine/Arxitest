@extends('layouts.profile')

@section('profile-title', 'Profile Overview')

@section('profile-content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Name</h3>
                <p class="mt-1 text-lg text-zinc-900 dark:text-white">{{ $user->name }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Email Address</h3>
                <p class="mt-1 text-lg text-zinc-900 dark:text-white">{{ $user->email }}</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    @if($user->hasVerifiedEmail())
                        <span class="inline-flex items-center text-green-600 dark:text-green-400">
                            <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Verified
                        </span>
                    @else
                        <span class="inline-flex items-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="alert-circle" class="w-4 h-4 mr-1"></i> Not verified
                        </span>
                    @endif
                </p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Phone Number</h3>
                @if($user->phone_number)
                    <p class="mt-1 text-lg text-zinc-900 dark:text-white">{{ $user->phone_number }}</p>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        @if($user->phone_verified)
                            <span class="inline-flex items-center text-green-600 dark:text-green-400">
                                <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Verified
                            </span>
                        @else
                            <span class="inline-flex items-center text-amber-600 dark:text-amber-400">
                                <i data-lucide="alert-circle" class="w-4 h-4 mr-1"></i> Not verified
                            </span>
                        @endif
                    </p>
                @else
                    <p class="mt-1 text-zinc-500 dark:text-zinc-400">Not provided</p>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Account Creation</h3>
                <p class="mt-1 text-lg text-zinc-900 dark:text-white">{{ $user->created_at->format('F j, Y') }}</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $user->created_at->diffForHumans() }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Last Profile Update</h3>
                <p class="mt-1 text-lg text-zinc-900 dark:text-white">{{ $user->updated_at->format('F j, Y') }}</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $user->updated_at->diffForHumans() }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Connected Accounts</h3>
                <div class="mt-1 flex flex-wrap gap-2">
                    @if($user->google_id)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                            <i data-lucide="mail" class="w-3 h-3 mr-1"></i> Google
                        </span>
                    @endif

                    @if($user->github_id)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-700/50 dark:text-zinc-300">
                            <i data-lucide="github" class="w-3 h-3 mr-1"></i> GitHub
                        </span>
                    @endif

                    @if($user->microsoft_id)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                            <i data-lucide="microsoft" class="w-3 h-3 mr-1"></i> Microsoft
                        </span>
                    @endif

                    @if(!$user->google_id && !$user->github_id && !$user->microsoft_id)
                        <span class="text-zinc-500 dark:text-zinc-400">No third-party accounts connected</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
        <a href="{{ route('dashboard.profile.edit') }}" class="btn-primary inline-flex items-center">
            <i data-lucide="edit-3" class="w-4 h-4 mr-2"></i> Edit Profile
        </a>

        <a href="{{ route('dashboard.profile.password') }}" class="btn-secondary inline-flex items-center">
            <i data-lucide="key" class="w-4 h-4 mr-2"></i> Change Password
        </a>
    </div>
</div>
@endsection
