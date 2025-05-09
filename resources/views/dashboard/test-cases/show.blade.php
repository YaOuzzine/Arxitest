@php
    /**
     * @var \App\Models\Project $project
     * @var \App\Models\TestSuite|null $testSuite
     * @var \App\Models\TestCase $testCase
     * @var \Illuminate\Database\Eloquent\Collection $relatedCases
     */

    $phpItemNameVariable = 'Initial PHP Value for Item Name';

    // Helper to format priority
    $getPriorityBadge = function ($priority) {
        return match (strtolower($priority)) {
            'high'
                => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">High</span>',
            'medium'
                => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">Medium</span>',
            'low'
                => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">Low</span>',
            default
                => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300">Unknown</span>',
        };
    };

    // Helper to format status
    $getStatusBadge = function ($status) {
        return match (strtolower($status)) {
            'active'
                => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Active</span>',
            'draft'
                => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">Draft</span>',
            'deprecated'
                => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">Deprecated</span>',
            'archived'
                => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300">Archived</span>',
            default
                => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300">Unknown</span>',
        };
    };

    // Format the steps
    $steps = is_array($testCase->steps) ? $testCase->steps : json_decode($testCase->steps, true);
    if (!is_array($steps)) {
        $steps = [$testCase->steps];
    }

    // Route URLs
    $editUrl = route('dashboard.projects.test-cases.edit', [$project->id, $testCase->id]);
    $backUrl = $testSuite
        ? route('dashboard.projects.test-suites.test-cases.index', [$project->id, $testSuite->id])
        : route('dashboard.projects.test-cases.index', $project->id);
    $deleteUrl = route('dashboard.projects.test-cases.destroy', [$project->id, $testCase->id]);
    $cloneUrl = route('dashboard.projects.test-cases.clone', [$project->id, $testCase->id]);
@endphp

@extends('layouts.dashboard')

@section('title', $testCase->title)

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects') }}"
            class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">Projects</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.show', $project->id) }}"
            class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">{{ $project->name }}</a>
    </li>
    @if ($testSuite)
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <a href="{{ route('dashboard.projects.test-suites.show', [$project->id, $testSuite->id]) }}"
                class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">{{ $testSuite->name }}</a>
        </li>
    @endif
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Test Case</span>
    </li>
@endsection

