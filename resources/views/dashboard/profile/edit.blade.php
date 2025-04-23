@extends('layouts.profile')

@section('profile-title', 'Edit Profile')

@section('profile-breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Edit</span>
    </li>
@endsection

@section('profile-content')
<form action="{{ route('dashboard.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Profile Picture --}}
        <div class="space-y-4">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Profile Picture</label>
            <div class="flex items-center space-x-6">
                <div class="relative w-24 h-24">
                    <img id="avatar-preview"
                        src="{{ $user->avatar_path ? Storage::url($user->avatar_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF' }}"
                        alt="{{ $user->name }}"
                        class="w-full h-full object-cover rounded-full border-4 border-white dark:border-zinc-700 shadow-md">
                </div>
                <div class="flex-1">
                    <label for="avatar" class="btn-secondary inline-flex items-center cursor-pointer">
                        <i data-lucide="upload" class="w-4 h-4 mr-2"></i> Upload New Image
                        <input id="avatar" name="avatar" type="file" class="sr-only" accept="image/*" onchange="previewAvatar()">
                    </label>
                    <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                        JPG, PNG or GIF. Maximum size 2MB.
                    </p>
                    @error('avatar')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Basic Info --}}
        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Full Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                @error('email')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                @if(!$user->hasVerifiedEmail())
                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400 flex items-center">
                        <i data-lucide="alert-circle" class="w-3 h-3 mr-1"></i> Email not verified.
                        <a href="#" class="ml-1 underline">Resend verification</a>
                    </p>
                @endif
            </div>

            <div>
                <label for="phone_number" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Phone Number</label>
                <input type="tel" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}"
                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                @error('phone_number')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                @if($user->phone_number && !$user->phone_verified)
                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400 flex items-center">
                        <i data-lucide="alert-circle" class="w-3 h-3 mr-1"></i> Phone not verified.
                        <a href="#" class="ml-1 underline">Verify now</a>
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="flex justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
        <a href="{{ route('dashboard.profile.show') }}" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary">Save Changes</button>
    </div>
</form>

@push('scripts')
<script>
    function previewAvatar() {
        const file = document.getElementById('avatar').files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatar-preview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    }
</script>
@endpush
@endsection
