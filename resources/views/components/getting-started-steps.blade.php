<div>
    <div class="flex justify-between items-center">
        <h3 class="font-medium">Get started</h3>
        <span class="text-sm text-gray-500">3 months</span>
    </div>

    <!-- Steps -->
    <div class="space-y-3 mt-4">
        @foreach($steps as $index => $step)
            <div class="flex items-center">
                <div class="w-6 h-6 rounded-full {{ $index < $completedSteps ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-600' }} flex items-center justify-center text-sm mr-3">
                    @if($index < $completedSteps)
                        ✓
                    @else
                        {{ $index + 1 }}
                    @endif
                </div>
                <span class="text-sm {{ $index === $completedSteps ? 'font-medium' : 'text-gray-500' }}">{{ $step }}</span>
            </div>
        @endforeach
    </div>
</div>
