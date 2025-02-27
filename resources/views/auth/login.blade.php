{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Welcome to Arxitest</title>
    @vite('resources/css/app.css')
    <!-- Add FontAwesome for the eye icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-white">
    <div class="min-h-screen flex">
        <!-- Left side with login form -->
        <div class="flex-1 flex flex-col px-4 sm:px-6 lg:px-20 xl:px-24 relative">
            <!-- Fixed Navigation Bar -->
            <div class="absolute top-0 left-0 right-0 px-4 py-4">
                <nav class="bg-white shadow-md rounded-full px-6 py-3">
                    <div class="flex justify-between items-center">
                        <!-- Logo on the left -->
                        <div class="flex items-center">
                            <img src="{{ asset('images/logo.svg') }}" alt="Arxitest" class="h-8 w-auto">
                        </div>

                        <!-- Navigation links and button -->
                        <div class="flex items-center space-x-8">
                            <a href="{{ route('overview') }}"
                                class="text-sm font-medium text-gray-500 hover:text-gray-900">Overview</a>
                            <a href="{{ route('pricing') }}"
                                class="text-sm font-medium text-gray-500 hover:text-gray-900">Pricing</a>
                            <a href="{{ route('privacy') }}"
                                class="text-sm font-medium text-gray-500 hover:text-gray-900">Privacy and terms</a>
                            <a href="{{ route('faq') }}"
                                class="text-sm font-medium text-gray-500 hover:text-gray-900">FAQ</a>

                            <a href="{{ route('register') }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-black hover:font-bold">
                                Register
                            </a>

                        </div>
                    </div>
                </nav>
            </div>

            <div class="flex-1 flex flex-col justify-center mt-16">

                <div class="mx-auto w-full max-w-sm lg:w-96">
                    <div class="space-y-6">
                        <!-- Title and subtitle -->
                        <div class="space-y-2">
                            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Welcome to Arxitest</h1>
                            <h2 class="text-xl text-gray-600">Take test automation to the next level</h2>
                        </div>

                        <!-- Google login button -->
                        <button type="button"
                            class="w-full flex items-center justify-center gap-3 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            <img class="h-5 w-5" src="https://www.svgrepo.com/show/475656/google-color.svg"
                                alt="Google logo">
                            Continue with Google
                        </button>

                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-200"></div>
                            </div>
                            <div class="relative flex justify-center text-sm font-medium leading-6">
                                <span class="bg-white px-6 text-gray-500">or</span>
                            </div>
                        </div>

                        <!-- Error Alert -->
                        <div id="error-alert" class="hidden rounded-md bg-red-50 p-4">
                            <div class="flex">
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800" id="error-message"></h3>
                                </div>
                            </div>
                        </div>

                        <!-- Login form -->
                        <form id="login-form" class="space-y-6">
                            @csrf
                            <div>
                                <div class="mt-2">
                                    <input id="email" name="email" type="email" autocomplete="email" required
                                        placeholder="Work email"
                                        class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                                </div>
                            </div>

                            <div>
                                <div class="mt-2 relative">
                                    <input id="password" name="password" type="password" required
                                        placeholder="Password"
                                        class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                                    <button type="button" id="password-toggle"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-eye" id="eye-icon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input id="remember" name="remember" type="checkbox"
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600">
                                    <label for="remember" class="ml-2 block text-sm text-gray-900">Remember me</label>
                                </div>
                                <a href="{{ route('password.request') }}"
                                    class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                    Forgot password?
                                </a>
                            </div>

                            <div>
                                <button type="submit"
                                    class="flex w-full justify-center rounded-md bg-black px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                    Sign in
                                </button>
                            </div>
                        </form>

                        <!-- Terms text -->
                        <p class="text-center text-sm text-gray-500">
                            By signing up, you agree to the
                            <a href="#" class="font-medium text-gray-900 hover:underline">Terms of Use</a>,
                            <a href="#" class="font-medium text-gray-900 hover:underline">Privacy Notice</a>,
                            and
                            <a href="#" class="font-medium text-gray-900 hover:underline">Cookie Notice</a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="py-6">
                <div class="flex justify-between px-4">
                    <div class="flex items-center space-x-2">
                        <img src="{{ asset('images/logo-icon.svg') }}" alt="Arxitest Icon" class="h-20 w-20">
                    </div>
                    <div class="flex items-center">
                        <span class="text-gray-500">curated by</span>
                        <span class="ml-2 font-medium">Arxitest</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right side with screenshot -->
        <div class="hidden lg:block relative flex-1 ">
            <div class="absolute inset-0">
                <div class="h-full w-full object-cover">
                    <img src="{{ asset('images/dashboard-preview.webp') }}" alt="Arxitest Dashboard Preview"
                        class="h-full w-full object-cover rounded-3xl">
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password toggle functionality
        document.getElementById('password-toggle').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });

        // Login form submission
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorAlert = document.getElementById('error-alert');
            const errorMessage = document.getElementById('error-message');

            try {
                const response = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: document.getElementById('email').value,
                        password: document.getElementById('password').value,
                        remember: document.getElementById('remember').checked
                    }),
                    // Important: include credentials to ensure cookies are stored
                    credentials: 'include'
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Invalid credentials');
                }

                // Store the token
                localStorage.setItem('token', data.access_token);

                // For debugging
                console.log('Authentication successful', data);

                // Do a simple redirect - no need for an extra fetch since Auth::login()
                // has already been called in the AuthController
                window.location.href = '/';
            } catch (error) {
                errorMessage.textContent = error.message;
                errorAlert.classList.remove('hidden');

                setTimeout(() => {
                    errorAlert.classList.add('hidden');
                }, 5000);
            }
        });
    </script>
</body>

</html>
