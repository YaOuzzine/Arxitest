{{-- resources/views/pages/privacy.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy & Terms - Arxitest</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    @include('components.nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Privacy Policy & Terms</h1>

            <div class="prose prose-blue max-w-none">
                <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Privacy Policy</h2>
                <p class="mb-4">At Arxitest, we take your privacy seriously. This privacy policy describes how we collect, use, and protect your personal information.</p>

                <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">Information We Collect</h3>
                <ul class="list-disc pl-6 mb-4">
                    <li>Account information (name, email, password)</li>
                    <li>Usage data and test execution metrics</li>
                    <li>Technical information about your testing environment</li>
                    <li>Payment information (processed securely by our payment provider)</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">How We Use Your Information</h3>
                <ul class="list-disc pl-6 mb-4">
                    <li>To provide and improve our services</li>
                    <li>To communicate with you about your account</li>
                    <li>To send important updates and notifications</li>
                    <li>To analyze and optimize platform performance</li>
                </ul>

                <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Terms of Service</h2>
                <p class="mb-4">By using Arxitest, you agree to these terms of service. Please read them carefully.</p>

                <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">Usage Terms</h3>
                <ul class="list-disc pl-6 mb-4">
                    <li>You must maintain the security of your account credentials</li>
                    <li>You agree not to misuse or abuse the platform</li>
                    <li>You must comply with all applicable laws and regulations</li>
                    <li>You are responsible for all activity under your account</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">Service Level Agreement</h3>
                <ul class="list-disc pl-6 mb-4">
                    <li>We strive for 99.9% uptime</li>
                    <li>Support response times vary by subscription tier</li>
                    <li>Data is backed up daily</li>
                    <li>Security updates are applied promptly</li>
                </ul>

                <p class="mt-8 text-sm text-gray-600">Last updated: February 24, 2025</p>
            </div>
        </div>
    </div>
</body>
</html>
