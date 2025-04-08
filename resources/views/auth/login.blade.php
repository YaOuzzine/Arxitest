@extends('layouts.auth')

@section('title', 'Login')

@section('header-actions')
    <a href="{{ route('register') }}" class="text-sm font-medium hover:underline transition duration-150">
        Register
    </a>
@endsection

@section('content')
    <div class="flex flex-col items-center">
        <div class="w-full max-w-md">
            <div
                class="auth-card bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-sm overflow-hidden">
                <div class="p-8">
                    <div class="text-center mb-8">
                        <h1 class="text-2xl font-bold mb-2">Welcome to Arxitest</h1>
                        <p class="text-zinc-600 dark:text-zinc-400">Sign in to continue to the platform</p>
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

                    <form method="POST" action="{{ route('login') }}" class="auth-form space-y-6" x-data="{ processing: false }">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Email Address
                            </label>
                            <input id="email" name="email" type="email" autocomplete="email" required
                                class="auth-input appearance-none block w-full px-3 py-3 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm placeholder-zinc-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:focus:ring-zinc-400 focus:border-transparent transition duration-150"
                                value="{{ old('email') }}" placeholder="you@example.com">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Password
                                </label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}"
                                        class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 transition duration-150">
                                        Forgot password?
                                    </a>
                                @endif
                            </div>
                            <div class="relative">
                                <input id="password" name="password" type="password" autocomplete="current-password"
                                    required
                                    class="auth-input appearance-none block w-full px-3 py-3 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm placeholder-zinc-400 dark:placeholder-zinc-500 bg-white dark:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:focus:ring-zinc-400 focus:border-transparent transition duration-150"
                                    placeholder="••••••••">
                                <button type="button"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 focus:outline-none"
                                    onclick="togglePasswordVisibility('password')">
                                    <span id="password-toggle-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="w-5 h-5">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input id="remember_me" name="remember" type="checkbox"
                                class="h-4 w-4 text-zinc-800 dark:text-zinc-200 border-zinc-300 dark:border-zinc-600 rounded focus:ring-zinc-500 dark:focus:ring-zinc-400 transition duration-150">
                            <label for="remember_me" class="ml-2 block text-sm text-zinc-700 dark:text-zinc-300">
                                Remember me
                            </label>
                        </div>

                        <div>
                            <button type="submit"
                                class="btn-auth w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-900 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition duration-150"
                                x-bind:disabled="processing" x-on:click="processing = true">
                                <span x-show="!processing">Sign in</span>
                                <span x-show="processing" x-cloak class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </form>

                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-zinc-300 dark:border-zinc-600"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                                Or continue with
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <!-- Google Auth -->
                        <a href="{{ route('auth.oauth.redirect', ['provider' => 'google']) }}"
                            class="social-btn social-btn-google border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm bg-white dark:bg-zinc-900 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition duration-150">
                            <svg viewBox="0 0 24 24" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                                <g transform="matrix(1, 0, 0, 1, 27.009001, -39.238998)">
                                    <path fill="#4285F4"
                                        d="M -3.264 51.509 C -3.264 50.719 -3.334 49.969 -3.454 49.239 L -14.754 49.239 L -14.754 53.749 L -8.284 53.749 C -8.574 55.229 -9.424 56.479 -10.684 57.329 L -10.684 60.329 L -6.824 60.329 C -4.564 58.239 -3.264 55.159 -3.264 51.509 Z" />
                                    <path fill="#34A853"
                                        d="M -14.754 63.239 C -11.514 63.239 -8.804 62.159 -6.824 60.329 L -10.684 57.329 C -11.764 58.049 -13.134 58.489 -14.754 58.489 C -17.884 58.489 -20.534 56.379 -21.484 53.529 L -25.464 53.529 L -25.464 56.619 C -23.494 60.539 -19.444 63.239 -14.754 63.239 Z" />
                                    <path fill="#FBBC05"
                                        d="M -21.484 53.529 C -21.734 52.809 -21.864 52.039 -21.864 51.239 C -21.864 50.439 -21.724 49.669 -21.484 48.949 L -21.484 45.859 L -25.464 45.859 C -26.284 47.479 -26.754 49.299 -26.754 51.239 C -26.754 53.179 -26.284 54.999 -25.464 56.619 L -21.484 53.529 Z" />
                                    <path fill="#EA4335"
                                        d="M -14.754 43.989 C -12.984 43.989 -11.404 44.599 -10.154 45.789 L -6.734 42.369 C -8.804 40.429 -11.514 39.239 -14.754 39.239 C -19.444 39.239 -23.494 41.939 -25.464 45.859 L -21.484 48.949 C -20.534 46.099 -17.884 43.989 -14.754 43.989 Z" />
                                </g>
                            </svg>
                            Google
                        </a>

                        <!-- Microsoft Auth -->
                        <a href="{{ route('auth.oauth.redirect', ['provider' => 'microsoft']) }}"
                            class="social-btn social-btn-microsoft border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm bg-white dark:bg-zinc-900 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition duration-150">
                            <svg width="21" height="21" viewBox="0 0 21 21" xmlns="http://www.w3.org/2000/svg">
                                <rect x="1" y="1" width="9" height="9" fill="#f25022" />
                                <rect x="1" y="11" width="9" height="9" fill="#00a4ef" />
                                <rect x="11" y="1" width="9" height="9" fill="#7fba00" />
                                <rect x="11" y="11" width="9" height="9" fill="#ffb900" />
                            </svg>
                            Microsoft
                        </a>

                        <!-- GitHub Auth -->
                        <a href="{{ route('auth.oauth.redirect', ['provider' => 'github']) }}"
                            class="social-btn social-btn-github border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm bg-white dark:bg-zinc-900 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition duration-150">
                            <svg width="20" height="20" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"
                                    fill="currentColor" />
                            </svg>
                            GitHub
                        </a>

                        <!-- Phone Auth -->
                        <a href="{{ route('auth.phone') }}"
                            class="social-btn social-btn-phone border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm bg-white dark:bg-zinc-900 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                </path>
                            </svg>
                            Phone
                        </a>
                    </div>
                </div>

                <div
                    class="px-8 py-4 bg-zinc-50 dark:bg-zinc-700/20 border-t border-zinc-200 dark:border-zinc-700 text-sm text-center">
                    <span class="text-zinc-600 dark:text-zinc-400">Don't have an account?</span>
                    <a href="{{ route('register') }}"
                        class="font-medium text-zinc-800 dark:text-zinc-200 hover:underline transition duration-150 ml-1">
                        Create one now
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/auth/form-animations.js'])
@endpush
