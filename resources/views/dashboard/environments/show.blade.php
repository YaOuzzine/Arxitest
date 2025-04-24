@extends('layouts.dashboard')

@section('title', $environment->name . ' Environment')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.environments.index') }}" class="text-zinc-700 dark:text-zinc-300">Environments</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">{{ $environment->name }}</span>
    </li>
@endsection

@section('content')
<div x-data="{ showDeleteModal: false }">
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white flex items-center">
                {{ $environment->name }}
                @if($environment->is_global)
                    <span class="ml-3 px-2.5 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                        Global
                    </span>
                @endif
                @if($environment->is_active)
                    <span class="ml-2 px-2.5 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                        Active
                    </span>
                @else
                    <span class="ml-2 px-2.5 py-0.5 text-xs font-medium rounded-full bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300">
                        Inactive
                    </span>
                @endif
            </h1>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('dashboard.environments.edit', $environment->id) }}" class="btn-secondary">
                <i data-lucide="edit-3" class="w-5 h-5 mr-1.5"></i>
                Edit
            </a>
            <button @click="showDeleteModal = true" class="btn-danger">
                <i data-lucide="trash-2" class="w-5 h-5 mr-1.5"></i>
                Delete
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800/50 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800/50 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            {{-- Environment Variables --}}
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Environment Variables</h2>
                </div>

                <div class="p-6">
                    @if(!is_array($environment->configuration) || empty($environment->configuration))
                        <div class="text-center py-6 text-zinc-500 dark:text-zinc-400">
                            <i data-lucide="file-cog" class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-500 mb-3"></i>
                            <p>No environment variables defined for this environment.</p>
                        </div>
                    @else
                        <div class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-100 dark:bg-zinc-700/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Variable Name
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                            Value
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-zinc-50 dark:bg-zinc-700/30 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($environment->configuration as $key => $value)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                                {{ $key }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-zinc-700 dark:text-zinc-300 font-mono">
                                                {{ $value }}
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

        <div>
            {{-- Information Card --}}
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Details</h2>
                </div>

                <div class="p-6">
                    <dl class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Created</dt>
                            <dd class="text-sm text-zinc-900 dark:text-white ml-2 text-right">{{ $environment->created_at->format('M d, Y h:i A') }}</dd>
                        </div>

                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Last Updated</dt>
                            <dd class="text-sm text-zinc-900 dark:text-white ml-2 text-right">{{ $environment->updated_at->format('M d, Y h:i A') }}</dd>
                        </div>

                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">ID</dt>
                            <dd class="text-sm text-zinc-900 dark:text-white ml-2 text-right font-mono">{{ $environment->id }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Project Association --}}
            @if(!$environment->is_global)
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Associated Projects</h2>
                    </div>

                    <div class="p-6">
                        @if($environment->projects->isEmpty())
                            <div class="text-center py-6 text-zinc-500 dark:text-zinc-400">
                                <i data-lucide="folder" class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-500 mb-3"></i>
                                <p>No projects associated with this environment.</p>
                            </div>
                        @else
                            <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($environment->projects as $project)
                                    <li class="py-3">
                                        <a href="{{ route('dashboard.projects.show', $project->id) }}" class="flex items-center hover:text-indigo-600 dark:hover:text-indigo-400">
                                            <i data-lucide="folder" class="w-5 h-5 text-zinc-400 dark:text-zinc-500 mr-2"></i>
                                            <span class="text-zinc-800 dark:text-zinc-200">{{ $project->name }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Delete Modal --}}
    <div x-show="showDeleteModal" class="fixed inset-0 flex items-center justify-center z-50" x-cloak>
        <div class="absolute inset-0 bg-black bg-opacity-50" @click="showDeleteModal = false"></div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg p-6 max-w-md mx-auto relative z-10">
            <div class="flex items-center justify-center mb-4 text-red-600 dark:text-red-500">
                <i data-lucide="alert-triangle" class="w-12 h-12"></i>
            </div>
            <h3 class="text-xl font-bold text-zinc-900 dark:text-white text-center mb-2">Delete Environment</h3>
            <p class="text-zinc-600 dark:text-zinc-400 text-center mb-6">
                Are you sure you want to delete the environment <span class="font-medium">{{ $environment->name }}</span>? This action cannot be undone.
            </p>
            <div class="flex justify-center space-x-4">
                <button @click="showDeleteModal = false" class="btn-secondary">
                    Cancel
                </button>
                <form action="{{ route('dashboard.environments.destroy', $environment->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">
                        Delete Environment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
