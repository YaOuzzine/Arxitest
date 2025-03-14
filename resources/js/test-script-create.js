document.addEventListener('DOMContentLoaded', function() {
    // Current script content and context
    let currentScriptContent = '';
    let currentScriptName = '';
    let isSaved = false;
    let isGenerating = false;
    let contextData = {
        userStories: [],
        projectDescription: '',
        customInstructions: '',
        files: []
    };

    // Initialize Terminal - make sure it's visible right away
    showTerminal();
    writeToTerminal('Arxitest Terminal v1.0', 'info');
    writeToTerminal('Ready to generate test scripts.', 'info');
    writeToTerminal('Add context and click "Generate Script with AI" to start.', 'info');

    // Set the first framework option as default
    const firstFrameworkOption = document.querySelector('input[name="framework_type"]');
    if (firstFrameworkOption) {
        firstFrameworkOption.checked = true;
        // Trigger change to apply the styling
        const event = new Event('change');
        firstFrameworkOption.dispatchEvent(event);
    }

    // Setup file uploads
    setupFileUpload('modal-file-upload-trigger', 'modal-files', 'modal-file-list', 'modal-uploaded-files-list');

    // Add event listener to context button
    const contextButton = document.getElementById('context-button');
    if (contextButton) {
        contextButton.addEventListener('click', openContextModal);
    }

    // Add event listeners to context modal buttons
    document.getElementById('close-context-modal-btn').addEventListener('click', closeContextModal);
    document.getElementById('cancel-context-btn').addEventListener('click', closeContextModal);
    document.getElementById('apply-context-btn').addEventListener('click', applyContextFromModal);

    // Edit context button may not exist initially if no context is set
    const editContextBtn = document.getElementById('edit-context-btn');
    if (editContextBtn) {
        editContextBtn.addEventListener('click', openContextModal);
    }

    // Add event listeners to edit modal buttons
    document.getElementById('close-edit-modal-btn').addEventListener('click', closeEditModal);
    document.getElementById('cancel-edit-btn').addEventListener('click', closeEditModal);
    document.getElementById('apply-changes-btn').addEventListener('click', updateScriptContent);

    // Add event listener to generate button
    document.getElementById('ai-generate-btn').addEventListener('click', generateWithOpenAI);

    // Add event listeners to script action buttons
    document.getElementById('copy-btn')?.addEventListener('click', copyScriptToClipboard);
    document.getElementById('edit-btn')?.addEventListener('click', openEditModal);
    document.getElementById('download-btn')?.addEventListener('click', downloadScript);
    document.getElementById('save-script-btn')?.addEventListener('click', saveScript);

    // Add event listener to form submission
    document.getElementById('test-script-form')?.addEventListener('submit', submitForm);
});

function updateFrameworkSelection(input) {
    if (input.checked) {
        // Update preview language display
        const previewLanguage = document.getElementById('preview-language');
        const previewFilename = document.getElementById('preview-filename');

        if (input.value === 'selenium_python') {
            previewLanguage.textContent = 'Python';
            previewFilename.textContent = 'test_script.py';
        } else if (input.value === 'cypress') {
            previewLanguage.textContent = 'JavaScript';
            previewFilename.textContent = 'test_script.js';
        }
    }
}

