@extends('layouts.auth')

@section('title', 'Complete Registration')

@section('header-actions')
    <a href="{{ route('login') }}" class="text-sm font-medium hover:underline transition duration-150">
        Back to Login
    </a>
@endsection

@section('content')
<div class="flex flex-col items-center">
    <div class="w-full max-w-md">
        <div class="auth-card bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-sm overflow-hidden">
            <div class="p-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold mb-2">Almost There!</h1>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Just set your username and password to finish creating your account.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 p-4 rounded-md mb-6">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('dashboard') }}" class="auth-form space-y-6" x-data="{ processing: false }">
                    @csrf
                    <input type="hidden" name="verified_email" value="{{ session('verified_email') }}">

                    <div>
                        <label for="username" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Username
                        </label>
                        <input id="username" name="username" type="text" required
                               class="auth-input appearance-none block w-full px-3 py-3 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm placeholder-zinc-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:focus:ring-zinc-400 focus:border-transparent transition duration-150"
                               value="{{ old('username') }}"
                               placeholder="JohnDoe">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Password
                        </label>
                        <div class="relative">
                            <input id="password" name="password" type="password" autocomplete="new-password" required
                                   class="auth-input appearance-none block w-full px-3 py-3 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm placeholder-zinc-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:focus:ring-zinc-400 focus:border-transparent transition duration-150"
                                   placeholder="••••••••">
                            <button type="button"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 focus:outline-none"
                                    onclick="togglePasswordVisibility('password')">
                                <span id="password-toggle-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="w-5 h-5">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <input id="terms" name="terms" type="checkbox" required
                               class="h-4 w-4 mt-1 text-zinc-800 dark:text-zinc-200 border-zinc-300 dark:border-zinc-600 rounded focus:ring-zinc-500 dark:focus:ring-zinc-400 transition duration-150">
                        <label for="terms" class="ml-2 block text-sm text-zinc-700 dark:text-zinc-300">
                            I agree to the <a href="#" class="text-zinc-800 dark:text-zinc-100 font-medium hover:underline">Terms of Service</a> and <a href="#" class="text-zinc-800 dark:text-zinc-100 font-medium hover:underline">Privacy Policy</a>
                        </label>
                    </div>

                    <div>
                        <button type="submit"
                                class="btn-auth w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-900 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition duration-150"
                                x-bind:disabled="processing"
                                x-on:click="processing = true">
                            <span x-show="!processing">Complete Registration</span>
                            <span x-show="processing" x-cloak class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="px-8 py-4 bg-zinc-50 dark:bg-zinc-700/20 border-t border-zinc-200 dark:border-zinc-700 text-sm text-center">
                <span class="text-zinc-600 dark:text-zinc-400">Ready to continue to</span>
                <span class="font-medium text-zinc-800 dark:text-zinc-200">
                    {{ session('verified_email', 'your account') }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/auth/form-animations.js', 'resources/js/auth/password-strength.js'])
@endpush
