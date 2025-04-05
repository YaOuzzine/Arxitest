/**
 * Theme toggle functionality for light/dark mode
 */
(function() {
    // Execute as soon as this script loads
    // First, ensure the toggleTheme function is defined as a global function
    window.toggleTheme = function() {
        // Toggle theme class
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.theme = 'light';
        } else {
            document.documentElement.classList.add('dark');
            localStorage.theme = 'dark';
        }

        // Add a subtle animation effect on theme change
        addThemeChangeAnimation();

        // Trigger a custom event that other scripts can listen for
        const themeChangedEvent = new CustomEvent('themeChanged', {
            detail: { isDark: document.documentElement.classList.contains('dark') }
        });
        document.dispatchEvent(themeChangedEvent);

        console.log('Theme toggled to:', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    };

    // Add the animation function to global scope too
    window.addThemeChangeAnimation = function() {
        const overlay = document.createElement('div');
        overlay.classList.add('fixed', 'inset-0', 'bg-white', 'dark:bg-zinc-900', 'z-50', 'pointer-events-none');
        overlay.style.opacity = '0';
        document.body.appendChild(overlay);

        // Fade in and out
        setTimeout(() => {
            overlay.style.opacity = '0.1';
            overlay.style.transition = 'opacity 0.2s ease-in-out';

            setTimeout(() => {
                overlay.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(overlay);
                }, 200);
            }, 100);
        }, 10);
    };

    // Set up the event listener for the toggle button once the DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log("Theme toggle script loaded");
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            console.log("Theme toggle button found");
            themeToggle.addEventListener('click', window.toggleTheme);
        } else {
            console.error("Theme toggle button not found");
        }
    });

    // Log the current theme state for debugging
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Initial theme state:', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    });
})();
