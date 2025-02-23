// resources/js/inbox.js

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('#inbox-search');
    const messageList = document.querySelector('#message-list');
    let debounceTimer;

    if (searchInput && messageList) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => performSearch(e.target.value), 300);
        });
    }

    async function performSearch(query) {
        try {
            const response = await fetch(`/inbox/search?query=${encodeURIComponent(query)}`);
            const messages = await response.json();
            updateMessageList(messages);
        } catch (error) {
            console.error('Error searching messages:', error);
        }
    }

    function updateMessageList(messages) {
        messageList.innerHTML = messages.map(message => `
            <div class="flex items-center px-6 py-4 hover:bg-gray-50 ${message.status === 'unread' ? 'bg-blue-50' : ''}">
                <div class="flex-shrink-0">
                    <input type="checkbox" class="h-4 w-4 text-green-600 rounded border-gray-300 focus:ring-green-500">
                </div>
                <div class="ml-4 flex-1">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-900">${message.sender}</span>
                            ${message.status === 'unread' ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">New</span>' : ''}
                            ${message.priority === 'high' ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">High Priority</span>' : ''}
                        </div>
                        <span class="text-sm text-gray-500">${new Date(message.date).toLocaleDateString()}</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-900">${message.title}</p>
                </div>
                <div class="ml-4 flex-shrink-0">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
        `).join('');
    }
});