function setupFileUpload(triggerId, inputId, listContainerId, listId) {
    const trigger = document.getElementById(triggerId);
    const input = document.getElementById(inputId);
    const listContainer = document.getElementById(listContainerId);
    const list = document.getElementById(listId);

    if (trigger && input) {
        trigger.addEventListener('click', () => {
            input.click();
        });

        input.addEventListener('change', () => {
            listContainer.classList.remove('hidden');
            list.innerHTML = '';

            Array.from(input.files).forEach(file => {
                const item = document.createElement('li');
                item.className = 'flex items-center justify-between py-1';
                item.innerHTML = `
                    <span class="truncate">${file.name} <span class="text-gray-400">(${formatFileSize(file.size)})</span></span>
                    <button type="button" class="text-red-500 hover:text-red-700 ml-2" data-filename="${file.name}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18"></path>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                `;
                list.appendChild(item);

                // Add remove functionality
                const removeBtn = item.querySelector('button');
                removeBtn.addEventListener('click', (e) => {
                    const fileName = e.currentTarget.getAttribute('data-filename');
                    e.currentTarget.parentElement.remove();

                    // If no files left, hide the list container
                    if (list.children.length === 0) {
                        listContainer.classList.add('hidden');
                    }
                });
            });
        });
    }
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    else return (bytes / 1048576).toFixed(1) + ' MB';
}

function openContextModal() {
    // Animation for button
    animateButtonClick('context-button');

    // Update button text immediately to show feedback
    document.getElementById('context-button-text').textContent = "Opening Context...";

    // Update terminal
    writeToTerminal('Opening context modal...', 'info');

    const modal = document.getElementById('contextModal');
    const modalUserStories = document.getElementById('modal-user-stories');
    const modalProjectDescription = document.getElementById('modal-project-description');
    const modalCustomInstructions = document.getElementById('modal-custom-instructions');

    if (!modal || !modalUserStories || !modalProjectDescription || !modalCustomInstructions) {
        console.error('Context modal elements not found');
        writeToTerminal('Error: Could not open context modal.', 'error');
        document.getElementById('context-button-text').textContent = "Add Context for AI Generation";
        return;
    }

    // Pre-fill with existing context data
    if (contextData.userStories && contextData.userStories.length > 0) {
        Array.from(modalUserStories.options).forEach(option => {
            option.selected = contextData.userStories.includes(option.value);
        });
    }

    modalProjectDescription.value = contextData.projectDescription || '';
    modalCustomInstructions.value = contextData.customInstructions || '';

    // Show the modal with animation
    modal.classList.remove('hidden');

    // Force browser reflow to ensure transition works
    void modal.offsetWidth;

    setTimeout(() => {
        const modalElement = modal.querySelector('.modal');
        if (modalElement) {
            modalElement.classList.remove('opacity-0', 'scale-95');
        }
        // Reset button text
        document.getElementById('context-button-text').textContent = contextData.userStories.length > 0 ||
            contextData.projectDescription ||
            contextData.customInstructions ?
            "Edit Context Information" : "Add Context for AI Generation";

        writeToTerminal('Context modal opened.', 'success');
    }, 50);
}

function closeContextModal() {
    // Animation for button
    const targetId = event.target.id;
    if (targetId === 'close-context-modal-btn' || targetId === 'cancel-context-btn') {
        animateButtonClick(targetId);
    }

    // Update terminal
    writeToTerminal('Closing context modal...', 'info');

    const modal = document.getElementById('contextModal');
    const modalElement = modal.querySelector('.modal');

    if (modalElement) {
        modalElement.classList.add('opacity-0', 'scale-95');
    }

    setTimeout(() => {
        modal.classList.add('hidden');
        writeToTerminal('Context modal closed.', 'info');
    }, 200);
}

