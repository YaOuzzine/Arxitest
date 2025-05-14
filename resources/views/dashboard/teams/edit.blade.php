@extends('layouts.dashboard')

@section('title', 'Edit Team: ' . $team->name)

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
        <a href="{{ route('teams.show', $team->id) }}"
            class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
            {{ $team->name }}
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Edit</span>
    </li>
@endsection

@section('content')
    <div class="h-full" x-data="{
        teamName: {{ json_encode(old('name', $team->name)) }},
        teamDescription: {{ json_encode(old('description', $team->description ?? '')) }},
        removeLogo: false,
        hasLogo: {{ $team->logo_path ? 'true' : 'false' }},
        isSubmitting: false,
        showNotification: false,
        notificationType: 'success',
        notificationMessage: '',
        submitForm() {
            if (!this.teamName.trim()) {
                this.showNotificationMessage('error', 'Team name is required');
                return;
            }

            this.isSubmitting = true;
            document.getElementById('team-form').submit();
        },
        showNotificationMessage(type, message) {
            this.notificationType = type;
            this.notificationMessage = message;
            this.showNotification = true;

            // Auto-hide after 5 seconds
            setTimeout(() => {
                this.showNotification = false;
            }, 5000);
        },
        hideNotification() {
            this.showNotification = false;
        }
    }" x-init="$nextTick(() => {
        @if(session('success'))
        showNotificationMessage('success', '{{ session('success') }}');
        @endif

        @if(session('error'))
        showNotificationMessage('error', '{{ session('error') }}');
        @endif

        @if($errors->any())
        showNotificationMessage('error', 'There were errors in your submission. Please check the form.');
        @endif
    })">
        <!-- Animated Header -->
        <div class="mb-6 transform transition-all duration-300 ease-out" x-data="{ scrollY: 0 }"
            x-on:scroll.window="scrollY = window.scrollY" :class="scrollY > 50 ? 'opacity-90 scale-[0.99]' : ''">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="space-y-1">
                    <h1
                        class="text-3xl font-bold text-zinc-900 dark:text-white bg-gradient-to-r from-zinc-900 dark:from-zinc-100 to-zinc-600 dark:to-zinc-400 bg-clip-text text-transparent animate-fade-in-down">
                        Edit Team: <span
                            class="bg-gradient-to-r from-blue-600 to-indigo-400 dark:from-blue-400 dark:to-indigo-300 bg-clip-text text-transparent">{{ $team->name }}</span>
                    </h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 transition-opacity duration-300">
                        Update your team details, logo, and settings
                    </p>
                </div>
                <div>
                    <a href="{{ route('teams.show', $team->id) }}"
                        class="group inline-flex items-center px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-200 bg-white/70 dark:bg-zinc-800/50 hover:bg-white dark:hover:bg-zinc-700/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                        <i data-lucide="arrow-left"
                            class="mr-2 -ml-1 w-4 h-4 transition-transform group-hover:-translate-x-1"></i>
                        Back to Team
                    </a>
                </div>
            </div>
        </div>

        <!-- Floating Notification -->
        <div x-show="showNotification" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2"
            class="fixed bottom-6 right-6 z-50 max-w-sm w-full shadow-lg border rounded-xl p-4"
            :class="{
                'bg-green-50/80 border-green-200/50 dark:bg-green-900/30 dark:border-green-800/30': notificationType === 'success',
                'bg-red-50/80 border-red-200/50 dark:bg-red-900/30 dark:border-red-800/30': notificationType === 'error'
            }">
            <div class="flex items-start">
                <div x-show="notificationType === 'success'"
                    class="flex-shrink-0 w-5 h-5 mr-3 text-green-600 dark:text-green-400">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                </div>
                <div x-show="notificationType === 'error'"
                    class="flex-shrink-0 w-5 h-5 mr-3 text-red-600 dark:text-red-400">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-medium mb-1"
                        :class="{
                            'text-green-800 dark:text-green-200': notificationType === 'success',
                            'text-red-800 dark:text-red-200': notificationType === 'error'
                        }">
                        <span x-show="notificationType === 'success'">Success</span>
                        <span x-show="notificationType === 'error'">Error</span>
                    </h4>
                    <p class="text-sm"
                        :class="{
                            'text-green-700/90 dark:text-green-300/90': notificationType === 'success',
                            'text-red-700/90 dark:text-red-300/90': notificationType === 'error'
                        }"
                        x-text="notificationMessage"></p>
                </div>
                <button @click="hideNotification"
                    class="ml-4 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Glassmorphism Form Container -->
        <div
            class="bg-white/70 dark:bg-zinc-800/50 rounded-2xl shadow-2xl border border-zinc-200/50 dark:border-zinc-700/30 backdrop-blur-lg transition-all duration-300 hover:shadow-2xl">
            <div class="p-8">
                <form id="team-form" method="POST" action="{{ route('teams.update', $team->id) }}" enctype="multipart/form-data" @submit.prevent="submitForm()">
                    @csrf
                    @method('PUT')

                    <!-- Team Details Section -->
                    <div class="space-y-8">
                        <div class="animate-fade-in-left">
                            <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 mb-2">Team Details</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Basic information about your team
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                            <!-- Team Name with Floating Label -->
                            <div class="sm:col-span-4 relative animate-fade-in-up delay-100">
                                <div class="relative">
                                    <input type="text" name="name" id="name" x-model="teamName"
                                        class="peer h-12 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 pl-4 pr-12 text-zinc-700 dark:text-zinc-200 placeholder-transparent focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300"
                                        placeholder="Team Name" required value="{{ old('name', $team->name) }}">
                                    <label for="name"
                                        class="absolute left-4 -top-2.5 px-1 bg-zinc-100/50 dark:bg-zinc-800/50 text-sm text-zinc-600 dark:text-zinc-400 transition-all duration-300 peer-placeholder-shown:top-3 peer-placeholder-shown:text-base peer-placeholder-shown:text-zinc-400 peer-focus:-top-2.5 peer-focus:text-sm peer-focus:text-zinc-600 dark:peer-focus:text-zinc-300">
                                        Team Name <span class="text-red-400">*</span>
                                    </label>
                                    @error('name')
                                        <div class="absolute right-4 top-3">
                                            <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 animate-pulse"></i>
                                        </div>
                                    @enderror
                                </div>
                                @error('name')
                                    <p class="mt-2 text-sm text-red-500 animate-fade-in">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Team Description -->
                            <div class="sm:col-span-6 animate-fade-in-up delay-200">
                                <div class="relative">
                                    <textarea id="description" name="description" rows="3" x-model="teamDescription"
                                        class="peer h-32 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 p-4 text-zinc-700 dark:text-zinc-200 placeholder-transparent focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300 resize-none"
                                        placeholder="A brief description of your team">{{ old('description', $team->description ?? '') }}</textarea>
                                    <label for="description"
                                        class="absolute left-4 -top-2.5 px-1 bg-zinc-100/50 dark:bg-zinc-800/50 text-sm text-zinc-600 dark:text-zinc-400 transition-all duration-300 peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-zinc-400 peer-focus:-top-2.5 peer-focus:text-sm peer-focus:text-zinc-600 dark:peer-focus:text-zinc-300">
                                        Description
                                    </label>
                                </div>
                                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                                    Brief description of your team and its purpose
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Gradient Divider -->
                    <div
                        class="my-8 h-px bg-gradient-to-r from-transparent via-zinc-300/70 dark:via-zinc-600/50 to-transparent animate-scale-in-x">
                    </div>

                    <!-- Team Logo Section -->
                    <div class="space-y-8">
                        <div class="animate-fade-in-left delay-300">
                            <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 mb-2">Team Logo</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Upload a logo for your team
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6 animate-fade-in-up delay-400">
                            <div class="sm:col-span-6">
                                <div class="flex items-start space-x-6">
                                    <!-- Current Logo Preview -->
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-32 h-32 rounded-xl overflow-hidden border-2 border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                                            @if ($team->logo_path)
                                                <img src="{{ Storage::url($team->logo_path) }}"
                                                    alt="{{ $team->name }} logo" class="w-full h-full object-cover"
                                                    x-show="!removeLogo">
                                                <div class="w-full h-full flex items-center justify-center text-zinc-400 dark:text-zinc-500"
                                                    x-show="removeLogo">
                                                    <i data-lucide="image-off" class="w-10 h-10"></i>
                                                </div>
                                            @else
                                                <div
                                                    class="w-full h-full flex items-center justify-center text-zinc-400 dark:text-zinc-500">
                                                    <i data-lucide="users" class="w-10 h-10"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Logo Upload Controls -->
                                    <div class="flex-1">
                                        <label for="logo"
                                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                            Team Logo
                                        </label>
                                        <input type="file" name="logo" id="logo"
                                            class="border border-zinc-300 dark:border-zinc-600 focus:ring-indigo-500 focus:border-indigo-500 block w-full rounded-md text-zinc-700 dark:text-zinc-300 dark:bg-zinc-800 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800/40">
                                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                            Upload a square image for best results. JPG, PNG, or GIF format (max 2MB).
                                        </p>

                                        @if ($team->logo_path)
                                            <div class="mt-4 flex items-center">
                                                <input type="checkbox" name="remove_logo" id="remove_logo"
                                                    value="1" x-model="removeLogo"
                                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-zinc-300 rounded">
                                                <label for="remove_logo"
                                                    class="ml-2 block text-sm text-red-600 dark:text-red-400">
                                                    Remove current logo
                                                </label>
                                            </div>
                                        @endif

                                        @error('logo')
                                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Animated Submit Button -->
                    <div class="mt-12 flex justify-end space-x-4 animate-fade-in-up delay-600">
                        <button type="button" @click="window.history.back()"
                            class="px-6 py-2.5 text-zinc-700 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-white bg-zinc-100/70 dark:bg-zinc-700/50 rounded-xl hover:bg-zinc-200/50 dark:hover:bg-zinc-600/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md transform hover:-translate-y-0.5">
                            Cancel
                        </button>
                        <button type="submit" :disabled="isSubmitting || !teamName.trim()"
                            class="relative px-8 py-2.5 text-white bg-gradient-to-r from-indigo-600 to-indigo-500 dark:from-indigo-600 dark:to-indigo-500 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                            <span class="relative z-10 flex items-center">
                                <span x-show="!isSubmitting" class="flex items-center">
                                    Save Changes
                                    <i data-lucide="save" class="w-4 h-4 ml-2"></i>
                                </span>
                                <span x-show="isSubmitting" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Saving...
                                </span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        @keyframes fade-in-down {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fade-in-left {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scale-in-x {
            from {
                transform: scaleX(0);
            }

            to {
                transform: scaleX(1);
            }
        }

        .animate-fade-in-down {
            animation: fade-in-down 0.6s ease-out;
        }

        .animate-fade-in-left {
            animation: fade-in-left 0.6s ease-out;
        }

        .animate-fade-in-up {
            animation: fade-in-up 0.6s ease-out;
        }

        .animate-scale-in-x {
            animation: scale-in-x 0.6s ease-out;
        }

        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        .delay-400 {
            animation-delay: 0.4s;
        }

        .delay-500 {
            animation-delay: 0.5s;
        }

        .delay-600 {
            animation-delay: 0.6s;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
@endpush
