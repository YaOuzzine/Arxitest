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
        'draft' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 border-yellow-200 dark:border-yellow-800/40',
        'active' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 border-green-200 dark:border-green-800/40',
        'deprecated' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300 border-orange-200 dark:border-orange-800/40',
        'archived' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700/40 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700/40',
    ];
    $statusColor = $statusColors[$testCase->status] ?? $statusColors['draft'];

    $priorityColors = [
        'low' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border-blue-200 dark:border-blue-800/40',
        'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 border-yellow-200 dark:border-yellow-800/40',
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
        <a href="{{ route('dashboard.projects') }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">Projects</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.show', $project->id) }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">{{ $project->name }}</a>
    </li>
    @if ($testSuite)
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <a href="{{ route('dashboard.projects.test-suites.show', [$project->id, $testSuite->id]) }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">{{ $testSuite->name }}</a>
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
                <a href="{{ route('dashboard.projects.test-cases.edit', [$project->id, $testCase->id]) }}" class="btn-secondary">
                    <i data-lucide="edit-3" class="w-4 h-4 mr-1"></i> Edit
                </a>
                <button @click="confirmDelete = true" class="btn-danger">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Delete
                </button>
            </div>
        </div>

        <!-- Main Content Tabs -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
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
                        Test Scripts <span class="ml-1 px-2 py-0.5 rounded-full text-xs bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300">{{ $testScripts->count() }}</span>
                    </button>
                    <button @click="setActiveTab('testdata')"
                        :class="{
                            'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400': activeTab === 'testdata',
                            'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600': activeTab !== 'testdata'
                        }"
                        class="px-4 py-4 font-medium text-sm border-b-2 whitespace-nowrap">
                        <i data-lucide="database" class="inline-block w-4 h-4 mr-1"></i>
                        Test Data <span class="ml-1 px-2 py-0.5 rounded-full text-xs bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300">{{ $testData->count() }}</span>
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
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-4 border border-indigo-200 dark:border-indigo-800/40 mb-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i data-lucide="book-open" class="h-5 w-5 text-indigo-600 dark:text-indigo-400 mt-1"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-indigo-800 dark:text-indigo-200">Related Story</h3>
                                        <div class="mt-1">
                                            <a href="{{ route('dashboard.stories.show', $story->id) }}" class="text-base font-medium text-indigo-700 dark:text-indigo-300 hover:text-indigo-900 dark:hover:text-indigo-100">
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
                                <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                    <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-line">
                                        {{ $testCase->description ?: 'No description provided.' }}</p>
                                </div>
                            </div>

                            <!-- Steps -->
                            <div>
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Test Steps</h3>
                                <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
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
                                <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                    <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-line">
                                        {{ $testCase->expected_results }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Metadata -->
                        <div class="space-y-6">
                            <!-- Test Suite Info -->
                            @if ($testSuite)
                                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                                    <div class="px-4 py-3 bg-zinc-50 dark:bg-zinc-700/30 border-b border-zinc-200 dark:border-zinc-700">
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
                            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                                <div class="px-4 py-3 bg-zinc-50 dark:bg-zinc-700/30 border-b border-zinc-200 dark:border-zinc-700">
                                    <h3 class="font-medium text-zinc-900 dark:text-white">Tags</h3>
                                </div>
                                <div class="p-4">
                                    @if (count($tags) > 0)
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($tags as $tag)
                                                <span class="px-2 py-1 text-xs font-medium rounded-md bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/30">
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
                            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                                <div class="px-4 py-3 bg-zinc-50 dark:bg-zinc-700/30 border-b border-zinc-200 dark:border-zinc-700">
                                    <h3 class="font-medium text-zinc-900 dark:text-white">Creation Info</h3>
                                </div>
                                <div class="p-4 space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500 dark:text-zinc-400">Created</span>
                                        <span class="text-zinc-800 dark:text-zinc-200">{{ $testCase->created_at->format('M d, Y') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500 dark:text-zinc-400">Last Updated</span>
                                        <span class="text-zinc-800 dark:text-zinc-200">{{ $testCase->updated_at->format('M d, Y') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500 dark:text-zinc-400">ID</span>
                                        <span class="text-zinc-800 dark:text-zinc-200 font-mono text-xs">{{ $testCase->id }}</span>
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
                        <div class="flex gap-2">
                            <button @click="showCreateScriptModal = true" class="btn-secondary">
                                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Add Manually
                            </button>
                            <button @click="showAIScriptModal = true" class="btn-primary">
                                <i data-lucide="sparkles" class="w-4 h-4 mr-1"></i> Generate with AI
                            </button>
                        </div>
                    </div>

                    <!-- Script List -->
                    @if ($testScripts->count() > 0)
                        <div class="space-y-4">
                            @foreach ($testScripts as $script)
                                @php
                                    $scriptLanguage = $frameworkLanguages[$script->framework_type] ?? 'markup';
                                    $isAiGenerated = isset($script->metadata['created_through']) && $script->metadata['created_through'] === 'ai';
                                @endphp
                                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200"
                                    x-data="{ expanded: expandedScript === '{{ $script->id }}' }" :class="{ 'shadow-md': expanded }">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-700/30 gap-3">
                                        <div>
                                            <h4 class="text-md font-medium text-zinc-900 dark:text-white mb-1">
                                                {{ $script->name }}</h4>
                                            <div class="flex flex-wrap items-center text-sm text-zinc-500 dark:text-zinc-400 gap-x-3 gap-y-1">
                                                <span class="flex items-center">
                                                    <i data-lucide="code" class="w-3.5 h-3.5 mr-1"></i>
                                                    {{ ucfirst(str_replace('-', ' ', $script->framework_type)) }}
                                                </span>
                                                <span class="flex items-center">
                                                    <i data-lucide="clock" class="w-3.5 h-3.5 mr-1"></i>
                                                    {{ $script->created_at->diffForHumans() }}
                                                </span>
                                                @if ($isAiGenerated)
                                                    <span class="px-2 py-0.5 text-xs font-medium rounded-md bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/30">
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
                                            <button @click="copyToClipboard($el.parentElement.querySelector('code').innerText, 'Script')"
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
                        <div class="bg-zinc-50 dark:bg-zinc-700/30 border border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg p-8 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400 mb-3">
                                <i data-lucide="file-code" class="w-6 h-6"></i>
                            </div>
                            <h4 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No Test Scripts Yet</h4>
                            <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-4">
                                Test scripts help automate this test case. Add one manually or generate with AI assistance.
                            </p>
                            <div class="flex flex-col sm:flex-row justify-center gap-3">
                                <button @click="showCreateScriptModal = true" class="btn-secondary">
                                    <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Add Manually
                                </button>
                                <button @click="showAIScriptModal = true" class="btn-primary">
                                    <i data-lucide="sparkles" class="w-4 h-4 mr-1"></i> Generate with AI
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
                        <div class="flex gap-2">
                            <button @click="showCreateDataModal = true" class="btn-secondary">
                                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Add Manually
                            </button>
                            <button @click="showAIDataModal = true" class="btn-primary">
                                <i data-lucide="sparkles" class="w-4 h-4 mr-1"></i> Generate with AI
                            </button>
                        </div>
                    </div>

                    <!-- Data List -->
                    @if ($testData->count() > 0)
                        <div class="space-y-4">
                            @foreach ($testData as $data)
                                @php
                                    $dataLanguage = $dataFormatLanguages[$data->format] ?? 'markup';
                                    $isDataAiGenerated = isset($data->metadata['created_through']) && $data->metadata['created_through'] === 'ai';
                                @endphp
                                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200"
                                    x-data="{ expanded: expandedData === '{{ $data->id }}' }" :class="{ 'shadow-md': expanded }">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-700/30 gap-3">
                                        <div>
                                            <h4 class="text-md font-medium text-zinc-900 dark:text-white mb-1">
                                                {{ $data->name }}</h4>
                                            <div class="flex flex-wrap items-center text-sm text-zinc-500 dark:text-zinc-400 gap-x-3 gap-y-1">
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-md bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800/30">
                                                    {{ strtoupper($data->format) }}
                                                </span>
                                                @if ($data->is_sensitive)
                                                    <span class="px-2 py-0.5 text-xs font-medium rounded-md bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800/30">
                                                        Sensitive
                                                    </span>
                                                @endif
                                                <span class="flex items-center">
                                                    <i data-lucide="clock" class="w-3.5 h-3.5 mr-1"></i>
                                                    {{ $data->created_at->diffForHumans() }}
                                                </span>
                                                @if ($isDataAiGenerated)
                                                    <span class="px-2 py-0.5 text-xs font-medium rounded-md bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/30">
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
                                            <button @click="copyToClipboard($el.parentElement.querySelector('code').innerText, 'Data')"
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
                        <div class="bg-zinc-50 dark:bg-zinc-700/30 border border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg p-8 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400 mb-3">
                                <i data-lucide="database" class="w-6 h-6"></i>
                            </div>
                            <h4 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No Test Data Yet</h4>
                            <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-4">
                                Test data provides input values for this test case. Add data manually or generate with AI assistance.
                            </p>
                            <div class="flex flex-col sm:flex-row justify-center gap-3">
                                <button @click="showCreateDataModal = true" class="btn-secondary">
                                    <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Add Manually
                                </button>
                                <button @click="showAIDataModal = true" class="btn-primary">
                                    <i data-lucide="sparkles" class="w-4 h-4 mr-1"></i> Generate with AI
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Modals -->

        <!-- Create Script Modal - Improved -->
        <div x-cloak x-show="showCreateScriptModal" @keydown.escape.window="showCreateScriptModal = false"
            class="fixed inset-0 overflow-y-auto z-50" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-zinc-900/60 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
                @click="showCreateScriptModal = false"></div>
            <!-- Modal Panel -->
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative w-full max-w-4xl bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-8">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-indigo-50 dark:bg-indigo-900/20">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-indigo-900 dark:text-indigo-100 flex items-center">
                                <i data-lucide="file-code" class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400"></i>
                                Add Test Script
                            </h3>
                            <button @click="showCreateScriptModal = false"
                                class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Form -->
                    <form method="POST"
                        action="{{ route('dashboard.projects.test-cases.scripts.store', [$project->id, $testCase->id]) }}"
                        class="p-6">
                        @csrf
                        <input type="hidden" name="framework_type" x-model="manualScriptFramework">

                        <div class="space-y-6">
                            <!-- Framework Selection (First) -->
                            <div class="dropdown-container">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                    Framework <span class="text-red-500">*</span>
                                </label>
                                <button type="button"
                                    @click="manualScriptFrameworkDropdownOpen = !manualScriptFrameworkDropdownOpen"
                                    class="flex items-center justify-between w-full px-4 py-2.5 text-left bg-white dark:bg-zinc-700/50 border border-zinc-300 dark:border-zinc-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-800">
                                    <span class="flex items-center">
                                        <i x-show="manualScriptFramework === 'selenium-python'" data-lucide="brand-python" class="w-4 h-4 mr-2 text-zinc-500 dark:text-zinc-400"></i>
                                        <i x-show="manualScriptFramework === 'cypress'" data-lucide="brand-javascript" class="w-4 h-4 mr-2 text-zinc-500 dark:text-zinc-400"></i>
                                        <i x-show="manualScriptFramework === 'other'" data-lucide="file-question" class="w-4 h-4 mr-2 text-zinc-500 dark:text-zinc-400"></i>
                                        <span x-text="getLabel(manualScriptFrameworkOptions, manualScriptFramework)"></span>
                                    </span>
                                    <i data-lucide="chevron-down"
                                        class="w-5 h-5 text-zinc-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': manualScriptFrameworkDropdownOpen }"></i>
                                </button>
                                <div x-show="manualScriptFrameworkDropdownOpen"
                                    @click.away="manualScriptFrameworkDropdownOpen = false"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="dropdown-menu w-full"
                                    x-cloak>
                                    <div @click="selectOption('manualScriptFramework', 'selenium-python', 'manualScriptFrameworkDropdownOpen')"
                                        class="px-4 py-2.5 text-sm cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/20 flex items-center"
                                        :class="{'bg-indigo-50 dark:bg-indigo-900/30 font-medium text-indigo-700 dark:text-indigo-200': manualScriptFramework === 'selenium-python'}">
                                        <i data-lucide="brand-python" class="w-4 h-4 mr-2 text-zinc-500"></i>
                                        Selenium (Python)
                                    </div>
                                    <div @click="selectOption('manualScriptFramework', 'cypress', 'manualScriptFrameworkDropdownOpen')"
                                        class="px-4 py-2.5 text-sm cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/20 flex items-center"
                                        :class="{'bg-indigo-50 dark:bg-indigo-900/30 font-medium text-indigo-700 dark:text-indigo-200': manualScriptFramework === 'cypress'}">
                                        <i data-lucide="brand-javascript" class="w-4 h-4 mr-2 text-zinc-500"></i>
                                        Cypress (JavaScript)
                                    </div>
                                    <div @click="selectOption('manualScriptFramework', 'other', 'manualScriptFrameworkDropdownOpen')"
                                        class="px-4 py-2.5 text-sm cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/20 flex items-center"
                                        :class="{'bg-indigo-50 dark:bg-indigo-900/30 font-medium text-indigo-700 dark:text-indigo-200': manualScriptFramework === 'other'}">
                                        <i data-lucide="file-question" class="w-4 h-4 mr-2 text-zinc-500"></i>
                                        Other
                                    </div>
                                </div>
                                @error('framework_type')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Script Name -->
                            <div>
                                <label for="manual_script_name"
                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Script Name
                                    <span class="text-red-500">*</span></label>
                                <input type="text" id="manual_script_name" name="name" required
                                    class="form-input w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 shadow-sm"
                                    placeholder="e.g., Login Test, User Registration Test">
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Content with language-specific help -->
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="manual_script_content"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Script Content
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <span x-show="manualScriptFramework === 'selenium-python'"
                                          class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">
                                        Python with Selenium
                                    </span>
                                    <span x-show="manualScriptFramework === 'cypress'"
                                          class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">
                                        JavaScript with Cypress
                                    </span>
                                </div>

                                <!-- Framework-specific help text -->
                                <div x-show="manualScriptFramework === 'selenium-python'"
                                     class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg mb-3 text-xs text-blue-700 dark:text-blue-300">
                                    <p class="font-medium mb-1">Python Example:</p>
                                    <pre class="bg-white/70 dark:bg-black/20 p-2 rounded overflow-x-auto">import unittest
from selenium import webdriver
from selenium.webdriver.common.by import By

class LoginTest(unittest.TestCase):
    def setUp(self):
        self.driver = webdriver.Chrome()

    def test_login(self):
        # Your test code here
        pass

    def tearDown(self):
        self.driver.quit()</pre>
                                </div>

                                <div x-show="manualScriptFramework === 'cypress'"
                                     class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg mb-3 text-xs text-blue-700 dark:text-blue-300">
                                    <p class="font-medium mb-1">Cypress Example:</p>
                                    <pre class="bg-white/70 dark:bg-black/20 p-2 rounded overflow-x-auto">describe('Login Test', () => {
  beforeEach(() => {
    cy.visit('/login')
  })

  it('should log in successfully', () => {
    // Your test code here
  })
})</pre>
                                </div>

                                <textarea id="manual_script_content" name="script_content" rows="12" required
                                    class="form-textarea w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 font-mono text-sm shadow-sm"
                                    placeholder="Paste your test script code here..."></textarea>
                                @error('script_content')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end space-x-3">
                            <button type="button" @click="showCreateScriptModal = false"
                                class="btn-secondary">Cancel</button>
                            <button type="submit" class="btn-primary">
                                <i data-lucide="save" class="w-4 h-4 mr-1.5"></i> Save Script
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Create Test Data Modal - Improved -->
        <div x-cloak x-show="showCreateDataModal" @keydown.escape.window="showCreateDataModal = false"
            class="fixed inset-0 overflow-y-auto z-50" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-zinc-900/60 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
                @click="showCreateDataModal = false"></div>
            <!-- Modal Panel -->
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div @click.stop
                    class="relative w-full max-w-4xl bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-8">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 bg-teal-50 dark:bg-teal-900/20">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-teal-900 dark:text-teal-100 flex items-center">
                                <i data-lucide="database" class="w-5 h-5 mr-2 text-teal-600 dark:text-teal-400"></i>
                                Add Test Data
                            </h3>
                            <button @click="showCreateDataModal = false"
                                class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Form - Reorganized to put important fields first -->
                    <form method="POST"
                        action="{{ route('dashboard.projects.test-cases.data.store', [$project->id, $testCase->id]) }}"
                        class="p-6">
                        @csrf
                        <input type="hidden" name="format" x-model="manualDataFormat">

                        <div class="space-y-6">
                            <!-- Data Name - First field -->
                            <div>
                                <label for="data_name"
                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Data Name
                                    <span class="text-red-500">*</span></label>
                                <input type="text" id="data_name" name="name" required
                                    class="form-input w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 shadow-sm"
                                    placeholder="e.g., Valid User Credentials, Product Catalog Data">
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Usage Context - Second field -->
                            <div>
                                <label for="usage_context" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                    Usage Context <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="usage_context" name="usage_context" required
                                       class="form-input w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 shadow-sm"
                                       placeholder="e.g., 'Positive test case inputs' or 'Edge case scenario'">
                                @error('usage_context')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Format Dropdown - Third field -->
                            <div class="dropdown-container">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Format
                                    <span class="text-red-500">*</span></label>
                                <button type="button"
                                    @click="manualDataFormatDropdownOpen = !manualDataFormatDropdownOpen"
                                    class="flex items-center justify-between w-full px-4 py-2.5 text-left bg-white dark:bg-zinc-700/50 border border-zinc-300 dark:border-zinc-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 dark:focus:ring-offset-zinc-800">
                                    <span class="flex items-center">
                                        <i x-show="manualDataFormat === 'json'" data-lucide="braces" class="w-4 h-4 mr-2 text-zinc-500 dark:text-zinc-400"></i>
                                        <i x-show="manualDataFormat === 'csv'" data-lucide="table" class="w-4 h-4 mr-2 text-zinc-500 dark:text-zinc-400"></i>
                                        <i x-show="manualDataFormat === 'xml'" data-lucide="file-code" class="w-4 h-4 mr-2 text-zinc-500 dark:text-zinc-400"></i>
                                        <i x-show="manualDataFormat === 'plain'" data-lucide="file-text" class="w-4 h-4 mr-2 text-zinc-500 dark:text-zinc-400"></i>
                                        <i x-show="manualDataFormat === 'other'" data-lucide="file-question" class="w-4 h-4 mr-2 text-zinc-500 dark:text-zinc-400"></i>
                                        <span x-text="getLabel(manualDataFormatOptions, manualDataFormat)"></span>
                                    </span>
                                    <i data-lucide="chevron-down"
                                        class="w-5 h-5 text-zinc-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': manualDataFormatDropdownOpen }"></i>
                                </button>
                                <div x-show="manualDataFormatDropdownOpen"
                                    @click.away="manualDataFormatDropdownOpen = false"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="dropdown-menu w-full"
                                    x-cloak>
                                    <div @click="selectOption('manualDataFormat', 'json', 'manualDataFormatDropdownOpen')"
                                        class="px-4 py-2.5 text-sm cursor-pointer hover:bg-teal-50 dark:hover:bg-teal-900/20 flex items-center"
                                        :class="{'bg-teal-50 dark:bg-teal-900/30 font-medium text-teal-700 dark:text-teal-200': manualDataFormat === 'json'}">
                                        <i data-lucide="braces" class="w-4 h-4 mr-2 text-zinc-500"></i>
                                        JSON
                                    </div>
                                    <div @click="selectOption('manualDataFormat', 'csv', 'manualDataFormatDropdownOpen')"
                                        class="px-4 py-2.5 text-sm cursor-pointer hover:bg-teal-50 dark:hover:bg-teal-900/20 flex items-center"
                                        :class="{'bg-teal-50 dark:bg-teal-900/30 font-medium text-teal-700 dark:text-teal-200': manualDataFormat === 'csv'}">
                                        <i data-lucide="table" class="w-4 h-4 mr-2 text-zinc-500"></i>
                                        CSV
                                    </div>
                                    <div @click="selectOption('manualDataFormat', 'xml', 'manualDataFormatDropdownOpen')"
                                        class="px-4 py-2.5 text-sm cursor-pointer hover:bg-teal-50 dark:hover:bg-teal-900/20 flex items-center"
                                        :class="{'bg-teal-50 dark:bg-teal-900/30 font-medium text-teal-700 dark:text-teal-200': manualDataFormat === 'xml'}">
                                        <i data-lucide="file-code" class="w-4 h-4 mr-2 text-zinc-500"></i>
                                        XML
                                    </div>
                                    <div @click="selectOption('manualDataFormat', 'plain', 'manualDataFormatDropdownOpen')"
                                        class="px-4 py-2.5 text-sm cursor-pointer hover:bg-teal-50 dark:hover:bg-teal-900/20 flex items-center"
                                        :class="{'bg-teal-50 dark:bg-teal-900/30 font-medium text-teal-700 dark:text-teal-200': manualDataFormat === 'plain'}">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2 text-zinc-500"></i>
                                        Plain Text
                                    </div>
                                    <div @click="selectOption('manualDataFormat', 'other', 'manualDataFormatDropdownOpen')"
                                        class="px-4 py-2.5 text-sm cursor-pointer hover:bg-teal-50 dark:hover:bg-teal-900/20 flex items-center"
                                        :class="{'bg-teal-50 dark:bg-teal-900/30 font-medium text-teal-700 dark:text-teal-200': manualDataFormat === 'other'}">
                                        <i data-lucide="file-question" class="w-4 h-4 mr-2 text-zinc-500"></i>
                                        Other
                                    </div>
                                </div>
                                @error('format')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Content with format-specific examples -->
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <label for="data_content"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Data Content
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <span class="text-xs text-teal-600 dark:text-teal-400 font-medium" x-text="getLabel(manualDataFormatOptions, manualDataFormat) + ' Format'"></span>
                                </div>

                                <!-- Format examples -->
                                <div x-show="manualDataFormat === 'json'"
                                     class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg mb-3 text-xs text-blue-700 dark:text-blue-300">
                                    <pre class="bg-white/70 dark:bg-black/20 p-2 rounded overflow-x-auto">{
  "users": [
    { "username": "testuser1", "password": "password123" },
    { "username": "testuser2", "password": "password456" }
  ]
}</pre>
                                </div>

                                <div x-show="manualDataFormat === 'csv'"
                                     class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg mb-3 text-xs text-blue-700 dark:text-blue-300">
                                    <pre class="bg-white/70 dark:bg-black/20 p-2 rounded overflow-x-auto">username,password,email
testuser1,password123,user1@example.com
testuser2,password456,user2@example.com</pre>
                                </div>

                                <div x-show="manualDataFormat === 'xml'"
                                     class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg mb-3 text-xs text-blue-700 dark:text-blue-300">
                                    <pre class="bg-white/70 dark:bg-black/20 p-2 rounded overflow-x-auto">&lt;users&gt;
  &lt;user&gt;
    &lt;username&gt;testuser1&lt;/username&gt;
    &lt;password&gt;password123&lt;/password&gt;
  &lt;/user&gt;
  &lt;user&gt;
    &lt;username&gt;testuser2&lt;/username&gt;
    &lt;password&gt;password456&lt;/password&gt;
  &lt;/user&gt;
&lt;/users&gt;</pre>
                                </div>

                                <textarea id="data_content" name="content" rows="10" required
                                    x-bind:class="{'language-json': manualDataFormat === 'json',
                                                  'language-csv': manualDataFormat === 'csv',
                                                  'language-xml': manualDataFormat === 'xml',
                                                  'language-plaintext': manualDataFormat === 'plain'}"
                                    class="form-textarea w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 font-mono text-sm shadow-sm"
                                    placeholder="Paste your test data here..."></textarea>
                                @error('content')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Sensitive Data Toggle -->
                            <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" id="is_sensitive" name="is_sensitive" value="1"
                                            class="form-checkbox h-5 w-5 rounded text-red-600 border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700/50 focus:ring-red-500 dark:focus:ring-offset-zinc-800 shadow-sm">
                                    </div>
                                    <div class="ml-3">
                                        <label for="is_sensitive" class="font-medium text-zinc-700 dark:text-zinc-300">Contains Sensitive Data</label>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                                            Mark this if the data contains credentials, personal information, or other confidential data.
                                            Sensitive data will be handled with extra precautions.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end space-x-3">
                            <button type="button" @click="showCreateDataModal = false" class="btn-secondary">Cancel</button>
                            <button type="submit" class="btn-primary">
                                <i data-lucide="save" class="w-4 h-4 mr-1.5"></i> Save Test Data
                            </button>
                        </div>
                    </form>
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
                                <div class="flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30">
                                    <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600 dark:text-red-500"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Delete Test Case</h3>
                                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                    Are you sure you want to delete test case "<strong class="font-semibold">{{ $testCase->title }}</strong>"?
                                    This action cannot be undone. Associated test scripts and data connections will also be removed.
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
        .form-input:focus, .form-textarea:focus {
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

                // Script Modal
                showCreateScriptModal: false,
                manualScriptFramework: 'selenium-python', // Default value
                manualScriptFrameworkDropdownOpen: false,
                manualScriptFrameworkOptions: [
                    {
                        value: 'selenium-python',
                        label: 'Selenium (Python)',
                        icon: 'brand-python'
                    },
                    {
                        value: 'cypress',
                        label: 'Cypress (JavaScript)',
                        icon: 'brand-javascript'
                    },
                    {
                        value: 'other',
                        label: 'Other',
                        icon: 'file-question'
                    }
                ],

                // AI Script
                showAIScriptModal: false,
                aiScriptFramework: 'selenium-python',
                aiScriptFrameworkDropdownOpen: false,
                aiScriptFrameworkOptions: [
                    {
                        value: 'selenium-python',
                        label: 'Selenium (Python)',
                        icon: 'brand-python'
                    },
                    {
                        value: 'cypress',
                        label: 'Cypress (JavaScript)',
                        icon: 'brand-javascript'
                    },
                    {
                        value: 'other',
                        label: 'Other',
                        icon: 'file-question'
                    }
                ],

                aiScriptPrompt: '',
                aiScriptLoading: false,
                aiScriptError: null,
                aiScriptResponse: null,

                // Data Modal
                showCreateDataModal: false,
                manualDataFormat: 'json', // Default value
                manualDataFormatDropdownOpen: false,
                manualDataFormatOptions: [{
                        value: 'json',
                        label: 'JSON',
                        icon: 'braces'
                    },
                    {
                        value: 'csv',
                        label: 'CSV',
                        icon: 'table'
                    },
                    {
                        value: 'xml',
                        label: 'XML',
                        icon: 'file-code'
                    },
                    {
                        value: 'plain',
                        label: 'Plain Text',
                        icon: 'file-text'
                    },
                    {
                        value: 'other',
                        label: 'Other',
                        icon: 'file-question'
                    }
                ],

                // AI Data
                showAIDataModal: false,
                aiDataFormat: 'json',
                aiDataFormatDropdownOpen: false,
                aiDataFormatOptions: [
                    {
                        value: 'json',
                        label: 'JSON',
                        icon: 'braces'
                    },
                    {
                        value: 'csv',
                        label: 'CSV',
                        icon: 'table'
                    },
                    {
                        value: 'xml',
                        label: 'XML',
                        icon: 'file-code'
                    },
                    {
                        value: 'plain',
                        label: 'Plain Text',
                        icon: 'file-text'
                    },
                    {
                        value: 'other',
                        label: 'Other',
                        icon: 'file-question'
                    }
                ],
                aiDataPrompt: '',
                aiDataLoading: false,
                aiDataError: null,
                aiDataResponse: null,

                init() {
                    this.$nextTick(() => {
                        // Initialize icons
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                        // Initialize Prism highlighting
                        this.highlightCode();

                        // Set initial active tab from URL hash if present
                        const hash = window.location.hash;
                        if (hash) {
                            const tab = hash.replace('#', '');
                            if (['details', 'scripts', 'testdata'].includes(tab)) {
                                this.activeTab = tab;
                            }
                        }
                    });

                    // Update URL hash when tab changes
                    this.$watch('activeTab', (value) => {
                        // Update hash without page jump
                        if (history.pushState) {
                            history.pushState(null, null, `#${value}`);
                        } else {
                            window.location.hash = `#${value}`;
                        }

                        // Re-highlight code when switching to code tabs
                        if (['scripts', 'testdata'].includes(value)) {
                            this.highlightCode();
                        }
                        // Reinitialize icons in case new ones became visible
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                        });
                    });

                    // Watch the script format changes to auto-highlight
                    this.$watch('manualScriptFramework', () => {
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                        });
                    });

                    // Watch the data format changes to auto-highlight
                    this.$watch('manualDataFormat', () => {
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                            // Highlight code examples when format changes
                            this.highlightCode();
                        });
                    });
                },

                getLabel(optionsArray, value) {
                    const option = optionsArray.find(opt => opt.value === value);
                    return option ? option.label : 'Select...';
                },

                // Helper to set value and close dropdown
                selectOption(optionType, value, dropdownFlag) {
                    this[optionType] = value;
                    this[dropdownFlag] = false;
                    // Re-initialize icons if needed after selection changes display
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                        // Refresh syntax highlighting on format change
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
                        () => this.showNotificationMessage(`${type} copied to clipboard!`, 'success'),
                        (err) => this.showNotificationMessage(`Failed to copy ${type}: ${err}`, 'error')
                    );
                },

                refreshPage() {
                    // Reload the page to show the newly added item in the list
                    window.location.reload();
                },

                // Centralized notification handling
                showNotificationMessage(message, type = 'success') {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            message,
                            type
                        }
                    }));
                }
            }));

            // Separate Alpine component for notifications
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

                        // Clear previous timeout if exists
                        if (this.timeout) {
                            clearTimeout(this.timeout);
                        }

                        // Auto-hide after 5 seconds
                        this.timeout = setTimeout(() => {
                            this.show = false;
                        }, 5000);

                        // Re-init icons when notification appears
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
