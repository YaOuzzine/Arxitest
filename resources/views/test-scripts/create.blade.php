<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Arxitest') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Keep only one Lucide script to avoid duplication -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    @stack('scripts')
</head>
<style>
    /* Base styles */
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        box-shadow: 0 0 0 3px rgba(156, 163, 175, 0.1);
    }

    /* Framework selection styles */
    .framework-option input:checked+div {
        border-color: #3f3f46;
        background-color: #f8fafc;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .framework-options {
        --ring-color: rgba(139, 92, 246, 0.2);
    }

    .framework-option-group:focus-within {
        z-index: 10;
    }

    .peer-checked\:border-purple-500 {
        border-color: #8b5cf6;
    }

    .peer-checked\:bg-purple-50 {
        background-color: #f5f3ff;
    }

    .peer-focus-visible\:ring-4 {
        box-shadow: 0 0 0 4px var(--ring-color);
    }

    .framework-icon {
        transition: transform 0.2s ease;
    }

    .framework-option-group:hover .framework-icon {
        transform: scale(1.1);
    }

    /* Tab styles */
    .tabs-container {
        margin-top: 1.5rem;
    }

    .tabs {
        display: flex;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 1.5rem;
    }

    .tab {
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
    }

    .tab.active {
        color: #4f46e5;
        border-bottom-color: #4f46e5;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .tab-button {
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        border-bottom: 2px solid transparent;
        transition: all 0.2s;
    }

    .tab-button.active {
        border-bottom-color: #4f46e5;
        color: #4f46e5;
    }

    /* Modal styles */
    .modal {
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    .modal.show {
        opacity: 1 !important;
        transform: scale(1) !important;
    }

    /* Code editor styles */
    .code-editor {
        font-family: 'Courier New', monospace;
        font-size: 14px;
        line-height: 1.5;
        tab-size: 4;
    }

    .code-editor-container {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .code-preview {
        padding: 1rem;
        background-color: #f8fafc;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        overflow: auto;
        height: 400px;
        white-space: pre;
    }

    .code-line {
        display: flex;
    }

    .line-number {
        color: #94a3b8;
        text-align: right;
        padding-right: 0.75rem;
        user-select: none;
        width: 2.5rem;
        border-right: 1px solid #e2e8f0;
        margin-right: 0.75rem;
    }

    .line-content {
        width: 100%;
    }

    /* Syntax highlighting */
    .python-keyword {
        color: #8b5cf6;
    }

    .python-string {
        color: #10b981;
    }

    .python-comment {
        color: #94a3b8;
        font-style: italic;
    }

    .python-function {
        color: #3b82f6;
    }

    .python-class {
        color: #f59e0b;
    }

    /* Context summary styles */
    .context-tag {
        display: inline-flex;
        align-items: center;
        background-color: #eef2ff;
        color: #4f46e5;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .context-tag button {
        margin-left: 0.5rem;
        line-height: 0;
    }

    /* Status badge styles */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .status-badge.info {
        background-color: #e0f2fe;
        color: #0369a1;
    }

    .status-badge.success {
        background-color: #dcfce7;
        color: #15803d;
    }

    .status-badge.warning {
        background-color: #fef9c3;
        color: #854d0e;
    }

    .status-badge.error {
        background-color: #fee2e2;
        color: #b91c1c;
    }

    /* Terminal styles */
    .terminal {
        background-color: #1e293b;
        color: #e2e8f0;
        border-radius: 0.5rem;
        padding: 1rem;
        font-family: 'Courier New', monospace;
        height: 400px;
        overflow-y: auto;
    }

    .terminal-line {
        margin-bottom: 0.5rem;
        line-height: 1.5;
    }

    .terminal-prompt {
        color: #22c55e;
        font-weight: bold;
    }

    .terminal-info {
        color: #60a5fa;
    }

    .terminal-success {
        color: #4ade80;
    }

    .terminal-error {
        color: #f87171;
    }

    .terminal-warning {
        color: #facc15;
    }

    /* Button animations */
    .btn-click-effect {
        transition: transform 0.1s;
    }

    .btn-click-effect:active {
        transform: scale(0.97);
    }

    /* Ripple effect */
    .ripple {
        position: relative;
        overflow: hidden;
        transform: translate3d(0, 0, 0);
    }

    .ripple:after {
        content: "";
        display: block;
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        pointer-events: none;
        background-image: radial-gradient(circle, #fff 10%, transparent 10.01%);
        background-repeat: no-repeat;
        background-position: 50%;
        transform: scale(10, 10);
        opacity: 0;
        transition: transform .5s, opacity 1s;
    }

    .ripple:active:after {
        transform: scale(0, 0);
        opacity: .3;
        transition: 0s;
    }

    /* Spinner */
    .spinner {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }

    /* Responsive layout */
    @media (min-width: 1024px) {
        .tabs-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
    }
</style>

<div class="relative py-8">
    <!-- Animated background elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-gradient-to-br from-blue-100/30 to-purple-100/30 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -left-24 w-80 h-80 bg-gradient-to-tr from-green-100/20 to-blue-100/20 rounded-full blur-3xl"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <!-- Form Container -->
        <div class="bg-white/90 p-8 rounded-2xl shadow-xl border border-gray-100 backdrop-blur-sm transition-all duration-300 hover:shadow-2xl">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center">
                    <div class="mr-4 bg-gradient-to-br from-gray-800 to-gray-900 p-3 rounded-xl shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-white">
                            <path d="M14 2v6h6"></path>
                            <path d="M4 6v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6H6a2 2 0 0 0-2 2z"></path>
                            <path d="M12 18v-6"></path>
                            <path d="M9 15h6"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Create New Test Script</h2>
                </div>

                <!-- Status badge for showing generation status -->
                <div id="generation-status" class="hidden">
                    <span id="status-badge" class="status-badge info">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <span id="status-text">Ready</span>
                    </span>
                </div>
            </div>

            <!-- Main container with side-by-side layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left side - Form content -->
                <div>
                    <form method="POST" action="{{ route('test-scripts.store') }}" class="space-y-6" id="test-script-form">
                        @csrf

                        <!-- Test Suite Selection -->
                        <div class="form-group">
                            <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-gray-500">
                                    <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"></path>
                                </svg>
                                Test Suite
                            </label>
                            <div class="relative">
                                <select name="suite_id" required class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 focus:border-gray-400 focus:ring-2 focus:ring-gray-200/50 transition-all outline-none appearance-none">
                                    <option value="">Select Test Suite</option>
                                    @foreach ($testSuites as $suite)
                                        <option value="{{ $suite->id }}">{{ $suite->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                        <polyline points="7 3 7 8 15 8"></polyline>
                                    </svg>
                                        <path d="m6 9 6 6 6-6"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Script Name -->
                        <div class="form-group">
                            <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-gray-500">
                                    <path d="M17 6.1H3"></path>
                                    <path d="M21 12.1H3"></path>
                                    <path d="M15.1 18H3"></path>
                                </svg>
                                Script Name
                            </label>
                            <input type="text" name="name" required id="script-name" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-gray-400 focus:ring-2 focus:ring-gray-200/50 transition-all outline-none placeholder-gray-400" placeholder="Enter script name">
                        </div>

                        <!-- Framework Type -->
                        <div class="form-group">
                            <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-gray-500">
                                    <rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect>
                                    <rect x="9" y="9" width="6" height="6"></rect>
                                    <path d="M15 2v2"></path>
                                    <path d="M15 20v2"></path>
                                    <path d="M2 15h2"></path>
                                    <path d="M20 15h2"></path>
                                </svg>
                                Framework Type <span class="text-red-500 ml-1">*</span>
                            </label>

                            <div class="grid grid-cols-2 gap-4 framework-options">
                                @foreach($frameworkTypes as $type)
                                <div class="framework-option-group relative">
                                    <input type="radio" name="framework_type" value="{{ $type }}" id="framework-{{ $type }}" required class="absolute opacity-0 peer" @if($loop->first) checked @endif>

                                    <label for="framework-{{ $type }}" class="block p-4 rounded-xl border-2 border-gray-200 cursor-pointer transition-all hover:border-purple-300 hover:bg-purple-50 peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-focus-visible:ring-4 peer-focus-visible:ring-purple-200 h-full flex items-center space-x-3 relative">
                                        <!-- Checkmark badge -->
                                        <div class="absolute top-2 right-2 w-5 h-5 bg-purple-500 rounded-full flex items-center justify-center transform scale-0 peer-checked:scale-100 transition-transform">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>

                                        <!-- Framework Icon -->
                                        <div class="framework-icon text-gray-500 peer-checked:text-purple-600 transition-colors">
                                            @if($type === 'selenium_python')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                                <!-- Python/Selenium icon paths -->
                                            </svg>
                                            @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                                <!-- Playwright icon paths -->
                                            </svg>
                                            @endif
                                        </div>

                                        <!-- Framework Label -->
                                        <span class="font-medium text-gray-700 peer-checked:text-purple-800 transition-colors">
                                            {{ str_replace('_', ' ', $type) }}
                                        </span>
                                    </label>
                                </div>
                                @endforeach
                            </div>

                            <!-- Validation Message -->
                            <p class="mt-2 text-sm text-red-600 hidden" id="framework-error">Please select a framework type</p>
                        </div>

                        <!-- Jira Story Integration -->
                        <div class="form-group">
                            <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-gray-500">
                                    <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"></path>
                                </svg>
                                Link to Jira Story (optional)
                            </label>
                            <div class="relative">
                                <select name="jira_story_id" id="jira-story-select" class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 focus:border-gray-400 focus:ring-2 focus:ring-gray-200/50 transition-all outline-none appearance-none">
                                    <option value="">Select Jira Story</option>
                                    @foreach ($jiraStories as $story)
                                        <option value="{{ $story->id }}">{{ $story->jira_key }} - {{ $story->title }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-gray-400">
                                        <path d="m6 9 6 6 6-6"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- AI Context Button -->
                        <div class="form-group">
                            <button type="button" id="context-button" class="flex items-center px-4 py-3 bg-gradient-to-br from-green-500 to-emerald-600 text-white rounded-lg w-full hover:shadow-lg transition-all duration-300 hover:scale-[1.02] justify-center ripple btn-click-effect">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M12 8v8"></path>
                                    <path d="M8 12h8"></path>
                                </svg>
                                <span id="context-button-text">Add Context for AI Generation</span>
                            </button>
                        </div>

                        <!-- Context info summary (initially hidden) -->
                        <div id="context-summary" class="hidden p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="font-medium text-gray-700">Context Information</h4>
                                <button type="button" id="edit-context-btn" class="text-blue-600 text-sm hover:text-blue-800">
                                    Edit
                                </button>
                            </div>
                            <div id="context-tags" class="flex flex-wrap"></div>
                        </div>

                        <!-- AI Generation Button -->
                        <div class="form-group">
                            <button type="button" id="ai-generate-btn" class="flex items-center px-4 py-3 bg-gradient-to-br from-purple-500 to-indigo-600 text-white rounded-lg w-full hover:shadow-lg transition-all duration-300 hover:scale-[1.02] justify-center ripple btn-click-effect">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-2">
                                    <path d="m12 2 4.24 4.24-4.24 4.25-4.24-4.24L12 2Z"></path>
                                    <path d="m15.5 8.5 4.24 4.24-4.24 4.25-4.24-4.24L15.5 8.5Z"></path>
                                    <path d="m8.5 8.5 4.24 4.24-4.24 4.25L4.26 12.7 8.5 8.5Z"></path>
                                    <path d="m12 15 4.24 4.24-4.24 4.25-4.24-4.24L12 15Z"></path>
                                </svg>
                                <span id="generate-button-text">Generate Script with AI</span>
                            </button>
                        </div>

                        <!-- Hidden Script Content -->
                        <input type="hidden" name="script_content" id="script-content-input">

                        <!-- Submit Button -->
                        <div class="pt-4 border-t border-gray-100">
                            <button type="submit" id="submit-btn" class="w-full py-3.5 bg-gradient-to-br from-gray-800 to-gray-900 text-white rounded-xl font-semibold hover:shadow-lg transition-all duration-300 hover:scale-[1.02] flex items-center justify-center ripple btn-click-effect">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-2">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                <span id="submit-button-text">Create Test Script</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right side - Terminal/Script Preview -->
                <div class="right-side-content">
                    <!-- Terminal Output (make visible from the start) -->
                    <div id="terminal-container" class="">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Terminal Output</h3>
                        </div>
                        <div class="terminal" id="terminal-output">
                            <div class="terminal-line">
                                <span class="terminal-prompt">$ </span>
                                <span>Awaiting commands...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Script Preview (initially hidden) -->
                    <div id="script-preview-container" class="hidden">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Test Script</h3>
                            <div class="flex space-x-2">
                                <button type="button" id="copy-btn" class="text-gray-600 hover:text-gray-900 p-1 rounded hover:bg-gray-100 transition-colors btn-click-effect" title="Copy to clipboard">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                        <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                        <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path>
                                    </svg>
                                </button>
                                <button type="button" id="edit-btn" class="text-gray-600 hover:text-gray-900 p-1 rounded hover:bg-gray-100 transition-colors btn-click-effect" title="Edit script">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                                    </svg>
                                </button>
                                <button type="button" id="download-btn" class="text-gray-600 hover:text-gray-900 p-1 rounded hover:bg-gray-100 transition-colors btn-click-effect" title="Download script">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" x2="12" y1="15" y2="3"></line>
                                    </svg>
                                </button>
                                <button type="button" id="save-script-btn" class="text-gray-600 hover:text-gray-900 p-1 rounded hover:bg-gray-100 transition-colors btn-click-effect" title="Save script">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                        <polyline points="7 3 7 8 15 8"></polyline>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="code-editor-container bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                            <div class="flex items-center justify-between bg-gray-50 px-4 py-2 border-b border-gray-200">
                                <div class="flex items-center space-x-1">
                                    <span class="inline-block w-3 h-3 bg-red-400 rounded-full"></span>
                                    <span class="inline-block w-3 h-3 bg-yellow-400 rounded-full"></span>
                                    <span class="inline-block w-3 h-3 bg-green-400 rounded-full"></span>
                                </div>
                                <div class="text-xs text-gray-500 font-medium" id="preview-filename">
                                    test_script.py
                                </div>
                                <div class="text-xs text-gray-400" id="preview-language">Python</div>
                            </div>

                            <!-- Script content preview -->
                            <div class="code-preview code-editor" id="script-preview">
                                <div class="code-line">
                                    <div class="line-number">1</div>
                                    <div class="line-content">
                                        <span class="python-comment"># No script generated yet. Click "Generate Script with AI" to create a test script.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Context Modal -->
        <div id="contextModal" class="fixed inset-0 bg-black/50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-2xl p-6 max-w-xl w-full transform transition-all modal opacity-0 scale-95">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold">Add Context for AI Generation</h3>
                        <button id="close-context-modal-btn" type="button" class="text-gray-500 hover:text-gray-700 btn-click-effect">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="w-5 h-5">
                                <path d="M18 6 6 18"></path>
                                <path d="m6 6 12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- User Stories -->
                        <div class="form-group">
                            <label class="text-sm font-medium text-gray-700 mb-2 block">User Stories</label>
                            <select id="modal-user-stories" multiple
                                class="w-full px-4 py-2 rounded-lg border border-gray-200">
                                @foreach ($jiraStories as $story)
                                    <option value="{{ $story->id }}">{{ $story->jira_key }} - {{ $story->title }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple stories</p>
                        </div>

                        <!-- Project Description -->
                        <div class="form-group">
                            <label class="text-sm font-medium text-gray-700 mb-2 block">Project Description</label>
                            <textarea id="modal-project-description" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-200"
                                placeholder="Describe the project or application being tested..."></textarea>
                        </div>

                        <!-- Custom Instructions -->
                        <div class="form-group">
                            <label class="text-sm font-medium text-gray-700 mb-2 block">Custom Instructions</label>
                            <textarea id="modal-custom-instructions" rows="4" class="w-full px-4 py-2 rounded-lg border border-gray-200"
                                placeholder="Add specific instructions for test generation..."></textarea>
                        </div>

                        <!-- File Upload -->
                        <div class="form-group">
                            <label class="text-sm font-medium text-gray-700 mb-2 block">Upload Files (optional)</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                                <input type="file" id="modal-files" multiple class="hidden">
                                <div id="modal-file-upload-trigger" class="cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="w-6 h-6 text-gray-400 mx-auto mb-2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" x2="12" y1="3" y2="15"></line>
                                    </svg>
                                    <p class="text-sm text-gray-600">Click to browse files</p>
                                </div>

                                <div id="modal-file-list" class="mt-3 text-left hidden">
                                    <h4 class="text-xs font-medium text-gray-700 mb-1">Uploaded Files:</h4>
                                    <ul id="modal-uploaded-files-list" class="text-xs text-gray-600"></ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button id="cancel-context-btn" type="button"
                            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 btn-click-effect">
                            Cancel
                        </button>
                        <button id="apply-context-btn" type="button"
                            class="px-4 py-2 bg-gradient-to-br from-green-500 to-emerald-600 text-white rounded-lg hover:shadow-md ripple btn-click-effect">
                            Apply Context
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div id="editModal" class="fixed inset-0 bg-black/50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-2xl p-6 max-w-4xl w-full transform transition-all modal opacity-0 scale-95">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Edit Script</h3>
                        <button id="close-edit-modal-btn" type="button" class="text-gray-500 hover:text-gray-700 btn-click-effect">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="w-5 h-5">
                                <path d="M18 6 6 18"></path>
                                <path d="m6 6 12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <textarea id="edit-script-content" class="w-full h-80 px-4 py-3 rounded-lg border border-gray-200 font-mono text-sm"
                        spellcheck="false"></textarea>
                    <div class="flex justify-end space-x-3 mt-4">
                        <button id="cancel-edit-btn" type="button"
                            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 btn-click-effect">
                            Cancel
                        </button>
                        <button id="apply-changes-btn" type="button"
                            class="px-4 py-2 bg-gradient-to-br from-gray-800 to-gray-900 text-white rounded-lg hover:shadow-md ripple btn-click-effect">
                            Apply Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // DOM Elements
        const contextButton = document.getElementById('context-button');
        const contextModal = document.getElementById('contextModal');
        const editModal = document.getElementById('editModal');
        const closeContextModalBtn = document.getElementById('close-context-modal-btn');
        const cancelContextBtn = document.getElementById('cancel-context-btn');
        const applyContextBtn = document.getElementById('apply-context-btn');
        const editContextBtn = document.getElementById('edit-context-btn');
        const contextSummary = document.getElementById('context-summary');
        const contextTags = document.getElementById('context-tags');
        const editBtn = document.getElementById('edit-btn');
        const closeEditModalBtn = document.getElementById('close-edit-modal-btn');
        const cancelEditBtn = document.getElementById('cancel-edit-btn');
        const applyChangesBtn = document.getElementById('apply-changes-btn');
        const scriptPreview = document.getElementById('script-preview');
        const scriptContentInput = document.getElementById('script-content-input');
        const editScriptContent = document.getElementById('edit-script-content');
        const scriptPreviewContainer = document.getElementById('script-preview-container');
        const terminalContainer = document.getElementById('terminal-container');
        const terminalOutput = document.getElementById('terminal-output');
        const aiGenerateBtn = document.getElementById('ai-generate-btn');

        // Framework type handling
        const frameworkRadios = document.querySelectorAll('input[name="framework_type"]');
        frameworkRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                updateFrameworkSelection(this);
            });
        });

        // Initialize the first framework as selected
        if(frameworkRadios.length > 0) {
            updateFrameworkSelection(frameworkRadios[0]);
        }

        // Context Modal Functionality
        contextButton.addEventListener('click', function() {
            showModal(contextModal);
        });

        closeContextModalBtn.addEventListener('click', function() {
            hideModal(contextModal);
        });

        cancelContextBtn.addEventListener('click', function() {
            hideModal(contextModal);
        });

        applyContextBtn.addEventListener('click', function() {
            // Gather context information
            const projectDescription = document.getElementById('modal-project-description').value;
            const customInstructions = document.getElementById('modal-custom-instructions').value;
            const userStories = Array.from(document.getElementById('modal-user-stories').selectedOptions)
                .map(option => option.text);

            // Update UI to show context has been added
            contextSummary.classList.remove('hidden');
            contextButton.querySelector('#context-button-text').textContent = 'Edit Context Information';

            // Clear existing tags
            contextTags.innerHTML = '';

            // Add new tags
            if (projectDescription) {
                addContextTag('Project Description');
            }

            if (customInstructions) {
                addContextTag('Custom Instructions');
            }

            userStories.forEach(story => {
                addContextTag(`Story: ${story.split(' - ')[0]}`);
            });

            // Hide modal
            hideModal(contextModal);
        });

        editContextBtn.addEventListener('click', function() {
            showModal(contextModal);
        });

        // Edit Modal Functionality
        editBtn.addEventListener('click', function() {
            // Copy content from preview to editor
            editScriptContent.value = getCurrentScriptContent();
            showModal(editModal);
        });

        closeEditModalBtn.addEventListener('click', function() {
            hideModal(editModal);
        });

        cancelEditBtn.addEventListener('click', function() {
            hideModal(editModal);
        });

        applyChangesBtn.addEventListener('click', function() {
            // Update preview with edited content
            updateScriptPreview(editScriptContent.value);
            scriptContentInput.value = editScriptContent.value;
            hideModal(editModal);
        });

        // AI Generate Button
        aiGenerateBtn.addEventListener('click', function() {
            // Show loading state
            this.disabled = true;
            this.querySelector('#generate-button-text').textContent = 'Generating...';

            // Show terminal output
            addTerminalLine('Starting AI script generation...', 'info');

            // Get selected framework
            const selectedFramework = document.querySelector('input[name="framework_type"]:checked').value;
            const selectedJiraStory = document.getElementById('jira-story-select').value;

            // Simulate AI generation
            setTimeout(() => {
                addTerminalLine('Analyzing requirements...', 'info');

                setTimeout(() => {
                    addTerminalLine('Generating test script draft...', 'info');

                    setTimeout(() => {
                        // Sample script based on framework
                        let generatedScript = '';

                        if (selectedFramework === 'selenium_python') {
                            generatedScript = `import unittest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

class TestExample(unittest.TestCase):
    def setUp(self):
        self.driver = webdriver.Chrome()
        self.driver.maximize_window()
        self.driver.get('https://example.com')

    def test_example_functionality(self):
        # Wait for elements to be visible
        WebDriverWait(self.driver, 10).until(
            EC.visibility_of_element_located((By.ID, 'username'))
        )

        # Enter username
        username_field = self.driver.find_element(By.ID, 'username')
        username_field.send_keys('testuser')

        # Enter password
        password_field = self.driver.find_element(By.ID, 'password')
        password_field.send_keys('password123')

        # Click login button
        login_button = self.driver.find_element(By.ID, 'login-button')
        login_button.click()

        # Assert successful login
        WebDriverWait(self.driver, 10).until(
            EC.presence_of_element_located((By.CLASS_NAME, 'welcome-message'))
        )
        welcome_message = self.driver.find_element(By.CLASS_NAME, 'welcome-message')
        self.assertTrue(welcome_message.is_displayed())

    def tearDown(self):
        self.driver.quit()

if __name__ == '__main__':
    unittest.main()`;
                        } else {
                            generatedScript = `// Cypress Test Example
describe('Login Test', () => {
  beforeEach(() => {
    cy.visit('https://example.com')
  })

  it('should log in successfully', () => {
    // Enter username
    cy.get('#username').type('testuser')

    // Enter password
    cy.get('#password').type('password123')

    // Click login button
    cy.get('#login-button').click()

    // Assert successful login
    cy.get('.welcome-message').should('be.visible')
  })
})`;
                        }

                        // Update script preview
                        updateScriptPreview(generatedScript);
                        scriptContentInput.value = generatedScript;

                        // Show script preview, hide terminal
                        terminalContainer.classList.add('hidden');
                        scriptPreviewContainer.classList.remove('hidden');

                        // Add success message to terminal
                        addTerminalLine('Script generated successfully!', 'success');

                        // Suggest a name if not already entered
                        const scriptNameInput = document.getElementById('script-name');
                        if (!scriptNameInput.value) {
                            scriptNameInput.value = 'Generated Test Script';
                        }

                        // Reset button state
                        aiGenerateBtn.disabled = false;
                        aiGenerateBtn.querySelector('#generate-button-text').textContent = 'Generate Script with AI';

                    }, 1000);
                }, 800);
            }, 500);
        });

        // Helper Functions
        function showModal(modal) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.querySelector('.modal').classList.add('show');
                modal.querySelector('.modal').style.opacity = '1';
                modal.querySelector('.modal').style.transform = 'scale(1)';
            }, 10);
        }

        function hideModal(modal) {
            const modalContent = modal.querySelector('.modal');
            modalContent.style.opacity = '0';
            modalContent.style.transform = 'scale(0.95)';
            modalContent.classList.remove('show');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

        function updateFrameworkSelection(selected) {
            // Remove previous selections
            document.querySelectorAll('.framework-option-group').forEach(option => {
                option.classList.remove('selected');
            });

            // Add visual feedback
            selected.closest('.framework-option-group').classList.add('selected');

            // Update form validation
            const errorElement = document.getElementById('framework-error');
            if (!selected.value) {
                errorElement.classList.remove('hidden');
            } else {
                errorElement.classList.add('hidden');
            }

            // Update dependent form elements based on selection
            updateFrameworkDependentFields(selected.value);
        }

        function updateFrameworkDependentFields(framework) {
            // Update filename preview based on framework
            const filenameElement = document.getElementById('preview-filename');
            filenameElement.textContent = framework === 'selenium_python'
                ? 'test_script.py'
                : 'test_script.spec.js';

            // Update language display
            document.getElementById('preview-language').textContent =
                framework === 'selenium_python' ? 'Python' : 'JavaScript';
        }

        function addContextTag(text) {
            const tag = document.createElement('div');
            tag.classList.add('context-tag');
            tag.innerHTML = `
                ${text}
                <button type="button" class="remove-tag">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            `;

            tag.querySelector('.remove-tag').addEventListener('click', function() {
                tag.remove();
                if (contextTags.children.length === 0) {
                    contextSummary.classList.add('hidden');
                    contextButton.querySelector('#context-button-text').textContent = 'Add Context for AI Generation';
                }
            });

            contextTags.appendChild(tag);
        }

        function getCurrentScriptContent() {
            // In a real app, this would parse the displayed content
            return scriptContentInput.value || '# No script content yet';
        }

        function updateScriptPreview(content) {
            // Clear existing content
            scriptPreview.innerHTML = '';

            // Split content into lines
            const lines = content.split('\n');

            // Create line elements with syntax highlighting
            lines.forEach((line, index) => {
                const lineElement = document.createElement('div');
                lineElement.classList.add('code-line');

                const lineNumber = document.createElement('div');
                lineNumber.classList.add('line-number');
                lineNumber.textContent = index + 1;

                const lineContent = document.createElement('div');
                lineContent.classList.add('line-content');

                // Simple syntax highlighting (in a real app, use a proper syntax highlighter)
                let highlightedLine = line;

                // Check for framework type
                const frameworkType = document.querySelector('input[name="framework_type"]:checked').value;

                if (frameworkType === 'selenium_python') {
                    // Python highlighting
                    highlightedLine = line
                        .replace(/(import|from|def|class|if|else|return|self|for|in|as|with)/g, '<span class="python-keyword">$1</span>')
                        .replace(/(["'].*?["'])/g, '<span class="python-string">$1</span>')
                        .replace(/(#.*)/g, '<span class="python-comment">$1</span>')
                        .replace(/(\w+)\(/g, '<span class="python-function">$1</span>(');
                } else {
                    // JavaScript highlighting
                    highlightedLine = line
                        .replace(/(const|let|var|function|if|else|return|this|for|of|in|await|async)/g, '<span class="python-keyword">$1</span>')
                        .replace(/(["'].*?["'])/g, '<span class="python-string">$1</span>')
                        .replace(/(\/\/.*)/g, '<span class="python-comment">$1</span>')
                        .replace(/(\w+)\(/g, '<span class="python-function">$1</span>(');
                }

                lineContent.innerHTML = highlightedLine || '&nbsp;';

                lineElement.appendChild(lineNumber);
                lineElement.appendChild(lineContent);
                scriptPreview.appendChild(lineElement);
            });
        }

        function addTerminalLine(text, type = '') {
            const line = document.createElement('div');
            line.classList.add('terminal-line');

            if (type) {
                line.innerHTML = `<span class="terminal-${type}">${text}</span>`;
            } else {
                line.innerHTML = `<span class="terminal-prompt">$ </span><span>${text}</span>`;
            }

            terminalOutput.appendChild(line);
            terminalOutput.scrollTop = terminalOutput.scrollHeight;
        }

        // Initialize file upload trigger
        const fileUploadTrigger = document.getElementById('modal-file-upload-trigger');
        const fileInput = document.getElementById('modal-files');
        const fileList = document.getElementById('modal-file-list');
        const uploadedFilesList = document.getElementById('modal-uploaded-files-list');

        fileUploadTrigger.addEventListener('click', function() {
            fileInput.click();
        });

        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileList.classList.remove('hidden');
                uploadedFilesList.innerHTML = '';

                Array.from(this.files).forEach(file => {
                    const listItem = document.createElement('li');
                    listItem.classList.add('mb-1');
                    listItem.textContent = `${file.name} (${formatFileSize(file.size)})`;
                    uploadedFilesList.appendChild(listItem);
                });
            } else {
                fileList.classList.add('hidden');
            }
        });

        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' bytes';
            else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            else return (bytes / 1048576).toFixed(1) + ' MB';
        }

        // Initialize copy button
        const copyBtn = document.getElementById('copy-btn');
        copyBtn.addEventListener('click', function() {
            navigator.clipboard.writeText(getCurrentScriptContent())
                .then(() => {
                    const originalTitle = this.getAttribute('title');
                    this.setAttribute('title', 'Copied!');
                    setTimeout(() => {
                        this.setAttribute('title', originalTitle);
                    }, 2000);
                });
        });

        // Initialize download button
        const downloadBtn = document.getElementById('download-btn');
        downloadBtn.addEventListener('click', function() {
            const content = getCurrentScriptContent();
            const frameworkType = document.querySelector('input[name="framework_type"]:checked').value;
            const fileName = frameworkType === 'selenium_python' ? 'test_script.py' : 'test_script.js';

            const blob = new Blob([content], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = fileName;
            a.click();
            URL.revokeObjectURL(url);
        });

        // Handle escape key for modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (!contextModal.classList.contains('hidden')) {
                    hideModal(contextModal);
                }
                if (!editModal.classList.contains('hidden')) {
                    hideModal(editModal);
                }
            }
        });

        // Click outside to close modals
        contextModal.addEventListener('click', function(e) {
            if (e.target === contextModal) {
                hideModal(contextModal);
            }
        });

        editModal.addEventListener('click', function(e) {
            if (e.target === editModal) {
                hideModal(editModal);
            }
        });
    });
</script>
