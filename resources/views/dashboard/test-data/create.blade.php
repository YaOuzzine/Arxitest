@extends('layouts.dashboard')

@section('title', "Add Test Data - {$testCase->title}")

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.show', $project->id) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">{{ $project->name }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">{{ $testCase->title }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.data.index', [$project->id, $testCase->id]) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">Test Data</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Create</span>
    </li>
@endsection

@section('content')
    <div class="h-full space-y-6" x-data="testDataCreator">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-zinc-800 dark:from-zinc-100 to-zinc-600 dark:to-zinc-300 bg-clip-text text-transparent tracking-tight">
                    Add Test Data
                </h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Create test data for {{ $testCase->title }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('dashboard.projects.test-cases.data.index', [$project->id, $testCase->id]) }}"
                   class="group inline-flex items-center px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-200 bg-white/70 dark:bg-zinc-800/50 hover:bg-white dark:hover:bg-zinc-700/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                    <i data-lucide="arrow-left"
                       class="mr-2 -ml-1 w-4 h-4 transition-transform group-hover:-translate-x-1"></i>
                    Back to Test Data
                </a>
            </div>
        </div>

        <!-- Creation Mode Tabs -->
        <div class="mb-6 flex justify-center">
            <div class="inline-flex bg-zinc-100 dark:bg-zinc-800 p-1 rounded-lg shadow-sm">
                <button @click="creationMode = 'manual'"
                    :class="{ 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-sm border-b-2 border-blue-500': creationMode === 'manual', 'text-zinc-600 dark:text-zinc-400 hover:bg-white/30 dark:hover:bg-zinc-700/30': creationMode !== 'manual' }"
                    class="px-6 py-3 rounded-md font-medium transition-all duration-200 flex items-center space-x-2">
                    <i data-lucide="pen-square" class="w-5 h-5 mr-2"></i>
                    <span>Manual Entry</span>
                </button>
                <button @click="creationMode = 'ai'"
                    :class="{ 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-sm border-b-2 border-blue-500': creationMode === 'ai', 'text-zinc-600 dark:text-zinc-400 hover:bg-white/30 dark:hover:bg-zinc-700/30': creationMode !== 'ai' }"
                    class="px-6 py-3 rounded-md font-medium transition-all duration-200 flex items-center space-x-2">
                    <i data-lucide="sparkles" class="w-5 h-5 mr-2"></i>
                    <span>AI Generation</span>
                </button>
            </div>
        </div>

        <!-- Form Container -->
        <div class="bg-white/70 dark:bg-zinc-800/50 rounded-2xl shadow-2xl border border-zinc-200/50 dark:border-zinc-700/30 backdrop-blur-lg transition-all duration-300 hover:shadow-2xl">
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
                            <div class="bg-gradient-to-br from-indigo-50/80 to-purple-50/80 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-4 border border-indigo-100/70 dark:border-indigo-800/50">
                                <h3 class="text-lg font-semibold text-indigo-800 dark:text-indigo-200 mb-2 flex items-center">
                                    <i data-lucide="lightbulb" class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400"></i>
                                    Generation Options
                                </h3>
                                <p class="text-sm text-indigo-700/90 dark:text-indigo-300 mb-4">
                                    Choose format and options for the AI-generated test data.
                                </p>

                                <!-- Data Format -->
                                <div class="mb-4">
                                    <label for="ai-format" class="block text-sm font-medium text-indigo-700 dark:text-indigo-300 mb-2">
                                        Data Format <span class="text-red-500">*</span>
                                    </label>
                                    <select id="ai-format" x-model="aiFormat" class="w-full px-4 py-2.5 rounded-lg border border-indigo-200/70 dark:border-indigo-800/50 bg-white/70 dark:bg-zinc-800/50 text-indigo-800 dark:text-indigo-200 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all">
                                        <option value="json">JSON</option>
                                        <option value="csv">CSV</option>
                                        <option value="xml">XML</option>
                                        <option value="plain">Plain Text</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Test Case Information -->
                            <div class="bg-white dark:bg-zinc-800 rounded-xl p-4 border border-zinc-200/70 dark:border-zinc-700/50">
                                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-3">
                                    <i data-lucide="info" class="w-5 h-5 mr-2 inline text-zinc-600 dark:text-zinc-400"></i>
                                    Test Case Details
                                </h3>
                                <div class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                                    <p><span class="font-medium">Title:</span> {{ $testCase->title }}</p>

                                    @if ($testCase->steps && is_array($testCase->steps))
                                    <div>
                                        <p class="font-medium">Steps:</p>
                                        <ol class="ml-5 list-decimal">
                                            @foreach ($testCase->steps as $step)
                                                <li>{{ $step }}</li>
                                            @endforeach
                                        </ol>
                                    </div>
                                    @endif

                                    <p><span class="font-medium">Expected Results:</span> {{ $testCase->expected_results }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Prompt & Results -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Prompt Builder -->
                            <div class="bg-white dark:bg-zinc-800 rounded-xl p-6 border border-zinc-200/70 dark:border-zinc-700/50 shadow-sm">
                                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-4 flex items-center">
                                    <i data-lucide="message-square-plus" class="w-5 h-5 mr-2 text-zinc-600 dark:text-zinc-400"></i>
                                    Prompt Builder
                                </h3>

                                <!-- Prompt Templates -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Template <span class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional)</span>
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <button @click="useTemplate('user-profiles')" type="button"
                                            class="flex items-center px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="user" class="w-4 h-4 mr-2 text-blue-500"></i>
                                            User Profiles
                                        </button>
                                        <button @click="useTemplate('product-catalog')" type="button"
                                            class="flex items-center px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="shopping-bag" class="w-4 h-4 mr-2 text-green-500"></i>
                                            Product Catalog
                                        </button>
                                        <button @click="useTemplate('api-responses')" type="button"
                                            class="flex items-center px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="code" class="w-4 h-4 mr-2 text-purple-500"></i>
                                            API Responses
                                        </button>
                                        <button @click="useTemplate('form-inputs')" type="button"
                                            class="flex items-center px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i data-lucide="form-input" class="w-4 h-4 mr-2 text-amber-500"></i>
                                            Form Inputs
                                        </button>
                                    </div>
                                </div>

                                <!-- Prompt Input -->
                                <div class="mb-4">
                                    <label for="ai-prompt" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                        Prompt <span class="text-red-500">*</span>
                                    </label>
                                    <textarea x-model="aiPrompt" id="ai-prompt" rows="5" placeholder="Describe the test data you want to generate..."
                                        class="w-full px-4 py-3 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
                                        :class="{ 'border-red-500 dark:border-red-500': promptError }"></textarea>
                                    <p x-show="promptError" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        <span x-text="promptError"></span>
                                    </p>
                                </div>

                                <div class="flex justify-end">
                                    <button @click="generateTestData"
                                        class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg shadow-md hover:shadow-lg flex items-center transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="isGenerating || !aiPrompt.trim()">
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
                                        <span x-text="isGenerating ? 'Generating...' : 'Generate Test Data'"></span>
                                    </button>
                                </div>
                            </div>

                            <!-- Generated Result -->
                            <div x-show="generatedData" x-transition
                                class="bg-gradient-to-br from-indigo-50/80 to-purple-50/80 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-6 border border-indigo-200/70 dark:border-indigo-800/40 shadow-sm">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-lg font-semibold text-indigo-800 dark:text-indigo-200 flex items-center">
                                        <i data-lucide="file-text" class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400"></i>
                                        Generated Test Data
                                    </h3>
                                    <div class="flex items-center space-x-2">
                                        <button @click="regenerateTestData"
                                            class="p-2 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors"
                                            :disabled="isGenerating" title="Regenerate">
                                            <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                                        </button>
                                        <button @click="useGeneratedData" type="button"
                                            class="p-2 rounded-lg text-green-600 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors"
                                            title="Use This Data">
                                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="bg-white/80 dark:bg-zinc-800/60 rounded-lg p-4 border border-indigo-100 dark:border-indigo-800/30 space-y-4 max-h-96 overflow-y-auto">
                                    <div>
                                        <h4 class="text-sm font-medium text-indigo-700 dark:text-indigo-300">Preview:</h4>
                                        <pre class="mt-2 whitespace-pre-wrap text-zinc-800 dark:text-zinc-200 text-sm font-mono" x-text="generatedData.content"></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manual Entry Form -->
                <form x-show="creationMode === 'manual'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    action="{{ route('dashboard.projects.test-cases.data.store', [$project->id, $testCase->id]) }}"
                    method="POST"
                    @submit.prevent="submitForm">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input x-model="formData.name" type="text" name="name" id="name" class="w-full px-4 py-2.5 rounded-lg" required>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                Give your test data a descriptive name
                            </p>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Format -->
                        <div>
                            <label for="format" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Format <span class="text-red-500">*</span>
                            </label>
                            <select x-model="formData.format" name="format" id="format" class="w-full px-4 py-2.5 rounded-lg" required>
                                <option value="">Select Format</option>
                                @foreach($formats as $format)
                                    <option value="{{ $format }}">{{ strtoupper($format) }}</option>
                                @endforeach
                            </select>
                            @error('format')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Usage Context -->
                        <div>
                            <label for="usage_context" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Usage Context <span class="text-red-500">*</span>
                            </label>
                            <input x-model="formData.usage_context" type="text" name="usage_context" id="usage_context" class="w-full px-4 py-2.5 rounded-lg" required>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                Describe how this data will be used in testing
                            </p>
                            @error('usage_context')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sensitive Data Toggle -->
                        <div class="md:col-span-2">
                            <div class="flex items-center">
                                <input x-model="formData.is_sensitive" type="checkbox" id="is_sensitive" name="is_sensitive" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-zinc-300 rounded">
                                <label for="is_sensitive" class="ml-2 block text-sm text-zinc-700 dark:text-zinc-300">
                                    Mark as sensitive data (contains passwords, tokens, PII, etc.)
                                </label>
                            </div>
                            @error('is_sensitive')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Content -->
                        <div class="md:col-span-2">
                            <label for="content" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Content <span class="text-red-500">*</span>
                            </label>
                            <textarea x-model="formData.content" name="content" id="content" rows="12"
                                class="w-full px-4 py-2.5 rounded-lg font-mono text-sm" required></textarea>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                Enter your test data content in the selected format
                            </p>
                            @error('content')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-8 flex justify-end space-x-4">
                        <a href="{{ route('dashboard.projects.test-cases.data.index', [$project->id, $testCase->id]) }}"
                            class="px-6 py-2.5 text-zinc-700 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-white bg-zinc-100/70 dark:bg-zinc-700/50 rounded-xl hover:bg-zinc-200/50 dark:hover:bg-zinc-600/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md transform hover:-translate-y-0.5">
                            Cancel
                        </a>
                        <button type="submit"
                            :disabled="isSubmitting"
                            class="relative px-8 py-2.5 text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                            <span class="relative z-10 flex items-center">
                                <span x-show="!isSubmitting">Create Test Data</span>
                                <span x-show="isSubmitting" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Creating...
                                </span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notification Toast -->
        <div x-show="showNotification" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed bottom-6 right-6 z-50 max-w-md"
            @click.away="showNotification = false">
            <div class="flex items-start p-4 rounded-xl shadow-lg border"
                :class="{
                    'bg-green-50 border-green-200 dark:bg-green-900/30 dark:border-green-800': notificationType === 'success',
                    'bg-red-50 border-red-200 dark:bg-red-900/30 dark:border-red-800': notificationType === 'error',
                    'bg-blue-50 border-blue-200 dark:bg-blue-900/30 dark:border-blue-800': notificationType === 'info'
                }">
                <div class="flex-shrink-0" x-show="notificationType === 'success'">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400"></i>
                </div>
                <div class="flex-shrink-0" x-show="notificationType === 'error'">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 dark:text-red-400"></i>
                </div>
                <div class="flex-shrink-0" x-show="notificationType === 'info'">
                    <i data-lucide="info" class="w-5 h-5 text-blue-600 dark:text-blue-400"></i>
                </div>
                <div class="ml-3 flex-1">
                    <h4 class="text-sm font-medium"
                        :class="{
                            'text-green-800 dark:text-green-200': notificationType === 'success',
                            'text-red-800 dark:text-red-200': notificationType === 'error',
                            'text-blue-800 dark:text-blue-200': notificationType === 'info'
                        }"
                        x-text="notificationTitle"></h4>
                    <p class="mt-1 text-sm"
                        :class="{
                            'text-green-700 dark:text-green-300': notificationType === 'success',
                            'text-red-700 dark:text-red-300': notificationType === 'error',
                            'text-blue-700 dark:text-blue-300': notificationType === 'info'
                        }"
                        x-text="notificationMessage"></p>
                </div>
                <button @click="showNotification = false"
                    class="ml-4 flex-shrink-0 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('testDataCreator', () => ({
            // UI State
            creationMode: 'manual',
            isSubmitting: false,
            isGenerating: false,
            showNotification: false,
            notificationType: 'info',
            notificationTitle: '',
            notificationMessage: '',

            // Form Data
            formData: {
                name: '{{ old('name') }}',
                format: '{{ old('format') }}',
                content: '{{ old('content') }}',
                usage_context: '{{ old('usage_context', 'General testing') }}',
                is_sensitive: {{ old('is_sensitive', 'false') === 'true' ? 'true' : 'false' }}
            },

            // AI Data
            aiPrompt: '',
            aiFormat: 'json',
            promptError: '',
            generatedData: null,

            init() {
                // Initialize fields from old input if form validation failed
                // Already handled above using Laravel's old() helper
            },

            useTemplate(templateId) {
                if (templateId === 'user-profiles') {
                    this.aiPrompt = 'Generate test data for user profiles with names, emails, ages, and addresses';
                } else if (templateId === 'product-catalog') {
                    this.aiPrompt = 'Generate test data for product catalog with names, prices, SKUs, and descriptions';
                } else if (templateId === 'api-responses') {
                    this.aiPrompt = 'Generate test API response data with pagination, results array, and metadata';
                } else if (templateId === 'form-inputs') {
                    this.aiPrompt = 'Generate form input test data with edge cases like special characters and empty values';
                }
            },

            async generateTestData() {
                if (!this.aiPrompt.trim() || this.isGenerating) {
                    return;
                }

                this.promptError = '';
                this.isGenerating = true;

                try {
                    const response = await fetch('{{ route("api.ai.generate", "test-data") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            prompt: this.aiPrompt,
                            context: {
                                project_id: '{{ $project->id }}',
                                test_case_id: '{{ $testCase->id }}',
                                format: this.aiFormat
                            }
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.generatedData = result.data;
                        this.showNotificationMessage('success', 'Success', 'Test data generated successfully. Click "Use This Data" to continue.');
                    } else {
                        throw new Error(result.message || 'Failed to generate test data');
                    }
                } catch (error) {
                    console.error('Error generating test data:', error);
                    this.promptError = error.message || 'An error occurred during generation';
                    this.showNotificationMessage('error', 'Generation Failed', this.promptError);
                } finally {
                    this.isGenerating = false;
                }
            },

            regenerateTestData() {
                this.generateTestData();
            },

            useGeneratedData() {
                if (!this.generatedData) return;

                // Apply the generated data to the form
                this.formData.name = this.generatedData.name || `Test Data (${this.aiFormat.toUpperCase()})`;
                this.formData.content = this.generatedData.content || '';
                this.formData.format = this.aiFormat;
                this.formData.usage_context = 'AI Generated';

                // Switch to manual mode
                this.creationMode = 'manual';

                this.showNotificationMessage('success', 'Applied', 'The generated test data has been applied to the form. You can edit it before saving.');
            },

            async submitForm() {
                try {
                    this.isSubmitting = true;

                    // Get the form element and submit it
                    const form = event.target;
                    form.submit();
                } catch (error) {
                    console.error('Error submitting form:', error);
                    this.showNotificationMessage('error', 'Error', 'Error submitting form. Please try again.');
                    this.isSubmitting = false;
                }
            },

            showNotificationMessage(type, title, message) {
                this.notificationType = type;
                this.notificationTitle = title;
                this.notificationMessage = message;
                this.showNotification = true;

                // Auto-hide notification after delay
                setTimeout(() => {
                    this.showNotification = false;
                }, 5000);
            }
        }));
    });
</script>
@endpush

@push('styles')
<style>
    /* Additional styles for this page */
    .btn-primary {
        @apply bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm hover:shadow-md transition-all;
    }

    .btn-secondary {
        @apply bg-white/50 dark:bg-zinc-700/50 border border-zinc-300/70 dark:border-zinc-600/50 hover:bg-zinc-50/70 dark:hover:bg-zinc-600/50 shadow-sm text-zinc-700 dark:text-zinc-300 transition-all;
    }
</style>
@endpush
