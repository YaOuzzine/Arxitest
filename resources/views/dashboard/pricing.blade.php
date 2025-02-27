{{-- resources/views/pages/pricing.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pricing - Arxitest</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    @include('components.nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Simple, Transparent Pricing</h1>
            <p class="text-xl text-gray-600 mb-8">Only pay for what you use with our flexible pricing plans</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
            <!-- Free Trial -->
            <div class="bg-white rounded-lg shadow-sm p-8">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Free Trial</h3>
                <p class="text-4xl font-bold mb-6">$0<span class="text-gray-500 text-lg font-normal">/month</span></p>
                <ul class="space-y-4 mb-8">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        1 Concurrent Container
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Basic Analytics
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Email Support
                    </li>
                </ul>
                <a href="{{ route('register') }}" class="block w-full text-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-blue-50 hover:bg-blue-100">
                    Start Free Trial
                </a>
            </div>

            <!-- Team -->
            <div class="bg-blue-600 rounded-lg shadow-lg p-8 transform scale-105">
                <h3 class="text-xl font-semibold text-white mb-4">Team</h3>
                <p class="text-4xl font-bold text-white mb-6">$99<span class="text-blue-200 text-lg font-normal">/month</span></p>
                <ul class="space-y-4 mb-8 text-white">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        5 Concurrent Containers
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Advanced Analytics
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Priority Support
                    </li>
                </ul>
                <a href="{{ route('register') }}" class="block w-full text-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50">
                    Get Started
                </a>
            </div>

            <!-- Enterprise -->
            <div class="bg-white rounded-lg shadow-sm p-8">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Enterprise</h3>
                <p class="text-4xl font-bold mb-6">Custom<span class="text-gray-500 text-lg font-normal">/month</span></p>
                <ul class="space-y-4 mb-8">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Unlimited Containers
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Custom Analytics
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        24/7 Support
                    </li>
                </ul>
                <a href="{{ route('contact') }}" class="block w-full text-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-blue-50 hover:bg-blue-100">
                    Contact Sales
                </a>
            </div>
        </div>
    </div>
</body>
</html>
