@php
    /**
     * @var \App\Models\Project $project
     * @var \App\Models\TestCase $testCase
     * @var \Illuminate\Database\Eloquent\Collection $testSuites
     * @var \Illuminate\Database\Eloquent\Collection $stories
     */
    $pageTitle = 'Edit Test Case: ' . Str::limit($testCase->title, 40);

    // Unpack steps and tags from JSON if needed, defaulting to empty arrays
    $currentSteps = is_array($testCase->steps) ? $testCase->steps : (json_decode($testCase->steps, true) ?: []);
    $currentTags = is_array($testCase->tags) ? $testCase->tags : (json_decode($testCase->tags, true) ?: []);

    // Get values considering validation errors (old input) or existing data
    $selectedSuiteId = old('suite_id', $testCase->suite_id);
    $selectedStoryId = old('story_id', $testCase->story_id);
    $selectedSuiteName = $testSuites->firstWhere('id', $selectedSuiteId)?->name ?? 'No Test Suite';
    $selectedStoryName = $stories->firstWhere('id', $selectedStoryId)?->title ?? 'Select Story';
    $selectedPriority = old('priority', $testCase->priority);
    $selectedStatus = old('status', $testCase->status);
    $displaySteps = old('steps', $currentSteps);
    $displayTags = old('tags', $currentTags);

    // Ensure displaySteps is always an array for the loop, add an empty element if empty for initial display
    if (!is_array($displaySteps)) {
        $displaySteps = [];
    }
    if (empty($displaySteps)) {
        $displaySteps = [''];
    }

    // Ensure displayTags is always an array
    if (!is_array($displayTags)) {
        $displayTags = [];
    }
@endphp

@extends('layouts.dashboard')

@section('title', $pageTitle)

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects') }}"
            class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">Projects</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.show', $project->id) }}"
            class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">{{ $project->name }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.index', $project->id) }}"
            class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">Test Cases</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}"
            class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">{{ Str::limit($testCase->title, 30) }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Edit</span>
    </li>
@endsection

