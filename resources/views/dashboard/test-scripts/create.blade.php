{{-- resources/views/dashboard/test-scripts/create.blade.php --}}
@extends('layouts.dashboard')

@section('title', "New Test Script - {$testCase->title}")

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
        <a href="{{ route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id]) }}" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">Test Scripts</a>
    </li>
    <li class="flex items-center">
        <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400 mx-1"></i>
        <span class="text-zinc-700 dark:text-zinc-300">Create</span>
    </li>
@endsection

@section('content')
    <x-entity-form
        title="Create Test Script"
        description="Create an automated test script for {{ $testCase->title }}"
        :backRoute="route('dashboard.projects.test-cases.scripts.index', [$project->id, $testCase->id])"
        backLabel="Back to Test Scripts"
        :submitAction="route('dashboard.projects.test-cases.scripts.store', [$project->id, $testCase->id])"
        submitMethod="POST"
        submitButtonText="Create Test Script"
        entityName="test-script"
        :hasAI="true"
        :aiEndpoint="route('api.ai.generate', 'test-script')"
        :aiConfiguration="[
            'contextFields' => [
                [
                    'name' => 'framework_type',
                    'label' => 'Framework Type',
                    'type' => 'select',
                    'options' => [
                        ['value' => 'selenium-python', 'label' => 'Selenium Python'],
                        ['value' => 'cypress', 'label' => 'Cypress'],
                        ['value' => 'other', 'label' => 'Other']
                    ],
                    'required' => true,
                    'default' => 'selenium-python',
                ],
            ],
            'templates' => [
                [
                    'id' => 'selenium-basic',
                    'name' => 'Selenium Basic Test',
                    'icon' => 'chrome',
                    'iconClass' => 'text-blue-500',
                    'promptTemplate' => 'Generate a Selenium Python test script for a login page that verifies successful login with valid credentials and failed login with invalid credentials.',
                ],
                [
                    'id' => 'cypress-basic',
                    'name' => 'Cypress Basic Test',
                    'icon' => 'browser',
                    'iconClass' => 'text-green-500',
                    'promptTemplate' => 'Generate a Cypress test script for testing a form submission that includes validation and successful submission.',
                ],
                [
                    'id' => 'screenshot',
                    'name' => 'Screenshot Capture',
                    'icon' => 'camera',
                    'iconClass' => 'text-purple-500',
                    'promptTemplate' => 'Generate a test script that captures screenshots at key points during the test execution for visual verification.',
                ],
                [
                    'id' => 'data-driven',
                    'name' => 'Data-Driven Test',
                    'icon' => 'database',
                    'iconClass' => 'text-amber-500',
                    'promptTemplate' => 'Generate a data-driven test script that reads test data from an external source and runs the same test with multiple data sets.',
                ]
            ]
        ]"
    >
        <x-slot:form>
            <div x-data="testScriptForm({
                testCaseId: '{{ $testCase->id }}',
                testCaseTitle: '{{ addslashes($testCase->title) }}',
                testCaseSteps: {{ json_encode($testCase->steps) }},
                testCaseExpectedResults: '{{ addslashes($testCase->expected_results) }}',
                frameworks: {
                    'selenium-python': 'Selenium Python',
                    'cypress': 'Cypress',
                    'other': 'Other'
                }
            })">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Script Name -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Script Name <span class="text-red-500">*</span>
                        </label>
                        <input x-model="formData.name" type="text" name="name" id="name" class="w-full px-4 py-2.5 rounded-lg" required>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            Give your script a descriptive name
                        </p>
                    </div>

                    <!-- Framework Type -->
                    <div>
                        <label for="framework_type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Framework Type <span class="text-red-500">*</span>
                        </label>
                        <select x-model="formData.framework_type" name="framework_type" id="framework_type" class="w-full px-4 py-2.5 rounded-lg" required>
                            <option value="">Select Framework</option>
                            <option value="selenium-python">Selenium Python</option>
                            <option value="cypress">Cypress</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Script Content -->
                    <div class="md:col-span-2">
                        <label for="script_content" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Script Content <span class="text-red-500">*</span>
                        </label>
                        <textarea x-model="formData.script_content" name="script_content" id="script_content" rows="12"
                            class="w-full px-4 py-2.5 rounded-lg font-mono text-sm" required></textarea>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            Enter the code for your test script
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
        Alpine.data('testScriptForm', (config) => ({
            testCaseId: config.testCaseId,
            testCaseTitle: config.testCaseTitle,
            testCaseSteps: config.testCaseSteps,
            testCaseExpectedResults: config.testCaseExpectedResults,
            frameworks: config.frameworks,

            formData: {
                name: '',
                framework_type: 'selenium-python',
                script_content: ''
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
                this.formData.name = this.$parent.generatedResult.name || `Test Script for ${this.testCaseTitle}`;
                this.formData.framework_type = this.$parent.aiContext.framework_type || 'selenium-python';
                this.formData.script_content = this.$parent.generatedResult.content || '';

                // Notify user
                this.$parent.showNotificationMessage('success', 'Applied to Form',
                    'Generated script has been applied to the form. You can now edit and save it.');
            }
        }));
    });
</script>
@endpush
