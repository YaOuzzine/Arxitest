@props(['documents'])

<tbody class="bg-white divide-y divide-gray-200">
    @foreach($documents as $timeframe => $docs)
        <tr class="bg-gray-50">
            <td colspan="5" class="px-6 py-2">
                <span class="text-xs font-medium text-gray-500">{{ strtoupper($timeframe) }}</span>
            </td>
        </tr>
        @foreach($docs as $document)
            <tr>
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $document->title }}</div>
                            <div class="text-sm text-gray-500">by {{ $document->author->name ?? 'Unknown' }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100">{{ $document->status }}</span>
                </td>
                <td class="px-6 py-4">
                    @if($document->recipients && $document->recipients->count() > 0)
                        <div class="flex -space-x-2">
                            @foreach($document->recipients as $recipient)
                                <div class="w-6 h-6 rounded-full bg-{{ ['blue', 'green', 'purple', 'orange'][array_rand(['blue', 'green', 'purple', 'orange'])] }}-500 flex items-center justify-center text-white text-xs">
                                    {{ substr($recipient->name ?? 'U', 0, 2) }}
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="text-sm text-gray-500">No recipients</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    {{ $document->updated_at->format('M d, Y') }}
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">{{ $document->last_action ?? 'created' }} by {{ $document->lastActionBy->name ?? $document->author->name ?? 'Unknown' }}</div>
                    <div class="text-sm text-gray-500">{{ $document->updated_at->format('M d, Y') }}</div>
                </td>
            </tr>
        @endforeach
    @endforeach
</tbody>
