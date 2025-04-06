@extends('layouts.app')

@section('title', 'Welcome to Arxitest | Intelligent Test Automation Platform')

@section('content')
<div class="relative overflow-hidden">
    <!-- Hero Section -->
    <section id="hero" class="relative py-20 md:py-32 bg-gradient-to-br from-zinc-50 to-zinc-100 dark:from-zinc-900 dark:to-zinc-800">
        <div class="absolute inset-0 bg-grid-pattern opacity-10 dark:opacity-5"></div>
        <div class="container mx-auto px-6 relative z-10">
            <div class="flex flex-col lg:flex-row items-center">
                <div class="w-full lg:w-1/2 lg:pr-12">
                    <h1 class="text-4xl md:text-5xl xl:text-6xl font-bold text-zinc-800 dark:text-white leading-tight mb-6">
                        Intelligent Test Automation for Everyone
                    </h1>
                    <p class="text-lg md:text-xl text-zinc-600 dark:text-zinc-300 mb-8">
                        Arxitest streamlines the software testing lifecycle, making QA accessible for teams of all experience levels. With AI-powered test generation and containerized execution, quality assurance has never been simpler.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('register') }}" class="btn-primary text-center">
                            Get Started Free
                        </a>
                        <a href="#how-it-works" class="btn-secondary text-center flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polygon points="10 8 16 12 10 16 10 8"></polygon>
                            </svg>
                            See How It Works
                        </a>
                    </div>
                </div>
                <div class="w-full lg:w-1/2 mt-12 lg:mt-0">
                    <div class="relative border-8 border-white dark:border-zinc-700 rounded-2xl shadow-xl overflow-hidden">
                        <img src="{{ asset('images/hero-platform-preview.PNG') }}" alt="Arxitest Platform Preview" class="w-full">
                        <!-- Placeholder: A dashboard view of the Arxitest platform showing test automation in action -->
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-white dark:from-zinc-900 to-transparent"></div>
    </section>

    {{-- <!-- Trusted By Section -->
    <section class="py-12 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
        <div class="container mx-auto px-6">
            <p class="text-center text-zinc-500 dark:text-zinc-400 mb-8">Trusted by innovative teams</p>
            <div class="flex flex-wrap justify-center items-center gap-x-12 gap-y-8">
                <!-- Company logos should be grayscale and adjust color in dark mode -->
                <img src="{{ asset('images/clients/client-1.svg') }}" alt="Client Logo" class="h-8 md:h-10 opacity-60 dark:opacity-40 grayscale">
                <img src="{{ asset('images/clients/client-2.svg') }}" alt="Client Logo" class="h-8 md:h-10 opacity-60 dark:opacity-40 grayscale">
                <img src="{{ asset('images/clients/client-3.svg') }}" alt="Client Logo" class="h-8 md:h-10 opacity-60 dark:opacity-40 grayscale">
                <img src="{{ asset('images/clients/client-4.svg') }}" alt="Client Logo" class="h-8 md:h-10 opacity-60 dark:opacity-40 grayscale">
                <img src="{{ asset('images/clients/client-5.svg') }}" alt="Client Logo" class="h-8 md:h-10 opacity-60 dark:opacity-40 grayscale">
                <!-- Placeholder: 5 grayscale company logos from clients -->
            </div>
        </div>
    </section> --}}

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white dark:bg-zinc-900">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-zinc-800 dark:text-white mb-4">Key Platform Capabilities</h2>
                <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-3xl mx-auto">Arxitest combines AI, containerization, and intuitive workflows to make testing accessible to everyone.</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card">
                    <div class="feature-icon bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="16 18 22 12 16 6"></polyline>
                            <polyline points="8 6 2 12 8 18"></polyline>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-white mb-3">AI-Assisted Test Creation</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">Generate test scripts automatically from your Jira stories and acceptance criteria. Our AI analyzes requirements and creates Selenium or Cypress tests.</p>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card">
                    <div class="feature-icon bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                            <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                            <line x1="6" y1="6" x2="6.01" y2="6"></line>
                            <line x1="6" y1="18" x2="6.01" y2="18"></line>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-white mb-3">Containerized Execution</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">Run tests in isolated Docker containers with consistent environments. Eliminate "works on my machine" problems and ensure reliable test results.</p>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card">
                    <div class="feature-icon bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-white mb-3">Deep Jira Integration</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">Connect seamlessly with Jira to fetch requirements and push test results back, creating a complete feedback loop for your QA process.</p>
                </div>

                <!-- Feature 4 -->
                <div class="feature-card">
                    <div class="feature-icon bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-white mb-3">Real-Time Monitoring</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">Track test execution progress, view detailed logs, and receive notifications when tests complete or fail. Stay informed at every step.</p>
                </div>

                <!-- Feature 5 -->
                <div class="feature-card">
                    <div class="feature-icon bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-white mb-3">Parallel Test Runs</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">Run multiple tests simultaneously to save time and get faster feedback. Scale up based on your needs with our pay-as-you-go model.</p>
                </div>

                <!-- Feature 6 -->
                <div class="feature-card">
                    <div class="feature-icon bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-white mb-3">Beginner-Friendly</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">Use built-in templates and tutorials to help new teams get started quickly with test automation, even with limited QA experience.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 bg-zinc-50 dark:bg-zinc-800">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-zinc-800 dark:text-white mb-4">How Arxitest Works</h2>
                <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-3xl mx-auto">See how Arxitest transforms your testing workflow in three simple steps.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-12">
                <!-- Step 1 -->
                <div class="flex flex-col items-center text-center">
                    <div class="step-number">1</div>
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-white mt-6 mb-3">Connect Your Project</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 mb-6">Integrate with Jira, GitHub, or other tools to import user stories and requirements.</p>
                    <div class="relative w-full rounded-lg overflow-hidden shadow-lg">
                        <img src="{{ asset('images/how-it-works-1.PNG') }}" alt="Connect to Jira" class="w-full">
                        <!-- Placeholder: Screenshot showing Jira integration setup screen -->
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="flex flex-col items-center text-center">
                    <div class="step-number">2</div>
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-white mt-6 mb-3">Generate & Edit Tests</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 mb-6">Our AI creates test scripts from your requirements, which you can review and customize.</p>
                    <div class="relative w-full rounded-lg overflow-hidden shadow-lg">
                        <img src="{{ asset('images/how-it-works-2.PNG') }}" alt="AI Test Generation" class="w-full">
                        <!-- Placeholder: Screenshot showing the AI test generation interface with an example script -->
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="flex flex-col items-center text-center">
                    <div class="step-number">3</div>
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-white mt-6 mb-3">Execute & Monitor</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 mb-6">Run tests in parallel containers and get detailed reports on test results and coverage.</p>
                    <div class="relative w-full rounded-lg overflow-hidden shadow-lg">
                        <img src="{{ asset('images/how-it-works-3.png') }}" alt="Test Execution Dashboard" class="w-full h-30">
                        <!-- Placeholder: Screenshot showing test execution dashboard with reports and metrics -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 bg-white dark:bg-zinc-900">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-zinc-800 dark:text-white mb-4">What Our Team Says</h2>
                <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-3xl mx-auto">Teams of all sizes will be streamlining their QA processes with Arxitest.</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="testimonial-card">
                    <div class="flex items-center mb-4">
                        <div class="h-12 w-12 rounded-full overflow-hidden mr-4">
                            <img src="{{ asset('images/testimonials/Amine-Abouaomar-1.webp') }}" alt="User Avatar" class="h-full w-full object-cover">
                            <!-- Placeholder: Professional headshot of a person -->
                        </div>
                        <div>
                            <h4 class="font-semibold text-zinc-800 dark:text-white">Dr. Amine Abouaomar</h4>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Supervisor</p>
                        </div>
                    </div>
                    <p class="text-zinc-600 dark:text-zinc-300">"Arxitest will transform the testing process. It will cut test creation time by 70% and significantly increased our test coverage with minimal effort."</p>
                </div>

                <!-- Testimonial 2 -->
                <div class="testimonial-card">
                    <div class="flex items-center mb-4">
                        <div class="h-12 w-12 rounded-full overflow-hidden mr-4">
                            <img src="{{ asset('images/testimonials/yasser.jpeg') }}" alt="User Avatar" class="h-full w-full object-cover">
                            <!-- Placeholder: Professional headshot of a person -->
                        </div>
                        <div>
                            <h4 class="font-semibold text-zinc-800 dark:text-white">Yasser Ouzzine</h4>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Dev</p>
                        </div>
                    </div>
                    <p class="text-zinc-600 dark:text-zinc-300">"For any startup with no dedicated QA team, Arxitest will allow developers to implement robust testing without specialized expertise."</p>
                </div>

                {{-- <!-- Testimonial 3 -->
                <div class="testimonial-card">
                    <div class="flex items-center mb-4">
                        <div class="h-12 w-12 rounded-full overflow-hidden mr-4">
                            <img src="{{ asset('images/testimonials/user-3.jpg') }}" alt="User Avatar" class="h-full w-full object-cover">
                            <!-- Placeholder: Professional headshot of a person -->
                        </div>
                        <div>
                            <h4 class="font-semibold text-zinc-800 dark:text-white">Maria Rodriguez</h4>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Engineering Manager at EnterpriseApp</p>
                        </div>
                    </div>
                    <p class="text-zinc-600 dark:text-zinc-300">"The containerized execution approach eliminated environment inconsistencies that were plaguing our test reliability. Now we have consistent, reliable test results every time."</p>
                </div> --}}
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 bg-zinc-50 dark:bg-zinc-800">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-zinc-800 dark:text-white mb-4">Simple, Transparent Pricing</h2>
                <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-3xl mx-auto">Only pay for what you use with our flexible pricing options.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Free Trial -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3 class="text-2xl font-bold text-zinc-800 dark:text-white mb-4">Free Trial</h3>
                        <div class="flex items-end mb-6">
                            <span class="text-4xl font-bold text-zinc-800 dark:text-white">$0</span>
                            <span class="text-zinc-500 dark:text-zinc-400 ml-2 mb-1">/month</span>
                        </div>
                        <p class="text-zinc-600 dark:text-zinc-300 mb-6">Perfect for exploring Arxitest features</p>
                    </div>
                    <ul class="pricing-features">
                        <li>Limited test concurrency (1 container)</li>
                        <li>Basic analytics dashboard</li>
                        <li>Standard email support</li>
                        <li>30-day trial period</li>
                    </ul>
                    <a href="{{ route('register') }}" class="pricing-cta bg-zinc-200 hover:bg-zinc-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-800 dark:text-white">
                        Start Free Trial
                    </a>
                </div>

                <!-- Team Edition -->
                <div class="pricing-card pricing-popular">
                    <div class="pricing-popular-badge">Popular</div>
                    <div class="pricing-header">
                        <h3 class="text-2xl font-bold text-zinc-800 dark:text-white mb-4">Team Edition</h3>
                        <div class="flex items-end mb-6">
                            <span class="text-4xl font-bold text-zinc-800 dark:text-white">$49</span>
                            <span class="text-zinc-500 dark:text-zinc-400 ml-2 mb-1">/month</span>
                        </div>
                        <p class="text-zinc-600 dark:text-zinc-300 mb-6">Ideal for small to mid-sized teams</p>
                    </div>
                    <ul class="pricing-features">
                        <li>Moderate concurrency (up to 5 containers)</li>
                        <li>Advanced analytics and reporting</li>
                        <li>AI-based test generation</li>
                        <li>Priority email support</li>
                        <li>30-day data retention</li>
                    </ul>
                    <a href="{{ route('register') }}" class="pricing-cta bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-100 dark:hover:bg-white text-white dark:text-zinc-800">
                        Start 14-Day Trial
                    </a>
                </div>

                <!-- Enterprise Edition -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3 class="text-2xl font-bold text-zinc-800 dark:text-white mb-4">Enterprise</h3>
                        <div class="flex items-end mb-6">
                            <span class="text-2xl font-bold text-zinc-800 dark:text-white">Pay as you go</span>
                        </div>
                        <p class="text-zinc-600 dark:text-zinc-300 mb-6">For larger teams with dynamic needs</p>
                    </div>
                    <ul class="pricing-features">
                        <li>Unlimited concurrency</li>
                        <li>Pay only for container hours used</li>
                        <li>Custom LLM fine-tuning assistance</li>
                        <li>24/7 priority support</li>
                        <li>90-day data retention</li>
                        <li>Custom integrations</li>
                    </ul>
                    <a href="{{ route('register') }}" class="pricing-cta bg-zinc-200 hover:bg-zinc-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-800 dark:text-white">
                        Contact Sales
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-br from-zinc-800 to-zinc-900 text-white">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to Transform Your Testing Process?</h2>
                <p class="text-xl text-zinc-300 mb-8">Start automating your tests today with Arxitest's intelligent platform.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="btn-cta-primary text-center">
                        Get Started Free
                    </a>
                    <a href="#" class="btn-cta-secondary text-center">
                        Schedule a Demo
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20 bg-white dark:bg-zinc-900">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-zinc-800 dark:text-white mb-4">Frequently Asked Questions</h2>
                <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-3xl mx-auto">Find answers to common questions about Arxitest.</p>
            </div>

            <div class="max-w-3xl mx-auto" x-data="{selected:null}">
                <!-- FAQ Item 1 -->
                <div class="faq-item" x-data="{id: 1}" :class="{'faq-active': selected == 1}">
                    <button @click="selected !== 1 ? selected = 1 : selected = null" class="faq-question">
                        <span>What programming languages and frameworks does Arxitest support?</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition-transform" :class="{'rotate-45': selected == 1}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </button>
                    <div class="faq-answer" x-show="selected == 1" x-collapse>
                        <p>Arxitest currently supports test automation with Selenium (Python) and Cypress. Our AI can generate test scripts in these frameworks from your requirements. We're continuously expanding our framework support based on user feedback.</p>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="faq-item" x-data="{id: 2}" :class="{'faq-active': selected == 2}">
                    <button @click="selected !== 2 ? selected = 2 : selected = null" class="faq-question">
                        <span>How does the pay-as-you-go pricing work?</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition-transform" :class="{'rotate-45': selected == 2}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </button>
                    <div class="faq-answer" x-show="selected == 2" x-collapse>
                        <p>With our pay-as-you-go model, you're only charged for the actual container hours used during test execution. This means you pay only when tests are running. We provide usage dashboards to help you monitor costs and optimize your testing strategy.</p>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="faq-item" x-data="{id: 3}" :class="{'faq-active': selected == 3}">
                    <button @click="selected !== 3 ? selected = 3 : selected = null" class="faq-question">
                        <span>Can Arxitest integrate with my existing CI/CD pipeline?</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition-transform" :class="{'rotate-45': selected == 3}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </button>
                    <div class="faq-answer" x-show="selected == 3" x-collapse>
                        <p>Yes, Arxitest provides a comprehensive REST API that allows you to trigger test runs, retrieve results, and manage containers from your existing CI/CD tools. We also offer webhook callbacks for events such as test completion or container failures, making it easy to integrate with Jenkins, GitHub Actions, GitLab CI, and more.</p>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="faq-item" x-data="{id: 4}" :class="{'faq-active': selected == 4}">
                    <button @click="selected !== 4 ? selected = 4 : selected = null" class="faq-question">
                        <span>Is my test data secure with Arxitest?</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition-transform" :class="{'rotate-45': selected == 4}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </button>
                    <div class="faq-answer" x-show="selected == 4" x-collapse>
                        <p>Absolutely. We take security seriously. Each test runs in its own isolated container, and we encrypt all sensitive data in transit and at rest. We also support integration with secure vaults for storing secrets, and our platform offers granular access controls to protect your test assets.</p>
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="faq-item" x-data="{id: 5}" :class="{'faq-active': selected == 5}">
                    <button @click="selected !== 5 ? selected = 5 : selected = null" class="faq-question">
                        <span>Do I need to be a testing expert to use Arxitest?</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition-transform" :class="{'rotate-45': selected == 5}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </button>
                    <div class="faq-answer" x-show="selected == 5" x-collapse>
                        <p>Not at all! Arxitest is designed to be accessible for teams of all experience levels. Our AI-assisted test generation and beginner-friendly templates make it easy to get started, even if you're new to testing. We provide comprehensive documentation and tutorials to help you along the way.</p>
                    </div>
                </div>

                <!-- FAQ Item 6 -->
                <div class="faq-item" x-data="{id: 6}" :class="{'faq-active': selected == 6}">
                    <button @click="selected !== 6 ? selected = 6 : selected = null" class="faq-question">
                        <span>How accurate is the AI test generation?</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition-transform" :class="{'rotate-45': selected == 6}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </button>
                    <div class="faq-answer" x-show="selected == 6" x-collapse>
                        <p>Our AI test generation is built on a fine-tuned LLM model specifically trained for test script creation. It typically creates tests that cover 80-90% of requirements directly from your user stories and acceptance criteria. You can then review and edit these tests as needed. The AI continuously improves as it learns from your edits and feedback.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
