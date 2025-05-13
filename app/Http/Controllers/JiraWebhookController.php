<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\Project;
use App\Models\ProjectIntegration;
use App\Models\SyncMapping;
use App\Services\JiraSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Traits\JsonResponse;

class JiraWebhookController extends Controller
{
    use JsonResponse;

    /**
     * Handle Jira webhook
     */
    public function handle(Request $request)
    {
        // Validate webhook secret if configured
        $webhookSecret = config('services.jira.webhook_secret');

        if ($webhookSecret && $request->header('X-Jira-Webhook-Secret') !== $webhookSecret) {
            Log::warning('Invalid Jira webhook secret');
            return $this->errorResponse('Invalid webhook secret', 403);
        }

        // Log the webhook
        Log::info('Jira webhook received', [
            'event' => $request->header('X-Jira-Event-Type'),
            'webhook_id' => $request->header('X-Jira-Webhook-ID')
        ]);

        // Process the webhook
        try {
            $payload = $request->all();

            // Check if it's a valid webhook payload
            if (!isset($payload['webhookEvent'])) {
                return $this->errorResponse('Invalid webhook payload', 400);
            }

            $event = $payload['webhookEvent'];

            switch ($event) {
                case 'jira:issue_created':
                case 'jira:issue_updated':
                    $this->processIssueEvent($payload);
                    break;

                case 'jira:issue_deleted':
                    $this->processIssueDeleted($payload);
                    break;

                case 'comment_created':
                case 'comment_updated':
                    $this->processCommentEvent($payload);
                    break;

                default:
                    // Unsupported event
                    Log::info('Unsupported Jira webhook event', ['event' => $event]);
            }

            return $this->successResponse(['status' => 'processed']);

        } catch (\Exception $e) {
            Log::error('Error processing Jira webhook: ' . $e->getMessage());
            return $this->errorResponse('Error processing webhook: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Process issue created/updated events
     */
    protected function processIssueEvent(array $payload): void
    {
        $issueKey = $payload['issue']['key'] ?? null;

        if (!$issueKey) {
            Log::warning('Issue key not found in webhook payload');
            return;
        }

        // Check if we have this issue mapped
        $mapping = SyncMapping::where('external_system', 'jira')
            ->where('external_id', $issueKey)
            ->first();

        if ($mapping) {
            // We have this issue - need to update it
            $entityClass = $mapping->arxitest_type;
            $entity = $entityClass::find($mapping->arxitest_id);

            if ($entity) {
                // Get the project
                $project = null;

                if ($entity instanceof \App\Models\Story) {
                    $project = Project::find($entity->project_id);
                } elseif ($entity instanceof \App\Models\TestCase) {
                    $testSuite = $entity->testSuite;
                    if ($testSuite) {
                        $project = Project::find($testSuite->project_id);
                    }
                }

                if ($project) {
                    // Update the entity through sync service
                    $syncService = new JiraSyncService($project);

                    // Convert webhook data to issue format
                    $issue = [
                        'id' => $payload['issue']['id'],
                        'key' => $payload['issue']['key'],
                        'fields' => $payload['issue']['fields']
                    ];

                    if ($entity instanceof \App\Models\Story) {
                        $syncService->updateStoryFromJiraWebhook($entity, $issue);
                    } elseif ($entity instanceof \App\Models\TestCase) {
                        // Similar for test cases
                    }

                    // Update last sync
                    $mapping->update(['last_sync' => now()]);

                    Log::info("Updated entity from webhook: {$entity->id}");
                }
            }
        } else {
            // We don't have this issue mapped
            // Check project configs to see if we should import it
            $projectKey = $payload['issue']['fields']['project']['key'] ?? null;

            if ($projectKey) {
                // Find projects configured with this Jira project
                $projects = Project::whereRaw("JSON_CONTAINS(settings, '\"{$projectKey}\"', '$.jira_project_key')")
                    ->get();

                foreach ($projects as $project) {
                    // Check auto-import setting
                    if (($project->settings['jira_sync_settings']['auto_import'] ?? false) === true) {
                        // Auto-import is enabled
                        $syncService = new JiraSyncService($project);

                        // Convert webhook data to issue format
                        $issue = [
                            'id' => $payload['issue']['id'],
                            'key' => $payload['issue']['key'],
                            'fields' => $payload['issue']['fields']
                        ];

                        // Create new entity
                        $syncService->createEntityFromJiraWebhook($issue);

                        Log::info("Created entity from webhook for project: {$project->id}");
                    }
                }
            }
        }
    }

    /**
     * Process issue deleted events
     */
    protected function processIssueDeleted(array $payload): void
    {
        $issueKey = $payload['issue']['key'] ?? null;

        if (!$issueKey) {
            Log::warning('Issue key not found in webhook payload');
            return;
        }

        // Just remove the mapping if it exists
        $mapping = SyncMapping::where('external_system', 'jira')
            ->where('external_id', $issueKey)
            ->first();

        if ($mapping) {
            $mapping->delete();
            Log::info("Removed mapping for deleted issue: {$issueKey}");
        }
    }

    /**
     * Process comment events
     */
    protected function processCommentEvent(array $payload): void
    {
        $issueKey = $payload['issue']['key'] ?? null;

        if (!$issueKey) {
            Log::warning('Issue key not found in webhook payload');
            return;
        }

        // Check if we have this issue mapped
        $mapping = SyncMapping::where('external_system', 'jira')
            ->where('external_id', $issueKey)
            ->first();

        if ($mapping) {
            // Process comment
            // This would need entity-specific implementations for handling comments
            // For example, creating a note on the Story or TestCase
        }
    }
}
