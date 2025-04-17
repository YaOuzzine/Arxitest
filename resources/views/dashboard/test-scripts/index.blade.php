@php
    /**
     * @var \App\Models\Project $project
     * @var \App\Models\TestCase $testCase
     * @var \App\Models\TestSuite $testSuite
     * @var \Illuminate\Database\Eloquent\Collection $testData
     */
    $pageTitle = 'Test Data for: ' . $testCase->title;
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
        <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">{{ Str::limit($testCase->title, 30) }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Test Data</span>
    </li>
@endsection

@section('content')
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">{{ $pageTitle }}</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Manage test data for this test case.
                </p>
            </div>
            <div class="flex flex-shrink-0 gap-2">
                <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}" class="btn-primary">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Test Case
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-700 mb-4">
                <i data-lucide="database" class="w-8 h-8 text-zinc-500 dark:text-zinc-400"></i>
            </div>
            <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">View Under Development</h2>
            <p class="text-zinc-600 dark:text-zinc-400 max-w-md mx-auto mb-6">
                The standalone test data view is currently being implemented. Please check back later.
            </p>
            <p class="text-zinc-600 dark:text-zinc-400 max-w-md mx-auto mb-6">
                In the meantime, you can manage test data directly from the Test Case page.
            </p>
            <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}#testdata" class="btn-primary inline-flex items-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Return to Test Case Data
            </a>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .btn-primary { @apply inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors; }
</style>
@endpush
