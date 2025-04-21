{{-- resources/views/dashboard/integrations/jira-import.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Import from Jira')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.integrations.index') }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">Integrations</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Import Jira Project</span>
    </li>
@endsection

@section('content')
<div class="h-full" x-data="jiraImport()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Import from Jira</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Configure and customize how to import Jira issues into your Arxitest project
            </p>
        </div>
        <div>
            <a href="{{ route('dashboard.integrations.index') }}" class="btn-secondary">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Integrations
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Multi-step import wizard -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <!-- Progress Steps -->
        <div class="border-b border-zinc-200 dark:border-zinc-700">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex flex-1">
                        <button @click="step = 1" :class="{'font-semibold text-indigo-600 dark:text-indigo-400': step >= 1, 'text-zinc-500 dark:text-zinc-400': step < 1}"
                            class="flex items-center">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full mr-2 border-2"
                                :class="{'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-500 dark:border-indigo-400': step >= 1, 'border-zinc-300 dark:border-zinc-600': step < 1}">
                                1
                            </span>
                            <span>Select Project</span>
                        </button>
                        <div class="flex-1 border-t-2 border-dashed mx-2 self-center" :class="{'border-indigo-300 dark:border-indigo-600': step > 1, 'border-zinc-300 dark:border-zinc-600': step <= 1}"></div>
                    </div>

                    <div class="flex flex-1">
                        <button @click="step = 2" :disabled="step < 2" :class="{'font-semibold text-indigo-600 dark:text-indigo-400': step >= 2, 'text-zinc-500 dark:text-zinc-400': step < 2}"
                            class="flex items-center">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full mr-2 border-2"
                                :class="{'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-500 dark:border-indigo-400': step >= 2, 'border-zinc-300 dark:border-zinc-600': step < 2}">
                                2
                            </span>
                            <span>Filter Issues</span>
                        </button>
                        <div class="flex-1 border-t-2 border-dashed mx-2 self-center" :class="{'border-indigo-300 dark:border-indigo-600': step > 2, 'border-zinc-300 dark:border-zinc-600': step <= 2}"></div>
                    </div>

                    <div class="flex flex-1">
                        <button @click="step = 3" :disabled="step < 3" :class="{'font-semibold text-indigo-600 dark:text-indigo-400': step >= 3, 'text-zinc-500 dark:text-zinc-400': step < 3}"
                            class="flex items-center">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full mr-2 border-2"
                                :class="{'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-500 dark:border-indigo-400': step >= 3, 'border-zinc-300 dark:border-zinc-600': step < 3}">
                                3
                            </span>
                            <span>Configure Mapping</span>
                        </button>
                        <div class="flex-1 border-t-2 border-dashed mx-2 self-center" :class="{'border-indigo-300 dark:border-indigo-600': step > 3, 'border-zinc-300 dark:border-zinc-600': step <= 3}"></div>
                    </div>

                    <div class="flex flex-1">
                        <button @click="step = 4" :disabled="step < 4" :class="{'font-semibold text-indigo-600 dark:text-indigo-400': step >= 4, 'text-zinc-500 dark:text-zinc-400': step < 4}"
                            class="flex items-center">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full mr-2 border-2"
                                :class="{'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-500 dark:border-indigo-400': step >= 4, 'border-zinc-300 dark:border-zinc-600': step < 4}">
                                4
                            </span>
                            <span>Preview & Import</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 1: Select Jira Project -->
        <div x-show="step === 1" class="p-6">
            <h3 class="text-lg font-semibold mb-4 text-zinc-900 dark:text-white">Select Jira Project</h3>

            <div class="mb-6">
                <label for="project-search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Search Projects</label>
                <input id="project-search" type="text" x-model="projectSearch" placeholder="Type to search projects..."
                    class="w-full px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
            </div>

            <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                <template x-for="project in filteredProjects" :key="project.id">
                    <div @click="selectProject(project)"
                        class="p-4 border rounded-lg cursor-pointer transition-all duration-200 flex items-start gap-3"
                        :class="{'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30': selectedProject?.id === project.id, 'border-zinc-200 dark:border-zinc-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:bg-zinc-50 dark:hover:bg-zinc-700/50': selectedProject?.id !== project.id}">
                        <div class="shrink-0">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-md flex items-center justify-center">
                                <span class="text-blue-700 dark:text-blue-300 font-mono" x-text="project.key?.substring(0, 2) || 'JP'"></span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-medium text-zinc-900 dark:text-white" x-text="project.name"></h4>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Key: <span class="font-mono" x-text="project.key"></span></p>
                        </div>
                    </div>
                </template>
            </div>

            <template x-if="filteredProjects.length === 0">
                <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                    <i data-lucide="search-x" class="w-12 h-12 mx-auto mb-3 text-zinc-400 dark:text-zinc-500"></i>
                    <p>No projects match your search. Try different keywords.</p>
                </div>
            </template>

            <div class="mt-8 flex justify-end">
                <button @click="goToStep(2)" :disabled="!selectedProject"
                    class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-indigo-700 transition-colors">
                    Continue <i data-lucide="arrow-right" class="w-4 h-4 ml-2 inline-block"></i>
                </button>
            </div>
        </div>

        <!-- Step 2: Filter Issues -->
        <div x-show="step === 2" class="p-6" x-cloak>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Filter Jira Issues</h3>
                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                    Selected Project: <span class="font-medium text-zinc-900 dark:text-white" x-text="selectedProject?.name"></span>
                </div>
            </div>

            <div class="mb-4 p-4 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg border border-zinc-200 dark:border-zinc-600" x-show="isLoadingMetadata">
                <div class="flex items-center justify-center">
                    <i data-lucide="loader-2" class="w-5 h-5 mr-3 animate-spin text-indigo-500"></i>
                    <span>Loading issue types, statuses and labels from Jira...</span>
                </div>
            </div>

            <div x-show="!isLoadingMetadata">
                <!-- Issue Types Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Issue Types</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="type in projectMetadata.issueTypes" :key="type.id">
                            <button @click="toggleIssueType(type.name)"
                                class="px-3 py-1.5 rounded-full text-sm border transition-colors"
                                :class="selectedIssueTypes.includes(type.name) ?
                                    'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300' :
                                    'bg-white dark:bg-zinc-700 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300'">
                                <span x-text="type.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Statuses Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Status (Optional)</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="(status, index) in projectMetadata.statuses" :key="index">
                            <button @click="toggleStatus(status)"
                                class="px-3 py-1.5 rounded-full text-sm border transition-colors"
                                :class="selectedStatuses.includes(status) ?
                                    'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300' :
                                    'bg-white dark:bg-zinc-700 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300'">
                                <span x-text="status"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Labels Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Labels (Optional)</label>
                    <div x-show="projectMetadata.labels.length > 0" class="flex flex-wrap gap-2">
                        <template x-for="(label, index) in projectMetadata.labels" :key="index">
                            <button @click="toggleLabel(label)"
                                class="px-3 py-1.5 rounded-full text-sm border transition-colors"
                                :class="selectedLabels.includes(label) ?
                                    'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300' :
                                    'bg-white dark:bg-zinc-700 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300'">
                                <span x-text="label"></span>
                            </button>
                        </template>
                    </div>
                    <div x-show="projectMetadata.labels.length === 0" class="text-sm italic text-zinc-500 dark:text-zinc-400">
                        No labels found in this project
                    </div>
                </div>

                <!-- Custom JQL Query -->
                <div class="mb-6">
                    <label for="custom-jql" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Advanced: Custom JQL Query (Optional)</label>
                    <div class="relative">
                        <input id="custom-jql" x-model="customJql" placeholder="E.g.: created >= -30d OR priority = High"
                            class="w-full px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                        <button @click="customJql = ''" x-show="customJql"
                            class="absolute right-3 top-2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        This will be combined with your filter selections above using AND operators
                    </p>
                </div>
            </div>

            <div class="mt-8 flex justify-between">
                <button @click="goToStep(1)" class="px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2 inline-block"></i> Back
                </button>
                <button @click="goToStep(3)" :disabled="selectedIssueTypes.length === 0"
                    class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-indigo-700 transition-colors">
                    Continue <i data-lucide="arrow-right" class="w-4 h-4 ml-2 inline-block"></i>
                </button>
            </div>
        </div>

        <!-- Step 3: Configure Mapping -->
        <div x-show="step === 3" class="p-6" x-cloak>
            <h3 class="text-lg font-semibold mb-4 text-zinc-900 dark:text-white">Configure Mapping</h3>

            <div class="space-y-6">
                <!-- Epic to Suite Mapping -->
                <div class="p-4 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg border border-zinc-200 dark:border-zinc-600">
                    <div class="flex items-center mb-3">
                        <label for="epic-to-suite" class="flex items-center cursor-pointer">
                            <input id="epic-to-suite" type="checkbox" x-model="mappings.epicToSuite" class="rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Convert Epics to Test Suites</span>
                        </label>
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-2">
                        When enabled, each Epic will become a Test Suite. Stories/Issues linked to epics will be organized inside their corresponding suite.
                    </p>
                </div>

                <!-- Default Suite Selection -->
                <div class="p-4 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg border border-zinc-200 dark:border-zinc-600">
                    <div class="flex items-center mb-3">
                        <label for="create-default-suite" class="flex items-center cursor-pointer">
                            <input id="create-default-suite" type="checkbox" x-model="mappings.createDefaultSuite" class="rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Create/Use Default Test Suite</span>
                        </label>
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-3">
                        Creates or uses a default Test Suite for issues not linked to Epics, or if Epic mapping is disabled.
                    </p>

                    <div x-show="mappings.createDefaultSuite">
                        <label for="default-suite" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Select Default Test Suite</label>
                        <select id="default-suite" x-model="mappings.defaultSuiteId" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white py-2 px-3">
                            <option value="">Create new default suite</option>
                            <template x-for="suite in testSuites" :key="suite.id">
                                <option :value="suite.id" x-text="suite.name"></option>
                            </template>
                        </select>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            If no suite is selected, a new one called "Imported from Jira" will be created
                        </p>
                    </div>
                </div>

                <!-- Include Description -->
                <div class="p-4 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg border border-zinc-200 dark:border-zinc-600">
                    <div class="flex items-center mb-2">
                        <label for="include-description" class="flex items-center cursor-pointer">
                            <input id="include-description" type="checkbox" x-model="mappings.includeDescription" class="rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Include Jira Descriptions</span>
                        </label>
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        Import detailed descriptions from Jira issues into your test cases and suites
                    </p>
                </div>

                <!-- Status to Priority Mapping -->
                <div class="p-4 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg border border-zinc-200 dark:border-zinc-600">
                    <h4 class="text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-3">Map Jira Status to Test Case Priority</h4>

                    <div class="space-y-3">
                        <template x-for="(status, index) in projectMetadata.statuses" :key="index">
                            <div class="flex items-center space-x-3">
                                <span class="w-1/3 text-sm text-zinc-700 dark:text-zinc-300" x-text="status"></span>
                                <select x-model="mappings.statusToPriority[status]" class="grow rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white py-1.5 px-3 text-sm">
                                    <option value="low">Low Priority</option>
                                    <option value="medium">Medium Priority</option>
                                    <option value="high">High Priority</option>
                                </select>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-between">
                <button @click="goToStep(2)" class="px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2 inline-block"></i> Back
                </button>
                <button @click="fetchPreview()" :disabled="fetchingPreview"
                    class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-indigo-700 transition-colors">
                    <i x-show="fetchingPreview" data-lucide="loader-2" class="w-4 h-4 mr-2 inline-block animate-spin"></i>
                    <span x-show="!fetchingPreview">Continue <i data-lucide="arrow-right" class="w-4 h-4 ml-2 inline-block"></i></span>
                    <span x-show="fetchingPreview">Generating Preview...</span>
                </button>
            </div>
        </div>

        <!-- Step 4: Preview & Import -->
        <div x-show="step === 4" class="p-6" x-cloak>
            <h3 class="text-lg font-semibold mb-4 text-zinc-900 dark:text-white">Preview & Import</h3>

            <div class="mb-6 p-4 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg border border-zinc-200 dark:border-zinc-600">
                <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-3">Import Summary</h4>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                    <div class="sm:col-span-2 py-2 border-b border-zinc-200 dark:border-zinc-600">
                        <dt class="font-medium text-zinc-700 dark:text-zinc-300">Jira Project</dt>
                        <dd class="mt-1 text-zinc-900 dark:text-white" x-text="selectedProject?.name + ' (' + selectedProject?.key + ')'"></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-700 dark:text-zinc-300">Issues to Import</dt>
                        <dd class="mt-1 text-zinc-900 dark:text-white" x-text="importPreview.total_issues || 0"></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-700 dark:text-zinc-300">Creating Test Suites</dt>
                        <dd class="mt-1 text-zinc-900 dark:text-white" x-text="importPreview.test_suites?.length || 0"></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-700 dark:text-zinc-300">Creating Test Cases</dt>
                        <dd class="mt-1 text-zinc-900 dark:text-white" x-text="importPreview.test_cases?.length || 0"></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-700 dark:text-zinc-300">Selected Issue Types</dt>
                        <dd class="mt-1 text-zinc-900 dark:text-white" x-text="selectedIssueTypes.join(', ')"></dd>
                    </div>
                </dl>
            </div>

            <div class="mb-6">
                <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-3">Preview Test Suites</h4>
                <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Jira Key</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Type</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            <template x-for="(suite, index) in importPreview.test_suites" :key="index">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-white" x-text="suite.name"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400" x-text="suite.jira_key"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">Epic</td>
                                </tr>
                            </template>
                            <template x-if="mappings.createDefaultSuite && importPreview.test_cases?.length > 0">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-white">Imported from Jira</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">-</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">Default Suite</td>
                                </tr>
                            </template>
                            <template x-if="importPreview.test_suites?.length === 0 && !mappings.createDefaultSuite">
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm italic text-zinc-500 dark:text-zinc-400">
                                        No test suites will be created. Enable "Create Default Test Suite" to create at least one suite.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-6">
                <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-3">Preview Test Cases (Sample)</h4>
                <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Title</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Jira Key</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Issue Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Suite</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            <template x-for="(testCase, index) in importPreview.test_cases?.slice(0, 5)" :key="index">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-white" x-text="testCase.title"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400" x-text="testCase.jira_key"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400" x-text="testCase.issue_type"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400" x-text="getSuiteName(testCase.parent_epic_key)"></td>
                                </tr>
                            </template>
                            <template x-if="importPreview.test_cases?.length > 5">
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm italic text-zinc-500 dark:text-zinc-400">
                                        And <span x-text="importPreview.test_cases.length - 5"></span> more test cases...
                                    </td>
                                </tr>
                            </template>
                            <template x-if="importPreview.test_cases?.length === 0">
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm italic text-zinc-500 dark:text-zinc-400">
                                        No test cases match your filter criteria.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/30 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i data-lucide="alert-triangle" class="h-5 w-5 text-amber-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-amber-800 dark:text-amber-300">Important Note</h3>
                        <div class="mt-2 text-sm text-amber-700 dark:text-amber-200">
                            <p>Importing a large number of issues may take some time. The page will redirect when complete.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-between">
                <button @click="goToStep(3)" :disabled="isImporting" class="px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2 inline-block"></i> Back
                </button>

                <form x-ref="importForm" method="POST" action="{{ route('integrations.jira.import.project') }}">
                    @csrf
                    <input type="hidden" name="jira_project_key" :value="selectedProject?.key">
                    <input type="hidden" name="jira_project_name" :value="selectedProject?.name">
                    <input type="hidden" name="arxitest_project_id" value="{{ $arxitestProjectId }}">
                    <input type="hidden" name="issue_types" :value="JSON.stringify(selectedIssueTypes)">
                    <input type="hidden" name="statuses" :value="JSON.stringify(selectedStatuses)">
                    <input type="hidden" name="labels" :value="JSON.stringify(selectedLabels)">
                    <input type="hidden" name="custom_jql" :value="customJql">
                    <input type="hidden" name="mappings[epic_to_suite]" :value="mappings.epicToSuite">
                    <input type="hidden" name="mappings[create_default_suite]" :value="mappings.createDefaultSuite">
                    <input type="hidden" name="mappings[default_suite_id]" :value="mappings.defaultSuiteId">
                    <input type="hidden" name="mappings[include_description]" :value="mappings.includeDescription">
                    <input type="hidden" name="mappings[status_to_priority]" :value="JSON.stringify(mappings.statusToPriority)">

                    <button type="submit" @click="startImport()" :disabled="isImporting || importPreview.total_issues === 0"
                        class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-indigo-700 transition-colors">
                        <i x-show="isImporting" data-lucide="loader-2" class="w-4 h-4 mr-2 inline-block animate-spin"></i>
                        <span x-show="!isImporting">Start Import</span>
                        <span x-show="isImporting">Importing...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.btn-primary {
    @apply inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200;
}
.btn-secondary {
    @apply inline-flex items-center px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200;
}
</style>
@endpush

