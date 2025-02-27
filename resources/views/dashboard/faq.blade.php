{{-- resources/views/pages/faq.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FAQ - Arxitest</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    @include('components.nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Frequently Asked Questions</h1>

            <div class="space-y-6">
                <!-- Question 1 -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">What is Arxitest?</h3>
                    <p class="text-gray-600">Arxitest is an intelligent test automation platform that helps teams automate their testing process using AI-assisted test creation, containerized execution, and comprehensive reporting.</p>
                </div>

                <!-- Question 2 -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">How does the AI test generation work?</h3>
                    <p class="text-gray-600">Our platform uses a fine-tuned LLAMA model to analyze your Jira user stories and acceptance criteria, automatically generating draft test scripts that can be reviewed and refined by your team.</p>
                </div>

                <!-- Question 3 -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">What frameworks do you support?</h3>
                    <p class="text-gray-600">We currently support Selenium (Python) and Cypress, with more frameworks coming soon. Our containerized execution environment ensures consistent test runs across different frameworks.</p>
                </div>

                <!-- Question 4 -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">How does pricing work?</h3>
                    <p class="text-gray-600">We offer a pay-as-you-go model where you only pay for the container hours you use. We have different tiers available, from free trial to enterprise, each with different levels of concurrency and support.</p>
                </div>

                <!-- Question 5 -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">How secure is my test data?</h3>
                    <p class="text-gray-600">We take security seriously. All test data is encrypted both in transit and at rest. Each test runs in its own isolated container, and we support secure credential management through vault integration.</p>
                </div>

                <!-- Question 6 -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">What kind of support do you offer?</h3>
                    <p class="text-gray-600">Support varies by tier. Free trial users get email support, Team users get priority email support, and Enterprise users get 24/7 dedicated support with guaranteed response times.</p>
                </div>

                <!-- Question 7 -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Can I try before I buy?</h3>
                    <p class="text-gray-600">Yes! We offer a free trial that includes 1 concurrent container and basic analytics. You can upgrade to a paid plan at any time.</p>
                </div>

                <!-- Contact Section -->
                <div class="mt-12 text-center">
                    <p class="text-gray-600 mb-4">Still have questions? We're here to help!</p>
                    <a href="mailto:support@arxitest.com" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Contact Support
                        <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
