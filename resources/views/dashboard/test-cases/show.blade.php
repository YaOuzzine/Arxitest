@php
    /**
     * @var \App\Models\Project $project
     * @var \App\Models\TestSuite $testSuite
     * @var \App\Models\TestCase $testCase
     * @var \Illuminate\Database\Eloquent\Collection|\App\Models\TestScript[] $testScripts
     * @var \Illuminate\Database\Eloquent\Collection|\App\Models\TestData[] $testData
     */
    $pageTitle = $testCase->title;

    // Load relationships if not already loaded
    $testCase->loadMissing(['testSuite', 'testScripts', 'testData', 'story']);
    $testScripts = $testCase->testScripts ?? collect();
    $testData = $testCase->testData ?? collect();
    $testSuite = $testCase->testSuite;
    $story = $testCase->story; // Add story relationship

    // Parse steps as array (handle both array and JSON string)
    $steps = $testCase->steps ?? [];
    if (is_string($steps)) {
        $decodedSteps = json_decode($steps, true);
        $steps = is_array($decodedSteps) ? $decodedSteps : [];
    } elseif (!is_array($steps)) {
        $steps = [];
    }

    // Parse tags as array
    $tags = $testCase->tags ?? [];
    if (is_string($tags)) {
        $decodedTags = json_decode($tags, true);
        $tags = is_array($decodedTags) ? $decodedTags : [];
    } elseif (!is_array($tags)) {
        $tags = [];
    }

    // Status & priority badges
    $statusColors = [
        'draft' =>
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 border-yellow-200 dark:border-yellow-800/40',
        'active' =>
            'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 border-green-200 dark:border-green-800/40',
        'deprecated' =>
            'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300 border-orange-200 dark:border-orange-800/40',
        'archived' =>
            'bg-zinc-100 text-zinc-800 dark:bg-zinc-700/40 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700/40',
    ];
    $statusColor = $statusColors[$testCase->status] ?? $statusColors['draft'];

    $priorityColors = [
        'low' =>
            'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border-blue-200 dark:border-blue-800/40',
        'medium' =>
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 border-yellow-200 dark:border-yellow-800/40',
        'high' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 border-red-200 dark:border-red-800/40',
    ];
    $priorityColor = $priorityColors[$testCase->priority] ?? $priorityColors['medium'];

    // Framework language mapping for PrismJS
    $frameworkLanguages = [
        'selenium-python' => 'python',
        'cypress' => 'javascript',
        'other' => 'markup',
    ];

    // Data format language mapping for PrismJS
    $dataFormatLanguages = [
        'json' => 'json',
        'csv' => 'csv',
        'xml' => 'xml',
        'plain' => 'plaintext',
        'other' => 'markup',
    ];
@endphp

@extends('layouts.dashboard')

@section('title', $pageTitle)

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
        <span class="text-zinc-700 dark:text-zinc-300">{{ Str::limit($testCase->title, 30) }}</span>
    </li>
@endsection

