@extends('layouts.profile')

@section('profile-title', 'Change Password')

@section('profile-breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Change Password</span>
    </li>
@endsection

@section('profile-content')
<form action="{{ route('dashboard.profile.password.update') }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="space-y-4 max-w-lg">
        <div>
            <label for="current_password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Current Password</label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <input type="password" id="current_password" name="current_password" required
                    class="block w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <button type="button" onclick="togglePasswordVisibility('current_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                    <i data-lucide="eye" class="h-5 w-5"></i>
                </button>
            </div>
            @error('current_password')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">New Password</label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <input type="password" id="password" name="password" required
                    class="block w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <button type="button" onclick="togglePasswordVisibility('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                    <i data-lucide="eye" class="h-5 w-5"></i>
                </button>
            </div>
            @error('password')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            <div id="password-strength-indicator" class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                Password must be at least 8 characters
            </div>
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Confirm New Password</label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <input type="password" id="password_confirmation" name="password_confirmation" required
                    class="block w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <button type="button" onclick="togglePasswordVisibility('password_confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                    <i data-lucide="eye" class="h-5 w-5"></i>
                </button>
            </div>
        </div>

        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 p-4 rounded-lg text-amber-700 dark:text-amber-300">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i data-lucide="alert-triangle" class="h-5 w-5 text-amber-600 dark:text-amber-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm">
                        Changing your password will log you out of all other devices. You'll need to sign in again on those devices.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
        <a href="{{ route('dashboard.profile.show') }}" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary">Change Password</button>
    </div>
</form>

@push('scripts')
<script>
    // Toggle password visibility
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const icon = input.nextElementSibling.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            input.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }

        // Re-render icon
        if (typeof lucide !== 'undefined') {
            lucide.createIcons({
                selector: `button[onclick="togglePasswordVisibility('${inputId}')"] i`
            });
        }
    }

    // Password strength indicator
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('password');
        const strengthIndicator = document.getElementById('password-strength-indicator');

        if (passwordInput && strengthIndicator) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                updateStrengthIndicator(calculatePasswordStrength(password), password);
            });
        }
    });

    function calculatePasswordStrength(password) {
        if (!password) return 0;

        let score = 0;

        // Length check
        if (password.length >= 8) score += 1;
        if (password.length >= 12) score += 1;

        // Complexity checks
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score += 1;
        if (/[0-9]/.test(password)) score += 0.5;
        if (/[^a-zA-Z0-9]/.test(password)) score += 0.5;

        return Math.min(4, Math.floor(score));
    }

    function updateStrengthIndicator(strength, password) {
        const indicator = document.getElementById('password-strength-indicator');

        indicator.classList.remove(
            'text-red-500', 'text-orange-500', 'text-yellow-500', 'text-green-500',
            'dark:text-red-400', 'dark:text-orange-400', 'dark:text-yellow-400', 'dark:text-green-400'
        );

        if (!password) {
            indicator.innerHTML = 'Password must be at least 8 characters';
            indicator.classList.add('text-zinc-500', 'dark:text-zinc-400');
            return;
        }

        let strengthText, colorClass;

        switch (strength) {
            case 1:
                strengthText = 'Very weak';
                colorClass = 'text-red-500 dark:text-red-400';
                break;
            case 2:
                strengthText = 'Weak';
                colorClass = 'text-orange-500 dark:text-orange-400';
                break;
            case 3:
                strengthText = 'Medium';
                colorClass = 'text-yellow-500 dark:text-yellow-400';
                break;
            case 4:
                strengthText = 'Strong';
                colorClass = 'text-green-500 dark:text-green-400';
                break;
            default:
                strengthText = 'Very weak';
                colorClass = 'text-red-500 dark:text-red-400';
        }

        // Generate the strength meter visually
        const meterHtml = generateStrengthMeter(strength);

        indicator.innerHTML = `${meterHtml} <span class="${colorClass}">${strengthText}</span>`;
        indicator.classList.add(colorClass);
    }

    function generateStrengthMeter(strength) {
        let meterHtml = '<div class="flex space-x-1 my-1">';

        for (let i = 1; i <= 4; i++) {
            const width = 'w-1/4';
            let bgClass = 'bg-zinc-200 dark:bg-zinc-700';

            if (i <= strength) {
                if (strength === 1) bgClass = 'bg-red-500 dark:bg-red-600';
                else if (strength === 2) bgClass = 'bg-orange-500 dark:bg-orange-600';
                else if (strength === 3) bgClass = 'bg-yellow-500 dark:bg-yellow-600';
                else if (strength === 4) bgClass = 'bg-green-500 dark:bg-green-600';
            }

            meterHtml += `<div class="${width} h-1 rounded-full ${bgClass}"></div>`;
        }

        meterHtml += '</div>';
        return meterHtml;
    }
</script>
@endpush
@endsection
