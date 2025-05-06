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
        'node_modules',
        'vendor',
        '.git',
        'public/build',
        'storage',
        'bootstrap/cache',
        'tests'
    ];
    protected array $ignoreFiles = [
        '.gitignore',
        '.env',
        '.env.example',
        'package-lock.json',
        'composer.lock'
    ];
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
        'yaml'
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
            $this->updateProgress(20, 'Collecting repository contents');

            // Collect repo contents
            list($files, $totalSize) = $this->collectRepositoryContents(
                $githubClient,
                $this->data['owner'],
                $this->data['repo'],
                $this->data['max_file_size'] ?? 1024 // Default to 1MB in KB
            );

            // Update progress to 50%
            $this->updateProgress(50, 'Processing ' . count($files) . ' files');

            if (count($files) === 0) {
                Log::warning('No files collected from repository', [
                    'repo' => $this->data['owner'] . '/' . $this->data['repo']
                ]);

                // Create a default test suite
                $this->createDefaultTestSuite($project);

                // Update progress to 100%
                $this->updateProgress(100, 'Project created with default test suite (no source files were collected)', true);

                // Notify user of completion
                $this->notifyCompletion($project->id, true, 'Project created, but no source files were collected.');
                return;
            }

            // Generate test suites and stories using collected files
            if ($this->data['auto_generate_tests'] ?? false) {
                $this->updateProgress(60, 'Generating test suites and stories');
                $this->generateTestSuitesAndStories($project, $files, $aiService);
            } else {
                $this->updateProgress(60, 'Creating default test suite');
                $this->createDefaultTestSuite($project);
            }

            // Update progress to 100%
            $this->updateProgress(100, 'Project created successfully', true);

            // Notify user of completion
            $this->notifyCompletion($project->id, true, 'Project created successfully.');
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
     * Collect repository contents and filter by file size
     */
    protected function collectRepositoryContents(
        GitHubApiClient $client,
        string $owner,
        string $repo,
        int $maxFileSizeKB = 1024
    ): array {
        $files = [];
        $totalSize = 0;
        $processedFiles = 0;
        $skippedFiles = 0;
        $queue = ['']; // Start with root directory
        $maxFileSizeBytes = $maxFileSizeKB * 1024;

        while (!empty($queue)) {
            $path = array_shift($queue);

            try {
                $contents = $client->getRepositoryContents($owner, $repo, $path);
                $processedItems = 0;
                $totalItems = count($contents);

                // Process each item in the directory
                foreach ($contents as $item) {
                    $processedItems++;

                    // Update progress occasionally
                    if ($processedItems % 10 === 0 || $processedItems === $totalItems) {
                        $progressMsg = "Scanning repository: $path ($processedItems/$totalItems)";
                        $progressPercentage = 20 + min(25, (int)(($processedFiles / ($processedFiles + count($queue))) * 25));
                        $this->updateProgress($progressPercentage, $progressMsg);
                    }

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
                            $skippedFiles++;
                            continue;
                        }

                        // Skip large files
                        if ($item['size'] > $maxFileSizeBytes) {
                            Log::info('Skipping large file', [
                                'path' => $item['path'],
                                'size' => $item['size'],
                                'max_size' => $maxFileSizeBytes
                            ]);
                            $skippedFiles++;
                            continue;
                        }

                        // Get file content
                        $content = $client->getFileContent($owner, $repo, $item['path']);

                        // Add file to collection
                        $files[] = [
                            'path' => $item['path'],
                            'name' => $fileName,
                            'content' => $content,
                            'size' => $item['size']
                        ];

                        $totalSize += $item['size'];
                        $processedFiles++;
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
            'total_size' => $totalSize,
            'processed_files' => $processedFiles,
            'skipped_files' => $skippedFiles
        ]);

        return [$files, $totalSize];
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

        $totalDirs = count($filesByDirectory);
        $processedDirs = 0;

        // Process each directory as a potential test suite
        foreach ($filesByDirectory as $directory => $directoryFiles) {
            $processedDirs++;
            $progressPercentage = 60 + (int)(($processedDirs / $totalDirs) * 35);
            $this->updateProgress($progressPercentage, "Generating test suite for: $directory");

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
                'job_id' => $this->data['job_id'] ?? null
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
