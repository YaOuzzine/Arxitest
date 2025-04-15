@php
    /**
     * @var \App\Models\Project $project
     * @var \App\Models\TestSuite $testSuite
     */
    $pageTitle = 'Edit Test Suite: ' . $testSuite->name;
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
        <a href="{{ route('dashboard.projects.test-suites.show', [$project->id, $testSuite->id]) }}" class="...">{{ $testSuite->name }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Edit</span>
    </li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ isSubmitting: false }">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">{{ $pageTitle }}</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Update the details and settings for this test suite.</p>
    </div>

    <!-- Form Container -->
    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg border border-zinc-200/50 dark:border-zinc-700/50 p-8">
        <form id="edit-test-suite-form" method="POST" action="{{ route('dashboard.projects.test-suites.update', [$project->id, $testSuite->id]) }}" @submit="isSubmitting = true">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-zinc-800 dark:text-white">Test Suite Details</h3>

                <div>
                    <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Test Suite Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $testSuite->name) }}" required maxlength="100"
                        class="w-full px-4 py-2.5 rounded-lg border border-zinc-300/80 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-900/30 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/30 transition-all duration-200 shadow-sm placeholder-zinc-400">
                    @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Description
                    </label>
                    <textarea id="description" name="description" rows="3" maxlength="255"
                        class="w-full px-4 py-2.5 rounded-lg border border-zinc-300/80 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-900/30 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/30 transition-all duration-200 shadow-sm placeholder-zinc-400 resize-none"
                        placeholder="Briefly describe what this test suite covers">{{ old('description', $testSuite->description) }}</textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <fieldset class="border border-zinc-200 dark:border-zinc-700 p-4 rounded-lg">
                    <legend class="text-sm font-medium text-zinc-700 dark:text-zinc-300 px-2">Settings</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                        <div>
                             <label for="settings-default-priority" class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">
                                Default Priority <span class="text-red-500">*</span>
                            </label>
                            <select id="settings-default-priority" name="settings[default_priority]" required
                                class="w-full px-3 py-2 rounded-md border border-zinc-300/80 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-900/30 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/30 text-sm transition-all duration-200 shadow-sm appearance-none bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9ImN1cnJlbnRDb2xvciIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiPjxwb2x5bGluZSBwb2ludHM9IjYgOSAxMiAxNSAxOCA5Ij48L3BvbHlsaW5lPjwvc3ZnPg==')] bg-no-repeat bg-[right_0.75rem_center] bg-[length:1em]">
                                <option value="low" {{ old('settings.default_priority', $testSuite->settings['default_priority'] ?? 'medium') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('settings.default_priority', $testSuite->settings['default_priority'] ?? 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('settings.default_priority', $testSuite->settings['default_priority'] ?? 'medium') === 'high' ? 'selected' : '' }}>High</option>
                            </select>
                             @error('settings.default_priority') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                             <label for="settings-execution-mode" class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">
                                Execution Mode
                            </label>
                            <select id="settings-execution-mode" name="settings[execution_mode]"
                                class="w-full px-3 py-2 rounded-md border border-zinc-300/80 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-900/30 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/30 text-sm transition-all duration-200 shadow-sm appearance-none bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZHRoPSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9ImN1cnJlbnRDb2xvciIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiPjxwb2x5bGluZSBwb2ludHM9IjYgOSAxMiAxNSAxOCA5Ij48L3BvbHlsaW5lPjwvc3ZnPg==')] bg-no-repeat bg-[right_0.75rem_center] bg-[length:1em]">
                                <option value="sequential" {{ old('settings.execution_mode', $testSuite->settings['execution_mode'] ?? 'sequential') === 'sequential' ? 'selected' : '' }}>Sequential</option>
                                <option value="parallel" {{ old('settings.execution_mode', $testSuite->settings['execution_mode'] ?? 'sequential') === 'parallel' ? 'selected' : '' }}>Parallel</option>
                            </select>
                            @error('settings.execution_mode') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </fieldset>

                 <!-- Submit Button -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('dashboard.projects.test-suites.show', [$project->id, $testSuite->id]) }}" class="px-5 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors duration-200 text-sm font-medium">
                        Cancel
                    </a>
                    <button type="submit"
                        :disabled="isSubmitting"
                        class="inline-flex items-center px-6 py-2.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-white shadow-md hover:shadow-lg transition-all duration-300 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i data-lucide="loader" x-show="isSubmitting" class="animate-spin w-4 h-4 mr-2"></i>
                        <i data-lucide="save" x-show="!isSubmitting" class="w-4 h-4 mr-2"></i>
                        <span x-text="isSubmitting ? 'Saving...' : 'Save Changes'"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