@section('content')
    <div class="space-y-8" x-data="testCaseView">
        <!-- Header with Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">{{ $testCase->title }}</h1>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 text-xs font-medium rounded-full border {{ $statusColor }}">
                        {{ ucfirst($testCase->status) }}
                    </span>
                    <span class="px-3 py-1 text-xs font-medium rounded-full border {{ $priorityColor }}">
                        {{ ucfirst($testCase->priority) }} Priority
                    </span>
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        <i data-lucide="clock" class="inline-block w-3.5 h-3.5 mr-1"></i>
                        Updated {{ $testCase->updated_at->diffForHumans() }}
                    </span>
                </div>
            </div>

            <div class="flex flex-shrink-0 gap-2">
                <a href="{{ route('dashboard.projects.test-cases.edit', [$project->id, $testCase->id]) }}"
                    class="btn-secondary">
                    <i data-lucide="edit-3" class="w-4 h-4 mr-1"></i> Edit
                </a>
                <button @click="confirmDelete = true" class="btn-danger">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Delete
                </button>
            </div>
        </div>

        <!-- Main Content Tabs -->
        <div
            class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <!-- Tab Navigation -->
            <div class="border-b border-zinc-200 dark:border-zinc-700">
                <nav class="flex overflow-x-auto" aria-label="Tabs">
                    <button @click="setActiveTab('details')"
                        :class="{
                            'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400': activeTab === 'details',
                            'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600': activeTab !== 'details'
                        }"
                        class="px-4 py-4 font-medium text-sm border-b-2 whitespace-nowrap">
                        <i data-lucide="clipboard-list" class="inline-block w-4 h-4 mr-1"></i>
                        Test Case Details
                    </button>
                    <button @click="setActiveTab('scripts')"
                        :class="{
                            'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400': activeTab === 'scripts',
                            'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600': activeTab !== 'scripts'
                        }"
                        class="px-4 py-4 font-medium text-sm border-b-2 whitespace-nowrap">
                        <i data-lucide="file-code" class="inline-block w-4 h-4 mr-1"></i>
                        Test Scripts <span
                            class="ml-1 px-2 py-0.5 rounded-full text-xs bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300">{{ $testScripts->count() }}</span>
                    </button>
                    <button @click="setActiveTab('testdata')"
                        :class="{
                            'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400': activeTab === 'testdata',
                            'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600': activeTab !== 'testdata'
                        }"
                        class="px-4 py-4 font-medium text-sm border-b-2 whitespace-nowrap">
                        <i data-lucide="database" class="inline-block w-4 h-4 mr-1"></i>
                        Test Data <span
                            class="ml-1 px-2 py-0.5 rounded-full text-xs bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300">{{ $testData->count() }}</span>
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Details Tab -->
                <div x-show="activeTab === 'details'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left Column: Description & Steps -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Story Information (NEW) -->
                            @if ($story)
                                <div
                                    class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-4 border border-indigo-200 dark:border-indigo-800/40 mb-6">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <i data-lucide="book-open"
                                                class="h-5 w-5 text-indigo-600 dark:text-indigo-400 mt-1"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-indigo-800 dark:text-indigo-200">Related
                                                Story</h3>
                                            <div class="mt-1">
                                                <a href="{{ route('dashboard.stories.show', $story->id) }}"
                                                    class="text-base font-medium text-indigo-700 dark:text-indigo-300 hover:text-indigo-900 dark:hover:text-indigo-100">
                                                    {{ $story->title }}
                                                </a>
                                                <p class="mt-1 text-sm text-indigo-700 dark:text-indigo-300">
                                                    {{ Str::limit($story->description, 150) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Description -->
                            <div>
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Description</h3>
                                <div
                                    class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                    <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-line">
                                        {{ $testCase->description ?: 'No description provided.' }}</p>
                                </div>
                            </div>

                            <!-- Steps -->
                            <div>
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Test Steps</h3>
                                <div
                                    class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                    @if (count($steps) > 0)
                                        <ol class="list-decimal list-inside space-y-2">
                                            @foreach ($steps as $index => $step)
                                                <li class="text-zinc-700 dark:text-zinc-300">
                                                    <span class="font-medium">{{ $index + 1 }}.</span>
                                                    {{ $step }}
                                                </li>
                                            @endforeach
                                        </ol>
                                    @else
                                        <p class="text-zinc-500 dark:text-zinc-400 italic">No steps defined.</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Expected Results -->
                            <div>
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Expected Results</h3>
                                <div
                                    class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                    <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-line">
                                        {{ $testCase->expected_results }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Metadata -->
                        <div class="space-y-6">
                            <!-- Test Suite Info -->
                            @if ($testSuite)
                                <div
                                    class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                                    <div
                                        class="px-4 py-3 bg-zinc-50 dark:bg-zinc-700/30 border-b border-zinc-200 dark:border-zinc-700">
                                        <h3 class="font-medium text-zinc-900 dark:text-white">Test Suite</h3>
                                    </div>
                                    <div class="p-4">
                                        <a href="{{ route('dashboard.projects.test-suites.show', [$project->id, $testSuite->id]) }}"
                                            class="flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                                            <i data-lucide="layers" class="w-4 h-4 mr-2"></i>
                                            <span>{{ $testSuite->name }}</span>
                                        </a>
                                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ Str::limit($testSuite->description, 100) ?: 'No description.' }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <!-- Tags -->
                            <div
                                class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                                <div
                                    class="px-4 py-3 bg-zinc-50 dark:bg-zinc-700/30 border-b border-zinc-200 dark:border-zinc-700">
                                    <h3 class="font-medium text-zinc-900 dark:text-white">Tags</h3>
                                </div>
                                <div class="p-4">
                                    @if (count($tags) > 0)
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($tags as $tag)
                                                <span
                                                    class="px-2 py-1 text-xs font-medium rounded-md bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/30">
                                                    {{ $tag }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-zinc-500 dark:text-zinc-400 italic">No tags defined.</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Creation Info -->
                            <div
                                class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                                <div
                                    class="px-4 py-3 bg-zinc-50 dark:bg-zinc-700/30 border-b border-zinc-200 dark:border-zinc-700">
                                    <h3 class="font-medium text-zinc-900 dark:text-white">Creation Info</h3>
                                </div>
                                <div class="p-4 space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500 dark:text-zinc-400">Created</span>
                                        <span
                                            class="text-zinc-800 dark:text-zinc-200">{{ $testCase->created_at->format('M d, Y') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500 dark:text-zinc-400">Last Updated</span>
                                        <span
                                            class="text-zinc-800 dark:text-zinc-200">{{ $testCase->updated_at->format('M d, Y') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500 dark:text-zinc-400">ID</span>
                                        <span
                                            class="text-zinc-800 dark:text-zinc-200 font-mono text-xs">{{ $testCase->id }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Scripts Tab -->
                <div x-show="activeTab === 'scripts'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Test Scripts</h3>
                        <div>
                            <button @click="openScriptModal()" class="btn-primary">
                                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Create Test Script
                            </button>
                        </div>
                    </div>

                    <!-- Script List -->
                    @if ($testScripts->count() > 0)
                        <div class="space-y-4">
                            @foreach ($testScripts as $script)
                                @php
                                    $scriptLanguage = $frameworkLanguages[$script->framework_type] ?? 'markup';
                                    $isAiGenerated =
                                        isset($script->metadata['created_through']) &&
                                        $script->metadata['created_through'] === 'ai';
                                @endphp
                                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200"
                                    x-data="{ expanded: expandedScript === '{{ $script->id }}' }" :class="{ 'shadow-md': expanded }">
                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-700/30 gap-3">
                                        <div>
                                            <h4 class="text-md font-medium text-zinc-900 dark:text-white mb-1">
                                                {{ $script->name }}</h4>
                                            <div
                                                class="flex flex-wrap items-center text-sm text-zinc-500 dark:text-zinc-400 gap-x-3 gap-y-1">
                                                <span class="flex items-center">
                                                    <i data-lucide="code" class="w-3.5 h-3.5 mr-1"></i>
                                                    {{ ucfirst(str_replace('-', ' ', $script->framework_type)) }}
                                                </span>
                                                <span class="flex items-center">
                                                    <i data-lucide="clock" class="w-3.5 h-3.5 mr-1"></i>
                                                    {{ $script->created_at->diffForHumans() }}
                                                </span>
                                                @if ($isAiGenerated)
                                                    <span
                                                        class="px-2 py-0.5 text-xs font-medium rounded-md bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/30">
                                                        AI Generated
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <button @click="toggleScript('{{ $script->id }}'); expanded = !expanded"
                                                class="px-2 py-1 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded">
                                                <span x-show="!expanded">View Code</span>
                                                <span x-show="expanded">Hide Code</span>
                                            </button>
                                            <form method="POST"
                                                action="{{ route('dashboard.projects.test-cases.scripts.destroy', [$project->id, $testCase->id, $script->id]) }}"
                                                onsubmit="return confirm('Are you sure you want to delete this script?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="px-2 py-1 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div x-show="expandedScript === '{{ $script->id }}'" x-collapse>
                                        <div class="relative p-4 bg-zinc-50 dark:bg-zinc-900">
                                            <button
                                                @click="copyToClipboard($el.parentElement.querySelector('code').innerText, 'Script')"
                                                class="absolute top-2 right-2 px-2 py-1 text-xs text-zinc-500 dark:text-zinc-400 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 rounded">
                                                Copy
                                            </button>
                                            <pre class="language-{{ $scriptLanguage }} max-h-96 overflow-y-auto !m-0 !p-0 !bg-transparent"><code>{{ $script->script_content }}</code></pre>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div
                            class="bg-zinc-50 dark:bg-zinc-700/30 border border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg p-8 text-center">
                            <div
                                class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400 mb-3">
                                <i data-lucide="file-code" class="w-6 h-6"></i>
                            </div>
                            <h4 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No Test Scripts Yet</h4>
                            <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-4">
                                Test scripts help automate this test case. Add one manually or generate with AI assistance.
                            </p>
                            <div class="flex justify-center">
                                <button @click="openScriptModal()" class="btn-primary">
                                    <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Create Test Script
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Test Data Tab -->
                <div x-show="activeTab === 'testdata'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Test Data</h3>
                        <div>
                            <button @click="openDataModal()" class="btn-primary">
                                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Create Test Data
                            </button>
                        </div>
                    </div>

                    <!-- Data List -->
                    @if ($testData->count() > 0)
                        <div class="space-y-4">
                            @foreach ($testData as $data)
                                @php
                                    $dataLanguage = $dataFormatLanguages[$data->format] ?? 'markup';
                                    $isDataAiGenerated =
                                        isset($data->metadata['created_through']) &&
                                        $data->metadata['created_through'] === 'ai';
                                @endphp
                                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200"
                                    x-data="{ expanded: expandedData === '{{ $data->id }}' }" :class="{ 'shadow-md': expanded }">
                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-700/30 gap-3">
                                        <div>
                                            <h4 class="text-md font-medium text-zinc-900 dark:text-white mb-1">
                                                {{ $data->name }}</h4>
                                            <div
                                                class="flex flex-wrap items-center text-sm text-zinc-500 dark:text-zinc-400 gap-x-3 gap-y-1">
                                                <span
                                                    class="px-2 py-0.5 text-xs font-medium rounded-md bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800/30">
                                                    {{ strtoupper($data->format) }}
                                                </span>
                                                @if ($data->is_sensitive)
                                                    <span
                                                        class="px-2 py-0.5 text-xs font-medium rounded-md bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800/30">
                                                        Sensitive
                                                    </span>
                                                @endif
                                                <span class="flex items-center">
                                                    <i data-lucide="clock" class="w-3.5 h-3.5 mr-1"></i>
                                                    {{ $data->created_at->diffForHumans() }}
                                                </span>
                                                @if ($isDataAiGenerated)
                                                    <span
                                                        class="px-2 py-0.5 text-xs font-medium rounded-md bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/30">
                                                        AI Generated
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <button @click="toggleData('{{ $data->id }}'); expanded = !expanded"
                                                class="px-2 py-1 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded">
                                                <span x-show="!expanded">View Data</span>
                                                <span x-show="expanded">Hide Data</span>
                                            </button>
                                            <form method="POST"
                                                action="{{ route('dashboard.projects.test-cases.data.detach', [$project->id, $testCase->id, $data->id]) }}"
                                                onsubmit="return confirm('Are you sure you want to remove this test data from this test case? The data itself will not be deleted.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="px-2 py-1 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded">
                                                    Remove
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div x-show="expandedData === '{{ $data->id }}'" x-collapse>
                                        <div class="relative p-4 bg-zinc-50 dark:bg-zinc-900">
                                            <button
                                                @click="copyToClipboard($el.parentElement.querySelector('code').innerText, 'Data')"
                                                class="absolute top-2 right-2 px-2 py-1 text-xs text-zinc-500 dark:text-zinc-400 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 rounded">
                                                Copy
                                            </button>
                                            <pre class="language-{{ $dataLanguage }} max-h-96 overflow-y-auto !m-0 !p-0 !bg-transparent"><code>{{ $data->content }}</code></pre>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div
                            class="bg-zinc-50 dark:bg-zinc-700/30 border border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg p-8 text-center">
                            <div
                                class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400 mb-3">
                                <i data-lucide="database" class="w-6 h-6"></i>
                            </div>
                            <h4 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No Test Data Yet</h4>
                            <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-4">
                                Test data provides input values for this test case. Add data manually or generate with AI
                                assistance.
                            </p>
                            <div class="flex justify-center">
                                <button @click="openDataModal()" class="btn-primary">
                                    <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Create Test Data
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Modals -->

        <!-- Create Script Modal - Improved & Consolidated -->
        <div x-cloak x-show="showScriptModal" @keydown.escape.window="showScriptModal = false"
            class="fixed inset-0 overflow-y-auto z-50" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-zinc-900/70 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
                @click="showScriptModal = false"></div>
            <!-- Modal Panel -->
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative w-full max-w-6xl bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-8">
                    <!-- Header -->
                    <div
                        class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-indigo-900 dark:text-indigo-100 flex items-center">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="code" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i>
                                    <span>Create Test Script</span>
                                </div>
                            </h3>
                            <button @click="showScriptModal = false"
                                class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Content -->
                    <div class="p-0">
                        <div class="grid grid-cols-1 lg:grid-cols-3 h-[calc(100vh-12rem)] max-h-[800px]">
                            <!-- Left Column: Context & Options -->
                            <div
                                class="lg:col-span-1 p-6 border-r border-zinc-200 dark:border-zinc-700/70 bg-zinc-50 dark:bg-zinc-800/50 overflow-y-auto">
                                <div class="space-y-6">
                                    <!-- Creation Mode Tabs -->
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Creation
                                            Mode</label>
                                        <div
                                            class="flex rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                                            <button @click="scriptCreationMode = 'ai'"
                                                :class="{
                                                    'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium': scriptCreationMode === 'ai',
                                                    'bg-white dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-700/50': scriptCreationMode !== 'ai'
                                                }"
                                                class="flex-1 py-2.5 px-3 text-sm transition-colors">
                                                <i data-lucide="sparkles" class="w-4 h-4 inline-block mr-1"></i>
                                                AI-Assisted
                                            </button>
                                            <button @click="scriptCreationMode = 'manual'"
                                                :class="{
                                                    'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium': scriptCreationMode === 'manual',
                                                    'bg-white dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-700/50': scriptCreationMode !== 'manual'
                                                }"
                                                class="flex-1 py-2.5 px-3 text-sm transition-colors">
                                                <i data-lucide="edit-3" class="w-4 h-4 inline-block mr-1"></i> Manual
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Framework Selection -->
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Framework
                                            <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <select x-model="scriptFramework" class="form-select w-full rounded-lg">
                                                <template x-for="option in scriptFrameworkOptions">
                                                    <option :value="option.value" x-text="option.label"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Context Information -->
                                    <div x-show="scriptCreationMode === 'ai'"
                                        class="bg-white dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                        <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-2 flex items-center">
                                            <i data-lucide="info"
                                                class="w-4 h-4 mr-2 text-indigo-500 dark:text-indigo-400"></i>
                                            Context Information
                                        </h4>
                                        <div class="text-sm text-zinc-600 dark:text-zinc-300 space-y-2">
                                            <div>
                                                <span class="font-medium">Test Case:</span> {{ $testCase->title }}
                                            </div>
                                            <div>
                                                <span class="font-medium">Steps:</span> {{ count($steps) }} steps defined
                                            </div>
                                            @if ($story)
                                                <div>
                                                    <span class="font-medium">Related Story:</span> {{ $story->title }}
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Additional Context Toggle -->
                                        <div x-data="{ showAdditionalContext: false }" class="mt-3">
                                            <button @click="showAdditionalContext = !showAdditionalContext"
                                                class="text-xs flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                                <i data-lucide="plus-circle" class="w-3.5 h-3.5 mr-1"
                                                    x-show="!showAdditionalContext"></i>
                                                <i data-lucide="minus-circle" class="w-3.5 h-3.5 mr-1"
                                                    x-show="showAdditionalContext"></i>
                                                <span
                                                    x-text="showAdditionalContext ? 'Hide Additional Context' : 'Add Additional Context'"></span>
                                            </button>

                                            <div x-show="showAdditionalContext" x-collapse class="mt-3 space-y-3">
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                        Code Context <span
                                                            class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional)</span>
                                                    </label>
                                                    <textarea x-model="scriptCodeContext" rows="4" class="form-textarea w-full rounded-lg text-xs font-mono"
                                                        placeholder="Paste relevant code, API specifications, or other technical details here"></textarea>
                                                </div>

                                                <!-- File Upload System with Preview and Delete -->
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                        Reference Files <span
                                                            class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional,
                                                            up to 5)</span>
                                                    </label>
                                                    <div class="space-y-3">
                                                        <div x-show="scriptFiles.length < 5">
                                                            <input type="file" id="script-files"
                                                                class="block w-full text-xs text-zinc-600 dark:text-zinc-400
                                                    file:mr-3 file:py-1.5 file:px-3
                                                    file:text-xs file:font-medium
                                                    file:border file:border-zinc-200 dark:file:border-zinc-600
                                                    file:bg-white dark:file:bg-zinc-700
                                                    file:text-indigo-600 dark:file:text-indigo-400
                                                    hover:file:bg-zinc-50 dark:hover:file:bg-zinc-600
                                                    file:rounded-md"
                                                                @change="handleScriptFileUpload($event)"
                                                                accept=".py,.js,.ts,.json,.txt,.csv,.xml,.html,.css">
                                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                                Upload related files to provide more context
                                                            </p>
                                                        </div>

                                                        <!-- File List -->
                                                        <div class="space-y-2" x-show="scriptFiles.length > 0">
                                                            <h5
                                                                class="text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                                                Uploaded Files (<span
                                                                    x-text="scriptFiles.length"></span>/5)
                                                            </h5>
                                                            <ul class="space-y-1.5">
                                                                <template x-for="(file, index) in scriptFiles"
                                                                    :key="index">
                                                                    <li
                                                                        class="flex items-center justify-between px-3 py-2 text-xs bg-zinc-50 dark:bg-zinc-700/30 rounded-md border border-zinc-200 dark:border-zinc-700">
                                                                        <div class="flex items-center truncate">
                                                                            <i data-lucide="file"
                                                                                class="w-3.5 h-3.5 mr-2 text-indigo-500 dark:text-indigo-400"></i>
                                                                            <span class="truncate"
                                                                                x-text="file.name"></span>
                                                                        </div>
                                                                        <button @click="removeScriptFile(index)"
                                                                            class="text-zinc-400 hover:text-red-500 dark:hover:text-red-400">
                                                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                                        </button>
                                                                    </li>
                                                                </template>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Generation History (AI Mode Only) -->
                                    <div x-show="scriptCreationMode === 'ai'">
                                        <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-2 flex items-center">
                                            <i data-lucide="history"
                                                class="w-4 h-4 mr-2 text-indigo-500 dark:text-indigo-400"></i>
                                            Generation History
                                        </h4>
                                        <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
                                            <template x-for="(item, index) in scriptGenerationHistory"
                                                :key="index">
                                                <div @click="useScriptHistoryItem(index)"
                                                    class="p-3 rounded-lg cursor-pointer bg-white dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors text-sm">
                                                    <div class="flex justify-between items-start">
                                                        <span class="font-medium text-zinc-900 dark:text-zinc-100"
                                                            x-text="item.framework || 'Script'"></span>
                                                        <span class="text-xs text-zinc-500 dark:text-zinc-400"
                                                            x-text="formatTime(item.timestamp)"></span>
                                                    </div>
                                                    <p class="mt-1 text-zinc-600 dark:text-zinc-400 line-clamp-2"
                                                        x-text="item.prompt"></p>
                                                </div>
                                            </template>
                                            <div x-show="scriptGenerationHistory.length === 0"
                                                class="p-4 text-center text-sm text-zinc-500 dark:text-zinc-400 italic">
                                                No generation history yet
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Input/Output -->
                            <div class="lg:col-span-2 flex flex-col">
                                <!-- Tabs for Input/Output -->
                                <div class="px-6 pt-6 pb-0 flex border-b border-zinc-200 dark:border-zinc-700">
                                    <!-- AI Mode Tabs -->
                                    <template x-if="scriptCreationMode === 'ai'">
                                        <div class="flex">
                                            <button @click="scriptTab = 'input'"
                                                class="px-4 py-2 font-medium text-sm border-b-2 -mb-px"
                                                :class="scriptTab === 'input' ?
                                                    'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400' :
                                                    'text-zinc-500 dark:text-zinc-400 border-transparent hover:text-zinc-700 dark:hover:text-zinc-300'">
                                                <i data-lucide="pencil" class="w-4 h-4 inline mr-1"></i> Input Prompt
                                            </button>
                                            <button @click="scriptTab = 'output'"
                                                class="px-4 py-2 font-medium text-sm border-b-2 -mb-px"
                                                :class="scriptTab === 'output' ?
                                                    'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400' :
                                                    'text-zinc-500 dark:text-zinc-400 border-transparent hover:text-zinc-700 dark:hover:text-zinc-300'">
                                                <i data-lucide="code" class="w-4 h-4 inline mr-1"></i> Generated Script
                                            </button>
                                        </div>
                                    </template>

                                    <!-- Manual Mode Tab -->
                                    <template x-if="scriptCreationMode === 'manual'">
                                        <div class="flex">
                                            <div
                                                class="px-4 py-2 font-medium text-sm border-b-2 -mb-px text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400">
                                                <i data-lucide="edit-3" class="w-4 h-4 inline mr-1"></i> Manual Script
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- AI Input Tab -->
                                <div x-show="scriptCreationMode === 'ai' && scriptTab === 'input'"
                                    class="p-6 overflow-y-auto flex-1">
                                    <div class="space-y-4">
                                        <!-- Prompt Templates -->
                                        <div>
                                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                                Template <span
                                                    class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional)</span>
                                            </label>
                                            <div class="grid grid-cols-2 gap-2">
                                                <button @click="useScriptTemplate('basic')"
                                                    class="flex items-center px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                                    <i data-lucide="layout-template"
                                                        class="w-4 h-4 mr-1.5 text-indigo-500"></i>
                                                    Basic Test
                                                </button>
                                                <button @click="useScriptTemplate('detailed')"
                                                    class="flex items-center px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                                    <i data-lucide="list-checks"
                                                        class="w-4 h-4 mr-1.5 text-green-500"></i>
                                                    Detailed Test
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Prompt Input -->
                                        <div>
                                            <label for="script-prompt"
                                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                                Prompt <span class="text-red-500">*</span>
                                            </label>
                                            <textarea x-model="scriptPrompt" id="script-prompt" rows="12"
                                                placeholder="Describe what you want the test script to do. Be specific about testing scenarios, assertions, and edge cases."
                                                class="form-textarea w-full rounded-lg" :class="{ 'border-red-500 dark:border-red-500': scriptError }"></textarea>
                                            <p x-show="scriptError" x-text="scriptError"
                                                class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                        </div>

                                        <!-- Generate Button -->
                                        <div class="flex justify-center">
                                            <button @click="generateScript"
                                                class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg shadow-md hover:shadow-lg flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
                                                :disabled="scriptLoading || !scriptPrompt">
                                                <template x-if="!scriptLoading">
                                                    <i data-lucide="sparkles" class="w-5 h-5 mr-2"></i>
                                                </template>
                                                <template x-if="scriptLoading">
                                                    <svg class="animate-spin h-5 w-5 mr-2 text-white"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                                            stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                        </path>
                                                    </svg>
                                                </template>
                                                <span x-text="scriptLoading ? 'Generating...' : 'Generate Script'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- AI Output Tab -->
                                <div x-show="scriptCreationMode === 'ai' && scriptTab === 'output'"
                                    class="flex-1 flex flex-col p-6 overflow-hidden">
                                    <div x-show="!scriptResponse" class="flex-1 flex items-center justify-center">
                                        <div class="text-center p-6">
                                            <div
                                                class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-900/30 mb-4">
                                                <i data-lucide="code"
                                                    class="w-8 h-8 text-indigo-600 dark:text-indigo-400"></i>
                                            </div>
                                            <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No Script
                                                Generated Yet</h3>
                                            <p class="text-zinc-500 dark:text-zinc-400 max-w-md">
                                                Switch to the Input tab and provide a prompt to generate a script using AI.
                                            </p>
                                        </div>
                                    </div>

                                    <div x-show="scriptResponse" class="flex-1 flex flex-col h-full">
                                        <!-- Script Header -->
                                        <div class="mb-4 flex justify-between items-start">
                                            <div>
                                                <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Generated
                                                    Script</h3>
                                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Review and edit the
                                                    generated script before saving</p>
                                            </div>
                                            <div class="flex gap-2">
                                                <button @click="regenerateScript"
                                                    class="p-2 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30"
                                                    :disabled="scriptLoading">
                                                    <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                                                </button>
                                                <button @click="copyScriptToClipboard"
                                                    class="p-2 rounded-lg text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/30">
                                                    <i data-lucide="clipboard-copy" class="w-5 h-5"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Script Content Editor -->
                                        <div class="flex-1 mb-4 overflow-hidden">
                                            <div class="h-full relative">
                                                <textarea x-model="scriptContent" rows="15"
                                                    class="w-full h-full px-4 py-3 font-mono text-sm bg-zinc-50 dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg"
                                                    :class="{ 'language-python': scriptFramework === 'selenium-python', 'language-javascript': scriptFramework === 'cypress' }"></textarea>
                                            </div>
                                        </div>

                                        <!-- Script Name Input -->
                                        <div class="mb-4">
                                            <label for="script-name"
                                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                Script Name <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="script-name" x-model="scriptName"
                                                class="form-input w-full rounded-lg"
                                                placeholder="Enter a name for this script">
                                        </div>
                                    </div>
                                </div>

                                <!-- Manual Entry Tab -->
                                <div x-show="scriptCreationMode === 'manual'"
                                    class="flex-1 flex flex-col p-6 overflow-hidden">
                                    <div class="space-y-4">
                                        <!-- Script Name Input -->
                                        <div>
                                            <label for="manual-script-name"
                                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                Script Name <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="manual-script-name" x-model="scriptName"
                                                class="form-input w-full rounded-lg"
                                                placeholder="Enter a name for this script">
                                        </div>

                                        <!-- Script Content Editor -->
                                        <div>
                                            <label for="manual-script-content"
                                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                Script Content <span class="text-red-500">*</span>
                                            </label>
                                            <textarea x-model="scriptContent" id="manual-script-content" rows="15"
                                                placeholder="Enter your test script code here..."
                                                class="w-full px-4 py-3 font-mono text-sm bg-zinc-50 dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg"
                                                :class="{ 'language-python': scriptFramework === 'selenium-python', 'language-javascript': scriptFramework === 'cypress' }"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Save Button Footer (for both modes) -->
                                <div
                                    class="p-6 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-200 dark:border-zinc-700">
                                    <div class="flex justify-end space-x-3">
                                        <button @click="showScriptModal = false" class="btn-secondary">
                                            Cancel
                                        </button>
                                        <button @click="saveScript" class="btn-primary"
                                            :disabled="!scriptContent || !scriptName">
                                            <i data-lucide="save" class="w-4 h-4 mr-1.5"></i> Save Script
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Create Test Data Modal - Complete Implementation -->
        <div x-cloak x-show="showDataModal" @keydown.escape.window="showDataModal = false"
            class="fixed inset-0 overflow-y-auto z-50" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-zinc-900/70 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
                @click="showDataModal = false"></div>
            <!-- Modal Panel -->
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative w-full max-w-6xl bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-8">
                    <!-- Header -->
                    <div
                        class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-gradient-to-r from-teal-50 to-emerald-50 dark:from-teal-900/20 dark:to-emerald-900/20">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-teal-900 dark:text-teal-100 flex items-center">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="database" class="w-5 h-5 text-teal-600 dark:text-teal-400"></i>
                                    <span>Create Test Data</span>
                                </div>
                            </h3>
                            <button @click="showDataModal = false"
                                class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Content -->
                    <div class="p-0">
                        <div class="grid grid-cols-1 lg:grid-cols-3 h-[calc(100vh-12rem)] max-h-[800px]">
                            <!-- Left Column: Context & Options -->
                            <div
                                class="lg:col-span-1 p-6 border-r border-zinc-200 dark:border-zinc-700/70 bg-zinc-50 dark:bg-zinc-800/50 overflow-y-auto">
                                <div class="space-y-6">
                                    <!-- Creation Mode Tabs -->
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Creation
                                            Mode</label>
                                        <div
                                            class="flex rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                                            <button @click="dataCreationMode = 'ai'"
                                                :class="{
                                                    'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 font-medium': dataCreationMode === 'ai',
                                                    'bg-white dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-700/50': dataCreationMode !== 'ai'
                                                }"
                                                class="flex-1 py-2.5 px-3 text-sm transition-colors">
                                                <i data-lucide="sparkles" class="w-4 h-4 inline-block mr-1"></i>
                                                AI-Assisted
                                            </button>
                                            <button @click="dataCreationMode = 'manual'"
                                                :class="{
                                                    'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 font-medium': dataCreationMode === 'manual',
                                                    'bg-white dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-700/50': dataCreationMode !== 'manual'
                                                }"
                                                class="flex-1 py-2.5 px-3 text-sm transition-colors">
                                                <i data-lucide="edit-3" class="w-4 h-4 inline-block mr-1"></i> Manual
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Format Selection -->
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Data
                                            Format
                                            <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <select x-model="dataFormat" class="form-select w-full rounded-lg">
                                                <template x-for="option in dataFormatOptions">
                                                    <option :value="option.value" x-text="option.label"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Context Information -->
                                    <div x-show="dataCreationMode === 'ai'"
                                        class="bg-white dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                        <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-2 flex items-center">
                                            <i data-lucide="info"
                                                class="w-4 h-4 mr-2 text-teal-500 dark:text-teal-400"></i>
                                            Context Information
                                        </h4>
                                        <div class="text-sm text-zinc-600 dark:text-zinc-300 space-y-2">
                                            <div>
                                                <span class="font-medium">Test Case:</span> {{ $testCase->title }}
                                            </div>
                                            <div>
                                                <span class="font-medium">Expected Results:</span>
                                                <div class="text-xs mt-1 line-clamp-2">{{ $testCase->expected_results }}
                                                </div>
                                            </div>
                                            @if ($testScripts->count() > 0)
                                                <div>
                                                    <span class="font-medium">Test Scripts:</span>
                                                    {{ $testScripts->count() }} available
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Additional Context Toggle -->
                                        <div x-data="{ showAdditionalContext: false }" class="mt-3">
                                            <button @click="showAdditionalContext = !showAdditionalContext"
                                                class="text-xs flex items-center text-teal-600 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-300">
                                                <i data-lucide="plus-circle" class="w-3.5 h-3.5 mr-1"
                                                    x-show="!showAdditionalContext"></i>
                                                <i data-lucide="minus-circle" class="w-3.5 h-3.5 mr-1"
                                                    x-show="showAdditionalContext"></i>
                                                <span
                                                    x-text="showAdditionalContext ? 'Hide Additional Context' : 'Add Additional Context'"></span>
                                            </button>

                                            <div x-show="showAdditionalContext" x-collapse class="mt-3 space-y-3">
                                                <!-- Script Selection -->
                                                @if ($testScripts->count() > 0)
                                                    <div>
                                                        <label
                                                            class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                            Reference Script
                                                        </label>
                                                        <select x-model="dataReferenceScript"
                                                            class="form-select w-full rounded-lg text-xs">
                                                            <option value="">None</option>
                                                            @foreach ($testScripts as $script)
                                                                <option value="{{ $script->id }}">{{ $script->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif

                                                <!-- Data Structure -->
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                        Data Structure <span
                                                            class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional)</span>
                                                    </label>
                                                    <textarea x-model="dataStructure" rows="4" class="form-textarea w-full rounded-lg text-xs font-mono"
                                                        placeholder="Describe the structure of the data you need (fields, expected types, constraints)"></textarea>
                                                </div>

                                                <!-- Example Data -->
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                        Example Data <span
                                                            class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional)</span>
                                                    </label>
                                                    <textarea x-model="dataExample" rows="4" class="form-textarea w-full rounded-lg text-xs font-mono"
                                                        placeholder="Paste examples of valid and/or invalid data here"></textarea>
                                                </div>

                                                <!-- File Upload System with Preview and Delete -->
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                        Reference Files <span
                                                            class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional,
                                                            up to 5)</span>
                                                    </label>
                                                    <div class="space-y-3">
                                                        <div x-show="dataFiles.length < 5">
                                                            <input type="file" id="data-files"
                                                                class="block w-full text-xs text-zinc-600 dark:text-zinc-400
                                                    file:mr-3 file:py-1.5 file:px-3
                                                    file:text-xs file:font-medium
                                                    file:border file:border-zinc-200 dark:file:border-zinc-600
                                                    file:bg-white dark:file:bg-zinc-700
                                                    file:text-teal-600 dark:file:text-teal-400
                                                    hover:file:bg-zinc-50 dark:hover:file:bg-zinc-600
                                                    file:rounded-md"
                                                                @change="handleDataFileUpload($event)"
                                                                accept=".json,.csv,.xml,.txt,.py,.js">
                                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                                Upload related files to provide more context
                                                            </p>
                                                        </div>

                                                        <!-- File List -->
                                                        <div class="space-y-2" x-show="dataFiles.length > 0">
                                                            <h5
                                                                class="text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                                                Uploaded Files (<span x-text="dataFiles.length"></span>/5)
                                                            </h5>
                                                            <ul class="space-y-1.5">
                                                                <template x-for="(file, index) in dataFiles"
                                                                    :key="index">
                                                                    <li
                                                                        class="flex items-center justify-between px-3 py-2 text-xs bg-zinc-50 dark:bg-zinc-700/30 rounded-md border border-zinc-200 dark:border-zinc-700">
                                                                        <div class="flex items-center truncate">
                                                                            <i data-lucide="file"
                                                                                class="w-3.5 h-3.5 mr-2 text-teal-500 dark:text-teal-400"></i>
                                                                            <span class="truncate"
                                                                                x-text="file.name"></span>
                                                                        </div>
                                                                        <button @click="removeDataFile(index)"
                                                                            class="text-zinc-400 hover:text-red-500 dark:hover:text-red-400">
                                                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                                        </button>
                                                                    </li>
                                                                </template>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Generation History (AI Mode Only) -->
                                    <div x-show="dataCreationMode === 'ai'">
                                        <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-2 flex items-center">
                                            <i data-lucide="history"
                                                class="w-4 h-4 mr-2 text-teal-500 dark:text-teal-400"></i>
                                            Generation History
                                        </h4>
                                        <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
                                            <template x-for="(item, index) in dataGenerationHistory"
                                                :key="index">
                                                <div @click="useDataHistoryItem(index)"
                                                    class="p-3 rounded-lg cursor-pointer bg-white dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-700 hover:bg-teal-50 dark:hover:bg-teal-900/20 transition-colors text-sm">
                                                    <div class="flex justify-between items-start">
                                                        <span class="font-medium text-zinc-900 dark:text-zinc-100"
                                                            x-text="item.format || 'Data'"></span>
                                                        <span class="text-xs text-zinc-500 dark:text-zinc-400"
                                                            x-text="formatTime(item.timestamp)"></span>
                                                    </div>
                                                    <p class="mt-1 text-zinc-600 dark:text-zinc-400 line-clamp-2"
                                                        x-text="item.prompt"></p>
                                                </div>
                                            </template>
                                            <div x-show="dataGenerationHistory.length === 0"
                                                class="p-4 text-center text-sm text-zinc-500 dark:text-zinc-400 italic">
                                                No generation history yet
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Input/Output -->
                            <div class="lg:col-span-2 flex flex-col">
                                <!-- Tabs for Input/Output -->
                                <div class="px-6 pt-6 pb-0 flex border-b border-zinc-200 dark:border-zinc-700">
                                    <!-- AI Mode Tabs -->
                                    <template x-if="dataCreationMode === 'ai'">
                                        <div class="flex">
                                            <button @click="dataTab = 'input'"
                                                class="px-4 py-2 font-medium text-sm border-b-2 -mb-px"
                                                :class="dataTab === 'input' ?
                                                    'text-teal-600 dark:text-teal-400 border-teal-600 dark:border-teal-400' :
                                                    'text-zinc-500 dark:text-zinc-400 border-transparent hover:text-zinc-700 dark:hover:text-zinc-300'">
                                                <i data-lucide="pencil" class="w-4 h-4 inline mr-1"></i> Input Prompt
                                            </button>
                                            <button @click="dataTab = 'output'"
                                                class="px-4 py-2 font-medium text-sm border-b-2 -mb-px"
                                                :class="dataTab === 'output' ?
                                                    'text-teal-600 dark:text-teal-400 border-teal-600 dark:border-teal-400' :
                                                    'text-zinc-500 dark:text-zinc-400 border-transparent hover:text-zinc-700 dark:hover:text-zinc-300'">
                                                <i data-lucide="database" class="w-4 h-4 inline mr-1"></i> Generated Data
                                            </button>
                                        </div>
                                    </template>

                                    <!-- Manual Mode Tab -->
                                    <template x-if="dataCreationMode === 'manual'">
                                        <div class="flex">
                                            <div
                                                class="px-4 py-2 font-medium text-sm border-b-2 -mb-px text-teal-600 dark:text-teal-400 border-teal-600 dark:border-teal-400">
                                                <i data-lucide="edit-3" class="w-4 h-4 inline mr-1"></i> Manual Data
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- AI Input Tab -->
                                <div x-show="dataCreationMode === 'ai' && dataTab === 'input'"
                                    class="p-6 overflow-y-auto flex-1">
                                    <div class="space-y-4">
                                        <!-- Prompt Templates -->
                                        <div>
                                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                                Template <span
                                                    class="text-zinc-500 dark:text-zinc-400 text-xs font-normal">(Optional)</span>
                                            </label>
                                            <div class="grid grid-cols-2 gap-2">
                                                <button @click="useDataTemplate('valid')"
                                                    class="flex items-center px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                                    <i data-lucide="check-circle"
                                                        class="w-4 h-4 mr-1.5 text-green-500"></i>
                                                    Valid Test Data
                                                </button>
                                                <button @click="useDataTemplate('invalid')"
                                                    class="flex items-center px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                                    <i data-lucide="x-circle" class="w-4 h-4 mr-1.5 text-red-500"></i>
                                                    Invalid Test Data
                                                </button>
                                                <button @click="useDataTemplate('mixed')"
                                                    class="flex items-center px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                                    <i data-lucide="shuffle" class="w-4 h-4 mr-1.5 text-purple-500"></i>
                                                    Mixed Data Set
                                                </button>
                                                <button @click="useDataTemplate('edge')"
                                                    class="flex items-center px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                                    <i data-lucide="alert-triangle"
                                                        class="w-4 h-4 mr-1.5 text-amber-500"></i>
                                                    Edge Cases
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Prompt Input -->
                                        <div>
                                            <label for="data-prompt"
                                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                                Prompt <span class="text-red-500">*</span>
                                            </label>
                                            <textarea x-model="dataPrompt" id="data-prompt" rows="12"
                                                placeholder="Describe what test data you need. Include details about data structure, required fields, and edge cases you want to test."
                                                class="form-textarea w-full rounded-lg" :class="{ 'border-red-500 dark:border-red-500': dataError }"></textarea>
                                            <p x-show="dataError" x-text="dataError"
                                                class="mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                        </div>

                                        <!-- Generate Button -->
                                        <div class="flex justify-center">
                                            <button @click="generateData"
                                                class="px-6 py-2.5 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white rounded-lg shadow-md hover:shadow-lg flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
                                                :disabled="dataLoading || !dataPrompt">
                                                <template x-if="!dataLoading">
                                                    <i data-lucide="sparkles" class="w-5 h-5 mr-2"></i>
                                                </template>
                                                <template x-if="dataLoading">
                                                    <svg class="animate-spin h-5 w-5 mr-2 text-white"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                                            stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                        </path>
                                                    </svg>
                                                </template>
                                                <span x-text="dataLoading ? 'Generating...' : 'Generate Data'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- AI Output Tab -->
                                <div x-show="dataCreationMode === 'ai' && dataTab === 'output'"
                                    class="flex-1 flex flex-col p-6 overflow-hidden">
                                    <div x-show="!dataResponse" class="flex-1 flex items-center justify-center">
                                        <div class="text-center p-6">
                                            <div
                                                class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-teal-50 dark:bg-teal-900/30 mb-4">
                                                <i data-lucide="database"
                                                    class="w-8 h-8 text-teal-600 dark:text-teal-400"></i>
                                            </div>
                                            <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No Data
                                                Generated Yet</h3>
                                            <p class="text-zinc-500 dark:text-zinc-400 max-w-md">
                                                Switch to the Input tab and provide a prompt to generate test data using AI.
                                            </p>
                                        </div>
                                    </div>

                                    <div x-show="dataResponse" class="flex-1 flex flex-col h-full">
                                        <!-- Data Header -->
                                        <div class="mb-4 flex justify-between items-start">
                                            <div>
                                                <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Generated
                                                    Test Data</h3>
                                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Review and edit the
                                                    generated data before saving</p>
                                            </div>
                                            <div class="flex gap-2">
                                                <button @click="regenerateData"
                                                    class="p-2 rounded-lg text-teal-600 dark:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-900/30"
                                                    :disabled="dataLoading">
                                                    <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                                                </button>
                                                <button @click="copyDataToClipboard"
                                                    class="p-2 rounded-lg text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/30">
                                                    <i data-lucide="clipboard-copy" class="w-5 h-5"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Data Content Editor -->
                                        <div class="flex-1 mb-4 overflow-hidden">
                                            <div class="h-full relative">
                                                <textarea x-model="dataContent" rows="15"
                                                    class="w-full h-full px-4 py-3 font-mono text-sm bg-zinc-50 dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg"
                                                    :class="{
                                                        'language-json': dataFormat === 'json',
                                                        'language-csv': dataFormat === 'csv',
                                                        'language-xml': dataFormat === 'xml',
                                                        'language-plaintext': dataFormat === 'plain'
                                                    }"></textarea>
                                            </div>
                                        </div>

                                        <!-- Common Fields (Name, Usage Context, Sensitive) -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <label for="data-name"
                                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                    Data Name <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" id="data-name" x-model="dataName"
                                                    class="form-input w-full rounded-lg"
                                                    placeholder="Enter a name for this test data">
                                            </div>
                                            <div>
                                                <label for="data-usage-context"
                                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                    Usage Context <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" id="data-usage-context" x-model="dataUsageContext"
                                                    class="form-input w-full rounded-lg"
                                                    placeholder="e.g., 'Valid input scenario' or 'Edge case testing'">
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="flex items-center">
                                                <input type="checkbox" x-model="dataIsSensitive" class="form-checkbox">
                                                <span class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">
                                                    Mark as sensitive data (contains private, personal, or confidential
                                                    information)
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Manual Entry Tab -->
                                <div x-show="dataCreationMode === 'manual'"
                                    class="flex-1 flex flex-col p-6 overflow-hidden">
                                    <div class="space-y-4">
                                        <!-- Data Name Input -->
                                        <div>
                                            <label for="manual-data-name"
                                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                Data Name <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="manual-data-name" x-model="dataName"
                                                class="form-input w-full rounded-lg"
                                                placeholder="Enter a name for this test data">
                                        </div>

                                        <!-- Data Content Editor -->
                                        <div>
                                            <label for="manual-data-content"
                                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                Data Content <span class="text-red-500">*</span>
                                            </label>
                                            <textarea x-model="dataContent" id="manual-data-content" rows="15"
                                                placeholder="Enter your test data content here..."
                                                class="w-full px-4 py-3 font-mono text-sm bg-zinc-50 dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg"
                                                :class="{
                                                    'language-json': dataFormat === 'json',
                                                    'language-csv': dataFormat === 'csv',
                                                    'language-xml': dataFormat === 'xml',
                                                    'language-plaintext': dataFormat === 'plain'
                                                }"></textarea>
                                        </div>

                                        <!-- Usage Context -->
                                        <div>
                                            <label for="manual-data-usage-context"
                                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                Usage Context <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="manual-data-usage-context"
                                                x-model="dataUsageContext" class="form-input w-full rounded-lg"
                                                placeholder="e.g., 'Valid input scenario' or 'Edge case testing'">
                                        </div>

                                        <!-- Sensitive Data Checkbox -->
                                        <div>
                                            <label class="flex items-center">
                                                <input type="checkbox" x-model="dataIsSensitive" class="form-checkbox">
                                                <span class="ml-2 text-sm text-zinc-700 dark:text-zinc-300">
                                                    Mark as sensitive data (contains private, personal, or confidential
                                                    information)
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Save Button Footer (for both modes) -->
                                <div
                                    class="p-6 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-200 dark:border-zinc-700">
                                    <div class="flex justify-end space-x-3">
                                        <button @click="showDataModal = false" class="btn-secondary">
                                            Cancel
                                        </button>
                                        <button @click="saveData" class="btn-primary"
                                            :disabled="!dataContent || !dataName || !dataUsageContext">
                                            <i data-lucide="save" class="w-4 h-4 mr-1.5"></i> Save Test Data
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-cloak x-show="confirmDelete" @keydown.escape.window="confirmDelete = false"
            class="fixed inset-0 overflow-y-auto z-50" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-zinc-900/60 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
                @click="confirmDelete = false"></div>

            <!-- Modal Panel -->
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div @click.stop
                    class="relative bg-white dark:bg-zinc-800 rounded-xl shadow-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden max-w-lg w-full"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-8">

                    <div class="p-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div
                                    class="flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30">
                                    <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600 dark:text-red-500"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Delete Test Case</h3>
                                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                    Are you sure you want to delete test case "<strong
                                        class="font-semibold">{{ $testCase->title }}</strong>"?
                                    This action cannot be undone. Associated test scripts and data connections will also be
                                    removed.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button @click="confirmDelete = false" class="btn-secondary">
                                Cancel
                            </button>
                            <form method="POST"
                                action="{{ route('dashboard.projects.test-cases.destroy', [$project->id, $testCase->id]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger">
                                    Delete Test Case
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Area -->
        <div x-data="notification" x-show="show" x-cloak
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed bottom-4 right-4 w-full max-w-sm p-4 rounded-lg shadow-lg pointer-events-auto"
            :class="{
                'bg-green-50 dark:bg-green-800/90 border border-green-200 dark:border-green-700': type === 'success',
                'bg-red-50 dark:bg-red-800/90 border border-red-200 dark:border-red-700': type === 'error'
            }">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i data-lucide="check-circle" class="w-6 h-6 text-green-500" x-show="type === 'success'"></i>
                    <i data-lucide="alert-circle" class="w-6 h-6 text-red-500" x-show="type === 'error'"></i>
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium"
                        :class="{ 'text-green-800 dark:text-green-100': type === 'success', 'text-red-800 dark:text-red-100': type === 'error' }"
                        x-text="message"></p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button @click="show = false"
                        class="inline-flex rounded-md p-1 focus:outline-none focus:ring-2 focus:ring-offset-2"
                        :class="{
                            'text-green-500 hover:bg-green-100 dark:hover:bg-green-700 focus:ring-green-600 dark:focus:ring-offset-green-800': type === 'success',
                            'text-red-500 hover:bg-red-100 dark:hover:bg-red-700 focus:ring-red-600 dark:focus:ring-offset-red-800': type === 'error'
                        }">
                        <span class="sr-only">Close</span>
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('styles')
    {{-- Include PrismJS theme --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css"
        integrity="sha512-vswe+cgvic/XBoF1OcM/TeJ2FW0OofqAVdCZiEYkd6dwGXuxGoVZSgoqvPKrG4+DingPYFKcCZmHAIU5xyzY解答=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Button Styles */
        .btn-primary {
            @apply inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed;
        }

        .btn-secondary {
            @apply inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 font-medium rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed;
        }

        .btn-danger {
            @apply inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed;
        }

        /* Form Styles */
        .form-input,
        .form-textarea,
        .form-select {
            @apply shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 dark:text-zinc-200 dark:placeholder-zinc-400;
        }

        .form-checkbox {
            @apply shadow-sm focus:ring-indigo-500 text-indigo-600 border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 dark:checked:bg-indigo-500 dark:focus:ring-offset-zinc-800;
        }

        /* Code Highlighting */
        pre[class*="language-"] {
            @apply text-sm leading-relaxed;
        }

        /* Adjust PrismJS background and text for better contrast with the theme */
        :not(pre)>code[class*="language-"],
        pre[class*="language-"] {
            background: #f8fafc;
            /* Light background for light mode */
        }

        .dark :not(pre)>code[class*="language-"],
        .dark pre[class*="language-"] {
            background: #18181b;
            /* Dark background for dark mode */
        }

        /* Ensure padding is handled correctly within the pre block */
        pre[class*="language-"] code {
            display: block;
            padding: 1em;
        }

        .token.comment,
        .token.prolog,
        .token.doctype,
        .token.cdata {
            @apply text-zinc-500 dark:text-zinc-400;
        }

        .token.punctuation {
            @apply text-zinc-600 dark:text-zinc-400;
        }

        .token.property,
        .token.tag,
        .token.boolean,
        .token.number,
        .token.constant,
        .token.symbol,
        .token.deleted {
            @apply text-purple-600 dark:text-purple-400;
        }

        .token.selector,
        .token.attr-name,
        .token.string,
        .token.char,
        .token.builtin,
        .token.inserted {
            @apply text-emerald-600 dark:text-emerald-400;
        }

        .token.operator,
        .token.entity,
        .token.url,
        .language-css .token.string,
        .style .token.string {
            @apply text-amber-700 dark:text-amber-500;
        }

        .token.atrule,
        .token.attr-value,
        .token.keyword {
            @apply text-sky-600 dark:text-sky-400;
        }

        .token.function,
        .token.class-name {
            @apply text-pink-600 dark:text-pink-400;
        }

        .token.regex,
        .token.important,
        .token.variable {
            @apply text-yellow-600 dark:text-yellow-400;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Dropdown styles */
        .dropdown-menu {
            @apply absolute z-50 mt-1 w-full bg-white dark:bg-zinc-800 rounded-lg shadow-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden;
        }

        /* Modal animations and polish */
        .modal-title {
            @apply text-lg font-medium;
        }

        /* Enhanced form input focus state */
        .form-input:focus,
        .form-textarea:focus {
            @apply ring-2 ring-indigo-200 dark:ring-indigo-800;
        }
    </style>
@endpush

@push('scripts')
    {{-- Include PrismJS Core & Components --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"
        integrity="sha512-7Z9J3l1+EYfeaPKcGXu3MS/7BLOQmLpoTsAbMTyog+Kmy8Џ1MLXMH4Q7mvN+6hQMER+7IUcudCLD7b/q+/mDQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"
        integrity="sha512-SkmBfuA2hqjzEVpmnMt/LINrjDhDHjXCqwsllmJNCDHEVLcwjDqfbYf9hPec6pvQO/+JiS9J7Gf6+mFk07kqBQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {{-- Explicitly include common languages to potentially speed up initial load --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"
        integrity="sha512-AKaNmg/7cgoALCU5Ym9JbUSGTz0KXvuRcV5I9Ua/qOPGIMI/6nMCFCWJ78SMOE4YQEJjOsZyrV3/7urTGC9QkQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"
        integrity="sha512-jwrwRWZAbkLEMLrbzLytL9BIJM8/1MvSknYZLHI501BHP+2KqS6Kk3tL9CHJDsF5Lj49Xh87jTmT9AXW/1h0DQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"
        integrity="sha512-jBiL8rLpA/nR/fN3h+Gk9x3jdgX9o8ZbbX5J7s+q+n1sQe5fMzy1b252b6E8v4v4BfX+HKfUUpiIXgmA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-xml-doc.min.js"
        integrity="sha512-PBrZ7p/w15J53sYyP4U81J81+M1L0jxqjF1Wp4z+3W8/94+5s0+Qd4h+biTXn7KAbwEB7GgX+ZNI7Q=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-csv.min.js"
        integrity="sha512-zVryyVVKpQW19+fJvljzY904/IFt0d41y5n1W1u3WsyN0f4o9s2Emtw0s44y+hAjX9t70y9b6qI+GvQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('testCaseView', () => ({
                activeTab: 'details',
                expandedScript: null,
                expandedData: null,
                confirmDelete: false,

                showScriptModal: false,
                scriptCreationMode: 'ai', // 'ai' or 'manual'
                scriptTab: 'input', // 'input' or 'output' for AI mode
                scriptFramework: 'selenium-python',
                scriptFrameworkOptions: [{
                        value: 'selenium-python',
                        label: 'Selenium (Python)'
                    },
                    {
                        value: 'cypress',
                        label: 'Cypress (JavaScript)'
                    },
                    {
                        value: 'other',
                        label: 'Other Framework'
                    }
                ],
                scriptPrompt: '',
                scriptCodeContext: '',
                scriptFiles: [], // Array to store uploaded files
                scriptLoading: false,
                scriptError: null,
                scriptResponse: null,
                scriptContent: '',
                scriptName: '',
                scriptGenerationHistory: [],

                // Data Modal
                showDataModal: false,
                dataCreationMode: 'ai', // 'ai' or 'manual'
                dataTab: 'input', // 'input' or 'output' for AI mode
                dataFormat: 'json',
                dataFormatOptions: [{
                        value: 'json',
                        label: 'JSON'
                    },
                    {
                        value: 'csv',
                        label: 'CSV'
                    },
                    {
                        value: 'xml',
                        label: 'XML'
                    },
                    {
                        value: 'plain',
                        label: 'Plain Text'
                    },
                    {
                        value: 'other',
                        label: 'Other Format'
                    }
                ],
                dataPrompt: '',
                dataReferenceScript: '',
                dataStructure: '',
                dataExample: '',
                dataFiles: [], // Array to store uploaded files
                dataLoading: false,
                dataError: null,
                dataResponse: null,
                dataContent: '',
                dataName: '',
                dataUsageContext: '',
                dataIsSensitive: false,
                dataGenerationHistory: [],

                init() {
                    // Load AI history from localStorage
                    this.loadGenerationHistory();

                    // Old init logic
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                        this.highlightCode();

                        const hash = window.location.hash;
                        if (hash) {
                            const tab = hash.replace('#', '');
                            if (['details', 'scripts', 'testdata'].includes(tab)) {
                                this.activeTab = tab;
                            }
                        }
                    });

                    this.$watch('activeTab', (value) => {
                        if (history.pushState) {
                            history.pushState(null, null, `#${value}`);
                        } else {
                            window.location.hash = `#${value}`;
                        }
                        if (['scripts', 'testdata'].includes(value)) {
                            this.highlightCode();
                        }
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                        });
                    });

                    this.$watch('manualScriptFramework', () => {
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                        });
                    });

                    this.$watch('manualDataFormat', () => {
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                            this.highlightCode();
                        });
                    });

                    try {
                        const savedScriptHistory = localStorage.getItem('script_generation_history');
                        if (savedScriptHistory) {
                            this.scriptGenerationHistory = JSON.parse(savedScriptHistory);
                        }
                    } catch (e) {
                        console.error('Failed to parse script generation history:', e);
                        this.scriptGenerationHistory = [];
                    }

                    try {
                        const savedDataHistory = localStorage.getItem('data_generation_history');
                        if (savedDataHistory) {
                            this.dataGenerationHistory = JSON.parse(savedDataHistory);
                        }
                    } catch (e) {
                        console.error('Failed to parse data generation history:', e);
                        this.dataGenerationHistory = [];
                    }
                },

                openDataModal() {
                    this.showDataModal = true;
                    this.dataCreationMode = 'ai';
                    this.dataTab = 'input';
                    this.dataError = null;
                    this.dataResponse = null;
                    this.dataContent = '';
                    this.dataName = '';
                    this.dataUsageContext = '';
                    this.dataIsSensitive = false;
                    this.dataFiles = [];
                },

                openScriptModal() {
                    this.showScriptModal = true;
                    this.scriptCreationMode = 'ai';
                    this.scriptTab = 'input';
                    this.scriptError = null;
                    this.scriptResponse = null;
                    this.scriptContent = '';
                    this.scriptName = '';
                    this.scriptFiles = [];
                },

                // New methods for AI history
                loadGenerationHistory() {
                    try {
                        const savedScriptHistory = localStorage.getItem('script_generation_history');
                        if (savedScriptHistory) {
                            this.scriptGenerationHistory = JSON.parse(savedScriptHistory);
                        }
                        const savedDataHistory = localStorage.getItem('data_generation_history');
                        if (savedDataHistory) {
                            this.dataGenerationHistory = JSON.parse(savedDataHistory);
                        }
                    } catch (e) {
                        console.error('Failed to parse generation history:', e);
                        this.scriptGenerationHistory = [];
                        this.dataGenerationHistory = [];
                    }
                },

                formatTime(timestamp) {
                    return new Date(timestamp).toLocaleString();
                },

                // Old helpers
                getLabel(optionsArray, value) {
                    const option = optionsArray.find(opt => opt.value === value);
                    return option ? option.label : 'Select...';
                },

                selectOption(optionType, value, dropdownFlag) {
                    this[optionType] = value;
                    this[dropdownFlag] = false;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                        this.highlightCode();
                    });
                },

                setActiveTab(tab) {
                    this.activeTab = tab;
                },

                highlightCode() {
                    this.$nextTick(() => {
                        if (typeof Prism !== 'undefined') {
                            Prism.highlightAll();
                        }
                    });
                },

                toggleScript(id) {
                    this.expandedScript = this.expandedScript === id ? null : id;
                    if (this.expandedScript === id) {
                        this.highlightCode();
                    }
                },

                toggleData(id) {
                    this.expandedData = this.expandedData === id ? null : id;
                    if (this.expandedData === id) {
                        this.highlightCode();
                    }
                },

                copyToClipboard(text, type = 'Content') {
                    navigator.clipboard.writeText(text).then(
                        () => this.showNotificationMessage(`${type} copied to clipboard!`,
                            'success'),
                        (err) => this.showNotificationMessage(`Failed to copy ${type}: ${err}`,
                            'error')
                    );
                },

                refreshPage() {
                    window.location.reload();
                },

                showNotificationMessage(message, type = 'success') {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            message,
                            type
                        }
                    }));
                },

                // --- AI Script Generation ---
                async generateScript() {
                    if (!this.scriptPrompt) {
                        this.scriptError = 'Please enter a prompt';
                        return;
                    }

                    this.scriptError = null;
                    this.scriptLoading = true;

                    try {
                        const context = {
                            project_id: '{{ $project->id }}',
                            test_case_id: '{{ $testCase->id }}',
                            framework_type: this.scriptFramework,
                            code: this.scriptCodeContext || undefined
                        };

                        // Add file contents if available
                        if (this.scriptFiles.length > 0) {
                            context.file_count = this.scriptFiles.length;
                        }

                        const response = await fetch(
                            '{{ route('api.ai.generate', 'test-script') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    prompt: this.scriptPrompt,
                                    context
                                })
                            });

                        const result = await response.json();

                        if (result.success) {
                            this.scriptResponse = result.data;
                            this.scriptContent = result.data.content;
                            this.scriptName =
                                `{{ $testCase->title }} - ${this.getScriptFrameworkLabel()} Script`;

                            // Add to generation history
                            this.addToScriptHistory({
                                timestamp: Date.now(),
                                prompt: this.scriptPrompt,
                                framework: this.getScriptFrameworkLabel(),
                                content: this.scriptContent
                            });

                            // Switch to output tab
                            this.scriptTab = 'output';
                            this.$nextTick(() => this.highlightCode());
                        } else {
                            throw new Error(result.message || 'Failed to generate script');
                        }
                    } catch (error) {
                        console.error('Script generation error:', error);
                        this.scriptError = error.message || 'An error occurred during generation';
                    } finally {
                        this.scriptLoading = false;
                    }
                },

                regenerateScript() {
                    this.scriptTab = 'input';
                    this.generateScript();
                },

                copyScriptToClipboard() {
                    navigator.clipboard.writeText(this.scriptContent).then(
                        () => this.showNotificationMessage('Script copied to clipboard!',
                            'success'),
                        (err) => this.showNotificationMessage('Failed to copy script: ' + err,
                            'error')
                    );
                },

                getScriptFrameworkLabel() {
                    const option = this.scriptFrameworkOptions.find(opt => opt.value === this
                        .scriptFramework);
                    return option ? option.label : 'Script';
                },

                // Add item to script generation history
                addToScriptHistory(item) {
                    this.scriptGenerationHistory = [item, ...this.scriptGenerationHistory].slice(0, 10);
                    localStorage.setItem('script_generation_history', JSON.stringify(this
                        .scriptGenerationHistory));
                },

                // Use a script history item
                useScriptHistoryItem(index) {
                    const item = this.scriptGenerationHistory[index];
                    if (!item) return;

                    this.scriptPrompt = item.prompt;
                    this.scriptContent = item.content;

                    const frameworkOption = this.scriptFrameworkOptions.find(opt => opt.label === item
                        .framework);
                    if (frameworkOption) {
                        this.scriptFramework = frameworkOption.value;
                    }

                    this.scriptResponse = {
                        content: item.content
                    };

                    this.scriptName = `{{ $testCase->title }} - ${item.framework || 'Script'}`;
                    this.scriptTab = 'output';

                    this.$nextTick(() => this.highlightCode());
                },

                // Use a script template
                useScriptTemplate(type) {
                    const frameworkLabel = this.getScriptFrameworkLabel();

                    switch (type) {
                        case 'basic':
                            this.scriptPrompt =
                                `Generate a simple ${frameworkLabel} test script that verifies all the basic functionality described in the test case steps. The script should handle setup, test execution, and teardown.`;
                            break;
                        case 'detailed':
                            this.scriptPrompt =
                                `Generate a comprehensive ${frameworkLabel} test script with detailed assertions, error handling, and documentation. Include proper setup and teardown, and add comments explaining the key parts of the code. Handle edge cases and potential failures.`;
                            break;
                    }
                },

                handleScriptFileUpload(event) {
                    const files = event.target.files;
                    if (!files || !files.length) return;

                    // Add the file to our array (up to 5 files)
                    if (this.scriptFiles.length < 5) {
                        this.scriptFiles.push(files[0]);

                        // Read the file content if it's a text file
                        if (files[0].type.includes('text') || files[0].name.match(
                                /\.(py|js|json|css|html|xml|csv|txt)$/i)) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                // Add file content to context
                                const fileName = files[0].name;
                                const fileContent = e.target.result;
                                this.scriptCodeContext +=
                                    `\n\n// File: ${fileName}\n${fileContent}`;
                            };
                            reader.readAsText(files[0]);
                        }

                        // Reset the file input to allow selecting the same file again
                        event.target.value = null;
                    }
                },
                // Remove a file from the list
                removeScriptFile(index) {
                    this.scriptFiles.splice(index, 1);
                },

                handleDataFileUpload(event) {
                    const files = event.target.files;
                    if (!files || !files.length) return;

                    // Add the file to our array (up to 5 files)
                    if (this.dataFiles.length < 5) {
                        this.dataFiles.push(files[0]);

                        // Read the file content if it's a text file
                        if (files[0].type.includes('text') || files[0].name.match(
                                /\.(json|csv|xml|txt|py|js)$/i)) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                // Add file content to context
                                const fileName = files[0].name;
                                const fileContent = e.target.result;
                                this.dataExample += `\n\n// File: ${fileName}\n${fileContent}`;
                            };
                            reader.readAsText(files[0]);
                        }

                        // Reset the file input to allow selecting the same file again
                        event.target.value = null;
                    }
                },

                // Remove a file from the list
                removeDataFile(index) {
                    this.dataFiles.splice(index, 1);
                },

                // --- AI Test Data Generation ---
                async generateData() {
                    if (!this.aiDataPrompt) {
                        this.aiDataError = 'Please enter a prompt';
                        return;
                    }
                    this.aiDataError = null;
                    this.aiDataLoading = true;
                    try {
                        const context = {
                            project_id: '{{ $project->id }}',
                            test_case_id: '{{ $testCase->id }}',
                            format: this.aiDataFormat
                        };
                        if (this.aiDataStructure) context.data_structure = this.aiDataStructure;
                        if (this.aiDataExample) context.example_data = this.aiDataExample;
                        if (this.aiDataReferenceScript) context.script_id = this
                            .aiDataReferenceScript;

                        const response = await fetch(
                            '{{ route('api.ai.generate', 'test-data') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    prompt: this.aiDataPrompt,
                                    context
                                })
                            });
                        const result = await response.json();
                        if (result.success) {
                            this.aiDataResponse = result.data;
                            this.aiDataContent = result.data.content;
                            this.aiDataName =
                                `{{ $testCase->title }} - ${this.getFormatLabel()} Data`;
                            this.aiDataUsageContext = 'AI Generated Test Data';
                            this.addToDataHistory({
                                timestamp: Date.now(),
                                prompt: this.aiDataPrompt,
                                format: this.getFormatLabel(),
                                content: this.aiDataContent
                            });
                            this.aiDataTab = 'output';
                            this.$nextTick(() => this.highlightCode());
                        } else {
                            throw new Error(result.message || 'Failed to generate test data');
                        }
                    } catch (error) {
                        console.error('Data generation error:', error);
                        this.aiDataError = error.message || 'An error occurred during generation';
                    } finally {
                        this.aiDataLoading = false;
                    }
                },

                regenerateData() {
                    this.aiDataTab = 'input';
                    this.generateData();
                },

                copyDataToClipboard() {
                    navigator.clipboard.writeText(this.aiDataContent).then(
                        () => this.showNotificationMessage('Data copied to clipboard!', 'success'),
                        (err) => this.showNotificationMessage('Failed to copy data: ' + err,
                            'error')
                    );
                },

                getFormatLabel() {
                    const option = this.aiDataFormatOptions.find(opt => opt.value === this
                        .aiDataFormat);
                    return option ? option.label : 'Data';
                },

                addToDataHistory(item) {
                    this.dataGenerationHistory = [item, ...this.dataGenerationHistory].slice(0, 10);
                    localStorage.setItem('data_generation_history', JSON.stringify(this
                        .dataGenerationHistory));
                },

                useDataHistoryItem(index) {
                    const item = this.dataGenerationHistory[index];
                    if (!item) return;
                    this.aiDataPrompt = item.prompt;
                    this.aiDataContent = item.content;
                    const formatOption = this.aiDataFormatOptions.find(opt => opt.label === item
                        .format);
                    if (formatOption) {
                        this.aiDataFormat = formatOption.value;
                    }
                    this.aiDataResponse = {
                        content: item.content
                    };
                    this.aiDataName = `{{ $testCase->title }} - ${item.format || 'Data'}`;
                    this.aiDataUsageContext = 'AI Generated Test Data';
                    this.aiDataTab = 'output';
                    this.$nextTick(() => this.highlightCode());
                },

                useDataTemplate(type) {
                    const formatName = this.getFormatLabel();
                    switch (type) {
                        case 'valid':
                            this.aiDataPrompt =
                                `Generate valid test data in ${formatName} format for this test case. Include all required fields and ensure the data matches the expected format.`;
                            break;
                        case 'invalid':
                            this.aiDataPrompt =
                                `Generate invalid test data in ${formatName} format for this test case. Create data that should fail validation with different types of errors (missing required fields, invalid formats, out of range values, etc.).`;
                            break;
                        case 'mixed':
                            this.aiDataPrompt =
                                `Generate a comprehensive set of test data in ${formatName} format, including both valid and invalid examples. Label or organize them clearly to distinguish between the different types.`;
                            break;
                        case 'edge':
                            this.aiDataPrompt =
                                `Generate edge case test data in ${formatName} format for this test case. Include boundary values, minimum/maximum values, empty strings, and other edge cases that should be tested.`;
                            break;
                    }
                },

                async generateData() {
                    if (!this.dataPrompt) {
                        this.dataError = 'Please enter a prompt';
                        return;
                    }

                    this.dataError = null;
                    this.dataLoading = true;

                    try {
                        const context = {
                            project_id: '{{ $project->id }}',
                            test_case_id: '{{ $testCase->id }}',
                            format: this.dataFormat
                        };

                        // Add additional context if available
                        if (this.dataStructure) context.data_structure = this.dataStructure;
                        if (this.dataExample) context.example_data = this.dataExample;
                        if (this.dataReferenceScript) context.script_id = this.dataReferenceScript;

                        // Add file contents count if available
                        if (this.dataFiles.length > 0) {
                            context.file_count = this.dataFiles.length;
                        }

                        const response = await fetch(
                            '{{ route('api.ai.generate', 'test-data') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    prompt: this.dataPrompt,
                                    context
                                })
                            });

                        const result = await response.json();

                        if (result.success) {
                            this.dataResponse = result.data;
                            this.dataContent = result.data.content;
                            this.dataName =
                                `{{ $testCase->title }} - ${this.getDataFormatLabel()} Data`;
                            this.dataUsageContext = 'AI Generated Test Data';

                            // Add to generation history
                            this.addToDataHistory({
                                timestamp: Date.now(),
                                prompt: this.dataPrompt,
                                format: this.getDataFormatLabel(),
                                content: this.dataContent
                            });

                            // Switch to output tab
                            this.dataTab = 'output';
                            this.$nextTick(() => this.highlightCode());
                        } else {
                            throw new Error(result.message || 'Failed to generate test data');
                        }
                    } catch (error) {
                        console.error('Data generation error:', error);
                        this.dataError = error.message || 'An error occurred during generation';
                    } finally {
                        this.dataLoading = false;
                    }
                },

                // Regenerate the data with the same parameters
                regenerateData() {
                    this.dataTab = 'input';
                    this.generateData();
                },

                // Copy the generated data to clipboard
                copyDataToClipboard() {
                    navigator.clipboard.writeText(this.dataContent).then(
                        () => this.showNotificationMessage('Data copied to clipboard!', 'success'),
                        (err) => this.showNotificationMessage('Failed to copy data: ' + err,
                            'error')
                    );
                },

                // Get the label of the current format
                getDataFormatLabel() {
                    const option = this.dataFormatOptions.find(opt => opt.value === this.dataFormat);
                    return option ? option.label : 'Data';
                },

                // Add item to data generation history
                addToDataHistory(item) {
                    this.dataGenerationHistory = [item, ...this.dataGenerationHistory].slice(0, 10);
                    localStorage.setItem('data_generation_history', JSON.stringify(this
                        .dataGenerationHistory));
                },

                // Use a data history item
                useDataHistoryItem(index) {
                    const item = this.dataGenerationHistory[index];
                    if (!item) return;

                    this.dataPrompt = item.prompt;
                    this.dataContent = item.content;

                    const formatOption = this.dataFormatOptions.find(opt => opt.label === item.format);
                    if (formatOption) {
                        this.dataFormat = formatOption.value;
                    }

                    this.dataResponse = {
                        content: item.content
                    };

                    this.dataName = `{{ $testCase->title }} - ${item.format || 'Data'}`;
                    this.dataUsageContext = 'AI Generated Test Data';
                    this.dataTab = 'output';

                    this.$nextTick(() => this.highlightCode());
                },

                // Use a data template
                useDataTemplate(type) {
                    const formatName = this.getDataFormatLabel();

                    switch (type) {
                        case 'valid':
                            this.dataPrompt =
                                `Generate valid test data in ${formatName} format for this test case. Include all required fields and ensure the data matches the expected format.`;
                            break;
                        case 'invalid':
                            this.dataPrompt =
                                `Generate invalid test data in ${formatName} format for this test case. Create data that should fail validation with different types of errors (missing required fields, invalid formats, out of range values, etc.).`;
                            break;
                        case 'mixed':
                            this.dataPrompt =
                                `Generate a comprehensive set of test data in ${formatName} format, including both valid and invalid examples. Label or organize them clearly to distinguish between the different types.`;
                            break;
                        case 'edge':
                            this.dataPrompt =
                                `Generate edge case test data in ${formatName} format for this test case. Include boundary values, minimum/maximum values, empty strings, and other edge cases that should be tested.`;
                            break;
                    }
                },

                async saveScript() {
                    if (!this.scriptContent || !this.scriptName) {
                        this.showNotificationMessage(
                            'Please provide a name and ensure the script has content', 'error');
                        return;
                    }

                    try {
                        const formData = new FormData();
                        formData.append('name', this.scriptName);
                        formData.append('framework_type', this.scriptFramework);
                        formData.append('script_content', this.scriptContent);
                        formData.append('_token', '{{ csrf_token() }}');

                        // Add metadata about creation method
                        if (this.scriptCreationMode === 'ai') {
                            formData.append('metadata[created_through]', 'ai');
                            formData.append('metadata[prompt]', this.scriptPrompt);
                        } else {
                            formData.append('metadata[created_through]', 'manual');
                        }

                        const response = await fetch(
                            '{{ route('dashboard.projects.test-cases.scripts.store', [$project->id, $testCase->id]) }}', {
                                method: 'POST',
                                body: formData
                            });

                        if (response.ok) {
                            this.showNotificationMessage('Script saved successfully!', 'success');
                            this.showScriptModal = false;
                            window.location.reload();
                        } else {
                            const error = await response.json();
                            throw new Error(error.message || 'Failed to save script');
                        }
                    } catch (error) {
                        console.error('Error saving script:', error);
                        this.showNotificationMessage('Failed to save script: ' + error.message,
                            'error');
                    }
                },

                // Save the data (works for both AI-generated and manual)
                async saveData() {
                    if (!this.dataContent || !this.dataName || !this.dataUsageContext) {
                        this.showNotificationMessage(
                            'Please provide a name, usage context, and ensure the data has content',
                            'error');
                        return;
                    }

                    try {
                        const formData = new FormData();
                        formData.append('name', this.dataName);
                        formData.append('format', this.dataFormat);
                        formData.append('content', this.dataContent);
                        formData.append('usage_context', this.dataUsageContext);
                        formData.append('is_sensitive', this.dataIsSensitive ? '1' : '0');
                        formData.append('_token', '{{ csrf_token() }}');

                        // Add metadata about creation method
                        if (this.dataCreationMode === 'ai') {
                            formData.append('metadata[created_through]', 'ai');
                            formData.append('metadata[prompt]', this.dataPrompt);
                        } else {
                            formData.append('metadata[created_through]', 'manual');
                        }

                        const response = await fetch(
                            '{{ route('dashboard.projects.test-cases.data.store', [$project->id, $testCase->id]) }}', {
                                method: 'POST',
                                body: formData
                            });

                        if (response.ok) {
                            this.showNotificationMessage('Test data saved successfully!',
                                'success');
                            this.showDataModal = false;
                            window.location.reload();
                        } else {
                            const error = await response.json();
                            throw new Error(error.message || 'Failed to save test data');
                        }
                    } catch (error) {
                        console.error('Error saving test data:', error);
                        this.showNotificationMessage('Failed to save test data: ' + error.message,
                            'error');
                    }
                },


            }));

            // Notification component remains unchanged
            Alpine.data('notification', () => ({
                show: false,
                message: '',
                type: 'success',
                timeout: null,

                init() {
                    window.addEventListener('notify', event => {
                        this.message = event.detail.message;
                        this.type = event.detail.type || 'success';
                        this.show = true;

                        if (this.timeout) {
                            clearTimeout(this.timeout);
                        }
                        this.timeout = setTimeout(() => {
                            this.show = false;
                        }, 5000);

                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                        });
                    });
                }
            }));
        });
    </script>
@endpush
