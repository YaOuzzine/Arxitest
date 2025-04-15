@php
    // Use the count passed from the View Composer
    $count = $pendingInvitationCount ?? 0;
@endphp

@if($count > 0)
<span class="ml-auto inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 text-[10px] font-semibold leading-none rounded-full bg-indigo-500 text-white animate-pulse">
    {{ $count }}
    <span class="sr-only">pending invitations</span>
</span>
@endif
