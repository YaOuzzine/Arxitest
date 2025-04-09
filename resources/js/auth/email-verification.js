import showNotification from './showNotification.js';

document.addEventListener('DOMContentLoaded', function() {
    // OTP Input Handling
    const otpInputs = document.querySelectorAll('#otp-inputs input[type="text"]');
    const form = document.querySelector('.auth-form');
    const hiddenInput = document.getElementById('verification_code_hidden');

    // 1. Input Navigation and Validation
    otpInputs.forEach((input, index) => {
        // Numeric input filtering
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, ''); // Only allow numbers

            // Auto-focus next input
            if (this.value.length === 1) {
                if (index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                } else {
                    this.blur(); // Last input, remove focus
                }
            }
        });

        // Backspace handling
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace') {
                this.value = ''; // Clear current input immediately
                if (index > 0) {
                    otpInputs[index - 1].focus();
                }
            }
        });

        // Paste handling
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pasteData = e.clipboardData.getData('text/plain').trim();
            const digits = pasteData.replace(/[^0-9]/g, '').split('');

            digits.slice(0, 6).forEach((digit, i) => {
                if (otpInputs[i]) otpInputs[i].value = digit;
            });

            if (digits.length >= 6) otpInputs[5].focus();
        });
    });

    // 2. Form Submission Handling
    function getFullCode() {
        return Array.from(otpInputs).map(input => input.value).join('');
    }

    if (form && hiddenInput) {
        // Auto-submit when all fields filled
        otpInputs.forEach(input => {
            input.addEventListener('input', () => {
                const code = getFullCode();
                if (code.length === 6) {
                    hiddenInput.value = code;
                    form.requestSubmit(); // Trigger form submission
                }
            });
        });

        // Validation before submission
        form.addEventListener('submit', function(event) {
            const code = getFullCode();
            if (code.length !== 6 || !/^\d+$/.test(code)) {
                event.preventDefault();
                showNotification('Please enter a valid 6-digit code', 'error');
                otpInputs[0].focus();
            }
        });
    }

    // 3. Resend Code Functionality
    function setupResendCode() {
        const resendButton = document.getElementById('resend-code');
        const resendUrl = resendButton.dataset.resendUrl;
        const countdownEl = document.getElementById('resend-countdown');

        if (!resendButton || !countdownEl) return;

        let countdownTime = 60;
        let countdownInterval = null;

        resendButton.addEventListener('click', function() {
            // Visual state changes
            this.classList.add('opacity-50', 'cursor-not-allowed');
            this.innerHTML = '<span class="resend-loader">Sending...</span>';

            // Countdown setup
            countdownEl.classList.remove('hidden');
            countdownEl.textContent = `(${countdownTime}s)`;

            countdownInterval = setInterval(() => {
                countdownTime--;
                countdownEl.textContent = `(${countdownTime}s)`;

                if (countdownTime <= 0) {
                    clearInterval(countdownInterval);
                    resendButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    resendButton.innerHTML = 'Resend code';
                    countdownEl.classList.add('hidden');
                    countdownTime = 60;
                }
            }, 1000);

            // API Request
            fetch(resendUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    email: "{{ session('email') }}" // Essential for backend
                })
            })
            .then(response => response.json())
            .then(data => {
                const message = data.success
                    ? 'New verification code sent!'
                    : data.message || 'Failed to resend code';
                showNotification(message, data.success ? 'success' : 'error');
            })
            .catch(() => {
                showNotification('Network error - please try again', 'error');
            })
            .finally(() => {
                resendButton.innerHTML = 'Resend code';
            });
        });
    }

    setupResendCode();
});
