@extends('layouts.dashboard')

@section('title', 'Settings')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Settings</span>
    </li>
@endsection

@section('content')
    {{-- Add a container for subtle animations on load --}}
    <div class="h-full space-y-8 animate-fade-in">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">Settings</h1>
            <p class="lg:text-lg text-zinc-600 dark:text-zinc-400">
                Manage your account and application preferences.
                @isset($team)
                    <span class="font-medium">Current Team: {{ $team->name }}</span>
                @endisset
            </p>
        </div>

        <!-- Settings Sections Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">

            {{-- Account Settings Card --}}
            <a href="{{ route('dashboard.profile.show') }}"
               class="p-5 settings-card group animate-pop-in bg-gradient-to-br from-indigo-600 to-blue-600 dark:from-indigo-700 dark:to-blue-700"
               style="--delay: 0.1s;">
                <div class="card-content">
                    <div class="icon-container bg-indigo-800/30 text-white dark:bg-indigo-600/50">
                         <i data-lucide="user-cog" class="w-8 h-8 transition-transform duration-300 group-hover:scale-110"></i>
                    </div>
                    <h3 class="title">Account Settings</h3>
                    <p class="description">Manage your personal profile, password, and basic details.</p>
                </div>
                 <div class="link-footer">Go to Settings <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1"></i></div>
            </a>

            {{-- Notification Settings Card --}}
             <a href="{{ route('dashboard.profile.notifications') }}"
                class="p-5 settings-card group animate-pop-in bg-gradient-to-br from-blue-600 to-teal-600 dark:from-blue-700 dark:to-teal-700"
                style="--delay: 0.2s;">
                 <div class="card-content">
                    <div class="icon-container bg-blue-800/30 text-white dark:bg-blue-600/50">
                        <i data-lucide="bell" class="w-8 h-8 transition-transform duration-300 group-hover:rotate-6"></i>
                    </div>
                    <h3 class="title">Notifications</h3>
                    <p class="description">Configure your email and push notification preferences.</p>
                </div>
                <div class="link-footer">Manage Notifications <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1"></i></div>
            </a>

            {{-- Security Settings Card --}}
            <a href="{{ route('dashboard.profile.security') }}"
               class="p-5 settings-card group animate-pop-in bg-gradient-to-br from-green-600 to-emerald-600 dark:from-green-700 dark:to-emerald-700"
               style="--delay: 0.3s;">
                <div class="card-content">
                    <div class="icon-container bg-green-800/30 text-white dark:bg-green-600/50">
                        <i data-lucide="shield" class="w-8 h-8 transition-transform duration-300 group-hover:scale-110"></i>
                    </div>
                    <h3 class="title">Security</h3>
                    <p class="description">Review active sessions and manage security settings.</p>
                </div>
                <div class="link-footer">Security Settings <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1"></i></div>
            </a>

             {{-- Connected Accounts Card --}}
             <a href="{{ route('dashboard.profile.connections') }}"
                class="p-5 settings-card group animate-pop-in bg-gradient-to-br from-purple-600 to-pink-600 dark:from-purple-700 dark:to-pink-700"
                style="--delay: 0.4s;">
                 <div class="card-content">
                    <div class="icon-container bg-purple-800/30 text-white dark:bg-purple-600/50">
                        <i data-lucide="link" class="w-8 h-8 transition-transform duration-300 group-hover:rotate-12"></i>
                    </div>
                    <h3 class="title">Connected Accounts</h3>
                    <p class="description">Link or disconnect your Google, GitHub, or Microsoft accounts.</p>
                </div>
                <div class="link-footer">Manage Connections <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1"></i></div>
            </a>

             {{-- Billing & Usage Card (Placeholder/Link) --}}
             <a href="#" onclick="alert('Billing & Usage page is under development!')"
                class="p-5 settings-card group animate-pop-in bg-gradient-to-br from-yellow-600 to-orange-600 dark:from-yellow-700 dark:to-orange-700"
                style="--delay: 0.5s;">
                 <div class="card-content">
                     <div class="icon-container bg-yellow-800/30 text-white dark:bg-yellow-600/50">
                        <i data-lucide="credit-card" class="w-8 h-8 transition-transform duration-300 group-hover:scale-110"></i>
                    </div>
                    <h3 class="title">Billing & Usage</h3>
                    <p class="description">Review your subscription plan, usage, and billing information.</p>
                 </div>
                <div class="link-footer">View Billing <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1"></i></div>
            </a>

             {{-- Developer Settings Card (Placeholder) --}}
             <a href="#" onclick="alert('Developer Settings are under development!')"
                class="p-5 settings-card group animate-pop-in bg-gradient-to-br from-red-600 to-rose-600 dark:from-red-700 dark:to-rose-700"
                style="--delay: 0.6s;">
                <div class="card-content">
                     <div class="icon-container bg-red-800/30 text-white dark:bg-red-600/50">
                        <i data-lucide="code-square" class="w-8 h-8 transition-transform duration-300 group-hover:scale-110"></i>
                    </div>
                    <h3 class="title">Developer Settings</h3>
                    <p class="description">Manage API keys and configure webhooks for integrations.</p>
                </div>
                <div class="link-footer">Coming Soon <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1"></i></div>
            </a>

             {{-- Add more setting categories as needed --}}

        </div>

    </div>
