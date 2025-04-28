@php
    // Determine initial selected suite if provided
    $initialSelectedSuiteId = $selectedSuite->id ?? old('suite_id', '');
    $initialSelectedSuiteName =
        $selectedSuite->name ??
        ($initialSelectedSuiteId ? $testSuites->firstWhere('id', $initialSelectedSuiteId)?->name : '');

    // Determine initial selected story if provided
    $initialSelectedStoryId = $selectedStory->id ?? old('story_id', '');
    $initialSelectedStoryName =
        $selectedStory->title ??
        ($initialSelectedStoryId ? $storiesForFilter->firstWhere('id', $initialSelectedStoryId)?->title : '');
@endphp

@extends('layouts.dashboard')

@section('title', isset($selectedSuite) ? "Create Test Case in {$selectedSuite->name}" : 'Create Test Case')

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
    @if (isset($selectedSuite))
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <a href="{{ route('dashboard.projects.test-suites.show', [$project->id, $selectedSuite->id]) }}"
                class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
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
    <div id="test-case-create-container" data-project-id="{{ $project->id }}"
    x-data="testCaseCreator({
        projectId: '{{ $project->id }}',
        projectName: '{{ $project->name }}',
        suiteId: '{{ $initialSelectedSuiteId }}',
        suiteName: '{{ $initialSelectedSuiteName ?: '-- Select a Test Suite --' }}',
        storyId: '{{ $initialSelectedStoryId }}',
        storyName: '{{ $initialSelectedStoryName ?: '-- Select a Story --' }}',
        apiEndpoint: '{{ route('api.ai.generate', 'test-case') }}',
        csrfToken: '{{ csrf_token() }}'
    })" class="h-full">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                        Create New Test Case
                    </h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        @if (isset($selectedSuite))
                            Adding test case to {{ $selectedSuite->name }} in {{ $project->name }}
                        @else
                            Create a new test case in {{ $project->name }}
                        @endif
                    </p>
                </div>
                <div>
                    @if (isset($selectedSuite))
                        <a href="{{ route('dashboard.projects.test-suites.show', [$project->id, $selectedSuite->id]) }}"
                            class="group inline-flex items-center px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-200 bg-white/70 dark:bg-zinc-800/50 hover:bg-white dark:hover:bg-zinc-700/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                            <i data-lucide="arrow-left"
                                class="mr-2 -ml-1 w-4 h-4 transition-transform group-hover:-translate-x-1"></i>
                            Back to Suite
                        </a>
                    @else
                        <a href="{{ route('dashboard.projects.test-cases.index', $project->id) }}"
                            class="group inline-flex items-center px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-200 bg-white/70 dark:bg-zinc-800/50 hover:bg-white dark:hover:bg-zinc-700/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                            <i data-lucide="arrow-left"
                                class="mr-2 -ml-1 w-4 h-4 transition-transform group-hover:-translate-x-1"></i>
                            Back to Test Cases
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Notification Toast --}}
        <div id="notification-container"
            x-show="notification.show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2"
            class="fixed bottom-6 right-6 z-50 max-w-md w-full"
            @click.away="notification.show = false">
            <div class="flex items-start p-4 rounded-xl shadow-lg border"
                :class="{
                    'bg-green-50 border-green-200 dark:bg-green-900/30 dark:border-green-800': notification.type === 'success',
                    'bg-red-50 border-red-200 dark:bg-red-900/30 dark:border-red-800': notification.type === 'error',
                    'bg-blue-50 border-blue-200 dark:bg-blue-900/30 dark:border-blue-800': notification.type === 'info'
                }">
                <div class="flex-shrink-0" x-show="notification.type === 'success'">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400"></i>
                </div>
                <div class="flex-shrink-0" x-show="notification.type === 'error'">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 dark:text-red-400"></i>
                </div>
                <div class="flex-shrink-0" x-show="notification.type === 'info'">
                    <i data-lucide="info" class="w-5 h-5 text-blue-600 dark:text-blue-400"></i>
                </div>
                <div class="ml-3 flex-1">
                    <h4 class="text-sm font-medium"
                        :class="{
                            'text-green-800 dark:text-green-200': notification.type === 'success',
                            'text-red-800 dark:text-red-200': notification.type === 'error',
                            'text-blue-800 dark:text-blue-200': notification.type === 'info'
                        }"
                        x-text="notification.title"></h4>
                    <p class="mt-1 text-sm"
                        :class="{
                            'text-green-700 dark:text-green-300': notification.type === 'success',
                            'text-red-700 dark:text-red-300': notification.type === 'error',
                            'text-blue-700 dark:text-blue-300': notification.type === 'info'
                        }"
                        x-text="notification.message"></p>
                </div>
                <button @click="notification.show = false"
                    class="ml-4 flex-shrink-0 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        {{-- Mode Toggle --}}
        <div class="mb-8 flex justify-center">
            <div class="inline-flex bg-zinc-100 dark:bg-zinc-800 p-1 rounded-lg shadow-sm">
                <button
                    @click="creationMode = 'manual'"
                    :class="{ 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-sm border-b-2 border-blue-500': creationMode === 'manual', 'text-zinc-600 dark:text-zinc-400 hover:bg-white/30 dark:hover:bg-zinc-700/30': creationMode !== 'manual' }"
                    class="px-6 py-3 rounded-md font-medium transition-all duration-200 flex items-center space-x-2">
                    <i data-lucide="pen-square" class="w-5 h-5"></i>
                    <span>Manual Entry</span>
                </button>
                <button
                    @click="creationMode = 'ai'"
                    :class="{ 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-sm border-b-2 border-blue-500': creationMode === 'ai', 'text-zinc-600 dark:text-zinc-400 hover:bg-white/30 dark:hover:bg-zinc-700/30': creationMode !== 'ai' }"
                    class="px-6 py-3 rounded-md font-medium transition-all duration-200 flex items-center space-x-2">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                    <span>AI Generation</span>
                </button>
            </div>
        </div>

        {{-- Form Container --}}
        <div class="bg-white/70 dark:bg-zinc-800/50 rounded-2xl shadow-2xl border border-zinc-200/50 dark:border-zinc-700/30 backdrop-blur-lg transition-all duration-300 hover:shadow-2xl">
            <div class="p-8">
                @php
                    $createRoute = isset($selectedSuite)
                        ? route('dashboard.projects.test-suites.test-cases.store', [$project->id, $selectedSuite->id])
                        : route('dashboard.projects.test-cases.store', $project->id);
                @endphp

                {{-- AI Generation Section --}}
                <div x-show="creationMode === 'ai'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0">

                    {{-- AI Generation Grid --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                        {{-- Left Column - Context Panel --}}
                        <div class="lg:col-span-1 space-y-6">
                            <div class="bg-gradient-to-br from-blue-50/80 to-indigo-50/80 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-4 border border-blue-100/70 dark:border-blue-800/50">
                                <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-2 flex items-center">
                                    <i data-lucide="lightbulb" class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400"></i>
                                    Context for AI
                                </h3>
                                <p class="text-sm text-blue-700/90 dark:text-blue-300 mb-4">
                                    Add context to help the AI generate a better test case.
                                </p>

                                {{-- Required Context: Story --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-blue-700 dark:text-blue-300 mb-2">
                                        Story <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select x-model="storyId" @change="onStoryChange" class="w-full px-4 py-2.5 rounded-lg border border-blue-200/70 dark:border-blue-800/50 bg-white/70 dark:bg-zinc-800/50 text-blue-800 dark:text-blue-200 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all">
                                            <option value="">Select Story</option>
                                            @foreach ($storiesForFilter as $story)
                                                <option value="{{ $story->id }}" {{ $initialSelectedStoryId == $story->id ? 'selected' : '' }}>
                                                    {{ $story->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <i data-lucide="clipboard-list" class="absolute right-3 top-3 w-5 h-5 text-blue-400"></i>
                                    </div>
                                    <p class="mt-1 text-xs text-blue-600/70 dark:text-blue-400/70">
                                        Provides essential context about requirements and acceptance criteria.
                                    </p>
                                </div>

                                {{-- Optional Context: Test Suite --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-blue-700 dark:text-blue-300 mb-2">
                                        Test Suite <span class="text-blue-500 dark:text-blue-400 text-xs font-normal">(Optional)</span>
                                    </label>
                                    <div class="relative">
                                        <select x-model="suiteId" class="w-full px-4 py-2.5 rounded-lg border border-blue-200/70 dark:border-blue-800/50 bg-white/70 dark:bg-zinc-800/50 text-blue-800 dark:text-blue-200 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all">
                                            <option value="">Select Test Suite</option>
                                            @foreach ($testSuites as $suite)
                                                <option value="{{ $suite->id }}" {{ $initialSelectedSuiteId == $suite->id ? 'selected' : '' }}>
                                                    {{ $suite->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <i data-lucide="folder" class="absolute right-3 top-3 w-5 h-5 text-blue-400"></i>
                                    </div>
                                    <p class="mt-1 text-xs text-blue-600/70 dark:text-blue-400/70">
                                        Helps align with similar test cases in the suite.
                                    </p>
                                </div>

                                {{-- Code Context --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-blue-700 dark:text-blue-300 mb-2">
                                        Code Context <span class="text-blue-500 dark:text-blue-400 text-xs font-normal">(Optional)</span>
                                    </label>
                                    <div class="relative">
                                        <textarea x-model="codeContext" rows="4" placeholder="Paste relevant code snippets here" class="w-full px-4 py-2.5 rounded-lg border border-blue-200/70 dark:border-blue-800/50 bg-white/70 dark:bg-zinc-800/50 text-blue-800 dark:text-blue-200 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all font-mono text-sm"></textarea>
                                    </div>
                                    <p class="mt-1 text-xs text-blue-600/70 dark:text-blue-400/70">
                                        Providing implementation code helps create more realistic test steps.
                                    </p>
                                </div>

                                {{-- UI/UX Description --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-blue-700 dark:text-blue-300 mb-2">
                                        UI/UX Description <span class="text-blue-500 dark:text-blue-400 text-xs font-normal">(Optional)</span>
                                    </label>
                                    <div class="relative">
                                        <textarea x-model="uiDescription" rows="3" placeholder="Describe the UI elements being tested..." class="w-full px-4 py-2.5 rounded-lg border border-blue-200/70 dark:border-blue-800/50 bg-white/70 dark:bg-zinc-800/50 text-blue-800 dark:text-blue-200 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all"></textarea>
                                    </div>
                                    <p class="mt-1 text-xs text-blue-600/70 dark:text-blue-400/70">
                                        Helps create more detailed steps for UI-focused tests.
                                    </p>
                                </div>

                                {{-- File upload --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-blue-700 dark:text-blue-300 mb-2">
                                        Upload Document <span class="text-blue-500 dark:text-blue-400 text-xs font-normal">(Optional)</span>
                                    </label>
                                    <div class="relative">
                                        <input type="file" @change="handleFileUpload" accept=".txt,.md,.csv,.json,.xml,.yaml,.html,.js,.ts,.py,.php,.java,.cs,.rb" class="hidden" id="contextFile">
                                        <label for="contextFile" class="w-full flex items-center justify-center px-4 py-2.5 border border-dashed border-blue-300 dark:border-blue-700 rounded-lg text-blue-700 dark:text-blue-300 hover:bg-blue-50/50 dark:hover:bg-blue-900/20 transition-colors cursor-pointer">
                                            <i data-lucide="upload" class="w-5 h-5 mr-2"></i>
                                            <span x-text="documentFile ? documentFile.name : 'Choose file'"></span>
                                        </label>
                                    </div>
                                    <p class="mt-1 text-xs text-blue-600/70 dark:text-blue-400/70">
                                        Upload documentation or specs for additional context.
                                    </p>
                                </div>
                            </div>

                            {{-- AI Generation History --}}
                            <div class="bg-zinc-50/80 dark:bg-zinc-800/80 rounded-xl p-4 border border-zinc-200/70 dark:border-zinc-700/50">
                                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-3 flex items-center">
                                    <i data-lucide="history" class="w-5 h-5 mr-2 text-zinc-600 dark:text-zinc-400"></i>
                                    Generation History
                                </h3>
                                <div class="space-y-2 max-h-64 overflow-auto pr-2" id="generation-history">
                                    <template x-for="(item, index) in generationHistory" :key="index">
                                        <div @click="useHistoryItem(index)" class="p-3 rounded-lg cursor-pointer text-sm bg-white dark:bg-zinc-700/30 border border-zinc-200/70 dark:border-zinc-700/50 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                            <div class="flex justify-between items-start">
                                                <h4 class="font-medium text-zinc-900 dark:text-zinc-100" x-text="item.title || 'Generated Test Case'"></h4>
                                                <span class="text-xs text-zinc-500 dark:text-zinc-400" x-text="formatTime(item.timestamp)"></span>
                                            </div>
                                            <p class="mt-1 text-zinc-600 dark:text-zinc-400 line-clamp-2" x-text="item.prompt"></p>
                                        </div>
                                    </template>
                                    <div x-show="generationHistory.length === 0" class="text-center py-4 text-zinc-500 dark:text-zinc-400 text-sm italic">
                                        No generation history yet
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column - Prompt & Results --}}
                        <div class="lg:col-span-2 space-y-6">
                            {{-- Prompt Builder --}}
                            <div class="bg-white dark:bg-zinc-800 rounded-xl p-6 border border-zinc-200/70 dark:border-zinc-700/50 shadow-sm">
                                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-4 flex items-center">
                                    <i data-lucide="message-square-plus" class="w-5 h-5 mr-2 text-zinc-600 dark:text-zinc-400"></i>
                                    Prompt Builder
                                </h3>

                                {{-- Prompt Templates --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Template <span class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional)</span>
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <button @click="useTemplate('functional')" class="flex items-center px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="check-square" class="w-4 h-4 mr-2 text-green-500"></i>
                                            Functional Test
                                        </button>
                                        <button @click="useTemplate('ui')" class="flex items-center px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="layout" class="w-4 h-4 mr-2 text-blue-500"></i>
                                            UI/UX Test
                                        </button>
                                        <button @click="useTemplate('edge')" class="flex items-center px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="alert-triangle" class="w-4 h-4 mr-2 text-amber-500"></i>
                                            Edge Case Test
                                        </button>
                                        <button @click="useTemplate('error')" class="flex items-center px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="x-circle" class="w-4 h-4 mr-2 text-red-500"></i>
                                            Error Handling Test
                                        </button>
                                    </div>
                                </div>

                                {{-- Prompt Input --}}
                                <div class="mb-4">
                                    <label for="ai-prompt" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Prompt <span class="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        x-model="prompt"
                                        id="ai-prompt"
                                        rows="5"
                                        placeholder="Describe the test case you want to create..."
                                        class="w-full px-4 py-3 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all"
                                        :class="{ 'border-red-500 dark:border-red-500': promptError }"></textarea>
                                    <p x-show="promptError" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        <span x-text="promptError"></span>
                                    </p>
                                </div>

                                {{-- Advanced Options --}}
                                <div class="mb-4" x-data="{ showOptions: false }">
                                    <button @click="showOptions = !showOptions" type="button" class="flex items-center text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 transition-colors">
                                        <i data-lucide="sliders" class="w-4 h-4 mr-1"></i>
                                        <span>Advanced Options</span>
                                        <i data-lucide="chevron-down" class="w-4 h-4 ml-1 transition-transform" :class="{ 'rotate-180': showOptions }"></i>
                                    </button>

                                    <div x-show="showOptions" x-transition class="mt-3 p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-700">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            {{-- Priority Preference --}}
                                            <div>
                                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                    Priority
                                                </label>
                                                <select x-model="defaultPriority" class="w-full px-3 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-sm">
                                                    <option value="">Let AI Decide</option>
                                                    <option value="high">High</option>
                                                    <option value="medium">Medium</option>
                                                    <option value="low">Low</option>
                                                </select>
                                            </div>

                                            {{-- Step Count --}}
                                            <div>
                                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                    Steps Detail Level
                                                </label>
                                                <select x-model="stepDetail" class="w-full px-3 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-sm">
                                                    <option value="normal">Normal Detail</option>
                                                    <option value="detailed">Very Detailed</option>
                                                    <option value="concise">Concise</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button
                                        @click="generateTestCase"
                                        class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg shadow-md hover:shadow-lg flex items-center transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="isGenerating || !storyId || !prompt">
                                        <template x-if="!isGenerating">
                                            <i data-lucide="sparkles" class="w-5 h-5 mr-2"></i>
                                        </template>
                                        <template x-if="isGenerating">
                                            <svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                        </template>
                                        <span x-text="isGenerating ? 'Generating...' : 'Generate Test Case'"></span>
                                    </button>
                                </div>
                            </div>

                            <!-- Generated Result -->
                            <div x-show="generatedTestCase" x-transition
                                class="bg-gradient-to-br from-blue-50/80 to-indigo-50/80 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6 border border-blue-200/70 dark:border-blue-800/40 shadow-sm">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 flex items-center">
                                        <i data-lucide="file-check" class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400"></i>
                                        Generated Test Case
                                    </h3>
                                    <div class="flex items-center space-x-2">
                                        <button @click="regenerateTestCase"
                                            class="p-2 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors"
                                            :disabled="isGenerating" title="Regenerate">
                                            <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                                        </button>
                                        <button @click="copyToForm" type="button"
                                            class="p-2 rounded-lg text-green-600 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors"
                                            title="Use This Test Case">
                                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-4 bg-white/80 dark:bg-zinc-800/60 rounded-lg p-4 border border-blue-100 dark:border-blue-800/30">
                                    <!-- Title -->
                                    <div>
                                        <h4 class="text-sm font-medium text-blue-700 dark:text-blue-300">Title:</h4>
                                        <p class="mt-1 text-zinc-900 dark:text-zinc-100 font-medium" x-text="generatedTestCase.title"></p>
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <h4 class="text-sm font-medium text-blue-700 dark:text-blue-300">Description:</h4>
                                        <p class="mt-1 text-zinc-800 dark:text-zinc-200 whitespace-pre-line" x-text="generatedTestCase.description"></p>
                                    </div>

                                    <!-- Steps -->
                                    <div>
                                        <h4 class="text-sm font-medium text-blue-700 dark:text-blue-300">Steps:</h4>
                                        <ol class="mt-1 space-y-1 text-zinc-800 dark:text-zinc-200 list-decimal list-inside">
                                            <template x-for="(step, index) in generatedTestCase.steps" :key="index">
                                                <li x-text="step"></li>
                                            </template>
                                        </ol>
                                    </div>

                                    <!-- Expected Results -->
                                    <div>
                                        <h4 class="text-sm font-medium text-blue-700 dark:text-blue-300">Expected Results:</h4>
                                        <p class="mt-1 text-zinc-800 dark:text-zinc-200 whitespace-pre-line" x-text="generatedTestCase.expected_results"></p>
                                    </div>

                                    <!-- Tags and Priority -->
                                    <div class="flex flex-wrap gap-2 pt-2">
                                        <div class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': generatedTestCase.priority === 'high',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': generatedTestCase.priority === 'medium',
                                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': generatedTestCase.priority === 'low'
                                            }">
                                            <i data-lucide="flag" class="w-3 h-3 mr-1"></i>
                                            <span x-text="generatedTestCase.priority ? (generatedTestCase.priority.charAt(0).toUpperCase() + generatedTestCase.priority.slice(1)) + ' Priority' : 'Priority'"></span>
                                        </div>

                                        <template x-for="(tag, index) in generatedTestCase.tags" :key="index">
                                            <div class="inline-flex items-center px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 text-xs font-medium">
                                                <i data-lucide="tag" class="w-3 h-3 mr-1"></i>
                                                <span x-text="tag"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manual Entry Form -->
                <form id="test-case-form" x-show="creationMode === 'manual'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    action="{{ $createRoute }}{{ request()->filled('story_id') ? '?story_id=' . request('story_id') : '' }}"
                    method="POST"
                    @submit="return submitForm($event)">
                    @csrf

                    {{-- Hidden Inputs --}}
                    <input type="hidden" name="suite_id" id="suite-id-input" :value="suiteId">
                    <input type="hidden" name="story_id" id="story-id-input" :value="storyId">
                    <input type="hidden" name="status" id="status-input" value="draft">

                    <div id="test-case-form-fields" class="space-y-8">
                        <div class="animate-fade-in-left">
                            <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 mb-2 flex items-center">
                                <i data-lucide="check-square" class="w-5 h-5 mr-2 text-blue-500"></i>
                                Test Case Details
                            </h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Define your test case details, including story, suite, steps, and expected results
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                            {{-- Story Selection --}}
                            <div class="sm:col-span-3 animate-fade-in-up dropdown-container">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Story <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="story_id" id="story_id" class="w-full px-4 py-2.5 rounded-xl border-0 bg-zinc-100/50 dark:bg-zinc-700/30 shadow-inner" x-model="storyId" @change="onStoryChange" required>
                                        <option value="">-- Select a Story --</option>
                                        @foreach ($storiesForFilter as $story)
                                            <option value="{{ $story->id }}" {{ $initialSelectedStoryId == $story->id ? 'selected' : '' }}>
                                                {{ $story->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i data-lucide="clipboard-list" class="absolute right-3 top-3 w-5 h-5 text-zinc-400"></i>
                                </div>
                                @error('story_id')
                                    <p class="mt-2 text-sm text-red-500 validation-error">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Suite Selection --}}
                            <div class="sm:col-span-3 animate-fade-in-up dropdown-container">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Test Suite
                                </label>
                                <div class="relative">
                                    <select name="suite_id" id="suite_id" class="w-full px-4 py-2.5 rounded-xl border-0 bg-zinc-100/50 dark:bg-zinc-700/30 shadow-inner" x-model="suiteId">
                                        <option value="">-- Select a Test Suite --</option>
                                        @foreach ($testSuites as $suite)
                                            <option value="{{ $suite->id }}" {{ $initialSelectedSuiteId == $suite->id ? 'selected' : '' }}>
                                                {{ $suite->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i data-lucide="folder" class="absolute right-3 top-3 w-5 h-5 text-zinc-400"></i>
                                </div>
                                @error('suite_id')
                                    <p class="mt-2 text-sm text-red-500 animate-fade-in">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Title --}}
                            <div class="sm:col-span-6 animate-fade-in-up">
                                <div class="relative">
                                    <label for="title" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Test Case Title <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="title"
                                        id="title"
                                        x-model="formData.title"
                                        class="w-full px-4 py-2.5 rounded-xl border-0 bg-zinc-100/50 dark:bg-zinc-700/30 shadow-inner text-zinc-700 dark:text-zinc-200 placeholder-zinc-400"
                                        placeholder="Enter test case title"
                                        required>
                                </div>
                                @error('title')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div class="sm:col-span-6 animate-fade-in-up">
                                <div class="relative">
                                    <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Description
                                    </label>
                                    <textarea
                                        id="description"
                                        name="description"
                                        x-model="formData.description"
                                        rows="3"
                                        class="w-full px-4 py-2.5 rounded-xl border-0 bg-zinc-100/50 dark:bg-zinc-700/30 shadow-inner text-zinc-700 dark:text-zinc-200 placeholder-zinc-400"
                                        placeholder="A brief description of what this test case verifies"></textarea>
                                </div>
                                @error('description')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                    Brief description of what this test case verifies
                                </p>
                            </div>

                            {{-- Priority --}}
                            <div class="sm:col-span-6 animate-fade-in-up">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Priority <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="priority-options">
                                    @php $selectedPriority = old('priority', 'medium'); @endphp
                                    @foreach (['low', 'medium', 'high'] as $prio)
                                        @php
                                            $labels = [
                                                'low' => ['Low', 'Minor functionality, edge cases'],
                                                'medium' => ['Medium', 'Important functionality but not critical'],
                                                'high' => ['High', 'Critical functionality or user-facing'],
                                            ];
                                        @endphp
                                        <div
                                            @click="formData.priority = '{{ $prio }}'"
                                            class="priority-option relative p-4 rounded-xl cursor-pointer transition-all duration-300 group ring-1 ring-zinc-200/70 dark:ring-zinc-600/50 hover:ring-zinc-300 dark:hover:ring-zinc-500"
                                            :class="{'ring-2 ring-blue-500 dark:ring-blue-400 bg-zinc-100/50 dark:bg-zinc-700/30': formData.priority === '{{ $prio }}'}"
                                            data-value="{{ $prio }}">
                                            <div class="flex items-start space-x-3">
                                                <div class="flex-1">
                                                    <div class="flex items-center">
                                                        <h4 class="text-base font-medium text-zinc-800 dark:text-zinc-200">
                                                            {{ $labels[$prio][0] }}
                                                        </h4>
                                                        @php
                                                            $badgeColors = [
                                                                'low' => 'green',
                                                                'medium' => 'yellow',
                                                                'high' => 'red',
                                                            ];
                                                        @endphp
                                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $badgeColors[$prio] }}-100 dark:bg-{{ $badgeColors[$prio] }}-900/30 text-{{ $badgeColors[$prio] }}-800 dark:text-{{ $badgeColors[$prio] }}-300">
                                                            {{ ucfirst($prio) }}
                                                        </span>
                                                    </div>
                                                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                                        {{ $labels[$prio][1] }}
                                                    </p>
                                                </div>
                                                <div class="flex items-center h-5 ml-2">
                                                    <div class="priority-radio bg-zinc-300 dark:bg-zinc-600 w-4 h-4 rounded-full transition-colors duration-300 relative"
                                                         :class="{'bg-blue-600 dark:bg-blue-500': formData.priority === '{{ $prio }}'}">
                                                        <div x-show="formData.priority === '{{ $prio }}'" class="absolute inset-0 flex items-center justify-center">
                                                            <div class="w-2 h-2 rounded-full bg-white dark:bg-zinc-800"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="priority" id="priority-input" x-model="formData.priority">
                                @error('priority')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Test Steps --}}
                            <div class="sm:col-span-6 space-y-4 animate-fade-in-up" id="test-steps-container">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Test Steps <span class="text-red-500">*</span>
                                    </label>
                                    <button type="button" @click="addStep"
                                        class="inline-flex items-center text-sm px-3 py-1.5 rounded-lg bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors">
                                        <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                                        Add Step
                                    </button>
                                </div>
                                <div class="space-y-3" id="steps-list">
                                    <template x-for="(step, index) in formData.steps" :key="index">
                                        <div class="step-item flex items-start space-x-3">
                                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-semibold text-sm">
                                                <span class="step-number" x-text="index + 1"></span>
                                            </div>
                                            <div class="flex-1 relative">
                                                <input
                                                    :name="'steps[' + index + ']'"
                                                    type="text"
                                                    x-model="formData.steps[index]"
                                                    class="step-input w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-lg shadow-inner p-3 text-zinc-700 dark:text-zinc-200 focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300"
                                                    placeholder="Describe the step to perform">
                                            </div>
                                            <button type="button" @click="removeStep(index)"
                                                class="remove-step-btn flex-shrink-0 p-1.5 rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-red-500 dark:hover:text-red-400 transition-colors"
                                                :disabled="formData.steps.length <= 1">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                @error('steps')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                                @error('steps.*')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Expected Results --}}
                            <div class="sm:col-span-6 animate-fade-in-up">
                                <div class="relative">
                                    <label for="expected_results" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Expected Results <span class="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        id="expected_results"
                                        name="expected_results"
                                        x-model="formData.expected_results"
                                        rows="4"
                                        class="w-full px-4 py-2.5 rounded-xl border-0 bg-zinc-100/50 dark:bg-zinc-700/30 shadow-inner text-zinc-700 dark:text-zinc-200"
                                        placeholder="Describe what should happen when the test is executed correctly"></textarea>
                                </div>
                                @error('expected_results')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                    Describe what should happen when the test is executed correctly
                                </p>
                            </div>

                            {{-- Tags --}}
                            <div class="sm:col-span-6 animate-fade-in-up">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Tags
                                </label>
                                <div class="flex flex-wrap items-center gap-2 mb-2" id="tags-container">
                                    <template x-for="(tag, index) in formData.tags" :key="index">
                                        <span class="tag-item inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 border border-blue-200/50 dark:border-blue-800/50">
                                            <span x-text="tag"></span>
                                            <input type="hidden" :name="'tags[' + index + ']'" :value="tag">
                                            <button type="button" @click="removeTag(index)"
                                                class="remove-tag-btn ml-1.5 -mr-1 flex-shrink-0 text-blue-400 hover:text-blue-600 dark:text-blue-500 dark:hover:text-blue-300">
                                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </span>
                                    </template>
                                    <div class="relative" id="tag-input-container">
                                        <input
                                            type="text"
                                            id="tag-input"
                                            x-model="newTag"
                                            @keydown.enter.prevent="addTag"
                                            class="border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-full p-1 pl-3 pr-8 text-sm text-zinc-700 dark:text-zinc-200 focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300"
                                            placeholder="Add a tag">
                                        <button type="button" @click="addTag"
                                            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                                            <i data-lucide="plus" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Press Enter or click + to add a tag
                                </p>
                                @error('tags')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                                @error('tags.*')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="mt-12 flex justify-end space-x-4 animate-fade-in-up">
                            <button type="button" id="cancel-btn"
                                class="px-6 py-2.5 text-zinc-700 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-white bg-zinc-100/70 dark:bg-zinc-700/50 rounded-xl hover:bg-zinc-200/50 dark:hover:bg-zinc-600/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md transform hover:-translate-y-0.5">
                                Cancel
                            </button>
                            <button type="submit" id="submit-btn"
                                class="relative px-8 py-2.5 text-white bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-700 dark:to-indigo-500 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
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

        <!-- Test Case Creation Guide -->
        <div class="bg-white dark:bg-zinc-800/50 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 backdrop-blur-sm mt-8">
            <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/30 dark:bg-zinc-800/30">
                <h2 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-amber-500 dark:text-amber-400"></i>
                    Test Case Creation Guide
                </h2>
            </div>
            <div class="p-6">
                <div class="prose dark:prose-invert max-w-none">
                    <div class="space-y-4">
                        <h3 class="text-zinc-900 dark:text-white">What Makes a Good Test Case?</h3>
                        <div class="p-4 rounded-xl bg-emerald-50/50 dark:bg-emerald-900/20 border border-emerald-200/50 dark:border-emerald-800/50">
                            <p class="font-medium text-emerald-800 dark:text-emerald-300">Key Components:</p>
                            <ul class="mt-2 space-y-1 text-zinc-700 dark:text-zinc-300">
                                <li><strong>Clear Title</strong> - Descriptive and specific</li>
                                <li><strong>Precise Steps</strong> - Easy to follow, step-by-step instructions</li>
                                <li><strong>Expected Results</strong> - Clear criteria for pass/fail judgments</li>
                                <li><strong>Appropriate Priority</strong> - Correctly indicate importance</li>
                            </ul>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4 mt-6">
                            <div class="p-4 rounded-xl bg-blue-50/50 dark:bg-blue-900/20 border border-blue-200/50 dark:border-blue-800/50">
                                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300">Example: Login Test</h4>
                                <p class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">
                                    <strong>Title:</strong> Verify User Login with Valid Credentials<br>
                                    <strong>Steps:</strong><br>
                                    1. Navigate to login page<br>
                                    2. Enter valid username<br>
                                    3. Enter valid password<br>
                                    4. Click login button<br>
                                    <strong>Expected Results:</strong> User is successfully logged in and redirected to dashboard.
                                </p>
                            </div>

                            <div class="p-4 rounded-xl bg-purple-50/50 dark:bg-purple-900/20 border border-purple-200/50 dark:border-purple-800/50">
                                <h4 class="text-sm font-medium text-purple-800 dark:text-purple-300">Example: Form Validation</h4>
                                <p class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">
                                    <strong>Title:</strong> Verify Email Field Validation<br>
                                    <strong>Steps:</strong><br>
                                    1. Navigate to registration form<br>
                                    2. Enter invalid email format (without @)<br>
                                    3. Click submit button<br>
                                    <strong>Expected Results:</strong> Form displays error message indicating invalid email format.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
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

        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('testCaseCreator', (config) => ({
                // Configuration values
                projectId: config.projectId || '',
                projectName: config.projectName || '',
                suiteId: config.suiteId || '',
                suiteName: config.suiteName || '',
                storyId: config.storyId || '',
                storyName: config.storyName || '',
                apiEndpoint: config.apiEndpoint,
                csrfToken: config.csrfToken,

                // UI state
                creationMode: 'manual', // 'manual' or 'ai'
                isGenerating: false,
                promptError: '',
                notification: {
                    show: false,
                    type: 'info',
                    title: '',
                    message: ''
                },

                // Data state for AI generation
                prompt: '',
                codeContext: '',
                uiDescription: '',
                documentFile: null,
                documentContent: '',
                aiProvider: 'claude',
                defaultPriority: '',
                stepDetail: 'normal',
                generatedTestCase: null,
                generationHistory: [],
                newTag: '',

                // Form data for manual creation
                formData: {
                    title: '',
                    description: '',
                    priority: 'medium',
                    steps: [''],
                    expected_results: '',
                    tags: []
                },

                // Lifecycle methods
                init() {
                    // Try to load history from localStorage
                    const savedHistory = localStorage.getItem('test_case_generation_history');
                    if (savedHistory) {
                        try {
                            this.generationHistory = JSON.parse(savedHistory).slice(0, 10);
                        } catch (e) {
                            console.error('Failed to parse generation history:', e);
                        }
                    }

                    // Initialize with at least one step
                    if (!this.formData.steps.length) {
                        this.formData.steps = [''];
                    }
                },

                // Methods for both modes
                onStoryChange() {
                    // Any story-specific logic here
                    console.log('Story changed to:', this.storyId);
                },

                // AI Generation Methods
                handleFileUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    this.documentFile = file;

                    // Process text files to extract content
                    if (file.size < 500000 && (
                            file.type.includes('text') ||
                            file.type.includes('application/json') ||
                            file.name.endsWith('.md') ||
                            file.name.endsWith('.csv') ||
                            file.name.endsWith('.txt') ||
                            file.name.endsWith('.js') ||
                            file.name.endsWith('.py') ||
                            file.name.endsWith('.php')
                        )) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.documentContent = e.target.result;
                        };
                        reader.readAsText(file);
                    }
                },

                useTemplate(type) {
                    switch (type) {
                        case 'functional':
                            this.prompt =
                                `Create a test case for verifying that [describe functionality] works correctly. The test should cover the main functionality path and verify the expected outcome.`;
                            break;
                        case 'ui':
                            this.prompt =
                                `Create a UI test case for verifying the appearance and behavior of [describe UI element/screen]. The test should check layout, responsiveness and user interactions.`;
                            break;
                        case 'edge':
                            this.prompt =
                                `Create a test case for the edge case scenario where [describe edge condition]. The test should verify the system handles this boundary condition appropriately.`;
                            break;
                        case 'error':
                            this.prompt =
                                `Create an error handling test case for when [describe error scenario]. The test should verify appropriate error messages and system recovery.`;
                            break;
                    }
                },

                async generateTestCase() {
                    if (!this.storyId || !this.prompt) {
                        this.promptError = 'Please select a story and enter a prompt';
                        return;
                    }

                    this.promptError = '';
                    this.isGenerating = true;

                    try {
                        // Build the request payload
                        const payload = {
                            prompt: this.prompt,
                            context: {
                                project_id: this.projectId,
                                story_id: this.storyId,
                                suite_id: this.suiteId || undefined,
                                code: this.codeContext || undefined,
                                ui_description: this.uiDescription || undefined,
                                document_content: this.documentContent || undefined
                            }
                        };

                        // Add advanced options if specified
                        if (this.defaultPriority) {
                            payload.context.priority = this.defaultPriority;
                        }

                        if (this.stepDetail !== 'normal') {
                            payload.context.step_detail = this.stepDetail;
                        }

                        // Call the AI generation API
                        const response = await fetch(this.apiEndpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Store the generated test case
                            this.generatedTestCase = result.data;

                            // Add to history
                            const historyEntry = {
                                timestamp: Date.now(),
                                prompt: this.prompt,
                                title: result.data.title,
                                data: result.data
                            };

                            this.generationHistory = [historyEntry, ...this.generationHistory].slice(0, 10);
                            localStorage.setItem('test_case_generation_history', JSON.stringify(this.generationHistory));

                            this.showNotification('success', 'Success!',
                                'Test case generated successfully. Use "Use This Test Case" to add it to the form and edit before saving.');
                        } else {
                            throw new Error(result.message || 'Failed to generate test case');
                        }
                    } catch (error) {
                        console.error('Generation error:', error);
                        this.showNotification('error', 'Generation Failed', error.message ||
                            'Failed to generate test case. Please try again.');
                    } finally {
                        this.isGenerating = false;
                    }
                },

                regenerateTestCase() {
                    // Just call generate again with the same settings
                    this.generateTestCase();
                },

                copyToForm() {
                    if (!this.generatedTestCase) return;

                    // Populate the form fields
                    this.formData = {
                        title: this.generatedTestCase.title || '',
                        description: this.generatedTestCase.description || '',
                        priority: this.generatedTestCase.priority || 'medium',
                        steps: this.generatedTestCase.steps || [''],
                        expected_results: this.generatedTestCase.expected_results || '',
                        tags: this.generatedTestCase.tags || []
                    };

                    // Switch to manual mode to show the form
                    this.creationMode = 'manual';

                    this.showNotification('info', 'Added to Form',
                        'Generated test case copied to form. You can now edit and submit it.');
                },

                useHistoryItem(index) {
                    const item = this.generationHistory[index];
                    if (!item) return;

                    this.generatedTestCase = item.data;
                    this.prompt = item.prompt;

                    this.showNotification('info', 'History Item Loaded', 'Test case loaded from history.');
                },

                // Manual Form Methods
                addStep() {
                    this.formData.steps.push('');
                },

                removeStep(index) {
                    if (this.formData.steps.length > 1) {
                        this.formData.steps.splice(index, 1);
                    }
                },

                addTag() {
                    if (this.newTag.trim() && !this.formData.tags.includes(this.newTag.trim())) {
                        this.formData.tags.push(this.newTag.trim());
                        this.newTag = '';
                    }
                },

                removeTag(index) {
                    this.formData.tags.splice(index, 1);
                },

                submitForm(event) {
                    // Validation
                    if (!this.storyId) {
                        this.showNotification('error', 'Validation Error', 'Please select a story');
                        return false;
                    }

                    if (!this.formData.title.trim()) {
                        this.showNotification('error', 'Validation Error', 'Please enter a title');
                        return false;
                    }

                    if (!this.formData.steps.length || !this.formData.steps[0].trim()) {
                        this.showNotification('error', 'Validation Error', 'Please add at least one step');
                        return false;
                    }

                    if (!this.formData.expected_results.trim()) {
                        this.showNotification('error', 'Validation Error', 'Please specify expected results');
                        return false;
                    }

                    // If validation passes, allow form submission
                    return true;
                },

                showNotification(type, title, message) {
                    this.notification = {
                        show: true,
                        type,
                        title,
                        message
                    };

                    // Auto-hide after a delay
                    setTimeout(() => {
                        this.notification.show = false;
                    }, 5000);
                },

                formatTime(timestamp) {
                    const date = new Date(timestamp);
                    return date.toLocaleString();
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();

            // For backward compatibility with existing event handlers
            document.getElementById('cancel-btn')?.addEventListener('click', function() {
                window.history.back();
            });
        });
    </script>
@endpush
