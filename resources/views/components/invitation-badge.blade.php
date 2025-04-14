@php
$pendingCount = \App\Models\TeamInvitation::where('email', Auth::user()->email)
    ->where('expires_at', '>', now())
    ->count();
@endphp

@if($pendingCount > 0)
    <span class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
        {{ $pendingCount }}
    </span>
@endif
