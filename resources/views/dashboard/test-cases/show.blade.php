@extends('layouts.dashboard')

@section('title', $testCase->title)

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
    @if ($testSuite)
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <a href="{{ route('dashboard.projects.test-suites.show', [$project->id, $testSuite->id]) }}"
                class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                {{ $testSuite->name }}
            </a>
        </li>
    @endif
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">{{ $testCase->title }}</span>
    </li>
@endsection

@section('content')
    <div x-data="testCaseDetail()" class="space-y-8">
        <!-- Header with actions -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white flex items-center">
                    <i data-lucide="file-check-2" class="w-7 h-7 mr-2 text-indigo-600 dark:text-indigo-400"></i>
                    {{ $testCase->title }}
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    @if ($testCase->story)
                        <span class="inline-flex items-center">
                            <i data-lucide="clipboard-list" class="w-4 h-4 mr-1"></i>
                            Story: <a href="{{ route('dashboard.stories.show', $testCase->story->id) }}"
                                class="ml-1 text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ $testCase->story->title }}
                            </a>
                        </span>
                    @endif
                    <span class="mx-2 text-zinc-300 dark:text-zinc-600">|</span>
                    <span class="inline-flex items-center">
                        <i data-lucide="calendar" class="w-4 h-4 mr-1"></i>
                        Updated: {{ $testCase->updated_at->diffForHumans() }}
                    </span>
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('dashboard.projects.test-cases.edit', [$project->id, $testCase->id]) }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                    Edit Test Case
                </a>
                <button @click="confirmDelete"
                    class="inline-flex items-center px-4 py-2 bg-white border border-zinc-300 rounded-lg shadow-sm font-medium text-zinc-700 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-700">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-2 text-red-500"></i>
                    Delete
                </button>
            </div>
        </div>

        <!-- Main content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Info Card -->
                <div
                    class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center">
                            <i data-lucide="info" class="w-5 h-5 mr-2 text-indigo-500"></i>
                            Test Case Details
                        </h2>
                    </div>
                    <div class="p-6">
                        <!-- Test Case Priority and Status -->
                        <div class="flex flex-wrap gap-3 mb-4">
                            @php
                                $priorityColors = [
                                    'low' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                    'medium' =>
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                    'high' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                ];
                                $statusColors = [
                                    'draft' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300',
                                    'active' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                    'deprecated' =>
                                        'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                                    'archived' =>
                                        'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                ];
                            @endphp
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $priorityColors[$testCase->priority] ?? 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300' }}">
                                <i data-lucide="flag" class="w-4 h-4 mr-1"></i>
                                {{ ucfirst($testCase->priority) }} Priority
                            </span>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$testCase->status] ?? 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300' }}">
                                <i data-lucide="activity" class="w-4 h-4 mr-1"></i>
                                Status: {{ ucfirst($testCase->status) }}
                            </span>
                            @if (is_array($testCase->tags) && count($testCase->tags) > 0)
                                @foreach ($testCase->tags as $tag)
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                                        <i data-lucide="tag" class="w-4 h-4 mr-1"></i>
                                        {{ $tag }}
                                    </span>
                                @endforeach
                            @endif
                        </div>

                        <!-- Description -->
                        @if ($testCase->description)
                            <div class="mb-6">
                                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Description</h3>
                                <div
                                    class="text-zinc-800 dark:text-zinc-200 bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                    {{ $testCase->description }}
                                </div>
                            </div>
                        @endif

                        <!-- Test Steps -->
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Test Steps</h3>
                            <div
                                class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                                <ol class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @if (is_array($testCase->steps))
                                        @foreach ($testCase->steps as $index => $step)
                                            <li class="p-4 flex">
                                                <span
                                                    class="flex-shrink-0 w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-800 dark:text-indigo-300 font-medium mr-3">
                                                    {{ $index + 1 }}
                                                </span>
                                                <span
                                                    class="text-zinc-800 dark:text-zinc-200 pt-1.5">{{ $step }}</span>
                                            </li>
                                        @endforeach
                                    @else
                                        <li class="p-4 italic text-zinc-500 dark:text-zinc-400">No steps defined</li>
                                    @endif
                                </ol>
                            </div>
                        </div>

                        <!-- Expected Results -->
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Expected Results</h3>
                            <div
                                class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <p class="text-zinc-800 dark:text-zinc-200 whitespace-pre-line">
                                    {{ $testCase->expected_results }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Scripts Section with Updated Design -->
                <div
                    class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div
                        class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-gradient-to-r from-zinc-50 to-indigo-50/30 dark:from-zinc-800 dark:to-indigo-900/10">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center">
                                <i data-lucide="code" class="w-5 h-5 mr-2 text-indigo-500"></i>
                                Test Scripts
                            </h2>
                            <a href="{{ route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id]) }}"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-800/30 rounded-lg transition-colors">
                                <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
                                Add Script
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        <!-- Show scripts if available -->
                        @if ($testCase->testScripts && $testCase->testScripts->count() > 0)
                            <div class="space-y-4">
                                @foreach ($testCase->testScripts as $script)
                                    <div
                                        class="bg-zinc-50/80 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/70 dark:border-zinc-700/70 p-4 hover:shadow-md transition-all duration-200 group">
                                        <div class="flex justify-between items-start">
                                            <div class="flex items-start space-x-3">
                                                <div
                                                    class="p-2 rounded-lg bg-indigo-100/80 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                                                    @php
                                                        $frameworkIcons = [
                                                            'selenium-python' => 'terminal-square',
                                                            'cypress' => 'globe',
                                                            'default' => 'code-2',
                                                        ];
                                                        $icon =
                                                            $frameworkIcons[$script->framework_type] ??
                                                            $frameworkIcons['default'];
                                                    @endphp
                                                    <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
                                                </div>
                                                <div>
                                                    <h3 class="text-zinc-900 dark:text-white font-medium">
                                                        {{ $script->name }}</h3>
                                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">
                                                        <span class="inline-flex items-center">
                                                            <i data-lucide="git-branch" class="w-3.5 h-3.5 mr-1"></i>
                                                            {{ ucfirst($script->framework_type) }}
                                                        </span>
                                                        <span class="mx-1.5">•</span>
                                                        <span class="inline-flex items-center">
                                                            <i data-lucide="clock" class="w-3.5 h-3.5 mr-1"></i>
                                                            Updated {{ $script->updated_at->diffForHumans() }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <a href="{{ route('dashboard.projects.test-cases.scripts.update', [$project->id, $testCase->id, $script->id]) }}"
                                                    class="p-1.5 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-lg transition-colors">
                                                    <i data-lucide="edit" class="w-4.5 h-4.5"></i>
                                                </a>
                                                <button
                                                    @click="confirmDeleteScript('{{ $script->id }}', '{{ addslashes($script->name) }}')"
                                                    class="p-1.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors">
                                                    <i data-lucide="trash" class="w-4.5 h-4.5"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-700/50">
                                            <div class="flex justify-between items-center">
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    Created by: {{ $script->creator->name ?? 'System' }}
                                                </div>
                                                <a href="{{ route('dashboard.projects.test-cases.scripts.show', [$project->id, $testCase->id, $script->id]) }}"
                                                    class="inline-flex items-center text-xs font-medium text-indigo-600 dark:text-indigo-400 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors">
                                                    View Script
                                                    <i data-lucide="chevron-right"
                                                        class="w-3.5 h-3.5 ml-1 group-hover:ml-2 transition-all"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-10 px-4">
                                <div
                                    class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-indigo-50 to-blue-100 dark:from-indigo-900/20 dark:to-blue-900/20 text-indigo-600 dark:text-indigo-400 mb-4">
                                    <i data-lucide="code" class="w-8 h-8"></i>
                                </div>
                                <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No Test Scripts Yet</h3>
                                <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-6">
                                    Add test scripts to automate this test case with Selenium, Cypress, or other frameworks.
                                </p>
                                <a href="{{ route('dashboard.projects.test-cases.scripts.create', [$project->id, $testCase->id]) }}"
                                    class="inline-flex items-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                    <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
                                    Create Test Script
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Test Data Section with Updated Design -->
                <div
                    class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden mt-6">
                    <div
                        class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-gradient-to-r from-zinc-50 to-blue-50/30 dark:from-zinc-800 dark:to-blue-900/10">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center">
                                <i data-lucide="database" class="w-5 h-5 mr-2 text-blue-500"></i>
                                Test Data
                            </h2>
                            <a href="#"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-800/30 rounded-lg transition-colors">
                                <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
                                Add Test Data
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        <!-- Show test data if available -->
                        @if ($testCase->testData && $testCase->testData->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($testCase->testData as $data)
                                    <div
                                        class="bg-zinc-50/80 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/70 dark:border-zinc-700/70 p-4 hover:shadow-md transition-all duration-200 group">
                                        <div class="flex justify-between items-start">
                                            <div class="flex items-start space-x-3">
                                                <div
                                                    class="p-2 rounded-lg
                                    @if ($data->format == 'json') bg-blue-100/80 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400
                                    @elseif($data->format == 'csv')
                                        bg-green-100/80 dark:bg-green-900/30 text-green-600 dark:text-green-400
                                    @elseif($data->format == 'xml')
                                        bg-purple-100/80 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400
                                    @else
                                        bg-cyan-100/80 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400 @endif">
                                                    @php
                                                        $formatIcons = [
                                                            'json' => 'braces',
                                                            'csv' => 'table',
                                                            'xml' => 'code',
                                                            'plain' => 'file-text',
                                                            'default' => 'database',
                                                        ];
                                                        $icon = $formatIcons[$data->format] ?? $formatIcons['default'];
                                                    @endphp
                                                    <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
                                                </div>
                                                <div>
                                                    <div class="flex items-center">
                                                        <h3 class="text-zinc-900 dark:text-white font-medium">
                                                            {{ $data->name }}</h3>
                                                        @if ($data->is_sensitive)
                                                            <span
                                                                class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                                                <i data-lucide="shield" class="w-3 h-3 mr-0.5"></i>
                                                                Sensitive
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">
                                                        <span class="uppercase font-medium">{{ $data->format }}</span>
                                                        @if (isset($data->pivot) && $data->pivot->usage_context)
                                                            <span class="mx-1.5">•</span>
                                                            {{ $data->pivot->usage_context }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <a href="#"
                                                    class="p-1.5 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-lg transition-colors">
                                                    <i data-lucide="edit" class="w-4.5 h-4.5"></i>
                                                </a>
                                                <button
                                                    @click="confirmRemoveTestData('{{ $data->id }}', '{{ addslashes($data->name) }}')"
                                                    class="p-1.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors">
                                                    <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-700/50">
                                            <div class="flex justify-between items-center">
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate max-w-[70%]">
                                                    @php
                                                        $contentPreview = Str::of($data->content)->limit(60);
                                                    @endphp
                                                    {{ $contentPreview }}
                                                </div>
                                                <button
                                                    @click="previewTestData('{{ $data->id }}', '{{ addslashes($data->name) }}', '{{ $data->format }}')"
                                                    class="inline-flex items-center text-xs font-medium text-blue-600 dark:text-blue-400 group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors">
                                                    Preview
                                                    <i data-lucide="external-link"
                                                        class="w-3.5 h-3.5 ml-1 group-hover:ml-2 transition-all"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-10 px-4">
                                <div
                                    class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-blue-50 to-cyan-100 dark:from-blue-900/20 dark:to-cyan-900/20 text-blue-600 dark:text-blue-400 mb-4">
                                    <i data-lucide="database" class="w-8 h-8"></i>
                                </div>
                                <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No Test Data Yet</h3>
                                <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-6">
                                    Add test data to provide inputs for your test scripts and define expected outcomes.
                                </p>
                                <a href="#"
                                    class="inline-flex items-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                    <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
                                    Create Test Data
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Right Column - Sidebar -->
            <div class="space-y-6">
                <!-- Related Info Card -->
                <div
                    class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center">
                            <i data-lucide="link" class="w-5 h-5 mr-2 text-indigo-500"></i>
                            Related Information
                        </h2>
                    </div>
                    <div class="p-4">
                        <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            <!-- Project -->
                            <li class="py-3 flex justify-between items-center">
                                <div class="flex items-center">
                                    <i data-lucide="folder" class="w-5 h-5 text-indigo-500 mr-3"></i>
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Project</span>
                                </div>
                                <a href="{{ route('dashboard.projects.show', $project->id) }}"
                                    class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                    {{ $project->name }}
                                </a>
                            </li>

                            <!-- Test Suite -->
                            @if ($testSuite)
                                <li class="py-3 flex justify-between items-center">
                                    <div class="flex items-center">
                                        <i data-lucide="folder-open" class="w-5 h-5 text-indigo-500 mr-3"></i>
                                        <span class="text-sm text-zinc-500 dark:text-zinc-400">Test Suite</span>
                                    </div>
                                    <a href="{{ route('dashboard.projects.test-suites.show', [$project->id, $testSuite->id]) }}"
                                        class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                        {{ $testSuite->name }}
                                    </a>
                                </li>
                            @endif

                            <!-- Story -->
                            @if ($testCase->story)
                                <li class="py-3 flex justify-between items-center">
                                    <div class="flex items-center">
                                        <i data-lucide="clipboard-list" class="w-5 h-5 text-indigo-500 mr-3"></i>
                                        <span class="text-sm text-zinc-500 dark:text-zinc-400">Story</span>
                                    </div>
                                    <a href="{{ route('dashboard.stories.show', $testCase->story->id) }}"
                                        class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                        {{ $testCase->story->title }}
                                    </a>
                                </li>
                            @endif

                            <!-- Number of Steps -->
                            <li class="py-3 flex justify-between items-center">
                                <div class="flex items-center">
                                    <i data-lucide="list-checks" class="w-5 h-5 text-indigo-500 mr-3"></i>
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Steps</span>
                                </div>
                                <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ is_array($testCase->steps) ? count($testCase->steps) : 0 }}
                                </span>
                            </li>

                            <!-- Created Date -->
                            <li class="py-3 flex justify-between items-center">
                                <div class="flex items-center">
                                    <i data-lucide="calendar-plus" class="w-5 h-5 text-indigo-500 mr-3"></i>
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Created</span>
                                </div>
                                <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $testCase->created_at->format('M d, Y') }}
                                </span>
                            </li>

                            <!-- Updated Date -->
                            <li class="py-3 flex justify-between items-center">
                                <div class="flex items-center">
                                    <i data-lucide="calendar-clock" class="w-5 h-5 text-indigo-500 mr-3"></i>
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Last Updated</span>
                                </div>
                                <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $testCase->updated_at->format('M d, Y') }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Related Test Cases Card -->
                @if ($relatedCases && $relatedCases->count() > 0)
                    <div
                        class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                        <div
                            class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
                            <h2 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center">
                                <i data-lucide="git-branch" class="w-5 h-5 mr-2 text-indigo-500"></i>
                                Related Test Cases
                            </h2>
                        </div>
                        <div class="p-4">
                            <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($relatedCases as $relatedCase)
                                    <li class="py-3">
                                        <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $relatedCase->id]) }}"
                                            class="block hover:bg-zinc-50 dark:hover:bg-zinc-700/30 -mx-2 px-2 py-1 rounded-lg transition-colors">
                                            <h4 class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $relatedCase->title }}</h4>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 line-clamp-1">
                                                {{ Str::limit($relatedCase->expected_results, 60) }}
                                            </p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Actions Card -->
                <div
                    class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center">
                            <i data-lucide="play" class="w-5 h-5 mr-2 text-indigo-500"></i>
                            Actions
                        </h2>
                    </div>
                    <div class="p-4 space-y-4">
                        <a href="#"
                            class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium text-left text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <span class="flex items-center">
                                <i data-lucide="play-circle" class="w-5 h-5 mr-2"></i>
                                Run Test
                            </span>
                            <i data-lucide="chevron-right" class="w-5 h-5 opacity-75"></i>
                        </a>
                        <a href="#"
                            class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium text-left text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900/30 hover:bg-indigo-200 dark:hover:bg-indigo-800/30 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <span class="flex items-center">
                                <i data-lucide="history" class="w-5 h-5 mr-2"></i>
                                View Execution History
                            </span>
                            <i data-lucide="chevron-right" class="w-5 h-5 opacity-75"></i>
                        </a>
                        <a href="#"
                            class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium text-left text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-700/50 hover:bg-zinc-200 dark:hover:bg-zinc-600/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <span class="flex items-center">
                                <i data-lucide="clipboard-copy" class="w-5 h-5 mr-2"></i>
                                Clone Test Case
                            </span>
                            <i data-lucide="chevron-right" class="w-5 h-5 opacity-75"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <x-modals.delete-confirmation id="delete-test-case-modal" title="Delete Test Case"
            message="Are you sure you want to delete this test case?" itemName="'{{ $testCase->title }}'"
            dangerText="This action cannot be undone. All associated scripts and data relationships will be lost."
            confirmText="Delete Test Case" cancelText="Cancel" x-show="showDeleteModal" />

        <!-- Test Script Delete Modal -->
        <x-modals.delete-confirmation id="delete-test-script-modal" title="Delete Test Script"
            message="Are you sure you want to delete this test script?" itemName="scriptToDelete.name"
            dangerText="This action cannot be undone and the script will be permanently deleted."
            confirmText="Delete Script" cancelText="Cancel" x-show="showDeleteScriptModal" />

        <!-- Test Data Remove Modal -->
        <x-modals.delete-confirmation id="remove-test-data-modal" title="Remove Test Data"
            message="Are you sure you want to remove this test data from the test case?" itemName="testDataToRemove.name"
            dangerText="This will only detach the test data from this test case. The test data will still be available for other test cases."
            confirmText="Remove Test Data" cancelText="Cancel" x-show="showRemoveTestDataModal" />

        <div x-show="showTestDataPreviewModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
                <div class="fixed inset-0 bg-zinc-900/60 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
                    @click="showTestDataPreviewModal = false"></div>
                <div
                    class="relative inline-block w-full max-w-4xl p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-zinc-800 shadow-xl rounded-2xl">
                    <div class="absolute top-0 right-0 pt-5 pr-5">
                        <button type="button" @click="showTestDataPreviewModal = false"
                            class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div>
                        <h3 class="text-xl font-medium text-zinc-900 dark:text-zinc-100 flex items-center">
                            <i data-lucide="database" class="w-6 h-6 text-blue-500 mr-2"></i>
                            <span x-text="currentTestData.name"></span>
                            <span class="ml-2 text-sm font-normal text-zinc-500 dark:text-zinc-400"
                                x-text="'Format: ' + currentTestData.format.toUpperCase()"></span>
                        </h3>

                        <div class="mt-4">
                            <div
                                class="bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 h-96 overflow-auto">
                                <pre class="text-xs font-mono whitespace-pre-wrap break-words text-zinc-800 dark:text-zinc-200"
                                    x-text="currentTestData.content"></pre>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="button" @click="showTestDataPreviewModal = false"
                            class="inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('testCaseDetail', () => ({
                // Test Case deletion
                showDeleteModal: false,
                showRemoveTestDataModal: false,
                showDeleteScriptModal: false,
                isDeleting: false,
                deleteConfirmed: false,

                // Test Case actions
                confirmDelete() {
                    this.showDeleteModal = true;
                },

                // Test Script actions
                scriptToDelete: {
                    id: null,
                    name: ''
                },

                confirmDeleteScript(id, name) {
                    this.scriptToDelete.id = id;
                    this.scriptToDelete.name = name;
                    this.showDeleteScriptModal = true;
                },


                // Test Data actions
                showTestDataPreviewModal: false,
                testDataToRemove: {
                    id: null,
                    name: ''
                },
                currentTestData: {
                    id: null,
                    name: '',
                    format: '',
                    content: ''
                },

                previewTestData(id, name, format) {
                    this.currentTestData.id = id;
                    this.currentTestData.name = name;
                    this.currentTestData.format = format;

                    // Fetch the test data content
                    fetch(`/api/test-data/${id}/content`)
                        .then(response => response.json())
                        .then(data => {
                            this.currentTestData.content = data.content;
                            this.showTestDataPreviewModal = true;
                        })
                        .catch(error => {
                            console.error('Error fetching test data:', error);
                            alert('Failed to load test data content');
                        });
                },

                confirmRemoveTestData(id, name) {
                    this.testDataToRemove.id = id;
                    this.testDataToRemove.name = name;
                    this.showRemoveTestDataModal = true;
                },


                init() {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            }));
        });
    </script>
@endpush
