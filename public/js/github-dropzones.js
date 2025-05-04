// public/js/github-dropzones.js
document.addEventListener('DOMContentLoaded', function() {
    // Find all forms with textareas or code editors
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        const textareas = form.querySelectorAll('textarea');
        if (textareas.length > 0) {
            // This form has textareas, make it a dropzone
            const formType = determineFormType(form);
            if (formType) {
                form.setAttribute('data-dropzone', formType);

                // Setup drag and drop handling
                form.addEventListener('dragenter', handleDragEnter);
                form.addEventListener('dragleave', handleDragLeave);
                form.addEventListener('dragover', handleDragOver);
                form.addEventListener('drop', handleDrop);
            }
        }
    });

    // Determine the type of form (test-case, test-script, etc.)
    function determineFormType(form) {
        // Check form action URL
        const action = form.getAttribute('action') || '';

        if (action.includes('test-cases')) return 'test-case';
        if (action.includes('scripts')) return 'test-script';
        if (action.includes('stories')) return 'story';
        if (action.includes('test-suites')) return 'test-suite';
        if (action.includes('data')) return 'test-data';

        // Check for form elements that might indicate the type
        if (form.querySelector('[name="script_content"]')) return 'test-script';
        if (form.querySelector('[name="steps"]')) return 'test-case';
        if (form.querySelector('[name="description"]')) {
            // Could be many things, check for other clues
            if (form.querySelector('[name="expected_results"]')) return 'test-case';
            if (form.querySelector('[name="acceptance_criteria"]')) return 'story';
        }

        // Default to null - not a dropzone
        return null;
    }

    // Handle drag and drop events
    function handleDragEnter(e) {
        e.preventDefault();
        this.classList.add('github-drag-active');
    }

    function handleDragLeave(e) {
        e.preventDefault();
        if (!e.relatedTarget || !this.contains(e.relatedTarget)) {
            this.classList.remove('github-drag-active');
        }
    }

    function handleDragOver(e) {
        e.preventDefault();
    }

    function handleDrop(e) {
        e.preventDefault();
        this.classList.remove('github-drag-active');

        // Check if this is a GitHub file
        try {
            const data = JSON.parse(e.dataTransfer.getData('text/plain'));
            if (data.type === 'github-file') {
                // Process the dropped GitHub file
                processGitHubFileDrop(data, this);
            }
        } catch (error) {
            console.error('Error parsing dropped data:', error);
        }
    }

    // Process a dropped GitHub file
    function processGitHubFileDrop(data, form) {
        // Show loading indicator
        const loadingEl = document.createElement('div');
        loadingEl.className = 'fixed top-0 left-0 w-full h-full flex items-center justify-center bg-black bg-opacity-50 z-50';
        loadingEl.innerHTML = `
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-xl flex items-center space-x-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-zinc-900 dark:border-zinc-100"></div>
                <div class="text-zinc-900 dark:text-zinc-100">Loading file from GitHub...</div>
            </div>
        `;
        document.body.appendChild(loadingEl);

        // Fetch the file content from GitHub
        fetch(`/api/github/file/${data.owner}/${data.repo}/${encodeURIComponent(data.path)}`)
            .then(response => response.json())
            .then(result => {
                // Remove loading indicator
                document.body.removeChild(loadingEl);

                if (result.success) {
                    // Process based on form type
                    const formType = form.getAttribute('data-dropzone');
                    const content = result.data.content;

                    switch (formType) {
                        case 'test-case':
                            handleTestCaseDrop(form, data, content);
                            break;
                        case 'test-script':
                            handleTestScriptDrop(form, data, content);
                            break;
                        case 'story':
                            handleStoryDrop(form, data, content);
                            break;
                        case 'test-suite':
                            handleTestSuiteDrop(form, data, content);
                            break;
                        case 'test-data':
                            handleTestDataDrop(form, data, content);
                            break;
                    }

                    // Show success notification
                    showNotification(`Added ${data.path} as context`, 'success');
                } else {
                    showNotification(`Error: ${result.message}`, 'error');
                }
            })
            .catch(error => {
                // Remove loading indicator
                document.body.removeChild(loadingEl);
                showNotification(`Error: ${error.message}`, 'error');
            });
    }

    // Handle drops for different form types
    function handleTestCaseDrop(form, data, content) {
        // For test cases, add file content to description or steps
        const description = form.querySelector('[name="description"]');
        if (description) {
            description.value += `\n\n**From GitHub file ${data.path}:**\n\`\`\`\n${content}\n\`\`\``;
        }

        // Also prepopulate steps if empty
        const stepsContainer = form.querySelector('[data-steps-container]');
        if (stepsContainer && stepsContainer.querySelectorAll('input, textarea').length === 0) {
            // Try to generate steps based on file content
            const steps = generateStepsFromFileContent(data, content);
            // Add steps (implementation would depend on your UI)
        }
    }

    function handleTestScriptDrop(form, data, content) {
        // For test scripts, set the content directly
        const scriptContent = form.querySelector('[name="script_content"]');
        if (scriptContent) {
            scriptContent.value = content;
        }

        // Set name if empty
        const nameField = form.querySelector('[name="name"]');
        if (nameField && !nameField.value) {
            nameField.value = `Test script for ${data.name}`;
        }

        // Set framework type based on file extension
        const frameworkSelect = form.querySelector('[name="framework_type"]');
        if (frameworkSelect) {
            const ext = data.path.split('.').pop().toLowerCase();
            if (ext === 'py') {
                frameworkSelect.value = 'selenium-python';
            } else if (ext === 'js' || ext === 'ts') {
                frameworkSelect.value = 'cypress';
            }
        }
    }

    function handleStoryDrop(form, data, content) {
        // For stories, add file content to description
        const description = form.querySelector('[name="description"]');
        if (description) {
            description.value += `\n\n**File reference:** ${data.path}\n\n${content.substring(0, 500)}${content.length > 500 ? '...' : ''}`;
        }

        // Set title if empty
        const titleField = form.querySelector('[name="title"]');
        if (titleField && !titleField.value) {
            titleField.value = `Feature based on ${data.name}`;
        }
    }

    function handleTestSuiteDrop(form, data, content) {
        // For test suites, add file content to description
        const description = form.querySelector('[name="description"]');
        if (description) {
            description.value += `\n\nBased on GitHub file: ${data.path}`;
        }
    }

    function handleTestDataDrop(form, data, content) {
        // For test data, set the content directly
        const dataContent = form.querySelector('[name="content"]');
        if (dataContent) {
            dataContent.value = content;
        }

        // Set name and format if empty
        const nameField = form.querySelector('[name="name"]');
        if (nameField && !nameField.value) {
            nameField.value = `Data from ${data.name}`;
        }

        const formatField = form.querySelector('[name="format"]');
        if (formatField) {
            const ext = data.path.split('.').pop().toLowerCase();
            if (ext === 'json') formatField.value = 'json';
            else if (ext === 'csv') formatField.value = 'csv';
            else if (ext === 'xml') formatField.value = 'xml';
            else formatField.value = 'plain';
        }
    }

    // Helper function to show notifications
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
            type === 'error' ? 'bg-red-500 text-white' : 'bg-green-500 text-white'
        }`;
        notification.innerHTML = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('opacity-0');
            notification.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 500);
        }, 3000);
    }

    // Helper function to generate steps from file content
    function generateStepsFromFileContent(data, content) {
        // This is a placeholder - you would implement your own logic
        // For example, for a test file, you might extract test functions
        return [
            `Review code from ${data.path}`,
            'Identify key functionality',
            'Test main functions',
            'Verify expected behavior'
        ];
    }
});