function applyContextFromModal() {
    // Animation for button
    animateButtonClick('apply-context-btn');

    // Show loading state
    const applyBtn = document.getElementById('apply-context-btn');
    const originalBtnText = applyBtn.textContent;
    applyBtn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Applying...
    `;

    // Update terminal
    writeToTerminal('Applying context...', 'info');

    const modalUserStories = document.getElementById('modal-user-stories');
    const modalProjectDescription = document.getElementById('modal-project-description');
    const modalCustomInstructions = document.getElementById('modal-custom-instructions');
    const modalFiles = document.getElementById('modal-files');

    // Save selected user stories
    contextData.userStories = Array.from(modalUserStories.selectedOptions).map(option => option.value);

    // Save other context information
    contextData.projectDescription = modalProjectDescription.value;
    contextData.customInstructions = modalCustomInstructions.value;

    // Transfer selected files to main context
    if (modalFiles.files.length > 0) {
        contextData.files = Array.from(modalFiles.files);
        writeToTerminal(`Files: ${contextData.files.length} uploaded`, 'info');
    }

    // Update context summary
    updateContextSummary();

    // Log context to terminal
    setTimeout(() => {
        writeToTerminal('Context applied successfully!', 'success');
        if (contextData.userStories.length > 0) {
            writeToTerminal(`User stories: ${contextData.userStories.length} selected`, 'info');
        }
        if (contextData.projectDescription) {
            writeToTerminal('Project description added', 'info');
        }
        if (contextData.customInstructions) {
            writeToTerminal('Custom instructions added', 'info');
        }

        // Reset button
        applyBtn.innerHTML = originalBtnText;

        // Close the modal
        closeContextModal();
    }, 500);
}

function updateContextSummary() {
    const summary = document.getElementById('context-summary');
    const tags = document.getElementById('context-tags');

    // Clear existing tags
    tags.innerHTML = '';

    // Create tags for each context item
    let hasContext = false;

    if (contextData.userStories.length > 0) {
        const tag = createContextTag('User Stories', `${contextData.userStories.length} selected`);
        tags.appendChild(tag);
        hasContext = true;
    }

    if (contextData.projectDescription) {
        const tag = createContextTag('Project Description', 'Added');
        tags.appendChild(tag);
        hasContext = true;
    }

    if (contextData.customInstructions) {
        const tag = createContextTag('Custom Instructions', 'Added');
        tags.appendChild(tag);
        hasContext = true;
    }

    if (contextData.files && contextData.files.length > 0) {
        const tag = createContextTag('Files', `${contextData.files.length} uploaded`);
        tags.appendChild(tag);
        hasContext = true;
    }

    // Show or hide summary based on context
    if (hasContext) {
        summary.classList.remove('hidden');
        // Update button text to show that context is added
        document.getElementById('context-button-text').innerHTML = "Edit Context Information";
    } else {
        summary.classList.add('hidden');
        // Reset button text
        document.getElementById('context-button-text').innerHTML = "Add Context for AI Generation";
    }
}

function createContextTag(title, value) {
    const tag = document.createElement('div');
    tag.className = 'context-tag';
    tag.innerHTML = `
        <span>${title}: <strong>${value}</strong></span>
    `;
    return tag;
}

async function generateWithOpenAI() {
    // Prevent multiple generation requests
    if (isGenerating) {
        writeToTerminal('Generation already in progress...', 'warning');
        return;
    }

    // Animation for button
    animateButtonClick('ai-generate-btn');

    // Check if we have a form selection
    const selectedFramework = document.querySelector('input[name="framework_type"]:checked');
    const jiraStory = document.getElementById('jira-story-select').value;

    if (!selectedFramework) {
        showStatus('error', 'Please select a framework type');
        writeToTerminal('Error: Please select a framework type.', 'error');
        return;
    }

    // Check if context is provided
    if (!hasContextData()) {
        showStatus('warning', 'No context provided');
        writeToTerminal('Warning: No context provided. Opening context modal...', 'warning');
        openContextModal();
        return;
    }

    // Show loading status
    isGenerating = true;
    showStatus('info', 'Generating script...', true);

    // Show terminal with generation progress
    showTerminal();
    writeToTerminal('Starting test script generation...', 'info');
    writeToTerminal(`Framework: ${selectedFramework.value}`, 'info');
    if (jiraStory) {
        writeToTerminal(`Linked to Jira story: ${jiraStory}`, 'info');
    }
    writeToTerminal('Preparing context data...', 'info');

    // Update button to show loading state
    const generateBtn = document.getElementById('ai-generate-btn');
    const originalBtnText = document.getElementById('generate-button-text').textContent;
    document.getElementById('generate-button-text').innerHTML = `
        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Generating...
    `;
    generateBtn.disabled = true;

    try {
        // Gather context information
        const contextPayload = prepareContextPayload(selectedFramework.value, jiraStory);
        writeToTerminal('Context prepared, sending to AI...', 'info');

        // Simulate API call with progress updates
        writeToTerminal('Connecting to OpenAI API...', 'info');
        await simulateDelay(800);
        writeToTerminal('Sending context data...', 'info');
        await simulateDelay(1200);
        writeToTerminal('AI processing context...', 'info');
        await simulateDelay(1500);
        writeToTerminal('Generating test script...', 'info');
        await simulateDelay(2000);

        // Call the API
        writeToTerminal('Waiting for response from OpenAI...', 'info');
        const response = await fetchOpenAIGeneration(contextPayload);

        // Process response
        if (response.success) {
            writeToTerminal('Script generation successful!', 'success');

            // Update script content
            currentScriptContent = response.script_content;
            document.getElementById('script-content-input').value = response.script_content;

            // Update script name if provided
            if (response.suggested_name) {
                currentScriptName = response.suggested_name;
                const scriptNameInput = document.getElementById('script-name');
                if (scriptNameInput && !scriptNameInput.value) {
                    scriptNameInput.value = response.suggested_name;
                    writeToTerminal(`Suggested name: ${response.suggested_name}`, 'info');
                }
            }

            // Update preview
            await simulateDelay(500);
            writeToTerminal('Rendering script preview...', 'info');
            updateScriptPreview(response.script_content);

            // Reset saved state
            isSaved = false;

            // Show script preview instead of terminal
            await simulateDelay(800);
            hideTerminal();
            showScriptPreview();

            // Show success message
            showStatus('success', 'Script generated successfully');
        } else {
            writeToTerminal(`Error: ${response.message || 'Failed to generate script'}`, 'error');
            showStatus('error', response.message || 'Failed to generate script');
        }
    } catch (error) {
        console.error('Generation failed:', error);
        writeToTerminal(`Error: Generation failed - ${error.message}`, 'error');
        showStatus('error', 'Error generating script. Please try again.');
    } finally {
        // Re-enable generate button and reset text
        generateBtn.disabled = false;
        document.getElementById('generate-button-text').innerHTML = originalBtnText;
        isGenerating = false;
    }
}

function hasContextData() {
    return contextData.userStories.length > 0 ||
        contextData.projectDescription ||
        contextData.customInstructions ||
        (contextData.files && contextData.files.length > 0);
}

function prepareContextPayload(frameworkType, jiraStoryId) {
    const payload = {
        framework_type: frameworkType,
        context: {
            jira_story_id: jiraStoryId || null,
            user_stories: [],
            project_description: contextData.projectDescription || '',
            custom_instructions: contextData.customInstructions || '',
            files: []
        }
    };

    // Add selected user stories
    if (contextData.userStories.length > 0) {
        payload.context.user_stories = contextData.userStories;
    }

    // Indicate if files are present
    if (contextData.files && contextData.files.length > 0) {
        payload.context.has_files = true;
        payload.context.file_count = contextData.files.length;
    }

    return payload;
}

async function fetchOpenAIGeneration(payload) {
    // Get CSRF token from meta tag
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    try {
        // Simulate API call for demo purposes
        await simulateDelay(2000);

        // Return simulated success
        return {
            success: true,
            script_content: generateSampleScript(payload.framework_type),
            suggested_name: payload.framework_type === 'selenium_python' ? 'login_test' : 'login_test',
        };
    } catch (error) {
        console.error("Error fetching from OpenAI API:", error);
        throw error;
    }
}

// Function to generate a sample script for the demo
function generateSampleScript(frameworkType) {
    if (frameworkType === 'selenium_python') {
        return `import unittest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

class LoginTest(unittest.TestCase):
    def setUp(self):
        self.driver = webdriver.Chrome()
        self.driver.maximize_window()
        self.driver.get("http://example.com/login")
        self.wait = WebDriverWait(self.driver, 10)

    def test_successful_login(self):
        # Find username and password fields
        username_field = self.wait.until(EC.visibility_of_element_located((By.ID, "username")))
        password_field = self.driver.find_element(By.ID, "password")

        # Enter credentials
        username_field.send_keys("test_user")
        password_field.send_keys("password123")

        # Click login button
        login_button = self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        login_button.click()

        # Verify successful login
        dashboard_element = self.wait.until(EC.presence_of_element_located((By.ID, "dashboard")))
        self.assertTrue(dashboard_element.is_displayed())

        # Verify welcome message
        welcome_message = self.driver.find_element(By.CSS_SELECTOR, ".welcome-message")
        self.assertEqual(welcome_message.text, "Welcome, test_user!")

    def test_invalid_credentials(self):
        # Find username and password fields
        username_field = self.wait.until(EC.visibility_of_element_located((By.ID, "username")))
        password_field = self.driver.find_element(By.ID, "password")

        # Enter invalid credentials
        username_field.send_keys("wrong_user")
        password_field.send_keys("wrong_password")

        # Click login button
        login_button = self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        login_button.click()

        # Verify error message
        error_message = self.wait.until(EC.visibility_of_element_located((By.CSS_SELECTOR, ".error-message")))
        self.assertEqual(error_message.text, "Invalid username or password")

    def tearDown(self):
        self.driver.quit()

if __name__ == "__main__":
    unittest.main()`;
    } else {
        return `describe('Login Functionality', () => {
  beforeEach(() => {
    cy.visit('/login')
  })

  it('should login successfully with valid credentials', () => {
    // Enter valid credentials
    cy.get('#username').type('test_user')
    cy.get('#password').type('password123')

    // Click the login button
    cy.get('button[type="submit"]').click()

    // Verify redirection to dashboard
    cy.url().should('include', '/dashboard')

    // Verify welcome message is displayed
    cy.get('.welcome-message').should('be.visible')
      .and('contain', 'Welcome, test_user!')

    // Verify navigation menu is available
    cy.get('nav').should('be.visible')
  })

  it('should show error message with invalid credentials', () => {
    // Enter invalid credentials
    cy.get('#username').type('wrong_user')
    cy.get('#password').type('wrong_password')

    // Click the login button
    cy.get('button[type="submit"]').click()

    // Verify error message
    cy.get('.error-message')
      .should('be.visible')
      .and('contain', 'Invalid username or password')

    // Verify we're still on login page
    cy.url().should('include', '/login')
  })

  it('should validate required fields', () => {
    // Click login without entering credentials
    cy.get('button[type="submit"]').click()

    // Check for validation messages
    cy.get('#username:invalid')
      .should('have.length', 1)

    cy.get('#password:invalid')
      .should('have.length', 1)

    // Enter username only and try again
    cy.get('#username').type('test_user')
    cy.get('button[type="submit"]').click()

    // Only password field should be invalid now
    cy.get('#username:invalid')
      .should('have.length', 0)
    cy.get('#password:invalid')
      .should('have.length', 1)
  })
})`;
    }
}

function showStatus(type, message, isLoading = false) {
    const statusContainer = document.getElementById('generation-status');
    const statusBadge = document.getElementById('status-badge');
    const statusText = document.getElementById('status-text');

    if (!statusContainer || !statusBadge || !statusText) return;

    statusContainer.classList.remove('hidden');
    statusBadge.className = 'status-badge ' + type;
    statusText.textContent = message;

    if (isLoading) {
        statusBadge.innerHTML = `
            <svg class="animate-spin w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            ${message}
        `;
    } else {
        // Show correct icon based on status type
        let icon = '';
        switch (type) {
            case 'success':
                icon =
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M20 6 9 17l-5-5"></path></svg>';
                break;
            case 'error':
                icon =
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><circle cx="12" cy="12" r="10"></circle><path d="m15 9-6 6"></path><path d="m9 9 6 6"></path></svg>';
                break;
            case 'warning':
                icon =
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>';
                break;
            default:
                icon =
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>';
        }

        statusBadge.innerHTML = icon + message;

        // Auto-hide success and info messages after 5 seconds
        if (type === 'success' || type === 'info') {
            setTimeout(() => {
                statusContainer.classList.add('hidden');
            }, 5000);
        }
    }
}

function updateScriptPreview(scriptContent) {
    const previewElement = document.getElementById('script-preview');
    const previewContainer = document.getElementById('script-preview-container');

    if (!previewElement) return;

    // Clear the preview
    previewElement.innerHTML = '';

    if (!scriptContent) {
        previewElement.innerHTML = `
            <div class="code-line">
                <div class="line-number">1</div>
                <div class="line-content">
                    <span class="python-comment"># No script content yet.</span>
                </div>
            </div>`;
        return;
    }

    // Split content into lines and create styled elements
    const lines = scriptContent.split('\n');

    lines.forEach((line, index) => {
        const lineNumber = index + 1;
        let styledLine = line;

        // Simple syntax highlighting
        const framework = document.querySelector('input[name="framework_type"]:checked')?.value;

        if (framework === 'selenium_python') {
            // Python syntax highlighting
            styledLine = line
                .replace(
                    /\b(import|from|class|def|return|self|if|else|elif|for|while|try|except|as|with|True|False|None)\b/g,
                    '<span class="python-keyword">$1</span>')
                .replace(/(['"])(.*?)\1/g, '<span class="python-string">$1$2$1</span>')
                .replace(/(#.*)$/g, '<span class="python-comment">$1</span>')
                .replace(/\b(unittest|WebDriverWait|expected_conditions|EC|By|webdriver)\b/g,
                    '<span class="python-class">$1</span>')
                .replace(/(\w+)(?=\()/g, '<span class="python-function">$1</span>');
        } else {
            // JavaScript/Cypress syntax highlighting
            styledLine = line
                .replace(
                    /\b(const|let|var|function|return|this|if|else|for|while|try|catch|await|async|true|false|null|undefined)\b/g,
                    '<span class="python-keyword">$1</span>')
                .replace(/(['"])(.*?)\1/g, '<span class="python-string">$1$2$1</span>')
                .replace(/\/\/(.*?)$/g, '<span class="python-comment">//$1</span>')
                .replace(/\b(describe|it|beforeEach|cy|expect)\b/g,
                    '<span class="python-function">$1</span>');
        }

        const lineElement = document.createElement('div');
        lineElement.className = 'code-line';
        lineElement.innerHTML = `
            <div class="line-number">${lineNumber}</div>
            <div class="line-content">${styledLine || '&nbsp;'}</div>
        `;

        previewElement.appendChild(lineElement);
    });
}

function openEditModal() {
    // Animation for button
    animateButtonClick('edit-btn');

    const modal = document.getElementById('editModal');
    const editScriptContent = document.getElementById('edit-script-content');

    if (!modal || !editScriptContent) {
        console.error('Edit modal elements not found');
        return;
    }

    // Set current script content in the textarea
    editScriptContent.value = currentScriptContent;

    // Show modal with animation
    modal.classList.remove('hidden');

    // Force browser reflow to ensure transition works
    void modal.offsetWidth;

    setTimeout(() => {
        const modalElement = modal.querySelector('.modal');
        if (modalElement) {
            modalElement.classList.remove('opacity-0', 'scale-95');
        }
    }, 50);

    // Write to terminal
    writeToTerminal('Opening edit modal...', 'info');
}

function closeEditModal() {
    // Animation for button
    const targetId = event.target.id;
    if (targetId === 'close-edit-modal-btn' || targetId === 'cancel-edit-btn') {
        animateButtonClick(targetId);
    }

    const modal = document.getElementById('editModal');
    const modalElement = modal.querySelector('.modal');

    if (modalElement) {
        modalElement.classList.add('opacity-0', 'scale-95');
    }

    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);

    // Write to terminal
    writeToTerminal('Edit modal closed.', 'info');
}

function updateScriptContent() {
    // Animation for button
    animateButtonClick('apply-changes-btn');

    // Show loading state
    const applyBtn = document.getElementById('apply-changes-btn');
    const originalBtnText = applyBtn.textContent;
    applyBtn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Applying...
    `;

    const editScriptContent = document.getElementById('edit-script-content');
    const scriptContent = editScriptContent.value;

    // Simulate processing delay
    setTimeout(() => {
        // Update current script content
        currentScriptContent = scriptContent;

        // Update the hidden input field
        document.getElementById('script-content-input').value = scriptContent;

        // Update the script preview
        updateScriptPreview(scriptContent);

        // Reset button
        applyBtn.innerHTML = originalBtnText;

        // Close the edit modal
        closeEditModal();

        // Write to terminal
        writeToTerminal('Script updated with new content.', 'success');
        showStatus('success', 'Script updated successfully');
    }, 500);
}

