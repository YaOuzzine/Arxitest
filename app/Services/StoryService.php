<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Story;
use App\Models\Team;
use App\Models\TestCase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
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

        // Base query for stories that belong to the current team
        $query = Story::query()
            ->whereHas('testCases', function ($q) use ($projectIds) {
                $q->whereHas('testSuite', function ($q) use ($projectIds) {
                    $q->whereIn('project_id', $projectIds);
                });
            });

        // Apply project filter
        if (isset($filters['project_id']) && !empty($filters['project_id'])) {
            $query->whereHas('testCases', function ($q) use ($filters) {
                $q->whereHas('testSuite', function ($q) use ($filters) {
                    $q->where('project_id', $filters['project_id']);
                });
            });
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
     * Get projects for the team
     */
    public function getProjectsForTeam(Team $team)
    {
        return $team->projects()->orderBy('name')->get(['id', 'name']);
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
        $story = new Story();
        $story->title = $data['title'];
        $story->description = $data['description'] ?? null;
        $story->source = $data['source'];
        $story->external_id = $data['external_id'] ?? null;
        $story->metadata = $data['metadata'] ?? [];
        $story->save();

        return $story;
    }

    /**
     * Update an existing story
     */
    public function updateStory(Story $story, array $data): Story
    {
        $story->title = $data['title'];
        $story->description = $data['description'] ?? null;
        $story->source = $data['source'];
        $story->external_id = $data['external_id'] ?? null;
        $story->metadata = $data['metadata'] ?? $story->metadata;
        $story->save();

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
}