@endsection

{{-- Add custom styles for the settings cards --}}
@push('styles')
<style>
    /* New styles for Settings Cards */
    .settings-card {
        @apply block rounded-xl shadow-lg border border-white/20 dark:border-zinc-700/50 p-6 space-y-4 transition-all duration-300 hover:shadow-xl hover:translate-y-[-4px] overflow-hidden relative text-white; /* Base text color white */
        z-index: 1; /* Ensure content is above pseudo-element */
        text-decoration: none; /* Remove default underline from anchor tag */
    }

    /* Pseudo-element for background pattern overlay */
    .settings-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.10'/%3E%3C/svg%3E"); /* Subtle noise */
        opacity: 0.5; /* Adjust opacity */
        transition: opacity 0.3s ease;
        z-index: -1; /* Place behind content */
        border-radius: 0.75rem; /* Match card border-radius */
    }

    .settings-card:hover::before {
         opacity: 0.7; /* Enhance opacity on hover */
    }


    .settings-card .card-content {
        @apply space-y-4; /* Consistent spacing within card */
        position: relative; /* Ensure content is in standard flow */
        z-index: 1;
    }

    .settings-card .icon-container {
        @apply p-3 rounded-xl inline-flex shadow-sm group-hover:shadow-md transition-shadow duration-300;
        /* Opacity adjusted for better visibility on gradients */
        background-color: rgba(255, 255, 255, 0.2); /* Semi-transparent white */
        color: inherit; /* Icon color inherits from card (white) */
    }
     /* Specific icon background tweaks for dark mode if needed */
    .dark .settings-card .icon-container {
         background-color: rgba(0, 0, 0, 0.2); /* Semi-transparent black in dark mode */
    }


    .settings-card .title {
        @apply text-lg font-semibold text-white; /* Title always white */
    }

    .settings-card .description {
        @apply text-sm text-indigo-100 dark:text-indigo-200 min-h-[40px]; /* Lighter text for description */
    }

    .settings-card .link-footer {
         @apply mt-4 text-sm font-medium text-indigo-100 dark:text-indigo-200 group-hover:text-white transition-colors duration-200 flex items-center; /* Lighter color for link */
         position: relative;
         z-index: 1;
    }


    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes popIn {
         0% { opacity: 0; transform: scale(0.95) translateY(10px); }
        100% { opacity: 1; transform: scale(1) translateY(0); }
    }

    /* This is applied to the main content div @yield('content') */
    .animate-fade-in { animation: fadeIn 0.6s ease-out forwards; opacity: 0; }

    /* This is applied to individual cards with staggered delay */
    .animate-pop-in { animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; opacity: 0; animation-delay: var(--delay, 0s); }

</style>
@endpush

{{-- Add necessary scripts --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure Lucide icons are initialized after the DOM is ready
        // The layout likely does this, but this is a fallback
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

         // Any specific JS functionality for this view (e.g., modals, dynamic content) goes here.
         // Based on the plan, this view mainly relies on links to other pages, so minimal JS is needed here.
         // The onclick="alert(...)" for placeholder links uses basic JS already.
    });
</script>
@endpush
