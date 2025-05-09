{{-- resources/views/dashboard/test-data/create.blade.php --}}
@extends('layouts.dashboard')

@section('title', "Add Test Data - {$testCase->title}")

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
        <span class="text-zinc-700 dark:text-zinc-300">Create</span>
    </li>
@endsection

@section('content')
    <x-entity-form
        title="Create Test Data"
        description="Create test data for {{ $testCase->title }}"
        :backRoute="route('dashboard.projects.test-cases.data.index', [$project->id, $testCase->id])"
        backLabel="Back to Test Data"
        :submitAction="route('dashboard.projects.test-cases.data.store', [$project->id, $testCase->id])"
        submitMethod="POST"
        submitButtonText="Create Test Data"
        entityName="test-data"
        :hasAI="true"
        :aiEndpoint="route('api.ai.generate', 'test-data')"
        :aiConfiguration="[
            'contextFields' => [
                [
                    'name' => 'format',
                    'label' => 'Data Format',
                    'type' => 'select',
                    'options' => [
                        ['value' => 'json', 'label' => 'JSON'],
                        ['value' => 'csv', 'label' => 'CSV'],
                        ['value' => 'xml', 'label' => 'XML'],
                        ['value' => 'plain', 'label' => 'Plain Text'],
                    ],
                    'required' => true,
                    'default' => 'json',
                ],
            ],
            'templates' => [
                [
                    'id' => 'user-data',
                    'name' => 'User Data',
                    'icon' => 'users',
                    'iconClass' => 'text-indigo-500',
                    'promptTemplate' => 'Generate test data for user profiles with name, email, age, and subscription status fields.',
                ],
                [
                    'id' => 'product-data',
                    'name' => 'Product Data',
                    'icon' => 'shopping-cart',
                    'iconClass' => 'text-green-500',
                    'promptTemplate' => 'Generate product test data with id, name, price, category, and inventory fields.',
                ],
                [
                    'id' => 'auth-tokens',
                    'name' => 'Auth Tokens',
                    'icon' => 'key',
                    'iconClass' => 'text-amber-500',
                    'promptTemplate' => 'Generate authentication test data with user ids, tokens, refresh tokens, and expiration timestamps.',
                ],
                [
                    'id' => 'api-responses',
                    'name' => 'API Responses',
                    'icon' => 'code',
                    'iconClass' => 'text-purple-500',
                    'promptTemplate' => 'Generate API response test data for both success and error cases, with status codes and messages.',
                ]
            ]
        ]"
    >
        <x-slot:form>
            <div x-data="testDataForm({
                testCaseId: '{{ $testCase->id }}',
                testCaseTitle: '{{ addslashes($testCase->title) }}',
                testCaseSteps: {{ json_encode($testCase->steps) }},
                testCaseExpectedResults: '{{ addslashes($testCase->expected_results) }}'
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
                                <option value="{{ $format }}">{{ strtoupper($format) }}</option>
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
                            Enter the test data content in the selected format
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
            testCaseTitle: config.testCaseTitle,
            testCaseSteps: config.testCaseSteps,
            testCaseExpectedResults: config.testCaseExpectedResults,

            formData: {
                name: '',
                format: 'json',
                usage_context: 'General testing',
                is_sensitive: false,
                content: ''
            },

            init() {
                // Initialize form with default values or values from entityForm parent if available
                if (this.$parent.oldData && this.$parent.oldData.name) {
                    this.formData = this.$parent.oldData;
                }
            },

            applyGeneratedContent() {
                if (!this.$parent.generatedResult) return;

                // Map generated result to form fields
                this.formData.name = this.$parent.generatedResult.name || `Test Data for ${this.testCaseTitle}`;
                this.formData.format = this.$parent.aiContext.format || 'json';
                this.formData.content = this.$parent.generatedResult.content || '';

                // Notify user
                this.$parent.showNotificationMessage('success', 'Applied to Form',
                    'Generated test data has been applied to the form. You can now edit and save it.');
            }
        }));
    });
</script>
@endpush
