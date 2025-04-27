@extends('layouts.dashboard')

@section('title', 'Create New Story')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.stories.indexAll') }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">Stories</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Create</span>
    </li>
@endsection

@section('content')
<div class="h-full space-y-6">
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
    </div>

    <!-- Create Form -->
    <div class="bg-white dark:bg-zinc-800/50 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 backdrop-blur-sm">
        <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/30 dark:bg-zinc-800/30">
            <h2 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center gap-2">
                <i data-lucide="file-plus" class="w-5 h-5 text-zinc-500 dark:text-zinc-400"></i>
                Story Information
            </h2>
        </div>

        <form action="{{ route('dashboard.stories.store') }}" method="POST" class="p-6 space-y-6" id="storyForm">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Project Selection (Required) -->
                <div>
                    <label for="project_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2 flex items-center gap-1">
                        <i data-lucide="folder" class="w-4 h-4 text-zinc-400"></i>
                        Project <span class="text-red-600">*</span>
                    </label>
                    <select name="project_id" id="project_id" class="project-select w-full" required>
                        <option value="">Select Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $selectedProject?->id) == $project->id ? 'selected' : '' }}>
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
                    <label for="epic_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2 flex items-center gap-1">
                        <i data-lucide="layers" class="w-4 h-4 text-zinc-400"></i>
                        Epic <span class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional)</span>
                    </label>
                    <select name="epic_id" id="epic_id" class="epic-select w-full" {{ empty($epics) && !$selectedProject ? 'disabled' : '' }}>
                        <option value="">None - Independent Story</option>
                        @foreach($epics as $epic)
                            <option value="{{ $epic->id }}" {{ old('epic_id') == $epic->id ? 'selected' : '' }}>
                                {{ $epic->name }}{{ $epic->status ? ' (' . $epic->status . ')' : '' }}
                            </option>
                        @endforeach
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
                    <label for="title" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2 flex items-center gap-1">
                        <i data-lucide="text" class="w-4 h-4 text-zinc-400"></i>
                        Title <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}"
                        class="w-full px-4 py-2.5 rounded-lg border border-zinc-300/70 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-700/50 text-zinc-800 dark:text-zinc-200 placeholder-zinc-400 dark:placeholder-zinc-500 shadow-sm
                               focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all
                               @error('title') border-red-500 dark:border-red-500 @enderror"
                        placeholder="Enter story title" required>
                    @error('title')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2 flex items-center gap-1">
                        <i data-lucide="align-left" class="w-4 h-4 text-zinc-400"></i>
                        Description
                    </label>
                    <textarea name="description" id="description" rows="6"
                        class="w-full px-4 py-2.5 rounded-lg border border-zinc-300/70 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-700/50 text-zinc-800 dark:text-zinc-200 placeholder-zinc-400 dark:placeholder-zinc-500 shadow-sm
                               focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                        placeholder="Enter story description, acceptance criteria, and other details">{{ old('description') }}</textarea>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Include acceptance criteria, user requirements, and other relevant details
                    </p>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- AI Generation Prompt -->
                <div class="md:col-span-2">
                    <div class="rounded-xl bg-gradient-to-br from-indigo-50/50 to-purple-50/50 dark:from-indigo-900/20 dark:to-purple-900/20 p-4 border border-indigo-200/70 dark:border-indigo-800/50">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 animate-pulse">
                                <i data-lucide="sparkles" class="h-6 w-6 text-indigo-600 dark:text-indigo-400"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-medium text-indigo-800 dark:text-indigo-200">Need help writing your story?</h3>
                                <div class="mt-2 text-sm text-indigo-700/90 dark:text-indigo-300">
                                    <p>Use our AI-powered generator to create well-structured user stories instantly.</p>
                                </div>
                                <div class="mt-3">
                                    <button type="button"
                                            id="openAiPromptBtn"
                                            class="inline-flex items-center px-3.5 py-2.5 rounded-lg bg-indigo-600/10 dark:bg-indigo-400/10 border border-indigo-200/50 dark:border-indigo-600/50 text-indigo-600 dark:text-indigo-300 hover:bg-indigo-600/20 dark:hover:bg-indigo-400/20 transition-colors">
                                        <i data-lucide="wand" class="mr-2 h-4 w-4"></i>
                                        Generate with AI
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-zinc-200/50 dark:border-zinc-700/50 pt-6 flex flex-wrap gap-3 justify-between">
                <a href="{{ route('dashboard.stories.indexAll') }}"
                   class="btn-secondary px-4 py-2.5 rounded-lg bg-white/50 dark:bg-zinc-700/50 border border-zinc-300/70 dark:border-zinc-600/50 hover:bg-zinc-50/70 dark:hover:bg-zinc-600/50 shadow-sm transition-all">
                    Cancel
                </a>
                <div class="flex flex-wrap gap-3">
                    <button type="submit"
                            class="btn-primary px-4 py-2.5 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white shadow-sm transition-all">
                        Create Story
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- User Stories Guide -->
    <div class="bg-white dark:bg-zinc-800/50 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 backdrop-blur-sm">
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
                    <div class="p-4 rounded-xl bg-emerald-50/50 dark:bg-emerald-900/20 border border-emerald-200/50 dark:border-emerald-800/50">
                        <p class="font-medium text-emerald-800 dark:text-emerald-300">Ideal Structure:</p>
                        <blockquote class="mt-2 pl-4 border-l-4 border-emerald-400 dark:border-emerald-500">
                            <p class="text-zinc-700 dark:text-zinc-300">As a <span class="font-medium">[user role]</span>,<br>
                            I want <span class="font-medium">[goal]</span><br>
                            So that <span class="font-medium">[reason]</span></p>
                        </blockquote>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4 mt-6">
                        <div class="p-4 rounded-xl bg-blue-50/50 dark:bg-blue-900/20 border border-blue-200/50 dark:border-blue-800/50">
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300">Example 1</h4>
                            <p class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">
                                <strong>Title:</strong> Password Reset<br>
                                <strong>Description:</strong> As a registered user, I want to reset my password so I can regain access if I forget it.
                            </p>
                        </div>

                        <div class="p-4 rounded-xl bg-purple-50/50 dark:bg-purple-900/20 border border-purple-200/50 dark:border-purple-800/50">
                            <h4 class="text-sm font-medium text-purple-800 dark:text-purple-300">Example 2</h4>
                            <p class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">
                                <strong>Title:</strong> Product Search<br>
                                <strong>Description:</strong> As a shopper, I want to search products by name so I can quickly find items I'm looking for.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Generator Modal -->
