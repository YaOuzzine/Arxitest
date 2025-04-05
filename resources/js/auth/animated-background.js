/**
 * Animated background effect for auth pages
 */
document.addEventListener('DOMContentLoaded', function() {
    const bg = document.getElementById('animated-background');

    if (!bg) return;

    // Setup initial theme-based background
    updateBackgroundContainer();

    // Clear any existing shapes
    while (bg.firstChild) {
        bg.removeChild(bg.firstChild);
    }

    // Create initial shapes
    for (let i = 0; i < 15; i++) {
        createShape();
    }

    // Add new shapes periodically
    setInterval(createShape, 3000);

    // Listen for theme changes
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            // Allow time for the dark class to be added/removed
            setTimeout(function() {
                updateBackgroundContainer();
                updateAllShapes();
            }, 50);
        });
    }
});

/**
 * Update the background container itself based on theme
 */
function updateBackgroundContainer() {
    const bg = document.getElementById('animated-background');
    if (!bg) return;

    const isDarkMode = document.documentElement.classList.contains('dark');

    if (isDarkMode) {
        bg.style.opacity = '0.5';  // More visible in dark mode
    } else {
        bg.style.opacity = '1';    // Full opacity in light mode
    }
}

/**
 * Create a single animated shape
 */
function createShape() {
    const bg = document.getElementById('animated-background');
    if (!bg) return;

    const shape = document.createElement('div');
    const size = Math.random() * 80 + 30; // Larger shapes
    const isSquare = Math.random() > 0.5;

    // Set shape properties
    shape.classList.add('shape-element');
    shape.style.position = 'absolute';
    shape.style.width = `${size}px`;
    shape.style.height = isSquare ? `${size}px` : `${size * 1.5}px`;

    // Apply appropriate style based on current theme
    applyShapeTheme(shape);

    // Position and transform
    shape.style.left = `${Math.random() * 100}%`;
    shape.style.top = `${Math.random() * 100}%`;
    shape.style.transform = `rotate(${Math.random() * 360}deg)`;
    shape.style.transition = 'all 30s linear';

    // Add border for more visibility
    shape.style.border = '1px solid';
    shape.style.borderColor = document.documentElement.classList.contains('dark')
        ? 'rgba(255, 255, 255, 0.05)'
        : 'rgba(0, 0, 0, 0.08)';

    bg.appendChild(shape);

    // Start animation after a small delay
    setTimeout(() => {
        shape.style.left = `${Math.random() * 100}%`;
        shape.style.top = `${Math.random() * 100}%`;
        shape.style.transform = `rotate(${Math.random() * 360}deg) scale(${Math.random() + 0.7})`;
    }, 100);

    // Remove the shape after animation completes
    setTimeout(() => {
        if (bg.contains(shape)) {
            bg.removeChild(shape);
        }
    }, 30000);
}

/**
 * Apply theme-appropriate styling to a shape
 */
function applyShapeTheme(shape) {
    const isDarkMode = document.documentElement.classList.contains('dark');

    if (isDarkMode) {
        // In dark mode: lighter colors with higher opacity
        shape.style.backgroundColor = 'rgba(255, 255, 255, 0.03)';
        shape.style.boxShadow = '0 4px 12px rgba(255, 255, 255, 0.05)';
    } else {
        // In light mode: darker colors with distinct borders
        shape.style.backgroundColor = 'rgba(0, 0, 0, 0.02)';
        shape.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.04)';
    }
}

/**
 * Update all existing shapes when theme changes
 */
function updateAllShapes() {
    const shapes = document.querySelectorAll('.shape-element');

    shapes.forEach(shape => {
        applyShapeTheme(shape);

        // Update border color too
        shape.style.borderColor = document.documentElement.classList.contains('dark')
            ? 'rgba(255, 255, 255, 0.05)'
            : 'rgba(0, 0, 0, 0.08)';
    });
}
