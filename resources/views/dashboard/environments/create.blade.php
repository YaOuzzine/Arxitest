@extends('layouts.dashboard')

@section('title', 'Create Environment')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.environments.index') }}" class="text-zinc-700 dark:text-zinc-300">Environments</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Create</span>
    </li>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6">Create Environment</h1>

        <form action="{{ route('dashboard.environments.store') }}" method="POST" x-data="{
            isGlobal: false,
            configurations: [],
            addConfig() {
                this.configurations.push({ key: '', value: '' });
            },
            removeConfig(index) {
                this.configurations.splice(index, 1);
            }
        }">
            @csrf

            <div class="space-y-6">
                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Environment Name
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                        class="w-full border-zinc-300 dark:border-zinc-700 rounded-lg shadow-sm dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Development, Staging, Production, etc.">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Global Toggle --}}
                <div class="flex items-center">
                    <div class="flex items-center h-5">
                        <input id="is_global" name="is_global" type="checkbox"
                            x-model="isGlobal"
                            class="h-4 w-4 text-indigo-600 border-zinc-300 rounded focus:ring-indigo-500 dark:bg-zinc-700 dark:border-zinc-600">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_global" class="font-medium text-zinc-700 dark:text-zinc-300">
                            Global Environment
                        </label>
                        <p class="text-zinc-500 dark:text-zinc-400">Make this environment available to all projects across all teams</p>
                    </div>
                </div>

                {{-- Active Toggle --}}
                <div class="flex items-center">
                    <div class="flex items-center h-5">
                        <input id="is_active" name="is_active" type="checkbox" checked
                            class="h-4 w-4 text-indigo-600 border-zinc-300 rounded focus:ring-indigo-500 dark:bg-zinc-700 dark:border-zinc-600">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_active" class="font-medium text-zinc-700 dark:text-zinc-300">
                            Active
                        </label>
                        <p class="text-zinc-500 dark:text-zinc-400">Make this environment available for test executions</p>
                    </div>
                </div>

                {{-- Project Selection (only if not global) --}}
                <div x-show="!isGlobal" x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <label for="projects" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Projects
                    </label>
                    <select id="projects" name="projects[]" multiple
                        class="w-full border-zinc-300 dark:border-zinc-700 rounded-lg shadow-sm dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-indigo-500 focus:border-indigo-500"
                        size="5">
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ in_array($project->id, old('projects', [])) ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Hold Ctrl/Cmd to select multiple projects</p>
                    @error('projects')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Configuration Variables --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Environment Variables
                        </label>
                        <button type="button" @click="addConfig"
                            class="inline-flex items-center px-3 py-1 text-xs rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 dark:text-indigo-300 dark:bg-indigo-900/30 dark:hover:bg-indigo-800/50">
                            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i>
                            Add Variable
                        </button>
                    </div>

                    <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4">
                        <template x-if="configurations.length === 0">
                            <div class="text-center py-4 text-zinc-500 dark:text-zinc-400 text-sm">
                                No environment variables defined. Click "Add Variable" to create one.
                            </div>
                        </template>

                        <div class="space-y-3">
                            <template x-for="(config, index) in configurations" :key="index">
                                <div class="flex items-center space-x-2">
                                    <div class="flex-1">
                                        <input type="text" :name="`configuration[${index}][key]`" x-model="config.key"
                                            class="w-full border-zinc-300 dark:border-zinc-700 rounded-lg shadow-sm dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                            placeholder="Variable Name (e.g. BASE_URL)">
                                    </div>
                                    <div class="flex-1">
                                        <input type="text" :name="`configuration[${index}][value]`" x-model="config.value"
                                            class="w-full border-zinc-300 dark:border-zinc-700 rounded-lg shadow-sm dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                            placeholder="Value">
                                    </div>
                                    <div>
                                        <button type="button" @click="removeConfig(index)"
                                            class="inline-flex items-center p-1.5 rounded-md text-red-600 hover:bg-red-100 dark:text-red-400 dark:hover:bg-red-900/30">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    @error('configuration')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit Button --}}
                <div class="flex justify-end mt-8">
                    <a href="{{ route('dashboard.environments.index') }}" class="btn-secondary mr-3">
                        Cancel
                    </a>
                    <button type="submit" class="btn-primary">
                        Create Environment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        // Pre-populate configurations from old input if validation failed
        @if(old('configuration'))
            let oldConfig = @json(old('configuration'));
            Alpine.store('oldConfig', oldConfig);
        @else
            Alpine.store('oldConfig', []);
        @endif
    });

    document.addEventListener('DOMContentLoaded', () => {
        // Initialize with default environment variables
        window.addEventListener('alpine:init', () => {
            let initializeForm = document.querySelector('form');
            if (initializeForm && typeof initializeForm.__x !== 'undefined') {
                // Check if we have old input
                if (Alpine.store('oldConfig').length > 0) {
                    initializeForm.__x.getUnobservedData().configurations = Alpine.store('oldConfig');
                } else {
                    // Add default variables
                    initializeForm.__x.getUnobservedData().configurations = [
                        { key: 'BASE_URL', value: 'http://localhost:8000' },
                        { key: 'HEADLESS', value: 'false' }
                    ];
                }
            }
        });
    });
</script>
@endpush
