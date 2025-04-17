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
    // Mode toggle buttons
    const manualModeBtn = document.getElementById('manual-mode-btn');
    const aiModeBtn = document.getElementById('ai-mode-btn');

    // AI section elements
    const aiGenerationSection = document.getElementById('ai-generation-section');
    const aiPrompt = document.getElementById('ai-prompt');
    const generateAiBtn = document.getElementById('generate-ai-btn');
    const generateAiBtnText = document.getElementById('generate-ai-btn-text');
    const aiErrorContainer = document.getElementById('ai-error-container');
    const aiErrorMessage = document.getElementById('ai-error-message');

    // Form elements
    const testCaseForm = document.getElementById('test-case-form');
    const formSectionTitle = document.getElementById('form-section-title');
    const formSectionDescription = document.getElementById('form-section-description');
    const suiteIdInput = document.getElementById('suite-id-input');
    const titleInput = document.getElementById('title');
    const descriptionInput = document.getElementById('description');
    const priorityInput = document.getElementById('priority-input');
    const priorityOptions = document.querySelectorAll('.priority-option');
    const expectedResultsInput = document.getElementById('expected_results');

    // Steps
    const stepsList = document.getElementById('steps-list');
    const addStepBtn = document.getElementById('add-step-btn');

    // Tags
    const tagsContainer = document.getElementById('tags-container');
    const tagInput = document.getElementById('tag-input');
    const addTagBtn = document.getElementById('add-tag-btn');

    // Buttons
    const submitBtn = document.getElementById('submit-btn');
    const cancelBtn = document.getElementById('cancel-btn');

    // Project ID for API calls
    const projectId = document.getElementById('test-case-create-container')?.dataset.projectId;

    // State variables
    let hasPopulatedFromAI = false;

    // Event Listeners

    // Mode toggle
    if (manualModeBtn) {
        manualModeBtn.addEventListener('click', () => setCreationMode('manual'));
    }

    if (aiModeBtn) {
        aiModeBtn.addEventListener('click', () => setCreationMode('ai'));
    }

    // AI generation
    if (generateAiBtn) {
        generateAiBtn.addEventListener('click', generateWithAI);
    }

    // Priority selection
    priorityOptions.forEach(option => {
        option.addEventListener('click', () => {
            setPriority(option.getAttribute('data-value'));
        });
    });

    // Steps
    if (addStepBtn) {
        addStepBtn.addEventListener('click', addStep);
    }

    // Handle remove step button clicks using event delegation
    if (stepsList) {
        stepsList.addEventListener('click', (e) => {
            if (e.target.closest('.remove-step-btn')) {
                const stepItem = e.target.closest('.step-item');
                if (stepItem) {
                    removeStep(stepItem);
                }
            }
        });
    }

    // Tags
    if (addTagBtn) {
        addTagBtn.addEventListener('click', addTag);
    }

    if (tagInput) {
        tagInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addTag();
            }
        });
    }

    // Handle tag removal using event delegation
    if (tagsContainer) {
        tagsContainer.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.remove-tag-btn');
            if (removeBtn) {
                const tagItem = removeBtn.closest('.tag-item');
                if (tagItem) {
                    tagsContainer.removeChild(tagItem);
                }
            }
        });
    }

    // Form submission
    if (testCaseForm) {
        testCaseForm.addEventListener('submit', (e) => {
            if (!validateForm()) {
                e.preventDefault();
            }
        });
    }

    // Cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            window.history.back();
        });
    }

    /**
     * Set the creation mode (manual or AI)
     */
    function setCreationMode(mode) {
        if (mode === 'manual') {
            manualModeBtn.classList.add('bg-white', 'dark:bg-zinc-700', 'text-zinc-900', 'dark:text-white', 'shadow-sm', 'border-b-2', 'border-blue-500');
            manualModeBtn.classList.remove('text-zinc-600', 'dark:text-zinc-400');

            aiModeBtn.classList.remove('bg-white', 'dark:bg-zinc-700', 'text-zinc-900', 'dark:text-white', 'shadow-sm', 'border-b-2', 'border-purple-500');
            aiModeBtn.classList.add('text-zinc-600', 'dark:text-zinc-400');

            if (aiGenerationSection) {
                aiGenerationSection.classList.add('hidden');
            }

            if (formSectionTitle) {
                formSectionTitle.textContent = 'Test Case Details';
            }

            if (formSectionDescription) {
                formSectionDescription.textContent = 'Define your test case details, including steps and expected results';
            }
        } else {
            aiModeBtn.classList.add('bg-white', 'dark:bg-zinc-700', 'text-zinc-900', 'dark:text-white', 'shadow-sm', 'border-b-2', 'border-purple-500');
            aiModeBtn.classList.remove('text-zinc-600', 'dark:text-zinc-400');

            manualModeBtn.classList.remove('bg-white', 'dark:bg-zinc-700', 'text-zinc-900', 'dark:text-white', 'shadow-sm', 'border-b-2', 'border-blue-500');
            manualModeBtn.classList.add('text-zinc-600', 'dark:text-zinc-400');

            if (aiGenerationSection) {
                aiGenerationSection.classList.remove('hidden');
            }

            if (formSectionTitle) {
                if (hasPopulatedFromAI) {
                    formSectionTitle.textContent = 'Review & Customize Generated Test Case';
                } else {
                    formSectionTitle.textContent = 'AI Generated Test Case';
                }
            }

            if (formSectionDescription) {
                if (hasPopulatedFromAI) {
                    formSectionDescription.textContent = 'Review the AI-generated test case and make any necessary adjustments';
                } else {
                    formSectionDescription.textContent = 'The AI will generate a complete test case based on your description';
                }
            }
        }
    }

    /**
     * Set the priority for the test case
     */
    function setPriority(value) {
        if (priorityInput) {
            priorityInput.value = value;
        }

        // Update UI
        priorityOptions.forEach(option => {
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
        const stepCount = stepsList.querySelectorAll('.step-item').length;
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

        stepsList.appendChild(stepItem);

        // Enable all remove buttons if we have more than one step
        if (newStepId > 1) {
            const removeButtons = stepsList.querySelectorAll('.remove-step-btn');
            removeButtons.forEach(btn => btn.removeAttribute('disabled'));
        }

        // Re-render Lucide icons for the new elements
        if (typeof lucide !== 'undefined') {
            lucide.createIcons({
                elements: [stepItem]
            });
        }
    }

    /**
     * Remove a test step
     */
    function removeStep(stepItem) {
        const steps = stepsList.querySelectorAll('.step-item');

        // Only allow removal if we have more than one step
        if (steps.length <= 1) {
            return;
        }

        // Remove the step
        stepsList.removeChild(stepItem);

        // Renumber the remaining steps
        const remainingSteps = stepsList.querySelectorAll('.step-item');
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
        if (!tagInput) return;

        const tagValue = tagInput.value.trim();

        if (!tagValue) {
            tagInput.value = '';
            return;
        }

        // Check if tag already exists
        const existingTags = Array.from(tagsContainer.querySelectorAll('.tag-item'))
            .map(el => el.querySelector('span')?.textContent);

        if (existingTags.includes(tagValue)) {
            tagInput.value = '';
            return;
        }

        // Create tag element
        const tagElement = document.createElement('span');
        tagElement.className = 'tag-item inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-200 border border-indigo-200/50 dark:border-indigo-800/50';
        tagElement.innerHTML = `
            ${escapeHtml(tagValue)}
            <input type="hidden" name="tags[]" value="${escapeHtml(tagValue)}">
            <button type="button" class="remove-tag-btn ml-1.5 -mr-1 flex-shrink-0 text-indigo-400 hover:text-indigo-600 dark:text-indigo-500 dark:hover:text-indigo-300">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        `;

        // Insert the tag before the input container
        const tagInputContainer = document.getElementById('tag-input-container');
        if (tagInputContainer) {
            tagsContainer.insertBefore(tagElement, tagInputContainer);
        } else {
            tagsContainer.appendChild(tagElement);
        }

        // Clear the input
        tagInput.value = '';

        // Re-render Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons({
                elements: [tagElement]
            });
        }
    }

    /**
     * Generate a test case using AI
     */
    async function generateWithAI() {
        // Make sure the project ID is available
        if (!projectId) {
            showNotification('error', 'Error', 'Project ID not found');
            return;
        }

        // Get suite ID from the Alpine.js data model
        const suiteId = suiteIdInput ? suiteIdInput.value : null;
        const prompt = aiPrompt ? aiPrompt.value.trim() : '';

        if (!prompt) {
            showNotification('error', 'Error', 'Please provide a description of the test scenario');
            return;
        }

        if (!suiteId) {
            showNotification('error', 'Error', 'Please select a test suite');
            return;
        }

        try {
            // Update UI to loading state
            if (generateAiBtn) {
                generateAiBtn.disabled = true;
            }

            if (generateAiBtnText) {
                generateAiBtnText.textContent = 'Generating Test Case...';
            }

            // Create a loading icon
            const loadingIcon = document.createElement('i');
            loadingIcon.setAttribute('data-lucide', 'loader');
            loadingIcon.className = 'animate-spin w-5 h-5 mr-2';

            // Add the loading icon before the text
            if (generateAiBtnText && generateAiBtnText.parentNode) {
                generateAiBtnText.parentNode.insertBefore(loadingIcon, generateAiBtnText);

                // Render the new icon
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons({
                        elements: [loadingIcon]
                    });
                }
            }

            // Hide previous error if any
            if (aiErrorContainer) {
                aiErrorContainer.classList.add('hidden');
            }

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
                populateFormFromAI(result.data);
                hasPopulatedFromAI = true;
                showNotification('success', 'Success', 'AI-generated test case created! Review and edit as needed.');

                // Update form section title/description
                if (formSectionTitle) {
                    formSectionTitle.textContent = 'Review & Customize Generated Test Case';
                }

                if (formSectionDescription) {
                    formSectionDescription.textContent = 'Review the AI-generated test case and make any necessary adjustments';
                }
            } else {
                throw new Error(result.message || 'AI generation returned no data.');
            }
        } catch (error) {
            console.error('AI Generation Error:', error);

            // Show error in UI
            if (aiErrorContainer && aiErrorMessage) {
                aiErrorContainer.classList.remove('hidden');
                aiErrorMessage.textContent = error.message || 'An unexpected error occurred.';
            } else {
                showNotification('error', 'Error', error.message || 'An unexpected error occurred.');
            }
        } finally {
            // Reset UI
            if (generateAiBtn) {
                generateAiBtn.disabled = false;
            }

            if (generateAiBtnText) {
                generateAiBtnText.textContent = 'Generate Test Case';
            }

            // Remove loading icon
            const loadingIcon = document.querySelector('#generate-ai-btn i.animate-spin');
            if (loadingIcon && loadingIcon.parentNode) {
                loadingIcon.parentNode.removeChild(loadingIcon);
            }
        }
    }

    /**
     * Populate the form fields with AI-generated data
     */
    function populateFormFromAI(data) {
        console.log('Populating form with AI data:', data);

        // Title
        if (data.title && titleInput) {
            titleInput.value = data.title;
        }

        // Description
        if (data.description && descriptionInput) {
            descriptionInput.value = data.description;
        }

        // Priority
        if (data.priority) {
            setPriority(data.priority);
        }

        // Steps
        if (Array.isArray(data.steps) && data.steps.length > 0) {
            // Clear existing steps
            if (stepsList) {
                stepsList.innerHTML = '';

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

                    stepsList.appendChild(stepItem);
                });

                // If only one step, disable its remove button
                if (data.steps.length === 1) {
                    const removeButton = stepsList.querySelector('.remove-step-btn');
                    if (removeButton) {
                        removeButton.setAttribute('disabled', 'disabled');
                    }
                }

                // Re-render Lucide icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons({
                        elements: [stepsList]
                    });
                }
            }
        }

        // Expected Results
        if (data.expected_results && expectedResultsInput) {
            expectedResultsInput.value = data.expected_results;
        }

        // Tags
        if (Array.isArray(data.tags) && data.tags.length > 0 && tagsContainer) {
            // Clear existing tags (except the input container)
            const tagElements = tagsContainer.querySelectorAll('.tag-item');
            tagElements.forEach(el => {
                tagsContainer.removeChild(el);
            });

            // Find the tag input container if it exists
            const tagInputContainer = document.getElementById('tag-input-container');

            // Add new tags
            data.tags.forEach(tag => {
                if (tag && typeof tag === 'string') {
                    // Create tag element
                    const tagElement = document.createElement('span');
                    tagElement.className = 'tag-item inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-200 border border-indigo-200/50 dark:border-indigo-800/50';
                    tagElement.innerHTML = `
                        ${escapeHtml(tag)}
                        <input type="hidden" name="tags[]" value="${escapeHtml(tag)}">
                        <button type="button" class="remove-tag-btn ml-1.5 -mr-1 flex-shrink-0 text-indigo-400 hover:text-indigo-600 dark:text-indigo-500 dark:hover:text-indigo-300">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    `;

                    // Insert the tag before the input container if it exists
                    if (tagInputContainer) {
                        tagsContainer.insertBefore(tagElement, tagInputContainer);
                    } else {
                        tagsContainer.appendChild(tagElement);
                    }
                }
            });

            // Re-render Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons({
                    elements: [tagsContainer]
                });
            }
        }
    }

    /**
     * Validate the form before submission
     */
    function validateForm() {
        const title = titleInput ? titleInput.value.trim() : '';
        const suiteId = suiteIdInput ? suiteIdInput.value : '';
        const steps = stepsList ? Array.from(stepsList.querySelectorAll('.step-input')).map(input => input.value.trim()) : [];
        const expectedResults = expectedResultsInput ? expectedResultsInput.value.trim() : '';

        if (!title) {
            showNotification('error', 'Error', 'Test case title is required');
            if (titleInput) titleInput.focus();
            return false;
        }

        if (!suiteId) {
            showNotification('error', 'Error', 'Please select a test suite');
            return false;
        }

        if (steps.length === 0 || steps.some(step => !step)) {
            showNotification('error', 'Error', 'All test steps must be filled in');
            const emptyStep = stepsList ? stepsList.querySelector('.step-input[value=""]') : null;
            if (emptyStep) emptyStep.focus();
            return false;
        }

        if (!expectedResults) {
            showNotification('error', 'Error', 'Expected results are required');
            if (expectedResultsInput) expectedResultsInput.focus();
            return false;
        }

        return true;
    }

    /**
     * Show a notification
     * This function will try to use the native notification component if available,
     * or create a simple notification if not
     */
    function showNotification(type, title, message) {
        // Try to use notification component via custom event if available
        try {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: {
                    type: type,
                    title: title,
                    message: message
                }
            }));
            return;
        } catch (e) {
            console.warn('Native notification component not available, using fallback');
        }

        // Fallback: Create a simple notification
        const notificationContainer = document.getElementById('notification-container');
        const notificationTitle = document.getElementById('notification-title');
        const notificationMessage = document.getElementById('notification-message');
        const notificationIcon = document.getElementById('notification-icon');

        if (notificationContainer && notificationTitle && notificationMessage) {
            // Set notification content
            notificationTitle.textContent = title;
            notificationMessage.textContent = message;

            // Set notification styling based on type
            if (type === 'success') {
                notificationContainer.className = 'fixed bottom-6 right-6 z-50 max-w-sm w-full shadow-lg border rounded-xl p-4 bg-green-50/80 border-green-200/50 dark:bg-green-900/30 dark:border-green-800/30';
                notificationTitle.className = 'font-medium mb-1 text-green-800 dark:text-green-200';
                notificationMessage.className = 'text-sm text-green-700/90 dark:text-green-300/90';
                if (notificationIcon) {
                    notificationIcon.innerHTML = '<i data-lucide="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400"></i>';
                }
            } else {
                notificationContainer.className = 'fixed bottom-6 right-6 z-50 max-w-sm w-full shadow-lg border rounded-xl p-4 bg-red-50/80 border-red-200/50 dark:bg-red-900/30 dark:border-red-800/30';
                notificationTitle.className = 'font-medium mb-1 text-red-800 dark:text-red-200';
                notificationMessage.className = 'text-sm text-red-700/90 dark:text-red-300/90';
                if (notificationIcon) {
                    notificationIcon.innerHTML = '<i data-lucide="alert-circle" class="w-5 h-5 text-red-600 dark:text-red-400"></i>';
                }
            }

            // Render icon
            if (typeof lucide !== 'undefined' && notificationIcon) {
                lucide.createIcons({
                    elements: [notificationIcon]
                });
            }

            // Show notification
            notificationContainer.classList.remove('hidden');

            // Auto-hide after delay
            setTimeout(hideNotification, 5000);
        } else {
            // If notification container not found, use console
            console.log(`${type.toUpperCase()}: ${title} - ${message}`);
        }
    }

    /**
     * Hide the notification
     */
    function hideNotification() {
        const notificationContainer = document.getElementById('notification-container');
        if (notificationContainer) {
            notificationContainer.classList.add('hidden');
        }
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

        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    // Check for flash messages and validation errors
    const flashMessages = document.getElementById('flash-messages');
    const flashSuccess = flashMessages ? flashMessages.getAttribute('data-success') : '';
    const flashError = flashMessages ? flashMessages.getAttribute('data-error') : '';

    if (flashSuccess) {
        showNotification('success', 'Success', flashSuccess);
    }

    if (flashError) {
        showNotification('error', 'Error', flashError);
    }

    // Check for validation errors
    const hasErrors = document.querySelectorAll('.text-red-500').length > 0;
    if (hasErrors) {
        showNotification('error', 'Error', 'There were errors in your submission. Please check the form.');
    }
}
