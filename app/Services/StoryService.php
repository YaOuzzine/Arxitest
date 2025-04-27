<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Story;
use App\Models\Team;
use App\Models\Epic;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoryService
{
    /**
     * Get stories for a team with filtering options
     */
    public function getStoriesForTeam(Team $team, array $filters = []): LengthAwarePaginator
    {
        // Get project IDs for the team
        $projectIds = $team->projects()->pluck('id');

        if ($projectIds->isEmpty()) {
            return Story::where('id', null)->paginate(10); // Empty paginator
        }

        // Base query for stories that belong to the current team's projects
        $query = Story::whereIn('project_id', $projectIds);

        // Apply project filter
        if (isset($filters['project_id']) && !empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        // Apply epic filter
        if (isset($filters['epic_id']) && !empty($filters['epic_id'])) {
            $query->where('epic_id', $filters['epic_id']);
        }

        // Apply test case filter
        if (isset($filters['test_case_id']) && !empty($filters['test_case_id'])) {
            $query->whereHas('testCases', function ($q) use ($filters) {
                $q->where('id', $filters['test_case_id']);
            });
        }

        // Apply source filter (multiple sources can be selected)
        if (isset($filters['sources']) && !empty($filters['sources'])) {
            $query->whereIn('source', $filters['sources']);
        }

        // Apply search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhere('external_id', 'like', $searchTerm);
            });
        }

        // Apply sorting
        $sortField = $filters['sort'] ?? 'updated_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        $allowedSortFields = ['title', 'created_at', 'updated_at', 'source', 'external_id'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        return $query->paginate(10)->withQueryString();
    }

    /**
     * Get stories for a specific project with filtering options
     */
    public function getStoriesForProject(Project $project, array $filters = []): LengthAwarePaginator
    {
        // Base query for stories in this project
        $query = Story::where('project_id', $project->id);

        // Apply epic filter
        if (isset($filters['epic_id']) && !empty($filters['epic_id'])) {
            $query->where('epic_id', $filters['epic_id']);
        }

        // Apply source filter
        if (isset($filters['sources']) && !empty($filters['sources'])) {
            $query->whereIn('source', $filters['sources']);
        }

        // Apply search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhere('external_id', 'like', $searchTerm);
            });
        }

        // Apply sorting
        $sortField = $filters['sort'] ?? 'updated_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        $allowedSortFields = ['title', 'created_at', 'updated_at', 'source', 'external_id'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        return $query->paginate(10)->withQueryString();
    }

    /**
     * Get projects for the team
     */
    public function getProjectsForTeam(Team $team)
    {
        return $team->projects()->orderBy('name')->get(['id', 'name']);
    }

    /**
     * Get epics for a project
     */
    public function getEpicsForProject(Project $project)
    {
        return $project->epics()->orderBy('name')->get(['id', 'name', 'status']);
    }

    /**
     * Get test cases for story
     */
    public function getTestCasesForStory(Story $story)
    {
        return $story->testCases()->with('testSuite.project')->get();
    }

    /**
     * Create a new story
     */
    public function createStory(array $data): Story
    {
        // Ensure project exists
        $project = Project::findOrFail($data['project_id']);

        // Check if epic belongs to the project (if provided)
        if (!empty($data['epic_id'])) {
            $epic = Epic::findOrFail($data['epic_id']);
            if ($epic->project_id !== $project->id) {
                throw new \Exception('The selected epic does not belong to the selected project.');
            }
        }

        // Set source as 'manual' for stories created in the system
        $data['source'] = $data['source'] ?? 'manual';

        // Set default metadata
        $metadata = [
            'created_by' => Auth::id(),
            'created_at' => now()->toIsoString(),
            'created_through' => 'web_interface',
        ];

        // Create the story
        $story = new Story();
        $story->project_id = $data['project_id'];
        $story->epic_id = $data['epic_id'] ?? null;
        $story->title = $data['title'];
        $story->description = $data['description'] ?? null;
        $story->source = $data['source'] ?? 'manual';
        $story->external_id = $data['external_id'] ?? null;
        $story->metadata = $metadata;
        $story->save();

        Log::info('Story created successfully', [
            'id' => $story->id,
            'project_id' => $story->project_id,
            'created_by' => Auth::id()
        ]);

        return $story;
    }

    /**
     * Update an existing story with specific fields
     */
    public function updateStory(Story $story, array $data): Story
    {
        // Verify epic belongs to the same project
        if (!empty($data['epic_id'])) {
            $epic = Epic::findOrFail($data['epic_id']);
            if ($epic->project_id !== $story->project_id) {
                throw new \Exception('The selected epic does not belong to the story\'s project.');
            }
        }

        // Update only allowed fields
        $story->title = $data['title'] ?? $story->title;
        $story->description = $data['description'] ?? $story->description;

        // Update epic_id if provided (can be null)
        if (array_key_exists('epic_id', $data)) {
            $story->epic_id = $data['epic_id'];
        }

        // Update metadata to track the update
        $metadata = $story->metadata ?? [];
        $metadata['last_updated_by'] = Auth::id();
        $metadata['last_updated_at'] = now()->toIsoString();
        $story->metadata = $metadata;

        $story->save();

        Log::info('Story updated successfully', [
            'id' => $story->id,
            'updated_by' => Auth::id()
        ]);

        return $story;
    }

    /**
     * Delete a story if it has no test cases
     */
    public function deleteStory(Story $story): bool
    {
        if ($story->testCases()->exists()) {
            throw new \Exception('Cannot delete story - it has associated test cases.');
        }

        return $story->delete();
    }

    /**
     * Generate a story using AI (placeholder)
     */
    public function generateWithAI(string $prompt, string $projectId): array
    {
        // This is a placeholder method for AI generation
        // We'll implement the actual AI integration later

        // For now, return a dummy success response
        return [
            'success' => true,
            'data' => [
                'title' => 'AI Generated: ' . substr($prompt, 0, 50) . '...',
                'description' => 'This is a placeholder for AI-generated content based on: ' . $prompt,
            ]
        ];
    }
}
