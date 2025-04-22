{{-- resources/views/integrations/jira/import-existing-project.blade.php --}}
@extends('layouts.dashboard') {{-- Assuming you have a base dashboard layout --}}
@section('title', 'Import to Existing Project from Jira')

{{-- Inject Arxitest Project ID into JS --}}
@push('head-scripts')
<script>
    window.arxitestProjectId = {{ $arxitestProjectId }}; // Pass project ID for JS usage
</script>
@endpush

{{-- Breadcrumbs Section --}}
@section('breadcrumbs')
<li>
    <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
    <a href="{{ route('dashboard.integrations.index') }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">Integrations</a>
</li>
<li>
    <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
    <a href="{{ route('integrations.jira.import.options') }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">Import from Jira</a>
</li>
<li>
    <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
    <span class="text-zinc-700 dark:text-zinc-300">Existing Project</span>
</li>
@endsection

{{-- Main Content Section --}}
@section('content')
{{-- Alpine Component Root --}}
<div class="page-transition" x-data="jiraImportApp()">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white tracking-tight mb-2">Import to Existing Project from Jira</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Import Jira issues as test cases (and optionally test suites) into your selected Arxitest project.</p>
    </div>

    <div class="mb-8 bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
        <div class="flex items-start sm:items-center flex-col sm:flex-row">
            <div class="p-3 rounded-xl bg-gradient-to-br from-purple-600 to-pink-500 shadow-lg mr-0 mb-3 sm:mr-4 sm:mb-0 flex-shrink-0">
                <i data-lucide="folder-sync" class="h-6 w-6 text-white"></i>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $arxitestProjectName }}</h2>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">You are importing Jira issues into this Arxitest project.</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
             <div class="flex items-center">
                {{-- Step 1 Indicator --}}
                <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm" :class="{
                        'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400': step > 1,
                        'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400': step == 1,
                    }">
                    <span x-show="step > 1"><i data-lucide="check" class="w-5 h-5"></i></span>
                    <span x-show="step <= 1">1</span>
                </div>
                <div class="ml-2 mr-6">
                    <p class="text-sm font-medium" :class="step == 1 ? 'text-zinc-900 dark:text-white' : 'text-zinc-600 dark:text-zinc-400'">Select Source & Target</p> {{-- Adjusted title --}}
                </div>
                <div class="flex-grow h-0.5 bg-zinc-200 dark:bg-zinc-700"></div>

                {{-- Step 2 Indicator --}}
                <div class="mx-6 flex items-center justify-center w-8 h-8 rounded-full text-sm" :class="{
                        'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400': step > 2,
                        'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400': step == 2,
                        'bg-zinc-100 dark:bg-zinc-700/50 text-zinc-400 dark:text-zinc-500': step < 2
                    }">
                    <span x-show="step > 2"><i data-lucide="check" class="w-5 h-5"></i></span>
                    <span x-show="step <= 2">2</span>
                </div>
                <div class="ml-2 mr-6">
                    <p class="text-sm font-medium" :class="{
                        'text-zinc-900 dark:text-white': step == 2,
                        'text-zinc-600 dark:text-zinc-400': step > 2,
                        'text-zinc-400 dark:text-zinc-500': step < 2
                    }">Configure Import</p>
                </div>
                <div class="flex-grow h-0.5 bg-zinc-200 dark:bg-zinc-700"></div>

                {{-- Step 3 Indicator --}}
                <div class="ml-6 flex items-center justify-center w-8 h-8 rounded-full text-sm" :class="{
                        'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400': step == 3 && !isImporting && !importCompleted && !importError,
                        'bg-zinc-100 dark:bg-zinc-700/50 text-zinc-400 dark:text-zinc-500': step < 3 || isImporting || importCompleted || importError
                    }">
                    <span>3</span>
                </div>
                <div class="ml-2">
                    <p class="text-sm font-medium" :class="{
                        'text-zinc-900 dark:text-white': step == 3 && !isImporting && !importCompleted && !importError,
                        'text-zinc-400 dark:text-zinc-500': step < 3 || isImporting || importCompleted || importError
                    }">Review & Import</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div x-show="step == 1">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Select Source Jira Project & Target Suite</h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Choose the Jira project to import from and where the imported items should go within '{{ $arxitestProjectName }}'.</p>
                </div>

                <div class="mb-6">
                    <label for="jira_project" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Source Jira Project</label>
                    <select id="jira_project" x-model="selectedJiraProject" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select a Jira project...</option>
                        @forelse($jiraProjects as $project)
                            <option value="{{ $project['key'] }}" data-name="{{ $project['name'] }}">{{ $project['name'] }} ({{ $project['key'] }})</option>
                        @empty
                             <option value="" disabled>No Jira projects found or integration not configured.</option>
                        @endforelse
                    </select>
                    @error('jira_project_key') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="mb-6">
                    <label for="test_suite" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Import into Arxitest Test Suite</label>
                    <select id="test_suite" x-model="selectedTestSuite" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select a target test suite...</option>
                        <option value="new">-- Create New Test Suites from Jira Epics --</option>
                        @forelse($testSuites as $suite)
                            <option value="{{ $suite->id }}" data-name="{{ $suite->name }}">{{ $suite->name }}</option>
                        @empty
                            {{-- Still allow 'new' even if no existing suites --}}
                        @endforelse
                    </select>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Choose an existing suite to add test cases to, or select the 'Create New' option to generate suites based on Jira Epics.</p>
                    @error('target_test_suite_id') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end pt-4">
                    <button type="button" @click="goToNextStep" :disabled="!selectedJiraProject || !selectedTestSuite" class="px-4 py-2 rounded-lg text-white font-medium bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-colors">
                        Next Step
                    </button>
                </div>
            </div>

            <div x-show="step == 2" x-cloak>
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Configure Import Options</h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Customize which Jira issue types become Arxitest items and apply filters.</p>
                </div>

                <div x-show="isLoading" class="flex justify-center items-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                </div>

                <div x-show="!isLoading && !error">
                    <div class="mb-6">
                        <h3 class="text-md font-medium text-zinc-800 dark:text-zinc-200 mb-2">Issue Type Mapping</h3>
                        <div class="space-y-2">
                            {{-- Conditionally show Epic mapping only if creating new suites --}}
                            <template x-if="selectedTestSuite === 'new'">
                                <label class="flex items-center p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
                                    <input type="checkbox" x-model="importEpics" class="rounded text-blue-600 focus:ring-blue-500 border-zinc-300 dark:border-zinc-500 bg-white dark:bg-zinc-700 shadow-sm">
                                    <span class="ml-3 text-sm text-zinc-700 dark:text-zinc-300">Import Jira <strong class="font-semibold">Epics</strong> as Arxitest <strong class="font-semibold">Test Suites</strong></span>
                                </label>
                            </template>
                            <label class="flex items-center p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
                                <input type="checkbox" x-model="importStories" class="rounded text-blue-600 focus:ring-blue-500 border-zinc-300 dark:border-zinc-500 bg-white dark:bg-zinc-700 shadow-sm">
                                <span class="ml-3 text-sm text-zinc-700 dark:text-zinc-300">Import Jira <strong class="font-semibold">Stories, Tasks, Bugs</strong> as Arxitest <strong class="font-semibold">Test Cases</strong></span>
                            </label>
                        </div>
                         <p x-show="!importStories && (selectedTestSuite !== 'new' || !importEpics)" class="mt-2 text-xs text-red-600 dark:text-red-400">Please select at least one issue type mapping to import.</p>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-md font-medium text-zinc-800 dark:text-zinc-200 mb-2">Additional Options</h3>
                        <div class="space-y-2">
                            <label class="flex items-center p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
                                <input type="checkbox" x-model="generateTestScripts" class="rounded text-blue-600 focus:ring-blue-500 border-zinc-300 dark:border-zinc-500 bg-white dark:bg-zinc-700 shadow-sm">
                                <span class="ml-3 text-sm text-zinc-700 dark:text-zinc-300">Attempt to Generate Test Script Steps from Acceptance Criteria (if found)</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-md font-medium text-zinc-800 dark:text-zinc-200">Advanced Filtering</h3>
                             <button type="button" @click="showAdvancedFilters = !showAdvancedFilters" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                <span x-text="showAdvancedFilters ? 'Hide Advanced Filters' : 'Show Advanced Filters'"></span>
                                <i :class="showAdvancedFilters ? 'rotate-180' : ''" data-lucide="chevron-down" class="inline-block w-3 h-3 ml-1 transition-transform"></i>
                            </button>
                        </div>

                        <div x-show="showAdvancedFilters" x-collapse x-cloak class="mt-2 space-y-4 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <div>
                                <label for="jql_filter" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Custom JQL Filter (Optional)</label>
                                <input type="text" id="jql_filter" x-model.lazy="jqlFilter" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="e.g., status = 'Ready for Test' AND labels = qa-approved">
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Enter a JQL query to filter issues within the selected Jira project. This will be combined with the project selection. <a href="https://support.atlassian.com/jira-software-cloud/docs/advanced-search-reference-jql-fields/" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">JQL Reference</a></p>
                            </div>

                            <div>
                                <label for="max_issues" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Maximum Issues to Import</label>
                                <input type="number" id="max_issues" x-model.number="maxIssues" min="1" max="1000" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Limit the number of issues imported (max 1000). Useful for testing or large projects.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="error" x-cloak class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 text-red-700 dark:text-red-300 text-sm" x-text="error"></div>

                <div class="flex justify-between pt-4">
                    <button type="button" @click="step = 1" class="px-4 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 font-medium bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 shadow-sm transition-colors">
                        Back
                    </button>
                    {{-- Disable button if no work would be done --}}
                    <button type="button" @click="goToPreview" :disabled="isLoading || (!importStories && (selectedTestSuite !== 'new' || !importEpics))" class="px-4 py-2 rounded-lg text-white font-medium bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-colors">
                        Preview Import
                    </button>
                </div>
            </div>

            <div x-show="step == 3 && !isImporting && !importCompleted && !importError" x-cloak>
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Review Import</h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Verify the details below. Items shown are a sample based on your configuration.</p>
                </div>

                <div x-show="isLoadingPreview" class="flex flex-col items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mb-4"></div>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Generating preview based on your selections...</p>
                </div>

                <div x-show="previewError" x-cloak class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 text-red-700 dark:text-red-300 text-sm">
                    <strong>Error generating preview:</strong> <span x-text="previewError"></span>
                    <p class="mt-2 text-xs">Please check your JQL syntax or try adjusting the import options.</p>
                </div>

                <div x-show="!isLoadingPreview && !previewError && preview">
                     <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 mb-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Project Details Column --}}
                            <div>
                                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-2">Import Details</h3>
                                <p class="text-sm text-zinc-900 dark:text-white mb-1">
                                    <span class="font-semibold text-zinc-600 dark:text-zinc-300">Source Jira Project:</span>
                                    <span x-text="selectedJiraProjectName || 'N/A'"></span> (<span x-text="selectedJiraProject || 'N/A'"></span>)
                                </p>
                                <p class="text-sm text-zinc-900 dark:text-white mb-1">
                                    <span class="font-semibold text-zinc-600 dark:text-zinc-300">Target Arxitest Project:</span>
                                    <span>{{ $arxitestProjectName }}</span>
                                </p>
                                {{-- Show target suite name or indication of new suites --}}
                                <p class="text-sm text-zinc-900 dark:text-white">
                                    <span class="font-semibold text-zinc-600 dark:text-zinc-300">Target Test Suite(s):</span>
                                    <template x-if="selectedTestSuite === 'new'"><span class="italic">New Suites from Epics</span></template>
                                    <template x-if="selectedTestSuite !== 'new'"><span x-text="selectedTestSuiteName || 'N/A'"></span></template>
                                </p>
                            </div>
                            {{-- Import Summary Column --}}
                            <div>
                                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-2">Estimated Import</h3>
                                <div class="space-y-1">
                                     <p class="text-sm text-zinc-900 dark:text-white flex justify-between">
                                        <span>Total Matching Issues Found:</span>
                                        <span class="font-semibold" x-text="preview.total_matching_issues != null ? preview.total_matching_issues : 'Calculating...'"></span>
                                    </p>
                                    {{-- Conditionally show Test Suites count --}}
                                    <template x-if="selectedTestSuite === 'new'">
                                        <p class="text-sm text-zinc-900 dark:text-white flex justify-between">
                                            <span>Test Suites (from Epics):</span>
                                            <span class="font-semibold" x-text="preview.potential_suites_count != null ? preview.potential_suites_count : 'Calculating...'"></span>
                                        </p>
                                    </template>
                                    <p class="text-sm text-zinc-900 dark:text-white flex justify-between" x-show="importStories">
                                        <span>Test Cases (from Issues):</span>
                                        <span class="font-semibold" x-text="preview.potential_cases_count != null ? preview.potential_cases_count : 'Calculating...'"></span>
                                    </p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 text-right mt-1" x-show="maxIssues > 0 && preview.total_matching_issues > maxIssues">(Limited to <span x-text="maxIssues"></span> issues)</p>
                                </div>
                            </div>
                        </div>
                        {{-- Configuration Summary Row --}}
                        <div class="mt-4 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                             <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-2">Configuration</h3>
                             <ul class="list-disc list-inside text-sm text-zinc-700 dark:text-zinc-300 space-y-1">
                                <li x-show="selectedTestSuite === 'new' && importEpics">Import Epics as Test Suites</li>
                                <li x-show="importStories">Import Stories, Tasks, Bugs as Test Cases</li>
                                <li x-show="generateTestScripts">Generate Test Scripts from Acceptance Criteria</li>
                                <li x-show="jqlFilter">Custom JQL Filter: <code class="text-xs bg-zinc-200 dark:bg-zinc-700 px-1 py-0.5 rounded" x-text="jqlFilter"></code></li>
                                <li x-show="maxIssues > 0 && maxIssues < 1000">Maximum Issues: <span x-text="maxIssues"></span></li>
                             </ul>
                        </div>
                    </div>

                    {{-- Preview Tables --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div x-show="selectedTestSuite === 'new' && importEpics && preview.sample_suites && preview.sample_suites.length > 0">
                             <h3 class="text-md font-medium text-zinc-800 dark:text-zinc-200 mb-2">Sample Test Suites to be Created</h3>
                            <div class="max-h-60 overflow-y-auto border border-zinc-200 dark:border-zinc-700 rounded-lg">
                                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                    <thead class="bg-zinc-50 dark:bg-zinc-800 sticky top-0">
                                        <tr>
                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Jira Key</th>
                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Epic Name / Suite Title</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                                        <template x-for="(suite, index) in preview.sample_suites" :key="'suite-'+index">
                                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-300 font-mono" x-text="suite.jira_key"></td>
                                                <td class="px-4 py-2 text-sm text-zinc-900 dark:text-zinc-100" x-text="suite.title"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1" x-show="preview.potential_suites_count > preview.sample_suites.length">Showing <span x-text="preview.sample_suites.length"></span> of <span x-text="preview.potential_suites_count"></span> potential new suites.</p>
                        </div>

                         <div x-show="importStories && preview.sample_cases && preview.sample_cases.length > 0">
                             <h3 class="text-md font-medium text-zinc-800 dark:text-zinc-200 mb-2">Sample Test Cases to be Created</h3>
                            <div class="max-h-60 overflow-y-auto border border-zinc-200 dark:border-zinc-700 rounded-lg">
                                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                     <thead class="bg-zinc-50 dark:bg-zinc-800 sticky top-0">
                                        <tr>
                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Jira Key</th>
                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Type</th>
                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Issue Summary / Case Title</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                                        <template x-for="(testCase, index) in preview.sample_cases" :key="'case-'+index">
                                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-300 font-mono" x-text="testCase.jira_key"></td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-300" x-text="testCase.issue_type"></td>
                                                <td class="px-4 py-2 text-sm text-zinc-900 dark:text-zinc-100" x-text="testCase.title"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                             <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1" x-show="preview.potential_cases_count > preview.sample_cases.length">Showing <span x-text="preview.sample_cases.length"></span> of <span x-text="preview.potential_cases_count"></span> potential new cases.</p>
                        </div>
                    </div>

                     <div x-show="preview && preview.potential_suites_count === 0 && preview.potential_cases_count === 0" class="mb-6 p-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800/50 text-yellow-700 dark:text-yellow-300 text-sm">
                        <i data-lucide="alert-circle" class="inline-block w-4 h-4 mr-1 align-text-bottom"></i> No matching Jira issues found to import based on your current selections. Please go back and adjust the configuration.
                    </div>

                    <div class="mb-6 p-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800/50" x-show="preview && (preview.potential_suites_count > 0 || preview.potential_cases_count > 0)">
                        <div class="flex">
                           <i data-lucide="alert-triangle" class="h-5 w-5 text-yellow-600 dark:text-yellow-500 mr-3 flex-shrink-0 mt-0.5"></i>
                            <div>
                                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Important: Review Carefully</h3>
                                <div class="mt-1 text-sm text-yellow-700 dark:text-yellow-200 space-y-1">
                                    <p>Clicking "Start Import" will add items to the existing project '<span class="font-semibold">{{ $arxitestProjectName }}</span>'.</p>
                                    <template x-if="selectedTestSuite === 'new'">
                                        <p>New test suites will be created based on Jira Epics found.</p>
                                    </template>
                                    <template x-if="selectedTestSuite !== 'new'">
                                        <p>New test cases will be added to the test suite '<span class="font-semibold" x-text="selectedTestSuiteName"></span>'.</p>
                                    </template>
                                    <p>This action cannot be easily undone once started.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between pt-4">
                    <button type="button" @click="step = 2" :disabled="isLoadingPreview" class="px-4 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 font-medium bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 shadow-sm transition-colors disabled:opacity-50">
                        Back
                    </button>
                     {{-- Disable button if nothing would be imported --}}
                    <button
                        type="button"
                        @click="startImport"
                        :disabled="isImporting || isLoadingPreview || previewError || !preview || (preview.potential_cases_count === 0 && (selectedTestSuite !== 'new' || preview.potential_suites_count === 0))"
                        class="px-6 py-2 rounded-lg text-white font-medium bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-colors flex items-center"
                    >
                        <i data-lucide="download-cloud" class="w-4 h-4 mr-2" x-show="!isImporting"></i>
                        <i data-lucide="loader-2" class="animate-spin w-4 h-4 mr-2" x-show="isImporting"></i>
                        <span x-text="isImporting ? 'Importing...' : 'Start Import'"></span>
                    </button>
                </div>
            </div>

            <div x-show="isImporting" x-cloak>
                 <div class="mb-6 text-center">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mb-4"></div>
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Import in Progress...</h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Please wait while your Jira data is imported into '{{ $arxitestProjectName }}'.</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">This may take several minutes. Please do not navigate away from this page.</p>
                </div>

                 <div class="mb-6" x-show="importProgress">
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Overall Progress</span>
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300" x-text="getOverallProgress() + '%'"></span>
                    </div>
                    <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500 ease-out" :style="`width: ${getOverallProgress()}%`"></div>
                    </div>
                </div>

                <div class="space-y-3 mb-6 text-sm" x-show="importProgress?.details">
                    <div x-show="selectedTestSuite === 'new' && importProgress.details.suites !== undefined">
                        <p class="text-zinc-600 dark:text-zinc-400">
                           <i data-lucide="check-circle" class="w-4 h-4 inline-block mr-1 text-green-500" x-show="importProgress.details.suites?.processed == importProgress.details.suites?.total && importProgress.details.suites?.total > 0"></i>
                           <i data-lucide="loader-2" class="w-4 h-4 inline-block mr-1 animate-spin" x-show="importProgress.details.suites?.processed < importProgress.details.suites?.total"></i>
                           Processing Test Suites (Epics): <span x-text="importProgress.details.suites?.processed || 0"></span> / <span x-text="importProgress.details.suites?.total || 0"></span>
                        </p>
                        <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-1.5 mt-1 overflow-hidden">
                            <div class="bg-indigo-500 h-1.5 rounded-full" :style="`width: ${calculatePercentage(importProgress.details.suites?.processed, importProgress.details.suites?.total)}%`"></div>
                        </div>
                    </div>
                     <div x-show="importProgress.details.cases !== undefined">
                        <p class="text-zinc-600 dark:text-zinc-400">
                            <i data-lucide="check-circle" class="w-4 h-4 inline-block mr-1 text-green-500" x-show="importProgress.details.cases?.processed == importProgress.details.cases?.total && importProgress.details.cases?.total > 0"></i>
                            <i data-lucide="loader-2" class="w-4 h-4 inline-block mr-1 animate-spin" x-show="importProgress.details.cases?.processed < importProgress.details.cases?.total"></i>
                            Processing Test Cases (Issues): <span x-text="importProgress.details.cases?.processed || 0"></span> / <span x-text="importProgress.details.cases?.total || 0"></span>
                        </p>
                         <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-1.5 mt-1 overflow-hidden">
                            <div class="bg-purple-500 h-1.5 rounded-full" :style="`width: ${calculatePercentage(importProgress.details.cases?.processed, importProgress.details.cases?.total)}%`"></div>
                        </div>
                    </div>
                     <div x-show="importProgress.details.scripts !== undefined">
                         <p class="text-zinc-600 dark:text-zinc-400">
                            <i data-lucide="check-circle" class="w-4 h-4 inline-block mr-1 text-green-500" x-show="importProgress.details.scripts?.processed == importProgress.details.scripts?.total && importProgress.details.scripts?.total > 0"></i>
                            <i data-lucide="loader-2" class="w-4 h-4 inline-block mr-1 animate-spin" x-show="importProgress.details.scripts?.processed < importProgress.details.scripts?.total"></i>
                            Generating Test Scripts: <span x-text="importProgress.details.scripts?.processed || 0"></span> / <span x-text="importProgress.details.scripts?.total || 0"></span>
                         </p>
                         <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-1.5 mt-1 overflow-hidden">
                            <div class="bg-teal-500 h-1.5 rounded-full" :style="`width: ${calculatePercentage(importProgress.details.scripts?.processed, importProgress.details.scripts?.total)}%`"></div>
                        </div>
                    </div>
                </div>

                </div>

            <div x-show="importCompleted" x-cloak>
                 <div class="text-center py-8">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900/30 mb-4">
                        <i data-lucide="check-check" class="h-8 w-8 text-green-600 dark:text-green-400"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-2">Import Completed Successfully!</h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">Jira issues have been imported into '<span class="font-medium">{{ $arxitestProjectName }}</span>'.</p>

                    <div class="mb-6">
                         <div class="inline-block bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 text-left">
                            <h3 class="text-md font-medium text-zinc-800 dark:text-zinc-200 mb-3 text-center">Import Summary</h3>
                            <div class="space-y-1 text-sm">
                                <p class="text-zinc-900 dark:text-white flex justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-300">Target Project:</span>
                                    <strong>{{ $arxitestProjectName }}</strong>
                                </p>
                                {{-- Conditionally show suites created --}}
                                <template x-if="selectedTestSuite === 'new'">
                                    <p class="text-zinc-900 dark:text-white flex justify-between">
                                        <span class="text-zinc-600 dark:text-zinc-300">Test Suites Created:</span>
                                        <strong x-text="importStats?.suites_created || 0"></strong>
                                    </p>
                                </template>
                                 <template x-if="selectedTestSuite !== 'new'">
                                    <p class="text-zinc-900 dark:text-white flex justify-between">
                                        <span class="text-zinc-600 dark:text-zinc-300">Target Test Suite:</span>
                                        <strong x-text="selectedTestSuiteName || 'N/A'"></strong>
                                    </p>
                                </template>
                                <p class="text-zinc-900 dark:text-white flex justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-300">Test Cases Created:</span>
                                    <strong x-text="importStats?.cases_created || 0"></strong>
                                </p>
                                <p class="text-zinc-900 dark:text-white flex justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-300">Test Scripts Generated:</span>
                                    <strong x-text="importStats?.scripts_generated || 0"></strong>
                                </p>
                                 <p class="text-zinc-900 dark:text-white flex justify-between" x-show="(importStats?.issues_skipped || 0) > 0">
                                    <span class="text-zinc-600 dark:text-zinc-300">Issues Skipped:</span>
                                    <strong x-text="importStats?.issues_skipped || 0"></strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center space-x-4 pt-4">
                        {{-- Link to the specific Arxitest project --}}
                        <a href="{{ route('dashboard.projects.show', $arxitestProjectId) }}" class="px-4 py-2 rounded-lg text-white font-medium bg-blue-600 hover:bg-blue-700 shadow-sm transition-colors">
                            Go to Project
                        </a>
                         <a href="{{ route('integrations.jira.import.options') }}" class="px-4 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 font-medium bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 shadow-sm transition-colors">
                            Import More
                        </a>
                         <a href="{{ route('dashboard.projects') }}" class="px-4 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 font-medium bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 shadow-sm transition-colors">
                            View All Projects
                        </a>
                    </div>
                </div>
            </div>

            <div x-show="importError" x-cloak>
                <div class="text-center py-8">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                         <i data-lucide="x-octagon" class="h-8 w-8 text-red-600 dark:text-red-400"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white mb-2">Import Failed</h2>
                    <p class="text-sm text-red-600 dark:text-red-400 mb-6">An error occurred while importing issues into '{{ $arxitestProjectName }}':</p>
                    <p class="mb-6 p-3 rounded bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800/50 text-red-700 dark:text-red-300 text-sm text-left font-mono" x-text="importError"></p>

                    <div class="flex justify-center space-x-4 pt-4">
                        <button type="button" @click="resetAndGoToStep(2)" class="px-4 py-2 rounded-lg text-white font-medium bg-blue-600 hover:bg-blue-700 shadow-sm transition-colors">
                             Try Again (Adjust Options)
                        </button>
                        <a href="{{ route('dashboard.projects.show', $arxitestProjectId) }}" class="px-4 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 font-medium bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 shadow-sm transition-colors">
                            Back to Project
                        </a>
                    </div>
                </div>
            </div>

        </div></div></div>@endsection

{{-- Scripts Section --}}
@push('scripts')
<script>
    function jiraImportApp() {
        return {
            // State Properties
            step: 1,
            selectedJiraProject: '',
            selectedJiraProjectName: '',
            selectedTestSuite: '',      // Can be 'new' or an ID
            selectedTestSuiteName: '',  // Name of the selected existing suite
            importEpics: true,          // Relevant only if selectedTestSuite is 'new'
            importStories: true,
            generateTestScripts: false,
            showAdvancedFilters: false,
            jqlFilter: '',
            maxIssues: 1000,            // Default max

            // Loading/Error States (Same as previous)
            isLoading: false,
            error: null,
            isLoadingPreview: false,
            previewError: null,
            isImporting: false,
            importError: null,

            // Data Properties (Same as previous, except no newProjectName)
            preview: null,
            importJobId: null,
            importProgress: null,
            importStats: null,
            importCompleted: false,
            // createdProjectId is not needed here, use window.arxitestProjectId passed from PHP

            // Timers/Intervals (Same as previous)
            progressCheckInterval: null,
            progressCheckDelay: 3000,
            maxProgressChecks: 100,
            progressCheckCount: 0,

            // Lifecycle Hooks & Watchers
            init() {
                console.log('Jira Import (Existing Project) App Initialized');
                this.$watch('selectedJiraProject', (value) => {
                    this.handleJiraProjectChange(value);
                });
                 this.$watch('selectedTestSuite', (value) => {
                    this.handleTestSuiteChange(value);
                 });

                // Ensure Lucide icons are rendered (Same as previous)
                 this.$nextTick(() => {
                     if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                     } else {
                         console.warn('Lucide icons library not found.');
                     }
                });
                this.$watch('step', () => this.$nextTick(() => lucide.createIcons()));
                this.$watch('isImporting', () => this.$nextTick(() => lucide.createIcons()));
                this.$watch('importCompleted', () => this.$nextTick(() => lucide.createIcons()));
                this.$watch('importError', () => this.$nextTick(() => lucide.createIcons()));
                this.$watch('showAdvancedFilters', () => this.$nextTick(() => lucide.createIcons()));
                this.$watch('isLoadingPreview', () => this.$nextTick(() => lucide.createIcons()));
                this.$watch('previewError', () => this.$nextTick(() => lucide.createIcons()));
                this.$watch('selectedTestSuite', () => this.$nextTick(() => lucide.createIcons())); // For conditional previews/options
            },

            // Methods for Step Transitions & Input Handling
            handleJiraProjectChange(value) {
                if (value) {
                    const selectEl = document.getElementById('jira_project');
                    if (selectEl && selectEl.options[selectEl.selectedIndex]) {
                         this.selectedJiraProjectName = selectEl.options[selectEl.selectedIndex].getAttribute('data-name');
                    } else {
                        this.selectedJiraProjectName = '';
                    }
                } else {
                    this.selectedJiraProjectName = '';
                }
            },

            handleTestSuiteChange(value) {
                if (value && value !== 'new') {
                    const selectEl = document.getElementById('test_suite');
                     if (selectEl && selectEl.options[selectEl.selectedIndex]) {
                         this.selectedTestSuiteName = selectEl.options[selectEl.selectedIndex].getAttribute('data-name');
                     } else {
                         this.selectedTestSuiteName = ''; // Reset if not found
                     }
                } else {
                    this.selectedTestSuiteName = ''; // Reset if 'new' or empty
                }
                 // Reset importEpics if not creating new suites, otherwise default to true
                if (value !== 'new') {
                    this.importEpics = false;
                } else {
                     this.importEpics = true; // Default back to true when 'new' is selected
                }
            },

            goToNextStep() {
                this.error = null;
                this.isLoading = false;
                if (this.step === 1 && this.selectedJiraProject && this.selectedTestSuite) {
                    this.step = 2;
                    // No metadata load needed here unless fetching specific fields/types
                }
            },

            // Method to fetch preview data (Adapted)
            goToPreview() {
                // Check if at least one import action is selected
                if (!this.importStories && (this.selectedTestSuite !== 'new' || !this.importEpics)) {
                    this.previewError = "Please select at least one mapping (Stories/Tasks/Bugs, or Epics if creating new suites).";
                    return;
                }

                this.isLoadingPreview = true;
                this.previewError = null;
                this.preview = null;

                const payload = this.buildPayload();
                payload.sample_size = 30; // Request a sample for preview

                console.log('Fetching preview (existing project) with payload:', payload);

                // Use the same preview route, backend logic differentiates based on payload
                fetch('{{ route('integrations.jira.preview-import') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw new Error(err.message || `HTTP error! Status: ${response.status}`) });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Preview data received:', data);
                    this.isLoadingPreview = false;
                    if (data.success && data.preview) {
                        this.preview = data.preview;
                        this.step = 3;
                    } else {
                        this.previewError = data.message || 'Failed to generate import preview. The server returned an unexpected response.';
                    }
                })
                .catch(error => {
                    this.isLoadingPreview = false;
                    this.previewError = error.message || 'An error occurred while generating the preview. Check console for details.';
                    console.error('Preview Fetch Error:', error);
                });
            },

            // Method to start the actual import (Adapted)
            startImport() {
                if (this.isImporting) return;

                // Final check: ensure something will actually be imported
                 if (!this.preview || (this.preview.potential_cases_count === 0 && (this.selectedTestSuite !== 'new' || this.preview.potential_suites_count === 0))) {
                     console.warn('Start import blocked: Preview indicates nothing to import.');
                     // Optionally show a user message here
                     return;
                 }

                this.isImporting = true;
                this.importError = null;
                this.importCompleted = false;
                this.importProgress = null;
                this.importJobId = null;
                this.importStats = null;
                this.clearProgressCheck();

                const payload = this.buildPayload();
                delete payload.sample_size; // Remove sample size for actual import

                console.log('Starting import (existing project) with payload:', payload);

                // Use the same start route, backend logic differentiates based on payload
                fetch('{{ route('integrations.jira.start-import') }}', {
                    method: 'POST',
                     headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || `Import initiation failed. Status: ${response.status}`);
                        }).catch(() => {
                            throw new Error(`Import initiation failed. Status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Import started response:', data);
                    if (data.success && data.job_id) {
                        this.importJobId = data.job_id;
                        this.progressCheckCount = 0;
                        this.checkImportProgress(); // Initial check
                        this.progressCheckInterval = setInterval(() => this.checkImportProgress(), this.progressCheckDelay);
                    } else {
                        this.isImporting = false;
                        this.importError = data.message || 'Failed to start the import process. No job ID received.';
                    }
                })
                .catch(error => {
                    this.isImporting = false;
                    this.importError = error.message || 'An unexpected error occurred while starting the import.';
                    console.error('Start Import Error:', error);
                });
            },

            // Method to poll for import progress (Identical logic to previous example)
            checkImportProgress() {
                 if (!this.importJobId || !this.isImporting) {
                    this.clearProgressCheck();
                    return;
                }

                this.progressCheckCount++;
                if (this.progressCheckCount > this.maxProgressChecks) {
                     console.warn('Max progress checks reached. Stopping polling.');
                     this.importError = 'The import seems to be taking longer than expected. Please check the project later or contact support if the issue persists.';
                     this.isImporting = false;
                     this.clearProgressCheck();
                     return;
                }

                console.log(`Checking progress for job ${this.importJobId} (Check #${this.progressCheckCount})`);

                fetch(`{{ url('/api/integration/jira/import-status') }}/${this.importJobId}`, { // Use the same status route
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                     if (!response.ok) {
                         console.error(`Progress check failed: Status ${response.status}`);
                         return null;
                     }
                     return response.json();
                })
                .then(data => {
                    if (!data) return;

                    console.log('Progress data:', data);
                    if (data.status === 'processing' || data.status === 'pending') {
                        this.importProgress = data.progress || { overall: 0 };
                    } else if (data.status === 'completed') {
                        this.isImporting = false;
                        this.importCompleted = true;
                        this.importStats = data.stats || {};
                        // No createdProjectId needed here, project already exists
                        this.importProgress = data.progress || { overall: 100 };
                        this.clearProgressCheck();
                        console.log('Import Completed Successfully!');
                    } else if (data.status === 'failed') {
                        this.isImporting = false;
                        this.importError = data.error_message || 'The import process failed.';
                        this.clearProgressCheck();
                        console.error('Import Failed:', this.importError);
                    } else {
                         console.warn('Unknown import status received:', data.status);
                    }
                })
                .catch(error => {
                    console.error('Progress Check Network Error:', error);
                });
            },

            // Utility Methods
            buildPayload() {
                const payload = {
                    // Target Arxitest Project
                    arxitest_project_id: window.arxitestProjectId, // Get ID passed from PHP
                    target_test_suite_id: this.selectedTestSuite === 'new' ? null : this.selectedTestSuite,

                    // Source Jira Project
                    jira_project_key: this.selectedJiraProject,

                    // Mappings (conditional)
                    mappings: {},

                    // Options
                    options: {
                        generate_scripts: this.generateTestScripts,
                        jql_filter: this.jqlFilter || null,
                        max_issues: this.maxIssues > 0 ? this.maxIssues : null
                    }
                };

                 // Only include epic mapping if creating new suites
                 if (this.selectedTestSuite === 'new' && this.importEpics) {
                     payload.mappings.epic_to_suite = true;
                 }
                 // Always include story/task/bug mapping if selected
                 if (this.importStories) {
                     payload.mappings.story_task_bug_to_case = true;
                 }

                return payload;
            },

            // Other utility methods (identical to previous example)
            clearProgressCheck() {
                if (this.progressCheckInterval) {
                    clearInterval(this.progressCheckInterval);
                    this.progressCheckInterval = null;
                    console.log('Progress polling stopped.');
                }
            },
            getOverallProgress() {
                return this.importProgress?.overall ?? 0;
            },
            calculatePercentage(value, total) {
                if (!total || total <= 0 || !value || value < 0) return 0;
                if (value >= total) return 100;
                return Math.round((value / total) * 100);
            },
            resetAndGoToStep(targetStep) {
                this.isImporting = false;
                this.importError = null;
                this.importCompleted = false;
                this.importProgress = null;
                this.importJobId = null;
                this.importStats = null;
                this.clearProgressCheck();
                if (targetStep <= 2) {
                    this.isLoadingPreview = false;
                    this.previewError = null;
                    this.preview = null;
                }
                 if (targetStep === 1) {
                    this.error = null;
                 }
                this.step = targetStep;
            }
        }
    }
</script>
@endpush
