@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Navigation -->
    <div class="flex items-center text-sm text-gray-500 mb-4">
        <a href="{{ route('projects.index') }}" class="hover:text-blue-600">Projects</a>
        <svg class="h-4 w-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-gray-700">Create Project</span>
    </div>

    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Create Project</h1>
        <p class="text-gray-600">Set up a new project to organize your test automation</p>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Project Name</label>
                        <input type="text" name="name" id="name" class="border border-gray-300 rounded-md w-full py-2 px-3 @error('name') border-red-500 @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="team_id" class="block text-sm font-medium text-gray-700 mb-1">Team</label>
                        <select name="team_id" id="team_id" class="border border-gray-300 rounded-md w-full py-2 px-3 @error('team_id') border-red-500 @enderror" required>
                            <option value="">Select a team</option>
                            @foreach(Auth::user()->teams as $team)
                                <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                    {{ $team->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('team_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="3" class="border border-gray-300 rounded-md w-full py-2 px-3 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Environments -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Environments</label>
                    <div class="bg-gray-50 p-4 rounded-md">
                        <div class="mb-2 text-sm text-gray-500">Select the environments where tests for this project can run:</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach(\App\Models\Environment::where('is_active', true)->get() as $env)
                                <div class="flex items-center">
                                    <input type="checkbox" name="environments[]" id="env_{{ $env->id }}" value="{{ $env->id }}" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" {{ (old('environments') && in_array($env->id, old('environments'))) ? 'checked' : '' }}>
                                    <label for="env_{{ $env->id }}" class="ml-2 block text-sm text-gray-700">
                                        {{ $env->name }}
                                        @if(isset($env->configuration['description']))
                                            <span class="text-xs text-gray-500 block">{{ $env->configuration['description'] }}</span>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Integrations -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Integrations</label>
                    <div class="bg-gray-50 p-4 rounded-md">
                        <div class="mb-2 text-sm text-gray-500">Select the integrations to configure for this project:</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach(\App\Models\Integration::where('is_active', true)->get() as $integration)
                                <div class="flex items-center">
                                    <input type="checkbox" name="integrations[]" id="integration_{{ $integration->id }}" value="{{ $integration->id }}" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" {{ (old('integrations') && in_array($integration->id, old('integrations'))) ? 'checked' : '' }}>
                                    <label for="integration_{{ $integration->id }}" class="ml-2 block text-sm text-gray-700">
                                        {{ $integration->name }} ({{ $integration->type }})
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- AI Settings -->
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700">AI-Assisted Test Generation</label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="settings[ai_enabled]" id="ai_enabled" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" {{ old('settings.ai_enabled') ? 'checked' : '' }} onchange="toggleAIOptions()">
                            <span class="ml-2 text-sm text-gray-700">Enable AI features</span>
                        </label>
                    </div>

                    <div id="ai_options" class="mt-4 bg-gray-50 p-4 rounded-md {{ old('settings.ai_enabled') ? '' : 'hidden' }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="ai_provider" class="block text-sm font-medium text-gray-700 mb-1">AI Provider</label>
                                <select name="settings[ai_provider]" id="ai_provider" class="border border-gray-300 rounded-md w-full py-2 px-3">
                                    <option value="openai" {{ old('settings.ai_provider') == 'openai' ? 'selected' : '' }}>OpenAI</option>
                                    <option value="llama" {{ old('settings.ai_provider') == 'llama' ? 'selected' : '' }}>LLAMA Model</option>
                                </select>
                            </div>

                            <div>
                                <label for="ai_template" class="block text-sm font-medium text-gray-700 mb-1">Generation Template</label>
                                <select name="settings[ai_template]" id="ai_template" class="border border-gray-300 rounded-md w-full py-2 px-3">
                                    <option value="basic" {{ old('settings.ai_template') == 'basic' ? 'selected' : '' }}>Basic Test Structure</option>
                                    <option value="bdd" {{ old('settings.ai_template') == 'bdd' ? 'selected' : '' }}>Behavior-Driven Development</option>
                                    <option value="e2e" {{ old('settings.ai_template') == 'e2e' ? 'selected' : '' }}>End-to-End Testing</option>
                                    <option value="performance" {{ old('settings.ai_template') == 'performance' ? 'selected' : '' }}>Performance Testing</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="ai_custom_prompt" class="block text-sm font-medium text-gray-700 mb-1">Custom Generation Instructions (Optional)</label>
                            <textarea name="settings[ai_custom_prompt]" id="ai_custom_prompt" rows="3" class="border border-gray-300 rounded-md w-full py-2 px-3">{{ old('settings.ai_custom_prompt') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">These instructions will be used to customize the AI-generated tests for this project.</p>
                        </div>
                    </div>
                </div>

                <!-- General Settings -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">General Settings</label>
                    <div class="bg-gray-50 p-4 rounded-md">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <input type="checkbox" name="settings[version_control]" id="version_control" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" {{ old('settings.version_control', true) ? 'checked' : '' }}>
                                <label for="version_control" class="ml-2 block text-sm text-gray-700">
                                    Enable version control for test scripts
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="settings[notification_settings][on_failure]" id="notify_failure" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" {{ old('settings.notification_settings.on_failure', true) ? 'checked' : '' }}>
                                <label for="notify_failure" class="ml-2 block text-sm text-gray-700">
                                    Notify on test failure
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="settings[notification_settings][on_completion]" id="notify_completion" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" {{ old('settings.notification_settings.on_completion', true) ? 'checked' : '' }}>
                                <label for="notify_completion" class="ml-2 block text-sm text-gray-700">
                                    Notify on test completion
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="settings[advanced_metrics]" id="advanced_metrics" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" {{ old('settings.advanced_metrics') ? 'checked' : '' }}>
                                <label for="advanced_metrics" class="ml-2 block text-sm text-gray-700">
                                    Enable advanced metrics collection
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between">
                    <a href="{{ route('projects.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                        Create Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleAIOptions() {
        const aiEnabled = document.getElementById('ai_enabled').checked;
        const aiOptions = document.getElementById('ai_options');

        if (aiEnabled) {
            aiOptions.classList.remove('hidden');
        } else {
            aiOptions.classList.add('hidden');
        }
    }
</script>
@endsection
