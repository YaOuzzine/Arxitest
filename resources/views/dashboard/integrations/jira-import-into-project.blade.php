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
<div class="h-full" x-data="jiraImport({
        arxitestProjectId: '{{ $arxitestProjectId }}',
        jiraProjectsData: {{ json_encode($jiraProjects ?? []) }},
        arxitestSuitesData: {{ json_encode($testSuites ?? []) }},
        existingMappingsData: {{ json_encode($existingMappings ?? []) }},
        projectMetadataUrl: '{{ route('integrations.jira.project-metadata') }}',
        previewImportUrl: '{{ route('integrations.jira.preview-import') }}',
        csrfToken: '{{ csrf_token() }}'
    })" x-init="init()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Import from Jira</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Configure and customize how to import Jira issues into '{{ $arxitestProjectName }}'.
            </p>
        </div>
        <div>
            <a href="{{ route('dashboard.integrations.index') }}" class="btn-secondary">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Integrations
            </a>
        </div>
    </div>

    {{-- Display general errors from session --}}
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 rounded-lg">
            <div class="flex items-center">
                <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Display specific error messages from Alpine --}}
    <div x-show="errorMessage" x-cloak class="mb-6 p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 rounded-lg">
        <div class="flex items-center">
            <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i>
            <span x-text="errorMessage"></span>
        </div>
    </div>

    <!-- Multi-step import wizard -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-zinc-200/50 dark:border-zinc-700/50 overflow-hidden">
        <!-- Progress Steps -->
        <div class="border-b border-zinc-200 dark:border-zinc-700">
            <div class="px-6 py-4">
                <div class="flex flex-col sm:flex-row items-center justify-between space-y-2 sm:space-y-0 sm:space-x-4 text-sm">
                    {{-- Step 1 --}}
                    <button @click="goToStep(1)" :disabled="isImporting"
                            class="flex items-center"
                            :class="{'font-semibold text-indigo-600 dark:text-indigo-400': step >= 1, 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300': step < 1}">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full mr-2 border-2"
                              :class="{'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-500 dark:border-indigo-400': step >= 1, 'border-zinc-300 dark:border-zinc-600': step < 1}">1</span>
                        Select Project
                    </button>
                    <div class="flex-1 h-px bg-zinc-200 dark:bg-zinc-700 sm:block hidden"></div>
                    {{-- Step 2 --}}
                    <button @click="goToStep(2)" :disabled="!canGoToStep(2) || isImporting"
                            class="flex items-center"
                            :class="{'font-semibold text-indigo-600 dark:text-indigo-400': step >= 2, 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300': step < 2, 'opacity-50 cursor-not-allowed': !canGoToStep(2)}">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full mr-2 border-2"
                              :class="{'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-500 dark:border-indigo-400': step >= 2, 'border-zinc-300 dark:border-zinc-600': step < 2}">2</span>
                        Filter Issues
                    </button>
                    <div class="flex-1 h-px bg-zinc-200 dark:bg-zinc-700 sm:block hidden"></div>
                    {{-- Step 3 --}}
                    <button @click="goToStep(3)" :disabled="!canGoToStep(3) || isImporting"
                            class="flex items-center"
                            :class="{'font-semibold text-indigo-600 dark:text-indigo-400': step >= 3, 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300': step < 3, 'opacity-50 cursor-not-allowed': !canGoToStep(3)}">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full mr-2 border-2"
                              :class="{'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-500 dark:border-indigo-400': step >= 3, 'border-zinc-300 dark:border-zinc-600': step < 3}">3</span>
                        Configure Mapping
                    </button>
                    <div class="flex-1 h-px bg-zinc-200 dark:bg-zinc-700 sm:block hidden"></div>
                    {{-- Step 4 --}}
                    <button @click="goToStep(4)" :disabled="!canGoToStep(4) || isImporting"
                            class="flex items-center"
                            :class="{'font-semibold text-indigo-600 dark:text-indigo-400': step >= 4, 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300': step < 4, 'opacity-50 cursor-not-allowed': !canGoToStep(4)}">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full mr-2 border-2"
                              :class="{'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-500 dark:border-indigo-400': step >= 4, 'border-zinc-300 dark:border-zinc-600': step < 4}">4</span>
                        Preview & Import
                    </button>
                </div>
            </div>
        </div>

        {{-- Step 1: Select Jira Project --}}
        <div x-show="step === 1" class="p-6 animate-fade-in">
            <h3 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-white">1. Select Jira Project</h3>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">Choose the Jira project you want to import issues from.</p>

            <div class="mb-6">
                <label for="project-search" class="sr-only">Search Projects</label>
                <div class="relative">
                    <input id="project-search" type="search" x-model="projectSearch" placeholder="Search projects by name or key..."
                        class="w-full px-4 py-3 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent pl-10">
                    <div class="absolute left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-5 h-5 text-zinc-400"></i>
                    </div>
                </div>
            </div>

            <div class="max-h-[50vh] overflow-y-auto pr-2 space-y-3 custom-scrollbar">
                <template x-if="filteredProjects.length === 0 && !isLoadingProjects">
                    <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                        <i data-lucide="search-x" class="w-12 h-12 mx-auto mb-3 text-zinc-400 dark:text-zinc-500"></i>
                        <p>No projects match your search or none found.</p>
                    </div>
                </template>
                <template x-if="isLoadingProjects">
                     <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                        <i data-lucide="loader-2" class="w-8 h-8 mx-auto mb-3 text-indigo-500 animate-spin"></i>
                        <p>Loading Jira Projects...</p>
                    </div>
                </template>
                <template x-for="project in filteredProjects" :key="project.id">
                    <div @click="selectProject(project)"
                        class="p-4 border rounded-lg cursor-pointer transition-all duration-200 flex items-start gap-4 hover:shadow-md"
                        :class="{
                            'border-indigo-500 dark:border-indigo-400 ring-2 ring-indigo-300 dark:ring-indigo-600 bg-indigo-50 dark:bg-indigo-900/30': selectedProject?.id === project.id,
                            'border-zinc-200 dark:border-zinc-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:bg-zinc-50 dark:hover:bg-zinc-700/50': selectedProject?.id !== project.id
                        }">
                        {{-- Project Icon --}}
                        <div class="shrink-0">
                             <div class="w-10 h-10 rounded-lg flex items-center justify-center overflow-hidden bg-zinc-100 dark:bg-zinc-700 ring-1 ring-zinc-200 dark:ring-zinc-600">
                                <template x-if="project.avatarUrls && project.avatarUrls['32x32']">
                                    <img :src="project.avatarUrls['32x32']" :alt="project.name + ' Logo'" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!project.avatarUrls || !project.avatarUrls['32x32']">
                                    <span class="text-zinc-600 dark:text-zinc-300 font-semibold text-sm" x-text="project.key ? project.key.substring(0, 2).toUpperCase() : '?'"></span>
                                </template>
                            </div>
                        </div>
                        {{-- Project Details --}}
                        <div class="flex-1 min-w-0">
                            <h4 class="font-medium text-zinc-900 dark:text-white truncate" x-text="project.name" :title="project.name"></h4>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                Key: <span class="font-mono" x-text="project.key"></span>
                                <template x-if="project.projectTypeKey">
                                    <span class="ml-2 capitalize font-medium px-1.5 py-0.5 rounded text-indigo-700 bg-indigo-100 dark:text-indigo-300 dark:bg-indigo-900/30" x-text="project.projectTypeKey.replace('-', ' ')"></span>
                                </template>
                            </p>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-8 flex justify-end">
                <button @click="goToStep(2)" :disabled="!selectedProject" class="btn-primary">
                    Continue <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                </button>
            </div>
        </div>

        {{-- Step 2: Filter Issues --}}
        <div x-show="step === 2" class="p-6 animate-fade-in" x-cloak>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">2. Filter Jira Issues</h3>
                <div class="text-sm text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-700 px-3 py-1 rounded-full">
                    Project: <span class="font-medium text-zinc-900 dark:text-white" x-text="selectedProject?.name"></span>
                </div>
            </div>

            <div class="mb-6 p-4 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg border border-zinc-200 dark:border-zinc-600" x-show="isLoadingMetadata">
                <div class="flex items-center justify-center space-x-2 text-zinc-600 dark:text-zinc-400">
                    <i data-lucide="loader-2" class="w-5 h-5 animate-spin text-indigo-500"></i>
                    <span>Loading issue types, statuses and labels from Jira...</span>
                </div>
            </div>

            <div x-show="!isLoadingMetadata" class="space-y-6">
                {{-- Issue Types Selection --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Issue Types to Import <span class="text-red-500">*</span></label>
                    <div class="flex flex-wrap gap-2">
                        <template x-if="projectMetadata.issueTypes.length === 0">
                            <span class="text-sm text-zinc-500 italic">No issue types found or failed to load.</span>
                        </template>
                        <template x-for="type in projectMetadata.issueTypes" :key="type.id">
                             <button type="button" @click="toggleIssueType(type.name)"
                                class="inline-flex items-center px-3 py-1.5 rounded-full text-sm border transition-all duration-200 transform hover:scale-105"
                                :class="selectedIssueTypes.includes(type.name) ?
                                    'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300 ring-1 ring-indigo-300' :
                                    'bg-white dark:bg-zinc-700 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:border-indigo-400'">
                                {{-- Display Jira Icon --}}
                                <img :src="type.iconUrl" :alt="type.name" class="w-4 h-4 mr-1.5 flex-shrink-0">
                                <span x-text="type.name"></span>
                                <i data-lucide="check" class="w-3 h-3 ml-1.5 text-indigo-500" x-show="selectedIssueTypes.includes(type.name)"></i>
                            </button>
                        </template>
                    </div>
                     @error('issue_types') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Statuses Selection --}}
                 <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Status Filter (Optional - Select statuses to include)</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-if="projectMetadata.statuses.length === 0">
                            <span class="text-sm text-zinc-500 italic">No statuses found or failed to load.</span>
                        </template>
                        <template x-for="status in projectMetadata.statuses" :key="status">
                            <button type="button" @click="toggleStatus(status)"
                                class="px-3 py-1.5 rounded-full text-sm border transition-all duration-200 transform hover:scale-105"
                                :class="selectedStatuses.includes(status) ?
                                    'bg-blue-100 dark:bg-blue-900/30 border-blue-300 dark:border-blue-600 text-blue-700 dark:text-blue-300 ring-1 ring-blue-300' :
                                    'bg-white dark:bg-zinc-700 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:border-blue-400'">
                                <span x-text="status"></span>
                                <i data-lucide="check" class="w-3 h-3 ml-1.5 text-blue-500" x-show="selectedStatuses.includes(status)"></i>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Labels Selection --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Label Filter (Optional - Select labels to include)</label>
                    <div x-show="projectMetadata.labels.length === 0 && !isLoadingMetadata" class="text-sm italic text-zinc-500 dark:text-zinc-400">
                        No labels found in recent issues for this project.
                    </div>
                     <div class="flex flex-wrap gap-2">
                        <template x-for="label in projectMetadata.labels" :key="label">
                            <button type="button" @click="toggleLabel(label)"
                                class="px-3 py-1.5 rounded-full text-sm border transition-all duration-200 transform hover:scale-105"
                                :class="selectedLabels.includes(label) ?
                                    'bg-green-100 dark:bg-green-900/30 border-green-300 dark:border-green-600 text-green-700 dark:text-green-300 ring-1 ring-green-300' :
                                    'bg-white dark:bg-zinc-700 border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:border-green-400'">
                                <span x-text="label"></span>
                                <i data-lucide="check" class="w-3 h-3 ml-1.5 text-green-500" x-show="selectedLabels.includes(label)"></i>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Custom JQL Query --}}
                <div>
                    <label for="custom-jql" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Advanced: Custom JQL Filter (Optional)</label>
                    <div class="relative">
                        <input id="custom-jql" x-model="customJql" placeholder="E.g., created >= -30d OR priority = High"
                            class="w-full px-4 py-3 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent pl-10">
                        <i data-lucide="search-code" class="absolute left-3 top-3.5 w-5 h-5 text-zinc-400"></i>
                        <button type="button" @click="customJql = ''" x-show="customJql"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 p-1 rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-600">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        This JQL will be added using `AND` to your other filters (Project, Issue Types, Status, Labels).
                    </p>
                </div>
            </div>

            <div class="mt-8 flex justify-between">
                <button @click="goToStep(1)" class="btn-secondary">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back
                </button>
                <button @click="goToStep(3)" :disabled="selectedIssueTypes.length === 0" class="btn-primary">
                    Continue <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                </button>
            </div>
        </div>

        {{-- Step 3: Configure Mapping --}}
        <div x-show="step === 3" class="p-6 animate-fade-in" x-cloak>
             <h3 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-white">3. Configure Mapping</h3>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">Define how Jira issues and fields should map to Arxitest entities.</p>

             <div class="space-y-6">
                {{-- Epic to Suite Mapping --}}
                <div class="p-5 bg-zinc-50 dark:bg-zinc-700/30 rounded-xl border border-zinc-200 dark:border-zinc-600/50 shadow-sm">
                    <div class="flex items-center justify-between">
                         <label for="epic-to-suite" class="flex items-center cursor-pointer">
                             <input id="epic-to-suite" type="checkbox" x-model="mappings.epicToSuite" class="form-checkbox rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                             <span class="ml-3 text-sm font-medium text-zinc-800 dark:text-zinc-200">Map Jira Epics to Test Suites</span>
                         </label>
                         <span class="text-xs px-2 py-0.5 rounded-full" :class="mappings.epicToSuite ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-600 dark:text-zinc-300'" x-text="mappings.epicToSuite ? 'Enabled' : 'Disabled'"></span>
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2 ml-7">
                        If enabled, each Jira Epic found by your filters will create a Test Suite. Issues linked to that Epic will be placed inside.
                    </p>
                </div>

                 {{-- Default Suite Selection --}}
                <div class="p-5 bg-zinc-50 dark:bg-zinc-700/30 rounded-xl border border-zinc-200 dark:border-zinc-600/50 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <label for="create-default-suite" class="flex items-center cursor-pointer">
                            <input id="create-default-suite" type="checkbox" x-model="mappings.createDefaultSuite" class="form-checkbox rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-3 text-sm font-medium text-zinc-800 dark:text-zinc-200">Use Default Test Suite</span>
                        </label>
                         <span class="text-xs px-2 py-0.5 rounded-full" :class="mappings.createDefaultSuite ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-600 dark:text-zinc-300'" x-text="mappings.createDefaultSuite ? 'Enabled' : 'Disabled'"></span>
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-3 ml-7">
                        Handles issues not linked to an Epic (or if Epic mapping is off). Choose an existing suite or a new one will be created.
                    </p>
                    <div x-show="mappings.createDefaultSuite" class="ml-7">
                        <label for="default-suite" class="sr-only">Select Default Test Suite</label>
                        <select id="default-suite" x-model="mappings.defaultSuiteId" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white py-2.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">-- Create New Default Suite --</option>
                             <template x-for="suite in arxitestSuites" :key="suite.id">
                                <option :value="suite.id" x-text="suite.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                 {{-- Include Description --}}
                 <div class="p-5 bg-zinc-50 dark:bg-zinc-700/30 rounded-xl border border-zinc-200 dark:border-zinc-600/50 shadow-sm">
                    <div class="flex items-center justify-between">
                         <label for="include-description" class="flex items-center cursor-pointer">
                             <input id="include-description" type="checkbox" x-model="mappings.includeDescription" class="form-checkbox rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500">
                             <span class="ml-3 text-sm font-medium text-zinc-800 dark:text-zinc-200">Import Jira Descriptions</span>
                         </label>
                          <span class="text-xs px-2 py-0.5 rounded-full" :class="mappings.includeDescription ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-600 dark:text-zinc-300'" x-text="mappings.includeDescription ? 'Enabled' : 'Disabled'"></span>
                    </div>
                     <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2 ml-7">
                        Include the full issue description in the corresponding Arxitest test suite or test case.
                    </p>
                </div>

                 {{-- Status to Priority Mapping --}}
                <div class="p-5 bg-zinc-50 dark:bg-zinc-700/30 rounded-xl border border-zinc-200 dark:border-zinc-600/50 shadow-sm">
                    <h4 class="text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-3">Map Jira Status to Test Case Priority</h4>
                     <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-4">Optionally assign Arxitest test case priorities based on the Jira issue status.</p>

                    <div class="space-y-3 max-h-48 overflow-y-auto custom-scrollbar pr-2">
                         <template x-if="projectMetadata.statuses.length === 0 && !isLoadingMetadata">
                            <p class="text-sm italic text-zinc-500 dark:text-zinc-400">No statuses loaded to map.</p>
                        </template>
                        <template x-for="(status, index) in projectMetadata.statuses" :key="index">
                             <div class="flex items-center space-x-3">
                                <span class="w-1/3 text-sm text-zinc-700 dark:text-zinc-300 truncate" :title="status" x-text="status"></span>
                                 <span class="text-zinc-400 text-sm"><i data-lucide="arrow-right" class="w-4 h-4"></i></span>
                                <select x-model="mappings.statusToPriority[status]" class="grow rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white py-1.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                     <option value="">(No Mapping)</option>
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
                <button @click="goToStep(2)" class="btn-secondary">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back
                </button>
                <button @click="fetchPreview()" :disabled="fetchingPreview" class="btn-primary flex items-center">
                    <i x-show="fetchingPreview" data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i>
                    <span x-show="!fetchingPreview">Generate Preview <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i></span>
                    <span x-show="fetchingPreview">Generating Preview...</span>
                </button>
            </div>
        </div>

        {{-- Step 4: Preview & Import --}}
        <div x-show="step === 4" class="p-6 animate-fade-in" x-cloak>
             <h3 class="text-xl font-semibold mb-4 text-zinc-900 dark:text-white">4. Preview & Import</h3>
             <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">Review the items that will be created or updated in Arxitest based on your selections.</p>

            <div class="mb-6 p-5 bg-zinc-50 dark:bg-zinc-700/30 rounded-xl border border-zinc-200 dark:border-zinc-600/50 shadow-sm">
                 <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-3">Import Summary</h4>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                     <div class="sm:col-span-2 py-2 border-b border-zinc-200 dark:border-zinc-600/50">
                        <dt class="font-medium text-zinc-600 dark:text-zinc-300">Jira Project</dt>
                        <dd class="mt-1 text-zinc-900 dark:text-white flex items-center space-x-2">
                            <template x-if="selectedProject.avatarUrls && selectedProject.avatarUrls['16x16']">
                                <img :src="selectedProject.avatarUrls['16x16']" class="w-4 h-4 rounded-sm">
                            </template>
                            <span x-text="selectedProject?.name + ' (' + selectedProject?.key + ')'"></span>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-600 dark:text-zinc-300">Issues Found (Matching Filters)</dt>
                        <dd class="mt-1 text-zinc-900 dark:text-white" x-text="importPreview.total_issues ?? 0"></dd>
                    </div>
                     <div>
                        <dt class="font-medium text-zinc-600 dark:text-zinc-300">Test Suites to Create/Update</dt>
                         <dd class="mt-1 text-zinc-900 dark:text-white" x-text="(importPreview.test_suites?.length || 0) + (mappings.createDefaultSuite && (importPreview.test_cases?.length || 0) > 0 ? 1 : 0)"></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-600 dark:text-zinc-300">Test Cases to Create/Update</dt>
                        <dd class="mt-1 text-zinc-900 dark:text-white" x-text="importPreview.test_cases?.length || 0"></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-600 dark:text-zinc-300">Selected Issue Types</dt>
                        <dd class="mt-1 text-zinc-900 dark:text-white truncate" :title="selectedIssueTypes.join(', ')" x-text="selectedIssueTypes.join(', ') || 'None'"></dd>
                    </div>
                </dl>
            </div>

            <div class="mb-6">
                <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-3">Preview Test Suites (<span x-text="importPreview.test_suites?.length || 0"></span>)</h4>
                <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden shadow-sm">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-700/50">
                            <tr>
                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider w-10"></th>
                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Jira Key</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            <template x-if="importPreview.test_suites?.length === 0 && !mappings.createDefaultSuite">
                                <tr><td colspan="3" class="px-4 py-4 text-center text-sm italic text-zinc-500 dark:text-zinc-400">No test suites will be created.</td></tr>
                            </template>
                            <template x-for="(suite, index) in importPreview.test_suites" :key="index">
                                <tr>
                                     <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                        <template x-if="suite.jira_icon_url">
                                            <img :src="suite.jira_icon_url" :alt="suite.name" class="w-4 h-4 inline-block">
                                        </template>
                                        <template x-if="!suite.jira_icon_url"><i data-lucide="layers" class="w-4 h-4 inline-block text-indigo-500"></i></template>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-white" x-text="suite.name"></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400" x-text="suite.jira_key"></td>
                                </tr>
                            </template>
                            <template x-if="mappings.createDefaultSuite && (importPreview.test_cases?.length || 0) > 0">
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center"><i data-lucide="folder" class="w-4 h-4 inline-block text-zinc-400"></i></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-white italic">Default Suite (if needed)</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">-</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-6">
                 <h4 class="font-medium text-zinc-800 dark:text-zinc-200 mb-3">Preview Test Cases (<span x-text="importPreview.test_cases?.slice(0, 5).length || 0"></span> of <span x-text="importPreview.test_cases?.length || 0"></span>)</h4>
                <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden shadow-sm">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-700/50">
                            <tr>
                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider w-10">Type</th>
                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Title</th>
                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Jira Key</th>
                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Target Suite</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            <template x-if="importPreview.test_cases?.length === 0">
                                <tr><td colspan="4" class="px-4 py-4 text-center text-sm italic text-zinc-500 dark:text-zinc-400">No test cases match your filter criteria.</td></tr>
                            </template>
                            <template x-for="(testCase, index) in importPreview.test_cases?.slice(0, 5)" :key="index">
                                <tr>
                                     <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                        <template x-if="testCase.jira_icon_url">
                                            <img :src="testCase.jira_icon_url" :alt="testCase.issue_type" class="w-4 h-4 inline-block">
                                        </template>
                                        <template x-if="!testCase.jira_icon_url"><i data-lucide="check-square" class="w-4 h-4 inline-block text-green-500"></i></template>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-white truncate max-w-xs" :title="testCase.title" x-text="testCase.title"></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400" x-text="testCase.jira_key"></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400" x-text="getSuiteName(testCase.parent_epic_key)"></td>
                                </tr>
                            </template>
                            <template x-if="importPreview.test_cases?.length > 5">
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-center text-sm italic text-zinc-500 dark:text-zinc-400">
                                        + <span x-text="(importPreview.test_cases.length || 0) - 5"></span> more test cases...
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

             <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/30 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0"> <i data-lucide="alert-triangle" class="h-5 w-5 text-amber-500 dark:text-amber-400"></i> </div>
                    <div class="ml-3">
                         <h3 class="text-sm font-medium text-amber-800 dark:text-amber-300">Important Note</h3>
                        <div class="mt-2 text-sm text-amber-700 dark:text-amber-200">
                             <p>Importing a large number of issues may take several minutes. The process will run in the background, and you'll be redirected upon completion. Do not close this tab until the import finishes.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-between">
                <button @click="goToStep(3)" :disabled="isImporting" class="btn-secondary">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back
                </button>

                {{-- Actual Form Submission --}}
                <form x-ref="importForm" method="POST" action="{{ route('integrations.jira.import.project') }}" @submit="startImport()">
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

                    <button type="submit" :disabled="isImporting || (importPreview.total_issues ?? 0) === 0" class="btn-primary flex items-center">
                        <i x-show="isImporting" data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i>
                        <i x-show="!isImporting" data-lucide="download-cloud" class="w-4 h-4 mr-2"></i>
                        <span x-show="!isImporting">Start Import (<span x-text="importPreview.total_issues ?? 0"></span> issues)</span>
                        <span x-show="isImporting">Importing... Please Wait</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-primary { @apply inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed; }
    .btn-secondary { @apply inline-flex items-center px-4 py-2 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 font-medium rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed; }
    .dropdown-container { position: relative; }
    .dropdown-menu {
        position: absolute;
        z-index: 50;
        margin-top: 0.25rem;
        width: 100%;
        background-color: white;
        border-radius: 0.375rem; /* rounded-md */
        --tw-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --tw-shadow-colored: 0 10px 15px -3px var(--tw-shadow-color), 0 4px 6px -4px var(--tw-shadow-color);
        box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
        border: 1px solid #e5e7eb; /* zinc-200 */
        max-height: 15rem; /* max-h-60 */
        overflow-y: auto;
    }
    .dark .dropdown-menu { background-color: #27272a; border-color: #3f3f46; } /* zinc-800, zinc-700 */
    .dropdown-item { padding: 0.5rem 1rem; font-size: 0.875rem; cursor: pointer; display: flex; align-items: center; } /* Adjust padding */
    .dropdown-item:hover { background-color: #f3f4f6; } /* zinc-100 */
    .dark .dropdown-item:hover { background-color: #3f3f46; } /* zinc-700 */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(161, 161, 170, 0.3); border-radius: 3px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(113, 113, 122, 0.4); }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
</style>
@endpush

@push('scripts')
<script>
    function jiraImport(config) {
        return {
            step: 1,
            isLoadingMetadata: false,
            fetchingPreview: false,
            isImporting: false,
            errorMessage: '',
            projectSearch: '',
            selectedProject: null,
            arxitestProjectId: config.arxitestProjectId,
            allJiraProjects: config.jiraProjectsData,
            projectMetadata: { issueTypes: [], statuses: [], labels: [] },
            selectedIssueTypes: [],
            selectedStatuses: [],
            selectedLabels: [],
            customJql: '',
            arxitestSuites: config.arxitestSuitesData,
            mappings: {
                epicToSuite: true,
                createDefaultSuite: true,
                defaultSuiteId: '',
                includeDescription: true,
                statusToPriority: {} // Initialize as empty object
            },
            importPreview: { test_suites: [], test_cases: [], total_issues: 0 },

            init() {
                // Load existing mappings if provided
                if (config.existingMappingsData && Object.keys(config.existingMappingsData).length > 0) {
                    this.mappings = { ...this.mappings, ...config.existingMappingsData };
                     // Ensure statusToPriority is an object if it exists in saved data but is null/empty
                    if (typeof this.mappings.statusToPriority !== 'object' || this.mappings.statusToPriority === null) {
                        this.mappings.statusToPriority = {};
                    }
                } else {
                     this.mappings.statusToPriority = {}; // Ensure it's an object if no saved data
                }


                // Initialize icons after Alpine is ready
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            },

            get filteredProjects() {
                if (!this.projectSearch) return this.allJiraProjects;
                const search = this.projectSearch.toLowerCase();
                return this.allJiraProjects.filter(project =>
                    project.name.toLowerCase().includes(search) ||
                    project.key.toLowerCase().includes(search)
                );
            },

            selectProject(project) {
                this.selectedProject = project;
                this.errorMessage = ''; // Clear errors when selecting a project
            },

            canGoToStep(targetStep) {
                if (targetStep <= this.step) return true; // Can always go back
                switch (this.step) {
                    case 1: return !!this.selectedProject;
                    case 2: return this.selectedIssueTypes.length > 0;
                    case 3: return this.importPreview.total_issues !== null; // Allow going to preview once generated
                    default: return false;
                }
            },

            goToStep(targetStep) {
                this.errorMessage = ''; // Clear errors on step change
                if (!this.canGoToStep(targetStep)) {
                    if (this.step === 1 && !this.selectedProject) this.errorMessage = "Please select a Jira project first.";
                    if (this.step === 2 && this.selectedIssueTypes.length === 0) this.errorMessage = "Please select at least one issue type to import.";
                    return;
                }

                if (targetStep === 2 && this.step === 1) {
                    this.loadProjectMetadata(); // Load data when moving to step 2
                }

                if (targetStep === 4 && this.step === 3) {
                    this.fetchPreview(); // Generate preview when moving to step 4
                } else {
                    this.step = targetStep; // Directly change step otherwise
                }

                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            },

            async loadProjectMetadata() {
                if (!this.selectedProject) return;
                this.isLoadingMetadata = true;
                this.errorMessage = '';
                try {
                    const response = await fetch(config.projectMetadataUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({ jira_project_key: this.selectedProject.key, arxitest_project_id: this.arxitestProjectId })
                    });
                    const data = await response.json();
                    if (!response.ok || !data.success) throw new Error(data.message || 'Failed to load metadata');
                    this.projectMetadata = data;
                    // Set default selections after loading
                    this.selectedIssueTypes = this.projectMetadata.issueTypes.filter(type => ['Epic', 'Story', 'Task', 'Bug'].includes(type.name)).map(type => type.name);
                    this.initializeStatusMappings(); // Initialize mappings after statuses are loaded
                } catch (error) {
                    this.errorMessage = `Error loading Jira project data: ${error.message}. Please check connection and permissions.`;
                    this.projectMetadata = { issueTypes: [], statuses: [], labels: [] }; // Reset on error
                } finally {
                    this.isLoadingMetadata = false;
                     this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                }
            },

            initializeStatusMappings() {
                 const defaultMappings = { 'To Do': 'low', 'In Progress': 'medium', 'Done': 'high', 'Closed': 'high', 'Resolved': 'high' };
                 let existingMappings = this.mappings.statusToPriority || {};
                 let newMappings = {};
                 (this.projectMetadata.statuses || []).forEach(status => {
                     // Use existing mapping if available, otherwise use default, otherwise null
                     newMappings[status] = existingMappings[status] !== undefined ? existingMappings[status] : (defaultMappings[status] || '');
                 });
                 this.mappings.statusToPriority = newMappings;
             },

            toggleIssueType(type) { this.selectedIssueTypes.includes(type) ? this.selectedIssueTypes = this.selectedIssueTypes.filter(t => t !== type) : this.selectedIssueTypes.push(type); },
            toggleStatus(status) { this.selectedStatuses.includes(status) ? this.selectedStatuses = this.selectedStatuses.filter(s => s !== status) : this.selectedStatuses.push(status); },
            toggleLabel(label) { this.selectedLabels.includes(label) ? this.selectedLabels = this.selectedLabels.filter(l => l !== label) : this.selectedLabels.push(label); },

            async fetchPreview() {
                if (this.fetchingPreview || !this.selectedProject) return;
                this.fetchingPreview = true;
                this.errorMessage = '';
                try {
                    const response = await fetch(config.previewImportUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({
                            jira_project_key: this.selectedProject.key,
                            arxitest_project_id: this.arxitestProjectId,
                            issue_types: this.selectedIssueTypes,
                            statuses: this.selectedStatuses,
                            labels: this.selectedLabels,
                            custom_jql: this.customJql,
                            mappings: this.mappings,
                            sample_size: 20 // Limit preview size
                        })
                    });
                    const data = await response.json();
                    if (!response.ok || !data.success) throw new Error(data.message || 'Failed to generate preview');
                    this.importPreview = data.preview;
                    this.step = 4; // Move to next step on success
                } catch (error) {
                    this.errorMessage = `Error generating preview: ${error.message}`;
                } finally {
                    this.fetchingPreview = false;
                     this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                }
            },

            getSuiteName(epicKey) {
                if (!epicKey || !this.mappings.epicToSuite) {
                    return this.mappings.createDefaultSuite ? 'Default Suite' : 'N/A';
                }
                const suite = (this.importPreview.test_suites || []).find(s => s.jira_key === epicKey);
                return suite ? suite.name : (this.mappings.createDefaultSuite ? 'Default Suite' : 'N/A');
            },

            startImport() {
                this.isImporting = true;
                // The actual form submission is handled by the browser via the @submit on the form element
                // You might want to show a more persistent loading indicator here if the backend takes time
            }
        };
    }
</script>
@endpush
