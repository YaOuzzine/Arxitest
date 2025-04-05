/**
 * Password strength meter for registration form
 */
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const strengthIndicator = document.getElementById('password-strength-indicator');
    const confirmPasswordInput = document.getElementById('password_confirmation');

    if (!passwordInput || !strengthIndicator) return;

    // Listen for password input changes
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);
        updateStrengthIndicator(strength, password);
    });

    // Check if passwords match
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirmPassword = this.value;

            if (confirmPassword.length > 0) {
                if (password !== confirmPassword) {
                    this.classList.add('border-red-300', 'dark:border-red-600');
                    this.classList.remove('border-green-300', 'dark:border-green-600');
                } else {
                    this.classList.remove('border-red-300', 'dark:border-red-600');
                    this.classList.add('border-green-300', 'dark:border-green-600');
                }
            } else {
                this.classList.remove('border-red-300', 'dark:border-red-600', 'border-green-300', 'dark:border-green-600');
            }
        });
    }
});

/**
 * Calculate password strength score (0-4)
 * 0: Empty
 * 1: Very weak
 * 2: Weak
 * 3: Medium
 * 4: Strong
 *
 * @param {string} password
 * @returns {number} Strength score 0-4
 */
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

/**
 * Update the strength indicator based on the calculated score
 *
 * @param {number} strength Strength score 0-4
 * @param {string} password The password string
 */
function updateStrengthIndicator(strength, password) {
    const indicator = document.getElementById('password-strength-indicator');

    // Remove all existing classes
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

/**
 * Generate HTML for the strength meter
 *
 * @param {number} strength Strength score 0-4
 * @returns {string} HTML for the strength meter
 */
function generateStrengthMeter(strength) {
    let meterHtml = '<div class="flex space-x-1 my-1">';

    for (let i = 1; i <= 4; i++) {
        const width = i === 1 ? 'w-1/4' : i === 2 ? 'w-1/4' : i === 3 ? 'w-1/4' : 'w-1/4';
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
