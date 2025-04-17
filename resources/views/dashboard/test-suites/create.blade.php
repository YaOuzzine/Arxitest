@php
    /**
     * @var \App\Models\Project $project
     */
    $pageTitle = 'Create Test Suite for: ' . $project->name;
@endphp

@extends('layouts.dashboard')

@section('title', $pageTitle)

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects') }}" class="...">Projects</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.show', $project->id) }}" class="...">{{ $project->name }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-suites.index', $project->id) }}" class="...">Test Suites</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Create</span>
    </li>
@endsection

@section('content')
    <div class="w-full" x-data="createTestSuiteForm({
        projectId: '{{ $project->id }}',
        generateUrl: '{{ route('dashboard.projects.test-suites.generateAI', $project->id) }}',
        csrfToken: '{{ csrf_token() }}',
        oldInput: {{ json_encode(old()) }}
    })" x-init="init()">

        <!-- Header -->
        <div class="mb-10 pb-6 border-b border-zinc-200 dark:border-zinc-700">
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">{{ $pageTitle }}</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Create a new test suite either manually or using AI assistance.</p>
        </div>

        <!-- Form Container -->
        <div
            class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-zinc-200 dark:border-zinc-700 p-8 max-w-4xl mx-auto">
            <form id="test-suite-form" method="POST"
                action="{{ route('dashboard.projects.test-suites.store', $project->id) }}" @submit.prevent="submitForm">
                @csrf
                <!-- Manual Entry / AI Edit Section -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-zinc-800 dark:text-white mb-4">
                        <i data-lucide="settings-2" class="w-5 h-5 mr-2 text-blue-500 inline-block"></i>
                        <span>Test Suite Configuration</span>
                    </h3>

                    <div class="space-y-5">
                        <div>
                            <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Suite Name
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" id="name" name="name" required maxlength="100"
                                    x-model="suiteName" {{-- Add x-model --}}
                                    class="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-200 placeholder-zinc-400 pl-11"
                                    placeholder="Authentication Test Suite">
                                <i data-lucide="tag" class="absolute left-3 top-3.5 w-4 h-4 text-zinc-400"></i>
                            </div>
                        </div>

                        <div>
                            <label for="description"
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Description
                            </label>
                            <div class="relative">
                                <textarea id="description" name="description" rows="3" maxlength="255" required x-model="suiteDescription"
                                    {{-- Add x-model --}}
                                    class="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 placeholder-zinc-400 pl-11 resize-none"
                                    placeholder="Describe the purpose and scope of this test suite"></textarea>
                                <i data-lucide="align-left" class="absolute left-3 top-3.5 w-4 h-4 text-zinc-400"></i>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="settings-default-priority"
                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Default Priority
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <div class="relative">
                                    <select id="settings-default-priority" name="settings[default_priority]" required
                                        x-model="suiteSettings.default_priority" {{-- Add x-model --}}
                                        class="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 appearance-none pr-10">
                                        <option value="low">Low Priority</option>
                                        <option value="medium">Medium Priority</option>
                                        <option value="high">High Priority</option>
                                    </select>
                                    <i data-lucide="chevron-down"
                                        class="absolute right-3 top-3.5 w-4 h-4 text-zinc-400 pointer-events-none"></i>
                                </div>
                            </div>

                            <div>
                                <label for="settings-execution-mode"
                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Execution Mode
                                </label>
                                <div class="relative">
                                    <select id="settings-execution-mode" name="settings[execution_mode]"
                                        x-model="suiteSettings.execution_mode" {{-- Add x-model --}}
                                        class="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 appearance-none pr-10">
                                        <option value="sequential">Sequential Execution</option>
                                        <option value="parallel">Parallel Execution</option>
                                    </select>
                                    <i data-lucide="chevron-down"
                                        class="absolute right-3 top-3.5 w-4 h-4 text-zinc-400 pointer-events-none"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-8 flex justify-end gap-3">
                        <a href="{{ route('dashboard.projects.test-suites.index', $project->id) }}"
                            class="px-5 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors duration-200 text-sm font-medium">
                            Cancel
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-md hover:shadow-lg transition-all duration-300 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                            <span>Create Test Suite</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('createTestSuiteForm', (config) => ({
                    projectId: config.projectId,
                    generateUrl: config.generateUrl,
                    csrfToken: config.csrfToken,
                    oldInput: config.oldInput || {},

                    creationMode: 'manual', // 'manual' or 'ai'
                    aiPrompt: '',
                    aiResult: null,
                    aiLoading: false,
                    aiError: '',
                    hasPopulatedFromAI: false, // Track if form was ever populated by AI

                    suiteName: '',
                    suiteDescription: '',
                    suiteSettings: {
                        default_priority: 'medium',
                        execution_mode: 'sequential'
                    },

                    isSubmitting: false,

                    init() {
                        // Populate form with old input if validation failed
                        if (Object.keys(this.oldInput).length > 0) {
                            this.suiteName = this.oldInput.name || '';
                            this.suiteDescription = this.oldInput.description || '';
                            this.suiteSettings.default_priority = this.oldInput[
                                'settings.default_priority'] || 'medium';
                            this.suiteSettings.execution_mode = this.oldInput['settings.execution_mode'] ||
                                'sequential';
                            // If old input exists, assume manual mode unless AI result was also present?
                            // Let's default to manual if there's old input.
                            this.creationMode = 'manual';
                        }

                        this.$watch('aiResult', (newResult) => {
                            if (newResult) {
                                this.populateFormFromAI(newResult);
                                this.hasPopulatedFromAI = true;
                            }
                        });

                        this.$watch('creationMode', (newMode) => {
                            // Optionally clear AI fields if switching back to manual
                            // if (newMode === 'manual') {
                            //     this.aiPrompt = '';
                            //     this.aiResult = null;
                            //     this.aiError = '';
                            // }
                            // Decide if you want to reset manual fields when switching to AI
                            if (newMode === 'ai' && !this.hasPopulatedFromAI) {
                                // Only reset if AI hasn't populated yet
                                // this.suiteName = '';
                                // this.suiteDescription = '';
                                // this.suiteSettings = { default_priority: 'medium', execution_mode: 'sequential' };
                            }
                        });
                    },

                    async generateWithAI() {
                        if (!this.aiPrompt.trim() || this.aiLoading) return;

                        this.aiLoading = true;
                        this.aiError = '';
                        this.aiResult = null; // Clear previous results

                        try {
                            const response = await fetch(this.generateUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrfToken
                                },
                                body: JSON.stringify({
                                    prompt: this.aiPrompt
                                })
                            });

                            const result = await response.json();

                            if (!response.ok) {
                                let errorMsg = 'AI generation failed.';
                                if (result.errors && result.errors.prompt) {
                                    errorMsg = result.errors.prompt[0];
                                } else if (result.message) {
                                    errorMsg = result.message;
                                }
                                throw new Error(errorMsg);
                            }

                            if (result.success && result.data) {
                                this.aiResult = result.data; // This triggers the $watch
                                // Optionally clear the prompt after success
                                // this.aiPrompt = '';
                            } else {
                                throw new Error(result.message || 'AI generation returned no data.');
                            }

                        } catch (error) {
                            console.error('AI Generation Error:', error);
                            this.aiError = error.message || 'An unexpected error occurred.';
                            this.showNotification('error', 'AI Error', this
                                .aiError); // Assuming showNotification exists globally or in layout
                        } finally {
                            this.aiLoading = false;
                        }
                    },

                    populateFormFromAI(data) {
                        this.suiteName = data.name || '';
                        this.suiteDescription = data.description || '';
                        this.suiteSettings.default_priority = data.settings?.default_priority || 'medium';
                        this.suiteSettings.execution_mode = data.settings?.execution_mode || 'sequential';
                        // Add other settings if generated
                    },

                    submitForm() {
                        if (!this.suiteName.trim()) {
                            this.showNotification('error', 'Validation Error',
                                'Test Suite Name is required.');
                            document.getElementById('name')?.focus();
                            return;
                        }
                        if (!this.suiteSettings.default_priority) {
                            this.showNotification('error', 'Validation Error',
                                'Default Priority setting is required.');
                            document.getElementById('settings-default-priority')?.focus();
                            return;
                        }

                        this.isSubmitting = true;
                        document.getElementById('test-suite-form').submit(); // Submit the actual form
                    }
                }));
            });
        </script>
    @endpush
@endsection
