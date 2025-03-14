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
        <span class="text-gray-700">Version History</span>
    </div>

    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Version History</h1>
            <p class="text-gray-600">{{ $testScript->name }}</p>
        </div>
        <a href="{{ route('test-scripts.show', $testScript->id) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Back to Script
        </a>
    </div>

    <!-- Version History List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-800">Version History</h2>
            <p class="text-sm text-gray-500">{{ $versions->count() }} versions</p>
        </div>

        <div class="divide-y divide-gray-200">
            @foreach($versions as $index => $version)
                <div class="p-6 @if($index === 0) bg-blue-50 @endif">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 pt-1">
                            @if($index === 0)
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @else
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>
                            @endif
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium @if($index === 0) text-blue-800 @else text-gray-900 @endif">
                                    @if($index === 0)
                                        Current Version
                                    @else
                                        Version {{ $versions->count() - $index }}
                                    @endif
                                </h3>
                                <div class="flex space-x-2">
                                    <button type="button" onclick="showVersionContent('{{ $version->id }}')" class="text-indigo-600 hover:text-indigo-900">
                                        View
                                    </button>
                                    @if($index !== 0)
                                        <form action="{{ url('test-scripts/'. $testScript->id .'/versions/'. $version->id .'/restore') }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="text-green-600 hover:text-green-900" onclick="return confirm('Are you sure you want to restore this version?')">
                                                Restore
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-1 text-sm text-gray-500">
                                <time datetime="{{ $version->created_at->toISOString() }}">{{ $version->created_at->format('M d, Y \a\t h:i A') }}</time>
                                @if(isset($version->changes['restored_from']))
                                    • Restored from previous version
                                @elseif(isset($version->changes['updated_by']))
                                    • Updated by {{ \App\Models\User::find($version->changes['updated_by'])->name ?? 'Unknown' }}
                                @elseif(isset($version->changes['initial_version']) && $version->changes['initial_version'])
                                    • Initial version
                                @endif
                            </div>

                            <div class="mt-2">
                                <span class="text-xs text-gray-500">Hash: {{ substr($version->version_hash, 0, 8) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Version Content Modal -->
<div id="version-modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900" id="version-modal-title">Version Content</h3>
            <button type="button" onclick="hideVersionModal()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="px-6 py-4 max-h-96 overflow-y-auto">
            <pre id="version-content" class="border border-gray-200 bg-gray-50 rounded-md p-4 overflow-x-auto text-sm font-mono text-gray-800"></pre>
        </div>
        <div class="px-6 py-4 bg-gray-50 flex justify-end rounded-b-lg">
            <button type="button" onclick="hideVersionModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    const versions = @json($versions->keyBy('id'));

    function showVersionContent(versionId) {
        const version = versions[versionId];
        if (!version) return;

        document.getElementById('version-modal-title').textContent = 'Version from ' + new Date(version.created_at).toLocaleString();
        document.getElementById('version-content').textContent = version.script_content;
        document.getElementById('version-modal').classList.remove('hidden');
    }

    function hideVersionModal() {
        document.getElementById('version-modal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('version-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideVersionModal();
        }
    });
</script>
@endsection