@section('content')
    <div class="h-full" id="test-case-edit-container" data-project-id="{{ $project->id }}" x-data="{
        currentSuiteId: '{{ $selectedSuiteId }}',
        currentSuiteName: '{{ addslashes($selectedSuiteName) }}',
        currentStoryId: '{{ $selectedStoryId }}',
        currentStoryName: '{{ addslashes($selectedStoryName) }}',
        manualSuiteDropdownOpen: false,
        manualStoryDropdownOpen: false,
        priority: '{{ $selectedPriority }}',
        status: '{{ $selectedStatus }}',

        updateSuite(id, name) {
            this.currentSuiteId = id;
            this.currentSuiteName = name;
            this.manualSuiteDropdownOpen = false;
        },
        updateStory(id, name) {
            this.currentStoryId = id;
            this.currentStoryName = name;
            this.manualStoryDropdownOpen = false;
        },
        isPriority(value) {
            return this.priority === value;
        },
        setPriority(value) {
            this.priority = value;
        }
    }">
        <div id="flash-messages" data-success="{{ session('success') }}" data-error="{{ session('error') }}"
            style="display: none;">
        </div>
        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                        Edit Test Case
                    </h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Editing "<span class="font-medium">{{ $testCase->title }}</span>"
                    </p>
                </div>
                <div>
                    <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}"
                        class="group inline-flex items-center px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-200 bg-white/70 dark:bg-zinc-800/50 hover:bg-white dark:hover:bg-zinc-700/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                        <i data-lucide="arrow-left"
                            class="mr-2 -ml-1 w-4 h-4 transition-transform group-hover:-translate-x-1"></i>
                        Cancel Edit
                    </a>
                </div>
            </div>
        </div>

        <!-- Notification Area (displayed by JS) -->
        <div id="notification-container"
            class="fixed bottom-6 right-6 z-50 max-w-sm w-full hidden shadow-lg border rounded-xl p-4">
            <div class="flex items-start">
                <div id="notification-icon" class="flex-shrink-0 w-5 h-5 mr-3"></div>
                <div class="flex-1">
                    <h4 id="notification-title" class="font-medium mb-1"></h4>
                    <p id="notification-message" class="text-sm"></p>
                </div>
                <button onclick="hideNotification()"
                    class="ml-4 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Glassmorphism Form Container -->
        <div
            class="bg-white/70 dark:bg-zinc-800/50 rounded-2xl shadow-2xl border border-zinc-200/50 dark:border-zinc-700/30 backdrop-blur-lg transition-all duration-300 hover:shadow-2xl">
            <div class="p-8">
                <form id="test-case-form" method="POST"
                    action="{{ route('dashboard.projects.test-cases.update', [$project->id, $testCase->id]) }}">
                    @csrf
                    @method('PUT')

                    {{-- Hidden inputs bound to Alpine --}}
                    <input type="hidden" name="suite_id" id="suite-id-input" x-model="currentSuiteId">
                    <input type="hidden" name="story_id" id="story-id-input" x-model="currentStoryId">
                    <input type="hidden" name="priority" id="priority-input" x-model="priority">

                    <!-- Test Case Form Fields -->
                    <div id="test-case-form-fields" class="space-y-8">
                        <div class="animate-fade-in-left">
                            <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 mb-2 flex items-center">
                                <i data-lucide="edit" class="w-5 h-5 mr-2 text-blue-500"></i>
                                <span id="form-section-title">Edit Test Case Details</span>
                            </h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400" id="form-section-description">
                                Update the test case details, steps, and expected results.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                            <!-- Test Suite Selection (Custom Dropdown) -->
                            <div class="sm:col-span-3 animate-fade-in-up dropdown-container relative"
                                id="manual-suite-selection-container">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Test Suite
                                </label>
                                <button type="button" @click="manualSuiteDropdownOpen = !manualSuiteDropdownOpen"
                                    :class="{ 'ring-2 ring-zinc-500/50 dark:ring-zinc-400/50': manualSuiteDropdownOpen }"
                                    class="flex items-center justify-between h-12 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 pl-4 pr-3 py-2 text-zinc-700 dark:text-zinc-200 focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300">
                                    <span x-text="currentSuiteName"
                                        :class="{ 'text-zinc-400 dark:text-zinc-500': !currentSuiteId }"></span>
                                    <i data-lucide="chevron-down"
                                        class="w-5 h-5 text-zinc-400 dark:text-zinc-500 transition-transform duration-200"
                                        :class="{ 'rotate-180': manualSuiteDropdownOpen }"></i>
                                </button>
                                <div x-show="manualSuiteDropdownOpen" @click.away="manualSuiteDropdownOpen = false"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="dropdown-menu absolute z-20 mt-1 w-full bg-white dark:bg-zinc-800 shadow-lg rounded-md border border-zinc-200 dark:border-zinc-700 max-h-60 overflow-y-auto"
                                    x-cloak>
                                    <div @click="updateSuite('', 'No Test Suite')"
                                        class="dropdown-item px-4 py-3 text-sm cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                        :class="{ 'bg-zinc-100 dark:bg-zinc-700 font-medium': currentSuiteId === '' }">
                                        No Test Suite
                                    </div>
                                    @if ($testSuites->isNotEmpty())
                                        @foreach ($testSuites as $suite)
                                            <div @click="updateSuite('{{ $suite->id }}', '{{ addslashes($suite->name) }}')"
                                                class="dropdown-item px-4 py-3 text-sm cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                :class="{ 'bg-zinc-100 dark:bg-zinc-700 font-medium': currentSuiteId ==
                                                        '{{ $suite->id }}' }">
                                                {{ $suite->name }}
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                @error('suite_id')
                                    <p class="mt-2 text-sm text-red-500 animate-fade-in">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Story Selection (Custom Dropdown) -->
                            <div class="sm:col-span-3 animate-fade-in-up dropdown-container relative"
                                id="manual-story-selection-container">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Story <span class="text-red-500">*</span>
                                </label>
                                <button type="button" @click="manualStoryDropdownOpen = !manualStoryDropdownOpen"
                                    :class="{ 'ring-2 ring-zinc-500/50 dark:ring-zinc-400/50': manualStoryDropdownOpen }"
                                    class="flex items-center justify-between h-12 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 pl-4 pr-3 py-2 text-zinc-700 dark:text-zinc-200 focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300">
                                    <span x-text="currentStoryName"
                                        :class="{ 'text-zinc-400 dark:text-zinc-500': !currentStoryId }"></span>
                                    <i data-lucide="chevron-down"
                                        class="w-5 h-5 text-zinc-400 dark:text-zinc-500 transition-transform duration-200"
                                        :class="{ 'rotate-180': manualStoryDropdownOpen }"></i>
                                </button>
                                <div x-show="manualStoryDropdownOpen" @click.away="manualStoryDropdownOpen = false"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="dropdown-menu absolute z-20 mt-1 w-full bg-white dark:bg-zinc-800 shadow-lg rounded-md border border-zinc-200 dark:border-zinc-700 max-h-60 overflow-y-auto"
                                    x-cloak>
                                    @if ($stories->isEmpty())
                                        <div class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">No stories
                                            available.</div>
                                    @else
                                        @foreach ($stories as $story)
                                            <div @click="updateStory('{{ $story->id }}', '{{ addslashes($story->title) }}')"
                                                class="dropdown-item px-4 py-3 text-sm cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                :class="{ 'bg-zinc-100 dark:bg-zinc-700 font-medium': currentStoryId ==
                                                        '{{ $story->id }}' }">
                                                {{ $story->title }}
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                @error('story_id')
                                    <p class="mt-2 text-sm text-red-500 animate-fade-in">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status Selection -->
                            <div class="sm:col-span-3 animate-fade-in-up">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <div class="flex flex-wrap gap-x-4 gap-y-2">
                                    @foreach (['draft', 'active', 'deprecated', 'archived'] as $statusValue)
                                        <div class="status-option relative flex items-center">
                                            <input type="radio" id="status-{{ $statusValue }}" name="status"
                                                value="{{ $statusValue }}" @checked($selectedStatus === $statusValue)
                                                class="form-radio h-4 w-4 text-indigo-600 border-zinc-300 dark:border-zinc-600 focus:ring-indigo-500">
                                            <label for="status-{{ $statusValue }}"
                                                class="ml-2 text-sm text-zinc-700 dark:text-zinc-300 cursor-pointer">
                                                {{ ucfirst($statusValue) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('status')
                                    <p class="mt-2 text-sm text-red-500 animate-fade-in">{{ $message }}</p>
                                @enderror
                            </div>


                            <!-- Test Case Title -->
                            <div class="sm:col-span-6 animate-fade-in-up">
                                <div class="relative">
                                    <input type="text" name="title" id="title"
                                        class="peer h-12 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 pl-4 pr-12 text-zinc-700 dark:text-zinc-200 placeholder-transparent focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300"
                                        placeholder="Test Case Title" value="{{ old('title', $testCase->title) }}"
                                        required>
                                    <label for="title"
                                        class="absolute left-4 -top-2.5 px-1 bg-zinc-100/50 dark:bg-zinc-800/50 text-sm text-zinc-600 dark:text-zinc-400 transition-all duration-300 peer-placeholder-shown:top-3 peer-placeholder-shown:text-base peer-placeholder-shown:text-zinc-400 peer-focus:-top-2.5 peer-focus:text-sm peer-focus:text-zinc-600 dark:peer-focus:text-zinc-300">
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
                                    <textarea id="description" name="description" rows="3"
                                        class="peer h-24 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 p-4 text-zinc-700 dark:text-zinc-200 placeholder-transparent focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300 resize-none"
                                        placeholder="A brief description of what this test case verifies">{{ old('description', $testCase->description) }}</textarea>
                                    <label for="description"
                                        class="absolute left-4 -top-2.5 px-1 bg-zinc-100/50 dark:bg-zinc-800/50 text-sm text-zinc-600 dark:text-zinc-400 transition-all duration-300 peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-zinc-400 peer-focus:-top-2.5 peer-focus:text-sm peer-focus:text-zinc-600 dark:peer-focus:text-zinc-300">
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
                                    {{-- Low Priority --}}
                                    <div @click="setPriority('low')"
                                        class="priority-option relative p-4 rounded-xl cursor-pointer transition-all duration-300 group ring-1 ring-zinc-200/70 dark:ring-zinc-600/50 hover:ring-zinc-300 dark:hover:ring-zinc-500"
                                        :class="{ 'ring-2 ring-zinc-500 dark:ring-zinc-400 bg-zinc-100/50 dark:bg-zinc-700/30': isPriority(
                                                'low') }">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-1">
                                                <div class="flex items-center">
                                                    <h4 class="text-base font-medium text-zinc-800 dark:text-zinc-200">Low
                                                    </h4>
                                                    <span
                                                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Low</span>
                                                </div>
                                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Minor
                                                    functionality, edge cases, or cosmetic issues</p>
                                            </div>
                                            <div class="flex items-center h-5 ml-2">
                                                <div class="priority-radio bg-zinc-300 dark:bg-zinc-600 w-4 h-4 rounded-full transition-colors duration-300 relative"
                                                    :class="{ 'bg-zinc-800 dark:bg-zinc-200': isPriority('low') }">
                                                    <div x-show="isPriority('low')"
                                                        class="absolute inset-0 flex items-center justify-center transform">
                                                        <div class="w-2 h-2 rounded-full bg-white dark:bg-zinc-800"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Medium Priority --}}
                                    <div @click="setPriority('medium')"
                                        class="priority-option relative p-4 rounded-xl cursor-pointer transition-all duration-300 group ring-1 ring-zinc-200/70 dark:ring-zinc-600/50 hover:ring-zinc-300 dark:hover:ring-zinc-500"
                                        :class="{ 'ring-2 ring-zinc-500 dark:ring-zinc-400 bg-zinc-100/50 dark:bg-zinc-700/30': isPriority(
                                                'medium') }">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-1">
                                                <div class="flex items-center">
                                                    <h4 class="text-base font-medium text-zinc-800 dark:text-zinc-200">
                                                        Medium</h4>
                                                    <span
                                                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">Medium</span>
                                                </div>
                                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Important
                                                    functionality but not critical path</p>
                                            </div>
                                            <div class="flex items-center h-5 ml-2">
                                                <div class="priority-radio bg-zinc-300 dark:bg-zinc-600 w-4 h-4 rounded-full transition-colors duration-300 relative"
                                                    :class="{ 'bg-zinc-800 dark:bg-zinc-200': isPriority('medium') }">
                                                    <div x-show="isPriority('medium')"
                                                        class="absolute inset-0 flex items-center justify-center transform">
                                                        <div class="w-2 h-2 rounded-full bg-white dark:bg-zinc-800"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- High Priority --}}
                                    <div @click="setPriority('high')"
                                        class="priority-option relative p-4 rounded-xl cursor-pointer transition-all duration-300 group ring-1 ring-zinc-200/70 dark:ring-zinc-600/50 hover:ring-zinc-300 dark:hover:ring-zinc-500"
                                        :class="{ 'ring-2 ring-zinc-500 dark:ring-zinc-400 bg-zinc-100/50 dark:bg-zinc-700/30': isPriority(
                                                'high') }">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-1">
                                                <div class="flex items-center">
                                                    <h4 class="text-base font-medium text-zinc-800 dark:text-zinc-200">High
                                                    </h4>
                                                    <span
                                                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">High</span>
                                                </div>
                                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Critical
                                                    functionality or user-facing features</p>
                                            </div>
                                            <div class="flex items-center h-5 ml-2">
                                                <div class="priority-radio bg-zinc-300 dark:bg-zinc-600 w-4 h-4 rounded-full transition-colors duration-300 relative"
                                                    :class="{ 'bg-zinc-800 dark:bg-zinc-200': isPriority('high') }">
                                                    <div x-show="isPriority('high')"
                                                        class="absolute inset-0 flex items-center justify-center transform">
                                                        <div class="w-2 h-2 rounded-full bg-white dark:bg-zinc-800"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                    <button type="button" id="add-step-btn"
                                        class="inline-flex items-center text-sm px-3 py-1.5 rounded-lg bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors">
                                        <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                                        Add Step
                                    </button>
                                </div>
                                <div class="space-y-3" id="steps-list">
                                    {{-- Loop through steps from PHP --}}
                                    @foreach ($displaySteps as $index => $stepValue)
                                        <div class="step-item flex items-start space-x-3">
                                            <div
                                                class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-semibold text-sm">
                                                <span class="step-number">{{ $index + 1 }}</span>
                                            </div>
                                            <div class="flex-1 relative">
                                                <input name="steps[{{ $index }}]" type="text"
                                                    value="{{ $stepValue }}"
                                                    class="step-input w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-lg shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 p-3 text-zinc-700 dark:text-zinc-200 focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300"
                                                    placeholder="Describe the step to perform">
                                            </div>
                                            <button type="button"
                                                class="remove-step-btn flex-shrink-0 p-1.5 rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-red-500 dark:hover:text-red-400 transition-colors @if (count($displaySteps) <= 1) disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:text-zinc-500 @endif"
                                                @if (count($displaySteps) <= 1) disabled @endif>
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
                                    <textarea id="expected_results" name="expected_results" rows="4"
                                        class="peer h-32 w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-xl shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 p-4 text-zinc-700 dark:text-zinc-200 placeholder-transparent focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300 resize-none"
                                        placeholder="Describe what should happen when the test is executed correctly">{{ old('expected_results', $testCase->expected_results) }}</textarea>
                                    <label for="expected_results"
                                        class="absolute left-4 -top-2.5 px-1 bg-zinc-100/50 dark:bg-zinc-800/50 text-sm text-zinc-600 dark:text-zinc-400 transition-all duration-300 peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-zinc-400 peer-focus:-top-2.5 peer-focus:text-sm peer-focus:text-zinc-600 dark:peer-focus:text-zinc-300">
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
                                    {{-- Loop through tags from PHP --}}
                                    @foreach ($displayTags as $tag)
                                        <span
                                            class="tag-item inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-200 border border-indigo-200/50 dark:border-indigo-800/50">
                                            {{ $tag }}
                                            <input type="hidden" name="tags[]" value="{{ $tag }}">
                                            <button type="button"
                                                class="remove-tag-btn ml-1.5 -mr-1 flex-shrink-0 text-indigo-400 hover:text-indigo-600 dark:text-indigo-500 dark:hover:text-indigo-300">
                                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </span>
                                    @endforeach
                                    {{-- Input area for new tags --}}
                                    <div class="relative" id="tag-input-container">
                                        <input type="text" id="tag-input"
                                            class="border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-full shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 py-1 pl-3 pr-8 text-sm text-zinc-700 dark:text-zinc-200 focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300"
                                            placeholder="Add a tag">
                                        <button type="button" id="add-tag-btn"
                                            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
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
                            <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}"
                                id="cancel-btn"
                                class="px-6 py-2.5 text-zinc-700 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-white bg-zinc-100/70 dark:bg-zinc-700/50 rounded-xl hover:bg-zinc-200/50 dark:hover:bg-zinc-600/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md transform hover:-translate-y-0.5">
                                Cancel
                            </a>
                            <button type="submit" id="submit-btn"
                                class="relative px-8 py-2.5 text-white bg-gradient-to-r from-zinc-800 to-zinc-600 dark:from-zinc-700 dark:to-zinc-500 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                                <span class="relative z-10 flex items-center">
                                    <span id="submit-btn-text">Update Test Case</span>
                                    <i data-lucide="check" class="w-4 h-4 ml-2"></i>
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
        @keyframes fade-in-down {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fade-in-left {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fade-in {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .animate-fade-in-down {
            animation: fade-in-down 0.6s ease-out;
        }

        .animate-fade-in-left {
            animation: fade-in-left 0.6s ease-out;
        }

        .animate-fade-in-up {
            animation: fade-in-up 0.6s ease-out;
        }

        .animate-fade-in {
            animation: fade-in 0.4s ease-out;
        }

        .hidden {
            display: none !important;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Add styles for your custom dropdown if they are not globally available */
        .dropdown-menu {
            /* Base styles */
            position: absolute;
            z-index: 20;
            margin-top: 0.25rem;
            width: 100%;
            background-color: white;
            border-radius: 0.375rem;
            --tw-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --tw-shadow-colored: 0 10px 15px -3px var(--tw-shadow-color), 0 4px 6px -4px var(--tw-shadow-color);
            box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
            border: 1px solid #e5e7eb;
            /* zinc-200 */
            max-height: 15rem;
            overflow-y: auto;
        }

        .dark .dropdown-menu {
            background-color: #27272a;
            border-color: #3f3f46;
        }

        /* zinc-800, zinc-700 */
        .dropdown-item {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .dropdown-item:hover {
            background-color: #f3f4f6;
        }

        /* zinc-100 */
        .dark .dropdown-item:hover {
            background-color: #3f3f46;
        }

        /* zinc-700 */
    </style>
@endpush

@push('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            // Reuse the create form data definition if it's compatible
            // If not, create a specific one for edit
            Alpine.data('testCaseEditForm', (config) => ({
                currentSuiteId: config.suiteId,
                currentSuiteName: config.suiteName,
                currentStoryId: config.storyId,
                currentStoryName: config.storyName,
                manualSuiteDropdownOpen: false,
                manualStoryDropdownOpen: false,
                priority: config.priority,
                status: config.status,

                init() {
                    // Add any edit-specific initialization if needed
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons(); // Ensure icons render on load
                        }
                    });
                },
                updateSuite(id, name) {
                    this.currentSuiteId = id;
                    this.currentSuiteName = name;
                    this.manualSuiteDropdownOpen = false;
                },
                updateStory(id, name) {
                    this.currentStoryId = id;
                    this.currentStoryName = name;
                    this.manualStoryDropdownOpen = false;
                },
                isPriority(value) {
                    return this.priority === value;
                },
                setPriority(value) {
                    this.priority = value;
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Priority Selection (Using Alpine now, but this is a fallback/alternative if not using Alpine for it)
            const priorityOptions = document.querySelectorAll('.priority-option');
            const priorityInput = document.getElementById('priority-input'); // Ensure this input exists

            priorityOptions.forEach(option => {
                option.addEventListener('click', function() {
                    if (priorityInput) priorityInput.value = this.dataset
                    .value; // Update hidden input

                    // Update UI styles
                    priorityOptions.forEach(opt => {
                        const radio = opt.querySelector('.priority-radio');
                        opt.classList.remove('ring-2', 'ring-zinc-500',
                            'dark:ring-zinc-400', 'bg-zinc-100/50',
                            'dark:bg-zinc-700/30');
                        radio.classList.remove('bg-zinc-800', 'dark:bg-zinc-200');
                        radio.classList.add('bg-zinc-300', 'dark:bg-zinc-600');
                        radio.innerHTML = ''; // Clear inner dot
                    });

                    this.classList.add('ring-2', 'ring-zinc-500', 'dark:ring-zinc-400',
                        'bg-zinc-100/50', 'dark:bg-zinc-700/30');
                    const currentRadio = this.querySelector('.priority-radio');
                    currentRadio.classList.add('bg-zinc-800', 'dark:bg-zinc-200');
                    currentRadio.classList.remove('bg-zinc-300', 'dark:bg-zinc-600');
                    currentRadio.innerHTML =
                        '<div class="absolute inset-0 flex items-center justify-center transform"><div class="w-2 h-2 rounded-full bg-white dark:bg-zinc-800"></div></div>'; // Add inner dot
                });
            });


            // Steps management (Same as create view)
            const stepsList = document.getElementById('steps-list');
            const addStepBtn = document.getElementById('add-step-btn');

            function updateStepNumbers() {
                if (!stepsList) return;
                stepsList.querySelectorAll('.step-item').forEach((step, index) => {
                    step.querySelector('.step-number').textContent = index + 1;
                    step.querySelector('input').name = `steps[${index}]`;
                });
                // Enable/disable remove buttons
                const removeButtons = stepsList.querySelectorAll('.remove-step-btn');
                const canRemove = removeButtons.length > 1;
                removeButtons.forEach(btn => {
                    if (canRemove) {
                        btn.removeAttribute('disabled');
                        btn.classList.remove('disabled:opacity-50', 'disabled:cursor-not-allowed',
                            'disabled:hover:bg-transparent', 'disabled:hover:text-zinc-500');
                    } else {
                        btn.setAttribute('disabled', 'disabled');
                        btn.classList.add('disabled:opacity-50', 'disabled:cursor-not-allowed',
                            'disabled:hover:bg-transparent', 'disabled:hover:text-zinc-500');
                    }
                });
            }

            if (addStepBtn && stepsList) {
                addStepBtn.addEventListener('click', function() {
                    const newStepIndex = stepsList.querySelectorAll('.step-item').length;
                    const newStep = document.createElement('div');
                    newStep.className =
                    'step-item flex items-start space-x-3 animate-fade-in'; // Added animation
                    newStep.innerHTML = `
                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-semibold text-sm">
                        <span class="step-number">${newStepIndex + 1}</span>
                    </div>
                    <div class="flex-1 relative">
                        <input name="steps[${newStepIndex}]" type="text" value="" class="step-input w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-lg shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 p-3 text-zinc-700 dark:text-zinc-200 focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300" placeholder="Describe the step to perform">
                    </div>
                    <button type="button" class="remove-step-btn flex-shrink-0 p-1.5 rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-red-500 dark:hover:text-red-400 transition-colors">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                `;
                    stepsList.appendChild(newStep);
                    updateStepNumbers(); // Renumber and manage remove buttons
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons({
                            elements: [newStep.querySelector('.remove-step-btn')]
                        });
                    }
                });
            }

            if (stepsList) {
                stepsList.addEventListener('click', function(e) {
                    const removeBtn = e.target.closest('.remove-step-btn');
                    if (!removeBtn || removeBtn.disabled) return; // Check if disabled

                    const stepItem = removeBtn.closest('.step-item');
                    if (!stepItem) return;

                    stepItem.remove();
                    updateStepNumbers(); // Renumber and manage remove buttons
                });
                // Initial numbering and button state
                updateStepNumbers();
            }

            // Tags management (Same as create view)
            const tagsContainer = document.getElementById('tags-container');
            const tagInput = document.getElementById('tag-input');
            const addTagBtn = document.getElementById('add-tag-btn');
            const tagInputContainer = document.getElementById(
            'tag-input-container'); // Reference to input's container

            function addTagFromInput() {
                if (!tagInput || !tagsContainer || !tagInputContainer) return;

                const tagValue = tagInput.value.trim().toLowerCase(); // Normalize to lowercase
                if (!tagValue) return;

                // Check if tag already exists (case-insensitive)
                const existingTags = Array.from(tagsContainer.querySelectorAll('input[name="tags[]"]')).map(input =>
                    input.value.toLowerCase());
                if (existingTags.includes(tagValue)) {
                    tagInput.value = ''; // Clear input even if duplicate
                    // Optionally show a message
                    // showNotification('error', 'Duplicate Tag', `Tag "${tagValue}" already added.`);
                    return;
                }

                const tagElement = document.createElement('span');
                tagElement.className =
                    'tag-item inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-200 border border-indigo-200/50 dark:border-indigo-800/50 animate-fade-in';
                tagElement.innerHTML = `
                <span>${tagValue}</span>
                <input type="hidden" name="tags[]" value="${tagValue}">
                <button type="button" class="remove-tag-btn ml-1.5 -mr-1 flex-shrink-0 text-indigo-400 hover:text-indigo-600 dark:text-indigo-500 dark:hover:text-indigo-300">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            `;

                tagsContainer.insertBefore(tagElement, tagInputContainer);
                tagInput.value = ''; // Clear input
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons({
                        elements: [tagElement.querySelector('.remove-tag-btn i')]
                    });
                }
            }

            if (addTagBtn) addTagBtn.addEventListener('click', addTagFromInput);
            if (tagInput) tagInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addTagFromInput();
                }
            });
            if (tagsContainer) tagsContainer.addEventListener('click', (e) => {
                const removeBtn = e.target.closest('.remove-tag-btn');
                if (removeBtn) {
                    removeBtn.closest('.tag-item')?.remove();
                }
            });

            // Form submission handling (mostly the same as create)
            const form = document.getElementById('test-case-form');
            const submitBtn = document.getElementById('submit-btn');
            const submitBtnText = document.getElementById('submit-btn-text');

            if (form && submitBtn && submitBtnText) {
                form.addEventListener('submit', function(e) {
                    // Basic frontend validation (can be enhanced)
                    const title = document.getElementById('title').value.trim();
                    const suiteId = document.getElementById('suite-id-input')
                    .value; // Can be empty, that's ok
                    const storyId = document.getElementById('story-id-input')
                    .value; // This should not be empty
                    const steps = Array.from(document.querySelectorAll('.step-input')).map(input => input
                        .value.trim()).filter(s => s); // Filter empty steps
                    const expectedResults = document.getElementById('expected_results').value.trim();
                    const statusSelected = document.querySelector('input[name="status"]:checked');

                    let isValid = true;
                    if (!title) {
                        console.error("Title missing");
                        isValid = false;
                    }
                    if (!storyId) {
                        console.error("Story ID missing");
                        isValid = false;
                    }
                    if (steps.length === 0) {
                        console.error("Steps missing");
                        isValid = false;
                    }
                    if (!expectedResults) {
                        console.error("Expected Results missing");
                        isValid = false;
                    }
                    if (!statusSelected) {
                        console.error("Status missing");
                        isValid = false;
                    }

                    if (!isValid) {
                        e.preventDefault(); // Prevent submission
                        showNotification('error', 'Validation Error',
                            'Please fill all required fields (*).');
                        return;
                    }

                    // Disable button and show loading
                    submitBtn.disabled = true;
                    const originalText = submitBtnText.textContent;
                    submitBtnText.textContent = 'Updating...';

                    // Add spinner (consider reusing spinner logic)
                    const spinner = document.createElement('span');
                    spinner.innerHTML =
                        `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
                    submitBtn.prepend(spinner);

                    // Allow form to submit normally now (or use fetch API if preferred)
                });
            }
        });

        // Notification functions (ensure these are defined globally or included)
        function showNotification(type, title, message) {
            // Implementation from create view...
            const container = document.getElementById('notification-container');
            const titleEl = document.getElementById('notification-title');
            const messageEl = document.getElementById('notification-message');
            const iconEl = document.getElementById('notification-icon');
            if (!container || !titleEl || !messageEl || !iconEl) return;
            titleEl.textContent = title;
            messageEl.textContent = message;
            const iconMap = {
                success: 'check-circle',
                error: 'alert-circle'
            };
            const colorMap = {
                success: {
                    bg: 'bg-green-50/80 border-green-200/50 dark:bg-green-900/30 dark:border-green-800/30',
                    text: 'text-green-800 dark:text-green-200',
                    icon: 'text-green-600 dark:text-green-400'
                },
                error: {
                    bg: 'bg-red-50/80 border-red-200/50 dark:bg-red-900/30 dark:border-red-800/30',
                    text: 'text-red-800 dark:text-red-200',
                    icon: 'text-red-600 dark:text-red-400'
                }
            };
            container.className =
                `fixed bottom-6 right-6 z-50 max-w-sm w-full shadow-lg border rounded-xl p-4 ${colorMap[type]?.bg || ''}`;
            titleEl.className = `font-medium mb-1 ${colorMap[type]?.text || ''}`;
            messageEl.className = `text-sm ${colorMap[type]?.text || ''} opacity-90`;
            iconEl.innerHTML =
                `<i data-lucide="${iconMap[type] || 'info'}" class="w-5 h-5 ${colorMap[type]?.icon || ''}"></i>`;
            container.classList.remove('hidden');
            if (typeof lucide !== 'undefined') {
                lucide.createIcons({
                    elements: [iconEl]
                });
            }
            setTimeout(hideNotification, 5000);
        }

        function hideNotification() {
            document.getElementById('notification-container')?.classList.add('hidden');
        }
        const flash = document.getElementById('flash-messages');
        if (flash?.dataset.success) showNotification('success', 'Success', flash.dataset.success);
        if (flash?.dataset.error) showNotification('error', 'Error', flash.dataset.error);
    </script>
@endpush