@section('content')
    <div x-data="testCaseDetails({
        testCaseId: '{{ $testCase->id }}',
        testCaseTitle: '{{ addslashes($testCase->title) }}',
        projectId: '{{ $project->id }}',
        deleteUrl: '{{ $deleteUrl }}',
        cloneUrl: '{{ $cloneUrl }}',
        csrfToken: '{{ csrf_token() }}'
    })" class="flex flex-col lg:flex-row gap-6">
        <!-- Main Content (2/3 width on larger screens) -->
        <div class="w-full lg:w-2/3 space-y-6">
            <!-- Header Card with Title & Actions -->
            <div
                class="bg-white dark:bg-zinc-800/70 shadow-lg rounded-2xl border border-zinc-100 dark:border-zinc-700/60 backdrop-blur-sm overflow-hidden transition-all duration-200 hover:shadow-xl">
                <div class="p-6 sm:px-8">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="space-y-2">
                            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white tracking-tight">
                                {{ $testCase->title }}
                            </h1>
                            <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <span>ID: <span class="font-mono">{{ Str::limit($testCase->id, 8, '') }}</span></span>
                                <span class="w-1 h-1 rounded-full bg-zinc-300 dark:bg-zinc-600"></span>
                                <span>Last updated: {{ $testCase->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-4 lg:mt-0">
                            <a href="{{ $editUrl }}"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-800/40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                <i data-lucide="pencil" class="w-4 h-4 mr-2"></i>
                                Edit
                            </a>
                            <button type="button" @click="openCloneModal = true"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-800/40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                                <i data-lucide="copy" class="w-4 h-4 mr-2"></i>
                                Clone
                            </button>
                            <button type="button" @click="openDeleteTestCaseModal"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-800/40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
                                Delete
                            </button>
                            <a href="{{ $backUrl }}"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 transition-colors">
                                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                                Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Info Card -->
            <div
                class="bg-white dark:bg-zinc-800/70 shadow-lg rounded-2xl border border-zinc-100 dark:border-zinc-700/60 backdrop-blur-sm overflow-hidden transition-all duration-200 hover:shadow-xl">
                <div class="p-6 sm:p-8 space-y-6">
                    <!-- Description -->
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center mb-2">
                            <i data-lucide="file-text" class="w-5 h-5 mr-2 text-zinc-500 dark:text-zinc-400"></i>
                            Description
                        </h2>
                        <div
                            class="prose dark:prose-invert max-w-full prose-p:text-zinc-700 dark:prose-p:text-zinc-300 prose-headings:text-zinc-900 dark:prose-headings:text-zinc-100">
                            {{ $testCase->description ?: 'No description provided.' }}
                        </div>
                    </div>

                    <!-- Steps -->
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center mb-3">
                            <i data-lucide="list-checks" class="w-5 h-5 mr-2 text-zinc-500 dark:text-zinc-400"></i>
                            Test Steps <span
                                class="ml-2 text-sm font-normal text-zinc-500 dark:text-zinc-400">({{ count($steps) }}
                                steps)</span>
                        </h2>
                        <div class="space-y-3">
                            @foreach ($steps as $index => $step)
                                <div class="flex items-start gap-3 bg-zinc-50 dark:bg-zinc-700/30 p-3 rounded-lg">
                                    <div
                                        class="flex-shrink-0 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-800 dark:text-indigo-300 font-medium rounded-full w-6 h-6 flex items-center justify-center text-sm">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="flex-grow text-zinc-700 dark:text-zinc-300">
                                        {{ $step }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Expected Results -->
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center mb-2">
                            <i data-lucide="target" class="w-5 h-5 mr-2 text-zinc-500 dark:text-zinc-400"></i>
                            Expected Results
                        </h2>
                        <div class="prose dark:prose-invert max-w-full prose-p:text-zinc-700 dark:prose-p:text-zinc-300">
                            {{ $testCase->expected_results ?: 'No expected results defined.' }}
                        </div>
                    </div>

                    <!-- Meta Info -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-zinc-50 dark:bg-zinc-700/30 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Priority & Status</h3>
                            <div class="flex items-center gap-3">
                                <div>{!! $getPriorityBadge($testCase->priority) !!}</div>
                                <div>{!! $getStatusBadge($testCase->status) !!}</div>
                            </div>
                        </div>

                        <div class="bg-zinc-50 dark:bg-zinc-700/30 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Associated Story</h3>
                            @if ($testCase->story)
                                <a href="{{ route('dashboard.stories.show', $testCase->story->id) }}"
                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                                    {{ $testCase->story->title }}
                                </a>
                            @else
                                <span class="text-zinc-500 dark:text-zinc-400">No story associated</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Scripts Section -->
            <div
                class="bg-white dark:bg-zinc-800/70 shadow-lg rounded-2xl border border-zinc-100 dark:border-zinc-700/60 backdrop-blur-sm overflow-hidden transition-all duration-200 hover:shadow-xl">
                <div
                    class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-700/60 bg-gradient-to-r from-zinc-50/50 to-indigo-50/20 dark:from-zinc-800/50 dark:to-indigo-900/10 flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            Test Scripts
                        </h2>
                        <span
                            class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">
                            {{ $testCase->testScripts->count() }}
                        </span>
                    </div>
                    <a href="{{ route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id]) }}"
                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-sm font-medium">
                        Manage Scripts
                    </a>
                </div>
                <div x-data="{ open: true }" class="border-b border-zinc-100 dark:border-zinc-700/60 last:border-b-0">
                    <div @click="open = !open"
                        class="px-6 py-4 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700/20 flex justify-between items-center">
                        <h3 class="text-base font-medium text-zinc-900 dark:text-white">
                            Test Scripts
                        </h3>
                        <i x-bind:data-lucide="open ? 'chevron-up' : 'chevron-down'" class="w-5 h-5 text-zinc-400"></i>
                    </div>
                    <div x-show="open" x-transition class="px-6 pb-4">
                        @if ($testCase->testScripts->isEmpty())
                            <div class="text-center py-8 px-4">
                                <div
                                    class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800 mb-4">
                                    <i data-lucide="file-code" class="w-8 h-8 text-zinc-400 dark:text-zinc-500"></i>
                                </div>
                                <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No Test Scripts Yet</h3>
                                <p class="text-zinc-500 dark:text-zinc-400 mb-4">Create automation scripts to execute this
                                    test case.</p>
                                <a href="{{ route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id]) }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                    Add Test Script
                                </a>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                Name</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                Framework</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                Updated</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach ($testCase->testScripts as $script)
                                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                        {{ $script->name }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                        {{ $script->framework_type }}
                                                    </span>
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ $script->updated_at->diffForHumans() }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div class="flex justify-end space-x-2">
                                                        <a href="{{ route('dashboard.projects.test-cases.scripts.show', [$project->id, $testCase->id, $script->id]) }}"
                                                            class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                                        </a>
                                                        <form
                                                            action="{{ route('dashboard.projects.test-cases.scripts.destroy', [$project->id, $testCase->id, $script->id]) }}"
                                                            method="POST" class="inline-block">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
                                                                onclick="return confirm('Are you sure you want to delete this script?')">
                                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Test Data Section -->
            <div
                class="bg-white dark:bg-zinc-800/70 shadow-lg rounded-2xl border border-zinc-100 dark:border-zinc-700/60 backdrop-blur-sm overflow-hidden transition-all duration-200 hover:shadow-xl">
                <div
                    class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-700/60 bg-gradient-to-r from-zinc-50/50 to-emerald-50/20 dark:from-zinc-800/50 dark:to-emerald-900/10 flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            Test Data
                        </h2>
                        <span
                            class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                            {{ $testCase->testData->count() }}
                        </span>
                    </div>
                    <a href="{{ route('dashboard.projects.test-cases.data.index', [$project->id, $testCase->id]) }}"
                        class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 text-sm font-medium">
                        Manage Test Data
                    </a>
                </div>
                <div x-data="{ open: true }" class="border-b border-zinc-100 dark:border-zinc-700/60 last:border-b-0">
                    <div @click="open = !open"
                        class="px-6 py-4 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700/20 flex justify-between items-center">
                        <h3 class="text-base font-medium text-zinc-900 dark:text-white">
                            Test Data
                        </h3>
                        <i x-bind:data-lucide="open ? 'chevron-up' : 'chevron-down'" class="w-5 h-5 text-zinc-400"></i>
                    </div>
                    <div x-show="open" x-transition class="px-6 pb-4">
                        @if ($testCase->testData->isEmpty())
                            <div class="text-center py-8 px-4">
                                <div
                                    class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800 mb-4">
                                    <i data-lucide="database" class="w-8 h-8 text-zinc-400 dark:text-zinc-500"></i>
                                </div>
                                <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No Test Data Yet</h3>
                                <p class="text-zinc-500 dark:text-zinc-400 mb-4">Add test data to use with this test case.
                                </p>
                                <a href="{{ route('dashboard.projects.test-cases.data.index', [$project->id, $testCase->id]) }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                    Add Test Data
                                </a>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                Name</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                Format</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                Usage</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach ($testCase->testData as $data)
                                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                        {{ $data->name }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $data->format === 'json' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : '' }}
                                                    {{ $data->format === 'csv' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                                    {{ $data->format === 'xml' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300' : '' }}
                                                    {{ $data->format === 'plain' ? 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300' : '' }}
                                                    {{ !in_array($data->format, ['json', 'csv', 'xml', 'plain']) ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : '' }}">
                                                        {{ strtoupper($data->format) }}
                                                    </span>
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ $data->pivot->usage_context ?? 'General' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div class="flex justify-end space-x-2">
                                                        <a href="{{ route('dashboard.projects.test-cases.data.show', ['project' => $project->id, 'test_case' => $testCase->id, 'test_data' => $data->id]) }}"
                                                            class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300"
                                                            title="Preview Test Data">
                                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                                        </a>

                                                        <form
                                                            action="{{ route('dashboard.projects.test-cases.data.detach', [$project->id, $testCase->id, $data->id]) }}"
                                                            method="POST" class="inline-block">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button"
                                                                @click="openDetachDataModal('{{ $data->id }}', '{{ addslashes($data->name) }}')"
                                                                class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                                                <i data-lucide="unlink" class="w-5 h-5"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar (1/3 width on larger screens) -->
        <div class="w-full lg:w-1/3 space-y-6">
            <!-- Project & Suite Info -->
            <div
                class="bg-white dark:bg-zinc-800/70 shadow-lg rounded-2xl border border-zinc-100 dark:border-zinc-700/60 backdrop-blur-sm overflow-hidden transition-all duration-200 hover:shadow-xl">
                <div
                    class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-700/60 bg-gradient-to-r from-zinc-50/50 to-zinc-100/20 dark:from-zinc-800/50 dark:to-zinc-700/20">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                        Related Entities
                    </h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Project</h3>
                        <a href="{{ route('dashboard.projects.show', $project->id) }}"
                            class="block text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                            {{ $project->name }}
                        </a>
                    </div>

                    @if ($testSuite)
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Test Suite</h3>
                            <a href="{{ route('dashboard.projects.test-suites.show', [$project->id, $testSuite->id]) }}"
                                class="block text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                                {{ $testSuite->name }}
                            </a>
                        </div>
                    @endif

                    @if ($testCase->story)
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Story</h3>
                            <a href="{{ route('dashboard.stories.show', $testCase->story->id) }}"
                                class="block text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                                {{ $testCase->story->title }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Metadata Card -->
            <div
                class="bg-white dark:bg-zinc-800/70 shadow-lg rounded-2xl border border-zinc-100 dark:border-zinc-700/60 backdrop-blur-sm overflow-hidden transition-all duration-200 hover:shadow-xl">
                <div
                    class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-700/60 bg-gradient-to-r from-zinc-50/50 to-zinc-100/20 dark:from-zinc-800/50 dark:to-zinc-700/20">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                        Metadata
                    </h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">ID</span>
                            <span class="text-sm text-zinc-900 dark:text-white font-mono">{{ $testCase->id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Test Steps</span>
                            <span class="text-sm text-zinc-900 dark:text-white">{{ count($steps) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Status</span>
                            <span class="text-sm text-zinc-900 dark:text-white">{!! $getStatusBadge($testCase->status) !!}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Priority</span>
                            <span class="text-sm text-zinc-900 dark:text-white">{!! $getPriorityBadge($testCase->priority) !!}</span>
                        </div>
                    </div>

                    <hr class="border-zinc-100 dark:border-zinc-700/60">

                    <div class="grid grid-cols-1 gap-4">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Created At</span>
                            <span
                                class="text-sm text-zinc-900 dark:text-white">{{ $testCase->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Last Updated</span>
                            <span
                                class="text-sm text-zinc-900 dark:text-white">{{ $testCase->updated_at->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>

                    @if (!empty($testCase->tags) && (is_array($testCase->tags) || is_object($testCase->tags)))
                        <hr class="border-zinc-100 dark:border-zinc-700/60">

                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Tags</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach (is_array($testCase->tags) ? $testCase->tags : (array) $testCase->tags as $tag)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                        {{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Related Test Cases -->
            @if ($relatedCases->isNotEmpty())
                <div
                    class="bg-white dark:bg-zinc-800/70 shadow-lg rounded-2xl border border-zinc-100 dark:border-zinc-700/60 backdrop-blur-sm overflow-hidden transition-all duration-200 hover:shadow-xl">
                    <div
                        class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-700/60 bg-gradient-to-r from-zinc-50/50 to-zinc-100/20 dark:from-zinc-800/50 dark:to-zinc-700/20">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            Related Test Cases
                        </h2>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-3">
                            @foreach ($relatedCases as $relatedCase)
                                <li>
                                    <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $relatedCase->id]) }}"
                                        class="block p-3 rounded-lg border border-zinc-100 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors">
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-white mb-1">
                                            {{ $relatedCase->title }}</h3>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                {!! $getPriorityBadge($relatedCase->priority) !!}
                                                {!! $getStatusBadge($relatedCase->status) !!}
                                            </div>
                                            <span
                                                class="text-xs text-zinc-500 dark:text-zinc-400">{{ $relatedCase->updated_at->diffForHumans() }}</span>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        <!-- Modals -->
        <!-- Delete Confirmation Modal -->
        <x-modals.delete-confirmation title="Delete Test Case" message="Are you sure you want to delete the test case"
            itemName="itemName"
            dangerText="This will permanently delete this test case and all its associations. This action cannot be undone."
            confirmText="Delete Test Case" />
        <x-modals.delete-confirmation title="Detach Test Data" message="Are you sure you want to detach the test data"
            itemName="itemName"
            dangerText="This will remove the association between this test data and the test case. The test data itself will not be deleted."
            confirmText="Detach Test Data" />
        <!-- Clone Modal -->
        <x-modals.clone-confirmation title="Clone Test Case"
            message="Create a copy of this test case with the following options:" itemName="testCaseTitle"
            confirmText="Clone Test Case" />
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('testCaseDetails', (config) => ({
                // Configuration passed from Blade
                testCaseId: config.testCaseId,
                testCaseTitle: config.testCaseTitle,
                projectId: config.projectId,
                deleteUrl: config.deleteUrl, // URL for deleting this test case
                cloneUrl: config.cloneUrl, // URL for cloning this test case
                csrfToken: config.csrfToken,

                // State for the shared delete/detach confirmation modal
                showDeleteModal: false, // Controls visibility of the confirmation modal
                deleteConfirmed: false, // Tracks if the confirmation checkbox is checked
                isDeleting: false, // Tracks if a delete/detach operation is in progress
                itemName: '', // Name of the item being acted upon (for display in modal)
                requireConfirmation: true, // If confirmation checkbox is needed for the action

                // Internal state for the current confirmation action
                _currentActionType: null, // 'deleteTestCase' or 'detachData'
                _currentActionConfig: {}, // Holds URL for the current action

                // State for the clone modal
                showCloneModal: false, // Controls visibility of the clone modal
                cloneTitle: '', // Suggested title for the cloned test case
                cloneOptions: { // Options for what to include in the clone
                    scripts: true,
                    testData: true
                },
                isCloning: false, // Tracks if a cloning operation is in progress
                cloneFormAction: config.cloneUrl, // Action URL for the clone form

                // Initializes the component
                init() {
                    this.cloneTitle = `${this.testCaseTitle} (Copy)`; // Set default title for cloning

                    // Reset confirmation checkbox when delete modal is closed
                    this.$watch('showDeleteModal', (value) => {
                        if (!value) {
                            this.deleteConfirmed = false;
                        }
                    });

                    // Re-initialize Lucide icons if they are rendered by Alpine
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });
                },

                // Opens the confirmation modal for deleting the current test case
                openDeleteTestCaseModal() {
                    this._currentActionType = 'deleteTestCase';
                    this.itemName = this.testCaseTitle;
                    this._currentActionConfig = {
                        url: this.deleteUrl
                    };
                    this.deleteConfirmed = false;
                    this.isDeleting = false;
                    this.showDeleteModal = true;
                },

                // Opens the confirmation modal for detaching a specific test data item
                openDetachDataModal(dataId, dataName) {
                    this._currentActionType = 'detachData';
                    this.itemName = dataName;
                    this._currentActionConfig = {
                        url: `/dashboard/projects/${this.projectId}/test-cases/${this.testCaseId}/data/${dataId}`,
                    };
                    this.deleteConfirmed = false;
                    this.isDeleting = false;
                    this.showDeleteModal = true;
                },

                // Closes the shared confirmation modal
                closeDeleteModal() {
                    if (!this.isDeleting) { // Prevent closing if an operation is in progress
                        this.showDeleteModal = false;
                    }
                },

                // Handles the confirmation of a delete or detach action
                confirmDelete() {
                    if (this.isDeleting) return; // Prevent multiple submissions

                    this.isDeleting = true;

                    // Dynamically create and submit a form for the DELETE request
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = this._currentActionConfig.url;

                    const csrfField = document.createElement('input');
                    csrfField.type = 'hidden';
                    csrfField.name = '_token';
                    csrfField.value = this.csrfToken;
                    form.appendChild(csrfField);

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';
                    form.appendChild(methodField);

                    document.body.appendChild(form);
                    form.submit();
                    // Page is expected to reload or redirect, so no need to reset isDeleting here
                },

                // Opens the clone modal
                openCloneModal() {
                    this.showCloneModal = true;
                    this.isCloning = false; // Reset cloning state
                },

                // Closes the clone modal
                closeCloneModal() {
                    if (!this.isCloning) { // Prevent closing if cloning is in progress
                        this.showCloneModal = false;
                    }
                },

                // Handles the confirmation of a clone action
                async confirmClone() {
                    if (this.isCloning) return;
                    this.isCloning = true;

                    try {
                        const response = await fetch(this
                        .cloneUrl, { // Uses cloneUrl directly from config
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                title: this.cloneTitle,
                                clone_scripts: this.cloneOptions.scripts,
                                clone_test_data: this.cloneOptions.testData,
                            })
                        });

                        const result = await response.json();

                        if (response.ok) {
                            if (result.redirect_url) {
                                window.location.href = result.redirect_url; // Redirect if provided
                            } else {
                                this.closeCloneModal();
                                location.reload(); // Otherwise, reload the page
                            }
                        } else {
                            console.error('Clone failed:', result);
                            alert(
                                `Clone failed: ${result.message || response.statusText || 'Unknown error'}`);
                            this.isCloning = false; // Reset loading state on failure
                        }
                    } catch (error) {
                        console.error('Error cloning test case:', error);
                        alert('An error occurred while cloning the test case.');
                        this.isCloning = false; // Reset loading state on exception
                    }
                }
            }));
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Custom scrollbar for code blocks */
        pre {
            scrollbar-width: thin;
            scrollbar-color: rgba(161, 161, 170, 0.5) rgba(63, 63, 70, 0.1);
            max-height: 400px;
            overflow-y: auto;
        }

        pre::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        pre::-webkit-scrollbar-track {
            background: rgba(63, 63, 70, 0.1);
            border-radius: 3px;
        }

        pre::-webkit-scrollbar-thumb {
            background-color: rgba(161, 161, 170, 0.5);
            border-radius: 3px;
        }

        /* Tooltip styles */
        .tooltip {
            position: relative;
        }

        .tooltip:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            z-index: 10;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            padding: 0.5rem;
            background: rgba(15, 23, 42, 0.9);
            color: white;
            border-radius: 0.375rem;
            white-space: nowrap;
            font-size: 0.75rem;
            pointer-events: none;
        }
    </style>
@endpush
