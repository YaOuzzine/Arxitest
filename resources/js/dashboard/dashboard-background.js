document.addEventListener('DOMContentLoaded', function() {
    // Create animated background
    createAnimatedBackground();

    // Initialize dashboard interactions
    initDashboardInteractions();
});

function createAnimatedBackground() {
    // Remove existing background if any
    const existingBg = document.querySelector('.dashboard-background');
    if (existingBg) existingBg.remove();

    const background = document.createElement('div');
    background.className = 'dashboard-background';

    // Create animated circles with more subtle colors
    for (let i = 0; i < 5; i++) {
        const circle = document.createElement('div');
        circle.className = 'animated-circle';

        // Random size between 100px and 400px
        const size = Math.floor(Math.random() * 300) + 100;
        circle.style.width = `${size}px`;
        circle.style.height = `${size}px`;

        // Random position
        circle.style.left = `${Math.random() * 80 + 10}%`;
        circle.style.top = `${Math.random() * 80 + 10}%`;

        // Random animation duration and delay
        const duration = Math.floor(Math.random() * 20) + 15;
        const delay = Math.floor(Math.random() * 10);
        circle.style.animation = `float ${duration}s infinite ease-in-out ${delay}s`;

        // Random subtle color
        const colors = [
            'rgba(99, 102, 241, 0.05)',  // indigo
            'rgba(139, 92, 246, 0.05)',  // purple
            'rgba(59, 130, 246, 0.05)',  // blue
            'rgba(16, 185, 129, 0.05)',  // green
            'rgba(245, 158, 11, 0.05)'   // yellow
        ];
        circle.style.background = colors[Math.floor(Math.random() * colors.length)];

        background.appendChild(circle);
    }

    // Insert at the beginning of body to ensure it's behind everything
    document.body.insertBefore(background, document.body.firstChild);
}

function initDashboardInteractions() {
    // Add click effects to all clickable elements
    const clickableElements = document.querySelectorAll('[data-clickable]');
    clickableElements.forEach(element => {
        element.addEventListener('click', (e) => {
            // Only prevent default if it's actually a link
            if (element.tagName === 'A') {
                e.preventDefault();
            }

            // Create ripple effect
            const rect = element.getBoundingClientRect();
            const ripple = document.createElement('span');
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            ripple.classList.add('ripple-effect');

            element.appendChild(ripple);

            // Remove ripple after animation
            setTimeout(() => {
                ripple.remove();

                // Redirect after ripple animation completes
                if (element.tagName === 'A') {
                    window.location.href = '/dashboard';
                }
            }, 600);
        });
    });

    // Add hover effects to cards
    const cards = document.querySelectorAll('.simplified-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-2px)';
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });
}
