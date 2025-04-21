@extends('layouts.dashboard')

@section('title', 'Integrations')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Integrations</span>
    </li>
@endsection

@section('content')
<div class="h-full">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div class="space-y-1">
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white tracking-tight">Connected Ecosystem</h1>
            <p class="text-zinc-600 dark:text-zinc-400 text-lg">
                Supercharge your workflow with seamless integrations
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <div class="animate-pulse w-3 h-3 rounded-full bg-green-500"></div>
            <span class="text-sm text-zinc-500 dark:text-zinc-400">3+ new integrations coming soon</span>
        </div>
    </div>

    <!-- Integration Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <!-- Jira Card -->
        <div class="group relative h-full">
            <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-cyan-500 rounded-2xl blur opacity-10 group-hover:opacity-20 transition duration-1000"></div>
            <div class="integration-card relative h-full bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden transition-all duration-300 hover:-translate-y-2">
                <div class="p-6">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-500 shadow-lg">
                            <svg class="h-8 w-8 text-white" viewBox="0 0 32 32" fill="currentColor">
                                <path d="M16.175 2.188c-3.313 0-6 2.688-6 6 0 3.313 2.688 6 6 6s6-2.688 6-6c0-3.313-2.688-6-6-6zm-12 12c-3.313 0-6 2.688-6 6 0 3.313 2.688 6 6 6 1.725 0 3.35-.75 4.5-2.031.45 1.275 1.5 2.25 2.813 2.25h5.363c1.313 0 2.363-.975 2.813-2.25 1.125 1.275 2.738 2.031 4.5 2.031 3.313 0 6-2.688 6-6 0-3.313-2.688-6-6-6-1.762 0-3.375.756-4.5 2.031-.45-1.275-1.5-2.25-2.813-2.25h-5.363c-1.313 0-2.363.975-2.813 2.25-1.15-1.281-2.775-2.031-4.5-2.031zm24 0c-3.313 0-6 2.688-6 6 0 3.313 2.688 6 6 6s6-2.688 6-6c0-3.313-2.688-6-6-6z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Jira Cloud</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Atlassian • Issue Tracking</p>
                        </div>
                    </div>
                    <p class="text-sm text-zinc-600 dark:text-zinc-300 mb-5 leading-relaxed">
                        Sync user stories, track test results, and automate workflows with Jira Cloud integration.
                    </p>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2 text-sm text-emerald-600 dark:text-emerald-400">
                            <i data-lucide="zap" class="w-4 h-4"></i>
                            <span>Real-time sync • OAuth 2.0 • Smart automation</span>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-zinc-50/50 dark:bg-zinc-800/20 border-t border-zinc-200/50 dark:border-zinc-700 flex justify-between items-center">
                    @if($jiraConnected)
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Connected</span>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('integrations.jira.import.options') }}" class="btn-secondary px-3 py-1.5 text-sm flex items-center space-x-1">
                                <i data-lucide="download" class="w-4 h-4"></i>
                                <span>Import</span>
                            </a>
                            <button @click="showDisconnectConfirm('jira')" class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors">
                                Disconnect
                            </button>
                        </div>
                    @else
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 rounded-full bg-zinc-400"></div>
                            <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Not connected</span>
                        </div>
                        <a href="{{ route('integrations.jira.redirect') }}" class="btn-primary px-4 py-1.5 text-sm flex items-center space-x-2 transition-transform hover:scale-105">
                            <i data-lucide="plug" class="w-4 h-4"></i>
                            <span>Connect</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- GitHub Card -->
        <div class="group relative h-full">
            <div class="absolute -inset-1 bg-gradient-to-r from-purple-600 to-fuchsia-500 rounded-2xl blur opacity-10 group-hover:opacity-20 transition duration-1000"></div>
            <div class="integration-card relative h-full bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden transition-all duration-300 hover:-translate-y-2">
                <div class="p-6">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-purple-600 to-fuchsia-500 shadow-lg">
                            <svg class="h-8 w-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.463 2 11.97c0 4.404 2.865 8.14 6.839 9.458.5.092.682-.216.682-.48 0-.236-.008-.864-.013-1.695-2.782.602-3.369-1.337-3.369-1.337-.454-1.151-1.11-1.458-1.11-1.458-.908-.618.069-.606.069-.606 1.003.07 1.531 1.027 1.531 1.027.892 1.524 2.341 1.084 2.91.828.092-.643.35-1.083.636-1.332-2.22-.251-4.555-1.107-4.555-4.927 0-1.088.39-1.979 1.029-2.675-.103-.252-.446-1.266.098-2.638 0 0 .84-.268 2.75 1.022A9.606 9.606 0 0112 6.82c.85.004 1.705.114 2.504.336 1.909-1.29 2.747-1.022 2.747-1.022.546 1.372.202 2.386.1 2.638.64.696 1.028 1.587 1.028 2.675 0 3.83-2.337 4.673-4.566 4.92.359.307.678.915.678 1.846 0 1.332-.012 2.407-.012 2.734 0 .267.18.577.688.48C19.137 20.107 22 16.373 22 11.969 22 6.463 17.522 2 12 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white">GitHub</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Microsoft • Version Control</p>
                        </div>
                    </div>
                    <p class="text-sm text-zinc-600 dark:text-zinc-300 mb-5 leading-relaxed">
                        Connect repositories, sync test scripts, and enable CI/CD automation with GitHub Actions.
                    </p>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2 text-sm text-purple-600 dark:text-purple-400">
                            <i data-lucide="git-branch" class="w-4 h-4"></i>
                            <span>Repo sync • Webhooks • Actions support</span>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-zinc-50/50 dark:bg-zinc-800/20 border-t border-zinc-200/50 dark:border-zinc-700 flex justify-between items-center">
                    @if($githubConnected)
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Connected</span>
                        </div>
                        <button class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors">
                            Disconnect
                        </button>
                    @else
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 rounded-full bg-zinc-400"></div>
                            <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Not connected</span>
                        </div>
                        <button class="btn-primary px-4 py-1.5 text-sm flex items-center space-x-2 opacity-50 cursor-not-allowed" title="Coming Q4 2023">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                            <span>Soon</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Coming Soon Card -->
        <div class="group relative h-full">
            <div class="absolute -inset-1 bg-gradient-to-r from-teal-600 to-emerald-500 rounded-2xl blur opacity-10 group-hover:opacity-20 transition duration-1000"></div>
            <div class="integration-card relative h-full bg-white dark:bg-zinc-900 rounded-2xl border-2 border-dashed border-zinc-300 dark:border-zinc-700 flex flex-col items-center justify-center p-6 text-center transition-all duration-300 hover:-translate-y-2 hover:border-zinc-400 dark:hover:border-zinc-600">
                <div class="mb-4 p-3 rounded-xl bg-gradient-to-br from-teal-600 to-emerald-500 shadow-lg">
                    <i data-lucide="plus" class="h-8 w-8 text-white"></i>
                </div>
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-2">More Connections</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">
                    We're adding new integrations every month
                </p>
                <div class="space-y-2 text-sm text-teal-600 dark:text-teal-400">
                    <div class="flex items-center space-x-2 justify-center">
                        <i data-lucide="slack" class="w-4 h-4"></i>
                        <i data-lucide="gitlab" class="w-4 h-4"></i>
                        <i data-lucide="docker" class="w-4 h-4"></i>
                    </div>
                    <p class="text-xs">Your wishlist: Jenkins, Slack, GitLab</p>
                </div>
                <button class="mt-4 text-sm text-teal-600 dark:text-teal-400 hover:text-teal-700 dark:hover:text-teal-300 flex items-center space-x-1 transition-colors">
                    <i data-lucide="megaphone" class="w-4 h-4"></i>
                    <span>Request Integration</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .integration-card {
        min-height: 320px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.05);
        backdrop-filter: blur(4px);
    }

    .btn-primary {
        @apply inline-flex items-center px-4 py-2 rounded-lg font-medium bg-gradient-to-br from-zinc-800 to-zinc-700 dark:from-zinc-700 dark:to-zinc-600 text-white shadow-sm hover:shadow-md transition-all duration-200 hover:scale-[1.02];
    }

    .btn-secondary {
        @apply inline-flex items-center px-3 py-1.5 rounded-lg font-medium bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors duration-200;
    }

    @keyframes cardEntrance {
        from {
            opacity: 0;
            transform: translateY(20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        lucide.createIcons();

        // Animate cards on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = `cardEntrance 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards`;
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.integration-card').forEach((card) => {
            card.style.opacity = '0';
            observer.observe(card);
        });

        // Hover effect enhancement
        document.querySelectorAll('.integration-card').forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                card.style.setProperty('--mouse-x', `${x}px`);
                card.style.setProperty('--mouse-y', `${y}px`);
            });
        });
    });

    function showDisconnectConfirm(service) {
        // Implement your disconnect confirmation modal here
        console.log(`Disconnect ${service}`);
    }
</script>
@endpush
