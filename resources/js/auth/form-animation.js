/**
 * Form animations for auth pages
 */
document.addEventListener('DOMContentLoaded', function() {
    const formElements = document.querySelectorAll('.auth-input');

    formElements.forEach(element => {
        // Add focus animations
        element.addEventListener('focus', function() {
            this.parentElement.classList.add('form-field-active');

            // Subtle scale effect
            this.classList.add('scale-105');
            setTimeout(() => {
                this.classList.remove('scale-105');
            }, 200);
        });

        element.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('form-field-active');
            }
        });

        // Initialize with active class if has value (e.g. on form error)
        if (element.value) {
            element.parentElement.classList.add('form-field-active');
        }
    });

    // Add form submission animation
    const authForms = document.querySelectorAll('.auth-form');
    authForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            const button = this.querySelector('button[type="submit"]');

            if (button && !button.classList.contains('processing')) {
                button.classList.add('processing');

                // Prevent double submission
                if (!this.checkValidity()) {
                    button.classList.remove('processing');
                }
            }
        });
    });
});

/**
 * Password visibility toggle
 *
 * @param {string} inputId - The ID of the password input element
 */
// In form-animations.js
function togglePasswordVisibility(inputId, toggleIconId) {
    const input = document.getElementById(inputId);
    const toggleIcon = document.getElementById(toggleIconId);

    if (!input || !toggleIcon) return;

    const isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";

    toggleIcon.innerHTML = isPassword ?
        `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             class="w-5 h-5">
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
         </svg>` :
        `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             class="w-5 h-5">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
         </svg>`;
}
