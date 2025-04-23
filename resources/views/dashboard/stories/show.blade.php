@extends('layouts.dashboard')

@section('title', $story->title)

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.stories.indexAll') }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">Stories</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">{{ \Illuminate\Support\Str::limit($story->title, 30) }}</span>
    </li>
@endsection

@section('content')
<div class="h-full space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                {{ $story->title }}
            </h1>
            <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                <span class="inline-flex items-center">
                    <span class="font-medium text-zinc-700 dark:text-zinc-300 mr-1">Source:</span>
                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-medium rounded-full
                        {{ match($story->source) {
                            'jira' => 'bg-blue-100/80 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                            'github' => 'bg-purple-100/80 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                            'azure' => 'bg-cyan-100/80 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300',
                            'manual' => 'bg-zinc-100/80 text-zinc-800 dark:bg-zinc-700/30 dark:text-zinc-300',
                            default => 'bg-zinc-100/80 text-zinc-800 dark:bg-zinc-700/30 dark:text-zinc-300',
                        } }}">
                        {{ ucfirst($story->source) }}
                    </span>
                </span>
                @if($story->external_id)
                <span class="inline-flex items-center">
                    <i data-lucide="hash" class="w-4 h-4 mr-1 text-zinc-400 dark:text-zinc-500"></i>
                    <span class="font-mono">{{ $story->external_id }}</span>
                </span>
                @endif
                <span class="inline-flex items-center">
                    <i data-lucide="clock" class="w-4 h-4 mr-1 text-zinc-400 dark:text-zinc-500"></i>
                    {{ $story->updated_at->format('M d, Y H:i') }}
                </span>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('dashboard.stories.edit', $story->id) }}"
               class="btn-secondary inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-white/50 dark:bg-zinc-800/50 border border-zinc-300/70 dark:border-zinc-600/50 hover:bg-zinc-50/70 dark:hover:bg-zinc-700/50 shadow-sm transition-all">
                <i data-lucide="edit" class="w-4 h-4"></i> Edit
            </a>
            <button type="button"
                onclick="if(confirm('Are you sure you want to delete this story?')) { document.getElementById('delete-form').submit(); }"
                class="btn-danger inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white shadow-sm transition-all">
                <i data-lucide="trash-2" class="w-4 h-4"></i> Delete
            </button>
            <form id="delete-form" action="{{ route('dashboard.stories.destroy', $story->id) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <!-- Story Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description Card -->
            <div class="bg-white dark:bg-zinc-800/50 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 backdrop-blur-sm">
                <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/30 dark:bg-zinc-800/30">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="align-left" class="w-5 h-5 text-zinc-500 dark:text-zinc-400"></i>
                        Description
                    </h2>
                </div>
                <div class="p-6">
                    @if($story->description)
                        <div class="prose dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300">
                            {!! nl2br(e($story->description)) !!}
                        </div>
                    @else
                        <div class="text-center p-6">
                            <div class="mx-auto w-12 h-12 rounded-full bg-zinc-100/50 dark:bg-zinc-700/20 flex items-center justify-center mb-3">
                                <i data-lucide="file-text" class="w-6 h-6 text-zinc-400 dark:text-zinc-500"></i>
                            </div>
                            <p class="text-zinc-500 dark:text-zinc-400 italic">No description provided</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Associated Test Cases -->
            <div class="bg-white dark:bg-zinc-800/50 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 backdrop-blur-sm">
                <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/30 dark:bg-zinc-800/30">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="link" class="w-5 h-5 text-zinc-500 dark:text-zinc-400"></i>
                        Associated Test Cases
                    </h2>
                </div>
                <div class="p-6">
                    @if($story->testCases && $story->testCases->count() > 0)
                        <div class="space-y-4">
                            @foreach($story->testCases as $testCase)
                                <div class="group relative bg-zinc-50/30 dark:bg-zinc-700/20 p-4 rounded-xl border border-zinc-200/50 dark:border-zinc-700/50 hover:border-indigo-200/50 dark:hover:border-indigo-700/50 transition-all">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 space-y-2">
                                            <h3 class="text-md font-medium text-zinc-900 dark:text-white">
                                                <a href="{{ route('dashboard.projects.test-cases.show', [
                                                    'project' => $testCase->testSuite->project_id,
                                                    'test_case' => $testCase->id
                                                ]) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                                    {{ $testCase->title }}
                                                </a>
                                            </h3>
                                            <div class="flex flex-wrap gap-3 text-sm">
                                                <a href="{{ route('dashboard.projects.show', $testCase->testSuite->project_id) }}"
                                                   class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:underline">
                                                    <i data-lucide="folder" class="w-4 h-4 mr-1"></i>
                                                    {{ $testCase->testSuite->project->name }}
                                                </a>
                                                <a href="{{ route('dashboard.projects.test-suites.show', [
                                                    'project' => $testCase->testSuite->project_id,
                                                    'test_suite' => $testCase->testSuite->id
                                                ]) }}"
                                                   class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:underline">
                                                    <i data-lucide="layers" class="w-4 h-4 mr-1"></i>
                                                    {{ $testCase->testSuite->name }}
                                                </a>
                                            </div>
                                            @if($testCase->priority)
                                                <div class="mt-1">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                                        {{ match($testCase->priority) {
                                                            'high' => 'bg-red-100/80 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                                            'medium' => 'bg-yellow-100/80 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                            'low' => 'bg-green-100/80 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                                            default => 'bg-zinc-100/80 text-zinc-800 dark:bg-zinc-700/30 dark:text-zinc-300',
                                                        } }}">
                                                        <i data-lucide="alert-circle" class="w-4 h-4 mr-1"></i>
                                                        {{ ucfirst($testCase->priority) }} Priority
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <div class="mx-auto w-12 h-12 rounded-full bg-zinc-100/50 dark:bg-zinc-700/20 flex items-center justify-center mb-3">
                                <i data-lucide="link-off" class="w-6 h-6 text-zinc-400 dark:text-zinc-500"></i>
                            </div>
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-white">No test cases associated</h3>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                This story is not linked to any test cases yet.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Metadata Card -->
            <div class="bg-white dark:bg-zinc-800/50 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 backdrop-blur-sm">
                <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/30 dark:bg-zinc-800/30">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="file-text" class="w-5 h-5 text-zinc-500 dark:text-zinc-400"></i>
                        Metadata
                    </h2>
                </div>
                <div class="p-6">
                    <dl class="divide-y divide-zinc-200/50 dark:divide-zinc-700/50">
                        <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400 flex items-center gap-1">
                                <i data-lucide="calendar-plus" class="w-4 h-4"></i> Created
                            </dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-white sm:mt-0 sm:col-span-2 font-medium">
                                {{ $story->created_at->format('M d, Y H:i') }}
                            </dd>
                        </div>
                        <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400 flex items-center gap-1">
                                <i data-lucide="calendar-edit" class="w-4 h-4"></i> Modified
                            </dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-white sm:mt-0 sm:col-span-2 font-medium">
                                {{ $story->updated_at->format('M d, Y H:i') }}
                            </dd>
                        </div>
                        @if($story->external_id)
                        <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400 flex items-center gap-1">
                                <i data-lucide="hash" class="w-4 h-4"></i> External ID
                            </dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-white sm:mt-0 sm:col-span-2 font-mono">
                                {{ $story->external_id }}
                            </dd>
                        </div>
                        @endif

                        @if($story->metadata && is_array($story->metadata) && count($story->metadata) > 0)
                            @foreach($story->metadata as $key => $value)
                                @if(!is_array($value) && !is_object($value))
                                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                        {{ ucwords(str_replace('_', ' ', $key)) }}
                                    </dt>
                                    <dd class="mt-1 text-sm text-zinc-900 dark:text-white sm:mt-0 sm:col-span-2 break-words">
                                        {{ $value }}
                                    </dd>
                                </div>
                                @endif
                            @endforeach
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="bg-white dark:bg-zinc-800/50 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 backdrop-blur-sm">
                <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/30 dark:bg-zinc-800/30">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="zap" class="w-5 h-5 text-zinc-500 dark:text-zinc-400"></i>
                        Actions
                    </h2>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('dashboard.stories.edit', $story->id) }}"
                       class="w-full btn-secondary inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-white/50 dark:bg-zinc-800/50 border border-zinc-300/70 dark:border-zinc-600/50 hover:bg-zinc-50/70 dark:hover:bg-zinc-700/50 shadow-sm transition-all">
                        <i data-lucide="edit" class="w-4 h-4"></i> Edit Story
                    </a>
                    <a href="{{ route('dashboard.stories.indexAll') }}"
                       class="w-full btn-secondary inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-white/50 dark:bg-zinc-800/50 border border-zinc-300/70 dark:border-zinc-600/50 hover:bg-zinc-50/70 dark:hover:bg-zinc-700/50 shadow-sm transition-all">
                        <i data-lucide="list" class="w-4 h-4"></i> All Stories
                    </a>
                    @if($story->testCases && $story->testCases->count() > 0)
                        @php
                            $firstTestCase = $story->testCases->first();
                            $projectId = $firstTestCase->testSuite->project_id;
                        @endphp
                        <a href="{{ route('dashboard.projects.test-cases.index', $projectId) }}"
                           class="w-full btn-secondary inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-white/50 dark:bg-zinc-800/50 border border-zinc-300/70 dark:border-zinc-600/50 hover:bg-zinc-50/70 dark:hover:bg-zinc-700/50 shadow-sm transition-all">
                            <i data-lucide="check-square" class="w-4 h-4"></i> View Test Cases
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
