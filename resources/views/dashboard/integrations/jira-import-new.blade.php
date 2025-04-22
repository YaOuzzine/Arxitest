{{-- resources/views/integrations/jira/import-new-project.blade.php --}}
@extends('layouts.dashboard') {{-- Assuming you have a base dashboard layout --}}
@section('title', 'Import to New Project from Jira')

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
    <span class="text-zinc-700 dark:text-zinc-300">New Project</span>
</li>
@endsection

{{-- Main Content Section --}}
@section('content')
<div class="page-transition" x-data="jiraImportApp()">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white tracking-tight mb-2">Import to New Project from Jira</h1>
        <p class="text-zinc-600 dark:text-zinc-400">Create a new Arxitest project by importing issues from a selected Jira project.</p>
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
                    <p class="text-sm font-medium" :class="step == 1 ? 'text-zinc-900 dark:text-white' : 'text-zinc-600 dark:text-zinc-400'">Select Jira Project</p>
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
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Select Jira Project</h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Choose the Jira project you want to import issues from to create a new Arxitest project.</p>
                </div>

                <div class="mb-6">
                    <label for="jira_project" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Jira Project</label>
                    <select id="jira_project" x-model="selectedJiraProject" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select a project...</option>
                        {{-- Loop through available Jira projects passed from the controller --}}
                        @forelse($jiraProjects as $project)
                            <option value="{{ $project['key'] }}" data-name="{{ $project['name'] }}">{{ $project['name'] }} ({{ $project['key'] }})</option>
                        @empty
                            <option value="" disabled>No Jira projects found or integration not configured.</option>
                        @endforelse
                    </select>
                    @error('jira_project_key') {{-- Example validation error display --}}
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="project_name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">New Arxitest Project Name</label>
                    <input type="text" id="project_name" x-model="newProjectName" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Enter project name">
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">This will be the name of your new project in Arxitest. It defaults to the Jira project name if left empty.</p>
                     @error('project_name') {{-- Example validation error display --}}
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end pt-4">
                    <button type="button" @click="goToNextStep" :disabled="!selectedJiraProject" class="px-4 py-2 rounded-lg text-white font-medium bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-colors">
                        Next Step
                    </button>
                </div>
            </div>

            <div x-show="step == 2" x-cloak>
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Configure Import Options</h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Customize which issue types to import and apply filters.</p>
                </div>

                <div x-show="isLoading" class="flex justify-center items-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                </div>

                <div x-show="!isLoading && !error">
                    <div class="mb-6">
                        <h3 class="text-md font-medium text-zinc-800 dark:text-zinc-200 mb-2">Issue Types to Import</h3>
                        <div class="space-y-2">
                            <label class="flex items-center p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
                                <input type="checkbox" x-model="importEpics" class="rounded text-blue-600 focus:ring-blue-500 border-zinc-300 dark:border-zinc-500 bg-white dark:bg-zinc-700 shadow-sm">
                                <span class="ml-3 text-sm text-zinc-700 dark:text-zinc-300">Import Jira <strong class="font-semibold">Epics</strong> as Arxitest <strong class="font-semibold">Test Suites</strong></span>
                            </label>
                            <label class="flex items-center p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
                                <input type="checkbox" x-model="importStories" class="rounded text-blue-600 focus:ring-blue-500 border-zinc-300 dark:border-zinc-500 bg-white dark:bg-zinc-700 shadow-sm">
                                <span class="ml-3 text-sm text-zinc-700 dark:text-zinc-300">Import Jira <strong class="font-semibold">Stories, Tasks, Bugs</strong> as Arxitest <strong class="font-semibold">Test Cases</strong></span>
                            </label>
                        </div>
                         <p x-show="!importEpics && !importStories" class="mt-2 text-xs text-red-600 dark:text-red-400">Please select at least one issue type mapping to import.</p>
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
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Enter a JQL query to filter issues within the selected project. This will be combined with the project selection. <a href="https://support.atlassian.com/jira-software-cloud/docs/advanced-search-reference-jql-fields/" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">JQL Reference</a></p>
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
                    <button type="button" @click="goToPreview" :disabled="isLoading || (!importEpics && !importStories)" class="px-4 py-2 rounded-lg text-white font-medium bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-colors">
                        Preview Import
                    </button>
                </div>
            </div>

            <div x-show="step == 3 && !isImporting && !importCompleted && !importError" x-cloak>
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Review Import</h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Verify the details below. Issues shown are a sample based on your configuration.</p>
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
                            <div>
                                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-2">Project Details</h3>
                                <p class="text-sm text-zinc-900 dark:text-white mb-1">
                                    <span class="font-semibold text-zinc-600 dark:text-zinc-300">Source Jira Project:</span>
                                    <span x-text="selectedJiraProjectName || 'N/A'"></span> (<span x-text="selectedJiraProject || 'N/A'"></span>)
                                </p>
                                <p class="text-sm text-zinc-900 dark:text-white">
                                    <span class="font-semibold text-zinc-600 dark:text-zinc-300">New Arxitest Project:</span>
                                    <span x-text="newProjectName || selectedJiraProjectName || 'N/A'"></span>
                                </p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-2">Estimated Import</h3>
                                <div class="space-y-1">
                                    <p class="text-sm text-zinc-900 dark:text-white flex justify-between">
                                        <span>Total Matching Issues Found:</span>
                                        <span class="font-semibold" x-text="preview.total_matching_issues != null ? preview.total_matching_issues : 'Calculating...'"></span>
                                    </p>
                                     <p class="text-sm text-zinc-900 dark:text-white flex justify-between" x-show="importEpics">
                                        <span>Test Suites (from Epics):</span>
                                        <span class="font-semibold" x-text="preview.potential_suites_count != null ? preview.potential_suites_count : 'Calculating...'"></span>
                                    </p>
                                    <p class="text-sm text-zinc-900 dark:text-white flex justify-between" x-show="importStories">
                                        <span>Test Cases (from Issues):</span>
                                        <span class="font-semibold" x-text="preview.potential_cases_count != null ? preview.potential_cases_count : 'Calculating...'"></span>
                                    </p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 text-right mt-1" x-show="maxIssues > 0 && preview.total_matching_issues > maxIssues">(Limited to <span x-text="maxIssues"></span> issues)</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                             <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-2">Configuration</h3>
                             <ul class="list-disc list-inside text-sm text-zinc-700 dark:text-zinc-300 space-y-1">
                                <li x-show="importEpics">Import Epics as Test Suites</li>
                                <li x-show="importStories">Import Stories, Tasks, Bugs as Test Cases</li>
                                <li x-show="generateTestScripts">Generate Test Scripts from Acceptance Criteria</li>
                                <li x-show="jqlFilter">Custom JQL Filter: <code class="text-xs bg-zinc-200 dark:bg-zinc-700 px-1 py-0.5 rounded" x-text="jqlFilter"></code></li>
                                <li x-show="maxIssues > 0 && maxIssues < 1000">Maximum Issues: <span x-text="maxIssues"></span></li>
                             </ul>
                        </div>
                    </div>

                    {{-- Preview Tables --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div x-show="importEpics && preview.sample_suites && preview.sample_suites.length > 0">
                            <h3 class="text-md font-medium text-zinc-800 dark:text-zinc-200 mb-2">Sample Test Suites (from Epics)</h3>
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
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1" x-show="preview.potential_suites_count > preview.sample_suites.length">Showing <span x-text="preview.sample_suites.length"></span> of <span x-text="preview.potential_suites_count"></span> potential suites.</p>
                        </div>

                        <div x-show="importStories && preview.sample_cases && preview.sample_cases.length > 0">
                            <h3 class="text-md font-medium text-zinc-800 dark:text-zinc-200 mb-2">Sample Test Cases (from Issues)</h3>
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
                             <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1" x-show="preview.potential_cases_count > preview.sample_cases.length">Showing <span x-text="preview.sample_cases.length"></span> of <span x-text="preview.potential_cases_count"></span> potential cases.</p>
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
                                    <p>Clicking "Start Import" will create a <strong class="font-semibold">new project</strong> named '<span x-text="newProjectName || selectedJiraProjectName"></span>'.</p>
                                    <p>The import process will fetch data from Jira based on your configuration. This action cannot be easily undone once started.</p>
                                    <p>Large imports may take several minutes.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between pt-4">
                    <button type="button" @click="step = 2" :disabled="isLoadingPreview" class="px-4 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 font-medium bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 shadow-sm transition-colors disabled:opacity-50">
                        Back
                    </button>
                    <button
                        type="button"
                        @click="startImport"
                        :disabled="isLoadingPreview || previewError || !preview || (preview.potential_suites_count === 0 && preview.potential_cases_count === 0)"
                        class="px-6 py-2 rounded-lg text-white font-medium bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-colors flex items-center"
                    >
                        <i data-lucide="download-cloud" class="w-4 h-4 mr-2"></i>
                        <span>Start Import</span>
                    </button>
                </div>
            </div>

            <div x-show="isImporting" x-cloak>
                 <div class="mb-6 text-center">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mb-4"></div>
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">Import in Progress...</h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Please wait while your Jira data is imported into the new Arxitest project.</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">This may take several minutes depending on the number of issues. Please do not navigate away from this page.</p>
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
                    <div x-show="importProgress.details.suites !== undefined">
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
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">Your new Arxitest project '<span x-text="newProjectName || selectedJiraProjectName"></span>' has been created.</p>

                    <div class="mb-6">
                        <div class="inline-block bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 text-left">
                            <h3 class="text-md font-medium text-zinc-800 dark:text-zinc-200 mb-3 text-center">Import Summary</h3>
                            <div class="space-y-1 text-sm">
                                <p class="text-zinc-900 dark:text-white flex justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-300">New Project Name:</span>
                                    <strong x-text="newProjectName || selectedJiraProjectName"></strong>
                                </p>
                                <p class="text-zinc-900 dark:text-white flex justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-300">Test Suites Created:</span>
                                    <strong x-text="importStats?.suites_created || 0"></strong>
                                </p>
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
                        {{-- The URL generation assumes your project route uses slugs or IDs --}}
                        <a x-show="createdProjectId" :href="'{{ url('/dashboard/projects') }}/' + createdProjectId" class="px-4 py-2 rounded-lg text-white font-medium bg-blue-600 hover:bg-blue-700 shadow-sm transition-colors">
                            Go to New Project
                        </a>
                        <a href="{{ route('dashboard.projects') }}" class="px-4 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 font-medium bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 shadow-sm transition-colors">
                            View All Projects
                        </a>
                         <a href="{{ route('integrations.jira.import.options') }}" class="px-4 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 font-medium bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 shadow-sm transition-colors">
                            Import Another
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
                    <p class="text-sm text-red-600 dark:text-red-400 mb-6">An error occurred during the import process:</p>
                    <p class="mb-6 p-3 rounded bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800/50 text-red-700 dark:text-red-300 text-sm text-left font-mono" x-text="importError"></p>

                    <div class="flex justify-center space-x-4 pt-4">
                        <button type="button" @click="resetAndGoToStep(2)" class="px-4 py-2 rounded-lg text-white font-medium bg-blue-600 hover:bg-blue-700 shadow-sm transition-colors">
                            Try Again (Adjust Options)
                        </button>
                         <a href="{{ route('dashboard.integrations.index') }}" class="px-4 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 font-medium bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 shadow-sm transition-colors">
                            Back to Integrations
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
            newProjectName: '',
            importEpics: true,
            importStories: true,
            generateTestScripts: false,
            showAdvancedFilters: false,
            jqlFilter: '',
            maxIssues: 1000, // Default max

            // Loading/Error States
            isLoading: false,       // General loading (e.g., fetching metadata)
            error: null,            // General error message (e.g., metadata fetch failed)
            isLoadingPreview: false,// Loading state for preview step
            previewError: null,     // Error message specific to preview generation
            isImporting: false,     // True while import job is running
            importError: null,      // Error message specific to the import job failure

            // Data Properties
            preview: null,          // Holds data returned from preview endpoint
            importJobId: null,      // ID for the background import job
            importProgress: null,   // Holds progress data { overall: %, details: {...} }
            importStats: null,      // Holds final stats after completion { suites_created: N, ... }
            importCompleted: false, // True when import finishes successfully
            createdProjectId: null, // ID of the newly created Arxitest project

            // Timers/Intervals
            progressCheckInterval: null,
            progressCheckDelay: 3000, // Check progress every 3 seconds
            maxProgressChecks: 100, // Stop checking after ~5 minutes if no completion
            progressCheckCount: 0,

            // Lifecycle Hooks & Watchers
            init() {
                console.log('Jira Import App Initialized');
                this.$watch('selectedJiraProject', (value) => {
                    this.handleJiraProjectChange(value);
                });
                // Ensure Lucide icons are rendered after Alpine initializes
                this.$nextTick(() => {
                     if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                     } else {
                         console.warn('Lucide icons library not found.');
                     }
                });

                // Add listener to update icons when steps change etc.
                 this.$watch('step', () => this.$nextTick(() => lucide.createIcons()));
                 this.$watch('isImporting', () => this.$nextTick(() => lucide.createIcons()));
                 this.$watch('importCompleted', () => this.$nextTick(() => lucide.createIcons()));
                 this.$watch('importError', () => this.$nextTick(() => lucide.createIcons()));
                 this.$watch('showAdvancedFilters', () => this.$nextTick(() => lucide.createIcons()));
                 this.$watch('isLoadingPreview', () => this.$nextTick(() => lucide.createIcons()));
                 this.$watch('previewError', () => this.$nextTick(() => lucide.createIcons()));
            },

            // Methods for Step Transitions
            handleJiraProjectChange(value) {
                if (value) {
                    const selectEl = document.getElementById('jira_project');
                    if (selectEl && selectEl.options[selectEl.selectedIndex]) {
                         const option = selectEl.options[selectEl.selectedIndex];
                         this.selectedJiraProjectName = option.getAttribute('data-name');

                         // Auto-fill project name if empty
                         if (!this.newProjectName.trim()) {
                             this.newProjectName = this.selectedJiraProjectName;
                         }
                    } else {
                        this.selectedJiraProjectName = ''; // Reset if project not found
                    }
                } else {
                    this.selectedJiraProjectName = '';
                    // Maybe clear newProjectName too, or leave as is? User preference.
                    // this.newProjectName = '';
                }
            },

            goToNextStep() {
                // Reset errors/states relevant to the next step
                this.error = null;
                this.isLoading = false; // Reset loading for metadata

                if (this.step === 1 && this.selectedJiraProject) {
                    // Validate project name (basic client-side)
                    if (!this.newProjectName.trim()) {
                        this.newProjectName = this.selectedJiraProjectName; // Ensure it's set if user cleared it
                        if (!this.newProjectName) { // Still empty? Maybe Jira name was bad?
                            this.error = "Please provide a name for the new Arxitest project.";
                            document.getElementById('project_name')?.focus();
                            return;
                        }
                    }
                    this.step = 2;
                    // Optionally load metadata if needed for step 2 configuration
                    // this.loadJiraProjectMetadata();
                }
            },

            // Method to fetch project metadata (Example - Adapt if needed)
            // loadJiraProjectMetadata() {
            //     if (!this.selectedJiraProject) return;
            //     this.isLoading = true;
            //     this.error = null;
            //     console.log('Loading metadata for:', this.selectedJiraProject);

            //     fetch('{{ route('integrations.jira.project-metadata') }}', { /* ... fetch options ... */ })
            //         .then(response => { /* ... handle response ... */ })
            //         .catch(error => {
            //             this.isLoading = false;
            //             this.error = 'An error occurred while loading Jira project details. Please try again.';
            //             console.error('Metadata Error:', error);
            //         });
            // },

            // Method to fetch preview data
            goToPreview() {
                if (!this.importEpics && !this.importStories) {
                    this.previewError = "Please select at least one mapping (Epics or Stories/Tasks/Bugs).";
                    return;
                }

                this.isLoadingPreview = true;
                this.previewError = null;
                this.preview = null; // Clear previous preview

                const payload = this.buildPayload();
                payload.sample_size = 30; // Request a sample for preview

                console.log('Fetching preview with payload:', payload);

                fetch('{{ route('integrations.jira.preview-import') }}', { // Ensure this route exists and accepts POST
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
                        // Use specific error from backend if available, otherwise generic
                        this.previewError = data.message || 'Failed to generate import preview. The server returned an unexpected response.';
                    }
                })
                .catch(error => {
                    this.isLoadingPreview = false;
                    this.previewError = error.message || 'An error occurred while generating the preview. Check console for details.';
                    console.error('Preview Fetch Error:', error);
                });
            },

            // Method to start the actual import
            startImport() {
                if (this.isImporting) return; // Prevent double-clicks

                this.isImporting = true;
                this.importError = null;
                this.importCompleted = false;
                this.importProgress = null; // Reset progress
                this.importJobId = null;    // Reset job ID
                this.importStats = null;
                this.createdProjectId = null;
                this.clearProgressCheck();  // Clear any previous interval

                const payload = this.buildPayload();
                // Remove sample_size for actual import if it was added for preview
                delete payload.sample_size;

                console.log('Starting import with payload:', payload);

                fetch('{{ route('integrations.jira.start-import') }}', { // Ensure this route exists and accepts POST
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
                         // Try to parse error message from JSON response body
                        return response.json().then(err => {
                            throw new Error(err.message || `Import initiation failed. Status: ${response.status}`);
                        }).catch(() => {
                             // Fallback if response body is not JSON or parsing fails
                            throw new Error(`Import initiation failed. Status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Import started response:', data);
                    if (data.success && data.job_id) {
                        this.importJobId = data.job_id;
                        // Start polling for progress immediately
                        this.progressCheckCount = 0; // Reset counter
                        this.checkImportProgress();
                        // Set up interval for subsequent checks
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

            // Method to poll for import progress
            checkImportProgress() {
                if (!this.importJobId || !this.isImporting) {
                    this.clearProgressCheck();
                    return;
                }

                this.progressCheckCount++;
                if (this.progressCheckCount > this.maxProgressChecks) {
                     console.warn('Max progress checks reached. Stopping polling.');
                     this.importError = 'The import seems to be taking longer than expected. Please check the project list later or contact support if the issue persists.';
                     this.isImporting = false;
                     this.clearProgressCheck();
                     return;
                }

                console.log(`Checking progress for job ${this.importJobId} (Check #${this.progressCheckCount})`);

                fetch(`{{ url('/api/integration/jira/import-status') }}/${this.importJobId}`, { // Example API route - Adjust URL
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                     if (!response.ok) {
                         // Don't stop importing on a single failed status check, but log it
                         console.error(`Progress check failed: Status ${response.status}`);
                         // Potentially implement retries or stop after several failures
                         return null; // Indicate failure to process this check
                     }
                     return response.json();
                })
                .then(data => {
                    if (!data) return; // Skip update if fetch failed

                    console.log('Progress data:', data);
                    if (data.status === 'processing' || data.status === 'pending') {
                        this.importProgress = data.progress || { overall: 0 }; // Update progress state
                    } else if (data.status === 'completed') {
                        this.isImporting = false;
                        this.importCompleted = true;
                        this.importStats = data.stats || {};
                        this.createdProjectId = data.project_id || null;
                        this.importProgress = data.progress || { overall: 100 }; // Ensure progress shows 100%
                        this.clearProgressCheck();
                        console.log('Import Completed Successfully!');
                    } else if (data.status === 'failed') {
                        this.isImporting = false;
                        this.importError = data.error_message || 'The import process failed.';
                        this.clearProgressCheck();
                        console.error('Import Failed:', this.importError);
                    } else {
                         console.warn('Unknown import status received:', data.status);
                         // Keep polling unless it's clearly finished or failed
                    }
                })
                .catch(error => {
                    // Don't stop importing on a network error for status check, maybe log it
                    console.error('Progress Check Network Error:', error);
                });
            },

            // Utility Methods
            buildPayload() {
                const payload = {
                    jira_project_key: this.selectedJiraProject,
                    new_project_name: this.newProjectName || this.selectedJiraProjectName, // Ensure name is set
                    mappings: {},
                    options: {
                        generate_scripts: this.generateTestScripts,
                        jql_filter: this.jqlFilter || null, // Send null if empty
                        max_issues: this.maxIssues > 0 ? this.maxIssues : null // Send null if 0 or less
                    }
                };

                // Map issue types based on checkboxes
                 if (this.importEpics) payload.mappings.epic_to_suite = true;
                 if (this.importStories) payload.mappings.story_task_bug_to_case = true;

                return payload;
            },

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
                // Reset states related to import/error before going back
                this.isImporting = false;
                this.importError = null;
                this.importCompleted = false;
                this.importProgress = null;
                this.importJobId = null;
                this.importStats = null;
                this.createdProjectId = null;
                this.clearProgressCheck();

                // Reset preview states if going back to step 1 or 2
                if (targetStep <= 2) {
                    this.isLoadingPreview = false;
                    this.previewError = null;
                    this.preview = null;
                }

                // Reset general error if going back to step 1
                 if (targetStep === 1) {
                    this.error = null;
                 }

                this.step = targetStep;
            }
        }
    }
</script>
@endpush
