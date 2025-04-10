@extends('layouts.dashboard')

@section('title', 'Projects')

@section('breadcrumbs')
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Projects</span>
    </li>
@endsection

@section('content')
    <div class="h-full" x-data="{
        view: 'grid',
        searchQuery: '',
        statusFilter: 'all',
        sortBy: 'name',
        sortDir: 'asc',
        projects: [{
                id: 'PRJ-001',
                name: 'User Management',
                description: 'Tests for user registration, authentication, and profile management features.',
                test_cases: 18,
                test_suites: 3,
                pass_rate: 92,
                status: 'active',
                last_updated: '2 hours ago',
                icon: 'users',
                color: 'violet'
            },
            {
                id: 'PRJ-002',
                name: 'Payment Processing',
                description: 'Tests for payment gateway integration, transaction processing, and invoice generation.',
                test_cases: 24,
                test_suites: 5,
                pass_rate: 85,
                status: 'active',
                last_updated: 'Yesterday',
                icon: 'credit-card',
                color: 'blue'
            },
            {
                id: 'PRJ-003',
                name: 'Notification System',
                description: 'Tests for email and SMS notification services, template rendering, and delivery confirmation.',
                test_cases: 12,
                test_suites: 2,
                pass_rate: 100,
                status: 'active',
                last_updated: '3 days ago',
                icon: 'bell',
                color: 'green'
            },
            {
                id: 'PRJ-004',
                name: 'Product Catalog',
                description: 'Tests for product listing, categorization, search, and filtering functionality.',
                test_cases: 30,
                test_suites: 4,
                pass_rate: 78,
                status: 'active',
                last_updated: '1 week ago',
                icon: 'package',
                color: 'amber'
            },
            {
                id: 'PRJ-005',
                name: 'Analytics Dashboard',
                description: 'Tests for data visualization, report generation, and export functionality.',
                test_cases: 15,
                test_suites: 3,
                pass_rate: 88,
                status: 'draft',
                last_updated: '2 weeks ago',
                icon: 'bar-chart-2',
                color: 'indigo'
            },
            {
                id: 'PRJ-006',
                name: 'Admin Portal',
                description: 'Tests for admin user management, permissions, and administrative functions.',
                test_cases: 22,
                test_suites: 3,
                pass_rate: 95,
                status: 'inactive',
                last_updated: '1 month ago',
                icon: 'shield',
                color: 'rose'
            }
        ],

        get filteredProjects() {
            return this.projects
                .filter(project => {
                    const matchesSearch = project.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                        project.description.toLowerCase().includes(this.searchQuery.toLowerCase());
                    const matchesStatus = this.statusFilter === 'all' || project.status === this.statusFilter;
                    return matchesSearch && matchesStatus;
                })
                .sort((a, b) => {
                    if (this.sortBy === 'name') {
                        return this.sortDir === 'asc' ?
                            a.name.localeCompare(b.name) :
                            b.name.localeCompare(a.name);
                    } else if (this.sortBy === 'test_cases') {
                        return this.sortDir === 'asc' ?
                            a.test_cases - b.test_cases :
                            b.test_cases - a.test_cases;
                    } else if (this.sortBy === 'pass_rate') {
                        return this.sortDir === 'asc' ?
                            a.pass_rate - b.pass_rate :
                            b.pass_rate - a.pass_rate;
                    } else if (this.sortBy === 'last_updated') {
                        // Simple string comparison for the demo
                        return this.sortDir === 'asc' ?
                            a.last_updated.localeCompare(b.last_updated) :
                            b.last_updated.localeCompare(a.last_updated);
                    }
                    return 0;
                });
        }
    }">
        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Projects</h1>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        Manage and organize your test projects
                    </p>
                </div>
                <div class="mt-4 md:mt-0">
                    <button
                        class="btn-primary inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                        <i data-lucide="plus" class="mr-2 -ml-1 w-4 h-4"></i>
                        New Project
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-4 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex flex-col sm:flex-row sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <div class="relative">
                        <input type="text" x-model="searchQuery" placeholder="Search projects..."
                            class="pl-10 pr-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:focus:ring-zinc-400 w-full sm:w-64">
                        <div class="absolute left-3 top-2.5">
                            <i data-lucide="search" class="w-4 h-4 text-zinc-400 dark:text-zinc-500"></i>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <label class="text-sm text-zinc-600 dark:text-zinc-400">Status:</label>
                        <select x-model="statusFilter"
                            class="border border-zinc-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:focus:ring-zinc-400">
                            <option value="all">All</option>
                            <option value="active">Active</option>
                            <option value="draft">Draft</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <button @click="view = 'grid'" class="p-1.5 rounded-md transition-colors duration-200"
                        :class="view === 'grid' ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-white' :
                            'text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white'">
                        <i data-lucide="grid" class="w-5 h-5"></i>
                    </button>
                    <button @click="view = 'list'" class="p-1.5 rounded-md transition-colors duration-200"
                        :class="view === 'list' ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-white' :
                            'text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white'">
                        <i data-lucide="list" class="w-5 h-5"></i>
                    </button>

                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="p-1.5 rounded-md text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors duration-200">
                            <i data-lucide="sort" class="w-5 h-5"></i>
                        </button>

                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-48 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-md shadow-lg z-10"
                            x-cloak>
                            <div class="p-2">
                                <div class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase mb-2 px-3">Sort
                                    By</div>
                                <button @click="sortBy = 'name'; sortDir = sortDir === 'asc' ? 'desc' : 'asc'; open = false"
                                    class="flex items-center justify-between w-full px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md"
                                    :class="sortBy === 'name' ? 'text-zinc-900 dark:text-white font-medium' :
                                        'text-zinc-600 dark:text-zinc-400'">
                                    <span>Name</span>
                                    <template x-if="sortBy === 'name'">
                                        <i :data-lucide="sortDir === 'asc' ? 'arrow-up' : 'arrow-down'"
                                            class="w-4 h-4"></i>
                                    </template>
                                </button>
                                <button
                                    @click="sortBy = 'test_cases'; sortDir = sortDir === 'asc' ? 'desc' : 'asc'; open = false"
                                    class="flex items-center justify-between w-full px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md"
                                    :class="sortBy === 'test_cases' ? 'text-zinc-900 dark:text-white font-medium' :
                                        'text-zinc-600 dark:text-zinc-400'">
                                    <span>Test Cases</span>
                                    <template x-if="sortBy === 'test_cases'">
                                        <i :data-lucide="sortDir === 'asc' ? 'arrow-up' : 'arrow-down'"
                                            class="w-4 h-4"></i>
                                    </template>
                                </button>
                                <button
                                    @click="sortBy = 'pass_rate'; sortDir = sortDir === 'asc' ? 'desc' : 'asc'; open = false"
                                    class="flex items-center justify-between w-full px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md"
                                    :class="sortBy === 'pass_rate' ? 'text-zinc-900 dark:text-white font-medium' :
                                        'text-zinc-600 dark:text-zinc-400'">
                                    <span>Pass Rate</span>
                                    <template x-if="sortBy === 'pass_rate'">
                                        <i :data-lucide="sortDir === 'asc' ? 'arrow-up' : 'arrow-down'"
                                            class="w-4 h-4"></i>
                                    </template>
                                </button>
                                <button
                                    @click="sortBy = 'last_updated'; sortDir = sortDir === 'asc' ? 'desc' : 'asc'; open = false"
                                    class="flex items-center justify-between w-full px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md"
                                    :class="sortBy === 'last_updated' ? 'text-zinc-900 dark:text-white font-medium' :
                                        'text-zinc-600 dark:text-zinc-400'">
                                    <span>Last Updated</span>
                                    <template x-if="sortBy === 'last_updated'">
                                        <i :data-lucide="sortDir === 'asc' ? 'arrow-up' : 'arrow-down'"
                                            class="w-4 h-4"></i>
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="view === 'grid'" x-transition:enter="transition duration-300 ease-out"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="project in filteredProjects" :key="project.id">
                    <div
                        class="card-hover bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 transition-all duration-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12 rounded-lg flex items-center justify-center"
                                    :class="{
                                        'bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400': project
                                            .color === 'violet',
                                        'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400': project
                                            .color === 'blue',
                                        'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400': project
                                            .color === 'green',
                                        'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400': project
                                            .color === 'amber',
                                        'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400': project
                                            .color === 'indigo',
                                        'bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400': project
                                            .color === 'rose'
                                    }">
                                    <i :data-lucide="project.icon" class="w-6 h-6"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-zinc-900 dark:text-white" x-text="project.name">
                                    </h3>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400" x-text="project.id"></p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': project
                                        .status === 'active',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': project
                                        .status === 'draft',
                                    'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300': project
                                        .status === 'inactive'
                                }"
                                x-text="project.status.charAt(0).toUpperCase() + project.status.slice(1)"></span>
                        </div>

                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 line-clamp-2"
                            x-text="project.description"></p>

                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Test Suites:</span>
                                <span class="text-zinc-900 dark:text-white font-medium"
                                    x-text="project.test_suites"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Test Cases:</span>
                                <span class="text-zinc-900 dark:text-white font-medium"
                                    x-text="project.test_cases"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Pass Rate:</span>
                                <span class="text-zinc-900 dark:text-white font-medium"
                                    x-text="project.pass_rate + '%'"></span>
                            </div>

                            <div class="w-full bg-zinc-200 dark:bg-zinc-600 rounded-full h-2">
                                <div class="h-2 rounded-full"
                                    :class="{
                                        'bg-green-500': project.pass_rate >= 90,
                                        'bg-yellow-500': project.pass_rate >= 75 && project.pass_rate < 90,
                                        'bg-red-500': project.pass_rate < 75
                                    }"
                                    :style="{ width: project.pass_rate + '%' }"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-6 text-xs text-zinc-500 dark:text-zinc-400">
                            <span>Updated <span x-text="project.last_updated"></span></span>

                            <div class="flex space-x-2">
                                <button
                                    class="p-1 hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors duration-200">
                                    <i data-lucide="play" class="w-4 h-4"></i>
                                </button>
                                <button
                                    class="p-1 hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors duration-200">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>
                                <button
                                    class="p-1 hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors duration-200">
                                    <i data-lucide="settings" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <div
                    class="border-2 border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg p-6 flex flex-col items-center justify-center text-center hover:border-zinc-400 dark:hover:border-zinc-500 transition-colors duration-200 cursor-pointer">
                    <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mb-4">
                        <i data-lucide="plus" class="w-8 h-8 text-zinc-500 dark:text-zinc-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">Create New Project</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Add a new test project to your workspace</p>
                </div>
            </div>
        </div>

        <div x-show="view === 'list'" x-transition:enter="transition duration-300 ease-out"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100">
            <div
                class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead>
                            <tr class="bg-zinc-50 dark:bg-zinc-700/50">
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Project
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Test Cases
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Pass Rate
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Last Updated
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            <template x-for="project in filteredProjects" :key="project.id">
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center"
                                                :class="{
                                                    'bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400': project
                                                        .color === 'violet',
                                                    'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400': project
                                                        .color === 'blue',
                                                    'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400': project
                                                        .color === 'green',
                                                    'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400': project
                                                        .color === 'amber',
                                                    'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400': project
                                                        .color === 'indigo',
                                                    'bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400': project
                                                        .color === 'rose'
                                                }">
                                                <i :data-lucide="project.icon" class="w-5 h-5"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-zinc-900 dark:text-white"
                                                    x-text="project.name"></div>
                                                <div
                                                    class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 flex items-center">
                                                    <span x-text="project.id"></span>
                                                    <span class="mx-1">&bull;</span>
                                                    <span x-text="project.test_suites + ' suites'"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white"
                                        x-text="project.test_cases"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="text-sm text-zinc-900 dark:text-white mr-2"
                                                x-text="project.pass_rate + '%'"></span>
                                            <div class="w-16 bg-zinc-200 dark:bg-zinc-600 rounded-full h-1.5">
                                                <div class="h-1.5 rounded-full"
                                                    :class="{
                                                        'bg-green-500': project.pass_rate >= 90,
                                                        'bg-yellow-500': project.pass_rate >= 75 && project.pass_rate <
                                                            90,
                                                        'bg-red-500': project.pass_rate < 75
                                                    }"
                                                    :style="{ width: project.pass_rate + '%' }"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400"
                                        x-text="project.last_updated"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': project
                                                    .status === 'active',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': project
                                                    .status === 'draft',
                                                'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300': project
                                                    .status === 'inactive'
                                            }"
                                            x-text="project.status.charAt(0).toUpperCase() + project.status.slice(1)"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-3">
                                            <button
                                                class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                                                <i data-lucide="play" class="w-4 h-4"></i>
                                            </button>
                                                <button
                                                    class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                                </button>
                                            <button
                                                class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                                                <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div x-show="filteredProjects.length === 0"
            class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-12 text-center">
            <div class="mx-auto w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center mb-4">
                <i data-lucide="folder" class="w-8 h-8 text-zinc-400 dark:text-zinc-500"></i>
            </div>
            <h3 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No projects found</h3>
            <p class="text-zinc-600 dark:text-zinc-400 mb-6">
                <span x-show="searchQuery">No projects match your search criteria "<span
                        x-text="searchQuery"></span>".</span>
                <span x-show="!searchQuery && statusFilter !== 'all'">No <span x-text="statusFilter"></span> projects
                    found.</span>
                <span x-show="!searchQuery && statusFilter === 'all'">You haven't created any projects yet.</span>
            </p>
            <button
                class="btn-primary inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-zinc-800 hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                <i data-lucide="plus" class="mr-2 -ml-1 w-4 h-4"></i>
                <span x-show="searchQuery || statusFilter !== 'all'">Create New Project</span>
                <span x-show="!searchQuery && statusFilter === 'all'">Get Started</span>
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add animations to the cards
            const cards = document.querySelectorAll('.card-hover');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.classList.add('transform', 'scale-105', 'shadow-md');
                });

                card.addEventListener('mouseleave', function() {
                    this.classList.remove('transform', 'scale-105', 'shadow-md');
                });
            });

            // Make sure icons are initialized after Alpine refreshes the DOM
            document.addEventListener('alpine:initialized', () => {
                lucide.createIcons();
            });
        });
    </script>
@endpush
