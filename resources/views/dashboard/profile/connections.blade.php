@extends('layouts.profile')

@section('profile-title', 'Connected Accounts')

@section('profile-breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Connected Accounts</span>
    </li>
@endsection

@section('profile-content')
    <div class="space-y-8">
        <!-- Single Sign-On Section -->
        <div>
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">Single Sign-On</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Connect your accounts for seamless authentication
                </p>
            </div>

            <div class="space-y-4">
                @foreach (['google', 'github', 'microsoft'] as $provider)
                    <div
                        class="group relative bg-white dark:bg-zinc-800/50 rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="p-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div
                                        class="flex-shrink-0 p-2 rounded-lg bg-white dark:bg-zinc-700 border border-zinc-200/70 dark:border-zinc-600/50">
                                        @if ($provider === 'google')
                                            <svg class="w-6 h-6" viewBox="0 0 24 24">
                                                <svg class="w-6 h-6" viewBox="0 0 24 24">
                                                    <path
                                                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                                        fill="#4285F4" />
                                                    <path
                                                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                                        fill="#34A853" />
                                                    <path
                                                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                                                        fill="#FBBC05" />
                                                    <path
                                                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                                        fill="#EA4335" />
                                                </svg>
                                            </svg>
                                        @elseif($provider === 'github')
                                            <svg class="w-6 h-6 text-white" viewBox="0 0 24 24">
                                                <svg class="w-6 h-6" viewBox="0 0 24 24">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"
                                                        fill="currentColor" />
                                                </svg>

                                            </svg>
                                        @else
                                            <svg class="w-6 h-6" viewBox="0 0 23 23">
                                                <svg class="w-6 h-6" viewBox="0 0 23 23">
                                                    <path fill="#f25022" d="M1 1h10v10H1z" />
                                                    <path fill="#00a4ef" d="M1 12h10v10H1z" />
                                                    <path fill="#7fba00" d="M12 1h10v10H12z" />
                                                    <path fill="#ffb900" d="M12 12h10v10H12z" />
                                                </svg>

                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="text-base font-medium text-zinc-900 dark:text-white capitalize">
                                            {{ $provider }}</h3>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                                            Sign in with your {{ $provider }} account
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-3">
                                    @if ($connectedAccounts[$provider])
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100/80 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                            <i data-lucide="check-circle" class="w-4 h-4 mr-1.5"></i> Connected
                                        </span>
                                        <form action="{{ route('oauth.disconnect', ['provider' => $provider]) }}"
                                            method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm font-medium flex items-center">
                                                <i data-lucide="trash-2" class="w-4 h-4 mr-1.5"></i> Disconnect
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('auth.oauth.redirect', ['provider' => $provider]) }}"
                                            class="btn-secondary px-4 py-2 rounded-lg flex items-center space-x-2 hover:bg-zinc-50/70 dark:hover:bg-zinc-700/30 transition-colors">
                                            <i data-lucide="link" class="w-4 h-4"></i>
                                            <span>Connect</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- <!-- Integrations Section -->
    <div>
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">Integrations</h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Connect third-party services for enhanced functionality
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            @foreach (['slack', 'discord'] as $service)
            <div class="bg-white dark:bg-zinc-800/50 rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 shadow-sm hover:shadow-md transition-shadow duration-200">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0 p-2 rounded-lg bg-white dark:bg-zinc-700 border border-zinc-200/70 dark:border-zinc-600/50">
                                @if ($service === 'slack')
                                <svg class="w-6 h-6" viewBox="0 0 24 24">
                                    <!-- Slack SVG paths -->
                                </svg>
                                @else
                                <svg class="w-6 h-6" viewBox="0 0 127.14 96.36">
                                    <!-- Discord SVG paths -->
                                </svg>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-base font-medium text-zinc-900 dark:text-white capitalize">{{ $service }}</h3>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                                    {{ $service === 'slack' ? 'Receive test notifications' : 'Connect your Discord server' }}
                                </p>
                            </div>
                        </div>
                        <button type="button" class="btn-secondary px-4 py-2 rounded-lg flex items-center space-x-2 hover:bg-zinc-50/70 dark:hover:bg-zinc-700/30 transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Connect</span>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- API Keys Section -->
    <div>
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">API Keys</h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Manage programmatic access to your account
            </p>
        </div>

        <div class="bg-white dark:bg-zinc-800/50 rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 shadow-sm">
            <div class="p-5">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-3 sm:space-y-0">
                    <div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Active Keys</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                            {{ count($apiKeys) }} active key{{ count($apiKeys) !== 1 ? 's' : '' }}
                        </p>
                    </div>
                    <button x-data @click="$dispatch('open-modal', 'create-api-key')"
                            class="btn-primary px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>New Key</span>
                    </button>
                </div>

                <div class="overflow-x-auto rounded-lg border border-zinc-200/50 dark:border-zinc-700/50">
                    <table class="min-w-full divide-y divide-zinc-200/50 dark:divide-zinc-700/50">
                        <!-- Table headers and rows -->
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Webhooks Section -->
    <div>
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">Webhooks</h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Configure event-driven notifications
            </p>
        </div>

        <div class="bg-white dark:bg-zinc-800/50 rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 shadow-sm">
            <div class="p-5">
                <!-- Webhook management content -->
            </div>
        </div>
    </div> --}}
    </div>
    {{--
<!-- API Key Creation Modal -->
<x-modal name="create-api-key" maxWidth="lg">
    <div class="p-6">
        <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4">Create API Key</h3>
        <form class="space-y-6">
            <!-- Form fields -->
            <div class="flex justify-end space-x-3">
                <button type="button" @click="show = false" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Create Key</button>
            </div>
        </form>
    </div>
</x-modal> --}}
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();

            // Confirm destructive actions
            document.querySelectorAll('[data-confirm]').forEach(element => {
                element.addEventListener('click', event => {
                    if (!confirm(element.dataset.confirm)) {
                        event.preventDefault();
                    }
                });
            });
        });
    </script>
@endpush
