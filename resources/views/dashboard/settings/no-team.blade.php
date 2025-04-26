@extends('layouts.dashboard')

@section('title', 'Settings - Team Required')

@section('content')
<div class="h-full space-y-6 animate-fade-in">
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white flex items-center">
                <i data-lucide="settings" class="w-6 h-6 mr-2 text-indigo-500"></i>
                Settings Dashboard
            </h1>
            <p class="text-zinc-600 dark:text-zinc-400 mt-1">
                Team selection required
            </p>
        </div>

        <div class="border-t border-zinc-200 dark:border-zinc-700 p-8 text-center">
            <div class="max-w-md mx-auto">
                <i data-lucide="users" class="w-16 h-16 mx-auto mb-6 text-indigo-500"></i>
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-3">
                    Please Select a Team
                </h2>
                <p class="text-zinc-600 dark:text-zinc-400 mb-6">
                    You need to select a team before you can access settings.
                    Settings are team-specific and help customize the Arxitest experience for your team.
                </p>
                <a href="{{ route('dashboard.select-team') }}" class="btn-primary inline-flex items-center">
                    <i data-lucide="users" class="w-4 h-4 mr-2"></i>
                    Select a Team
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endpush
