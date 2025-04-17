@php
    /**
     * @var \App\Models\Project $project
     * @var \App\Models\TestCase $testCase
     * @var \Illuminate\Database\Eloquent\Collection $testSuites
     * @var \App\Models\TestSuite $selectedSuite
     */
    $pageTitle = 'Edit Test Case: ' . $testCase->title;

    // Unpack steps and tags from JSON if needed
    $steps = is_array($testCase->steps) ? $testCase->steps : json_decode($testCase->steps, true);
    $steps = is_array($steps) ? $steps : [];

    $tags = is_array($testCase->tags) ? $testCase->tags : json_decode($testCase->tags, true);
    $tags = is_array($tags) ? $tags : [];
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
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.index', $project->id) }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">Test Cases</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">{{ Str::limit($testCase->title, 30) }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Edit</span>
    </li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">{{ $pageTitle }}</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Update the test case details.</p>
    </div>

    <!-- Placeholder Message -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-8 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-700 mb-4">
            <i data-lucide="file-edit" class="w-8 h-8 text-zinc-500 dark:text-zinc-400"></i>
        </div>
        <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Edit Form Under Development</h2>
        <p class="text-zinc-600 dark:text-zinc-400 max-w-md mx-auto mb-6">
            The edit test case form is currently being implemented. Please check back later.
        </p>
        <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}" class="btn-primary inline-flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Return to Test Case
        </a>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-primary { @apply inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors; }
</style>
@endpush
