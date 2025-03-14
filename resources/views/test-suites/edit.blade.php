@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Navigation -->
    <div class="flex items-center text-sm text-gray-500 mb-4">
        <a href="{{ route('projects.index') }}" class="hover:text-blue-600">Projects</a>
        <svg class="h-4 w-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
        <a href="{{ route('projects.show', $testSuite->project->id) }}" class="hover:text-blue-600">{{ $testSuite->project->name }}</a>
        <svg class="h-4 w-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
        <a href="{{ route('test-suites.show', $testSuite->id) }}" class="hover:text-blue-600">{{ $testSuite->name }}</a>
        <svg class="h-4 w-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-gray-700">Edit</span>
    </div>

    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Test Suite</h1>
        <p class="text-gray-600">Update test suite details and settings</p>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <form action="{{ route('test-suites.update', $testSuite->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Suite Name</label>
                    <input type="text" name="name" id="name" class="border border-gray-300 rounded-md w-full py-2 px-3 @error('name') border-red-500 @enderror" value="{{ old('name', $testSuite->name) }}" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="3" class="border border-gray-300 rounded-md w-full py-2 px-3 @error('description') border-red-500 @enderror">{{ old('description', $testSuite->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="project_id" class="block text-sm font-medium text-gray-700 mb-1">Project</label>

                    <div class="flex items-center p-4 border border-gray-200 rounded-md bg-gray-50">
                        <input type="hidden" name="project_id" value="{{ $testSuite->project_id }}">
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $testSuite->project->name }}</div>
                            <div class="text-xs text-gray-500">
                                Team: {{ $testSuite->project->team->name }}
                            </div>
                        </div>
                        <div class="ml-auto text-xs text-gray-500">
                            Changing projects is not supported
                        </div>
                    </div>
                </div>

                <!-- Suite Settings -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Settings</label>
                    <div class="bg-gray-50 p-4 rounded-md">
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" name="settings[parallel_execution]" id="parallel_execution" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" {{ isset($testSuite->settings['parallel_execution']) && $testSuite->settings['parallel_execution'] ? 'checked' : '' }}>
                                <label for="parallel_execution" class="ml-2 block text-sm text-gray-700">
                                    Enable parallel script execution
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="settings[data_driven]" id="data_driven" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" {{ isset($testSuite->settings['data_driven']) && $testSuite->settings['data_driven'] ? 'checked' : '' }}>
                                <label for="data_driven" class="ml-2 block text-sm text-gray-700">
                                    Enable data-driven testing
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="settings[retry_on_failure]" id="retry_on_failure" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" {{ isset($testSuite->settings['retry_on_failure']) && $testSuite->settings['retry_on_failure'] ? 'checked' : '' }}>
                                <label for="retry_on_failure" class="ml-2 block text-sm text-gray-700">
                                    Retry tests on failure
                                </label>
                            </div>

                            <div class="pl-6" id="retry_options" {{ isset($testSuite->settings['retry_on_failure']) && $testSuite->settings['retry_on_failure'] ? '' : 'hidden' }}>
                                <label for="settings[retry_count]" class="block text-sm text-gray-700 mb-1">Retry count</label>
                                <select name="settings[retry_count]" id="retry_count" class="border border-gray-300 rounded-md py-1 px-2 text-sm">
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ isset($testSuite->settings['retry_count']) && $testSuite->settings['retry_count'] == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="settings[capture_screenshots]" id="capture_screenshots" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" {{ isset($testSuite->settings['capture_screenshots']) && $testSuite->settings['capture_screenshots'] ? 'checked' : '' }}>
                                <label for="capture_screenshots" class="ml-2 block text-sm text-gray-700">
                                    Capture screenshots during execution
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between">
                    <a href="{{ route('test-suites.show', $testSuite->id) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg">
                        Cancel
                    </a>
                    <div>
                        <form action="{{ route('test-suites.destroy', $testSuite->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this test suite and all associated scripts?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg mr-2">
                                Delete Suite
                            </button>
                        </form>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Test Scripts Info -->
    <div class="mt-8 bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-800">Test Scripts</h2>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="text-sm text-gray-600">
                    This test suite contains <span class="font-bold">{{ $testSuite->testScripts->count() }}</span> test scripts.
                </div>
                <a href="{{ route('test-suites.show', $testSuite->id) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    Manage Scripts
                </a>
            </div>

            @if($testSuite->testScripts->isNotEmpty())
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($testSuite->testScripts->take(6) as $script)
                        <div class="border border-gray-200 rounded-md p-3 hover:bg-gray-50">
                            <div class="text-sm font-medium text-gray-900 truncate" title="{{ $script->name }}">
                                {{ $script->name }}
                            </div>
                            <div class="mt-1 flex justify-between">
                                <span class="text-xs text-gray-500">
                                    {{ $script->framework_type == 'selenium_python' ? 'Selenium' : ($script->framework_type == 'cypress' ? 'Cypress' : $script->framework_type) }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    Updated {{ $script->updated_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($testSuite->testScripts->count() > 6)
                    <div class="mt-4 text-center">
                        <a href="{{ route('test-suites.show', $testSuite->id) }}" class="text-sm text-blue-600 hover:underline">
                            View all {{ $testSuite->testScripts->count() }} scripts
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<script>
    // Show/hide retry options based on checkbox
    document.getElementById('retry_on_failure').addEventListener('change', function() {
        const retryOptions = document.getElementById('retry_options');
        if (this.checked) {
            retryOptions.classList.remove('hidden');
        } else {
            retryOptions.classList.add('hidden');
        }
    });
</script>
@endsection
