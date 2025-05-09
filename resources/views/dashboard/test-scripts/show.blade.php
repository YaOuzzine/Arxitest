{{-- resources/views/dashboard/test-scripts/show.blade.php --}}
@extends('layouts.dashboard')

@section('title', "{$testScript->name}")

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.show', $project->id) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">{{ $project->name }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">{{ $testCase->title }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id]) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">Test Scripts</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">{{ $testScript->name }}</span>
    </li>
@endsection

@section('content')
    <div class="h-full space-y-6" x-data="testScriptViewer">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-zinc-800 dark:from-zinc-100 to-zinc-600 dark:to-zinc-300 bg-clip-text text-transparent tracking-tight">
                    {{ $testScript->name }}
                </h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Test script for {{ $testCase->title }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id]) }}"
                   class="btn-secondary px-4 py-2 rounded-lg flex items-center">
                    <i data-lucide="chevron-left" class="w-4 h-4 mr-2"></i>
                    Back to Scripts
                </a>
                <button @click="openDeleteModal" type="button"
                   class="btn-danger px-4 py-2 rounded-lg flex items-center">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
                    Delete
                </button>
                <a href="{{ route('dashboard.projects.test-cases.scripts.edit', [$project->id, $testCase->id, $testScript->id]) }}"
                   class="btn-primary px-4 py-2 rounded-lg flex items-center">
                    <i data-lucide="pencil" class="w-4 h-4 mr-2"></i>
                    Edit
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Details Card -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/50 dark:bg-zinc-800/50">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-white">Details</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Framework Type</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ match($testScript->framework_type) {
                                        'selenium-python' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                        'cypress' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                        default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700/30 dark:text-zinc-300',
                                    } }}">
                                    {{ match($testScript->framework_type) {
                                        'selenium-python' => 'Selenium Python',
                                        'cypress' => 'Cypress',
                                        'other' => 'Other',
                                        default => $testScript->framework_type,
                                    } }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Created By</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $testScript->creator->name ?? 'Unknown' }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Created At</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $testScript->created_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Last Updated</h3>
                            <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $testScript->updated_at->format('M d, Y H:i') }}
                            </p>
                        </div>

                        <!-- Test Case Reference -->
                        <div class="pt-2 mt-4 border-t border-zinc-100 dark:border-zinc-700">
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Test Case</h3>
                            <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}"
                               class="mt-1 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 flex items-center">
                                <i data-lucide="file-check-2" class="w-4 h-4 mr-1.5"></i>
                                <span>{{ $testCase->title }}</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="mt-6 bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/50 dark:bg-zinc-800/50">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-white">Actions</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <button @click="copyToClipboard" class="w-full flex items-center px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                            <i data-lucide="copy" class="w-4 h-4 mr-2 text-zinc-600 dark:text-zinc-400"></i>
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">Copy Code</span>
                        </button>

                        <button @click="downloadScript" class="w-full flex items-center px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                            <i data-lucide="download" class="w-4 h-4 mr-2 text-zinc-600 dark:text-zinc-400"></i>
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">Download Script</span>
                        </button>

                        <a href="{{ route('dashboard.projects.test-cases.scripts.edit', [$project->id, $testCase->id, $testScript->id]) }}"
                           class="w-full flex items-center px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                            <i data-lucide="pencil" class="w-4 h-4 mr-2 text-zinc-600 dark:text-zinc-400"></i>
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">Edit Script</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Script Content -->
            <div class="lg:col-span-3">
                <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/50 dark:bg-zinc-800/50 flex justify-between items-center">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-white">Script Content</h2>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">Language:</span>
                            <span class="text-xs font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $testScript->framework_type === 'cypress' ? 'JavaScript' : 'Python' }}
                            </span>
                        </div>
                    </div>
                    <div class="p-0 relative">
                        <div class="absolute right-4 top-4 z-10 flex space-x-2">
                            <button @click="toggleLineNumbers" class="p-1.5 rounded-md bg-white/80 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 shadow-sm backdrop-blur-sm text-xs flex items-center">
                                <i data-lucide="list-ordered" class="w-3.5 h-3.5 mr-1"></i>
                                <span x-text="showLineNumbers ? 'Hide Line Numbers' : 'Show Line Numbers'"></span>
                            </button>
                            <button @click="toggleWordWrap" class="p-1.5 rounded-md bg-white/80 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 shadow-sm backdrop-blur-sm text-xs flex items-center">
                                <i data-lucide="wrap-text" class="w-3.5 h-3.5 mr-1"></i>
                                <span x-text="wordWrap ? 'Disable Word Wrap' : 'Enable Word Wrap'"></span>
                            </button>
                        </div>
                        <pre :class="{ 'line-numbers': showLineNumbers, 'whitespace-pre-wrap': wordWrap, 'whitespace-pre': !wordWrap }"
                             class="language-{{ $testScript->framework_type === 'cypress' ? 'javascript' : 'python' }} overflow-auto max-h-screen p-6 text-sm" id="code-display">{{ $testScript->script_content }}</pre>
                    </div>
                </div>

                <!-- Test Case Context -->
                <div class="mt-6 bg-white dark:bg-zinc-800 shadow-sm rounded-xl border border-zinc-200/70 dark:border-zinc-700/50 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-200/50 dark:border-zinc-700/50 bg-zinc-50/50 dark:bg-zinc-800/50">
                        <h2 class="text-lg font-medium text-zinc-900 dark:text-white">Test Case Context</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Test Steps:</h3>
                            <ul class="mt-2 list-decimal pl-5 space-y-1">
                                @foreach($testCase->steps as $step)
                                    <li class="text-sm text-zinc-600 dark:text-zinc-400">{{ $step }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Expected Results:</h3>
                            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $testCase->expected_results }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <x-modals.delete-confirmation
            title="Delete Test Script"
            message="Are you sure you want to delete this test script?"
            itemName="'{{ addslashes($testScript->name) }}'"
            dangerText="This action cannot be undone."
            confirmText="Delete Script" />
    </div>
