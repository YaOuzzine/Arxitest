@props([
    'id' => 'clone-modal',
    'title' => 'Clone Test Case',
    'message' => 'Create a copy of this test case with the following options:',
    'itemName' => null,
    'confirmText' => 'Clone',
    'cancelText' => 'Cancel',
])

<div x-show="showCloneModal" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="{{ $id }}-title" role="dialog" aria-modal="true" style="display: none;" id="{{ $id }}">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
        <div class="fixed inset-0 bg-zinc-900/60 dark:bg-zinc-900/80 backdrop-blur-sm transition-opacity"
            @click="showCloneModal = false"></div>
        <div
            class="relative inline-block w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-zinc-800 shadow-xl rounded-2xl">
            <div class="absolute top-0 right-0 pt-5 pr-5">
                <button type="button" @click="showCloneModal = false"
                    class="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form :action="cloneFormAction" method="POST" id="clone-form">
                @csrf
                <div>
                    <h3 class="text-xl font-medium text-zinc-900 dark:text-zinc-100 flex items-center"
                        id="{{ $id }}-title">
                        <i data-lucide="copy" class="w-6 h-6 text-indigo-500 mr-2"></i>
                        {{ $title }}
                    </h3>

                    <p class="mt-2 text-zinc-600 dark:text-zinc-400">
                        {{ $message }}
                    </p>

                    @if ($itemName)
                        <p class="mt-2 text-indigo-600 dark:text-indigo-400 font-medium">
                            "<span x-text="{{ $itemName }}"></span>"
                        </p>
                    @endif

                    <div class="mt-4 space-y-4">
                        <div class="flex items-center">
                            <input id="clone-title" type="text" name="title" x-model="cloneTitle" placeholder="New title for the cloned test case"
                                class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white">
                        </div>

                        <div class="flex items-start mt-4">
                            <div class="flex items-center h-5">
                                <input id="clone-scripts" name="clone_scripts" type="checkbox" x-model="cloneOptions.scripts"
                                    class="w-4 h-4 text-indigo-600 border-zinc-300 rounded focus:ring-indigo-500">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="clone-scripts" class="font-medium text-zinc-700 dark:text-zinc-300">Clone Test Scripts</label>
                                <p class="text-zinc-500 dark:text-zinc-400">Create copies of all associated test scripts</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="clone-test-data" name="clone_test_data" type="checkbox" x-model="cloneOptions.testData"
                                    class="w-4 h-4 text-indigo-600 border-zinc-300 rounded focus:ring-indigo-500">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="clone-test-data" class="font-medium text-zinc-700 dark:text-zinc-300">Clone Test Data References</label>
                                <p class="text-zinc-500 dark:text-zinc-400">Link the same test data to the cloned test case</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="showCloneModal = false"
                        class="px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        {{ $cancelText }}
                    </button>
                    <button type="submit" :disabled="isCloning || !cloneTitle.trim()"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isCloning" class="flex items-center">
                            <i data-lucide="copy" class="w-4 h-4 mr-2"></i>
                            {{ $confirmText }}
                        </span>
                        <span x-show="isCloning" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Cloning...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
