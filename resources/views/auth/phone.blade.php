@extends('layouts.auth')

@section('title', 'Phone Authentication')

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
                    <h1 class="text-2xl font-bold mb-2">Phone Authentication</h1>
                    <p class="text-zinc-600 dark:text-zinc-400">Enter your phone number to receive a verification code</p>
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

                <form method="POST" action="{{ route('auth.phone.send') }}" class="auth-form space-y-6" x-data="{ processing: false }">
                    @csrf

                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Phone Number
                        </label>
                        <input id="phone_number" name="phone_number" type="tel" required
                               class="auth-input appearance-none block w-full px-3 py-3 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm placeholder-zinc-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:focus:ring-zinc-400 focus:border-transparent transition duration-150"
                               value="{{ old('phone_number') }}"
                               placeholder="+12025550123">
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Enter your phone number in international format (e.g., +12025550123)</p>
                    </div>

                    <div>
                        <button type="submit"
                                class="btn-auth w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-900 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition duration-150"
                                x-bind:disabled="processing"
                                x-on:click="processing = true">
                            <span x-show="!processing">Send Verification Code</span>
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
                <span class="text-zinc-600 dark:text-zinc-400">Already have an account?</span>
                <a href="{{ route('login') }}" class="font-medium text-zinc-800 dark:text-zinc-200 hover:underline transition duration-150 ml-1">
                    Sign in
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/auth/form-animation.js'])
@endpush
