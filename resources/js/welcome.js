/**
 * Welcome page interactions and animations
 */
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });

        // Close mobile menu when clicking a navigation link
        const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
        mobileNavLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileMenu.classList.add('hidden');
            });
        });
    }

    // Theme toggle functionality
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        });
    }

    // Smooth scrolling for navigation links
    const navLinks = document.querySelectorAll('.nav-link, .mobile-nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Only apply to hash links on the same page
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);

                if (targetElement) {
                    // Offset for fixed header
                    const headerHeight = document.querySelector('header').offsetHeight;
                    const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;

                    window.scrollTo({
                        top: targetPosition - headerHeight,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // Animate elements as they enter the viewport
    const animateElements = document.querySelectorAll('.feature-card, .step-number, .testimonial-card, .pricing-card');
    animateElements.forEach(element => {
        element.classList.add('animate-on-scroll');
    });

    // Initialize intersection observer for scroll animations
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.2
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationDelay = '0.1s';
                entry.target.style.animationPlayState = 'running';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all elements with animations
    document.querySelectorAll('.animate-on-scroll').forEach(element => {
        element.style.animationPlayState = 'paused';
        observer.observe(element);
    });

    // Sticky header background change on scroll
    const header = document.querySelector('header');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 10) {
            header.classList.add('bg-white/95', 'dark:bg-zinc-900/95', 'backdrop-blur-sm', 'shadow-sm');
        } else {
            header.classList.remove('bg-white/95', 'dark:bg-zinc-900/95', 'backdrop-blur-sm', 'shadow-sm');
        }
    });

    // FAQ accordion functionality (backup for Alpine.js)
    const faqQuestions = document.querySelectorAll('.faq-question');
    if (faqQuestions.length > 0 && typeof Alpine === 'undefined') {
        faqQuestions.forEach(question => {
            question.addEventListener('click', () => {
                const answer = question.nextElementSibling;
                const isOpen = answer.style.display === 'block';

                // Close all FAQ answers
                document.querySelectorAll('.faq-answer').forEach(el => {
                    el.style.display = 'none';
                });

                document.querySelectorAll('.faq-item').forEach(el => {
                    el.classList.remove('faq-active');
                });

                // Toggle current answer
                if (!isOpen) {
                    answer.style.display = 'block';
                    question.closest('.faq-item').classList.add('faq-active');
                }
            });
        });
    }

    // Add parallax effect to hero section
    const hero = document.getElementById('hero');
    if (hero) {
        window.addEventListener('scroll', () => {
            const scrollPosition = window.scrollY;
            if (scrollPosition < window.innerHeight) {
                const parallaxOffset = scrollPosition * 0.4;
                hero.style.backgroundPosition = `center ${parallaxOffset}px`;
            }
        });
    }
});
