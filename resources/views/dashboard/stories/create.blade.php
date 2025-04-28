@php
    // Determine initial selected project
    $selectedProjectId = $selectedProject->id ?? old('project_id', '');
    $initialSelectedProjectName =
        $selectedProject->name ?? ($selectedProjectId ? $projects->firstWhere('id', $selectedProjectId)?->name : '');
@endphp

@extends('layouts.dashboard')

@section('title', 'Create New Story')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.stories.indexAll') }}"
            class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">Stories</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Create</span>
    </li>
@endsection

@section('content')
    <div class="h-full space-y-6" x-data="storyCreator({
        projectId: '{{ $selectedProjectId }}',
        projectName: '{{ $initialSelectedProjectName }}',
        initialEpicId: '{{ old('epic_id', '') }}',
        apiEndpoint: '{{ route('api.ai.generate', 'story') }}',
        projectsEndpoint: '{{ route('dashboard.projects') }}',
        epicsEndpoint: '/api/projects/',
        aiEnabled: true,
        csrfToken: '{{ csrf_token() }}'
    })">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    Create New Story
                </h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Add a new user story to your requirements
                </p>
            </div>
            <div>
                <a href="{{ route('dashboard.stories.indexAll') }}"
                    class="group inline-flex items-center px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-200 bg-white/70 dark:bg-zinc-800/50 hover:bg-white dark:hover:bg-zinc-700/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                    <i data-lucide="arrow-left"
                        class="mr-2 -ml-1 w-4 h-4 transition-transform group-hover:-translate-x-1"></i>
                    Back to Stories
                </a>
            </div>
        </div>

        <!-- Notification Toast (JS-driven) -->
        <div id="notification-container" x-show="notification.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2" class="fixed bottom-6 right-6 z-50 max-w-md"
            @click.away="notification.show = false">
            <div class="flex items-start p-4 rounded-xl shadow-lg border"
                :class="{
                    'bg-green-50 border-green-200 dark:bg-green-900/30 dark:border-green-800': notification
                        .type === 'success',
                    'bg-red-50 border-red-200 dark:bg-red-900/30 dark:border-red-800': notification.type === 'error',
                    'bg-blue-50 border-blue-200 dark:bg-blue-900/30 dark:border-blue-800': notification
                        .type === 'info'
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

        <!-- Creation Mode Tabs -->
        <div class="mb-6 flex justify-center">
            <div class="inline-flex bg-zinc-100 dark:bg-zinc-800 p-1 rounded-lg shadow-sm">
                <button @click="creationMode = 'manual'"
                    :class="{ 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-sm border-b-2 border-blue-500': creationMode === 'manual', 'text-zinc-600 dark:text-zinc-400 hover:bg-white/30 dark:hover:bg-zinc-700/30': creationMode !== 'manual' }"
                    class="px-6 py-3 rounded-md font-medium transition-all duration-200 flex items-center space-x-2">
                    <i data-lucide="pen-square" class="w-5 h-5"></i>
                    <span>Manual Entry</span>
                </button>
                <button @click="creationMode = 'ai'"
                    :class="{ 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-sm border-b-2 border-blue-500': creationMode === 'ai', 'text-zinc-600 dark:text-zinc-400 hover:bg-white/30 dark:hover:bg-zinc-700/30': creationMode !== 'ai' }"
                    class="px-6 py-3 rounded-md font-medium transition-all duration-200 flex items-center space-x-2">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                    <span>AI Generation</span>
                </button>
            </div>
        </div>

        <!-- Form Container -->
        <div
            class="bg-white/70 dark:bg-zinc-800/50 rounded-2xl shadow-2xl border border-zinc-200/50 dark:border-zinc-700/30 backdrop-blur-lg transition-all duration-300 hover:shadow-2xl">
            <div class="p-8">
                <!-- AI Generation Section -->
                <div x-show="creationMode === 'ai'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0">

                    <!-- AI Form Container -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Left Column - Context Panel -->
                        <div class="lg:col-span-1 space-y-6">
                            <div
                                class="bg-gradient-to-br from-indigo-50/80 to-purple-50/80 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-4 border border-indigo-100/70 dark:border-indigo-800/50">
                                <h3
                                    class="text-lg font-semibold text-indigo-800 dark:text-indigo-200 mb-2 flex items-center">
                                    <i data-lucide="lightbulb"
                                        class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400"></i>
                                    Context for AI
                                </h3>
                                <p class="text-sm text-indigo-700/90 dark:text-indigo-300 mb-4">
                                    Add context to help the AI generate a more relevant story.
                                </p>

                                <!-- Project selection -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-indigo-700 dark:text-indigo-300 mb-2">
                                        Project <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select x-model="projectId" @change="onProjectChange"
                                            class="w-full px-4 py-2.5 rounded-lg border border-indigo-200/70 dark:border-indigo-800/50 bg-white/70 dark:bg-zinc-800/50 text-indigo-800 dark:text-indigo-200 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all">
                                            <option value="">Select Project</option>
                                            @foreach ($projects as $project)
                                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                                            @endforeach
                                        </select>
                                        <i data-lucide="folders" class="absolute right-3 top-3 w-5 h-5 text-indigo-400"></i>
                                    </div>
                                </div>

                                <!-- Epic selection -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-indigo-700 dark:text-indigo-300 mb-2">
                                        Epic <span
                                            class="text-indigo-500 dark:text-indigo-400 text-xs font-normal">(Optional)</span>
                                    </label>
                                    <div class="relative">
                                        <select x-model="epicId" :disabled="!projectId || epics.length === 0"
                                            class="w-full px-4 py-2.5 rounded-lg border border-indigo-200/70 dark:border-indigo-800/50 bg-white/70 dark:bg-zinc-800/50 text-indigo-800 dark:text-indigo-200 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all">
                                            <option value="">None - Independent Story</option>
                                            <template x-for="epic in epics" :key="epic.id">
                                                <option :value="epic.id"
                                                    x-text="epic.name + (epic.status ? ' (' + epic.status + ')' : '')">
                                                </option>
                                            </template>
                                        </select>
                                        <i data-lucide="layers"
                                            class="absolute right-3 top-3 w-5 h-5 text-indigo-400"></i>
                                    </div>
                                    <p class="mt-1 text-xs text-indigo-600/70 dark:text-indigo-400/70">
                                        Selecting an epic provides context about related stories.
                                    </p>
                                </div>

                                <!-- Code/Documentation context -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-indigo-700 dark:text-indigo-300 mb-2">
                                        Code Context <span
                                            class="text-indigo-500 dark:text-indigo-400 text-xs font-normal">(Optional)</span>
                                    </label>
                                    <div class="relative">
                                        <textarea x-model="codeContext" rows="4" placeholder="Paste relevant code or API specs here"
                                            class="w-full px-4 py-2.5 rounded-lg border border-indigo-200/70 dark:border-indigo-800/50 bg-white/70 dark:bg-zinc-800/50 text-indigo-800 dark:text-indigo-200 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all font-mono text-sm"></textarea>
                                    </div>
                                    <p class="mt-1 text-xs text-indigo-600/70 dark:text-indigo-400/70">
                                        Providing relevant code helps create more specific stories.
                                    </p>
                                </div>

                                <!-- File upload -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-indigo-700 dark:text-indigo-300 mb-2">
                                        Upload Reference Files <span
                                            class="text-indigo-500 dark:text-indigo-400 text-xs font-normal">(Optional)</span>
                                    </label>
                                    <div class="relative">
                                        <input type="file" @change="handleFileUpload"
                                            accept=".txt,.md,.csv,.json,.xml,.yaml,.html,.js,.ts,.py,.php,.java,.cs,.rb"
                                            class="hidden" id="contextFiles" multiple>
                                        <label for="contextFiles"
                                            class="w-full flex items-center justify-center px-4 py-2.5 border border-dashed border-indigo-300 dark:border-indigo-700 rounded-lg text-indigo-700 dark:text-indigo-300 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/20 transition-colors cursor-pointer">
                                            <i data-lucide="upload" class="w-5 h-5 mr-2"></i>
                                            <span
                                                x-text="uploadedFiles.length > 0 ? `${uploadedFiles.length} file(s) selected` : 'Choose files'"></span>
                                        </label>
                                    </div>

                                    <!-- File list -->
                                    <div x-show="uploadedFiles.length > 0" class="mt-2 space-y-1">
                                        <template x-for="(file, index) in uploadedFiles" :key="index">
                                            <div
                                                class="flex items-center justify-between text-xs p-2 rounded-md bg-indigo-50/70 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/40">
                                                <div class="flex items-center overflow-hidden">
                                                    <i data-lucide="file-text"
                                                        class="w-4 h-4 flex-shrink-0 text-indigo-500 dark:text-indigo-400 mr-2"></i>
                                                    <span class="text-zinc-700 dark:text-zinc-300 truncate"
                                                        x-text="file.name"></span>
                                                    <span class="ml-2 text-zinc-500 dark:text-zinc-400 flex-shrink-0"
                                                        x-text="formatFileSize(file.size)"></span>
                                                </div>
                                                <button @click="removeFile(index)" type="button"
                                                    class="ml-2 flex-shrink-0 p-1 rounded-full bg-white dark:bg-zinc-700 text-zinc-500 hover:text-red-500 dark:text-zinc-400 dark:hover:text-red-400 border border-zinc-200 dark:border-zinc-600 hover:border-red-200 dark:hover:border-red-800 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                        fill="none" stroke="currentColor" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                                                        <line x1="18" y1="6" x2="6"
                                                            y2="18"></line>
                                                        <line x1="6" y1="6" x2="18"
                                                            y2="18"></line>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>

                                    <p class="mt-1 text-xs text-indigo-600/70 dark:text-indigo-400/70">
                                        Upload documentation, specs, or code files for additional context.
                                    </p>
                                </div>
                            </div>

                            <!-- AI Generation History -->
                            <div
                                class="bg-zinc-50/80 dark:bg-zinc-800/80 rounded-xl p-4 border border-zinc-200/70 dark:border-zinc-700/50">
                                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-3 flex items-center">
                                    <i data-lucide="history" class="w-5 h-5 mr-2 text-zinc-600 dark:text-zinc-400"></i>
                                    Generation History
                                </h3>
                                <div class="space-y-2 max-h-64 overflow-auto pr-2" id="generation-history">
                                    <template x-for="(item, index) in generationHistory" :key="index">
                                        <div @click="useHistoryItem(index)"
                                            class="p-3 rounded-lg cursor-pointer text-sm bg-white dark:bg-zinc-700/30 border border-zinc-200/70 dark:border-zinc-700/50 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                                            <div class="flex justify-between items-start">
                                                <h4 class="font-medium text-zinc-900 dark:text-zinc-100"
                                                    x-text="item.title || 'Generated Story'"></h4>
                                                <span class="text-xs text-zinc-500 dark:text-zinc-400"
                                                    x-text="formatTime(item.timestamp)"></span>
                                            </div>
                                            <p class="mt-1 text-zinc-600 dark:text-zinc-400 line-clamp-2"
                                                x-text="item.prompt"></p>
                                        </div>
                                    </template>
                                    <div x-show="generationHistory.length === 0"
                                        class="text-center py-4 text-zinc-500 dark:text-zinc-400 text-sm italic">
                                        No generation history yet
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Prompt & Results -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Prompt Builder -->
                            <div
                                class="bg-white dark:bg-zinc-800 rounded-xl p-6 border border-zinc-200/70 dark:border-zinc-700/50 shadow-sm">
                                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-4 flex items-center">
                                    <i data-lucide="message-square-plus"
                                        class="w-5 h-5 mr-2 text-zinc-600 dark:text-zinc-400"></i>
                                    Prompt Builder
                                </h3>

                                <!-- Prompt Templates -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Template <span
                                            class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional)</span>
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <button @click="useTemplate('feature')"
                                            class="flex items-center px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="plus-circle" class="w-4 h-4 mr-2 text-green-500"></i>
                                            New Feature
                                        </button>
                                        <button @click="useTemplate('enhancement')"
                                            class="flex items-center px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="arrow-up-circle" class="w-4 h-4 mr-2 text-blue-500"></i>
                                            Enhancement
                                        </button>
                                        <button @click="useTemplate('bugfix')"
                                            class="flex items-center px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="bug" class="w-4 h-4 mr-2 text-red-500"></i>
                                            Bug Fix
                                        </button>
                                        <button @click="useTemplate('usability')"
                                            class="flex items-center px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="users" class="w-4 h-4 mr-2 text-purple-500"></i>
                                            Usability
                                        </button>
                                    </div>
                                </div>

                                <!-- Prompt Input -->
                                <div class="mb-4">
                                    <label for="ai-prompt"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Prompt <span class="text-red-500">*</span>
                                    </label>
                                    <textarea x-model="prompt" id="ai-prompt" rows="5" placeholder="Describe the user story you want to create..."
                                        class="w-full px-4 py-3 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
                                        :class="{ 'border-red-500 dark:border-red-500': promptError }"></textarea>
                                    <p x-show="promptError" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        <span x-text="promptError"></span>
                                    </p>
                                </div>

                                <!-- Advanced Options -->
                                <div class="mb-4" x-data="{ showOptions: false }">
                                    <button @click="showOptions = !showOptions" type="button"
                                        class="flex items-center text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 transition-colors">
                                        <i data-lucide="sliders" class="w-4 h-4 mr-1"></i>
                                        <span>Advanced Options</span>
                                        <i data-lucide="chevron-down" class="w-4 h-4 ml-1 transition-transform"
                                            :class="{ 'rotate-180': showOptions }"></i>
                                    </button>

                                    <div x-show="showOptions" x-transition
                                        class="mt-3 p-3 rounded-lg bg-zinc-50 dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-700">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                            <!-- Priority Preference -->
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                    Default Priority
                                                </label>
                                                <select x-model="defaultPriority"
                                                    class="w-full px-3 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-sm">
                                                    <option value="">Let AI Decide</option>
                                                    <option value="high">High</option>
                                                    <option value="medium">Medium</option>
                                                    <option value="low">Low</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button @click="generateStory"
                                        class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg shadow-md hover:shadow-lg flex items-center transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="isGenerating || !projectId || !prompt">
                                        <template x-if="!isGenerating">
                                            <i data-lucide="sparkles" class="w-5 h-5 mr-2"></i>
                                        </template>
                                        <template x-if="isGenerating">
                                            <svg class="animate-spin h-5 w-5 mr-2 text-white"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                        </template>
                                        <span x-text="isGenerating ? 'Generating...' : 'Generate Story'"></span>
                                    </button>
                                </div>
                            </div>

                            <!-- Generated Result -->
                            <div x-show="generatedStory" x-transition
                                class="bg-gradient-to-br from-indigo-50/80 to-purple-50/80 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-6 border border-indigo-200/70 dark:border-indigo-800/40 shadow-sm">
                                <div class="flex justify-between items-start mb-4">
                                    <h3
                                        class="text-lg font-semibold text-indigo-800 dark:text-indigo-200 flex items-center">
                                        <i data-lucide="file-text"
                                            class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400"></i>
                                        Generated Story
                                    </h3>
                                    <div class="flex items-center space-x-2">
                                        <button @click="regenerateStory"
                                            class="p-2 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors"
                                            :disabled="isGenerating" title="Regenerate">
                                            <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                                        </button>
                                        <button @click="copyToForm" type="button"
                                            class="p-2 rounded-lg text-green-600 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors"
                                            title="Use This Story">
                                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                                        </button>
                                    </div>
                                </div>

                                <div
                                    class="space-y-4 bg-white/80 dark:bg-zinc-800/60 rounded-lg p-4 border border-indigo-100 dark:border-indigo-800/30">
                                    <div>
                                        <h4 class="text-sm font-medium text-indigo-700 dark:text-indigo-300">Title:</h4>
                                        <p class="mt-1 text-zinc-900 dark:text-zinc-100 font-medium"
                                            x-text="generatedStory.title"></p>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-medium text-indigo-700 dark:text-indigo-300">Description:
                                        </h4>
                                        <p class="mt-1 text-zinc-800 dark:text-zinc-200 whitespace-pre-line"
                                            x-text="generatedStory.description"></p>
                                    </div>

                                    <div
                                        x-show="generatedStory.acceptance_criteria && generatedStory.acceptance_criteria.length > 0">
                                        <h4 class="text-sm font-medium text-indigo-700 dark:text-indigo-300">Acceptance
                                            Criteria:</h4>
                                        <ul class="mt-1 space-y-1 text-zinc-800 dark:text-zinc-200 list-disc list-inside">
                                            <template x-for="(criterion, index) in generatedStory.acceptance_criteria"
                                                :key="index">
                                                <li x-text="criterion"></li>
                                            </template>
                                        </ul>
                                    </div>

                                    <div class="flex flex-wrap gap-2 pt-2">
                                        <div class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': generatedStory
                                                    .priority === 'high',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': generatedStory
                                                    .priority === 'medium',
                                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': generatedStory
                                                    .priority === 'low'
                                            }">
                                            <i data-lucide="flag" class="w-3 h-3 mr-1"></i>
                                            <span
                                                x-text="generatedStory.priority ? (generatedStory.priority.charAt(0).toUpperCase() + generatedStory.priority.slice(1)) + ' Priority' : 'Priority'"></span>
                                        </div>

                                        <template x-for="(tag, index) in generatedStory.tags" :key="index">
                                            <div
                                                class="inline-flex items-center px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300 text-xs font-medium">
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
                <form id="story-form" x-show="creationMode === 'manual'"
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    action="{{ route('dashboard.stories.store') }}" method="POST" @submit.prevent="submitManualForm">
                    @csrf

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Project Selection (Required) -->
                            <div>
                                <label for="project_id"
                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2 flex items-center gap-1">
                                    <i data-lucide="folder" class="w-4 h-4 text-zinc-400"></i>
                                    Project <span class="text-red-600">*</span>
                                </label>
                                <select name="project_id" id="project_id" class="w-full px-4 py-2.5 rounded-lg"
                                    x-model="projectId" @change="onProjectChange" required>
                                    <option value="">Select Project</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}"
                                            {{ old('project_id', $selectedProjectId) == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-4 h-4"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Epic Selection (Optional) -->
                            <div>
                                <label for="epic_id"
                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2 flex items-center gap-1">
                                    <i data-lucide="layers" class="w-4 h-4 text-zinc-400"></i>
                                    Epic <span
                                        class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional)</span>
                                </label>
                                <select name="epic_id" id="epic_id" class="w-full px-4 py-2.5 rounded-lg"
                                    x-model="epicId" :disabled="!projectId || epics.length === 0">
                                    <option value="">None - Independent Story</option>
                                    <template x-for="epic in epics" :key="epic.id">
                                        <option :value="epic.id"
                                            x-text="epic.name + (epic.status ? ' (' + epic.status + ')' : '')"></option>
                                    </template>
                                </select>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    Optional: Assign to an epic or leave as independent story
                                </p>
                                @error('epic_id')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-4 h-4"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Title -->
                            <div class="md:col-span-2">
                                <label for="title"
                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2 flex items-center gap-1">
                                    <i data-lucide="text" class="w-4 h-4 text-zinc-400"></i>
                                    Title <span class="text-red-600">*</span>
                                </label>
                                <input type="text" name="title" id="title" x-model="formData.title"
                                    class="w-full px-4 py-2.5 rounded-lg" placeholder="Enter story title" required>
                                @error('title')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-4 h-4"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label for="description"
                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2 flex items-center gap-1">
                                    <i data-lucide="align-left" class="w-4 h-4 text-zinc-400"></i>
                                    Description
                                </label>
                                <textarea name="description" id="description" rows="6" x-model="formData.description"
                                    class="w-full px-4 py-2.5 rounded-lg"
                                    placeholder="Enter story description, acceptance criteria, and other details"></textarea>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    Include acceptance criteria, user requirements, and other relevant details
                                </p>
                                @error('description')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-4 h-4"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <div
                            class="border-t border-zinc-200/50 dark:border-zinc-700/50 pt-6 flex flex-wrap gap-3 justify-between">
                            <a href="{{ route('dashboard.stories.indexAll') }}"
                                class="btn-secondary px-4 py-2.5 rounded-lg">
                                Cancel
                            </a>
                            <div class="flex flex-wrap gap-3">
                                <button type="button" @click="creationMode = 'ai'"
                                    class="btn-outline px-4 py-2.5 rounded-lg flex items-center">
                                    <i data-lucide="sparkles" class="w-4 h-4 mr-2"></i>
                                    Try AI Instead
                                </button>
                                <button type="submit"
                                    class="btn-primary px-4 py-2.5 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white shadow-sm transition-all">
                                    Create Story
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- User Stories Guide -->
        <div
            class="bg-white dark:bg-zinc-800/50 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 backdrop-blur-sm">
            <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/30 dark:bg-zinc-800/30">
                <h2 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-amber-500 dark:text-amber-400"></i>
                    User Stories Guide
                </h2>
            </div>
            <div class="p-6">
                <div class="prose dark:prose-invert max-w-none">
                    <div class="space-y-4">
                        <h3 class="text-zinc-900 dark:text-white">What Makes a Good User Story?</h3>
                        <div
                            class="p-4 rounded-xl bg-emerald-50/50 dark:bg-emerald-900/20 border border-emerald-200/50 dark:border-emerald-800/50">
                            <p class="font-medium text-emerald-800 dark:text-emerald-300">Ideal Structure:</p>
                            <blockquote class="mt-2 pl-4 border-l-4 border-emerald-400 dark:border-emerald-500">
                                <p class="text-zinc-700 dark:text-zinc-300">As a <span class="font-medium">[user
                                        role]</span>,<br>
                                    I want <span class="font-medium">[goal]</span><br>
                                    So that <span class="font-medium">[reason]</span></p>
                            </blockquote>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4 mt-6">
                            <div
                                class="p-4 rounded-xl bg-blue-50/50 dark:bg-blue-900/20 border border-blue-200/50 dark:border-blue-800/50">
                                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300">Example 1</h4>
                                <p class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">
                                    <strong>Title:</strong> Password Reset<br>
                                    <strong>Description:</strong> As a registered user, I want to reset my password so I can
                                    regain access if I forget it.
                                </p>
                            </div>

                            <div
                                class="p-4 rounded-xl bg-purple-50/50 dark:bg-purple-900/20 border border-purple-200/50 dark:border-purple-800/50">
                                <h4 class="text-sm font-medium text-purple-800 dark:text-purple-300">Example 2</h4>
                                <p class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">
                                    <strong>Title:</strong> Product Search<br>
                                    <strong>Description:</strong> As a shopper, I want to search products by name so I can
                                    quickly find items I'm looking for.
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
        .btn-secondary {
            @apply bg-white/50 dark:bg-zinc-700/50 border border-zinc-300/70 dark:border-zinc-600/50 hover:bg-zinc-50/70 dark:hover:bg-zinc-600/50 shadow-sm text-zinc-700 dark:text-zinc-300 transition-all;
        }

        .btn-primary {
            @apply bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm hover:shadow-md transition-all;
        }

        .btn-outline {
            @apply border border-zinc-300/70 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-700/50 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50/70 dark:hover:bg-zinc-600/50 shadow-sm transition-all;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('storyCreator', (config) => ({
                // Configuration values
                projectId: config.projectId || '',
                projectName: config.projectName || '',
                apiEndpoint: config.apiEndpoint,
                projectsEndpoint: config.projectsEndpoint,
                epicsEndpoint: config.epicsEndpoint,
                csrfToken: config.csrfToken,
                aiEnabled: config.aiEnabled,

                // UI state
                creationMode: 'manual', // 'manual' or 'ai'
                isGenerating: false,
                promptError: '',
                uploadedFiles: [],
                fileContents: {},
                notification: {
                    show: false,
                    type: 'info',
                    title: '',
                    message: ''
                },

                // Data state
                epics: [],
                prompt: '',
                codeContext: '',
                aiProvider: 'openai',
                defaultPriority: '',
                generatedStory: null,
                generationHistory: [],

                // Form data
                formData: {
                    title: '',
                    description: '',
                    epicId: config.initialEpicId || '',
                },

                // Lifecycle methods
                init() {
                    if (this.projectId) {
                        this.loadEpics();
                    }

                    // Try to load history from localStorage
                    const savedHistory = localStorage.getItem('story_generation_history');
                    if (savedHistory) {
                        try {
                            this.generationHistory = JSON.parse(savedHistory).slice(0, 10);
                        } catch (e) {
                            console.error('Failed to parse generation history:', e);
                        }
                    }

                    this.$watch('projectId', (value) => {
                        if (value) {
                            this.loadEpics();
                        } else {
                            this.epics = [];
                            this.epicId = '';
                        }
                    });
                },

                // Methods
                async loadEpics() {
                    if (!this.projectId) return;

                    try {
                        const response = await fetch(
                            `${this.epicsEndpoint}${this.projectId}/epics`);
                        const data = await response.json();

                        if (data.success) {
                            this.epics = data.data.epics || [];
                        }
                    } catch (error) {
                        console.error('Failed to load epics:', error);
                        this.showNotification('error', 'Error', 'Failed to load epics.');
                    }
                },

                onProjectChange() {
                    // Reset epic selection when project changes
                    this.epicId = '';
                    this.loadEpics();
                },

                handleFileUpload(event) {
                    const newFiles = Array.from(event.target.files || []);
                    if (!newFiles.length) return;

                    // Add the new files to our array
                    this.uploadedFiles = [...this.uploadedFiles, ...newFiles];

                    // Process text files to extract content (limit to reasonable size)
                    newFiles.forEach(file => {
                        if (file.size < 500000 && (
                                file.type.includes('text') ||
                                file.type.includes('application/json') ||
                                file.name.endsWith('.md') ||
                                file.name.endsWith('.csv') ||
                                file.name.endsWith('.txt') ||
                                file.name.endsWith('.js') ||
                                file.name.endsWith('.ts') ||
                                file.name.endsWith('.py') ||
                                file.name.endsWith('.php')
                            )) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.fileContents[file.name] = e.target.result;
                            };
                            reader.readAsText(file);
                        }
                    });
                },

                removeFile(index) {
                    const removedFile = this.uploadedFiles[index];
                    this.uploadedFiles = this.uploadedFiles.filter((_, i) => i !== index);

                    // Also remove from fileContents if exists
                    if (removedFile && removedFile.name in this.fileContents) {
                        delete this.fileContents[removedFile.name];
                    }
                },

                formatFileSize(bytes) {
                    if (bytes < 1024) return bytes + ' B';
                    else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                    else return (bytes / 1048576).toFixed(1) + ' MB';
                },

                useTemplate(type) {
                    switch (type) {
                        case 'feature':
                            this.prompt =
                                `Create a user story for a new feature that allows users to [describe feature]. This should be targeted at [user role] and solve the problem of [describe problem].`;
                            break;
                        case 'enhancement':
                            this.prompt =
                                `Create a user story for enhancing the existing [feature name]. Currently users can [current capability], but they need to be able to [desired capability].`;
                            break;
                        case 'bugfix':
                            this.prompt =
                                `Create a user story to fix the following issue: When users [describe action], the system [describe current incorrect behavior]. The expected behavior is [describe correct behavior].`;
                            break;
                        case 'usability':
                            this.prompt =
                                `Create a user story to improve the usability of [feature/page]. Currently, users find it difficult to [describe pain point]. This should be simplified so that [describe desired outcome].`;
                            break;
                    }
                },

                async generateStory() {
                    if (!this.projectId || !this.prompt) {
                        this.promptError = 'Please select a project and enter a prompt';
                        return;
                    }

                    this.promptError = '';
                    this.isGenerating = true;

                    try {
                        // Build the payload as before
                        const payload = {
                            prompt: this.prompt,
                            context: {
                                project_id: this.projectId,
                                epic_id: this.epicId || undefined,
                                code: this.codeContext || undefined,
                                // other context data
                            },
                            provider: this.aiProvider,
                        };

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
                            // Just store the data, don't save it yet
                            this.generatedStory = result.data;

                            // Add to history
                            const historyEntry = {
                                timestamp: Date.now(),
                                prompt: this.prompt,
                                title: result.data.title,
                                data: result.data
                            };

                            this.generationHistory = [historyEntry, ...this.generationHistory]
                                .slice(0, 10);
                            localStorage.setItem('story_generation_history', JSON.stringify(this
                                .generationHistory));

                            this.showNotification('success', 'Success!',
                                'Story generated successfully. Use "Use This Story" to add it to the form and edit before saving.'
                                );
                        } else {
                            throw new Error(result.message || 'Failed to generate story');
                        }
                    } catch (error) {
                        console.error('Generation error:', error);
                        this.showNotification('error', 'Generation Failed', error.message ||
                            'Failed to generate story. Please try again.');
                    } finally {
                        this.isGenerating = false;
                    }
                },

                regenerateStory() {
                    // Just call generate again with the same settings
                    this.generateStory();
                },

                copyToForm() {
                    if (!this.generatedStory) return;

                    // Populate the form fields
                    this.formData = {
                        title: this.generatedStory.title || '',
                        description: this.generatedStory.description || '',
                    };

                    // Set the epic ID if available
                    if (this.epicId) {
                        this.formData.epicId = this.epicId;
                    }

                    // Switch to manual mode to show the form
                    this.creationMode = 'manual';

                    // Ensure form fields are updated after DOM is rendered
                    this.$nextTick(() => {
                        document.getElementById('title').value = this.formData.title;
                        document.getElementById('description').value = this.formData
                            .description;

                        // Also update the related hidden input for form submission
                        if (document.getElementById('epic_id')) {
                            document.getElementById('epic_id').value = this.formData.epicId ||
                                '';
                        }
                    });

                    this.showNotification('info', 'Added to Form',
                        'Generated story copied to form. You can now edit and submit it.');
                },

                useHistoryItem(index) {
                    const item = this.generationHistory[index];
                    if (!item) return;

                    this.generatedStory = item.data;
                    this.prompt = item.prompt;

                    this.showNotification('info', 'History Item Loaded', 'Story loaded from history.');
                },
                submitManualForm(event) {
                    // Get form element
                    const form = event.target;

                    // Ensure the form data is correctly set
                    const formData = new FormData(form);

                    // Make sure project_id is set
                    if (!formData.get('project_id')) {
                        formData.set('project_id', this.projectId);
                    }

                    // Submit the form normally - this will redirect to the stories list
                    fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (response.redirected) {
                                window.location.href = response.url;
                            } else {
                                return response.json();
                            }
                        })
                        .then(data => {
                            if (data && data.success) {
                                window.location.href = data.data.redirect ||
                                    '{{ route('dashboard.stories.indexAll') }}';
                            } else if (data && data.errors) {
                                // Handle validation errors
                                this.showNotification('error', 'Validation Error',
                                    'Please check the form for errors');
                            }
                        })
                        .catch(error => {
                            console.error('Error submitting form:', error);
                            this.showNotification('error', 'Error',
                                'Failed to create story. Please try again.');
                        });
                },

                submitForm(event) {
                    // If we're coming from the AI-generated story, ensure fields are properly set
                    if (this.generatedStory) {
                        // Make sure form fields match our data
                        document.getElementById('title').value = this.formData.title;
                        document.getElementById('description').value = this.formData.description;

                        if (document.getElementById('epic_id')) {
                            document.getElementById('epic_id').value = this.formData.epicId || '';
                        }
                    }

                    // Proceed with form submission
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
    </script>
@endpush