<div id="aiGeneratorModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
            <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i data-lucide="sparkles" class="w-5 h-5 text-indigo-500"></i>
                AI Story Generator
            </h3>
            <button type="button" id="closeAiModal" class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="p-6 flex-1 overflow-auto">
            <div class="space-y-4">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Provide a brief description of what you want this story to be about. Our AI will generate a structured user story with title and description.
                </p>

                <div>
                    <label for="aiPrompt" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                        Story Prompt
                    </label>
                    <textarea id="aiPrompt" rows="4" placeholder="e.g., Create a story about user login functionality with both successful and failed login attempts"
                        class="w-full px-4 py-2.5 rounded-lg border border-zinc-300/70 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-700/50 text-zinc-800 dark:text-zinc-200 placeholder-zinc-400 dark:placeholder-zinc-500 shadow-sm
                            focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"></textarea>
                </div>

                <div id="aiOutputContainer" class="hidden">
                    <div class="p-4 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800/50 space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium text-indigo-700 dark:text-indigo-300">AI Generated Story</h4>
                            <button type="button" id="useAiOutput" class="text-xs px-2 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-all">
                                Use This
                            </button>
                        </div>
                        <div class="border-t border-indigo-200/50 dark:border-indigo-700/50 pt-3">
                            <h5 class="text-sm font-medium text-indigo-800 dark:text-indigo-200" id="aiGeneratedTitle"></h5>
                            <p class="mt-2 text-sm text-indigo-700 dark:text-indigo-300 whitespace-pre-line" id="aiGeneratedDescription"></p>
                        </div>
                    </div>
                </div>

                <div id="aiLoadingIndicator" class="hidden py-8 flex justify-center">
                    <div class="flex flex-col items-center">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 dark:border-indigo-400"></div>
                        <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-400">Generating your story...</p>
                    </div>
                </div>

                <div id="aiErrorMessage" class="hidden p-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800/50 text-red-700 dark:text-red-300 text-sm">
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end gap-2">
            <button type="button" id="cancelAiGeneration" class="px-4 py-2 bg-white dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-600 transition-all">
                Cancel
            </button>
            <button type="button" id="generateAiStory" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all">
                Generate
            </button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<style>
    .ts-control {
        @apply px-4 py-2.5 rounded-lg border border-zinc-300/70 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-700/50 shadow-sm;
    }
    .dark .ts-dropdown {
        @apply bg-zinc-800 border-zinc-700 rounded-lg;
    }
    .dark .ts-dropdown .option:hover {
        @apply bg-zinc-700;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Tom Select for dropdowns
    let projectSelect = new TomSelect('#project_id', {
        create: false,
        render: {
            option: function(data, escape) {
                return `<div class="p-2 hover:bg-zinc-100 dark:hover:bg-zinc-700">${escape(data.text)}</div>`;
            }
        }
    });

    let epicSelect = new TomSelect('#epic_id', {
        create: false,
        render: {
            option: function(data, escape) {
                return `<div class="p-2 hover:bg-zinc-100 dark:hover:bg-zinc-700">${escape(data.text)}</div>`;
            }
        }
    });

    // Load epics when project changes
    projectSelect.on('change', function(projectId) {
        if (!projectId) {
            epicSelect.clear();
            epicSelect.clearOptions();
            epicSelect.addOption({value: '', text: 'None - Independent Story'});
            epicSelect.refreshOptions(false);
            epicSelect.disable();
            return;
        }

        // Show loading state
        epicSelect.clear();
        epicSelect.clearOptions();
        epicSelect.addOption({value: '', text: 'Loading epics...'});
        epicSelect.refreshOptions(false);

        // Fetch epics for selected project
        fetch(`/api/projects/${projectId}/epics`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    epicSelect.enable();
                    epicSelect.clear();
                    epicSelect.clearOptions();

                    // Add "None" option
                    epicSelect.addOption({value: '', text: 'None - Independent Story'});

                    // Add epics
                    const epics = data.data.epics || [];
                    epics.forEach(epic => {
                        const statusText = epic.status ? ` (${epic.status})` : '';
                        epicSelect.addOption({value: epic.id, text: `${epic.name}${statusText}`});
                    });

                    epicSelect.refreshOptions(false);
                } else {
                    epicSelect.disable();
                    epicSelect.clear();
                    epicSelect.clearOptions();
                    epicSelect.addOption({value: '', text: 'Failed to load epics'});
                    epicSelect.refreshOptions(false);
                    console.error('Error loading epics:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                epicSelect.disable();
                epicSelect.clear();
                epicSelect.clearOptions();
                epicSelect.addOption({value: '', text: 'Error loading epics'});
                epicSelect.refreshOptions(false);
            });
    });

    // AI Generator Modal
    const aiGeneratorModal = document.getElementById('aiGeneratorModal');
    const openAiPromptBtn = document.getElementById('openAiPromptBtn');
    const closeAiModal = document.getElementById('closeAiModal');
    const cancelAiGeneration = document.getElementById('cancelAiGeneration');
    const generateAiStory = document.getElementById('generateAiStory');
    const aiPrompt = document.getElementById('aiPrompt');
    const aiOutputContainer = document.getElementById('aiOutputContainer');
    const aiLoadingIndicator = document.getElementById('aiLoadingIndicator');
    const aiErrorMessage = document.getElementById('aiErrorMessage');
    const aiGeneratedTitle = document.getElementById('aiGeneratedTitle');
    const aiGeneratedDescription = document.getElementById('aiGeneratedDescription');
    const useAiOutput = document.getElementById('useAiOutput');

    // Open modal
    openAiPromptBtn.addEventListener('click', function() {
        const projectId = projectSelect.getValue();
        if (!projectId) {
            alert('Please select a project first');
            return;
        }

        aiGeneratorModal.classList.remove('hidden');
        resetAiModal();
    });

    // Close modal
    [closeAiModal, cancelAiGeneration].forEach(element => {
        element.addEventListener('click', function() {
            aiGeneratorModal.classList.add('hidden');
        });
    });

    // Generate AI Story
    generateAiStory.addEventListener('click', function() {
        const projectId = projectSelect.getValue();
        const prompt = aiPrompt.value.trim();

        if (!prompt) {
            showAiError('Please enter a prompt for the AI');
            return;
        }

        if (prompt.length < 20) {
            showAiError('Prompt is too short. Please provide more details.');
            return;
        }

        // Show loading, hide other sections
        resetAiModal();
        aiLoadingIndicator.classList.remove('hidden');

        // Make API call to generate story
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                prompt: prompt,
                project_id: projectId
            })
        })
        .then(response => response.json())
        .then(data => {
            aiLoadingIndicator.classList.add('hidden');

            if (data.success) {
                // Show AI output
                aiOutputContainer.classList.remove('hidden');
                aiGeneratedTitle.textContent = data.data.title || 'Generated Story';
                aiGeneratedDescription.textContent = data.data.description || '';
            } else {
                showAiError(data.message || 'Failed to generate story');
            }
        })
        .catch(error => {
            aiLoadingIndicator.classList.add('hidden');
            showAiError('An error occurred while generating the story');
            console.error('Error:', error);
        });
    });

    // Use AI Output
    useAiOutput.addEventListener('click', function() {
        document.getElementById('title').value = aiGeneratedTitle.textContent;
        document.getElementById('description').value = aiGeneratedDescription.textContent;
        aiGeneratorModal.classList.add('hidden');
    });

    function resetAiModal() {
        aiOutputContainer.classList.add('hidden');
        aiLoadingIndicator.classList.add('hidden');
        aiErrorMessage.classList.add('hidden');
    }

    function showAiError(message) {
        aiErrorMessage.textContent = message;
        aiErrorMessage.classList.remove('hidden');
    }
});
</script>
@endpush
