@extends('layouts.profile')

@section('profile-title', 'Notification Preferences')

@section('profile-breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Notifications</span>
    </li>
@endsection

@section('profile-content')
<form action="{{ route('dashboard.profile.notifications.update') }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

    <p class="text-zinc-600 dark:text-zinc-400">
        Customize how and when you receive notifications from Arxitest.
    </p>

    <div class="space-y-6">
        {{-- Email Notifications --}}
        <div class="bg-white dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-600 rounded-lg overflow-hidden">
            <div class="p-4 sm:p-6">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-4">Email Notifications</h3>

                <div class="space-y-4">
                    {{-- Email Notifications Toggle --}}
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-base font-medium text-zinc-800 dark:text-zinc-200">Receive Email Notifications</h4>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                Master toggle for all email notifications
                            </p>
                        </div>
                        <div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="email_notifications" value="1" class="sr-only peer" {{ $preferences['email_notifications'] ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-zinc-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 dark:peer-focus:ring-indigo-600 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                    </div>

                    <div class="border-t border-zinc-200 dark:border-zinc-600 pt-4">
                        <h4 class="text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-3">Email Notification Types</h4>

                        {{-- Test Run Alerts --}}
                        <div class="flex items-center justify-between py-2">
                            <div>
                                <h5 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Test Run Alerts</h5>
                                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                    Receive emails when test runs complete or fail
                                </p>
                            </div>
                            <div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="test_run_alerts" value="1" class="sr-only peer" {{ $preferences['test_run_alerts'] ? 'checked' : '' }}
                                        {{ !$preferences['email_notifications'] ? 'disabled' : '' }}>
                                    <div class="w-9 h-5 bg-zinc-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 dark:peer-focus:ring-indigo-600 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-zinc-600 peer-checked:bg-indigo-600 peer-disabled:opacity-50 peer-disabled:cursor-not-allowed"></div>
                                </label>
                            </div>
                        </div>

                        {{-- Security Alerts --}}
                        <div class="flex items-center justify-between py-2 border-t border-zinc-100 dark:border-zinc-700/50">
                            <div>
                                <h5 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Security Alerts</h5>
                                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                    Important security notifications about your account
                                </p>
                            </div>
                            <div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="security_alerts" value="1" class="sr-only peer" {{ $preferences['security_alerts'] ? 'checked' : '' }}
                                        {{ !$preferences['email_notifications'] ? 'disabled' : '' }}>
                                    <div class="w-9 h-5 bg-zinc-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 dark:peer-focus:ring-indigo-600 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-zinc-600 peer-checked:bg-indigo-600 peer-disabled:opacity-50 peer-disabled:cursor-not-allowed"></div>
                                </label>
                            </div>
                        </div>

                        {{-- Marketing Emails --}}
                        <div class="flex items-center justify-between py-2 border-t border-zinc-100 dark:border-zinc-700/50">
                            <div>
                                <h5 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Marketing & Announcements</h5>
                                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                    New features, updates, and promotional offers
                                </p>
                            </div>
                            <div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="marketing_emails" value="1" class="sr-only peer" {{ $preferences['marketing_emails'] ? 'checked' : '' }}
                                        {{ !$preferences['email_notifications'] ? 'disabled' : '' }}>
                                    <div class="w-9 h-5 bg-zinc-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 dark:peer-focus:ring-indigo-600 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-zinc-600 peer-checked:bg-indigo-600 peer-disabled:opacity-50 peer-disabled:cursor-not-allowed"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Push Notifications --}}
        <div class="bg-white dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-600 rounded-lg overflow-hidden">
            <div class="p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Push Notifications</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Browser notifications for real-time updates
                        </p>
                    </div>
                    <div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="push_notifications" value="1" class="sr-only peer" {{ $preferences['push_notifications'] ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-zinc-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 dark:peer-focus:ring-indigo-600 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>

                @if(!$preferences['push_notifications'])
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-lg p-4 text-amber-700 dark:text-amber-300 text-sm">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i data-lucide="bell-off" class="h-5 w-5 text-amber-600 dark:text-amber-400"></i>
                            </div>
                            <div class="ml-3">
                                <p>
                                    Browser notifications are disabled. Enable them to receive real-time alerts about your test runs, even when you're not actively using the application.
                                </p>
                                <button type="button" class="mt-2 text-sm font-medium text-amber-800 dark:text-amber-300 hover:text-amber-700 dark:hover:text-amber-200 focus:outline-none">
                                    Enable Browser Notifications
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Notification Frequency --}}
        <div class="bg-white dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-600 rounded-lg overflow-hidden">
            <div class="p-4 sm:p-6">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-4">Notification Frequency</h3>

                <div class="space-y-4">
                    <div>
                        <label for="frequency" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email Digest Frequency</label>
                        <select id="frequency" name="notification_frequency" class="mt-1 block w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="realtime" selected>Real-time (as they happen)</option>
                            <option value="daily">Daily Digest</option>
                            <option value="weekly">Weekly Digest</option>
                        </select>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            How often you want to receive non-critical notification emails
                        </p>
                    </div>

                    <div>
                        <label for="quiet_hours" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Quiet Hours</label>
                        <div class="mt-1 grid grid-cols-2 gap-4">
                            <div>
                                <label for="quiet_start" class="block text-xs text-zinc-500 dark:text-zinc-400">Start Time</label>
                                <input type="time" id="quiet_start" name="quiet_start" value="22:00" class="mt-1 block w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="quiet_end" class="block text-xs text-zinc-500 dark:text-zinc-400">End Time</label>
                                <input type="time" id="quiet_end" name="quiet_end" value="07:00" class="mt-1 block w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            During these hours, you'll only receive critical notifications
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-end pt-4 border-t border-zinc-200 dark:border-zinc-700">
        <button type="submit" class="btn-primary">Save Preferences</button>
    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle master toggle for email notifications
        const masterToggle = document.querySelector('input[name="email_notifications"]');
        const dependentToggles = document.querySelectorAll('input[name="test_run_alerts"], input[name="security_alerts"], input[name="marketing_emails"]');

        if (masterToggle) {
            masterToggle.addEventListener('change', function() {
                dependentToggles.forEach(toggle => {
                    toggle.disabled = !this.checked;
                });
            });
        }
    });
</script>
@endpush
@endsection
