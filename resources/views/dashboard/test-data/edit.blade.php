{{-- resources/views/dashboard/test-data/edit.blade.php --}}
@extends('layouts.dashboard')

@section('title', "Edit {$testData->name}")

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
        <a href="{{ route('dashboard.projects.test-cases.data.index', [$project->id, $testCase->id]) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">Test Data</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <a href="{{ route('dashboard.projects.test-cases.data.show', [$project->id, $testCase->id, $testData->id]) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">{{ $testData->name }}</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Edit</span>
    </li>
@endsection

@section('content')
    <x-entity-form
        title="Edit Test Data"
        description="Update test data for {{ $testCase->title }}"
        :backRoute="route('dashboard.projects.test-cases.data.show', [$project->id, $testCase->id, $testData->id])"
        backLabel="Back to Test Data"
        :submitAction="route('dashboard.projects.test-cases.data.update', [$project->id, $testCase->id, $testData->id])"
        submitMethod="PUT"
        submitButtonText="Update Test Data"
        entityName="test-data"
        :isEdit="true"
        :oldData="[
            'name' => $testData->name,
            'format' => $testData->format,
            'content' => $testData->content,
            'usage_context' => $usageContext ?? 'General purpose',
            'is_sensitive' => $testData->is_sensitive
        ]"
        :dangerAction="route('dashboard.projects.test-cases.data.detach', [$project->id, $testCase->id, $testData->id])"
        dangerMethod="DELETE"
        showDangerZone="true"
        dangerText="Remove from Test Case"
        dangerConfirmText="Are you sure you want to remove this test data from the test case?"
    >
        <x-slot:form>
            <div x-data="testDataForm({
                testCaseId: '{{ $testCase->id }}',
                testData: {{ json_encode([
                    'name' => $testData->name,
                    'format' => $testData->format,
                    'content' => $testData->content,
                    'usage_context' => $usageContext ?? 'General purpose',
                    'is_sensitive' => $testData->is_sensitive
                ]) }}
            })">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input x-model="formData.name" type="text" name="name" id="name" class="w-full px-4 py-2.5 rounded-lg" required>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            Give your test data a descriptive name
                        </p>
                    </div>

                    <!-- Format -->
                    <div>
                        <label for="format" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Format <span class="text-red-500">*</span>
                        </label>
                        <select x-model="formData.format" name="format" id="format" class="w-full px-4 py-2.5 rounded-lg" required>
                            <option value="">Select Format</option>
                            @foreach($formats as $format)
                                <option value="{{ $format }}" {{ $testData->format == $format ? 'selected' : '' }}>{{ strtoupper($format) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Usage Context -->
                    <div>
                        <label for="usage_context" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Usage Context <span class="text-red-500">*</span>
                        </label>
                        <input x-model="formData.usage_context" type="text" name="usage_context" id="usage_context" class="w-full px-4 py-2.5 rounded-lg" required>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            Describe how this data will be used in testing
                        </p>
                    </div>

                    <!-- Sensitive Data Toggle -->
                    <div class="md:col-span-2">
                        <div class="flex items-center">
                            <input x-model="formData.is_sensitive" type="checkbox" id="is_sensitive" name="is_sensitive" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-zinc-300 rounded">
                            <label for="is_sensitive" class="ml-2 block text-sm text-zinc-700 dark:text-zinc-300">
                                Mark as sensitive data (contains passwords, tokens, PII, etc.)
                            </label>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="md:col-span-2">
                        <label for="content" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Content <span class="text-red-500">*</span>
                        </label>
                        <textarea x-model="formData.content" name="content" id="content" rows="12"
                            class="w-full px-4 py-2.5 rounded-lg font-mono text-sm" required></textarea>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            Edit the test data content
                        </p>
                    </div>
                </div>
            </div>
        </x-slot:form>
    </x-entity-form>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('testDataForm', (config) => ({
            testCaseId: config.testCaseId,
            formData: {
                name: config.testData.name || '',
                format: config.testData.format || 'json',
                usage_context: config.testData.usage_context || 'General testing',
                is_sensitive: config.testData.is_sensitive || false,
                content: config.testData.content || ''
            },

            init() {
                // Initialize form with data from props or parent entity form
                if (this.$parent.oldData && this.$parent.oldData.name) {
                    this.formData = this.$parent.oldData;
                }
            }
        }));
    });
</script>
@endpush
