{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('content')
    <!-- Debug States -->
    <div class="max-w-4xl mx-auto p-8">
        <h1 class="text-2xl font-bold mb-8">Authentication Debug Page</h1>

        <!-- Auth Status Section -->
        <div class="mb-8 p-4 bg-gray-100 rounded-lg">
            <h2 class="text-xl font-semibold mb-2">Authentication Status</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="font-medium">Server Authentication:</span>
                    <span class="ml-2 px-2 py-1 rounded {{ Auth::check() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ Auth::check() ? 'Authenticated' : 'Not Authenticated' }}
                    </span>
                </div>
                <div>
                    <span class="font-medium">Session ID:</span>
                    <span class="ml-2 text-gray-600">{{ session()->getId() }}</span>
                </div>
            </div>
        </div>

        <!-- User Info Section (Server Auth) -->
        @if(Auth::check())
            <div class="mb-8 p-4 bg-green-50 rounded-lg border border-green-200">
                <h2 class="text-xl font-semibold mb-4">Server-Side User Information</h2>
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-green-100">
                            <th class="border border-green-300 px-4 py-2 text-left">Field</th>
                            <th class="border border-green-300 px-4 py-2 text-left">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border border-green-200 px-4 py-2 font-medium">ID</td>
                            <td class="border border-green-200 px-4 py-2">{{ Auth::user()->id }}</td>
                        </tr>
                        <tr>
                            <td class="border border-green-200 px-4 py-2 font-medium">Name</td>
                            <td class="border border-green-200 px-4 py-2">{{ Auth::user()->name }}</td>
                        </tr>
                        <tr>
                            <td class="border border-green-200 px-4 py-2 font-medium">Email</td>
                            <td class="border border-green-200 px-4 py-2">{{ Auth::user()->email }}</td>
                        </tr>
                        <tr>
                            <td class="border border-green-200 px-4 py-2 font-medium">Role</td>
                            <td class="border border-green-200 px-4 py-2">{{ Auth::user()->role ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-green-200 px-4 py-2 font-medium">Created At</td>
                            <td class="border border-green-200 px-4 py-2">{{ Auth::user()->created_at }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Client Auth Section -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Client-Side Authentication</h2>
            <div id="token-status" class="p-4 bg-gray-100 rounded-lg mb-4">
                Checking for token...
            </div>

            <div id="client-user-container" class="p-4 bg-blue-50 rounded-lg border border-blue-200 hidden">
                <h3 class="text-lg font-medium mb-2">Token-Based User Information</h3>
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-blue-100">
                            <th class="border border-blue-300 px-4 py-2 text-left">Field</th>
                            <th class="border border-blue-300 px-4 py-2 text-left">Value</th>
                        </tr>
                    </thead>
                    <tbody id="client-user-info">
                        <tr>
                            <td colspan="2" class="border border-blue-200 px-4 py-2 text-center">
                                Loading user data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Authentication Actions -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <h3 class="text-lg font-medium mb-2">Server Auth Actions</h3>
                <div class="space-y-2">
                    @if(Auth::check())
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                Logout (Server)
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Login Page
                        </a>
                    @endif
                </div>
            </div>
            <div>
                <h3 class="text-lg font-medium mb-2">Client Auth Actions</h3>
                <div class="space-y-2">
                    <button id="client-logout-btn" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 hidden">
                        Logout (Client)
                    </button>
                    <button id="check-token-btn" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                        Refresh Token Check
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tokenStatus = document.getElementById('token-status');
            const clientUserContainer = document.getElementById('client-user-container');
            const clientUserInfo = document.getElementById('client-user-info');
            const clientLogoutBtn = document.getElementById('client-logout-btn');
            const checkTokenBtn = document.getElementById('check-token-btn');

            // Function to check token and get user info
            function checkTokenAuth() {
                const token = localStorage.getItem('token');

                if (!token) {
                    tokenStatus.textContent = 'No token found in localStorage';
                    tokenStatus.className = 'p-4 bg-red-100 text-red-800 rounded-lg mb-4';
                    clientUserContainer.classList.add('hidden');
                    clientLogoutBtn.classList.add('hidden');
                    return;
                }

                tokenStatus.textContent = 'Token found: ' + token.substring(0, 15) + '...';
                tokenStatus.className = 'p-4 bg-yellow-100 text-yellow-800 rounded-lg mb-4';

                // Try to get user data with the token
                fetch('/api/auth/me', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Token is valid, update UI
                    tokenStatus.textContent = 'Valid token - Authentication successful';
                    tokenStatus.className = 'p-4 bg-green-100 text-green-800 rounded-lg mb-4';

                    // Show client user container and logout button
                    clientUserContainer.classList.remove('hidden');
                    clientLogoutBtn.classList.remove('hidden');

                    // Populate user info from API response
                    const user = data.user;
                    const userFields = Object.keys(user).filter(k =>
                        typeof user[k] !== 'object' && k !== 'password' && k !== 'password_hash'
                    );

                    clientUserInfo.innerHTML = userFields.map(field => `
                        <tr>
                            <td class="border border-blue-200 px-4 py-2 font-medium">${field}</td>
                            <td class="border border-blue-200 px-4 py-2">${user[field]}</td>
                        </tr>
                    `).join('');

                    // Add API response info row
                    clientUserInfo.innerHTML += `
                        <tr>
                            <td class="border border-blue-200 px-4 py-2 font-medium">API Response</td>
                            <td class="border border-blue-200 px-4 py-2 text-xs overflow-auto" style="max-width: 400px; max-height: 100px;">
                                <pre>${JSON.stringify(data, null, 2)}</pre>
                            </td>
                        </tr>
                    `;
                })
                .catch(error => {
                    console.error('Authentication error:', error);
                    tokenStatus.textContent = 'Invalid token - Authentication failed: ' + error.message;
                    tokenStatus.className = 'p-4 bg-red-100 text-red-800 rounded-lg mb-4';
                    clientUserContainer.classList.add('hidden');
                    clientLogoutBtn.classList.remove('hidden');
                });
            }

            // Initial token check
            checkTokenAuth();

            // Event listeners
            checkTokenBtn.addEventListener('click', checkTokenAuth);

            clientLogoutBtn.addEventListener('click', function() {
                const token = localStorage.getItem('token');

                if (token) {
                    // Try to logout with the API
                    fetch('/api/auth/logout', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        // Even if the server fails, clear the token locally
                        localStorage.removeItem('token');
                        tokenStatus.textContent = 'Logged out - Token removed';
                        tokenStatus.className = 'p-4 bg-red-100 text-red-800 rounded-lg mb-4';
                        clientUserContainer.classList.add('hidden');
                        clientLogoutBtn.classList.add('hidden');
                    })
                    .catch(error => {
                        console.error('Logout error:', error);
                        // Still clear the token
                        localStorage.removeItem('token');
                        tokenStatus.textContent = 'Logged out (error occurred on server) - Token removed';
                        tokenStatus.className = 'p-4 bg-red-100 text-red-800 rounded-lg mb-4';
                        clientUserContainer.classList.add('hidden');
                        clientLogoutBtn.classList.add('hidden');
                    });
                }
            });
        });
    </script>
@endsection
