@if(session('github_connected', false))
<div id="github-bubble" class="fixed bottom-6 right-6 z-50">
    <button id="github-bubble-toggle" class="bg-zinc-900 dark:bg-zinc-700 text-white p-3 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
            <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path>
        </svg>
    </button>

    <div id="github-browser" class="hidden fixed right-6 bottom-20 bg-white dark:bg-zinc-800 shadow-2xl rounded-lg w-96 max-h-[80vh] border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center bg-zinc-50 dark:bg-zinc-900">
            <h3 class="text-lg font-bold">GitHub Repository Browser</h3>
            <button id="github-browser-close" class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
            <select id="github-repo-select" class="w-full p-2 rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200">
                <option value="">Select a repository...</option>
            </select>
        </div>

        <div id="github-content-browser" class="p-4 overflow-y-auto max-h-[calc(80vh-140px)]">
            <div id="github-loading" class="hidden flex justify-center py-8">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-zinc-900 dark:border-zinc-100"></div>
            </div>
            <div id="github-path-breadcrumb" class="pb-2 text-sm text-zinc-500 dark:text-zinc-400 hidden"></div>
            <div id="github-content-listing"></div>
            <div id="github-file-content" class="hidden">
                <div class="flex justify-between pb-2">
                    <button id="github-back-to-listing" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                        ← Back to listing
                    </button>
                    <button id="github-use-file" class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline">
                        Use as context
                    </button>
                </div>
                <pre id="github-file-preview" class="p-4 bg-zinc-100 dark:bg-zinc-900 rounded-md overflow-x-auto text-sm"></pre>
            </div>
        </div>

        <div id="github-actions" class="p-4 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 hidden">
            <div class="flex space-x-2">
                <button id="github-create-project" class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors text-sm">
                    Create Project from Repo
                </button>
                <button id="github-create-test-suite" class="px-3 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 transition-colors text-sm">
                    Create Test Suite
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // References to DOM elements
    const bubble = document.getElementById('github-bubble-toggle');
    const browser = document.getElementById('github-browser');
    const closeBtn = document.getElementById('github-browser-close');
    const repoSelect = document.getElementById('github-repo-select');
    const contentListing = document.getElementById('github-content-listing');
    const fileContent = document.getElementById('github-file-content');
    const filePreview = document.getElementById('github-file-preview');
    const backToListing = document.getElementById('github-back-to-listing');
    const useFileBtn = document.getElementById('github-use-file');
    const breadcrumb = document.getElementById('github-path-breadcrumb');
    const loading = document.getElementById('github-loading');
    const actions = document.getElementById('github-actions');

    // Current state
    let currentRepo = '';
    let currentOwner = '';
    let currentPath = '';
    let currentFileContent = '';

    // Toggle browser visibility
    bubble.addEventListener('click', function() {
        browser.classList.toggle('hidden');
        if (!browser.classList.contains('hidden') && repoSelect.options.length <= 1) {
            loadRepositories();
        }
    });

    // Close browser
    closeBtn.addEventListener('click', function() {
        browser.classList.add('hidden');
    });

    // Repository selection
    repoSelect.addEventListener('change', function() {
        const repoPath = repoSelect.value;
        if (!repoPath) return;

        const [owner, repo] = repoPath.split('/');
        currentOwner = owner;
        currentRepo = repo;
        currentPath = '';
        actions.classList.remove('hidden');

        loadContents(owner, repo, '');
    });

    // Back to listing
    backToListing.addEventListener('click', function() {
        fileContent.classList.add('hidden');
        contentListing.classList.remove('hidden');
    });

    // Create project from repo button
    document.getElementById('github-create-project').addEventListener('click', function() {
        if (!currentRepo || !currentOwner) return;

        const projectName = prompt('Enter a name for the new project:', currentRepo);
        if (!projectName) return;

        loading.classList.remove('hidden');
        contentListing.classList.add('hidden');
        fileContent.classList.add('hidden');

        fetch('/api/github/create-project', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                owner: currentOwner,
                repo: currentRepo,
                project_name: projectName
            })
        })
        .then(response => response.json())
        .then(data => {
            loading.classList.add('hidden');
            contentListing.classList.remove('hidden');

            if (data.success) {
                alert('Project creation has been started. You will be notified when it completes.');
                browser.classList.add('hidden');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            loading.classList.add('hidden');
            contentListing.classList.remove('hidden');
            alert('Error: ' + error.message);
        });
    });

    // Load repositories
    function loadRepositories() {
        loading.classList.remove('hidden');

        fetch('/api/github/repositories')
            .then(response => response.json())
            .then(data => {
                loading.classList.add('hidden');

                if (data.success) {
                    repoSelect.innerHTML = '<option value="">Select a repository...</option>';

                    data.data.repositories.forEach(repo => {
                        const option = document.createElement('option');
                        option.value = `${repo.owner.login}/${repo.name}`;
                        option.textContent = `${repo.name} (${repo.owner.login})`;
                        repoSelect.appendChild(option);
                    });
                } else {
                    alert('Error loading repositories: ' + data.message);
                }
            })
            .catch(error => {
                loading.classList.add('hidden');
                alert('Error loading repositories: ' + error.message);
            });
    }

    // Load repo contents
    function loadContents(owner, repo, path) {
        loading.classList.remove('hidden');
        contentListing.classList.add('hidden');
        fileContent.classList.add('hidden');

        const encodedPath = encodeURIComponent(path);
        fetch(`/api/github/contents/${owner}/${repo}/${encodedPath}`)
            .then(response => response.json())
            .then(data => {
                loading.classList.add('hidden');

                if (data.success) {
                    // Update breadcrumb
                    updateBreadcrumb(path);

                    // If it's a directory, show listing
                    contentListing.innerHTML = '';

                    if (Array.isArray(data.data.contents)) {
                        // It's a directory
                        contentListing.classList.remove('hidden');

                        // Add parent directory if not in root
                        if (path) {
                            const parentDir = document.createElement('div');
                            parentDir.className = 'flex items-center p-2 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md cursor-pointer';
                            parentDir.innerHTML = `
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-2 text-zinc-500 dark:text-zinc-400">
                                    <path d="M9 18l6-6-6-6"></path>
                                </svg>
                                <span class="text-zinc-600 dark:text-zinc-300">..</span>
                            `;
                            parentDir.addEventListener('click', function() {
                                const parentPath = path.includes('/')
                                    ? path.substring(0, path.lastIndexOf('/'))
                                    : '';
                                loadContents(owner, repo, parentPath);
                            });
                            contentListing.appendChild(parentDir);
                        }

                        // Sort directories first, then files
                        const contents = [...data.data.contents].sort((a, b) => {
                            if (a.type === b.type) return a.name.localeCompare(b.name);
                            return a.type === 'dir' ? -1 : 1;
                        });

                        contents.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'flex items-center p-2 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md cursor-pointer';
                            div.draggable = true;
                            div.dataset.path = item.path;
                            div.dataset.type = item.type;
                            div.dataset.name = item.name;

                            const icon = item.type === 'dir'
                                ? '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-2 text-blue-500"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>'
                                : '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-2 text-zinc-500 dark:text-zinc-400"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';

                            div.innerHTML = `
                                ${icon}
                                <span class="text-zinc-600 dark:text-zinc-300">${item.name}</span>
                            `;

                            // Click handler
                            div.addEventListener('click', function() {
                                if (item.type === 'dir') {
                                    loadContents(owner, repo, item.path);
                                } else {
                                    loadFileContent(owner, repo, item.path);
                                }
                            });

                            // Drag handler for files
                            if (item.type === 'file') {
                                div.addEventListener('dragstart', function(e) {
                                    e.dataTransfer.setData('text/plain', JSON.stringify({
                                        type: 'github-file',
                                        owner: owner,
                                        repo: repo,
                                        path: item.path,
                                        name: item.name
                                    }));
                                });
                            }

                            contentListing.appendChild(div);
                        });
                    } else {
                        // It's a file
                        fileContent.classList.remove('hidden');
                        filePreview.textContent = 'Loading file content...';
                        loadFileContent(owner, repo, path);
                    }
                } else {
                    contentListing.classList.remove('hidden');
                    contentListing.innerHTML = `<div class="p-4 text-red-500">Error: ${data.message}</div>`;
                }
            })
            .catch(error => {
                loading.classList.add('hidden');
                contentListing.classList.remove('hidden');
                contentListing.innerHTML = `<div class="p-4 text-red-500">Error: ${error.message}</div>`;
            });
    }

    // Load file content
    function loadFileContent(owner, repo, path) {
        loading.classList.remove('hidden');
        contentListing.classList.add('hidden');
        fileContent.classList.add('hidden');

        const encodedPath = encodeURIComponent(path);
        fetch(`/api/github/file/${owner}/${repo}/${encodedPath}`)
            .then(response => response.json())
            .then(data => {
                loading.classList.add('hidden');
                fileContent.classList.remove('hidden');

                if (data.success) {
                    currentPath = path;
                    currentFileContent = data.data.content;
                    filePreview.textContent = data.data.content;

                    // Update breadcrumb
                    updateBreadcrumb(path);

                    // Set up the "Use as context" button
                    useFileBtn.dataset.content = currentFileContent;
                    useFileBtn.dataset.path = path;
                } else {
                    filePreview.textContent = `Error: ${data.message}`;
                }
            })
            .catch(error => {
                loading.classList.add('hidden');
                fileContent.classList.remove('hidden');
                filePreview.textContent = `Error: ${error.message}`;
            });
    }

    // Update breadcrumb
    function updateBreadcrumb(path) {
        if (!path) {
            breadcrumb.classList.add('hidden');
            return;
        }

        breadcrumb.classList.remove('hidden');

        const parts = path.split('/');
        breadcrumb.innerHTML = '<span class="text-zinc-600 dark:text-zinc-400">Root</span>';

        let currentPath = '';
        parts.forEach((part, index) => {
            currentPath += (index === 0 ? '' : '/') + part;
            breadcrumb.innerHTML += `
                <span class="mx-1 text-zinc-400">/</span>
                <span class="text-zinc-600 dark:text-zinc-400 cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400"
                      data-path="${currentPath}">${part}</span>
            `;
        });

        // Add click handlers to breadcrumb parts
        breadcrumb.querySelectorAll('[data-path]').forEach(element => {
            element.addEventListener('click', function() {
                const pathToLoad = element.dataset.path;
                loadContents(currentOwner, currentRepo, pathToLoad);
            });
        });
    }

    // Use file as context
    useFileBtn.addEventListener('click', function() {
        // This will depend on where you are in the application
        // For now, store in localStorage and show a message
        localStorage.setItem('github-context', JSON.stringify({
            path: currentPath,
            content: currentFileContent,
            repo: currentRepo,
            owner: currentOwner
        }));

        alert(`File "${currentPath}" is now available as context. You can use it when creating test assets.`);
        browser.classList.add('hidden');
    });

    // Set up drag and drop functionality on the entire page
    document.addEventListener('dragover', function(e) {
        e.preventDefault(); // Allow drop
    });

    document.addEventListener('drop', function(e) {
        if (e.target.closest('[data-dropzone]')) {
            e.preventDefault();

            try {
                const data = JSON.parse(e.dataTransfer.getData('text/plain'));

                if (data.type === 'github-file') {
                    // Handle the dropped file based on the target
                    const dropzone = e.target.closest('[data-dropzone]');
                    const dropzoneType = dropzone.dataset.dropzone;

                    // Load the file content
                    fetch(`/api/github/file/${data.owner}/${data.repo}/${encodeURIComponent(data.path)}`)
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                // Handle different dropzone types
                                switch (dropzoneType) {
                                    case 'test-case':
                                        // Add file content to test case description or steps
                                        if (dropzone.querySelector('[name="description"]')) {
                                            dropzone.querySelector('[name="description"]').value +=
                                                `\n\nFrom GitHub file ${data.path}:\n${result.data.content}`;
                                        }
                                        break;
                                    case 'test-script':
                                        // Add file content to test script
                                        if (dropzone.querySelector('[name="script_content"]')) {
                                            dropzone.querySelector('[name="script_content"]').value = result.data.content;
                                        }
                                        break;
                                    // Add more cases as needed
                                }

                                alert(`File "${data.path}" has been added as context.`);
                            }
                        })
                        .catch(error => {
                            alert('Error loading file content: ' + error.message);
                        });
                }
            } catch (error) {
                console.error('Error processing dropped data:', error);
            }
        }
    });
});
</script>
@endif
