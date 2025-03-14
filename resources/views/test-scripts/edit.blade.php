@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Navigation -->
    <div class="flex items-center text-sm text-gray-500 mb-4">
        <a href="{{ route('test-scripts.index') }}" class="hover:text-blue-600">Test Scripts</a>
        <svg class="h-4 w-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
        <a href="{{ route('test-scripts.show', $testScript->id) }}" class="hover:text-blue-600">{{ $testScript->name }}</a>
        <svg class="h-4 w-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-gray-700">Edit</span>
    </div>

    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Test Script</h1>
        <p class="text-gray-600">Update your test script details and content</p>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <form action="{{ route('test-scripts.update', $testScript->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Script Name</label>
                        <input type="text" name="name" id="name" class="border border-gray-300 rounded-md w-full py-2 px-3 @error('name') border-red-500 @enderror" value="{{ old('name', $testScript->name) }}" required>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="suite_id" class="block text-sm font-medium text-gray-700 mb-1">Test Suite</label>
                        <select name="suite_id" id="suite_id" class="border border-gray-300 rounded-md w-full py-2 px-3 @error('suite_id') border-red-500 @enderror" required>
                            <option value="">Select a test suite</option>
                            @foreach(App\Models\TestSuite::all() as $suite)
                                <option value="{{ $suite->id }}" {{ (old('suite_id', $testScript->suite_id) == $suite->id) ? 'selected' : '' }}>
                                    {{ $suite->name }} ({{ $suite->project->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('suite_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="framework_type" class="block text-sm font-medium text-gray-700 mb-1">Framework</label>
                        <select name="framework_type" id="framework_type" class="border border-gray-300 rounded-md w-full py-2 px-3 @error('framework_type') border-red-500 @enderror" required>
                            <option value="">Select a framework</option>
                            <option value="selenium_python" {{ (old('framework_type', $testScript->framework_type) == 'selenium_python') ? 'selected' : '' }}>Selenium (Python)</option>
                            <option value="cypress" {{ (old('framework_type', $testScript->framework_type) == 'cypress') ? 'selected' : '' }}>Cypress</option>
                        </select>
                        @error('framework_type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jira_story_id" class="block text-sm font-medium text-gray-700 mb-1">Jira Story (Optional)</label>
                        <select name="jira_story_id" id="jira_story_id" class="border border-gray-300 rounded-md w-full py-2 px-3">
                            <option value="">None</option>
                            @foreach(App\Models\JiraStory::orderBy('jira_key')->get() as $story)
                                <option value="{{ $story->id }}" {{ (old('jira_story_id', $testScript->jira_story_id) == $story->id) ? 'selected' : '' }}>
                                    {{ $story->jira_key }} - {{ $story->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-1">
                        <label for="script_content" class="block text-sm font-medium text-gray-700">Script Content</label>
                        <div class="text-xs">
                            <span id="editor-info" class="text-gray-500">
                                Lines: <span id="line-count">0</span> | Characters: <span id="char-count">0</span>
                            </span>
                        </div>
                    </div>
                    <textarea name="script_content" id="script_content" rows="20" class="border border-gray-300 rounded-md w-full py-2 px-3 font-mono text-sm @error('script_content') border-red-500 @enderror" required>{{ old('script_content', $testScript->script_content) }}</textarea>
                    @error('script_content')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="border bg-yellow-50 border-yellow-100 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Editing this script will create a new version. You can always revert to previous versions if needed.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between">
                    <a href="{{ route('test-scripts.show', $testScript->id) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg">
                        Cancel
                    </a>
                    <div>
                        <a href="{{ url('test-scripts/'. $testScript->id .'/versions') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg mr-2">
                            Version History
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Update line and character count
    const scriptContent = document.getElementById('script_content');

    function updateEditorInfo() {
        const content = scriptContent.value;
        const lines = content.split('\n').length;
        const chars = content.length;

        document.getElementById('line-count').textContent = lines;
        document.getElementById('char-count').textContent = chars;
    }

    // Initial count
    updateEditorInfo();

    // Update on input
    scriptContent.addEventListener('input', updateEditorInfo);
</script>
@endsection
