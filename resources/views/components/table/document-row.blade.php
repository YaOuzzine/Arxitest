@props(['document'])
<tr>
    <td class="px-6 py-4">
        <div class="flex items-center">
            <x-document-icon />
            <div>
                <div class="text-sm font-medium text-gray-900">{{ $document->title }}</div>
                <div class="text-sm text-gray-500">by {{ $document->author }}</div>
            </div>
        </div>
    </td>
    <td class="px-6 py-4">
        <x-status-badge :status="$document->status" />
    </td>
    <td class="px-6 py-4">
        <x-recipients-list :recipients="$document->recipients" />
    </td>
    <td class="px-6 py-4 text-sm text-gray-500">{{ $document->updated_at->format('M d, Y') }}</td>
    <td class="px-6 py-4">
        <x-last-action :action="$document->last_action" />
    </td>
</tr>
