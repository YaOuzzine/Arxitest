@php
    /**
     * @var \App\Models\Project $project
     * @var \App\Models\TestCase $testCase
     * @var \App\Models\TestSuite $testSuite
     * @var \App\Models\TestScript $testScript
     */
    $pageTitle = 'Test Script: ' . Str::limit($testScript->name, 50);

    // Determine the language for PrismJS based on framework type
    $frameworkLanguages = [
        'selenium-python' => 'python',
        'cypress' => 'javascript',
        'other' => 'markup', // Default or placeholder
    ];
    $scriptLanguage = $frameworkLanguages[$testScript->framework_type] ?? 'markup';

    // Check if AI generated
    $isAiGenerated = isset($testScript->metadata['created_through']) && $testScript->metadata['created_through'] === 'ai';

    // Get associated test case steps (handle potential JSON string)
    $steps = $testCase->steps ?? [];
    if (is_string($steps)) {
        $decodedSteps = json_decode($steps, true);
        $steps = is_array($decodedSteps) ? $decodedSteps : [];
    } elseif (!is_array($steps)) {
        $steps = [];
    }
@endphp

@extends('layouts.dashboard')

@section('title', $pageTitle)

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects') }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">Projects</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.show', $project->id) }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">{{ $project->name }}</a>
    </li>
    @if($testSuite) {{-- Include suite if available --}}
        <li class="flex items-center">
            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
            <a href="{{ route('dashboard.projects.test-suites.show', [$project->id, $testSuite->id]) }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">{{ Str::limit($testSuite->name, 25) }}</a>
        </li>
    @endif
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">{{ Str::limit($testCase->title, 30) }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">{{ Str::limit($testScript->name, 30) }}</span>
    </li>
@endsection

