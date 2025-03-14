@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Navigation -->
    <div class="flex items-center text-sm text-gray-500 mb-4">
        <a href="{{ route('projects.index') }}" class="hover:text-blue-600">Projects</a>
        <svg class="h-4 w-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-gray-700">Import Test Suite</span>
    </div>

    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Import Test Suite</h1>
        <p class="text-gray-600">Import a test suite from a JSON file</p>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <form action="{{ route('test-suites.import') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-6">
                    <label for="project_id" class="block text-sm font-medium text-gray-700 mb-1">Target Project</label>
                    <select name="project_id" id="project_id" class="border border-gray-300 rounded-md w-full py-2 px-3 @error('project_id') border-red-500 @enderror" required>
                        <option value="">Select a project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $projectId) == $project->id ? 'selected' : '' }}>
                                {{ $project->name }} ({{ $project->team->name }})
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="suite_name" class="block text-sm font-medium text-gray-700 mb-1">Suite Name</label>
                    <input type="text" name="suite_name" id="suite_name" class="border border-gray-300 rounded-md w-full py-2 px-3 @error('suite_name') border-red-500 @enderror" value="{{ old('suite_name') }}" required>
                    @error('suite_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="import_file" class="block text-sm font-medium text-gray-700 mb-1">Import File (JSON)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="import_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Upload a file</span>
                                    <input id="import_file" name="import_file" type="file" accept=".json" class="sr-only" required>
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                JSON file only, up to 2MB
                            </p>
                        </div>
                    </div>
                    <div id="file-name" class="mt-2 text-sm text-gray-600 hidden">
                        Selected file: <span id="selected-file-name" class="font-medium"></span>
                    </div>
                    @error('import_file')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-yellow-50 p-4 rounded-md mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Import Information</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>The imported test suite must be in a valid JSON format exported from Arxitest.</li>
                                    <li>All scripts in the file will be imported as new scripts.</li>
                                    <li>Any referenced Jira stories must already exist in the system.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between">
                    <a href="{{ isset($projectId) ? route('projects.show', $projectId) : route('projects.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                        Import Test Suite
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Show selected file name
    document.getElementById('import_file').addEventListener('change', function() {
        const fileName = this.files[0] ? this.files[0].name : '';
        if (fileName) {
            document.getElementById('selected-file-name').textContent = fileName;
            document.getElementById('file-name').classList.remove('hidden');
        } else {
            document.getElementById('file-name').classList.add('hidden');
        }
    });

    // Drag and drop functionality
    const dropZone = document.querySelector('.border-dashed');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    dropZone.addEventListener('drop', handleDrop, false);

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight() {
        dropZone.classList.add('border-blue-300', 'bg-blue-50');
    }

    function unhighlight() {
        dropZone.classList.remove('border-blue-300', 'bg-blue-50');
    }

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files && files.length) {
            document.getElementById('import_file').files = files;
            document.getElementById('selected-file-name').textContent = files[0].name;
            document.getElementById('file-name').classList.remove('hidden');
        }
    }
</script>
@endsection