@endsection

@push('styles')
<style>
    .btn-secondary {
        @apply bg-white/50 dark:bg-zinc-700/50 border border-zinc-300/70 dark:border-zinc-600/50 hover:bg-zinc-50/70 dark:hover:bg-zinc-600/50 shadow-sm text-zinc-700 dark:text-zinc-300 transition-all;
    }

    .btn-primary {
        @apply bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm hover:shadow-md transition-all;
    }

    .btn-danger {
        @apply bg-red-500 hover:bg-red-600 text-white shadow-sm hover:shadow-md transition-all;
    }

    /* Syntax highlighting - Override for dark mode */
    .dark pre.language-python,
    .dark pre.language-javascript {
        background-color: #1a202c;
        color: #e2e8f0;
    }

    /* Line numbers styling */
    pre.line-numbers {
        position: relative;
        padding-left: 3.8em;
        counter-reset: linenumber;
    }

    pre.line-numbers > code {
        position: relative;
        white-space: inherit;
    }

    .line-numbers .line-numbers-rows {
        position: absolute;
        pointer-events: none;
        top: 0;
        font-size: 100%;
        left: -3.8em;
        width: 3em;
        letter-spacing: -1px;
        border-right: 1px solid #999;
        user-select: none;
    }

    .line-numbers-rows > span {
        display: block;
        counter-increment: linenumber;
    }

    .line-numbers-rows > span:before {
        content: counter(linenumber);
        color: #999;
        display: block;
        padding-right: 0.8em;
        text-align: right;
    }

    /* Word wrap styling */
    .whitespace-pre-wrap {
        white-space: pre-wrap;
    }

    .whitespace-pre {
        white-space: pre;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/prismjs/1.29.0/prism.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prismjs/1.29.0/components/prism-python.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prismjs/1.29.0/components/prism-javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prismjs/1.29.0/plugins/line-numbers/prism-line-numbers.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('testScriptViewer', () => ({
            showDeleteModal: false,
            deleteConfirmed: false,
            isDeleting: false,
            requireConfirmation: true,
            showLineNumbers: true,
            wordWrap: false,

            init() {
                // Initialize Prism syntax highlighting
                Prism.highlightAll();

                // Initialize line numbers
                if (this.showLineNumbers) {
                    Prism.plugins.lineNumbers.init();
                }
            },

            copyToClipboard() {
                const scriptContent = `{{ addslashes($testScript->script_content) }}`;
                navigator.clipboard.writeText(scriptContent).then(() => {
                    this.showNotification('info', 'Script copied to clipboard');
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                    this.showNotification('error', 'Failed to copy to clipboard');
                });
            },

            downloadScript() {
                const content = `{{ addslashes($testScript->script_content) }}`;
                const filename = `{{ str_replace(' ', '_', $testScript->name) }}.` +
                                 `{{ $testScript->framework_type === 'cypress' ? 'js' : 'py' }}`;

                const element = document.createElement('a');
                element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(content));
                element.setAttribute('download', filename);

                element.style.display = 'none';
                document.body.appendChild(element);
                element.click();
                document.body.removeChild(element);

                this.showNotification('success', `Downloaded ${filename}`);
            },

            openDeleteModal() {
                this.showDeleteModal = true;
                this.deleteConfirmed = false;
            },

            async confirmDelete() {
                this.isDeleting = true;

                try {
                    const url = "{{ route('dashboard.projects.test-cases.scripts.destroy', [$project->id, $testCase->id, $testScript->id]) }}";
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        window.location.href = "{{ route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id]) }}";
                    } else {
                        throw new Error(result.message || 'Failed to delete test script');
                    }
                } catch (error) {
                    console.error(error);
                    this.showNotification('error', error.message || 'An error occurred');
                } finally {
                    this.isDeleting = false;
                    this.showDeleteModal = false;
                }
            },

            toggleLineNumbers() {
                this.showLineNumbers = !this.showLineNumbers;

                // Re-highlight and re-initialize line numbers after toggling
                this.$nextTick(() => {
                    Prism.highlightAll();
                    if (this.showLineNumbers) {
                        Prism.plugins.lineNumbers.init();
                    }
                });
            },

            toggleWordWrap() {
                this.wordWrap = !this.wordWrap;
            },

            showNotification(type, message) {
                // Dispatch event to notification system
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { type, message }
                }));
            }
        }));
    });
</script>
@endpush
