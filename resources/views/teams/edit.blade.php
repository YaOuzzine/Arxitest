@extends('layouts.app')

@section('title', 'Edit Team - ' . $team->name)

@section('content')
<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Edit Team</h1>
        <p class="mt-2 text-gray-600">Update your team details.</p>
    </div>

    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Team Information</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Update your team name and description.
                </p>
            </div>
        </div>

        <div class="mt-5 md:mt-0 md:col-span-2">
            <form action="{{ route('teams.update', $team) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 py-5 bg-white sm:p-6">
                        @if ($errors->any())
                        <div class="mb-4 bg-red-50 text-red-700 p-3 rounded-md">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <div class="grid grid-cols-6 gap-6">
                            <div class="col-span-6">
                                <label for="name" class="block text-sm font-medium text-gray-700">Team Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $team->name) }}"
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    required>
                            </div>

                            <div class="col-span-6">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                                <textarea name="description" id="description" rows="3"
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description', $team->description) }}</textarea>
                                <p class="mt-2 text-sm text-gray-500">Brief description of your team's purpose.</p>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 flex items-center justify-between">
                        <a href="{{ route('teams.show', $team) }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="hidden sm:block" aria-hidden="true">
        <div class="py-5">
            <div class="border-t border-gray-200"></div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Danger Zone</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Permanently delete this team and all of its data.
                </p>
            </div>
        </div>

        <div class="mt-5 md:mt-0 md:col-span-2">
            <div class="shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 bg-white sm:p-6">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">Delete Team</h3>
                            <div class="mt-2 text-sm text-gray-500">
                                <p>Once you delete a team, there is no going back. All projects, test scripts, and data will be permanently deleted.</p>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                onclick="showDeleteModal()">
                                Delete Team
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden form for team deletion -->
            <form id="delete-team-form" action="{{ route('teams.destroy', $team) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal (initially hidden) -->
<div id="delete-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Modal backdrop -->
        <div id="modal-backdrop" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Delete Team
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to delete this team? All of your projects, test scripts, executions, and other data will be permanently removed. This action cannot be undone.
                            </p>
                            <div class="mt-4">
                                <label for="confirm-team-name" class="block text-sm font-medium text-gray-700">Please type <span class="font-semibold">{{ $team->name }}</span> to confirm:</label>
                                <input type="text" id="confirm-team-name" class="mt-1 focus:ring-red-500 focus:border-red-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirm-delete-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm" disabled>
                    Delete Team
                </button>
                <button type="button" id="cancel-delete-btn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Delete confirmation modal functionality
    function showDeleteModal() {
        document.getElementById('delete-modal').classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = document.getElementById('delete-modal');
        const modalBackdrop = document.getElementById('modal-backdrop');
        const cancelDeleteBtn = document.getElementById('cancel-delete-btn');
        const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
        const confirmTeamNameInput = document.getElementById('confirm-team-name');
        const deleteTeamForm = document.getElementById('delete-team-form');
        const teamName = "{{ $team->name }}";

        // Close modal on cancel
        cancelDeleteBtn.addEventListener('click', function() {
            deleteModal.classList.add('hidden');
            confirmTeamNameInput.value = '';
        });

        // Also close when clicking outside the modal
        modalBackdrop.addEventListener('click', function() {
            deleteModal.classList.add('hidden');
            confirmTeamNameInput.value = '';
        });

        // Enable/disable delete button based on input
        confirmTeamNameInput.addEventListener('input', function() {
            confirmDeleteBtn.disabled = this.value !== teamName;
        });

        // Submit the form on confirmation
        confirmDeleteBtn.addEventListener('click', function() {
            if (confirmTeamNameInput.value === teamName) {
                deleteTeamForm.submit();
            }
        });
    });
</script>
@endpush
