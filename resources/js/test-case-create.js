/**
 * Test Case Creation JavaScript
 * Handles all the interactivity for the test case creation page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the page
    initializeTestCaseCreation();

    // Render the Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

/**
 * Initialize all event listeners and page state
 */
function initializeTestCaseCreation() {
    // State variables
    let state = {
        creationMode: 'manual', // 'manual' or 'ai'
        steps: [{ id: 1, text: '' }],
        tags: [],
        tagInput: '',
        priority: 'medium',
        isSubmitting: false,
        aiLoading: false,
        aiPrompt: '',
        aiError: '',
        aiResult: null,
        hasPopulatedFromAI: false
    };

    // Initialize DOM element references
    const elements = {
        // Mode toggle buttons
        manualModeBtn: document.getElementById('manual-mode-btn'),
        aiModeBtn: document.getElementById('ai-mode-btn'),

        // AI section
        aiGenerationSection: document.getElementById('ai-generation-section'),
        aiSparklesIcon: document.querySelector('#ai-generation-section i[data-lucide="sparkles"]'),
        aiSuiteSelect: document.getElementById('ai-suite-select'),
        aiPrompt: document.getElementById('ai-prompt'),
        generateAiBtn: document.getElementById('generate-ai-btn'),
        generateAiBtnText: document.getElementById('generate-ai-btn-text'),
        aiErrorContainer: document.getElementById('ai-error-container'),
        aiErrorMessage: document.getElementById('ai-error-message'),

        // Form section
        testCaseForm: document.getElementById('test-case-form'),
        formSectionTitle: document.getElementById('form-section-title'),
        formSectionDescription: document.getElementById('form-section-description'),

        // Form fields
        suiteIdInput: document.getElementById('suite-id-input'),
        manualSuiteSelectionContainer: document.getElementById('manual-suite-selection-container'),
        suiteSelect: document.getElementById('suite-select'),
        titleInput: document.getElementById('title'),
        descriptionInput: document.getElementById('description'),
        priorityInput: document.getElementById('priority-input'),
        priorityOptions: document.querySelectorAll('.priority-option'),
        expectedResultsInput: document.getElementById('expected_results'),
        statusInput: document.getElementById('status-input'),

        // Steps
        testStepsContainer: document.getElementById('test-steps-container'),
        stepsList: document.getElementById('steps-list'),
        addStepBtn: document.getElementById('add-step-btn'),

        // Tags
        tagsContainer: document.getElementById('tags-container'),
        tagInput: document.getElementById('tag-input'),
        tagInputContainer: document.getElementById('tag-input-container'),
        addTagBtn: document.getElementById('add-tag-btn'),

        // Buttons
        submitBtn: document.getElementById('submit-btn'),
        submitBtnText: document.getElementById('submit-btn-text'),
        cancelBtn: document.getElementById('cancel-btn'),

        // Notification
        createContainer: document.getElementById('test-case-create-container'),
        notificationContainer: document.getElementById('notification-container'),
        notificationIcon: document.getElementById('notification-icon'),
        notificationTitle: document.getElementById('notification-title'),
        notificationMessage: document.getElementById('notification-message')
    };
    const projectId = elements.createContainer.dataset.projectId;
    // Set event listeners

    // Mode toggle
    elements.manualModeBtn.addEventListener('click', () => setCreationMode('manual'));
    elements.aiModeBtn.addEventListener('click', () => setCreationMode('ai'));

    // AI generation
    elements.aiSuiteSelect.addEventListener('change', (e) => {
        elements.suiteIdInput.value = e.target.value;
    });

    elements.generateAiBtn.addEventListener('click', generateWithAI);

    // Priority selection
    elements.priorityOptions.forEach(option => {
        option.addEventListener('click', () => {
            setPriority(option.getAttribute('data-value'));
        });
    });

    // Steps
    elements.addStepBtn.addEventListener('click', addStep);

    // Handle remove step button clicks using event delegation
    elements.stepsList.addEventListener('click', (e) => {
        if (e.target.closest('.remove-step-btn')) {
            const stepItem = e.target.closest('.step-item');
            if (stepItem) {
                removeStep(stepItem);
            }
        }
    });

    // Tags
    elements.addTagBtn.addEventListener('click', addTag);
    elements.tagInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            addTag();
        }
    });

    // Form submission
    elements.testCaseForm.addEventListener('submit', (e) => {
        e.preventDefault();
        submitForm();
    });

    elements.cancelBtn.addEventListener('click', () => {
        window.history.back();
    });

    // Initialize the suite selection if a selected suite exists
    if (elements.suiteIdInput.value) {
        if (elements.suiteSelect) {
            elements.suiteSelect.value = elements.suiteIdInput.value;
        }
        if (elements.aiSuiteSelect) {
            elements.aiSuiteSelect.value = elements.suiteIdInput.value;
        }
    }

    /**
     * Set the creation mode (manual or AI)
     */
    function setCreationMode(mode) {
        state.creationMode = mode;

        // Update UI based on mode
        if (mode === 'manual') {
            elements.manualModeBtn.classList.add('bg-white', 'dark:bg-zinc-700', 'text-zinc-900', 'dark:text-white', 'shadow-sm', 'border-b-2', 'border-blue-500');
            elements.manualModeBtn.classList.remove('text-zinc-600', 'dark:text-zinc-400');

            elements.aiModeBtn.classList.remove('bg-white', 'dark:bg-zinc-700', 'text-zinc-900', 'dark:text-white', 'shadow-sm', 'border-b-2', 'border-purple-500');
            elements.aiModeBtn.classList.add('text-zinc-600', 'dark:text-zinc-400');

            elements.aiGenerationSection.classList.add('hidden');

            elements.formSectionTitle.textContent = 'Test Case Details';
            elements.formSectionDescription.textContent = 'Define your test case details, including steps and expected results';
        } else {
            elements.aiModeBtn.classList.add('bg-white', 'dark:bg-zinc-700', 'text-zinc-900', 'dark:text-white', 'shadow-sm', 'border-b-2', 'border-purple-500');
            elements.aiModeBtn.classList.remove('text-zinc-600', 'dark:text-zinc-400');

            elements.manualModeBtn.classList.remove('bg-white', 'dark:bg-zinc-700', 'text-zinc-900', 'dark:text-white', 'shadow-sm', 'border-b-2', 'border-blue-500');
            elements.manualModeBtn.classList.add('text-zinc-600', 'dark:text-zinc-400');

            elements.aiGenerationSection.classList.remove('hidden');

            if (state.hasPopulatedFromAI) {
                elements.formSectionTitle.textContent = 'Review & Customize Generated Test Case';
                elements.formSectionDescription.textContent = 'Review the AI-generated test case and make any necessary adjustments';
            } else {
                elements.formSectionTitle.textContent = 'AI Generated Test Case';
                elements.formSectionDescription.textContent = 'The AI will generate a complete test case based on your description';
            }
        }
    }

    /**
     * Set the priority for the test case
     */
    function setPriority(value) {
        state.priority = value;
        elements.priorityInput.value = value;

        // Update UI
        elements.priorityOptions.forEach(option => {
            const optionValue = option.getAttribute('data-value');
            const radioElement = option.querySelector('.priority-radio');

            if (optionValue === value) {
                option.classList.add('ring-2', 'ring-zinc-500', 'dark:ring-zinc-400', 'bg-zinc-100/50', 'dark:bg-zinc-700/30');

                if (radioElement) {
                    radioElement.classList.add('bg-zinc-800', 'dark:bg-zinc-200');
                    radioElement.classList.remove('bg-zinc-300', 'dark:bg-zinc-600');

                    // Add the inner dot
                    if (!radioElement.querySelector('div')) {
                        const innerDot = document.createElement('div');
                        innerDot.className = 'absolute inset-0 flex items-center justify-center transform';
                        innerDot.innerHTML = '<div class="w-2 h-2 rounded-full bg-white dark:bg-zinc-800"></div>';
                        radioElement.appendChild(innerDot);
                    }
                }
            } else {
                option.classList.remove('ring-2', 'ring-zinc-500', 'dark:ring-zinc-400', 'bg-zinc-100/50', 'dark:bg-zinc-700/30');

                if (radioElement) {
                    radioElement.classList.remove('bg-zinc-800', 'dark:bg-zinc-200');
                    radioElement.classList.add('bg-zinc-300', 'dark:bg-zinc-600');

                    // Remove the inner dot
                    const innerDot = radioElement.querySelector('div');
                    if (innerDot) {
                        radioElement.removeChild(innerDot);
                    }
                }
            }
        });
    }

    /**
     * Add a new test step
     */
    function addStep() {
        const stepCount = elements.stepsList.querySelectorAll('.step-item').length;
        const newStepId = stepCount + 1;

        // Create new step item
        const stepItem = document.createElement('div');
        stepItem.className = 'step-item flex items-start space-x-3';
        stepItem.innerHTML = `
            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-semibold text-sm">
                <span class="step-number">${newStepId}</span>
            </div>
            <div class="flex-1 relative">
                <input name="steps[${stepCount}]" type="text" class="step-input w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-lg shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 p-3 text-zinc-700 dark:text-zinc-200 focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300" placeholder="Describe the step to perform">
            </div>
            <button type="button" class="remove-step-btn flex-shrink-0 p-1.5 rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-red-500 dark:hover:text-red-400 transition-colors">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        `;

        elements.stepsList.appendChild(stepItem);

        // Enable all remove buttons if we have more than one step
        if (newStepId > 1) {
            const removeButtons = elements.stepsList.querySelectorAll('.remove-step-btn');
            removeButtons.forEach(btn => btn.removeAttribute('disabled'));
        }

        // Re-render Lucide icons for the new elements
        if (typeof lucide !== 'undefined') {
            lucide.createIcons({
                attrs: {
                    class: ['step-icon']
                },
                elements: [stepItem]
            });
        }
    }

    /**
     * Remove a test step
     */
    function removeStep(stepItem) {
        const steps = elements.stepsList.querySelectorAll('.step-item');

        // Only allow removal if we have more than one step
        if (steps.length <= 1) {
            return;
        }

        // Remove the step
        elements.stepsList.removeChild(stepItem);

        // Renumber the remaining steps
        const remainingSteps = elements.stepsList.querySelectorAll('.step-item');
        remainingSteps.forEach((step, index) => {
            const numberElement = step.querySelector('.step-number');
            const inputElement = step.querySelector('input');

            if (numberElement) {
                numberElement.textContent = index + 1;
            }

            if (inputElement) {
                inputElement.name = `steps[${index}]`;
            }
        });

        // If only one step remains, disable its remove button
        if (remainingSteps.length === 1) {
            const removeButton = remainingSteps[0].querySelector('.remove-step-btn');
            if (removeButton) {
                removeButton.setAttribute('disabled', 'disabled');
            }
        }
    }

    /**
     * Add a tag to the test case
     */
    function addTag() {
        const tagValue = elements.tagInput.value.trim();

        if (!tagValue || state.tags.includes(tagValue)) {
            elements.tagInput.value = '';
            return;
        }

        // Add to state
        state.tags.push(tagValue);

        // Create tag element
        const tagElement = document.createElement('div');
        tagElement.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 space-x-1';
        tagElement.innerHTML = `
            <span>${tagValue}</span>
            <button type="button" class="text-zinc-500 hover:text-red-500 dark:text-zinc-400 dark:hover:text-red-400" data-tag="${tagValue}">
                <i data-lucide="x" class="w-3 h-3"></i>
            </button>
            <input type="hidden" name="tags[]" value="${tagValue}">
        `;

        // Add remove button event listener
        const removeButton = tagElement.querySelector('button');
        removeButton.addEventListener('click', (e) => {
            removeTag(e.currentTarget.getAttribute('data-tag'));
        });

        // Insert the tag before the input container
        elements.tagsContainer.insertBefore(tagElement, elements.tagInputContainer);

        // Clear the input
        elements.tagInput.value = '';

        // Re-render Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons({
                attrs: {
                    class: ['tag-icon']
                },
                elements: [tagElement]
            });
        }
    }

    /**
     * Remove a tag from the test case
     */
    function removeTag(tag) {
        // Remove from state
        state.tags = state.tags.filter(t => t !== tag);

        // Remove from DOM
        const tagElements = elements.tagsContainer.querySelectorAll('div');
        tagElements.forEach(el => {
            const tagSpan = el.querySelector('span');
            if (tagSpan && tagSpan.textContent === tag) {
                elements.tagsContainer.removeChild(el);
            }
        });
    }

    /**
     * Generate a test case using AI
     */
    async function generateWithAI() {
        const suiteId = elements.aiSuiteSelect.value;
        const prompt = elements.aiPrompt.value.trim();

        if (!prompt) {
            showError('Please provide a description of the test scenario');
            return;
        }

        if (!suiteId) {
            showError('Please select a test suite');
            return;
        }

        try {
            // Update UI to loading state
            state.aiLoading = true;
            state.aiError = '';
            elements.generateAiBtn.disabled = true;
            elements.generateAiBtnText.textContent = 'Generating Test Case...';
            if (elements.aiSparklesIcon) {
                elements.aiSparklesIcon.style.display = 'none';
            }

            // Create a new loading icon
            const loadingIcon = document.createElement('i');
            loadingIcon.setAttribute('data-lucide', 'loader');
            loadingIcon.className = 'animate-spin w-5 h-5 mr-2';
            elements.generateAiBtn.insertBefore(loadingIcon, elements.generateAiBtnText);

            if (typeof lucide !== 'undefined') {
                lucide.createIcons({
                    attrs: {
                        class: ['loading-icon']
                    },
                    elements: [loadingIcon]
                });
            }

            // Hide previous error if any
            elements.aiErrorContainer.classList.add('hidden');

            // Make API request
            const response = await fetch(`/dashboard/projects/${projectId}/test-cases/generate-ai`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    prompt: prompt,
                    suite_id: suiteId
                })
            });

            const result = await response.json();
            console.log('AI Generation response:', result);

            if (!response.ok) {
                let errorMsg = 'AI generation failed.';
                if (result.errors && result.errors.prompt) {
                    errorMsg = result.errors.prompt[0];
                } else if (result.message) {
                    errorMsg = result.message;
                }
                throw new Error(errorMsg);
            }

            if (result.success && result.data) {
                state.aiResult = result.data;
                populateFormFromAI(result.data);
                state.hasPopulatedFromAI = true;
                showSuccess('AI-generated test case created! Review and edit as needed.');

                // Update form section title/description
                elements.formSectionTitle.textContent = 'Review & Customize Generated Test Case';
                elements.formSectionDescription.textContent = 'Review the AI-generated test case and make any necessary adjustments';
            } else {
                throw new Error(result.message || 'AI generation returned no data.');
            }
        } catch (error) {
            console.error('AI Generation Error:', error);
            state.aiError = error.message || 'An unexpected error occurred.';

            // Show error in UI
            elements.aiErrorContainer.classList.remove('hidden');
            elements.aiErrorMessage.textContent = state.aiError;
        } finally {
            // Reset UI
            state.aiLoading = false;
            elements.generateAiBtn.disabled = false;
            elements.generateAiBtnText.textContent = 'Generate Test Case';

            // Remove loading icon and restore sparkles icon
            const loadingIcon = elements.generateAiBtn.querySelector('i.loading-icon, i[data-lucide="loader"]');
            if (loadingIcon) {
                elements.generateAiBtn.removeChild(loadingIcon);
            }

            if (elements.aiSparklesIcon) {
                elements.aiSparklesIcon.style.display = '';
            }
        }
    }

    /**
     * Populate the form fields with AI-generated data
     */
    function populateFormFromAI(data) {
        // Update hidden suite input
        elements.suiteIdInput.value = elements.aiSuiteSelect.value;

        // Title
        if (data.title) {
            elements.titleInput.value = data.title;
        }

        // Description
        if (data.description) {
            elements.descriptionInput.value = data.description;
        }

        // Priority
        if (data.priority) {
            setPriority(data.priority);
        }

        // Status
        if (data.status) {
            elements.statusInput.value = data.status;
        }

        // Steps
        if (Array.isArray(data.steps) && data.steps.length > 0) {
            // Clear existing steps
            elements.stepsList.innerHTML = '';

            // Add new steps
            data.steps.forEach((step, index) => {
                const stepItem = document.createElement('div');
                stepItem.className = 'step-item flex items-start space-x-3';
                stepItem.innerHTML = `
                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-semibold text-sm">
                        <span class="step-number">${index + 1}</span>
                    </div>
                    <div class="flex-1 relative">
                        <input name="steps[${index}]" type="text" value="${escapeHtml(step)}" class="step-input w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-lg shadow-inner shadow-zinc-300/50 dark:shadow-zinc-800/50 p-3 text-zinc-700 dark:text-zinc-200 focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300" placeholder="Describe the step to perform">
                    </div>
                    <button type="button" class="remove-step-btn flex-shrink-0 p-1.5 rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-red-500 dark:hover:text-red-400 transition-colors">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                `;

                elements.stepsList.appendChild(stepItem);
            });

            // If only one step, disable its remove button
            if (data.steps.length === 1) {
                const removeButton = elements.stepsList.querySelector('.remove-step-btn');
                if (removeButton) {
                    removeButton.setAttribute('disabled', 'disabled');
                }
            }

            // Re-render Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons({
                    attrs: {
                        class: ['step-icon']
                    },
                    elements: [elements.stepsList]
                });
            }
        }

        // Expected Results
        if (data.expected_results) {
            elements.expectedResultsInput.value = data.expected_results;
        }

        // Tags
        if (Array.isArray(data.tags) && data.tags.length > 0) {
            // Clear existing tags (except the input)
            const tagElements = elements.tagsContainer.querySelectorAll('div.inline-flex');
            tagElements.forEach(el => {
                elements.tagsContainer.removeChild(el);
            });

            // Reset state
            state.tags = [];

            // Add new tags
            data.tags.forEach(tag => {
                if (tag && typeof tag === 'string') {
                    // Update state
                    state.tags.push(tag);

                    // Create tag element
                    const tagElement = document.createElement('div');
                    tagElement.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 space-x-1';
                    tagElement.innerHTML = `
                        <span>${escapeHtml(tag)}</span>
                        <button type="button" class="text-zinc-500 hover:text-red-500 dark:text-zinc-400 dark:hover:text-red-400" data-tag="${escapeHtml(tag)}">
                            <i data-lucide="x" class="w-3 h-3"></i>
                        </button>
                        <input type="hidden" name="tags[]" value="${escapeHtml(tag)}">
                    `;

                    // Add remove button event listener
                    const removeButton = tagElement.querySelector('button');
                    removeButton.addEventListener('click', (e) => {
                        removeTag(e.currentTarget.getAttribute('data-tag'));
                    });

                    // Insert the tag before the input container
                    elements.tagsContainer.insertBefore(tagElement, elements.tagInputContainer);
                }
            });

            // Re-render Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons({
                    attrs: {
                        class: ['tag-icon']
                    },
                    elements: [elements.tagsContainer]
                });
            }
        }
    }

    /**
     * Submit the form
     */
    function submitForm() {
        // Validation
        if (!validateForm()) {
            return;
        }

        // Update UI to submitting state
        state.isSubmitting = true;
        elements.submitBtn.disabled = true;
        elements.submitBtnText.textContent = 'Creating...';

        // Add a loading icon
        const loadingIcon = document.createElement('svg');
        loadingIcon.className = 'animate-spin -ml-1 mr-2 h-4 w-4 text-white';
        loadingIcon.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        loadingIcon.setAttribute('fill', 'none');
        loadingIcon.setAttribute('viewBox', '0 0 24 24');
        loadingIcon.innerHTML = `
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        `;

        elements.submitBtnText.parentNode.insertBefore(loadingIcon, elements.submitBtnText);

        // Submit the form
        elements.testCaseForm.submit();
    }

    /**
     * Validate the form before submission
     */
    function validateForm() {
        const title = elements.titleInput.value.trim();
        const suiteId = elements.suiteIdInput.value;
        const steps = Array.from(elements.stepsList.querySelectorAll('.step-input')).map(input => input.value.trim());
        const expectedResults = elements.expectedResultsInput.value.trim();

        if (!title) {
            showError('Test case title is required');
            elements.titleInput.focus();
            return false;
        }

        if (!suiteId) {
            showError('Please select a test suite');
            if (state.creationMode === 'manual' && elements.suiteSelect) {
                elements.suiteSelect.focus();
            } else if (state.creationMode === 'ai' && elements.aiSuiteSelect) {
                elements.aiSuiteSelect.focus();
            }
            return false;
        }

        if (steps.length === 0 || steps.some(step => !step)) {
            showError('All test steps must be filled in');
            const emptyStep = elements.stepsList.querySelector('.step-input[value=""]');
            if (emptyStep) {
                emptyStep.focus();
            }
            return false;
        }

        if (!expectedResults) {
            showError('Expected results are required');
            elements.expectedResultsInput.focus();
            return false;
        }

        return true;
    }

    /**
     * Show a success notification
     */
    function showSuccess(message) {
        showNotification('success', 'Success', message);
    }

    /**
     * Show an error notification
     */
    function showError(message) {
        showNotification('error', 'Error', message);
    }

    /**
     * Show a notification
     */
    function showNotification(type, title, message) {
        // Set notification content
        elements.notificationTitle.textContent = title;
        elements.notificationMessage.textContent = message;

        // Set notification styling based on type
        if (type === 'success') {
            elements.notificationContainer.className = 'fixed bottom-6 right-6 z-50 max-w-sm w-full shadow-lg border rounded-xl p-4 bg-green-50/80 border-green-200/50 dark:bg-green-900/30 dark:border-green-800/30';
            elements.notificationTitle.className = 'font-medium mb-1 text-green-800 dark:text-green-200';
            elements.notificationMessage.className = 'text-sm text-green-700/90 dark:text-green-300/90';
            elements.notificationIcon.innerHTML = '<i data-lucide="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400"></i>';
        } else {
            elements.notificationContainer.className = 'fixed bottom-6 right-6 z-50 max-w-sm w-full shadow-lg border rounded-xl p-4 bg-red-50/80 border-red-200/50 dark:bg-red-900/30 dark:border-red-800/30';
            elements.notificationTitle.className = 'font-medium mb-1 text-red-800 dark:text-red-200';
            elements.notificationMessage.className = 'text-sm text-red-700/90 dark:text-red-300/90';
            elements.notificationIcon.innerHTML = '<i data-lucide="alert-circle" class="w-5 h-5 text-red-600 dark:text-red-400"></i>';
        }

        // Render icon
        if (typeof lucide !== 'undefined') {
            lucide.createIcons({
                attrs: {
                    class: ['notification-icon']
                },
                elements: [elements.notificationIcon]
            });
        }

        // Show notification
        elements.notificationContainer.classList.remove('hidden');

        // Auto-hide after delay
        setTimeout(hideNotification, type === 'success' ? 5000 : 7000);
    }

    /**
     * Hide the notification
     */
    function hideNotification() {
        elements.notificationContainer.classList.add('hidden');
    }

    /**
     * Escape HTML special characters
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };

        return text.replace(/[&<>"']/g, m => map[m]);
    }

    const flashMessages = document.getElementById('flash-messages');
    const flashSuccess = flashMessages ? flashMessages.getAttribute('data-success') : '';
    const flashError = flashMessages ? flashMessages.getAttribute('data-error') : '';

    if (flashSuccess) {
        showSuccess(flashSuccess);
    }

    if (flashError) {
        showError(flashError);
    }

    // Check for validation errors
    const hasErrors = document.querySelectorAll('.text-red-500').length > 0;
    if (hasErrors) {
        showError('There were errors in your submission. Please check the form.');
    }
}
