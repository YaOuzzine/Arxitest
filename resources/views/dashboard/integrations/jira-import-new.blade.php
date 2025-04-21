{{-- resources/views/dashboard/integrations/jira-import-new.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Import Jira Project as New')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.integrations.index') }}" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">Integrations</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Import New Jira Project</span>
    </li>
@endsection

@section('content')
<div class="h-full" x-data="{
    selectedProject: null,
    projectSearch: '',
    isImporting: false,
    importEpics: true,
    importStories: true,
    allJiraProjects: {{ json_encode($jiraProjects ?? []) }},
    errorMessage: '{{ session('error') }}'
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Import Jira Project as New</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Select a Jira project to create a corresponding project in Arxitest for team '{{ $teamName }}'.
            </p>
        </div>
        <div>
            <a href="{{ route('dashboard.integrations.index') }}" class="btn-secondary">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Cancel
            </a>
        </div>
    </div>

    {{-- Display Errors --}}
    <div x-show="errorMessage" x-cloak class="mb-6 p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 rounded-lg">
        <div class="flex items-center">
            <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i>
            <span x-text="errorMessage"></span>
        </div>
    </div>
    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 rounded-lg">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('integrations.jira.import-new.project') }}" @submit="isImporting = true">
        @csrf
        <input type="hidden" name="jira_project_key" :value="selectedProject?.key">
        <input type="hidden" name="jira_project_name" :value="selectedProject?.name">

        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-zinc-200/50 dark:border-zinc-700/50 overflow-hidden p-6 space-y-6">

            {{-- Project Selection --}}
            <div>
                <label class="block text-lg font-semibold mb-3 text-zinc-900 dark:text-white">1. Select Jira Project <span class="text-red-500">*</span></label>
                 <div class="mb-4">
                    <label for="project-search-new" class="sr-only">Search Projects</label>
                    <div class="relative">
                        <input id="project-search-new" type="search" x-model="projectSearch" placeholder="Search projects by name or key..."
                            class="w-full px-4 py-3 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent pl-10">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="w-5 h-5 text-zinc-400"></i>
                        </div>
                    </div>
                </div>
                <div class="max-h-[40vh] overflow-y-auto pr-2 space-y-3 custom-scrollbar">
                    {{-- Project List --}}
                    <template x-if="filteredProjects.length === 0">
                        <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">No projects match your search.</div>
                    </template>
                    <template x-for="project in filteredProjects" :key="project.id">
                        <div @click="selectedProject = project"
                             class="p-4 border rounded-lg cursor-pointer transition-all duration-200 flex items-start gap-4 hover:shadow-md"
                             :class="{
                                 'border-indigo-500 dark:border-indigo-400 ring-2 ring-indigo-300 dark:ring-indigo-600 bg-indigo-50 dark:bg-indigo-900/30': selectedProject?.id === project.id,
                                 'border-zinc-200 dark:border-zinc-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:bg-zinc-50 dark:hover:bg-zinc-700/50': selectedProject?.id !== project.id
                             }">
                             {{-- Icon --}}
                             <div class="shrink-0 w-10 h-10 rounded-lg flex items-center justify-center overflow-hidden bg-zinc-100 dark:bg-zinc-700 ring-1 ring-zinc-200 dark:ring-zinc-600">
                                 <template x-if="project.avatarUrls && project.avatarUrls['32x32']"><img :src="project.avatarUrls['32x32']" :alt="project.name" class="w-full h-full object-cover"></template>
                                 <template x-if="!project.avatarUrls || !project.avatarUrls['32x32']"><span class="text-zinc-600 dark:text-zinc-300 font-semibold text-sm" x-text="project.key ? project.key.substring(0, 2).toUpperCase() : '?'"></span></template>
                             </div>
                             {{-- Details --}}
                             <div class="flex-1 min-w-0">
                                 <h4 class="font-medium text-zinc-900 dark:text-white truncate" x-text="project.name"></h4>
                                 <p class="text-xs text-zinc-500 dark:text-zinc-400">Key: <span class="font-mono" x-text="project.key"></span></p>
                             </div>
                        </div>
                    </template>
                </div>
                 @error('jira_project_key') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Import Options --}}
            <div>
                <label class="block text-lg font-semibold mb-3 text-zinc-900 dark:text-white">2. Select Content to Import</label>
                <div class="space-y-3">
                     <label class="flex items-center p-4 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg border border-zinc-200 dark:border-zinc-600/50 cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-500 transition-colors">
                         <input type="checkbox" name="import_epics" value="1" x-model="importEpics" class="form-checkbox h-5 w-5 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500 dark:bg-zinc-600 dark:checked:bg-indigo-500">
                         <span class="ml-3 text-sm font-medium text-zinc-800 dark:text-zinc-200">Import Epics <span class="text-xs text-zinc-500 dark:text-zinc-400">(as Test Suites)</span></span>
                     </label>
                     <label class="flex items-center p-4 bg-zinc-50 dark:bg-zinc-700/30 rounded-lg border border-zinc-200 dark:border-zinc-600/50 cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-500 transition-colors">
                         <input type="checkbox" name="import_stories" value="1" x-model="importStories" class="form-checkbox h-5 w-5 rounded border-zinc-300 dark:border-zinc-600 text-indigo-600 focus:ring-indigo-500 dark:bg-zinc-600 dark:checked:bg-indigo-500">
                         <span class="ml-3 text-sm font-medium text-zinc-800 dark:text-zinc-200">Import Stories/Tasks/Bugs <span class="text-xs text-zinc-500 dark:text-zinc-400">(as Test Cases)</span></span>
                     </label>
                </div>
                 <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">Select which Jira issue types you want to bring into Arxitest.</p>
            </div>

             {{-- Submit Button --}}
            <div class="border-t border-zinc-200/50 dark:border-zinc-700/50 pt-6 flex justify-end">
                 <button type="submit" class="btn-primary flex items-center" :disabled="!selectedProject || isImporting || (!importEpics && !importStories)">
                    <i x-show="isImporting" data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i>
                    <i x-show="!isImporting" data-lucide="download-cloud" class="w-4 h-4 mr-2"></i>
                    <span x-show="!isImporting">Import Project</span>
                    <span x-show="isImporting">Importing... Please Wait</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .btn-primary { @apply inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed; }
    .btn-secondary { @apply inline-flex items-center px-4 py-2 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 font-medium rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(161, 161, 170, 0.3); border-radius: 3px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(113, 113, 122, 0.4); }
    .form-checkbox { @apply focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-zinc-300 dark:border-zinc-600 rounded dark:bg-zinc-600 dark:checked:bg-indigo-500 dark:focus:ring-offset-zinc-800; }
    [x-cloak] { display: none !important; }
</style>
@endpush

@push('scripts')
<script>
    function jiraImport(config) {
        return {
            selectedProject: null,
            projectSearch: '',
            isImporting: false,
            importEpics: true, // Default to true
            importStories: true, // Default to true
            allJiraProjects: config.jiraProjectsData || [],
            errorMessage: config.errorMessage || '',

            init() {
                this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
            },

            get filteredProjects() {
                if (!this.projectSearch) return this.allJiraProjects;
                const search = this.projectSearch.toLowerCase();
                return this.allJiraProjects.filter(project =>
                    project.name.toLowerCase().includes(search) ||
                    (project.key && project.key.toLowerCase().includes(search))
                );
            },

            selectProject(project) {
                this.selectedProject = project;
                this.errorMessage = ''; // Clear errors on selection
            }
        };
    }
</script>
@endpush
