@extends('layouts.dashboard')

@section('title', 'Edit Story - ' . $story->title)

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.stories.indexAll') }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">Stories</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.stories.show', $story->id) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">{{ \Illuminate\Support\Str::limit($story->title, 30) }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Edit</span>
    </li>
@endsection

@section('content')
<div class="h-full space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                Edit Story
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Update story details and information
            </p>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white dark:bg-zinc-800/50 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 backdrop-blur-sm">
        <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/30 dark:bg-zinc-800/30">
            <h2 class="text-lg font-medium text-zinc-900 dark:text-white flex items-center gap-2">
                <i data-lucide="file-text" class="w-5 h-5 text-zinc-500 dark:text-zinc-400"></i>
                Story Information
            </h2>
        </div>

        <form action="{{ route('dashboard.stories.update', $story->id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2 flex items-center gap-1">
                        <i data-lucide="text" class="w-4 h-4 text-zinc-400"></i>
                        Title <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title', $story->title) }}"
                        class="w-full px-4 py-2.5 rounded-lg border border-zinc-300/70 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-700/50 text-zinc-800 dark:text-zinc-200 placeholder-zinc-400 dark:placeholder-zinc-500 shadow-sm
                               focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all
                               @error('title') border-red-500 dark:border-red-500 @enderror"
                        required>
                    @error('title')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Source -->
                <div>
                    <label for="source" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2 flex items-center gap-1">
                        <i data-lucide="folder" class="w-4 h-4 text-zinc-400"></i>
                        Source <span class="text-red-600">*</span>
                    </label>
                    <select name="source" id="source" class="js-source-select w-full" required>
                        <option value="manual" {{ old('source', $story->source) == 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="jira" {{ old('source', $story->source) == 'jira' ? 'selected' : '' }}>Jira</option>
                        <option value="github" {{ old('source', $story->source) == 'github' ? 'selected' : '' }}>GitHub</option>
                        <option value="azure" {{ old('source', $story->source) == 'azure' ? 'selected' : '' }}>Azure</option>
                    </select>
                    @error('source')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- External ID -->
                <div>
                    <label for="external_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2 flex items-center gap-1">
                        <i data-lucide="hash" class="w-4 h-4 text-zinc-400"></i>
                        External ID
                    </label>
                    <input type="text" name="external_id" id="external_id" value="{{ old('external_id', $story->external_id) }}"
                        class="w-full px-4 py-2.5 rounded-lg border border-zinc-300/70 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-700/50 text-zinc-800 dark:text-zinc-200 placeholder-zinc-400 dark:placeholder-zinc-500 shadow-sm
                               focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                        @error('external_id') border-red-500 dark:border-red-500 @enderror>
                    <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                        Reference ID from external system (e.g., JIRA-123)
                    </p>
                    @error('external_id')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2 flex items-center gap-1">
                        <i data-lucide="align-left" class="w-4 h-4 text-zinc-400"></i>
                        Description
                    </label>
                    <textarea name="description" id="description" rows="6"
                        class="w-full px-4 py-2.5 rounded-lg border border-zinc-300/70 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-700/50 text-zinc-800 dark:text-zinc-200 placeholder-zinc-400 dark:placeholder-zinc-500 shadow-sm
                               focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all
                               @error('description') border-red-500 dark:border-red-500 @enderror">{{ old('description', $story->description) }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Metadata -->
                @if($story->metadata && is_array($story->metadata) && count($story->metadata) > 0)
                <div class="md:col-span-2">
                    <div class="rounded-xl bg-zinc-100/30 dark:bg-zinc-700/20 p-4 border border-zinc-200/70 dark:border-zinc-700/50">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i data-lucide="database" class="h-5 w-5 text-indigo-600 dark:text-indigo-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Additional Metadata</h3>
                                <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach($story->metadata as $key => $value)
                                            @if(!is_array($value) && !is_object($value))
                                                <li>{{ ucwords(str_replace('_', ' ', $key)) }}: <span class="font-mono">{{ $value }}</span></li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400 italic">
                                    Metadata is managed programmatically and cannot be edited directly.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="border-t border-zinc-200/50 dark:border-zinc-700/50 pt-6 flex flex-wrap gap-3 justify-between">
                <a href="{{ route('dashboard.stories.show', $story->id) }}"
                   class="btn-secondary px-4 py-2.5 rounded-lg bg-white/50 dark:bg-zinc-700/50 border border-zinc-300/70 dark:border-zinc-600/50 hover:bg-zinc-50/70 dark:hover:bg-zinc-600/50 shadow-sm transition-all">
                    Cancel
                </a>
                <div class="flex flex-wrap gap-3">
                    <button type="submit"
                            class="btn-primary px-4 py-2.5 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white shadow-sm transition-all">
                        Update Story
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="bg-red-50/30 dark:bg-red-900/10 shadow-sm rounded-xl border border-red-200/70 dark:border-red-800/50">
        <div class="px-6 py-4 border-b border-red-200/50 dark:border-red-800/20 bg-red-100/20 dark:bg-red-900/10">
            <h2 class="text-lg font-medium text-red-800 dark:text-red-400 flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                Danger Zone
            </h2>
        </div>
        <div class="p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0 text-red-400 dark:text-red-500">
                    <i data-lucide="trash-2" class="h-5 w-5"></i>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Delete this story</h3>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        Once you delete a story, there is no going back. This action cannot be undone.
                    </p>
                    <div class="mt-3">
                        <button type="button"
                            onclick="if(confirm('Are you sure you want to delete this story? This action cannot be undone.')) { document.getElementById('delete-form').submit(); }"
                            class="btn-danger px-4 py-2.5 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white shadow-sm transition-all">
                            Delete Story
                        </button>
                        <form id="delete-form" action="{{ route('dashboard.stories.destroy', $story->id) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<style>
    .ts-control {
        @apply px-4 py-2.5 rounded-lg border border-zinc-300/70 dark:border-zinc-600/50 bg-white/50 dark:bg-zinc-700/50 shadow-sm;
    }
    .dark .ts-dropdown {
        @apply bg-zinc-800 border-zinc-700 rounded-lg;
    }
    .dark .ts-dropdown .option:hover {
        @apply bg-zinc-700;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect('#source', {
            create: false,
            render: {
                option: function(data, escape) {
                    return `<div class="p-2 hover:bg-zinc-100 dark:hover:bg-zinc-700">${escape(data.text)}</div>`;
                }
            }
        });
    });
</script>
@endpush
