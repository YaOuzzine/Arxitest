<!-- resources/views/dashboard/test-cases/partials/tabs/data.blade.php -->
@php
    $dataFormatLanguages = [
        'json' => 'json',
        'csv' => 'csv',
        'xml' => 'xml',
        'plain' => 'plaintext',
        'other' => 'markup',
    ];
@endphp

<div x-show="activeTab === 'testdata'" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-data="{
        search: '',
        format: '',
        sensitive: '',
        editData: null,
        copySuccess: false,
        deleteConfirm: null
    }">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Test Data</h3>

        <!-- Search and Filters -->
        <div class="flex flex-wrap gap-4">
            <!-- Search Input -->
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Search test data..."
                    class="pl-10 pr-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
            </div>

            <!-- Format Filter -->
            <select x-model="format" class="border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Formats</option>
                <option value="json">JSON</option>
                <option value="csv">CSV</option>
                <option value="xml">XML</option>
                <option value="plain">Plain Text</option>
                <option value="other">Other</option>
            </select>

            <!-- Sensitive Filter -->
            <select x-model="sensitive" class="border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Data</option>
                <option value="1">Sensitive Only</option>
                <option value="0">Non-sensitive Only</option>
            </select>

            <!-- Add Button -->
            <button @click="openDataModal()" class="btn-primary">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Create Test Data
            </button>
        </div>
    </div>

    <!-- Data List -->
    <div class="space-y-4">
    <template x-for="data in filterData()" :key="data.id">
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200">
            <div class="flex flex-col md:flex-row md:items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-700/30 gap-3">
                <div class="flex-1">
                    <h4 class="text-md font-medium text-zinc-900 dark:text-white mb-1">
                        <span x-text="data.name"></span>
                    </h4>
                    <div class="flex flex-wrap items-center text-sm text-zinc-500 dark:text-zinc-400 gap-x-3 gap-y-1">
                        <span class="px-2 py-0.5 text-xs font-medium rounded-md bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800/30">
                            <span x-text="data.format.toUpperCase()"></span>
                        </span>
                        <span x-show="data.is_sensitive"
                            class="px-2 py-0.5 text-xs font-medium rounded-md bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800/30">
                            Sensitive
                        </span>
                        <span class="flex items-center">
                            <i data-lucide="clock" class="w-3.5 h-3.5 mr-1"></i>
                            <span x-text="formatDate(data.created_at)"></span>
                        </span>
                        <span x-show="data.metadata?.created_through === 'ai'"
                            class="px-2 py-0.5 text-xs font-medium rounded-md bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/30">
                            AI Generated
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button @click="editData = data.id; openEditDataModal()"
                        class="px-3 py-1.5 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded">
                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                    </button>
                    <button @click="toggleData(data.id)"
                        class="px-3 py-1.5 text-sm text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-900/20 rounded">
                        <span x-show="expandedData !== data.id">View Data</span>
                        <span x-show="expandedData === data.id">Hide Data</span>
                    </button>
                    <button @click="deleteConfirm = data.id"
                        class="px-3 py-1.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
            <div x-show="expandedData === data.id" x-collapse>
                <div class="relative p-4 bg-zinc-50 dark:bg-zinc-900">
                    <button @click="copyDataToClipboard(data.content, data.id)"
                        class="absolute top-2 right-2 px-2 py-1 text-xs text-zinc-500 dark:text-zinc-400 bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 rounded transition-colors">
                        <span x-show="copySuccess !== data.id">Copy</span>
                        <span x-show="copySuccess === data.id" class="text-green-600">Copied!</span>
                    </button>
                    <pre :class="'language-' + getLanguage(data.format)"
                        class="max-h-96 overflow-y-auto !m-0 !p-0 !bg-transparent">
                        <code x-text="data.content"></code>
                    </pre>
                </div>
            </div>
        </div>
    </template>

    <!-- Empty State -->
    <div x-show="filterData().length === 0" class="bg-zinc-50 dark:bg-zinc-700/30 border border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg p-8 text-center">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400 mb-3">
            <i data-lucide="database" class="w-6 h-6"></i>
        </div>
        <h4 class="text-lg font-medium text-zinc-900 dark:text-white mb-2">No Test Data Yet</h4>
        <p class="text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-4">
            Test data provides input values for this test case. Add data manually or generate with AI assistance.
        </p>
        <div class="flex justify-center">
            <button @click="openDataModal()" class="btn-primary">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Create Test Data
            </button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div x-show="deleteConfirm !== null" x-cloak x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-zinc-900/70 dark:bg-zinc-900/80 backdrop-blur-sm" @click="deleteConfirm = null"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white dark:bg-zinc-800 rounded-xl shadow-xl border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Remove Test Data</h3>
                <p class="text-zinc-600 dark:text-zinc-400 mb-6">Are you sure you want to remove this test data from this test case? The data itself will not be deleted.</p>
                <div class="flex justify-end gap-3">
                    <button @click="deleteConfirm = null" class="btn-secondary">Cancel</button>
                    <button @click="confirmDelete(deleteConfirm)" class="btn-danger">Remove</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterData() {
    const data = @json($testData);
    return data.filter(item => {
        const matchesSearch = !this.search ||
            item.name.toLowerCase().includes(this.search.toLowerCase()) ||
            item.content.toLowerCase().includes(this.search.toLowerCase());

        const matchesFormat = !this.format || item.format === this.format;

        const matchesSensitive = this.sensitive === '' || item.is_sensitive.toString() === this.sensitive;

        return matchesSearch && matchesFormat && matchesSensitive;
    });
}

function formatDate(date) {
    return new Date(date).toLocaleDateString();
}

function getLanguage(format) {
    const languages = @json($dataFormatLanguages);
    return languages[format] || 'markup';
}

function copyDataToClipboard(content, dataId) {
    navigator.clipboard.writeText(content).then(() => {
        this.copySuccess = dataId;
        setTimeout(() => this.copySuccess = false, 2000);
    });
}

function confirmDelete(dataId) {
    const data = @json($testData).find(d => d.id === dataId);
    if (!data) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `{{ route('dashboard.projects.test-cases.data.detach', ['project' => '__PROJECT__', 'test_case' => '__TEST_CASE__', 'test_data' => '__DATA__']) }}`
        .replace('__PROJECT__', '{{ $project->id }}')
        .replace('__TEST_CASE__', '{{ $testCase->id }}')
        .replace('__DATA__', dataId);

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';

    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';

    form.appendChild(csrfInput);
    form.appendChild(methodInput);

    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