function copyScriptToClipboard() {
    // Animation for button
    animateButtonClick('copy-btn');

    if (currentScriptContent) {
        navigator.clipboard.writeText(currentScriptContent)
            .then(() => {
                // Show a temporary success message
                const copyBtn = document.getElementById('copy-btn');
                const originalHTML = copyBtn.innerHTML;

                copyBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-green-500">
                        <path d="M20 6 9 17l-5-5"></path>
                    </svg>
                `;
                copyBtn.classList.add('text-green-500');

                setTimeout(() => {
                    copyBtn.innerHTML = originalHTML;
                    copyBtn.classList.remove('text-green-500');
                }, 2000);

                // Write to terminal
                writeToTerminal('Script copied to clipboard.', 'success');
                showStatus('success', 'Copied to clipboard');
            })
            .catch(err => {
                console.error('Failed to copy: ', err);
                showStatus('error', 'Failed to copy to clipboard');
                writeToTerminal('Error: Failed to copy script to clipboard.', 'error');
            });
    }
}

function downloadScript() {
    // Animation for button
    animateButtonClick('download-btn');

    if (!currentScriptContent) {
        showStatus('error', 'No script content to download');
        writeToTerminal('Error: No script content to download.', 'error');
        return;
    }

    // Get the filename
    const frameworkType = document.querySelector('input[name="framework_type"]:checked').value;
    const scriptName = document.getElementById('script-name').value || currentScriptName || 'test_script';
    const fileExtension = frameworkType === 'selenium_python' ? '.py' : '.js';
    const filename = scriptName.replace(/\s+/g, '_') + fileExtension;

    // Show loading state
    const downloadBtn = document.getElementById('download-btn');
    const originalHTML = downloadBtn.innerHTML;
    downloadBtn.innerHTML = `
        <svg class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    `;

    setTimeout(() => {
        // Create a blob with the script content
        const blob = new Blob([currentScriptContent], {
            type: 'text/plain'
        });

        // Create a download link and trigger it
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();

        // Clean up
        setTimeout(() => {
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            downloadBtn.innerHTML = originalHTML;
        }, 500);

        // Write to terminal
        writeToTerminal(`Script downloaded as ${filename}`, 'success');
        showStatus('success', `Downloaded as ${filename}`);
    }, 800);
}

function saveScript() {
    // Animation for button
    animateButtonClick('save-script-btn');

    if (!currentScriptContent) {
        showStatus('error', 'No script content to save');
        writeToTerminal('Error: No script content to save.', 'error');
        return;
    }

    // Show loading state
    const saveBtn = document.getElementById('save-script-btn');
    const originalHTML = saveBtn.innerHTML;
    saveBtn.innerHTML = `
        <svg class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    `;

    // In a real application, this would save to a database
    // Here we'll simulate saving by setting a flag
    setTimeout(() => {
        isSaved = true;

        saveBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-green-500">
                <path d="M20 6 9 17l-5-5"></path>
            </svg>
        `;
        saveBtn.classList.add('text-green-500');

        setTimeout(() => {
            saveBtn.innerHTML = originalHTML;
            saveBtn.classList.remove('text-green-500');
        }, 2000);

        // Write to terminal
        writeToTerminal('Script saved successfully.', 'success');
        showStatus('success', 'Script saved successfully');
    }, 1000);
}

function submitForm(event) {
    // Animation for button
    animateButtonClick('submit-btn');

    // Ensure the script content is set before submitting
    if (!currentScriptContent) {
        event.preventDefault();
        showStatus('error', 'Please generate or enter script content before submitting');
        writeToTerminal('Error: No script content to submit. Generate a script first.', 'error');
        return;
    }

    // Update button to show loading state
    const submitBtn = document.getElementById('submit-btn');
    const originalBtnText = document.getElementById('submit-button-text').textContent;
    document.getElementById('submit-button-text').innerHTML = `
        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Creating...
    `;

    // Disable the button to prevent multiple submissions
    submitBtn.disabled = true;

    // Write to terminal
    writeToTerminal('Submitting test script...', 'info');

    // Allow the form to submit normally after updating the button
    // In a real app, you might want to submit via AJAX instead
    setTimeout(() => {
        // For demo, simulate success
        writeToTerminal('Test script created successfully!', 'success');
        showStatus('success', 'Test script created successfully!');

        // Reset button after 1 second (this would normally happen on the next page)
        setTimeout(() => {
            document.getElementById('submit-button-text').textContent = originalBtnText;
            submitBtn.disabled = false;
        }, 1000);
    }, 2000);
}

// Terminal functions
function showTerminal() {
    const terminalContainer = document.getElementById('terminal-container');
    if (terminalContainer) {
        terminalContainer.classList.remove('hidden');
    }
}

function hideTerminal() {
    const terminalContainer = document.getElementById('terminal-container');
    if (terminalContainer) {
        terminalContainer.classList.add('hidden');
    }
}

function showScriptPreview() {
    const previewContainer = document.getElementById('script-preview-container');
    if (previewContainer) {
        previewContainer.classList.remove('hidden');
    }
}

function writeToTerminal(message, type = 'normal') {
    const terminal = document.getElementById('terminal-output');
    if (!terminal) return;

    const line = document.createElement('div');
    line.className = 'terminal-line';

    let timestamp = new Date().toLocaleTimeString();

    switch (type) {
        case 'info':
            line.innerHTML = `<span class="terminal-info">[${timestamp}] INFO: ${message}</span>`;
            break;
        case 'success':
            line.innerHTML = `<span class="terminal-success">[${timestamp}] SUCCESS: ${message}</span>`;
            break;
        case 'error':
            line.innerHTML = `<span class="terminal-error">[${timestamp}] ERROR: ${message}</span>`;
            break;
        case 'warning':
            line.innerHTML = `<span class="terminal-warning">[${timestamp}] WARNING: ${message}</span>`;
            break;
        default:
            line.innerHTML = `<span>[${timestamp}] ${message}</span>`;
    }

    terminal.appendChild(line);

    // Scroll to bottom
    terminal.scrollTop = terminal.scrollHeight;
}

// Helper functions
function simulateDelay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function animateButtonClick(buttonId) {
    const button = document.getElementById(buttonId);
    if (button) {
        button.classList.add('scale-95');
        setTimeout(() => {
            button.classList.remove('scale-95');
        }, 100);
    }
}
