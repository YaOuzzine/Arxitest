import showNotification from './showNotification.js';
/**
 * Phone verification form handling
 */
document.addEventListener('DOMContentLoaded', function() {
    // Handle OTP form submission
    const form = document.querySelector('.auth-form');
    const hiddenInput = document.getElementById('verification_code_hidden');

    if (form && hiddenInput) {
        form.addEventListener('submit', function(event) {
            const code = getFullCode();
            if (code.length !== 6) {
                event.preventDefault();
                return false;
            }

            hiddenInput.value = code;
        });
    }

    // Prepare each input cell for better UX
    prepareInputCells();

    // Handle resend code functionality
    setupResendCode();
});

/**
 * Move to the next input field when a digit is entered
 *
 * @param {HTMLElement} currentInput The current input field
 * @param {number} position Current position (1-6)
 */
function moveToNext(currentInput, position) {
    if (currentInput.value.length === 1) {
        currentInput.value = currentInput.value.replace(/[^0-9]/g, '');

        if (position < 6) {
            const nextInput = document.getElementById(`code-${position + 1}`);
            if (nextInput) {
                nextInput.focus();
            }
        } else {
            // Last input - check if all fields are filled and submit form
            const allFilled = getFullCode().length === 6;
            if (allFilled) {
                currentInput.blur();
                const submitButton = document.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.focus();
                }
            }
        }
    } else if (currentInput.value.length === 0 && position > 1) {
        // Handle backspace
        const prevInput = document.getElementById(`code-${position - 1}`);
        if (prevInput) {
            prevInput.focus();
        }
    }
}

/**
 * Get the full verification code by combining all input fields
 *
 * @returns {string} The complete verification code
 */
function getFullCode() {
    let code = '';
    for (let i = 1; i <= 6; i++) {
        const input = document.getElementById(`code-${i}`);
        if (input) {
            code += input.value;
        }
    }
    return code;
}

/**
 * Prepare all OTP input cells for better UX
 */
function prepareInputCells() {
    const inputs = document.querySelectorAll('[id^="code-"]');

    inputs.forEach(input => {
        // Allow pasting the full code
        input.addEventListener('paste', function(event) {
            event.preventDefault();
            const clipboardData = event.clipboardData || window.clipboardData;
            const pastedData = clipboardData.getData('text');

            // If pasted data looks like a verification code
            if (/^\d{6}$/.test(pastedData)) {
                fillInputs(pastedData);
            }
        });

        // Disable non-numeric input
        input.addEventListener('keydown', function(event) {
            // Allow backspace, tab, arrow keys
            if ([8, 9, 37, 38, 39, 40].includes(event.keyCode)) {
                return;
            }

            // Allow numbers
            if ((event.keyCode >= 48 && event.keyCode <= 57) ||
                (event.keyCode >= 96 && event.keyCode <= 105)) {
                return;
            }

            // Block all other keys
            event.preventDefault();
        });

        // Highlight on focus
        input.addEventListener('focus', function() {
            this.select();
        });
    });
}

/**
 * Fill all input fields with a pasted verification code
 *
 * @param {string} code The verification code to fill
 */
function fillInputs(code) {
    for (let i = 1; i <= 6; i++) {
        const input = document.getElementById(`code-${i}`);
        if (input && code.length >= i) {
            input.value = code.charAt(i - 1);
        }
    }

    // Focus on the last input
    const lastInput = document.getElementById(`code-6`);
    if (lastInput) {
        lastInput.focus();
    }
}

/**
 * Set up the resend code functionality
 */
function setupResendCode() {
    const resendButton = document.getElementById('resend-code');
    const countdownEl = document.getElementById('resend-countdown');

    if (!resendButton || !countdownEl) return;

    let countdownTime = 60;
    let countdownInterval = null;

    resendButton.addEventListener('click', function() {
        // Disable the button and show countdown
        resendButton.classList.add('opacity-50', 'cursor-not-allowed');
        resendButton.disabled = true;

        countdownEl.classList.remove('hidden');
        countdownEl.textContent = `(${countdownTime}s)`;

        // Start countdown
        countdownInterval = setInterval(() => {
            countdownTime--;
            countdownEl.textContent = `(${countdownTime}s)`;

            if (countdownTime <= 0) {
                clearInterval(countdownInterval);
                countdownTime = 60;

                resendButton.classList.remove('opacity-50', 'cursor-not-allowed');
                resendButton.disabled = false;
                countdownEl.classList.add('hidden');
            }
        }, 1000);

        // Send API request to resend code
        fetch('/auth/phone/resend', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                phone_number: document.querySelector('input[name="phone_number"]').value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success toast or message
                showNotification('Verification code resent successfully!', 'success');
            } else {
                showNotification('Failed to resend code. Please try again.', 'error');
            }
        })
        .catch(() => {
            showNotification('An error occurred. Please try again.', 'error');
        });
    });
}
