@php
/**
 * @var \App\Models\Project $project
 */
@endphp

@extends('layouts.dashboard')

@section('title', 'Edit Project: ' . $project->name)

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects') }}"
           class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
            Projects
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.show', $project->id) }}"
           class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
            {{ $project->name }}
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Edit</span>
    </li>
@endsection

@section('content')
<div class="h-full" x-data="{
    projectName: {{ json_encode(old('name', $project->name)) }},
    projectDescription: {{ json_encode(old('description', $project->description ?? '')) }},
    // Safely access settings with defaults
    defaultFramework: {{ json_encode(old('default_framework', $project->settings['default_framework'] ?? 'selenium-python')) }},
    autoGenerateTests: {{ old('auto_generate_tests', $project->settings['auto_generate_tests'] ?? false) ? 'true' : 'false' }},
    isSubmitting: false,
    showNotification: false,
    notificationType: 'success',
    notificationMessage: '',
    frameworks: [
        { value: 'selenium-python', name: 'Selenium (Python)', description: 'Python-based web testing using Selenium WebDriver', icon: 'code' },
        { value: 'cypress', name: 'Cypress', description: 'JavaScript end-to-end testing framework', icon: 'command' },
        { value: 'playwright', name: 'Playwright', description: 'Node.js library for browser automation', icon: 'monitor-play' },
        { value: 'rest-assured', name: 'REST Assured', description: 'Java DSL for testing REST services', icon: 'server' }
    ],
    submitForm() {
        if (!this.projectName.trim()) {
            this.showNotificationMessage('error', 'Project name is required');
            return;
        }

        this.isSubmitting = true;
        const form = document.getElementById('project-form');
        form.submit();
    },
    showNotificationMessage(type, message) {
        this.notificationType = type;
        this.notificationMessage = message;
        this.showNotification = true;

        // Auto-hide after 5 seconds
        setTimeout(() => {
            this.showNotification = false;
        }, 5000);
    },
    hideNotification() {
        this.showNotification = false;
    }
}" x-init="$nextTick(() => {
    // Focus logic remains the same
    // document.getElementById('name').focus(); // Removed focus to avoid jumping on load for edit page

    // Notification logic remains the same
    @if(session('success'))
        showNotificationMessage('success', '{{ session('success') }}');
    @endif

    @if(session('error'))
        showNotificationMessage('error', '{{ session('error') }}');
    @endif

    @if($errors->any())
        showNotificationMessage('error', 'There were errors in your submission. Please check the form.');
    @endif
})">
    <!-- Animated Header -->
    <div class="mb-6 transform transition-all duration-300 ease-out"
         x-data="{ scrollY: 0 }"
         x-on:scroll.window="scrollY = window.scrollY"
         :class="scrollY > 50 ? 'opacity-90 scale-[0.99]' : ''">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white bg-gradient-to-r from-zinc-900 dark:from-zinc-100 to-zinc-600 dark:to-zinc-400 bg-clip-text text-transparent animate-fade-in-down">
                    Edit Project: <span class="bg-gradient-to-r from-blue-600 to-teal-400 dark:from-blue-400 dark:to-teal-300 bg-clip-text text-transparent">{{ $project->name }}</span>
                </h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 transition-opacity duration-300">
                    Update the details and settings for this project
                </p>
            </div>
            <div>
                {{-- Back button can go to project list or project show page --}}
                <a href="{{ route('dashboard.projects.show', $project->id) }}"
                   class="group inline-flex items-center px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-200 bg-white/70 dark:bg-zinc-800/50 hover:bg-white dark:hover:bg-zinc-700/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                    <i data-lucide="arrow-left" class="mr-2 -ml-1 w-4 h-4 transition-transform group-hover:-translate-x-1"></i>
                    Back to Project
                </a>
            </div>
        </div>
    </div>

    <!-- Floating Notification (Identical to create view) -->
    <div x-show="showNotification"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed bottom-6 right-6 z-50 max-w-sm w-full shadow-lg border rounded-xl p-4"
         :class="{
            'bg-green-50/80 border-green-200/50 dark:bg-green-900/30 dark:border-green-800/30': notificationType === 'success',
            'bg-red-50/80 border-red-200/50 dark:bg-red-900/30 dark:border-red-800/30': notificationType === 'error'
         }">
        <div class="flex items-start">
            <div x-show="notificationType === 'success'" class="flex-shrink-0 w-5 h-5 mr-3 text-green-600 dark:text-green-400">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div x-show="notificationType === 'error'" class="flex-shrink-0 w-5 h-5 mr-3 text-red-600 dark:text-red-400">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-medium mb-1"
                    :class="{
                        'text-green-800 dark:text-green-200': notificationType === 'success',
                        'text-red-800 dark:text-red-200': notificationType === 'error'
                    }">
                    <span x-show="notificationType === 'success'">Success</span>
                    <span x-show="notificationType === 'error'">Error</span>
                </h4>
                <p class="text-sm"
                   :class="{
                        'text-green-700/90 dark:text-green-300/90': notificationType === 'success',
                        'text-red-700/90 dark:text-red-300/90': notificationType === 'error'
                    }"
                   x-text="notificationMessage"></p>
            </div>
            <button @click="hideNotification" class="ml-4 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- Glassmorphism Form Container -->
    <div class="bg-white/70 dark:bg-zinc-800/50 rounded-2xl shadow-2xl border border-zinc-200/50 dark:border-zinc-700/30 backdrop-blur-lg transition-all duration-300 hover:shadow-2xl">
        <div class="p-8">
            <form id="project-form" method="POST" action="{{ route('dashboard.projects.update', $project->id) }}">
                @csrf
                @method('PUT') {{-- Use PUT or PATCH for updates --}}

                <input type="hidden" name="default_framework" x-model="defaultFramework">
                <input type="hidden" name="auto_generate_tests" :value="autoGenerateTests ? 1 : 0">

                <!-- Project Details Section -->
                <div class="space-y-8">
                    <div class="animate-fade-in-left">
                        <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 mb-2">Project Details</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Basic information about your project
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                        <!-- Project Name with Floating Label -->
                        <div class="sm:col-span-4 relative animate-fade-in-up delay-100">
                            <div class="relative">
                                <input type="text" name="name" id="name"
                                       x-model="projectName"
                                       class="peer h-12 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 pl-4 pr-12 text-zinc-700 dark:text-zinc-200 placeholder-transparent focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300"
                                       placeholder="My Project Name"
                                       required
                                       value="{{ old('name', $project->name) }}"> {{-- Use old() with fallback to project data --}}
                                <label for="name"
                                       class="absolute left-4 -top-2.5 px-1 bg-zinc-100/50 dark:bg-zinc-800/50 text-sm text-zinc-600 dark:text-zinc-400 transition-all duration-300 peer-placeholder-shown:top-3 peer-placeholder-shown:text-base peer-placeholder-shown:text-zinc-400 peer-focus:-top-2.5 peer-focus:text-sm peer-focus:text-zinc-600 dark:peer-focus:text-zinc-300">
                                    Project Name <span class="text-red-400">*</span>
                                </label>
                                @error('name')
                                    <div class="absolute right-4 top-3">
                                        <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 animate-pulse"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('name')
                                <p class="mt-2 text-sm text-red-500 animate-fade-in">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Project Description -->
                        <div class="sm:col-span-6 animate-fade-in-up delay-200">
                            <div class="relative">
                                <textarea id="description" name="description" rows="3"
                                          x-model="projectDescription"
                                          class="peer h-32 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 p-4 text-zinc-700 dark:text-zinc-200 placeholder-transparent focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300 resize-none"
                                          placeholder="A brief description of your project">{{ old('description', $project->description ?? '') }}</textarea> {{-- Use old() with fallback --}}
                                <label for="description"
                                       class="absolute left-4 -top-2.5 px-1 bg-zinc-100/50 dark:bg-zinc-800/50 text-sm text-zinc-600 dark:text-zinc-400 transition-all duration-300 peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-zinc-400 peer-focus:-top-2.5 peer-focus:text-sm peer-focus:text-zinc-600 dark:peer-focus:text-zinc-300">
                                    Description
                                </label>
                            </div>
                            <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                                Brief description of your project and its purpose
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Gradient Divider -->
                <div class="my-8 h-px bg-gradient-to-r from-transparent via-zinc-300/70 dark:via-zinc-600/50 to-transparent animate-scale-in-x"></div>

                <!-- Framework Configuration Section -->
                <div class="space-y-8">
                    <div class="animate-fade-in-left delay-300">
                        <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 mb-2">Testing Configuration</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Choose your preferred testing framework and settings
                        </p>
                    </div>

                    {{-- Framework selection logic remains the same, initial state is handled by x-data --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 animate-fade-in-up delay-400">
                        <template x-for="framework in frameworks" :key="framework.value">
                            <div @click="defaultFramework = framework.value"
                                 :class="defaultFramework === framework.value
                                     ? 'ring-2 ring-zinc-500 dark:ring-zinc-400 bg-zinc-100/50 dark:bg-zinc-700/30'
                                     : 'ring-1 ring-zinc-200/70 dark:ring-zinc-600/50 hover:ring-zinc-300 dark:hover:ring-zinc-500'"
                                 class="relative p-6 rounded-xl cursor-pointer transition-all duration-300 group transform hover:-translate-y-1">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0 p-2 bg-white/50 dark:bg-zinc-800/50 rounded-lg shadow-sm">
                                        <i :data-lucide="framework.icon" class="w-6 h-6 text-zinc-600 dark:text-zinc-300"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-base font-medium text-zinc-800 dark:text-zinc-200" x-text="framework.name"></h4>
                                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400" x-text="framework.description"></p>
                                    </div>
                                    <div class="flex items-center h-5 ml-2">
                                        <div :class="defaultFramework === framework.value ? 'bg-zinc-800 dark:bg-zinc-200' : 'bg-zinc-300 dark:bg-zinc-600'"
                                             class="w-4 h-4 rounded-full transition-colors duration-300 relative">
                                            <div :class="defaultFramework === framework.value ? 'scale-100' : 'scale-0'"
                                                 class="absolute inset-0 bg-white dark:bg-zinc-800 rounded-full transform transition-transform duration-300"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Toggle switch logic remains the same, initial state is handled by x-data --}}
                    <div class="flex items-center space-x-4 animate-fade-in-up delay-500">
                        <button type="button"
                                @click="autoGenerateTests = !autoGenerateTests"
                                :class="autoGenerateTests ? 'bg-zinc-800 dark:bg-zinc-200' : 'bg-zinc-200 dark:bg-zinc-700'"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2">
                            <span :class="autoGenerateTests ? 'translate-x-5' : 'translate-x-0'"
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white dark:bg-zinc-800 shadow-lg ring-0 transition-transform duration-300 ease-in-out"></span>
                        </button>
                        <div>
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300 cursor-pointer">
                                Auto-generate tests from user stories
                            </label>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                Arxitest will automatically create test scripts from Jira user stories
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Animated Submit Button -->
                <div class="mt-12 flex justify-end space-x-4 animate-fade-in-up delay-600">
                    <button type="button"
                            @click="window.history.back()" {{-- Simple cancel: go back --}}
                            class="px-6 py-2.5 text-zinc-700 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-white bg-zinc-100/70 dark:bg-zinc-700/50 rounded-xl hover:bg-zinc-200/50 dark:hover:bg-zinc-600/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md transform hover:-translate-y-0.5">
                        Cancel
                    </button>
                    <button type="button"
                            @click="submitForm"
                            :disabled="isSubmitting || !projectName.trim()"
                            class="relative px-8 py-2.5 text-white bg-gradient-to-r from-zinc-800 to-zinc-600 dark:from-zinc-700 dark:to-zinc-500 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                        <span class="relative z-10 flex items-center">
                            <span x-show="!isSubmitting" class="flex items-center">
                                Save Changes {{-- Changed button text --}}
                                <i data-lucide="save" class="w-4 h-4 ml-2"></i> {{-- Changed icon --}}
                            </span>
                            <span x-show="isSubmitting" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving... {{-- Changed loading text --}}
                            </span>
                        </span>
                        <div :class="isSubmitting || !projectName.trim() ? 'opacity-0' : 'opacity-100'"
                             class="absolute inset-0 bg-gradient-to-r from-zinc-700 to-zinc-500 dark:from-zinc-600 dark:to-zinc-400 rounded-xl blur-md group-hover:blur-lg transition-all duration-300"></div>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
{{-- Styles remain the same as create view --}}
<style>
    @keyframes fade-in-down {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fade-in-left {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes scale-in-x {
        from { transform: scaleX(0); }
        to { transform: scaleX(1); }
    }

    .animate-fade-in-down { animation: fade-in-down 0.6s ease-out; }
    .animate-fade-in-left { animation: fade-in-left 0.6s ease-out; }
    .animate-fade-in-up { animation: fade-in-up 0.6s ease-out; }
    .animate-scale-in-x { animation: scale-in-x 0.6s ease-out; }

    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
    .delay-300 { animation-delay: 0.3s; }
    .delay-400 { animation-delay: 0.4s; }
    .delay-500 { animation-delay: 0.5s; }
    .delay-600 { animation-delay: 0.6s; }
</style>
@endpush

@push('scripts')
{{-- Scripts remain the same as create view --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
    });
</script>
@endpush
