{{-- resources/views/dashboard/test-cases/partials/details-tab.blade.php --}}

<div x-show="activeTab === 'details'" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Description & Steps -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Story Information -->
            @if ($story)
                <div
                    class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-4 border border-indigo-200 dark:border-indigo-800/40 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i data-lucide="book-open"
                                class="h-5 w-5 text-indigo-600 dark:text-indigo-400 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-indigo-800 dark:text-indigo-200">Related
                                Story</h3>
                            <div class="mt-1">
                                <a href="{{ route('dashboard.stories.show', $story->id) }}"
                                    class="text-base font-medium text-indigo-700 dark:text-indigo-300 hover:text-indigo-900 dark:hover:text-indigo-100">
                                    {{ $story->title }}
                                </a>
                                <p class="mt-1 text-sm text-indigo-700 dark:text-indigo-300">
                                    {{ Str::limit($story->description, 150) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Description -->
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Description</h3>
                <div
                    class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                    <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-line">
                        {{ $testCase->description ?: 'No description provided.' }}</p>
                </div>
            </div>

            <!-- Steps -->
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Test Steps</h3>
                <div
                    class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                    @if (count($steps) > 0)
                        <ol class="list-decimal list-inside space-y-2">
                            @foreach ($steps as $index => $step)
                                <li class="text-zinc-700 dark:text-zinc-300">
                                    <span class="font-medium">{{ $index + 1 }}.</span>
                                    {{ $step }}
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <p class="text-zinc-500 dark:text-zinc-400 italic">No steps defined.</p>
                    @endif
                </div>
            </div>

            <!-- Expected Results -->
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Expected Results</h3>
                <div
                    class="bg-zinc-50 dark:bg-zinc-700/30 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                    <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-line">
                        {{ $testCase->expected_results }}</p>
                </div>
            </div>
        </div>

        <!-- Right Column: Metadata -->
        <div class="space-y-6">
            <!-- Test Suite Info -->
            @if ($testSuite)
                <div
                    class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                    <div
                        class="px-4 py-3 bg-zinc-50 dark:bg-zinc-700/30 border-b border-zinc-200 dark:border-zinc-700">
                        <h3 class="font-medium text-zinc-900 dark:text-white">Test Suite</h3>
                    </div>
                    <div class="p-4">
                        <a href="{{ route('dashboard.projects.test-suites.show', [$project->id, $testSuite->id]) }}"
                            class="flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                            <i data-lucide="layers" class="w-4 h-4 mr-2"></i>
                            <span>{{ $testSuite->name }}</span>
                        </a>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ Str::limit($testSuite->description, 100) ?: 'No description.' }}
                        </p>
                    </div>
                </div>
            @endif

            <!-- Tags -->
            <div
                class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <div
                    class="px-4 py-3 bg-zinc-50 dark:bg-zinc-700/30 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="font-medium text-zinc-900 dark:text-white">Tags</h3>
                </div>
                <div class="p-4">
                    @if (count($tags) > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach ($tags as $tag)
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-md bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/30">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-zinc-500 dark:text-zinc-400 italic">No tags defined.</p>
                    @endif
                </div>
            </div>

            <!-- Creation Info -->
            <div
                class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <div
                    class="px-4 py-3 bg-zinc-50 dark:bg-zinc-700/30 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="font-medium text-zinc-900 dark:text-white">Creation Info</h3>
                </div>
                <div class="p-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-zinc-500 dark:text-zinc-400">Created</span>
                        <span
                            class="text-zinc-800 dark:text-zinc-200">{{ $testCase->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500 dark:text-zinc-400">Last Updated</span>
                        <span
                            class="text-zinc-800 dark:text-zinc-200">{{ $testCase->updated_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500 dark:text-zinc-400">ID</span>
                        <span
                            class="text-zinc-800 dark:text-zinc-200 font-mono text-xs">{{ $testCase->id }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
