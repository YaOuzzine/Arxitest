{{-- resources/views/auth/register.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - Arxitest</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Add FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-white">
    <div class="min-h-screen flex">
        <!-- Left side with registration form -->
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
                            <a href="{{ route('overview') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900">Overview</a>
                            <a href="{{ route('pricing') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900">Pricing</a>
                            <a href="{{ route('privacy') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900">Privacy and terms</a>
                            <a href="{{ route('faq') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900">FAQ</a>

                            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Sign in
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
                            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Create your account</h1>
                            <h2 class="text-xl text-gray-600">Start your testing journey with Arxitest</h2>
                        </div>

                        <!-- Google signup button -->
                        <button type="button" class="w-full flex items-center justify-center gap-3 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            <img class="h-5 w-5" src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google logo">
                            Sign up with Google
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
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800" id="error-message"></h3>
                                </div>
                            </div>
                        </div>

                        <!-- Registration form -->
                        <form id="register-form" class="space-y-4">
                            @csrf
                            <div>
                                <div class="relative">
                                    <input
                                        id="name"
                                        name="name"
                                        type="text"
                                        required
                                        placeholder="Full name"
                                        class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    >
                                    <div class="hidden absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-green-500" id="name-valid">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="hidden text-sm text-red-600" id="name-error"></div>
                                </div>
                            </div>

                            <div>
                                <div class="relative">
                                    <input
                                        id="email"
                                        name="email"
                                        type="email"
                                        required
                                        placeholder="Work email"
                                        class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    >
                                    <div class="hidden absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-green-500" id="email-valid">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="hidden text-sm text-red-600" id="email-error"></div>
                                </div>
                            </div>

                            <div>
                                <div class="relative">
                                    <input
                                        id="team_name"
                                        name="team_name"
                                        type="text"
                                        required
                                        placeholder="Team name"
                                        class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    >
                                    <div class="hidden absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-green-500" id="team_name-valid">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="hidden text-sm text-red-600" id="team_name-error"></div>
                                </div>
                            </div>

                            <div>
                                <div class="relative">
                                    <input
                                        id="password"
                                        name="password"
                                        type="password"
                                        required
                                        placeholder="Password"
                                        class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    >
                                    <button
                                        type="button"
                                        id="password-toggle"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                    >
                                        <i class="fas fa-eye" id="eye-icon"></i>
                                    </button>
                                </div>

                                <!-- Simplified password strength meter -->
                                <div class="mt-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                                            <div class="bg-red-500 h-1.5 rounded-full" style="width: 0%" id="password-strength-meter"></div>
                                        </div>
                                        <span class="text-xs text-gray-500 min-w-[40px]" id="password-strength-text">Weak</span>
                                    </div>
                                    <div class="hidden text-xs text-red-600" id="password-error"></div>
                                </div>
                            </div>

                            <div>
                                <div class="relative">
                                    <input
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        type="password"
                                        required
                                        placeholder="Confirm password"
                                        class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                    >
                                    <button
                                        type="button"
                                        id="confirm-password-toggle"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                    >
                                        <i class="fas fa-eye" id="confirm-eye-icon"></i>
                                    </button>
                                    <div class="hidden text-sm text-red-600" id="password_confirmation-error"></div>
                                </div>
                            </div>

                            <div class="pt-2">
                                <button type="submit" id="submit-button" class="flex w-full justify-center rounded-md bg-black px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                    Create account
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
            <div class="py-0">
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
        <div class="hidden lg:block relative flex-1">
            <div class="absolute inset-0">
                <div class="h-full w-full object-cover">
                    <img
                        src="{{ asset('images/dashboard-preview.webp') }}"
                        alt="Arxitest Dashboard Preview"
                        class="h-full w-full object-cover"
                    >
                </div>
            </div>
        </div>
    </div>

    <script>
        // Form validation state - start with null instead of false for initial state
        const formState = {
            name: null,
            email: null,
            team_name: null,
            password: null,
            password_confirmation: null
        };

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

        // Password confirmation toggle
        document.getElementById('confirm-password-toggle').addEventListener('click', function() {
            const confirmInput = document.getElementById('password_confirmation');
            const eyeIcon = document.getElementById('confirm-eye-icon');

            if (confirmInput.type === 'password') {
                confirmInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                confirmInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });

        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;

            // Check length (at least 8 characters)
            if (password.length >= 8) strength += 20;

            // Check for uppercase letters
            if (/[A-Z]/.test(password)) strength += 20;

            // Check for lowercase letters
            if (/[a-z]/.test(password)) strength += 20;

            // Check for numbers
            if (/[0-9]/.test(password)) strength += 20;

            // Check for special characters
            if (/[^A-Za-z0-9]/.test(password)) strength += 20;

            // Update the meter
            const meter = document.getElementById('password-strength-meter');
            const strengthText = document.getElementById('password-strength-text');

            if (meter && strengthText) {
                meter.style.width = strength + '%';

                // Color and text based on strength
                if (strength <= 40) {
                    meter.className = 'bg-red-500 h-1.5 rounded-full';
                    strengthText.innerText = 'Weak';
                    strengthText.className = 'text-xs text-red-500 min-w-[40px]';
                    return false;
                } else if (strength <= 80) {
                    meter.className = 'bg-yellow-500 h-1.5 rounded-full';
                    strengthText.innerText = 'Normal';
                    strengthText.className = 'text-xs text-yellow-600 min-w-[40px]';
                    return strength >= 60; // Require at least 60% for medium to be valid
                } else {
                    meter.className = 'bg-green-500 h-1.5 rounded-full';
                    strengthText.innerText = 'Strong';
                    strengthText.className = 'text-xs text-green-600 min-w-[40px]';
                    return true;
                }
            }

            return false;
        }

        // Validate that passwords match
        function validatePasswordMatch() {
            const password = document.getElementById('password')?.value || '';
            const confirmation = document.getElementById('password_confirmation')?.value || '';
            const errorElement = document.getElementById('password_confirmation-error');

            if (!errorElement) return false;

            if (confirmation === '') {
                errorElement.textContent = '';
                errorElement.classList.add('hidden');
                formState.password_confirmation = null; // not validated yet
                return false;
            }

            if (password !== confirmation) {
                errorElement.textContent = 'Passwords do not match';
                errorElement.classList.remove('hidden');
                formState.password_confirmation = false;
                return false;
            } else {
                errorElement.textContent = '';
                errorElement.classList.add('hidden');
                formState.password_confirmation = true;
                return true;
            }
        }

        // Basic field validations without server calls
        function validateFieldLocally(field, value) {
            const errorElement = document.getElementById(`${field}-error`);
            const validElement = document.getElementById(`${field}-valid`);

            // If elements don't exist, return
            if (!errorElement) {
                console.warn(`Element #${field}-error not found`);
                return false;
            }

            // If no value, mark as invalid
            if (!value || value.trim() === '') {
                errorElement.textContent = 'This field is required';
                errorElement.classList.remove('hidden');
                if (validElement) {
                    validElement.classList.add('hidden');
                }
                formState[field] = false;
                return false;
            }

            // Basic validation based on field type
            let isValid = true;

            switch(field) {
                case 'name':
                    isValid = value.length >= 2;
                    break;
                case 'email':
                    isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                    break;
                case 'team_name':
                    isValid = value.length >= 2;
                    break;
                case 'password':
                    isValid = checkPasswordStrength(value);
                    break;
            }

            if (isValid) {
                errorElement.classList.add('hidden');
                if (validElement) {
                    validElement.classList.remove('hidden');
                }
                formState[field] = true;
            } else {
                const errorMessages = {
                    'name': 'Name must be at least 2 characters',
                    'email': 'Please enter a valid email address',
                    'team_name': 'Team name must be at least 2 characters',
                    'password': 'Password must be at least 8 characters and include uppercase, lowercase, numbers, and symbols'
                };

                errorElement.textContent = errorMessages[field];
                errorElement.classList.remove('hidden');
                if (validElement) {
                    validElement.classList.add('hidden');
                }
                formState[field] = false;
            }

            return isValid;
        }

        // Update submit button state
        function updateSubmitButton() {
            const submitButton = document.getElementById('submit-button');
            if (!submitButton) return;

            // Only check defined values (not null)
            const definedValues = Object.entries(formState)
                .filter(([_, value]) => value !== null)
                .map(([_, value]) => value);

            // If we have at least one validation done and all are valid
            const allValid = definedValues.length > 0 && definedValues.every(value => value === true);

            submitButton.disabled = !allValid;
        }

        // Set up field validation events - with null checks
        document.getElementById('name')?.addEventListener('input', function() {
            validateFieldLocally('name', this.value);
            updateSubmitButton();
        });

        document.getElementById('email')?.addEventListener('input', function() {
            validateFieldLocally('email', this.value);
            updateSubmitButton();
        });

        document.getElementById('team_name')?.addEventListener('input', function() {
            validateFieldLocally('team_name', this.value);
            updateSubmitButton();
        });

        document.getElementById('password')?.addEventListener('input', function() {
            validateFieldLocally('password', this.value);
            updateSubmitButton();

            // If password confirmation has a value, validate it again
            if (document.getElementById('password_confirmation')?.value) {
                validatePasswordMatch();
                updateSubmitButton();
            }
        });

        document.getElementById('password_confirmation')?.addEventListener('input', function() {
            validatePasswordMatch();
            updateSubmitButton();
        });

        // Initial form validation - validate fields that have values on page load
        window.addEventListener('DOMContentLoaded', function() {
            const fields = ['name', 'email', 'team_name', 'password'];
            fields.forEach(field => {
                const element = document.getElementById(field);
                if (element && element.value) {
                    validateFieldLocally(field, element.value);
                }
            });

            // Check password confirmation if it has a value
            const passwordConfirmation = document.getElementById('password_confirmation');
            if (passwordConfirmation && passwordConfirmation.value) {
                validatePasswordMatch();
            }

            updateSubmitButton();
        });

        // Form submission
        const form = document.getElementById('register-form');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const errorAlert = document.getElementById('error-alert');
                const errorMessage = document.getElementById('error-message');
                const submitButton = e.target.querySelector('button[type="submit"]');

                if (!errorAlert || !errorMessage || !submitButton) {
                    console.error('Required elements not found');
                    return;
                }

                const originalButtonText = submitButton.innerHTML;

                // Revalidate all fields before submission
                const fields = ['name', 'email', 'team_name', 'password'];
                let allValid = true;

                fields.forEach(field => {
                    const element = document.getElementById(field);
                    if (element) {
                        if (!validateFieldLocally(field, element.value)) {
                            allValid = false;
                        }
                    } else {
                        allValid = false;
                    }
                });

                if (!validatePasswordMatch()) {
                    allValid = false;
                }

                if (!allValid) {
                    errorMessage.textContent = 'Please fix the validation errors before submitting';
                    errorAlert.classList.remove('hidden');
                    setTimeout(() => {
                        errorAlert.classList.add('hidden');
                    }, 5000);
                    return;
                }

                try {
                    // Show loading state
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Creating account...';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                    if (!csrfToken) {
                        throw new Error('CSRF token not found');
                    }

                    const response = await fetch('/api/auth/register', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            name: document.getElementById('name')?.value || '',
                            email: document.getElementById('email')?.value || '',
                            team_name: document.getElementById('team_name')?.value || '',
                            password: document.getElementById('password')?.value || '',
                            password_confirmation: document.getElementById('password_confirmation')?.value || ''
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        // Handle validation errors
                        if (response.status === 422 && data.errors) {
                            // Clear previous errors
                            Object.keys(formState).forEach(field => {
                                const errorElement = document.getElementById(`${field}-error`);
                                const validElement = document.getElementById(`${field}-valid`);

                                if (errorElement) {
                                    errorElement.textContent = '';
                                    errorElement.classList.add('hidden');
                                }
                                if (validElement) {
                                    validElement.classList.add('hidden');
                                }
                            });

                            // Show new errors
                            Object.keys(data.errors).forEach(field => {
                                const errorElement = document.getElementById(`${field}-error`);
                                if (errorElement) {
                                    errorElement.textContent = data.errors[field][0];
                                    errorElement.classList.remove('hidden');
                                    formState[field] = false;
                                }
                            });

                            throw new Error('Please fix the highlighted errors');
                        } else {
                            throw new Error(data.message || 'Registration failed');
                        }
                    }

                    // Store the token
                    localStorage.setItem('token', data.access_token);

                    // Redirect to home page
                    window.location.href = '/';
                } catch (error) {
                    errorMessage.textContent = error.message;
                    errorAlert.classList.remove('hidden');

                    // Reset button state
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;

                    setTimeout(() => {
                        errorAlert.classList.add('hidden');
                    }, 5000);
                }
            });
        } else {
            console.error('Registration form not found');
        }
    </script>
</body>
</html>
