@extends('layouts.profile')

@section('profile-title', 'Security Settings')

@section('profile-breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Security</span>
    </li>
@endsection

@section('profile-content')
<div class="space-y-8">
    {{-- Two-Factor Authentication --}}
    <div>
        <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-4">Two-Factor Authentication</h3>

        <div class="bg-white dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-600 rounded-lg overflow-hidden">
            <div class="p-4 sm:p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex items-center">
                            <i data-lucide="shield" class="h-6 w-6 text-indigo-600 dark:text-indigo-400 mr-3"></i>
                            <h4 class="text-base font-medium text-zinc-900 dark:text-white">Two-Factor Authentication</h4>
                        </div>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Add an extra layer of security to your account by requiring additional verification.
                        </p>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                            <i data-lucide="x-circle" class="w-3 h-3 mr-1"></i> Not Enabled
                        </span>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="button" class="btn-primary">
                        <i data-lucide="shield" class="h-4 w-4 mr-2"></i> Enable 2FA
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Session Management --}}
    <div>
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Active Sessions</h3>

            <button type="button" class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                <i data-lucide="refresh-cw" class="h-4 w-4 mr-1"></i> Refresh
            </button>
        </div>

        <div class="bg-white dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-600 rounded-lg overflow-hidden">
            <div class="p-4 sm:p-6">
                {{-- Current Session --}}
                <div class="border-b border-zinc-200 dark:border-zinc-600 pb-4 mb-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center">
                                <i data-lucide="monitor" class="h-5 w-5 text-green-600 dark:text-green-400 mr-3"></i>
                                <h4 class="text-base font-medium text-zinc-900 dark:text-white">This Device</h4>
                            </div>
                            <div class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                <p>{{ request()->server('HTTP_USER_AGENT') ?? 'Unknown Browser' }}</p>
                                <p class="mt-1">
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">IP:</span>
                                    {{ request()->ip() ?? 'Unknown' }}
                                </p>
                                <p class="mt-1">
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">Last activity:</span>
                                    Just now
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i> Current Session
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Other Sessions (if any) --}}
                @if(count($sessions ?? []) > 0)
                    @foreach($sessions as $session)
                        <div class="border-b border-zinc-200 dark:border-zinc-600 py-4 last:border-0 last:pb-0">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="flex items-center">
                                        <i data-lucide="{{ $session['device_type'] === 'mobile' ? 'smartphone' : 'monitor' }}" class="h-5 w-5 text-zinc-500 dark:text-zinc-400 mr-3"></i>
                                        <h4 class="text-base font-medium text-zinc-900 dark:text-white">{{ $session['device_name'] }}</h4>
                                    </div>
                                    <div class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                        <p>{{ $session['browser'] }}</p>
                                        <p class="mt-1">
                                            <span class="font-medium text-zinc-700 dark:text-zinc-300">IP:</span>
                                            {{ $session['ip'] }}
                                        </p>
                                        <p class="mt-1">
                                            <span class="font-medium text-zinc-700 dark:text-zinc-300">Last activity:</span>
                                            {{ $session['last_active'] }}
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <button type="button" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 inline-flex items-center text-sm">
                                        <i data-lucide="log-out" class="h-4 w-4 mr-1"></i> Logout
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="py-4 text-center text-zinc-500 dark:text-zinc-400">
                        No other active sessions found.
                    </div>
                @endif

                <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-600">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-danger w-full">
                            <i data-lucide="log-out" class="h-4 w-4 mr-2"></i> Logout From All Devices
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Login History --}}
    <div>
        <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-4">Recent Login Activity</h3>

        <div class="bg-white dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-600 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-600">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Date & Time</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">IP Address</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Device / Browser</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-transparent divide-y divide-zinc-200 dark:divide-zinc-700">
                        {{-- Example login history items, replace with actual data in a real implementation --}}
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-200">{{ now()->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-200">{{ request()->ip() }}</td>
                            <td class="px-6 py-4 text-sm text-zinc-900 dark:text-zinc-200">Current Browser</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                    Success
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-200">{{ now()->subDays(2)->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-200">192.168.1.1</td>
                            <td class="px-6 py-4 text-sm text-zinc-900 dark:text-zinc-200">Chrome on Windows</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                    Success
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-200">{{ now()->subDays(5)->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-200">10.0.0.1</td>
                            <td class="px-6 py-4 text-sm text-zinc-900 dark:text-zinc-200">Safari on iPhone</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                    Failed
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 bg-zinc-50 dark:bg-zinc-800/50 text-center text-sm text-zinc-500 dark:text-zinc-400">
                <a href="#" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                    View complete login history
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
