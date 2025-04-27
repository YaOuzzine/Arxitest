/**
 * Test Case Creation JavaScript (Simplified)
 * Handles interactivity for the test case creation page (manual mode only)
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeTestCaseCreation();
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  });

  function initializeTestCaseCreation() {
    // Elements
    const manualModeBtn       = document.getElementById('manual-mode-btn');
    const aiModeBtn           = document.getElementById('ai-mode-btn');
    const aiGenerationSection = document.getElementById('ai-generation-section');
    const formSectionTitle    = document.getElementById('form-section-title');
    const formSectionDesc     = document.getElementById('form-section-description');
    const storyIdInput        = document.getElementById('story-id-input');
    const titleInput          = document.getElementById('title');
    const descriptionInput    = document.getElementById('description');
    const priorityInput       = document.getElementById('priority-input');
    const priorityOptions     = document.querySelectorAll('.priority-option');
    const stepsList           = document.getElementById('steps-list');
    const addStepBtn          = document.getElementById('add-step-btn');
    const tagsContainer       = document.getElementById('tags-container');
    const tagInput            = document.getElementById('tag-input');
    const addTagBtn           = document.getElementById('add-tag-btn');
    const testCaseForm        = document.getElementById('test-case-form');
    const cancelBtn           = document.getElementById('cancel-btn');
    const flashMessages       = document.getElementById('flash-messages');

    // Mode toggle: always start manual
    setCreationMode('manual');
    manualModeBtn?.addEventListener('click', () => setCreationMode('manual'));
    aiModeBtn?.addEventListener('click', () => setCreationMode('ai'));

    // Priority selection
    priorityOptions.forEach(option => {
      option.addEventListener('click', () => setPriority(option.dataset.value));
    });

    // Steps: add / remove
    addStepBtn?.addEventListener('click', addStep);
    stepsList?.addEventListener('click', (e) => {
      const btn = e.target.closest('.remove-step-btn');
      if (btn) removeStep(btn.closest('.step-item'));
    });

    // Tags: add / remove
    addTagBtn?.addEventListener('click', addTag);
    tagInput?.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        addTag();
      }
    });
    tagsContainer?.addEventListener('click', (e) => {
      const btn = e.target.closest('.remove-tag-btn');
      if (btn) btn.closest('.tag-item')?.remove();
    });

    // Form submission validation
    testCaseForm?.addEventListener('submit', (e) => {
      if (!validateForm()) e.preventDefault();
    });

    // Cancel button
    cancelBtn?.addEventListener('click', () => window.history.back());

    // Flash and validation errors
    const successMsg = flashMessages?.dataset.success;
    const errorMsg   = flashMessages?.dataset.error;
    if (successMsg) showNotification('success', 'Success', successMsg);
    if (errorMsg)   showNotification('error', 'Error', errorMsg);
    const hasErrors = document.querySelectorAll('.validation-error').length > 0;
    if (hasErrors) {
    showNotification('error', 'Error', 'There were errors in your submission. Please check the form.');
    }

    /**
     * Switch between manual and AI sections
     */
    function setCreationMode(mode) {
      const manual = (mode === 'manual');
      manualModeBtn?.classList.toggle('border-b-2', manual);
      aiModeBtn?.classList.toggle  ('border-b-2', !manual);
      aiGenerationSection?.classList.toggle('hidden', manual);
      // Always show manual section title
      if (formSectionTitle) formSectionTitle.textContent = 'Test Case Details';
      if (formSectionDesc)  formSectionDesc.textContent  = 'Define your test case details, including steps and expected results';
    }

    /**
     * Update priority input and UI
     */
    function setPriority(value) {
      if (priorityInput) priorityInput.value = value;
      priorityOptions.forEach(option => {
        const radio = option.querySelector('.priority-radio');
        if (option.dataset.value === value) {
          option.classList.add('ring-2','ring-zinc-500','dark:ring-zinc-400','bg-zinc-100/50','dark:bg-zinc-700/30');
          if (radio) {
            radio.classList.replace('bg-zinc-300','bg-zinc-800');
            radio.classList.replace('dark:bg-zinc-600','dark:bg-zinc-200');
            if (!radio.querySelector('div')) {
              const dot = document.createElement('div');
              dot.className = 'absolute inset-0 flex items-center justify-center';
              dot.innerHTML = '<div class="w-2 h-2 rounded-full bg-white dark:bg-zinc-800"></div>';
              radio.appendChild(dot);
            }
          }
        } else {
          option.classList.remove('ring-2','ring-zinc-500','dark:ring-zinc-400','bg-zinc-100/50','dark:bg-zinc-700/30');
          if (radio) {
            radio.classList.replace('bg-zinc-800','bg-zinc-300');
            radio.classList.replace('dark:bg-zinc-200','dark:bg-zinc-600');
            const dot = radio.querySelector('div');
            if (dot) radio.removeChild(dot);
          }
        }
      });
    }

    /**
     * Add a test step
     */
    function addStep() {
      const count = stepsList.querySelectorAll('.step-item').length;
      const item  = document.createElement('div');
      item.className = 'step-item flex items-start space-x-3';
      item.innerHTML = `
        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-semibold text-sm">\${count+1}</div>
        <div class="flex-1 relative">
          <input name="steps[\${count}]" type="text" class="step-input w-full border-0 bg-zinc-100/50 dark:bg-zinc-700/30 rounded-lg p-3 text-zinc-700 dark:text-zinc-200 focus:ring-2 focus:ring-zinc-500/50 dark:focus:ring-zinc-400/50 transition-all duration-300" placeholder="Describe the step to perform">
        </div>
        <button type="button" class="remove-step-btn p-1.5 rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-red-500 dark:hover:text-red-400 transition-colors">
          <i data-lucide="trash-2" class="w-4 h-4"></i>
        </button>
      `;
      stepsList.appendChild(item);
      if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    /**
     * Remove a test step
     */
    function removeStep(stepItem) {
      const items = stepsList.querySelectorAll('.step-item');
      if (items.length <= 1) return;
      stepItem.remove();
      // Re-number
      stepsList.querySelectorAll('.step-item').forEach((el,i) => {
        el.querySelector('.w-8').textContent = i+1;
        el.querySelector('input').name = `steps[\${i}]`;
      });
    }

    /**
     * Add a tag
     */
    function addTag() {
      if (!tagInput) return;
      const txt = tagInput.value.trim();
      if (!txt) return;
      const existing = Array.from(tagsContainer.querySelectorAll('.tag-item span')).map(s=>s.textContent);
      if (existing.includes(txt)) { tagInput.value=''; return; }
      const span = document.createElement('span');
      span.className = 'tag-item inline-flex items-center px-3 py-1 rounded-full text-sm bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-200 border border-indigo-200/50 dark:border-indigo-800/50';
      span.innerHTML = `
        <span>\${escapeHtml(txt)}</span>
        <input type="hidden" name="tags[]" value="\${escapeHtml(txt)}">
        <button type="button" class="remove-tag-btn ml-1.5 -mr-1">
          <i data-lucide="x" class="w-3.5 h-3.5 text-indigo-400 hover:text-indigo-600 dark:text-indigo-500 dark:hover:text-indigo-300"></i>
        </button>
      `;
      const container = document.getElementById('tag-input-container');
      tagsContainer.insertBefore(span, container);
      tagInput.value = '';
      if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    /**
     * Validate form before submit
     */
    function validateForm() {
      const title = titleInput?.value.trim();
      const story = storyIdInput?.value;
      const steps = Array.from(stepsList.querySelectorAll('input')).map(i=>i.value.trim());
      const expected = document.getElementById('expected_results')?.value.trim();
      if (!title)      { showNotification('error','Error','Title is required'); titleInput.focus(); return false; }
      if (!story)      { showNotification('error','Error','Please select a story'); return false; }
      if (steps.some(s=>!s)) { showNotification('error','Error','All steps must be filled'); return false; }
      if (!expected)   { showNotification('error','Error','Expected results are required'); return false; }
      return true;
    }

    /**
     * Simple DOM-based notification
     */
    function showNotification(type, title, msg) {
      const nc = document.getElementById('notification-container');
      const nt = document.getElementById('notification-title');
      const nm = document.getElementById('notification-message');
      const ni = document.getElementById('notification-icon');
      if (!nc||!nt||!nm) return;
      // set content
      nt.textContent = title;
      nm.textContent = msg;
      ni.innerHTML = type==='success'
        ? '<i data-lucide="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400"></i>'
        : '<i data-lucide="alert-circle" class="w-5 h-5 text-red-600 dark:text-red-400"></i>';
      // apply styling
      nc.className = `fixed bottom-6 right-6 z-50 max-w-sm w-full p-4 rounded-xl shadow-lg border backdrop-blur-sm \${
        type==='success'
          ? 'bg-green-50/80 border-green-200/50 dark:bg-green-900/30 dark:border-green-800/30'
          : 'bg-red-50/80   border-red-200/50   dark:bg-red-900/30   dark:border-red-800/30'
      }`;
      if (typeof lucide!=='undefined') lucide.createIcons();
      nc.classList.remove('hidden');
      setTimeout(hideNotification,5000);
    }

    function hideNotification() {
      document.getElementById('notification-container')?.classList.add('hidden');
    }

    function escapeHtml(str) {
      return String(str).replace(/[&<>{}\"']/g, c => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','{':'&#123;','}':'&#125;'
      }[c]));
    }
  }
