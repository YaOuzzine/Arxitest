<?php

namespace App\Jobs;

use App\Models\Integration;
use App\Models\Project;
use App\Models\ProjectIntegration;
use App\Models\TestSuite;
use App\Models\Story;
use App\Services\GitHubApiClient;
use App\Services\AI\AIGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CreateProjectFromGitHubRepo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $data;
    protected array $ignoreDirs = [
        'node_modules', 'vendor', '.git', 'public/build', 'storage',
        'bootstrap/cache', 'tests'
    ];
    protected array $ignoreFiles = [
        '.gitignore', '.env', '.env.example', 'package-lock.json', 'composer.lock'
    ];
    protected array $fileExtensions = [
        'php', 'js', 'jsx', 'ts', 'tsx', 'py', 'rb', 'java', 'go', 'cs',
        'html', 'css', 'scss', 'vue', 'json', 'yml', 'yaml'
    ];

    // Estimated token counts per character for different file types
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
    ];

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(GitHubApiClient $githubClient, AIGenerationService $aiService): void
    {
        try {
            Log::info('Starting GitHub project creation job', [
                'repo' => $this->data['owner'] . '/' . $this->data['repo'],
                'project_name' => $this->data['project_name']
            ]);

            // Get integration and credentials
            $integration = ProjectIntegration::findOrFail($this->data['integration_id']);
            $credentials = json_decode(Crypt::decryptString($integration->encrypted_credentials), true);
            $accessToken = $credentials['access_token'];

            // Set up GitHub client
            $githubClient->setAccessToken($accessToken);

            // Get repository information
            $repository = $githubClient->getRepository($this->data['owner'], $this->data['repo']);

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

            // Collect repo contents
            list($files, $totalTokens) = $this->collectRepositoryContents(
                $githubClient,
                $this->data['owner'],
                $this->data['repo'],
                $this->data['max_files'],
                $this->data['max_tokens']
            );

            if (count($files) === 0) {
                Log::warning('No files collected from repository', [
                    'repo' => $this->data['owner'] . '/' . $this->data['repo']
                ]);

                // Create a default test suite
                $this->createDefaultTestSuite($project);

                // Notify user of completion
                $this->notifyCompletion($project->id, true, 'Project created, but no source files were collected.');
                return;
            }

            // Generate test suites and stories using collected files
            $this->generateTestSuitesAndStories($project, $files, $aiService);

            // Notify user of completion
            $this->notifyCompletion($project->id, true, 'Project created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create project from GitHub repo', [
                'repo' => $this->data['owner'] . '/' . $this->data['repo'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Notify user of failure
            $this->notifyCompletion(null, false, 'Failed to create project: ' . $e->getMessage());
        }
    }

    /**
     * Collect repository contents and estimate token usage
     */
    protected function collectRepositoryContents(
        GitHubApiClient $client,
        string $owner,
        string $repo,
        int $maxFiles = 20,
        int $maxTokens = 10000
    ): array {
        $files = [];
        $totalTokens = 0;
        $queue = ['']; // Start with root directory

        while (!empty($queue) && count($files) < $maxFiles && $totalTokens < $maxTokens) {
            $path = array_shift($queue);

            try {
                $contents = $client->getRepositoryContents($owner, $repo, $path);

                // Process each item in the directory
                foreach ($contents as $item) {
                    // Skip ignored directories
                    if ($item['type'] === 'dir') {
                        $dirName = basename($item['path']);
                        if (!in_array($dirName, $this->ignoreDirs)) {
                            $queue[] = $item['path'];
                        }
                        continue;
                    }

                    // Process files
                    if ($item['type'] === 'file') {
                        $fileName = basename($item['path']);
                        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

                        // Skip ignored files or non-code files
                        if (in_array($fileName, $this->ignoreFiles) || !in_array($extension, $this->fileExtensions)) {
                            continue;
                        }

                        // Get file content
                        $content = $client->getFileContent($owner, $repo, $item['path']);

                        // Estimate tokens
                        $ratio = $this->tokenRatios[$extension] ?? $this->tokenRatios['default'];
                        $estimatedTokens = ceil(strlen($content) * $ratio);

                        // Check if adding this file would exceed token limit
                        if ($totalTokens + $estimatedTokens > $maxTokens) {
                            Log::info('Reached token limit', [
                                'total_tokens' => $totalTokens,
                                'max_tokens' => $maxTokens
                            ]);
                            break 2; // Break out of both loops
                        }

                        // Add file to collection
                        $files[] = [
                            'path' => $item['path'],
                            'name' => $fileName,
                            'content' => $content,
                            'tokens' => $estimatedTokens
                        ];

                        $totalTokens += $estimatedTokens;

                        // Check if we've reached file limit
                        if (count($files) >= $maxFiles) {
                            Log::info('Reached file limit', [
                                'file_count' => count($files),
                                'max_files' => $maxFiles
                            ]);
                            break 2; // Break out of both loops
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error getting repository contents', [
                    'path' => $path,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        Log::info('Repository contents collected', [
            'file_count' => count($files),
            'total_tokens' => $totalTokens
        ]);

        return [$files, $totalTokens];
    }

    /**
     * Generate test suites and stories based on collected files
     */
    protected function generateTestSuitesAndStories(
        Project $project,
        array $files,
        AIGenerationService $aiService
    ): void {
        // Group files by directory
        $filesByDirectory = [];
        foreach ($files as $file) {
            $directory = dirname($file['path']);
            if ($directory === '.') {
                $directory = 'Root Directory';
            }

            if (!isset($filesByDirectory[$directory])) {
                $filesByDirectory[$directory] = [];
            }

            $filesByDirectory[$directory][] = $file;
        }

        // Process each directory as a potential test suite
        foreach ($filesByDirectory as $directory => $directoryFiles) {
            // If too many files in one directory, split them up
            $chunks = array_chunk($directoryFiles, min(5, ceil(count($directoryFiles) / 3)));

            foreach ($chunks as $index => $chunkFiles) {
                $suiteName = $directory;
                if (count($chunks) > 1) {
                    $suiteName .= " (Part " . ($index + 1) . ")";
                }

                // Create a context string from files
                $context = $this->buildContextFromFiles($chunkFiles);

                // Use AI to generate test suite
                try {
                    $suiteData = $aiService->generateTestSuite(
                        "Create a test suite for the code in: {$suiteName}",
                        [
                            'project_id' => $project->id,
                            'code' => $context,
                        ]
                    );

                    // Create story for this test suite
                    $story = new Story();
                    $story->project_id = $project->id;
                    $story->title = "Feature: " . substr($suiteData->name, 0, 80);
                    $story->description = "Implementation for {$directory} module.";
                    $story->source = 'github';
                    $story->metadata = [
                        'github_directory' => $directory,
                        'files_analyzed' => array_column($chunkFiles, 'path'),
                    ];
                    $story->save();

                    // Create a test case
                    $testCase = $aiService->generateTestCase(
                        "Create a test case for the main functionality in: {$suiteName}",
                        [
                            'suite_id' => $suiteData->id,
                            'story_id' => $story->id,
                            'code' => $context,
                        ]
                    );

                    Log::info('Created test suite and test case from GitHub directory', [
                        'directory' => $directory,
                        'suite_id' => $suiteData->id,
                        'story_id' => $story->id,
                        'test_case_id' => $testCase->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to generate test suite from GitHub directory', [
                        'directory' => $directory,
                        'error' => $e->getMessage()
                    ]);

                    // Create default test suite on error
                    $this->createDefaultTestSuite($project, $directory);
                }
            }
        }
    }

    /**
     * Build context string from files
     */
    protected function buildContextFromFiles(array $files): string
    {
        $context = '';

        foreach ($files as $file) {
            $context .= "--- File: {$file['path']} ---\n";
            $context .= "{$file['content']}\n\n";
        }

        return $context;
    }

    /**
     * Create a default test suite when we can't generate one
     */
    protected function createDefaultTestSuite(Project $project, ?string $name = null): TestSuite
    {
        $suite = new TestSuite();
        $suite->project_id = $project->id;
        $suite->name = $name ? "Tests for {$name}" : "Default Test Suite";
        $suite->description = "Default test suite created from GitHub repository";
        $suite->settings = [
            'default_priority' => 'medium',
            'execution_mode' => 'sequential',
        ];
        $suite->save();

        return $suite;
    }

    /**
     * Notify user of job completion
     */
    protected function notifyCompletion(?string $projectId, bool $success, string $message): void
    {
        if (isset($this->data['user_id'])) {
            $userId = $this->data['user_id'];

            // Create notification
            $notification = new \App\Models\Notification();
            $notification->actor_id = null; // System notification
            $notification->type = 'github_project_creation';
            $notification->data = [
                'message' => $message,
                'success' => $success,
                'project_id' => $projectId,
                'repository' => $this->data['owner'] . '/' . $this->data['repo'],
            ];
            $notification->save();

            // Link notification to user
            $userNotification = new \App\Models\UserNotification();
            $userNotification->notification_id = $notification->id;
            $userNotification->user_id = $userId;
            $userNotification->save();

            Log::info('GitHub project creation notification sent', [
                'user_id' => $userId,
                'success' => $success
            ]);
        }
    }
}
