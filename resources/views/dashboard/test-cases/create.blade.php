@php
    // Determine initial selected suite if provided
    $initialSelectedSuiteId = $selectedSuite->id ?? old('suite_id', '');
    $initialSelectedSuiteName = $selectedSuite->name ?? ($initialSelectedSuiteId ? $testSuites->firstWhere('id', $initialSelectedSuiteId)?->name : '');
@endphp

@extends('layouts.dashboard')

@section('title', isset($selectedSuite) ? "Create Test Case in {$selectedSuite->name}" : "Create Test Case")

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects') }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
            Projects
        </a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.show', $project->id) }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
            {{ $project->name }}
        </a>
    </li>
    @if(isset($selectedSuite))
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <a href="{{ route('dashboard.projects.test-suites.show', [$project->id, $selectedSuite->id]) }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                {{ $selectedSuite->name }}
            </a>
        </li>
    @endif
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Create Test Case</span>
    </li>
@endsection

@section('content')
<div class="h-full" id="test-case-create-container" data-project-id="{{ $project->id }}"
    x-data="{
        currentSuiteId: '{{ $initialSelectedSuiteId }}',
        currentSuiteName: '{{ $initialSelectedSuiteName ?: '-- Select a Test Suite --' }}',
        aiSuiteDropdownOpen: false,
        manualSuiteDropdownOpen: false,

        updateSuite(id, name) {
            this.currentSuiteId = id;
            this.currentSuiteName = name;
            // Close both dropdowns just in case
            this.aiSuiteDropdownOpen = false;
            this.manualSuiteDropdownOpen = false;
            // Manually trigger change event on hidden input for any potential JS listeners
            const input = document.getElementById('suite-id-input');
            if (input) {
                input.value = id; // Ensure hidden input has the value
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
            console.log('Suite updated:', this.currentSuiteId, this.currentSuiteName);
        }
    }">
    <div id="flash-messages"
     data-success="{{ session('success') }}"
     data-error="{{ session('error') }}"
     style="display: none;">
    </div>
    <!-- Animated Header -->
    <div class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    Create New Test Case
                </h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    @if(isset($selectedSuite))
                        Adding test case to {{ $selectedSuite->name }} in {{ $project->name }}
                    @else
                        Create a new test case in {{ $project->name }}
                    @endif
                </p>
            </div>
            <div>
                @if(isset($selectedSuite))
                    <a href="{{ route('dashboard.projects.test-suites.show', [$project->id, $selectedSuite->id]) }}" class="group inline-flex items-center px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-200 bg-white/70 dark:bg-zinc-800/50 hover:bg-white dark:hover:bg-zinc-700/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                        <i data-lucide="arrow-left" class="mr-2 -ml-1 w-4 h-4 transition-transform group-hover:-translate-x-1"></i>
                        Back to Suite
                    </a>
                @else
                    <a href="{{ route('dashboard.projects.test-cases.index', $project->id) }}" class="group inline-flex items-center px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-200 bg-white/70 dark:bg-zinc-800/50 hover:bg-white dark:hover:bg-zinc-700/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                        <i data-lucide="arrow-left" class="mr-2 -ml-1 w-4 h-4 transition-transform group-hover:-translate-x-1"></i>
                        Back to Test Cases
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Notification Area (displayed by JS) -->
    <div id="notification-container" class="fixed bottom-6 right-6 z-50 max-w-sm w-full hidden shadow-lg border rounded-xl p-4">
        <div class="flex items-start">
            <div id="notification-icon" class="flex-shrink-0 w-5 h-5 mr-3"></div>
            <div class="flex-1">
                <h4 id="notification-title" class="font-medium mb-1"></h4>
                <p id="notification-message" class="text-sm"></p>
            </div>
            <button onclick="hideNotification()" class="ml-4 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- Creation Mode Toggle -->
    <div class="mb-8 flex justify-center">
        <div class="inline-flex bg-zinc-100 dark:bg-zinc-800 p-1 rounded-lg shadow-sm">
            <button id="manual-mode-btn" class="px-6 py-3 rounded-md font-medium transition-all duration-200 flex items-center space-x-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-sm border-b-2 border-blue-500">
                <i data-lucide="pen-square" class="w-5 h-5"></i>
                <span>Manual Entry</span>
            </button>
            <button id="ai-mode-btn" class="px-6 py-3 rounded-md font-medium transition-all duration-200 flex items-center space-x-2 text-zinc-600 dark:text-zinc-400 hover:bg-white/30 dark:hover:bg-zinc-700/30">
                <i data-lucide="sparkles" class="w-5 h-5"></i>
                <span>AI Generation</span>
            </button>
        </div>
    </div>

    <!-- Glassmorphism Form Container -->
    <div class="bg-white/70 dark:bg-zinc-800/50 rounded-2xl shadow-2xl border border-zinc-200/50 dark:border-zinc-700/30 backdrop-blur-lg transition-all duration-300 hover:shadow-2xl">
        <div class="p-8">
            @if(isset($selectedSuite))
                <form id="test-case-form" method="POST" action="{{ route('dashboard.projects.test-suites.test-cases.store', [$project->id, $selectedSuite->id]) }}">
            @else
                <form id="test-case-form" method="POST" action="{{ route('dashboard.projects.test-cases.store', $project->id) }}">
            @endif
                @csrf

                {{-- ****** THIS IS THE SINGLE SOURCE OF TRUTH FOR SUITE ID ****** --}}
                <input type="hidden" name="suite_id" id="suite-id-input" x-model="currentSuiteId">
                {{-- ************************************************************ --}}

                <input type="hidden" name="status" id="status-input" value="draft">

                <!-- AI Generation Section -->
                <div id="ai-generation-section" class="space-y-6 mb-8 pb-8 border-b border-zinc-200 dark:border-zinc-700 hidden">
                    <div class="mb-6 animate-fade-in-left">
                        <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 mb-2 flex items-center">
                            <i data-lucide="bot" class="w-5 h-5 mr-2 text-purple-500"></i>
                            AI-Powered Test Case Generation
                        </h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Describe the feature or scenario you want to test in natural language. Our AI will generate a complete test case based on your description.
                        </p>
                    </div>

                    <!-- Test Suite Selection for AI (Custom Dropdown) -->
                    <div class="relative group animate-fade-in-up dropdown-container" id="ai-suite-selection-container">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Select Test Suite <span class="text-red-500">*</span>
                        </label>
                        <button type="button"
                                @click="aiSuiteDropdownOpen = !aiSuiteDropdownOpen"
                                :class="{ 'ring-2 ring-purple-500/50 dark:ring-purple-400/50': aiSuiteDropdownOpen }"
                                class="flex items-center justify-between h-12 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 pl-4 pr-3 py-2 text-zinc-700 dark:text-zinc-200 focus:ring-2 focus:ring-purple-500/50 dark:focus:ring-purple-400/50 transition-all duration-300">
                            <span x-text="currentSuiteName" :class="{'text-zinc-400 dark:text-zinc-500': !currentSuiteId}"></span>
                            <i data-lucide="chevron-down" class="w-5 h-5 text-zinc-400 dark:text-zinc-500 transition-transform duration-200" :class="{ 'rotate-180': aiSuiteDropdownOpen }"></i>
                        </button>
                        <div x-show="aiSuiteDropdownOpen"
                             @click.away="aiSuiteDropdownOpen = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="dropdown-menu w-full "
                             x-cloak>
                            @if($testSuites->isEmpty())
                                <div class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">No test suites available.</div>
                            @else
                                @foreach($testSuites as $suite)
                                    <div @click="updateSuite('{{ $suite->id }}', '{{ $suite->name }}')"
                                         class="dropdown-item px-4 py-3 text-sm cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                         :class="{ 'bg-zinc-100 dark:bg-zinc-700 font-medium': currentSuiteId == '{{ $suite->id }}' }">
                                        {{ $suite->name }}
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="relative group animate-fade-in-up">
                        <label for="ai-prompt" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Description of Test Scenario <span class="text-red-500">*</span>
                        </label>
                        <div class="relative rounded-lg bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 p-px">
                            <textarea
                                id="ai-prompt"
                                rows="4"
                                class="w-full px-4 py-3 rounded-[7px] border-0 bg-white/70 dark:bg-zinc-800/90 focus:ring-2 focus:ring-purple-500/30 placeholder-zinc-400 dark:placeholder-zinc-500 resize-none transition-all duration-300"
                                placeholder="Example: 'Test the login functionality with both valid and invalid credentials, check error messages, and verify that a successful login redirects to the dashboard'"></textarea>
                        </div>
                        <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                            Be specific about the feature, expected behavior, and any edge cases you want to include
                        </p>
                    </div>

                    <div class="flex justify-end animate-fade-in-up">
                        <button
                            type="button"
                            id="generate-ai-btn"
                            class="inline-flex items-center group px-5 py-2.5 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white shadow-lg hover:shadow-xl transition-all duration-300 disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <i data-lucide="sparkles" class="w-5 h-5 mr-2 transition-transform group-hover:scale-110"></i>
                            <span id="generate-ai-btn-text">Generate Test Case</span>
                        </button>
                    </div>

                    <!-- Error message -->
                    <div id="ai-error-container" class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-lg border border-red-200 dark:border-red-800/50 animate-fade-in hidden">
                        <div class="flex items-start">
                            <i data-lucide="alert-triangle" class="w-5 h-5 mr-2 flex-shrink-0 text-red-500 dark:text-red-400"></i>
                            <span id="ai-error-message"></span>
                        </div>
                    </div>
                </div>

                <!-- Test Case Form Fields -->
                <div id="test-case-form-fields" class="space-y-8">
                    <div class="animate-fade-in-left">
                        <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 mb-2 flex items-center">
                            <i data-lucide="check-square" class="w-5 h-5 mr-2 text-blue-500"></i>
                            <span id="form-section-title">Test Case Details</span>
                        </h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400" id="form-section-description">
                            Define your test case details, including steps and expected results
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                        <!-- Test Suite Selection (Manual - Custom Dropdown) -->
                        <div class="sm:col-span-3 animate-fade-in-up dropdown-container" id="manual-suite-selection-container">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Test Suite <span class="text-red-500">*</span>
                            </label>
                             <button type="button"
                                    @click="manualSuiteDropdownOpen = !manualSuiteDropdownOpen"
                                    :class="{ 'ring-2 ring-zinc-500/50 dark:ring-zinc-400/50': manualSuiteDropdownOpen }"
                                    class="flex items-center justify-between h-12 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 pl-4 pr-3 py-2 text-zinc-700 dark:text-zinc-200 focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300">
                                <span x-text="currentSuiteName" :class="{'text-zinc-400 dark:text-zinc-500': !currentSuiteId}"></span>
                                <i data-lucide="chevron-down" class="w-5 h-5 text-zinc-400 dark:text-zinc-500 transition-transform duration-200" :class="{ 'rotate-180': manualSuiteDropdownOpen }"></i>
                            </button>
                            <div x-show="manualSuiteDropdownOpen"
                                 @click.away="manualSuiteDropdownOpen = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="dropdown-menu w-full"
                                 x-cloak>
                                @if($testSuites->isEmpty())
                                    <div class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">No test suites available.</div>
                                @else
                                     @foreach($testSuites as $suite)
                                        <div @click="updateSuite('{{ $suite->id }}', '{{ $suite->name }}')"
                                             class="dropdown-item px-4 py-3 text-sm cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                             :class="{ 'bg-zinc-100 dark:bg-zinc-700 font-medium': currentSuiteId == '{{ $suite->id }}' }">
                                            {{ $suite->name }}
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            {{-- Display validation error for the hidden suite_id input --}}
                            @error('suite_id')
                                <p class="mt-2 text-sm text-red-500 animate-fade-in">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Test Case Title -->
                        <div class="sm:col-span-6 animate-fade-in-up">
                            <div class="relative">
                                <input type="text" name="title" id="title" class="peer h-12 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 pl-4 pr-12 text-zinc-700 dark:text-zinc-200 placeholder-transparent focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300" placeholder="Test Case Title" value="{{ old('title') }}" required>
                                <label for="title" class="absolute left-4 -top-2.5 px-1 bg-zinc-100/50 dark:bg-zinc-800/50 text-sm text-zinc-600 dark:text-zinc-400 transition-all duration-300 peer-placeholder-shown:top-3 peer-placeholder-shown:text-base peer-placeholder-shown:text-zinc-400 peer-focus:-top-2.5 peer-focus:text-sm peer-focus:text-zinc-600 dark:peer-focus:text-zinc-300">
                                    Test Case Title <span class="text-red-400">*</span>
                                </label>
                                @error('title')
                                    <div class="absolute right-4 top-3">
                                        <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 animate-pulse"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('title')
                                <p class="mt-2 text-sm text-red-500 animate-fade-in">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Test Case Description -->
                        <div class="sm:col-span-6 animate-fade-in-up">
                            <div class="relative">
                                <textarea id="description" name="description" rows="3" class="peer h-24 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 p-4 text-zinc-700 dark:text-zinc-200 placeholder-transparent focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300 resize-none" placeholder="A brief description of what this test case verifies">{{ old('description') }}</textarea>
                                <label for="description" class="absolute left-4 -top-2.5 px-1 bg-zinc-100/50 dark:bg-zinc-800/50 text-sm text-zinc-600 dark:text-zinc-400 transition-all duration-300 peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-zinc-400 peer-focus:-top-2.5 peer-focus:text-sm peer-focus:text-zinc-600 dark:peer-focus:text-zinc-300">
                                    Description
                                </label>
                            </div>
                             @error('description')
                                <p class="mt-2 text-sm text-red-500 animate-fade-in">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                Brief description of what this test case verifies
                            </p>
                        </div>

                        <!-- Priority Selection -->
                        <div class="sm:col-span-6 animate-fade-in-up">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Priority <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="priority-options">
                                @php $selectedPriority = old('priority', 'medium'); @endphp
                                <div class="priority-option relative p-4 rounded-xl cursor-pointer transition-all duration-300 group ring-1 ring-zinc-200/70 dark:ring-zinc-600/50 hover:ring-zinc-300 dark:hover:ring-zinc-500 @if($selectedPriority === 'low') ring-2 ring-zinc-500 dark:ring-zinc-400 bg-zinc-100/50 dark:bg-zinc-700/30 @endif" data-value="low">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <h4 class="text-base font-medium text-zinc-800 dark:text-zinc-200">Low</h4>
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Low</span>
                                            </div>
                                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Minor functionality, edge cases, or cosmetic issues</p>
                                        </div>
                                        <div class="flex items-center h-5 ml-2">
                                            <div class="priority-radio bg-zinc-300 dark:bg-zinc-600 w-4 h-4 rounded-full transition-colors duration-300 relative @if($selectedPriority === 'low') bg-zinc-800 dark:bg-zinc-200 @endif">
                                                 @if($selectedPriority === 'low') <div class="absolute inset-0 flex items-center justify-center transform"><div class="w-2 h-2 rounded-full bg-white dark:bg-zinc-800"></div></div> @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="priority-option relative p-4 rounded-xl cursor-pointer transition-all duration-300 group ring-1 ring-zinc-200/70 dark:ring-zinc-600/50 hover:ring-zinc-300 dark:hover:ring-zinc-500 @if($selectedPriority === 'medium') ring-2 ring-zinc-500 dark:ring-zinc-400 bg-zinc-100/50 dark:bg-zinc-700/30 @endif" data-value="medium">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <h4 class="text-base font-medium text-zinc-800 dark:text-zinc-200">Medium</h4>
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">Medium</span>
                                            </div>
                                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Important functionality but not critical path</p>
                                        </div>
                                        <div class="flex items-center h-5 ml-2">
                                            <div class="priority-radio bg-zinc-300 dark:bg-zinc-600 w-4 h-4 rounded-full transition-colors duration-300 relative @if($selectedPriority === 'medium') bg-zinc-800 dark:bg-zinc-200 @endif">
                                                @if($selectedPriority === 'medium') <div class="absolute inset-0 flex items-center justify-center transform"><div class="w-2 h-2 rounded-full bg-white dark:bg-zinc-800"></div></div> @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="priority-option relative p-4 rounded-xl cursor-pointer transition-all duration-300 group ring-1 ring-zinc-200/70 dark:ring-zinc-600/50 hover:ring-zinc-300 dark:hover:ring-zinc-500 @if($selectedPriority === 'high') ring-2 ring-zinc-500 dark:ring-zinc-400 bg-zinc-100/50 dark:bg-zinc-700/30 @endif" data-value="high">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <h4 class="text-base font-medium text-zinc-800 dark:text-zinc-200">High</h4>
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">High</span>
                                            </div>
                                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Critical functionality or user-facing features</p>
                                        </div>
                                        <div class="flex items-center h-5 ml-2">
                                            <div class="priority-radio bg-zinc-300 dark:bg-zinc-600 w-4 h-4 rounded-full transition-colors duration-300 relative @if($selectedPriority === 'high') bg-zinc-800 dark:bg-zinc-200 @endif">
                                                @if($selectedPriority === 'high') <div class="absolute inset-0 flex items-center justify-center transform"><div class="w-2 h-2 rounded-full bg-white dark:bg-zinc-800"></div></div> @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="priority" id="priority-input" value="{{ $selectedPriority }}">
                             @error('priority')
                                <p class="mt-2 text-sm text-red-500 animate-fade-in">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Test Steps -->
                        <div class="sm:col-span-6 space-y-4 animate-fade-in-up" id="test-steps-container">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Test Steps <span class="text-red-500">*</span>
                                </label>
                                <button type="button" id="add-step-btn" class="inline-flex items-center text-sm px-3 py-1.5 rounded-lg bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors">
                                    <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                                    Add Step
                                </button>
                            </div>
                            <div class="space-y-3" id="steps-list">
                                @php $oldSteps = old('steps', ['']); @endphp
                                @foreach($oldSteps as $index => $stepValue)
                                    <div class="step-item flex items-start space-x-3">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-semibold text-sm">
                                            <span class="step-number">{{ $index + 1 }}</span>
                                        </div>
                                        <div class="flex-1 relative">
                                            <input name="steps[{{ $index }}]" type="text" value="{{ $stepValue }}" class="step-input w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-lg shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 p-3 text-zinc-700 dark:text-zinc-200 focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300" placeholder="Describe the step to perform">
                                        </div>
                                        <button type="button" class="remove-step-btn flex-shrink-0 p-1.5 rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-red-500 dark:hover:text-red-400 transition-colors @if(count($oldSteps) <= 1) disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:text-zinc-500 @endif" @if(count($oldSteps) <= 1) disabled @endif>
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            @error('steps')
                                <p class="mt-2 text-sm text-red-500 animate-fade-in">{{ $message }}</p>
                            @enderror
                            @error('steps.*')
                                <p class="mt-2 text-sm text-red-500 animate-fade-in">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Expected Results -->
                        <div class="sm:col-span-6 animate-fade-in-up">
                            <div class="relative">
                                <textarea id="expected_results" name="expected_results" rows="4" class="peer h-32 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 p-4 text-zinc-700 dark:text-zinc-200 placeholder-transparent focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300 resize-none" placeholder="Describe what should happen when the test is executed correctly">{{ old('expected_results') }}</textarea>
                                <label for="expected_results" class="absolute left-4 -top-2.5 px-1 bg-zinc-100/50 dark:bg-zinc-800/50 text-sm text-zinc-600 dark:text-zinc-400 transition-all duration-300 peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-zinc-400 peer-focus:-top-2.5 peer-focus:text-sm peer-focus:text-zinc-600 dark:peer-focus:text-zinc-300">
                                    Expected Results <span class="text-red-400">*</span>
                                </label>
                            </div>
                            @error('expected_results')
                                <p class="mt-2 text-sm text-red-500 animate-fade-in">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                Describe what should happen when the test is executed correctly
                            </p>
                        </div>

                        <!-- Tags -->
                        <div class="sm:col-span-6 animate-fade-in-up">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Tags
                            </label>
                            <div class="flex flex-wrap items-center gap-2 mb-2" id="tags-container">
                                <!-- Tags will be inserted here by JS -->
                                 @php $oldTags = old('tags', []); @endphp
                                @foreach($oldTags as $tag)
                                    <span class="tag-item inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-200 border border-indigo-200/50 dark:border-indigo-800/50">
                                        {{ $tag }}
                                        <input type="hidden" name="tags[]" value="{{ $tag }}">
                                        <button type="button" class="remove-tag-btn ml-1.5 -mr-1 flex-shrink-0 text-indigo-400 hover:text-indigo-600 dark:text-indigo-500 dark:hover:text-indigo-300">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </span>
                                @endforeach
                                <div class="relative" id="tag-input-container">
                                    <input type="text" id="tag-input" class="border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-full shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 py-1 pl-3 pr-8 text-sm text-zinc-700 dark:text-zinc-200 focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300" placeholder="Add a tag">
                                    <button type="button" id="add-tag-btn" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                Press Enter or click + to add a tag
                            </p>
                             @error('tags')
                                <p class="mt-2 text-sm text-red-500 animate-fade-in">{{ $message }}</p>
                            @enderror
                            @error('tags.*')
                                <p class="mt-2 text-sm text-red-500 animate-fade-in">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-12 flex justify-end space-x-4 animate-fade-in-up">
                        <button type="button" id="cancel-btn" class="px-6 py-2.5 text-zinc-700 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-white bg-zinc-100/70 dark:bg-zinc-700/50 rounded-xl hover:bg-zinc-200/50 dark:hover:bg-zinc-600/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md transform hover:-translate-y-0.5">
                            Cancel
                        </button>
                        <button type="submit" id="submit-btn" class="relative px-8 py-2.5 text-white bg-gradient-to-r from-zinc-800 to-zinc-600 dark:from-zinc-700 dark:to-zinc-500 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                            <span class="relative z-10 flex items-center">
                                <span id="submit-btn-text">Create Test Case</span>
                                <i data-lucide="check-square" class="w-4 h-4 ml-2"></i>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @keyframes fade-in-down { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fade-in-left { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes fade-in-up { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
    .animate-fade-in-down { animation: fade-in-down 0.6s ease-out; }
    .animate-fade-in-left { animation: fade-in-left 0.6s ease-out; }
    .animate-fade-in-up { animation: fade-in-up 0.6s ease-out; }
    .animate-fade-in { animation: fade-in 0.4s ease-out; }
    .hidden { display: none !important; }
    [x-cloak] { display: none !important; }
    /* Add styles for your custom dropdown if they are not globally available */
    /* Example styles (adjust as needed) */
    .dropdown-container { /* Add your container styles */ }
    .dropdown-menu { /* Add your menu styles: positioning, background, border, etc. */ }
    .dropdown-item { /* Add your item styles: padding, hover effects */ }
</style>
@endpush

@push('meta')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('scripts')
    {{-- Include AlpineJS if not already included globally --}}
    {{-- <script src="//unpkg.com/alpinejs" defer></script> --}}
    @vite('resources/js/test-case-create.js')

    {{-- Inline script for dropdown initialization and updates (can be moved to test-case-create.js) --}}
    <script>
        // This script assumes Alpine.js is loaded
        document.addEventListener('DOMContentLoaded', () => {
            // Ensure icons are created after Alpine initializes and potentially reveals elements
             if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            // Maybe add a small delay or use Alpine.$nextTick if icons in dropdown aren't rendering
            setTimeout(() => {
                 if (typeof lucide !== 'undefined') {
                     lucide.createIcons();
                 }
            }, 100);
        });

        // If test-case-create.js handles priority clicks, steps, tags, ensure it's compatible
        // with the new Alpine data structure if needed.
        // The priority click handling might need adjustment if it was relying on direct DOM manipulation
        // that conflicts with Alpine.

        // Example of how priority selection might be handled in test-case-create.js (adjust as needed)
        // Ensure it updates the hidden #priority-input
        // document.querySelectorAll('.priority-option').forEach(option => {
        //     option.addEventListener('click', function() {
        //         document.querySelectorAll('.priority-option').forEach(opt => {
        //             opt.classList.remove('ring-2', 'ring-zinc-500', 'dark:ring-zinc-400', 'bg-zinc-100/50', 'dark:bg-zinc-700/30');
        //             opt.querySelector('.priority-radio').classList.remove('bg-zinc-800', 'dark:bg-zinc-200');
        //             opt.querySelector('.priority-radio').innerHTML = '';
        //         });
        //         this.classList.add('ring-2', 'ring-zinc-500', 'dark:ring-zinc-400', 'bg-zinc-100/50', 'dark:bg-zinc-700/30');
        //         const radio = this.querySelector('.priority-radio');
        //         radio.classList.add('bg-zinc-800', 'dark:bg-zinc-200');
        //         radio.innerHTML = '<div class="absolute inset-0 flex items-center justify-center transform"><div class="w-2 h-2 rounded-full bg-white dark:bg-zinc-800"></div></div>';
        //         document.getElementById('priority-input').value = this.dataset.value;
        //     });
        // });
    </script>
@endpush