@section('content')
<div class="space-y-8" x-data>
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">{{ $testScript->name }}</h1>
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/40">
                    <i data-lucide="code" class="w-3.5 h-3.5 mr-1.5"></i>
                    {{ ucfirst(str_replace('-', ' ', $testScript->framework_type)) }}
                </span>
                <span class="text-sm text-zinc-500 dark:text-zinc-400 inline-flex items-center">
                    <i data-lucide="clock" class="inline-block w-3.5 h-3.5 mr-1.5"></i>
                    Created {{ $testScript->created_at->diffForHumans() }}
                </span>
                @if($isAiGenerated)
                    <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800/40">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 mr-1.5"></i> AI Generated
                    </span>
                @endif
                 @if($testScript->creator)
                    <span class="text-sm text-zinc-500 dark:text-zinc-400 inline-flex items-center">
                        <i data-lucide="user" class="inline-block w-3.5 h-3.5 mr-1.5"></i>
                         By {{ $testScript->creator->name }}
                    </span>
                @endif
            </div>
        </div>
        <div class="flex flex-shrink-0 gap-2">
            {{-- Direct link back to the scripts tab on the test case page --}}
            <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}#scripts" class="btn-secondary">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Test Case
            </a>
            {{-- Maybe add an edit button later if script editing becomes a feature --}}
            {{-- <button class="btn-secondary">
                <i data-lucide="edit-3" class="w-4 h-4 mr-1"></i> Edit Script
            </button> --}}
            <form method="POST" action="{{ route('dashboard.projects.test-cases.scripts.destroy', [$project->id, $testCase->id, $testScript->id]) }}"
                  onsubmit="return confirm('Are you sure you want to delete this script? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Delete Script
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content Area: Script Code -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-700/30 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
            <h3 class="font-medium text-zinc-800 dark:text-zinc-200">Script Content</h3>
            <button @click="copyToClipboard($refs.scriptContent.innerText)"
                    class="btn-copy"
                    x-data="{ copied: false }"
                    @click="copied = true; setTimeout(() => copied = false, 2000)">
                <i data-lucide="copy" class="w-4 h-4" x-show="!copied"></i>
                <i data-lucide="check" class="w-4 h-4 text-green-500" x-show="copied" x-cloak></i>
                <span class="ml-1.5" x-text="copied ? 'Copied!' : 'Copy'"></span>
            </button>
        </div>
        {{-- The p-0 on the pre ensures the padding comes from the code block for correct background --}}
        <pre class="language-{{ $scriptLanguage }} !m-0 !p-0 max-h-[70vh] overflow-y-auto"><code x-ref="scriptContent" class="block !p-6">{{ $testScript->script_content }}</code></pre>
    </div>

    <!-- Associated Test Case Information (Context) -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-700/30 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
            <h3 class="font-medium text-zinc-800 dark:text-zinc-200">Associated Test Case</h3>
            <a href="{{ route('dashboard.projects.test-cases.show', [$project->id, $testCase->id]) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                View Full Details <i data-lucide="arrow-right" class="inline-block w-3.5 h-3.5 ml-1"></i>
            </a>
        </div>
        <div class="p-6 space-y-4">
            <h4 class="text-lg font-medium text-zinc-900 dark:text-white">{{ $testCase->title }}</h4>
            @if($testCase->description)
                <p class="text-zinc-600 dark:text-zinc-400 text-sm">{{ Str::limit($testCase->description, 200) }}</p>
            @endif

            <div>
                <h5 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Test Steps</h5>
                @if(count($steps) > 0)
                    <ol class="list-decimal list-inside space-y-1 text-sm text-zinc-700 dark:text-zinc-300 bg-zinc-50 dark:bg-zinc-700/30 p-4 rounded-md border border-zinc-200 dark:border-zinc-700">
                        @foreach($steps as $index => $step)
                            <li>{{ $step }}</li>
                        @endforeach
                    </ol>
                @else
                    <p class="text-zinc-500 dark:text-zinc-400 italic text-sm">No steps defined for this test case.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Notification Area -->
    <div x-data="notification" x-show="show" x-cloak
         x-transition:enter="transform ease-out duration-300 transition"
         x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
         x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-4 right-4 w-full max-w-sm p-4 rounded-lg shadow-lg pointer-events-auto z-50"
         :class="{
             'bg-green-50 dark:bg-green-800/90 border border-green-200 dark:border-green-700': type === 'success',
             'bg-red-50 dark:bg-red-800/90 border border-red-200 dark:border-red-700': type === 'error'
         }">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                 <i data-lucide="check-circle" class="w-6 h-6 text-green-500" x-show="type === 'success'"></i>
                 <i data-lucide="alert-circle" class="w-6 h-6 text-red-500" x-show="type === 'error'"></i>
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
                 <p class="text-sm font-medium" :class="{ 'text-green-800 dark:text-green-100': type === 'success', 'text-red-800 dark:text-red-100': type === 'error' }" x-text="message"></p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                 <button @click="show = false" class="inline-flex rounded-md p-1 focus:outline-none focus:ring-2 focus:ring-offset-2"
                         :class="{
                             'text-green-500 hover:bg-green-100 dark:hover:bg-green-700 focus:ring-green-600 dark:focus:ring-offset-green-800': type === 'success',
                             'text-red-500 hover:bg-red-100 dark:hover:bg-red-700 focus:ring-red-600 dark:focus:ring-offset-red-800': type === 'error'
                         }">
                     <span class="sr-only">Close</span>
                     <i data-lucide="x" class="w-5 h-5"></i>
                 </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
{{-- Include PrismJS theme --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" integrity="sha512-vswe+cgvic/XBoF1OcM/TeJ2FW0OofqAVdCZiEYkd6dwGXuxGoVZSgoqvPKrG4+DingPYFKcCZmHAIU5xyzY解答==" crossorigin="anonymous" referrerpolicy="no-referrer" />
{{-- Alternative theme: Okaidia --}}
{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-okaidia.min.css" integrity="sha512-mIs9kKbaw6JZFfSuo+MovjU+Ntggfoj8RwAmJbVXQ5mkAX5LlgETQEweFPI18humSPHymTb5iikEOKWF7I8ncQ==" crossorigin="anonymous" referrerpolicy="no-referrer" /> --}}
<style>
    /* Button Styles */
    .btn-primary { @apply inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed; }
    .btn-secondary { @apply inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 font-medium rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed; }
    .btn-danger { @apply inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed; }
    .btn-copy { @apply inline-flex items-center justify-center px-3 py-1.5 text-sm bg-zinc-100 dark:bg-zinc-700 border border-zinc-200 dark:border-zinc-600 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-600 font-medium rounded-md shadow-sm transition-colors; }

    /* Code Highlighting */
    pre[class*="language-"] { @apply text-sm leading-relaxed; }
    :not(pre) > code[class*="language-"], pre[class*="language-"] { background: #f8fafc; /* Light background for light mode */ }
    .dark :not(pre) > code[class*="language-"], .dark pre[class*="language-"] { background: #18181b; /* Dark background for dark mode */ }
    pre[class*="language-"] code { display: block; } /* Ensure code block takes full width */
    .token.comment, .token.prolog, .token.doctype, .token.cdata { @apply text-zinc-500 dark:text-zinc-400; }
    .token.punctuation { @apply text-zinc-600 dark:text-zinc-400; }
    .token.property, .token.tag, .token.boolean, .token.number, .token.constant, .token.symbol, .token.deleted { @apply text-purple-600 dark:text-purple-400; }
    .token.selector, .token.attr-name, .token.string, .token.char, .token.builtin, .token.inserted { @apply text-emerald-600 dark:text-emerald-400; }
    .token.operator, .token.entity, .token.url, .language-css .token.string, .style .token.string { @apply text-amber-700 dark:text-amber-500; }
    .token.atrule, .token.attr-value, .token.keyword { @apply text-sky-600 dark:text-sky-400; }
    .token.function, .token.class-name { @apply text-pink-600 dark:text-pink-400; }
    .token.regex, .token.important, .token.variable { @apply text-yellow-600 dark:text-yellow-400; }

    [x-cloak] { display: none !important; }
</style>
@endpush

@push('scripts')
{{-- Include PrismJS Core & Components --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js" integrity="sha512-7Z9J3l1+EYfeaPKcGXu3MS/7BLOQmLpoTsAbMTyog+Kmy8Џ1MLXMH4Q7mvN+6hQMER+7IUcudCLD7b/q+/mDQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js" integrity="sha512-SkmBfuA2hqjzEVpmnMt/LINrjDhDHjXCqwsllmJNCDHEVLcwjDqfbYf9hPec6pvQO/+JiS9J7Gf6+mFk07kqBQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
{{-- Explicitly include common languages --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js" integrity="sha512-AKaNmg/7cgoALCU5Ym9JbUSGTz0KXvuRcV5I9Ua/qOPGIMI/6nMCFCWJ78SMOE4YQEJjOsZyrV3/7urTGC9QkQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js" integrity="sha512-jwrwRWZAbkLEMLrbzLytL9BIJM8/1MvSknYZLHI501BHP+2KqS6Kk3tL9CHJDsF5Lj49Xh87jTmT9AXW/1h0DQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
document.addEventListener('alpine:init', () => {
    // Initialize Alpine component for notifications
    Alpine.data('notification', () => ({
        show: false,
        message: '',
        type: 'success',
        timeout: null,
        init() {
            window.addEventListener('notify', event => {
                this.message = event.detail.message;
                this.type = event.detail.type || 'success';
                this.show = true;
                if (this.timeout) clearTimeout(this.timeout);
                this.timeout = setTimeout(() => this.show = false, 5000);
                // Re-init icons when notification appears
                 this.$nextTick(() => {
                     if (typeof lucide !== 'undefined') lucide.createIcons();
                 });
            });
        }
    }));

    // Global copy function
    window.copyToClipboard = function(text) {
        navigator.clipboard.writeText(text).then(
            () => window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Script copied to clipboard!', type: 'success' }})),
            (err) => window.dispatchEvent(new CustomEvent('notify', { detail: { message: `Failed to copy: ${err}`, type: 'error' }}))
        );
    }
});

// Initialize Prism and Lucide after DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Prism !== 'undefined') {
        Prism.highlightAll();
    }
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

</script>
@endpush
