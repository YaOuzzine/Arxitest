<?php

namespace App\Jobs;

use App\Models\Integration;
use App\Models\Project;
use App\Models\ProjectIntegration;
use App\Models\TestSuite;
use App\Models\Story;
use App\Models\TestCase;
use App\Models\TestData;
use App\Models\TestScript;
use App\Models\Environment;
use App\Services\GitHubApiClient;
use App\Services\AI\AIGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CreateProjectFromGitHubRepo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $data;

    // Directories to ignore when collecting files
    protected array $ignoreDirs = [
        'node_modules',
        'vendor',
        '.git',
        'public/build',
        'storage',
        'bootstrap/cache',
        'tests'
    ];

    // Files to ignore when collecting
    protected array $ignoreFiles = [
        '.gitignore',
        '.env',
        '.env.example',
        'package-lock.json',
        'composer.lock'
    ];

    // Priority files to include first
    protected array $priorityFiles = [
        'README.md',
        'readme.md',
        'README',
        'package.json',
        'composer.json',
        'config.json',
        '.npmrc',
        '.yarnrc',
        'tsconfig.json',
        'webpack.config.js',
        'vite.config.js'
    ];

    // File extensions to collect
    protected array $fileExtensions = [
        'php',
        'js',
        'jsx',
        'ts',
        'tsx',
        'py',
        'rb',
        'java',
        'go',
        'cs',
        'html',
        'css',
        'scss',
        'vue',
        'json',
        'yml',
        'yaml',
        'md'
    ];

    // Token estimation ratios for different file types
    protected array $tokenRatios = [
        'default' => 0.25, // 4 chars per token as a default
        'php' => 0.3,
        'js' => 0.3,
        'jsx' => 0.3,
        'ts' => 0.3,
        'tsx' => 0.3,
        'html' => 0.2,
        'css' => 0.2,
        'json' => 0.2,
        'md' => 0.2,
        'txt' => 0.15,
    ];

    // GPT-4.1 context limits
    protected int $maxInputTokens = 1000000; // 1M tokens
    protected int $tokenSafetyMargin = 50000; // 50K safety margin
    protected int $maxEffectiveTokens;
    protected int $tokensUsed = 0;

    // Token usage tracking
    protected array $tokenUsageStats = [
        'total_chars' => 0,
        'estimated_tokens' => 0,
        'files_included' => 0,
        'files_skipped' => 0,
        'directories_included' => 0
    ];

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->maxEffectiveTokens = $this->maxInputTokens - $this->tokenSafetyMargin;
    }

    /**
     * Execute the job.
     */
    public function handle(GitHubApiClient $githubClient): void
    {
        try {
            Log::info('Starting improved GitHub project creation job', [
                'repo' => $this->data['owner'] . '/' . $this->data['repo'],
                'project_name' => $this->data['project_name'],
                'job_id' => $this->data['job_id'] ?? null
            ]);

            $jobId = $this->data['job_id'] ?? null;

            // Update progress to 5%
            $this->updateProgress(5, 'Initializing project creation');

            // Get integration and credentials
            $integration = ProjectIntegration::findOrFail($this->data['integration_id']);
            $credentials = json_decode(Crypt::decryptString($integration->encrypted_credentials), true);
            $accessToken = $credentials['access_token'];

            // Set up GitHub client
            $githubClient->setAccessToken($accessToken);

            // Update progress to 10%
            $this->updateProgress(10, 'Fetching repository information');

            // Get repository information
            $repository = $githubClient->getRepository($this->data['owner'], $this->data['repo']);

            // Update progress to 15%
            $this->updateProgress(15, 'Creating project');

            // Create the project
            $project = new Project();
            $project->name = $this->data['project_name'];
            $project->description = "Created from GitHub repository: {$repository['full_name']}";
            $project->team_id = $this->data['team_id'];
            $project->settings = [
                'github_repo' => $repository['full_name'],
                'github_repo_url' => $repository['html_url'],
                'github_repo_description' => $repository['description'],
                'created_from_github' => true,
                'default_framework' => 'selenium-python',
            ];
            $project->save();

            // Update progress to 20%
            $this->updateProgress(20, 'Building project tree and collecting repository contents');

            // Get the project tree
            $projectTree = $this->buildProjectTree($githubClient, $this->data['owner'], $this->data['repo']);

            // Log the project tree for analysis
            Log::info('Project tree built', [
                'tree_size' => strlen(json_encode($projectTree)),
                'top_level_items' => count($projectTree)
            ]);

            // Collect key repository contents - now with token tracking
            $collectedData = $this->collectRepositoryContents(
                $githubClient,
                $this->data['owner'],
                $this->data['repo'],
                $projectTree,
                $this->data['max_file_size'] ?? 64 // Default to 64KB
            );

            // Update progress to 40%
            $this->updateProgress(
                40,
                "Collected {$this->tokenUsageStats['files_included']} files, with approximately {$this->tokenUsageStats['estimated_tokens']} tokens"
            );

            // Generate the comprehensive prompt for GPT-4.1
            $prompt = $this->generateAIPrompt($project, $repository, $collectedData);

            // Log prompt stats
            Log::info('Generated prompt for AI', [
                'prompt_length' => strlen($prompt),
                'estimated_prompt_tokens' => strlen($prompt) / 4, // Rough estimation
                'token_stats' => $this->tokenUsageStats
            ]);

            // Update progress to 50%
            $this->updateProgress(50, 'Sending request to advanced AI model for test generation');

            // Send to GPT-4.1
            $aiResponse = $this->sendToAdvancedAI($prompt);

            // Update progress to 70%
            $this->updateProgress(70, 'Processing AI response and creating Arxitest entities');

            // Process the AI response to create Arxitest entities
            $this->processAIResponse($project, $aiResponse);

            // Update progress to 100%
            $this->updateProgress(100, 'Project created successfully with comprehensive test structure', true);

            // Notify user of completion
            $this->notifyCompletion($project->id, true, 'Project created successfully with comprehensive test suite structure.');
        } catch (\Exception $e) {
            Log::error('Failed to create project from GitHub repo', [
                'repo' => $this->data['owner'] . '/' . $this->data['repo'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update progress to indicate error
            $this->updateProgress(100, 'Error: ' . $e->getMessage(), true, false);

            // Notify user of failure
            $this->notifyCompletion(null, false, 'Failed to create project: ' . $e->getMessage());
        }
    }

    /**
     * Build a tree representation of the project structure
     */
    protected function buildProjectTree(GitHubApiClient $client, string $owner, string $repo, string $path = ''): array
    {
        try {
            $contents = $client->getRepositoryContents($owner, $repo, $path);
            $tree = [];

            foreach ($contents as $item) {
                // Skip ignored directories and files
                $itemName = basename($item['path']);
                if ($item['type'] === 'dir' && in_array($itemName, $this->ignoreDirs)) {
                    continue;
                }
                if ($item['type'] === 'file' && in_array($itemName, $this->ignoreFiles)) {
                    continue;
                }

                // Add this item to the tree
                $treeItem = [
                    'name' => $itemName,
                    'path' => $item['path'],
                    'type' => $item['type'],
                    'size' => $item['size'] ?? 0
                ];

                // Recursively build tree for directories
                if ($item['type'] === 'dir') {
                    $treeItem['children'] = $this->buildProjectTree($client, $owner, $repo, $item['path']);
                    $this->tokenUsageStats['directories_included']++;
                }

                $tree[] = $treeItem;
            }

            return $tree;
        } catch (\Exception $e) {
            Log::warning("Error building project tree for path: {$path}", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Collect repository contents with token tracking
     */
    protected function collectRepositoryContents(
        GitHubApiClient $client,
        string $owner,
        string $repo,
        array $projectTree,
        int $maxFileSizeKB = 64
    ): array {
        $collectedData = [
            'project_structure' => $this->formatProjectTree($projectTree),
            'readme' => '',
            'config_files' => [],
            'code_files' => [],
        ];

        $maxFileSizeBytes = $maxFileSizeKB * 1024;

        // First pass: collect README and configuration files
        $readme = $this->findReadmeFile($projectTree);
        if ($readme) {
            try {
                $content = $client->getFileContent($owner, $repo, $readme['path']);
                $collectedData['readme'] = $content;

                // Track token usage
                $tokens = $this->estimateTokens($content, 'md');
                $this->tokensUsed += $tokens;
                $this->tokenUsageStats['total_chars'] += strlen($content);
                $this->tokenUsageStats['estimated_tokens'] += $tokens;
                $this->tokenUsageStats['files_included']++;

                Log::info('README file collected', [
                    'path' => $readme['path'],
                    'size' => strlen($content),
                    'estimated_tokens' => $tokens
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to get README content', [
                    'path' => $readme['path'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Second pass: collect priority config files
        $configFiles = $this->findConfigFiles($projectTree);
        foreach ($configFiles as $configFile) {
            if ($this->tokensUsed >= $this->maxEffectiveTokens) {
                Log::warning('Token limit reached during config files collection', [
                    'tokens_used' => $this->tokensUsed,
                    'token_limit' => $this->maxEffectiveTokens
                ]);
                break;
            }

            try {
                // Check file size before fetching
                if ($configFile['size'] > $maxFileSizeBytes) {
                    Log::info('Skipping large config file', [
                        'path' => $configFile['path'],
                        'size' => $configFile['size'],
                        'max_size' => $maxFileSizeBytes
                    ]);
                    $this->tokenUsageStats['files_skipped']++;
                    continue;
                }

                $content = $client->getFileContent($owner, $repo, $configFile['path']);
                $extension = pathinfo($configFile['path'], PATHINFO_EXTENSION);

                // Track token usage
                $tokens = $this->estimateTokens($content, $extension);

                // Check if adding this file would exceed token limit
                if ($this->tokensUsed + $tokens > $this->maxEffectiveTokens) {
                    Log::info('Skipping config file due to token limit', [
                        'path' => $configFile['path'],
                        'tokens' => $tokens,
                        'tokens_used' => $this->tokensUsed,
                        'token_limit' => $this->maxEffectiveTokens
                    ]);
                    $this->tokenUsageStats['files_skipped']++;
                    continue;
                }

                $this->tokensUsed += $tokens;
                $this->tokenUsageStats['total_chars'] += strlen($content);
                $this->tokenUsageStats['estimated_tokens'] += $tokens;
                $this->tokenUsageStats['files_included']++;

                $collectedData['config_files'][] = [
                    'path' => $configFile['path'],
                    'content' => $content,
                    'estimated_tokens' => $tokens
                ];

                Log::info('Config file collected', [
                    'path' => $configFile['path'],
                    'size' => strlen($content),
                    'estimated_tokens' => $tokens
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to get config file content', [
                    'path' => $configFile['path'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Third pass: collect code files with importance scoring
        $codeFiles = $this->findCodeFiles($projectTree);

        // Sort code files by importance (this is a simple implementation - can be made more sophisticated)
        $scoredFiles = $this->scoreFilesByImportance($codeFiles);

        foreach ($scoredFiles as $file) {
            if ($this->tokensUsed >= $this->maxEffectiveTokens) {
                Log::warning('Token limit reached during code files collection', [
                    'tokens_used' => $this->tokensUsed,
                    'token_limit' => $this->maxEffectiveTokens
                ]);
                break;
            }

            try {
                // Check file size before fetching
                if ($file['size'] > $maxFileSizeBytes) {
                    Log::info('Skipping large code file', [
                        'path' => $file['path'],
                        'size' => $file['size'],
                        'max_size' => $maxFileSizeBytes
                    ]);
                    $this->tokenUsageStats['files_skipped']++;
                    continue;
                }

                $content = $client->getFileContent($owner, $repo, $file['path']);
                $extension = pathinfo($file['path'], PATHINFO_EXTENSION);

                // Track token usage
                $tokens = $this->estimateTokens($content, $extension);

                // Check if adding this file would exceed token limit
                if ($this->tokensUsed + $tokens > $this->maxEffectiveTokens) {
                    Log::info('Skipping code file due to token limit', [
                        'path' => $file['path'],
                        'tokens' => $tokens,
                        'tokens_used' => $this->tokensUsed,
                        'token_limit' => $this->maxEffectiveTokens
                    ]);
                    $this->tokenUsageStats['files_skipped']++;
                    continue;
                }

                $this->tokensUsed += $tokens;
                $this->tokenUsageStats['total_chars'] += strlen($content);
                $this->tokenUsageStats['estimated_tokens'] += $tokens;
                $this->tokenUsageStats['files_included']++;

                $collectedData['code_files'][] = [
                    'path' => $file['path'],
                    'content' => $content,
                    'importance_score' => $file['importance_score'],
                    'estimated_tokens' => $tokens
                ];

                Log::info('Code file collected', [
                    'path' => $file['path'],
                    'size' => strlen($content),
                    'estimated_tokens' => $tokens,
                    'importance_score' => $file['importance_score']
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to get code file content', [
                    'path' => $file['path'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $collectedData;
    }

    /**
     * Format project tree for inclusion in the prompt
     */
    protected function formatProjectTree(array $tree, int $depth = 0): string
    {
        $result = '';
        $indent = str_repeat('  ', $depth);

        foreach ($tree as $item) {
            if ($item['type'] === 'dir') {
                $result .= $indent . "📁 {$item['name']}/\n";
                if (isset($item['children']) && !empty($item['children'])) {
                    $result .= $this->formatProjectTree($item['children'], $depth + 1);
                }
            } else {
                $result .= $indent . "📄 {$item['name']}\n";
            }
        }

        return $result;
    }

    /**
     * Find README file in project tree
     */
    protected function findReadmeFile(array $tree): ?array
    {
        foreach ($tree as $item) {
            if (
                $item['type'] === 'file' &&
                preg_match('/^readme(\.md)?$/i', $item['name'])
            ) {
                return $item;
            }

            if ($item['type'] === 'dir' && isset($item['children'])) {
                $found = $this->findReadmeFile($item['children']);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Find configuration files in project tree
     */
    protected function findConfigFiles(array $tree): array
    {
        $configFiles = [];

        foreach ($tree as $item) {
            if (
                $item['type'] === 'file' &&
                (in_array($item['name'], $this->priorityFiles) ||
                    preg_match('/\.(json|yml|yaml|toml|xml|config)$/i', $item['name']))
            ) {
                $configFiles[] = $item;
            }

            if ($item['type'] === 'dir' && isset($item['children'])) {
                $configFiles = array_merge($configFiles, $this->findConfigFiles($item['children']));
            }
        }

        return $configFiles;
    }

    /**
     * Find code files in project tree
     */
    protected function findCodeFiles(array $tree): array
    {
        $codeFiles = [];

        foreach ($tree as $item) {
            if ($item['type'] === 'file') {
                $extension = pathinfo($item['name'], PATHINFO_EXTENSION);
                if (
                    in_array(strtolower($extension), $this->fileExtensions) &&
                    !in_array($item['name'], $this->priorityFiles)
                ) {
                    $codeFiles[] = $item;
                }
            }

            if ($item['type'] === 'dir' && isset($item['children'])) {
                $codeFiles = array_merge($codeFiles, $this->findCodeFiles($item['children']));
            }
        }

        return $codeFiles;
    }

    /**
     * Score files by their importance for test generation
     */
    protected function scoreFilesByImportance(array $files): array
    {
        foreach ($files as &$file) {
            $score = 0;
            $path = strtolower($file['path']);
            $name = strtolower($file['name']);

            // Score based on file path and name
            if (strpos($path, 'model') !== false || strpos($path, 'entity') !== false) {
                $score += 10; // Models/entities are important for understanding data structure
            }
            if (strpos($path, 'controller') !== false || strpos($path, 'service') !== false) {
                $score += 8; // Controllers/services show business logic
            }
            if (strpos($path, 'api') !== false || strpos($path, 'route') !== false) {
                $score += 7; // API/routes show endpoints
            }
            if (strpos($path, 'util') !== false || strpos($path, 'helper') !== false) {
                $score += 5; // Utils/helpers
            }
            if (strpos($path, 'component') !== false || strpos($path, 'view') !== false) {
                $score += 6; // UI components
            }

            // Score based on file extension
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            switch (strtolower($extension)) {
                case 'php':
                case 'js':
                case 'ts':
                case 'py':
                    $score += 5; // Core language files
                    break;
                case 'jsx':
                case 'tsx':
                    $score += 4; // React/UI files
                    break;
                case 'html':
                case 'css':
                case 'scss':
                    $score += 3; // Frontend files
                    break;
                default:
                    $score += 1;
            }

            // Size factor (smaller files often contain more concentrated logic)
            $sizeKB = ($file['size'] ?? 0) / 1024;
            if ($sizeKB < 5) {
                $score += 3;
            } else if ($sizeKB < 20) {
                $score += 2;
            } else if ($sizeKB < 50) {
                $score += 1;
            }

            $file['importance_score'] = $score;
        }

        // Sort by importance score (descending)
        usort($files, function ($a, $b) {
            return $b['importance_score'] - $a['importance_score'];
        });

        return $files;
    }

    /**
     * A quick token estimation algorithm without requiring a full tokenizer
     */
    protected function estimateTokens(string $text, string $fileType = 'default'): int
    {
        $fileType = strtolower($fileType);
        $ratio = $this->tokenRatios[$fileType] ?? $this->tokenRatios['default'];

        // Simple heuristic: whitespace and punctuation adjustment
        $wordCount = count(preg_split('/\s+/', $text));
        $punctuationCount = preg_match_all('/[.,:;!?()[\]{}"`\']/u', $text, $matches);

        // Estimate based on characters plus additional token-splitting rules
        return (int) ceil(strlen($text) * $ratio + ($punctuationCount * 0.1));
    }
    /**
     * Generate prompt for the AI to create comprehensive test structure
     */
    protected function generateAIPrompt(Project $project, array $repository, array $collectedData): string
    {
        $projectName = $project->name;
        $repoName = $repository['name'];
        $repoDescription = $repository['description'] ?? "No description available";
        $repoUrl = $repository['html_url'];

        $projectStructure = $collectedData['project_structure'];
        $readme = $collectedData['readme'];

        // Format config files section
        $configFilesContent = '';
        foreach ($collectedData['config_files'] as $file) {
            $configFilesContent .= "FILE: {$file['path']}\n```\n{$file['content']}\n```\n\n";
        }

        // Format code files section - limit based on token count
        $codeFilesContent = '';
        foreach ($collectedData['code_files'] as $file) {
            $codeFilesContent .= "FILE: {$file['path']} (Importance: {$file['importance_score']})\n```\n{$file['content']}\n```\n\n";
        }

        // Create the master prompt
        $prompt = <<<EOT
# Comprehensive Test Structure Generation for Arxitest Project

## Repository Information
- Project Name: {$projectName}
- Repository: {$repoName}
- Description: {$repoDescription}
- URL: {$repoUrl}

## Your Task
You need to create a comprehensive testing structure for this project, including test suites, user stories, test cases, test data, test scripts and environment configurations.

The test structure should follow Arxitest's format, which includes:

1. **Test Suites** - Logical groupings of related functionality (not one test case per suite)
2. **User Stories** - Following the format "As a [user role], I want [goal] so that [reason]"
3. **Test Cases** - Detailed verification steps for each user story
4. **Test Data** - Sample data for test execution
5. **Test Scripts** - Executable test scripts (Selenium Python or similar)
6. **Environments** - Test environment configurations

## Project Structure
{$projectStructure}

## README
{$readme}

## Configuration Files
{$configFilesContent}

## Code Files
{$codeFilesContent}

## Required Output Format
Please respond with a structured JSON that contains all necessary elements for creating a comprehensive Arxitest project. Follow this exact format:

```json
{
  "test_suites": [
    {
      "name": "Suite name",
      "description": "Suite description",
      "stories": [
        {
          "title": "Story title (As a X, I want Y, so that Z)",
          "description": "Detailed story description",
          "test_cases": [
            {
              "title": "Test case title",
              "description": "Test case description",
              "steps": ["Step 1", "Step 2", "..."],
              "expected_results": "Expected results",
              "priority": "high|medium|low",
              "test_data": {
                "name": "Test data name",
                "format": "json|csv|xml|plain",
                "content": "Test data content"
              },
              "test_script": {
                "name": "Script name",
                "framework_type": "selenium-python",
                "content": "Python script code"
              }
            }
          ]
        }
      ]
    }
  ],
  "environments": [
    {
      "name": "Environment name",
      "description": "Environment description",
      "configuration": {
        "key1": "value1",
        "key2": "value2"
      }
    }
  ]
}
Guidelines:

Each test suite should be focused on a logical component or feature area
User stories must follow "As a [user role], I want [goal] so that [reason]" format
Each user story should have 2-5 test cases covering different aspects
Each test case must include steps, expected results, and relevant test data
Test scripts should be real, executable code that would work with the actual application
Include at least development, staging, and production environments
The structure should be comprehensive and professional
Base the structure on your understanding of what the project does

Do not include placeholder text or "lorem ipsum" - everything should be detailed and specific to this project.
EOT;
        return $prompt;
    }

    /**
     * Send request to advanced AI model (GPT-4.1) with rate limit handling
     */
    protected function sendToAdvancedAI(string $prompt): array
    {
        // API configuration
        $apiKey = config('ai.providers.openai.api_key');
        $model = 'gpt-4.1'; // The advanced model

        // Break down the files data into smaller chunks to avoid rate limits
        $chunks = $this->chunkRepositoryData($prompt);
        Log::info('Split prompt into chunks for processing', [
            'chunks_count' => count($chunks),
            'original_tokens' => $this->tokensUsed
        ]);

        $combinedResults = [
            'test_suites' => [],
            'environments' => []
        ];

        // Process each chunk with appropriate waiting time between requests
        foreach ($chunks as $index => $chunk) {
            Log::info('Processing chunk', [
                'chunk_index' => $index + 1,
                'chunk_size' => strlen($chunk['prompt']),
                'estimated_tokens' => $chunk['tokens']
            ]);

            $success = false;
            $attempts = 0;
            $maxAttempts = 5;
            $backoffSeconds = 2;

            // Update progress to show current chunk
            $this->updateProgress(
                50 + (int)((30 * $index) / count($chunks)),
                "Processing data chunk " . ($index + 1) . " of " . count($chunks)
            );

            while (!$success && $attempts < $maxAttempts) {
                try {
                    $attempts++;

                    // If not the first attempt, wait with exponential backoff
                    if ($attempts > 1) {
                        $sleepTime = $backoffSeconds * (2 ** ($attempts - 2));
                        Log::info("Rate limit hit, backing off", [
                            'attempt' => $attempts,
                            'sleep_seconds' => $sleepTime
                        ]);
                        sleep($sleepTime);
                    }

                    $headers = [
                        'Authorization' => "Bearer {$apiKey}",
                        'Content-Type' => 'application/json'
                    ];

                    $data = [
                        'model' => $model,
                        'messages' => [
                            ['role' => 'system', 'content' => $chunk['system']],
                            ['role' => 'user', 'content' => $chunk['prompt']]
                        ],
                        'response_format' => ['type' => 'json_object'],
                        'temperature' => 0.7
                    ];

                    Log::info('Sending chunk to AI', [
                        'chunk_index' => $index + 1,
                        'estimated_tokens' => $chunk['tokens']
                    ]);

                    $response = Http::withHeaders($headers)
                        ->timeout(180)  // 3 minute timeout for large requests
                        ->post('https://api.openai.com/v1/chat/completions', $data);

                    if ($response->failed()) {
                        $statusCode = $response->status();
                        $body = $response->body();

                        // Check if it's a rate limit error
                        if ($statusCode === 429) {
                            // Let the loop retry with backoff
                            Log::warning('Rate limit hit, will retry', [
                                'status' => $statusCode,
                                'body' => $body,
                                'attempt' => $attempts
                            ]);
                            continue;
                        }

                        // Other error
                        Log::error('Failed to get response from AI', [
                            'status' => $statusCode,
                            'body' => $body
                        ]);
                        throw new \Exception('Failed to get response from AI: ' . $statusCode . ' ' . $body);
                    }

                    $responseData = $response->json();
                    $content = $responseData['choices'][0]['message']['content'] ?? null;

                    if (empty($content)) {
                        throw new \Exception('Empty response from AI');
                    }

                    Log::info('Received response from AI for chunk', [
                        'chunk_index' => $index + 1,
                        'response_size' => strlen($content),
                        'completion_tokens' => $responseData['usage']['completion_tokens'] ?? 'unknown'
                    ]);

                    // Parse JSON response
                    $parsed = json_decode($content, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception('Failed to parse JSON response: ' . json_last_error_msg());
                    }

                    // Merge the results from this chunk with the combined results
                    $this->mergeResults($combinedResults, $parsed);
                    $success = true;
                } catch (\Exception $e) {
                    if ($attempts >= $maxAttempts) {
                        Log::error('Maximum retry attempts reached for chunk ' . ($index + 1), [
                            'error' => $e->getMessage()
                        ]);
                        throw $e;
                    }

                    Log::warning('Error processing chunk, will retry', [
                        'chunk_index' => $index + 1,
                        'attempt' => $attempts,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Add a delay between chunks to respect rate limits
            if ($index < count($chunks) - 1) {
                sleep(10); // Wait 10 seconds between chunks
            }
        }

        return $combinedResults;
    }

    /**
     * Chunk the repository data into smaller pieces to avoid rate limits
     */
    protected function chunkRepositoryData(string $fullPrompt): array
    {
        // Extract the system and context parts
        $systemPrompt = 'You are a specialized AI for creating comprehensive test structures. Your response should be valid JSON following the specified format.';

        // Split the full prompt into sections
        $sections = $this->splitPromptIntoSections($fullPrompt);

        // Basic task description and required output format - always include these
        $basePrompt = $sections['intro'] . "\n\n" . $sections['required_format'];
        $baseTokens = $this->estimateTokens($basePrompt);

        // Calculate max tokens per chunk (leaving room for system prompt and response)
        $maxTokensPerChunk = 20000; // Safe limit well below the 30k TPM
        $availableTokens = $maxTokensPerChunk - $baseTokens - $this->estimateTokens($systemPrompt) - 2000; // Buffer for response

        $chunks = [];

        // First chunk always includes the README and project structure
        $firstChunkContent = $basePrompt . "\n\n## Project Structure\n" . $sections['project_structure'];

        if (isset($sections['readme'])) {
            $firstChunkContent .= "\n\n## README\n" . $sections['readme'];
        }

        $firstChunkTokens = $this->estimateTokens($firstChunkContent);

        $chunks[] = [
            'system' => $systemPrompt,
            'prompt' => $firstChunkContent,
            'tokens' => $firstChunkTokens
        ];

        // Now handle config and code files
        $fileChunks = [];
        $currentChunk = '';
        $currentTokens = 0;

        // Process config files first
        if (isset($sections['config_files'])) {
            $configFilesSection = "## Configuration Files\n" . $sections['config_files'];
            $configTokens = $this->estimateTokens($configFilesSection);

            if ($currentTokens + $configTokens <= $availableTokens) {
                $currentChunk .= "\n\n" . $configFilesSection;
                $currentTokens += $configTokens;
            } else {
                $fileChunks[] = [
                    'content' => "## Configuration Files\n" . $sections['config_files'],
                    'tokens' => $configTokens
                ];
            }
        }

        // Process code files section
        if (isset($sections['code_files'])) {
            // Split code files into individual file entries
            $codeFiles = $this->splitCodeFilesSection($sections['code_files']);

            foreach ($codeFiles as $file) {
                $fileTokens = $this->estimateTokens($file);

                if ($currentTokens + $fileTokens <= $availableTokens) {
                    $currentChunk .= "\n\n" . $file;
                    $currentTokens += $fileTokens;
                } else {
                    // Current chunk is full, add it to fileChunks
                    if (!empty($currentChunk)) {
                        $fileChunks[] = [
                            'content' => $currentChunk,
                            'tokens' => $currentTokens
                        ];
                    }

                    // Start a new chunk
                    $currentChunk = $file;
                    $currentTokens = $fileTokens;
                }
            }
        }

        // Add the last chunk if not empty
        if (!empty($currentChunk)) {
            $fileChunks[] = [
                'content' => $currentChunk,
                'tokens' => $currentTokens
            ];
        }

        // Create the actual chunks with base prompt + file chunks
        foreach ($fileChunks as $chunk) {
            $chunkPrompt = $basePrompt . "\n\n" . $chunk['content'];
            $chunks[] = [
                'system' => $systemPrompt,
                'prompt' => $chunkPrompt,
                'tokens' => $baseTokens + $chunk['tokens']
            ];
        }

        return $chunks;
    }

    /**
     * Split the full prompt into logical sections
     */
    protected function splitPromptIntoSections(string $prompt): array
    {
        $sections = [];

        // Extract introduction section
        if (preg_match('/^(.*?)(?=## Project Structure)/s', $prompt, $matches)) {
            $sections['intro'] = trim($matches[0]);
        }

        // Extract project structure section
        if (preg_match('/## Project Structure(.*?)(?=##|$)/s', $prompt, $matches)) {
            $sections['project_structure'] = trim($matches[1]);
        }

        // Extract readme section
        if (preg_match('/## README(.*?)(?=##|$)/s', $prompt, $matches)) {
            $sections['readme'] = trim($matches[1]);
        }

        // Extract configuration files section
        if (preg_match('/## Configuration Files(.*?)(?=##|$)/s', $prompt, $matches)) {
            $sections['config_files'] = trim($matches[1]);
        }

        // Extract code files section
        if (preg_match('/## Code Files(.*?)(?=##|$)/s', $prompt, $matches)) {
            $sections['code_files'] = trim($matches[1]);
        }

        // Extract required output format section
        if (preg_match('/## Required Output Format(.*?)$/s', $prompt, $matches)) {
            $sections['required_format'] = trim($matches[1]);
        }

        return $sections;
    }

    /**
     * Split code files section into individual file entries
     */
    protected function splitCodeFilesSection(string $codeFilesSection): array
    {
        $files = [];
        $pattern = '/FILE: (.*?)(?=FILE:|$)/s';

        if (preg_match_all($pattern, $codeFilesSection, $matches)) {
            foreach ($matches[0] as $fileContent) {
                $files[] = trim($fileContent);
            }
        }

        return $files;
    }

    /**
     * Merge results from multiple API responses
     */
    protected function mergeResults(array &$combinedResults, array $chunkResults): void
    {
        // Merge test suites
        if (isset($chunkResults['test_suites']) && is_array($chunkResults['test_suites'])) {
            foreach ($chunkResults['test_suites'] as $suite) {
                $combinedResults['test_suites'][] = $suite;
            }
        }

        // Merge environments
        if (isset($chunkResults['environments']) && is_array($chunkResults['environments'])) {
            foreach ($chunkResults['environments'] as $env) {
                $combinedResults['environments'][] = $env;
            }
        }
    }

    /**
     * Process AI response to create Arxitest entities
     */
    protected function processAIResponse(Project $project, array $aiResponse): void
    {
        Log::info('AI response received for GitHub project creation', [
            'project_id' => $project->id,
            'project_name' => $project->name,
            'suites_count' => count($aiResponse['test_suites'] ?? []),
            'environments_count' => count($aiResponse['environments'] ?? [])
        ]);

        $logFilePath = storage_path('logs/ai_responses/project_' . $project->id . '_' . date('Y-m-d_H-i-s') . '.json');
        if (!file_exists(dirname($logFilePath))) {
            mkdir(dirname($logFilePath), 0755, true);
        }

        // Write response to file
        file_put_contents($logFilePath, json_encode($aiResponse, JSON_PRETTY_PRINT));

        Log::info('AI response logged to file', ['file_path' => $logFilePath]);

        // Create environments
        $environments = [];
        foreach ($aiResponse['environments'] ?? [] as $envData) {
            $environment = new Environment();

            // Get project name and default environment name
            $projectName = Str::limit($project->name, 30); // Limit length for readability
            $originalEnvName = $envData['name'];

            // Format environment name based on type
            if (stripos($originalEnvName, 'prod') !== false || stripos($originalEnvName, 'production') !== false) {
                $envType = 'Production';
            } elseif (stripos($originalEnvName, 'stage') !== false || stripos($originalEnvName, 'staging') !== false) {
                $envType = 'Staging';
            } elseif (stripos($originalEnvName, 'dev') !== false || stripos($originalEnvName, 'development') !== false) {
                $envType = 'Development';
            } elseif (stripos($originalEnvName, 'test') !== false || stripos($originalEnvName, 'qa') !== false) {
                $envType = 'Testing';
            } else {
                $envType = $originalEnvName;
            }

            // Create standardized name format
            $environment->name = "{$projectName} - {$envType}";

            // Ensure uniqueness
            $counter = 1;
            $baseName = $environment->name;
            while (Environment::where('name', $environment->name)->exists()) {
                $environment->name = "{$baseName} ({$counter})";
                $counter++;
            }

            $environment->is_global = false;
            $environment->is_active = true;

            // Store original name in configuration for reference
            $envConfig = $envData['configuration'] ?? [];
            $envConfig['original_name'] = $originalEnvName;
            $environment->configuration = $envConfig;

            $environment->save();

            // Link environment to project
            $project->environments()->attach($environment->id);

            $environments[] = $environment;

            Log::info('Created environment', [
                'environment_id' => $environment->id,
                'name' => $environment->name,
                'original_name' => $originalEnvName,
                'project' => $projectName
            ]);
        }

        // Process test suites
        foreach ($aiResponse['test_suites'] ?? [] as $suiteData) {
            // Create test suite
            $suite = new TestSuite();
            $suite->project_id = $project->id;
            $suite->name = $suiteData['name'];
            $suite->description = $suiteData['description'] ?? '';
            $suite->settings = [
                'default_priority' => 'medium',
                'execution_mode' => 'sequential',
                'created_from_ai' => true
            ];
            $suite->save();

            Log::info('Created test suite', [
                'suite_id' => $suite->id,
                'name' => $suite->name,
                'stories_count' => count($suiteData['stories'] ?? [])
            ]);

            // Process user stories
            foreach ($suiteData['stories'] ?? [] as $storyData) {
                // Create story
                $story = new Story();
                $story->project_id = $project->id;
                $story->title = $storyData['title'];
                $story->description = $storyData['description'] ?? '';
                $story->source = 'github';
                $story->metadata = [
                    'created_through' => 'ai',
                    'github_repo' => $this->data['owner'] . '/' . $this->data['repo']
                ];
                $story->save();

                Log::info('Created story', [
                    'story_id' => $story->id,
                    'title' => $story->title,
                    'test_cases_count' => count($storyData['test_cases'] ?? [])
                ]);

                // Process test cases
                foreach ($storyData['test_cases'] ?? [] as $caseData) {
                    // Create test case
                    $testCase = new TestCase();
                    $testCase->suite_id = $suite->id;
                    $testCase->story_id = $story->id;
                    $testCase->title = $caseData['title'];
                    $testCase->description = $caseData['description'] ?? '';
                    $testCase->expected_results = $caseData['expected_results'] ?? '';
                    $testCase->steps = $caseData['steps'] ?? [];
                    $testCase->priority = $caseData['priority'] ?? 'medium';
                    $testCase->status = 'draft';
                    $testCase->tags = ['github', 'auto-generated'];
                    $testCase->save();

                    Log::info('Created test case', [
                        'test_case_id' => $testCase->id,
                        'title' => $testCase->title
                    ]);

                    // Create test data if available
                    if (isset($caseData['test_data']) && !empty($caseData['test_data'])) {
                        $testData = new TestData();
                        $testData->name = $caseData['test_data']['name'] ?? "Data for {$testCase->title}";
                        $testData->content = $caseData['test_data']['content'] ?? '';
                        $testData->format = $caseData['test_data']['format'] ?? 'json';
                        $testData->is_sensitive = false;
                        $testData->metadata = [
                            'created_through' => 'ai',
                            'created_at' => now()->toDateTimeString()
                        ];
                        $testData->save();

                        // Associate data with test case
                        $pivotData = new \App\Models\TestCaseData();
                        $pivotData->test_case_id = $testCase->id;
                        $pivotData->test_data_id = $testData->id;
                        $pivotData->usage_context = 'Generated with AI';
                        $pivotData->save();

                        Log::info('Created test data', [
                            'test_data_id' => $testData->id,
                            'name' => $testData->name
                        ]);
                    }

                    // Create test script if available
                    if (isset($caseData['test_script']) && !empty($caseData['test_script'])) {
                        $testScript = new TestScript();
                        $testScript->test_case_id = $testCase->id;
                        $testScript->creator_id = $this->data['user_id'] ?? null;
                        $testScript->name = $caseData['test_script']['name'] ?? "Script for {$testCase->title}";
                        $testScript->framework_type = $caseData['test_script']['framework_type'] ?? 'selenium-python';
                        $testScript->script_content = $caseData['test_script']['content'] ?? '';
                        $testScript->metadata = [
                            'created_through' => 'ai',
                            'created_at' => now()->toDateTimeString()
                        ];
                        $testScript->save();

                        Log::info('Created test script', [
                            'test_script_id' => $testScript->id,
                            'name' => $testScript->name
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Update job progress
     */
    protected function updateProgress(int $percentage, string $status, bool $completed = false, bool $success = true): void
    {
        $jobId = $this->data['job_id'] ?? null;

        if (!$jobId) {
            return;
        }

        $cacheKey = "github_project_progress_{$jobId}";
        $progressData = cache()->get($cacheKey, [
            'progress' => 0,
            'status' => 'initializing',
            'team_id' => $this->data['team_id'],
            'started_at' => now()->timestamp,
            'job_id' => $jobId
        ]);

        $progressData['progress'] = $percentage;
        $progressData['status'] = $status;

        if ($completed) {
            $progressData['completed'] = true;
            $progressData['completed_at'] = now()->timestamp;
            $progressData['success'] = $success;
            $progressData['duration'] = now()->timestamp - ($progressData['started_at'] ?? now()->timestamp);

            // Add token usage stats for completed jobs
            $progressData['token_stats'] = $this->tokenUsageStats;
        }

        cache()->put($cacheKey, $progressData, 3600); // Store for 1 hour

        Log::info('Updated job progress', [
            'job_id' => $jobId,
            'progress' => $percentage,
            'status' => $status,
            'completed' => $completed
        ]);
    }

    /**
     * Create notification for user
     */
    protected function notifyCompletion(?string $projectId, bool $success, string $message): void
    {
        if (!isset($this->data['user_id'])) {
            return;
        }

        try {
            // Create the notification
            $notification = new \App\Models\Notification();
            $notification->actor_id = null;
            $notification->type = 'github_project_creation';
            $notification->data = [
                'message' => $message,
                'success' => $success,
                'project_id' => $projectId,
                'repository' => $this->data['owner'] . '/' . $this->data['repo'],
                'job_id' => $this->data['job_id'] ?? null,
                'token_stats' => $this->tokenUsageStats
            ];
            $notification->save();

            // Directly insert the relationship record with a DB query
            \Illuminate\Support\Facades\DB::insert(
                'INSERT INTO user_notifications (notification_id, user_id, is_read) VALUES (?, ?, ?)',
                [$notification->id, $this->data['user_id'], false]
            );

            Log::info('GitHub project creation notification sent', [
                'user_id' => $this->data['user_id'],
                'success' => $success
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating notification: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            // Don't let notification errors stop the process
        }
    }
}
