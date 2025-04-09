import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css',
                'resources/css/auth.css',
                'resources/js/app.js',
                'resources/js/auth/theme-toggle.js',
                'resources/js/auth/animated-background.js',
                'resources/js/auth/form-animations.js',
                'resources/js/auth/email-verification.js',
                'resources/js/auth/password-strength.js',
                'resources/js/auth/phone-verification.js',],
            refresh: true,
        }),
    ],
});
