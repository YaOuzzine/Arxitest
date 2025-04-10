document.addEventListener('DOMContentLoaded', function() {
    // Initialize the animated background
    initAnimatedBackground();

    // Add hover effects to all cards
    const cards = document.querySelectorAll('.card-hover');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-4px) scale(1.02)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });

    // Add click handlers for all links to redirect to dashboard
    const links = document.querySelectorAll('a[href="#"]');
    links.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.href = 'dashboard';
        });
    });
});

function initAnimatedBackground() {
    const background = document.getElementById('animated-background');
    if (!background) return;

    // Create canvas for particles
    const canvas = document.createElement('canvas');
    canvas.style.position = 'absolute';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    background.appendChild(canvas);

    const ctx = canvas.getContext('2d');
    canvas.width = background.offsetWidth;
    canvas.height = background.offsetHeight;

    // Particle system
    const particles = [];
    const particleCount = window.innerWidth < 768 ? 30 : 60;

    // Create particles
    for (let i = 0; i < particleCount; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            size: Math.random() * 3 + 1,
            speedX: Math.random() * 0.5 - 0.25,
            speedY: Math.random() * 0.5 - 0.25,
            color: getComputedStyle(document.documentElement).classList.contains('dark') ?
                  `rgba(255, 255, 255, ${Math.random() * 0.05 + 0.05})` :
                  `rgba(0, 0, 0, ${Math.random() * 0.05 + 0.05})`
        });
    }

    // Animation loop
    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Update and draw particles
        for (let i = 0; i < particles.length; i++) {
            const p = particles[i];

            // Update position
            p.x += p.speedX;
            p.y += p.speedY;

            // Bounce off edges
            if (p.x < 0 || p.x > canvas.width) p.speedX *= -1;
            if (p.y < 0 || p.y > canvas.height) p.speedY *= -1;

            // Draw particle
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
            ctx.fillStyle = p.color;
            ctx.fill();
        }

        requestAnimationFrame(animate);
    }

    // Start animation
    animate();

    // Handle resize
    window.addEventListener('resize', () => {
        canvas.width = background.offsetWidth;
        canvas.height = background.offsetHeight;
    });

    // Watch for theme changes to update particle colors
    const observer = new MutationObserver(() => {
        const isDark = getComputedStyle(document.documentElement).classList.contains('dark');
        particles.forEach(p => {
            p.color = isDark ?
                `rgba(255, 255, 255, ${Math.random() * 0.05 + 0.05})` :
                `rgba(0, 0, 0, ${Math.random() * 0.05 + 0.05})`;
        });
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
}
