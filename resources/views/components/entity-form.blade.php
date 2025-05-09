<!-- resources/views/components/entity-form.blade.php -->
@props([
    'title' => 'Create New Item',
    'description' => 'Add a new item to your project',
    'backRoute' => '#',
    'backLabel' => 'Back',
    'submitAction' => '#',
    'submitMethod' => 'POST',
    'submitButtonText' => 'Create Item',
    'entityName' => 'item',
    'hasAI' => false,
    'aiEndpoint' => null,
    'aiConfiguration' => [],
    'showDangerZone' => false,
    'dangerAction' => null,
    'dangerMethod' => 'DELETE',
    'dangerText' => 'Delete',
    'dangerConfirmText' => 'Are you sure you want to delete this item?',
    'isEdit' => false,
    'oldData' => []
])

<div x-data="entityForm({
    submitAction: '{{ $submitAction }}',
    entityName: '{{ $entityName }}',
    hasAI: {{ $hasAI ? 'true' : 'false' }},
    aiEndpoint: '{{ $aiEndpoint }}',
    aiConfig: {{ json_encode($aiConfiguration) }},
    isEdit: {{ $isEdit ? 'true' : 'false' }},
    oldData: {{ json_encode($oldData) }},
    csrfToken: '{{ csrf_token() }}',
})" class="h-full">
    <!-- Animated Header -->
    <div class="mb-6 transform transition-all duration-300 ease-out"
         x-data="{ scrollY: 0 }"
         x-on:scroll.window="scrollY = window.scrollY"
         :class="scrollY > 50 ? 'opacity-90 scale-[0.99]' : ''">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white bg-gradient-to-r from-zinc-900 dark:from-zinc-100 to-zinc-600 dark:to-zinc-400 bg-clip-text text-transparent animate-fade-in-down">
                    {{ $title }}
                </h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 transition-opacity duration-300">
                    {{ $description }}
                </p>
            </div>
            <div>
                <a href="{{ $backRoute }}"
                   class="group inline-flex items-center px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-200 bg-white/70 dark:bg-zinc-800/50 hover:bg-white dark:hover:bg-zinc-700/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                    <i data-lucide="arrow-left" class="mr-2 -ml-1 w-4 h-4 transition-transform group-hover:-translate-x-1"></i>
                    {{ $backLabel }}
                </a>
            </div>
        </div>
    </div>

    <!-- Floating Notification -->
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
            'bg-red-50/80 border-red-200/50 dark:bg-red-900/30 dark:border-red-800/30': notificationType === 'error',
            'bg-blue-50/80 border-blue-200/50 dark:bg-blue-900/30 dark:border-blue-800/30': notificationType === 'info'
         }" style="display: none;">
        <div class="flex items-start">
            <div x-show="notificationType === 'success'" class="flex-shrink-0 w-5 h-5 mr-3 text-green-600 dark:text-green-400">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div x-show="notificationType === 'error'" class="flex-shrink-0 w-5 h-5 mr-3 text-red-600 dark:text-red-400">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
            <div x-show="notificationType === 'info'" class="flex-shrink-0 w-5 h-5 mr-3 text-blue-600 dark:text-blue-400">
                <i data-lucide="info" class="w-5 h-5"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-medium mb-1"
                    :class="{
                        'text-green-800 dark:text-green-200': notificationType === 'success',
                        'text-red-800 dark:text-red-200': notificationType === 'error',
                        'text-blue-800 dark:text-blue-200': notificationType === 'info'
                    }"
                    x-text="notificationTitle"></h4>
                <p class="text-sm"
                   :class="{
                        'text-green-700/90 dark:text-green-300/90': notificationType === 'success',
                        'text-red-700/90 dark:text-red-300/90': notificationType === 'error',
                        'text-blue-700/90 dark:text-blue-300/90': notificationType === 'info'
                    }"
                   x-text="notificationMessage"></p>
            </div>
            <button @click="hideNotification" class="ml-4 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-white/70 dark:bg-zinc-800/50 rounded-2xl shadow-2xl border border-zinc-200/50 dark:border-zinc-700/30 backdrop-blur-lg transition-all duration-300 hover:shadow-2xl">
        <div class="p-8">
            <!-- Creation Mode Tabs (only shown if AI is enabled) -->
            <div x-show="hasAI" class="mb-6 flex justify-center">
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

            <!-- AI Generation Section -->
            <div x-show="hasAI && creationMode === 'ai'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="mb-6 animate-fade-in-up">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - Context Panel -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-gradient-to-br from-indigo-50/80 to-purple-50/80 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-4 border border-indigo-100/70 dark:border-indigo-800/50">
                            <h3 class="text-lg font-semibold text-indigo-800 dark:text-indigo-200 mb-2 flex items-center">
                                <i data-lucide="lightbulb" class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400"></i>
                                AI Generation Context
                            </h3>
                            <p class="text-sm text-indigo-700/90 dark:text-indigo-300 mb-4">
                                Add context to help the AI generate better content.
                            </p>

                            <!-- AI Context Fields (dynamic based on configuration) -->
                            <template x-for="field in aiContextFields" :key="field.name">
                                <div class="mb-4">
                                    <label :for="'ai-context-' + field.name" class="block text-sm font-medium text-indigo-700 dark:text-indigo-300 mb-2">
                                        <span x-text="field.label"></span>
                                        <span x-show="field.required" class="text-red-500">*</span>
                                        <span x-show="!field.required" class="text-indigo-500 dark:text-indigo-400 text-xs font-normal">(Optional)</span>
                                    </label>
                                    <div class="relative">
                                        <!-- Select field -->
                                        <template x-if="field.type === 'select'">
                                            <select :id="'ai-context-' + field.name"
                                                    x-model="aiContext[field.name]"
                                                    :disabled="field.disabled"
                                                    class="w-full px-4 py-2.5 rounded-lg border border-indigo-200/70 dark:border-indigo-800/50 bg-white/70 dark:bg-zinc-800/50 text-indigo-800 dark:text-indigo-200 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all">
                                                <option value="">Select an option</option>
                                                <template x-for="option in field.options" :key="option.value">
                                                    <option :value="option.value" x-text="option.label"></option>
                                                </template>
                                            </select>
                                        </template>

                                        <!-- Text input field -->
                                        <template x-if="field.type === 'text'">
                                            <input :id="'ai-context-' + field.name"
                                                   type="text"
                                                   x-model="aiContext[field.name]"
                                                   :placeholder="field.placeholder"
                                                   class="w-full px-4 py-2.5 rounded-lg border border-indigo-200/70 dark:border-indigo-800/50 bg-white/70 dark:bg-zinc-800/50 text-indigo-800 dark:text-indigo-200 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all">
                                        </template>

                                        <!-- Textarea field -->
                                        <template x-if="field.type === 'textarea'">
                                            <textarea :id="'ai-context-' + field.name"
                                                      x-model="aiContext[field.name]"
                                                      :placeholder="field.placeholder"
                                                      :rows="field.rows || 4"
                                                      class="w-full px-4 py-2.5 rounded-lg border border-indigo-200/70 dark:border-indigo-800/50 bg-white/70 dark:bg-zinc-800/50 text-indigo-800 dark:text-indigo-200 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"></textarea>
                                        </template>
                                    </div>
                                    <p class="mt-1 text-xs text-indigo-600/70 dark:text-indigo-400/70" x-text="field.help || ''"></p>
                                </div>
                            </template>
                        </div>

                        <!-- AI Generation History -->
                        <div class="bg-zinc-50/80 dark:bg-zinc-800/80 rounded-xl p-4 border border-zinc-200/70 dark:border-zinc-700/50">
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
                                                x-text="item.title || 'Generated Content'"></h4>
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
                                    <template x-for="template in aiTemplates" :key="template.id">
                                        <button type="button" @click="useTemplate(template.id)"
                                            class="flex items-center px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                            <i :data-lucide="template.icon" class="w-4 h-4 mr-2" :class="template.iconClass"></i>
                                            <span x-text="template.name"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Prompt Input -->
                            <div class="mb-4">
                                <label for="ai-prompt" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Prompt <span class="text-red-500">*</span>
                                </label>
                                <textarea x-model="aiPrompt" id="ai-prompt" rows="5" placeholder="Describe what you want to create..."
                                    class="w-full px-4 py-3 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
                                    :class="{ 'border-red-500 dark:border-red-500': promptError }"></textarea>
                                <p x-show="promptError" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    <span x-text="promptError"></span>
                                </p>
                            </div>

                            <div class="flex justify-end">
                                <button @click="generateContent"
                                    class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg shadow-md hover:shadow-lg flex items-center transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="isGenerating || !canGenerate">
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
                                    <span x-text="isGenerating ? 'Generating...' : 'Generate'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Generated Result -->
                        <div x-show="generatedResult" x-transition
                            class="bg-gradient-to-br from-indigo-50/80 to-purple-50/80 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-6 border border-indigo-200/70 dark:border-indigo-800/40 shadow-sm">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-lg font-semibold text-indigo-800 dark:text-indigo-200 flex items-center">
                                    <i data-lucide="file-text" class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400"></i>
                                    Generated Result
                                </h3>
                                <div class="flex items-center space-x-2">
                                    <button @click="regenerateContent"
                                        class="p-2 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors"
                                        :disabled="isGenerating" title="Regenerate">
                                        <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                                    </button>
                                    <button @click="applyGeneratedContent" type="button"
                                        class="p-2 rounded-lg text-green-600 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors"
                                        title="Use This Content">
                                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="bg-white/80 dark:bg-zinc-800/60 rounded-lg p-4 border border-indigo-100 dark:border-indigo-800/30 space-y-4" id="generated-content-display">
                                <!-- Dynamically generated content goes here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manual Form -->
            <form id="entity-form" :action="submitAction" method="POST" @submit.prevent="submitForm" x-show="!hasAI || creationMode === 'manual'">
                @csrf
                <template x-if="isEdit">
                    @method('PUT')
                </template>
                <div class="space-y-6">
                    {{ $form ?? '' }}
                </div>

                <!-- Submit Button -->
                <div class="mt-8 flex justify-end space-x-4 animate-fade-in-up delay-600">
                    <button type="button"
                            @click="history.back()"
                            class="px-6 py-2.5 text-zinc-700 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-white bg-zinc-100/70 dark:bg-zinc-700/50 rounded-xl hover:bg-zinc-200/50 dark:hover:bg-zinc-600/50 backdrop-blur-sm transition-all duration-300 hover:shadow-md transform hover:-translate-y-0.5">
                        Cancel
                    </button>
                    <button type="submit"
                            :disabled="isSubmitting"
                            class="relative px-8 py-2.5 text-white bg-gradient-to-r from-zinc-800 to-zinc-600 dark:from-zinc-700 dark:to-zinc-500 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                        <span class="relative z-10 flex items-center">
                            <span x-show="!isSubmitting" class="flex items-center">
                                <span x-text="submitButtonText"></span>
                                <i data-lucide="save" class="w-4 h-4 ml-2"></i>
                            </span>
                            <span x-show="isSubmitting" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="submittingText"></span>
                            </span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Danger Zone (if needed) -->
    <div x-show="showDangerZone" class="mt-8 bg-red-50/30 dark:bg-red-900/10 shadow-sm rounded-xl border border-red-200/70 dark:border-red-800/50">
        <div class="px-6 py-4 border-b border-red-200/50 dark:border-red-800/20 bg-red-100/20 dark:bg-red-900/10">
            <h2 class="text-lg font-medium text-red-800 dark:text-red-400 flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                Danger Zone
            </h2>
        </div>
        <div class="p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0 text-red-400 dark:text-red-500">
                    <i data-lucide="trash-2" class="h-5 w-5"></i>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Delete this {{ $entityName }}</h3>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        Once you delete a {{ $entityName }}, there is no going back. This action cannot be undone.
                    </p>
                    <div class="mt-3">
                        <button type="button"
                            @click="confirmDelete"
                            class="btn-danger px-4 py-2.5 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white shadow-sm transition-all">
                            {{ $dangerText }}
                        </button>

                        <form :id="'delete-form-' + entityName" :action="dangerAction" method="POST" class="hidden">
                            @csrf
                            @method($dangerMethod)
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
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
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('entityForm', (config) => ({
        submitAction: config.submitAction,
        entityName: config.entityName,
        hasAI: config.hasAI,
        aiEndpoint: config.aiEndpoint,
        aiConfig: config.aiConfig,
        isEdit: config.isEdit,
        oldData: config.oldData || {},
        csrfToken: config.csrfToken,

        // Form state
        isSubmitting: false,
        submitButtonText: config.isEdit ? `Update ${config.entityName}` : `Create ${config.entityName}`,
        submittingText: config.isEdit ? 'Updating...' : 'Creating...',
        showDangerZone: config.isEdit,

        // Notification system
        showNotification: false,
        notificationType: 'success',
        notificationTitle: '',
        notificationMessage: '',

        // AI state
        creationMode: 'manual',
        aiPrompt: '',
        promptError: '',
        aiContext: {},
        aiContextFields: config.aiConfig?.contextFields || [],
        aiTemplates: config.aiConfig?.templates || [],
        isGenerating: false,
        generatedResult: null,
        generationHistory: [],

        init() {
            // Initialize from oldData or session flash
            this.initFromOldData();
            this.initNotificationsFromFlash();

            // Initialize the AI context if needed
            if (this.hasAI) {
                this.initAIContext();
            }

            // Load generation history from localStorage if available
            this.loadGenerationHistory();

            // Initialize Lucide icons
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });
        },

        initFromOldData() {
            // This will be extended by the implementing form
            // Default implementation does nothing
        },

        initNotificationsFromFlash() {
            @if(session('success'))
                this.showNotificationMessage('success', 'Success', '{{ session('success') }}');
            @endif

            @if(session('error'))
                this.showNotificationMessage('error', 'Error', '{{ session('error') }}');
            @endif

            @if($errors->any())
                this.showNotificationMessage('error', 'Validation Error', 'There were errors in your submission. Please check the form.');
            @endif
        },

        initAIContext() {
            // Initialize the AI context object with default values
            this.aiContextFields.forEach(field => {
                if (field.default !== undefined) {
                    this.aiContext[field.name] = field.default;
                } else {
                    this.aiContext[field.name] = '';
                }
            });
        },

        loadGenerationHistory() {
            const key = `${this.entityName}_generation_history`;
            const saved = localStorage.getItem(key);
            if (saved) {
                try {
                    this.generationHistory = JSON.parse(saved).slice(0, 10);
                } catch (e) {
                    console.error('Failed to parse generation history:', e);
                    this.generationHistory = [];
                }
            }
        },

        saveGenerationHistory() {
            const key = `${this.entityName}_generation_history`;
            localStorage.setItem(key, JSON.stringify(this.generationHistory.slice(0, 10)));
        },

        showNotificationMessage(type, title, message) {
            this.notificationType = type;
            this.notificationTitle = title;
            this.notificationMessage = message;
            this.showNotification = true;

            // Auto-hide after a delay
            setTimeout(() => {
                this.hideNotification();
            }, type === 'error' ? 7000 : 5000);
        },

        hideNotification() {
            this.showNotification = false;
        },

        formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleString();
        },

        useTemplate(templateId) {
            const template = this.aiTemplates.find(t => t.id === templateId);
            if (template && template.promptTemplate) {
                this.aiPrompt = template.promptTemplate;
            }
        },

        get canGenerate() {
            return this.aiPrompt.trim() !== '' &&
                   this.aiContextFields
                       .filter(field => field.required)
                       .every(field => this.aiContext[field.name]);
        },

        async generateContent() {
            if (!this.canGenerate || this.isGenerating) return;

            this.promptError = '';
            this.isGenerating = true;

            try {
                const payload = {
                    prompt: this.aiPrompt,
                    context: this.aiContext,
                    entity_type: this.entityName
                };

                const response = await fetch(this.aiEndpoint, {
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
                    this.generatedResult = result.data;

                    // Add to history
                    const historyEntry = {
                        timestamp: Date.now(),
                        prompt: this.aiPrompt,
                        title: result.data.title || `Generated ${this.entityName}`,
                        data: result.data
                    };

                    this.generationHistory = [historyEntry, ...this.generationHistory].slice(0, 10);
                    this.saveGenerationHistory();

                    // Display the result
                    this.displayGeneratedResult(result.data);

                    this.showNotificationMessage('success', 'Generation Complete',
                        `Content generated successfully. Click "Use This Content" to apply it to the form.`);
                } else {
                    throw new Error(result.message || 'Failed to generate content');
                }
            } catch (error) {
                console.error('Generation error:', error);
                this.promptError = error.message || 'An error occurred during generation';
                this.showNotificationMessage('error', 'Generation Failed', this.promptError);
            } finally {
                this.isGenerating = false;
            }
        },

        regenerateContent() {
            // Just call generate again with the same settings
            this.generateContent();
        },

        displayGeneratedResult(data) {
            const container = document.getElementById('generated-content-display');
            if (!container) return;

            // Clear previous content
            container.innerHTML = '';

            // Create elements based on the data structure
            // This is a generic implementation - specific entity types may need customization
            Object.entries(data).forEach(([key, value]) => {
                if (key === 'id' || key === 'timestamp') return;

                const section = document.createElement('div');

                const label = document.createElement('h4');
                label.className = 'text-sm font-medium text-indigo-700 dark:text-indigo-300';
                label.textContent = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) + ':';
                section.appendChild(label);

                if (Array.isArray(value)) {
                    // Handle arrays (like list of items)
                    const list = document.createElement('ul');
                    list.className = 'mt-1 space-y-1 text-zinc-800 dark:text-zinc-200 list-disc list-inside';

                    value.forEach(item => {
                        const li = document.createElement('li');
                        li.textContent = item;
                        list.appendChild(li);
                    });

                    section.appendChild(list);
                } else if (typeof value === 'object' && value !== null) {
                    // Handle nested objects
                    const pre = document.createElement('pre');
                    pre.className = 'mt-1 text-zinc-800 dark:text-zinc-200 text-sm overflow-auto';
                    pre.textContent = JSON.stringify(value, null, 2);
                    section.appendChild(pre);
                } else {
                    // Handle simple values
                    const p = document.createElement('p');
                    p.className = 'mt-1 text-zinc-800 dark:text-zinc-200 whitespace-pre-line';
                    p.textContent = value;
                    section.appendChild(p);
                }

                container.appendChild(section);
            });
        },

        applyGeneratedContent() {
            // This will be overridden in the implementing component
            // Default implementation shows a notification
            this.showNotificationMessage('info', 'Override Needed',
                'The applyGeneratedContent method needs to be implemented for this entity type.');
        },

        useHistoryItem(index) {
            const item = this.generationHistory[index];
            if (!item) return;

            this.generatedResult = item.data;
            this.aiPrompt = item.prompt;
            this.displayGeneratedResult(item.data);

            this.showNotificationMessage('info', 'History Item Loaded', 'Content loaded from history.');
        },

        submitForm() {
            // This will be extended by the implementing form
            // Default implementation just submits the form
            this.isSubmitting = true;
            document.getElementById('entity-form').submit();
        },

        confirmDelete() {
            if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                document.getElementById(`delete-form-${this.entityName}`).submit();
            }
        }
    }));
});
</script>
@endpush