@push('scripts')
<script>
function jiraImport() {
    return {
        step: 1,
        isLoadingMetadata: false,
        fetchingPreview: false,
        isImporting: false,
        projectSearch: '',
        selectedProject: null,
        projectMetadata: {
            issueTypes: [],
            statuses: [],
            labels: []
        },
        selectedIssueTypes: [],
        selectedStatuses: [],
        selectedLabels: [],
        customJql: '',
        testSuites: @json($testSuites ?? []),
        existingMappings: @json($existingMappings ?? []),
        mappings: {
            epicToSuite: true,
            createDefaultSuite: true,
            defaultSuiteId: '',
            includeDescription: true,
            statusToPriority: {}
        },
        importPreview: {
            test_suites: [],
            test_cases: [],
            total_issues: 0
        },

        init() {
            // Initialize mappings with existing values if available
            if (this.existingMappings.length > 0) {
                this.mappings = {
                    ...this.mappings,
                    ...this.existingMappings
                };
            }

            // Initialize status to priority mappings
            if (Object.keys(this.mappings.statusToPriority).length === 0) {
                this.initializeStatusMappings();
            }

            // Re-render Lucide icons when Alpine initializes
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });
        },

        get filteredProjects() {
            const projects = @json($jiraProjects ?? []);
            if (!this.projectSearch) return projects;

            const search = this.projectSearch.toLowerCase();
            return projects.filter(project =>
                project.name.toLowerCase().includes(search) ||
                project.key.toLowerCase().includes(search)
            );
        },

        initializeStatusMappings() {
            // Set default priority for common statuses
            const defaultMappings = {
                'To Do': 'low',
                'Open': 'low',
                'Backlog': 'low',
                'In Progress': 'medium',
                'In Review': 'medium',
                'Testing': 'medium',
                'Done': 'high',
                'Closed': 'high',
                'Resolved': 'high'
            };

            this.projectMetadata.statuses.forEach(status => {
                this.mappings.statusToPriority[status] = defaultMappings[status] || 'medium';
            });
        },

        selectProject(project) {
            this.selectedProject = project;
        },

        goToStep(step) {
            if (step === 2 && this.step === 1 && this.selectedProject) {
                this.loadProjectMetadata();
            }

            this.step = step;

            // Update icons after step change
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });
        },

        loadProjectMetadata() {
            this.isLoadingMetadata = true;

            fetch('{{ route("integrations.jira.project-metadata") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    jira_project_key: this.selectedProject.key,
                    arxitest_project_id: '{{ $arxitestProjectId }}'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.projectMetadata = data;

                    // Pre-select issue types with defaults
                    this.selectedIssueTypes = this.projectMetadata.issueTypes
                        .filter(type => ['Epic', 'Story', 'Task', 'Bug'].includes(type.name))
                        .map(type => type.name);

                    // Initialize status to priority mappings
                    this.initializeStatusMappings();
                } else {
                    console.error('Failed to load project metadata:', data.message);
                    alert('Error loading Jira project data: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error loading project metadata:', error);
                alert('Failed to load Jira project information. Please try again.');
            })
            .finally(() => {
                this.isLoadingMetadata = false;
                // Update icons after loading
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            });
        },

        toggleIssueType(type) {
            const index = this.selectedIssueTypes.indexOf(type);
            if (index === -1) {
                this.selectedIssueTypes.push(type);
            } else {
                this.selectedIssueTypes.splice(index, 1);
            }
        },

        toggleStatus(status) {
            const index = this.selectedStatuses.indexOf(status);
            if (index === -1) {
                this.selectedStatuses.push(status);
            } else {
                this.selectedStatuses.splice(index, 1);
            }
        },

        toggleLabel(label) {
            const index = this.selectedLabels.indexOf(label);
            if (index === -1) {
                this.selectedLabels.push(label);
            } else {
                this.selectedLabels.splice(index, 1);
            }
        },

        fetchPreview() {
            if (this.fetchingPreview) return;

            this.fetchingPreview = true;

            fetch('{{ route("integrations.jira.preview-import") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    jira_project_key: this.selectedProject.key,
                    arxitest_project_id: '{{ $arxitestProjectId }}',
                    issue_types: this.selectedIssueTypes,
                    statuses: this.selectedStatuses,
                    labels: this.selectedLabels,
                    custom_jql: this.customJql,
                    mappings: {
                        epic_to_suite: this.mappings.epicToSuite,
                        create_default_suite: this.mappings.createDefaultSuite,
                        default_suite_id: this.mappings.defaultSuiteId,
                        include_description: this.mappings.includeDescription
                    },
                    sample_size: 20
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.importPreview = data.preview;
                    this.goToStep(4);
                } else {
                    console.error('Failed to generate preview:', data.message);
                    alert('Error generating import preview: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error generating preview:', error);
                alert('Failed to generate import preview. Please try again.');
            })
            .finally(() => {
                this.fetchingPreview = false;
                // Update icons after preview loads
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            });
        },

        getSuiteName(epicKey) {
            if (!epicKey) {
                return 'Default Suite';
            }

            const suite = this.importPreview.test_suites.find(s => s.jira_key === epicKey);
            return suite ? suite.name : 'Default Suite';
        },

        startImport() {
            this.isImporting = true;
        }
    };
}
</script>
@endpush
