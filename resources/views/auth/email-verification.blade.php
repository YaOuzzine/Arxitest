@extends('layouts.auth')

@section('title', 'Email Verification')

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
                    <h1 class="text-2xl font-bold mb-2">Verify Your Email</h1>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        We've sent a verification code to<br>
                        <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ session('email', 'your email address') }}</span>
                    </p>
                </div>

                @if (session('status'))
                    <div class="bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 p-4 rounded-md mb-6">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 p-4 rounded-md mb-6">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('auth.email.verify') }}" class="auth-form space-y-6" x-data="{ processing: false }">
                    @csrf

                    <div>
                        <label for="verification_code" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Verification Code
                        </label>
                        <div class="mt-1">
                            <div class="flex justify-between gap-2" id="otp-inputs">
                                @for ($i = 1; $i <= 6; $i++)
                                    <input id="code-{{ $i }}" type="text" maxlength="1" pattern="[0-9]" inputmode="numeric"
                                           class="auth-input w-full aspect-square text-center text-xl font-semibold appearance-none block px-0 py-3 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm bg-white dark:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:focus:ring-zinc-400 focus:border-transparent transition duration-150"
                                           data-index="{{ $i }}"
                                           {{ $i == 1 ? 'autofocus' : '' }}>
                                @endfor
                                <input type="hidden" name="verification_code" id="verification_code_hidden">
                            </div>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                                class="btn-auth w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-900 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition duration-150"
                                x-bind:disabled="processing"
                                x-on:click="processing = true">
                            <span x-show="!processing">Verify Email</span>
                            <span x-show="processing" x-cloak class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Didn't receive the code?
                            <button type="button"
                                    class="text-zinc-800 dark:text-zinc-200 font-medium hover:underline transition duration-150 ml-1"
                                    id="resend-code"
                                    data-resend-url="{{ route('auth.email.resend-verification') }}">
                                Resend code
                            </button>
                            <span class="hidden ml-1" id="resend-countdown"></span>
                        </p>
                    </div>
                </form>
            </div>

            <div class="px-8 py-4 bg-zinc-50 dark:bg-zinc-700/20 border-t border-zinc-200 dark:border-zinc-700 text-sm text-center">
                <span class="text-zinc-600 dark:text-zinc-400">Wrong email address?</span>
                <a href="{{ route('register') }}" class="font-medium text-zinc-800 dark:text-zinc-200 hover:underline transition duration-150 ml-1">
                    Try again
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/auth/form-animation.js', 'resources/js/auth/email-verification.js'])
@endpush
