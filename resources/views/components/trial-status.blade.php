<div class="pt-4 border-t">
    <div class="text-sm text-gray-500">Your trial period is ending soon</div>
    <div class="mt-2">
        <div class="bg-gray-200 rounded-full h-2">
            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $progressPercentage }}%"></div>
        </div>
    </div>
    <div class="mt-2 text-sm font-medium">{{ $daysLeft }} days left</div>
    <button class="mt-4 w-full bg-gray-900 text-white px-4 py-2 rounded-lg">
        Pay now
    </button>
</div>
