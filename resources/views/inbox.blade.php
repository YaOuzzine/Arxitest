<!-- resources/views/inbox.blade.php -->
@extends('layouts.app')

@section('content')
<div class="flex-1 flex">
    <!-- Left Sidebar - Document Categories -->
    <div class="w-64 bg-white border-r border-gray-200">
        <div class="p-4">
            <h2 class="text-lg font-medium text-gray-900 flex items-center space-x-2">
                <i data-lucide="inbox" class="w-5 h-5 text-gray-500"></i>
                <span>Inbox</span>
            </h2>
        </div>

        <nav class="space-y-1 px-2">
            <a href="#" class="flex items-center px-3 py-2 text-sm font-medium text-gray-900 bg-gray-100 rounded-md transition-all duration-300 group hover:shadow-sm">
                <i data-lucide="folder" class="mr-3 h-5 w-5 text-gray-500 transition-transform duration-300 group-hover:scale-110"></i>
                <span>All Documents ({{ count($messages) }})</span>
            </a>
            <a href="#" class="flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-md transition-all duration-300 group">
                <i data-lucide="check-circle" class="mr-3 h-5 w-5 text-gray-500 transition-transform duration-300 group-hover:scale-110"></i>
                <span class="group-hover:text-gray-900">Completed</span>
            </a>
            <a href="#" class="flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-md transition-all duration-300 group">
                <i data-lucide="clock" class="mr-3 h-5 w-5 text-gray-500 transition-transform duration-300 group-hover:scale-110"></i>
                <span class="group-hover:text-gray-900">Pending</span>
            </a>
            <a href="#" class="flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-md transition-all duration-300 group">
                <i data-lucide="trash-2" class="mr-3 h-5 w-5 text-gray-500 transition-transform duration-300 group-hover:scale-110"></i>
                <span class="group-hover:text-gray-900">Deleted</span>
            </a>
        </nav>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 bg-white">
        <!-- Toolbar -->
        <div class="border-b border-gray-200">
            <div class="flex items-center justify-between p-4">
                <div class="flex items-center space-x-4">
                    <div class="relative group">
                        <input type="text" id="inbox-search" placeholder="Search inbox..."
                            class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 transition-shadow duration-300 hover:shadow-sm">
                        <i data-lucide="search" class="absolute left-3 top-2.5 h-5 w-5 text-gray-400 transition-transform duration-300 group-hover:scale-110"></i>
                    </div>
                    <select class="border border-gray-300 rounded-md text-sm focus:ring-green-500 focus:border-green-500 cursor-pointer hover:shadow-sm transition-all duration-300">
                        <option>All Documents</option>
                        <option>Recent</option>
                        <option>Unread</option>
                    </select>
                </div>
                <div class="flex items-center space-x-2">
                    <button class="p-2 text-gray-700 hover:bg-gray-50 rounded-full transition-all duration-300 group">
                        <i data-lucide="arrow-down" class="h-5 w-5 transition-transform duration-300 group-hover:scale-110"></i>
                    </button>
                    <button class="p-2 text-gray-700 hover:bg-gray-50 rounded-full transition-all duration-300 group">
                        <i data-lucide="more-vertical" class="h-5 w-5 transition-transform duration-300 group-hover:scale-110"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Message List -->
        <div id="message-list" class="divide-y divide-gray-200">
            @foreach($messages as $message)
            <div class="flex items-center px-6 py-4 hover:bg-gray-50 transition-colors duration-300 group cursor-pointer
                {{ $message['status'] === 'unread' ? 'bg-blue-50' : '' }}">
                <div class="flex-shrink-0">
                    <input type="checkbox" class="h-4 w-4 text-green-600 rounded border-gray-300 focus:ring-green-500 cursor-pointer">
                </div>
                <div class="ml-4 flex-1">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-900 group-hover:text-green-600 transition-colors duration-300">{{ $message['sender'] }}</span>
                            @if($message['status'] === 'unread')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors duration-300">
                                    <i data-lucide="mail" class="w-3 h-3 mr-1"></i>New
                                </span>
                            @endif
                            @if($message['priority'] === 'high')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200 transition-colors duration-300">
                                    <i data-lucide="alert-circle" class="w-3 h-3 mr-1"></i>High Priority
                                </span>
                            @endif
                        </div>
                        <span class="text-sm text-gray-500 group-hover:text-gray-700 transition-colors duration-300">
                            {{ \Carbon\Carbon::parse($message['date'])->format('M d, Y') }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-900 group-hover:text-gray-700 transition-colors duration-300">{{ $message['title'] }}</p>
                </div>
                <div class="ml-4 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <i data-lucide="chevron-right" class="h-5 w-5 text-gray-400"></i>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Right Sidebar - Message Preview -->
    <div class="w-80 bg-white border-l border-gray-200 p-6">
        <div class="text-center py-8">
            <div class="mx-auto h-12 w-12 text-gray-400 mb-4 transition-transform duration-300 hover:scale-110">
                <i data-lucide="file-text" class="w-full h-full"></i>
            </div>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Select a document</h3>
            <p class="mt-1 text-sm text-gray-500">Select a document to preview its contents and details here.</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize any additional inbox-specific interactions here
        const messageItems = document.querySelectorAll('#message-list > div');

        messageItems.forEach(item => {
            item.addEventListener('click', (e) => {
                // Don't trigger if clicking checkbox
                if (e.target.type === 'checkbox') return;

                // Remove active state from all items
                messageItems.forEach(i => i.classList.remove('bg-gray-100'));
                // Add active state to clicked item
                item.classList.add('bg-gray-100');

                // Add your message selection logic here
            });
        });
    });
</script>
@endpush
@endsection
