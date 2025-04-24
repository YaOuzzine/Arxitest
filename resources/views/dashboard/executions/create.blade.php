@extends('layouts.dashboard')

@section('title', 'Run Test')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.executions.index') }}" class="text-zinc-700 dark:text-zinc-300">Executions</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">New Execution</span>
    </li>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6">Run Test</h1>
            @if (session('error'))
                <div
                    class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800/50 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg mb-6">
                    {{ session('error') }}
                </div>
            @endif
            <form action="{{ route('dashboard.executions.store') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    {{-- Test Script Selection --}}
                    <div>
                        <label for="script_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Test Script
                        </label>
                        <select id="script_id" name="script_id"
                            class="w-full border-zinc-300 dark:border-zinc-700 rounded-lg shadow-sm dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select a script</option>
                            @foreach ($scripts as $script)
                                <option value="{{ $script->id }}">
                                    {{ $script->name }} ({{ $script->testCase->title ?? 'No test case' }})
                                </option>
                            @endforeach
                        </select>
                        @error('script_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Environment Selection --}}
                    <div>
                        <label for="environment_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Environment
                        </label>
                        <select id="environment_id" name="environment_id"
                            class="w-full border-zinc-300 dark:border-zinc-700 rounded-lg shadow-sm dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select an environment</option>
                            @foreach ($environments as $environment)
                                <option value="{{ $environment->id }}">
                                    {{ $environment->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('environment_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex justify-end">
                        <a href="{{ route('dashboard.executions.index') }}" class="btn-secondary mr-3">
                            Cancel
                        </a>
                        <button type="submit" class="btn-primary">
                            <i data-lucide="play" class="w-5 h-5 mr-1"></i>
                            Run Test
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
