{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex-1 p-8">
    <!-- Feature Cards -->
    <div class="grid grid-cols-3 gap-6 mb-8">
        <!-- Templates Card -->
        <div class="p-6 bg-green-50 rounded-lg transition-all duration-300 hover:shadow-lg hover:transform hover:scale-105 cursor-pointer group">
            <div class="flex items-center mb-4">
                <div class="p-3 bg-white rounded-lg shadow-sm transition-colors duration-300 group-hover:bg-green-100">
                    <i data-lucide="files" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
            <h3 class="mb-2 text-lg font-medium group-hover:text-green-700">Save time with templates</h3>
            <p class="text-sm text-gray-600 group-hover:text-gray-700">Send documents more efficiently by creating a master copy you can reuse.</p>
        </div>

        <!-- Integration Card -->
        <div class="p-6 bg-orange-50 rounded-lg transition-all duration-300 hover:shadow-lg hover:transform hover:scale-105 cursor-pointer group">
            <div class="flex items-center mb-4">
                <div class="p-3 bg-white rounded-lg shadow-sm transition-colors duration-300 group-hover:bg-orange-100">
                    <i data-lucide="plug-2" class="w-6 h-6 text-orange-600"></i>
                </div>
            </div>
            <h3 class="mb-2 text-lg font-medium group-hover:text-orange-700">Integrate with your favorite apps</h3>
            <p class="text-sm text-gray-600 group-hover:text-gray-700">Connect with dozens of CRMs, payment gateways, and other essential tools.</p>
        </div>

        <!-- Branding Card -->
        <div class="p-6 bg-purple-50 rounded-lg transition-all duration-300 hover:shadow-lg hover:transform hover:scale-105 cursor-pointer group">
            <div class="flex items-center mb-4">
                <div class="p-3 bg-white rounded-lg shadow-sm transition-colors duration-300 group-hover:bg-purple-100">
                    <i data-lucide="palette" class="w-6 h-6 text-purple-600"></i>
                </div>
            </div>
            <h3 class="mb-2 text-lg font-medium group-hover:text-purple-700">Show off your brand</h3>
            <p class="text-sm text-gray-600 group-hover:text-gray-700">Customize your workspace with your organization's colors and logo.</p>
        </div>
    </div>

    <!-- Document List -->
    <div class="bg-white rounded-lg shadow transition-shadow duration-300 hover:shadow-lg">
        <div class="flex items-center justify-between p-4 border-b">
            <div class="flex items-center space-x-3">
                <i data-lucide="edit-3" class="w-5 h-5 text-gray-500"></i>
                <h2 class="text-lg font-medium">Drafts</h2>
                <span class="px-2.5 py-1 text-sm text-gray-600 bg-gray-100 rounded-full transition-colors duration-300 hover:bg-gray-200 cursor-pointer">5 documents</span>
            </div>
            <div class="flex items-center space-x-2">
                <button class="p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 transition-colors duration-300">
                    <i data-lucide="filter" class="w-5 h-5"></i>
                </button>
                <button class="p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 transition-colors duration-300">
                    <i data-lucide="more-horizontal" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- Document Table -->
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 transition-colors duration-300">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 transition-colors duration-300">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 transition-colors duration-300">Recipients</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 transition-colors duration-300">Status updated</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 transition-colors duration-300">Last action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr class="hover:bg-gray-50 transition-colors duration-300 cursor-pointer">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <i data-lucide="file-text" class="w-5 h-5 mr-3 text-gray-400"></i>
                            <div>
                                <div class="font-medium text-gray-900 hover:text-green-600 transition-colors duration-300">Yasser Contract</div>
                                <div class="text-sm text-gray-500">by Jane Doe</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 text-sm text-gray-600 bg-gray-100 rounded-full transition-all duration-300 hover:bg-gray-200 hover:shadow-sm cursor-pointer">Draft</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs border-2 border-white hover:transform hover:scale-110 transition-transform duration-300 cursor-pointer">YO</div>
                            <div class="w-8 h-8 rounded-full bg-purple-500 flex items-center justify-center text-white text-xs border-2 border-white hover:transform hover:scale-110 transition-transform duration-300 cursor-pointer">JD</div>
                            <button class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 text-xs border-2 border-white hover:bg-gray-200 transition-colors duration-300">+3</button>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-500 hover:text-gray-700 transition-colors duration-300">Apr 22, 2024</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-500 hover:text-gray-700 transition-colors duration-300">
                            <span class="hover:text-green-600 cursor-pointer">created by Jane Doe</span><br>
                            Apr 22, 2024
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lucide icons
        lucide.createIcons();

        // Add click handlers for feature cards
        document.querySelectorAll('.grid > div').forEach(card => {
            card.addEventListener('click', function() {
                // Add your navigation or modal logic here
                console.log('Card clicked:', this.querySelector('h3').textContent);
            });
        });

        // Add click handlers for table rows
        document.querySelectorAll('tbody tr').forEach(row => {
            row.addEventListener('click', function(e) {
                // Don't trigger if clicking on interactive elements
                if (e.target.closest('.rounded-full')) return;

                // Add your row click logic here
                console.log('Row clicked:', this.querySelector('.font-medium').textContent);
            });
        });

        // Add sorting functionality
        document.querySelectorAll('th').forEach(header => {
            header.addEventListener('click', function() {
                // Add your sorting logic here
                console.log('Sort by:', this.textContent);
            });
        });
    });
</script>
@endpush
@endsection
