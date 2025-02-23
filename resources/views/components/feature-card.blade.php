@props(['title', 'description', 'icon', 'color'])
<div class="bg-{{ $color }}-50 rounded-lg p-6">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <x-dynamic-component
                :component="'icons.' . $icon"
                class="w-12 h-12 text-{{ $color }}-600"
            />
        </div>
        <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">{{ $title }}</h3>
            <p class="mt-2 text-sm text-gray-600">{{ $description }}</p>
        </div>
    </div>
</div>
