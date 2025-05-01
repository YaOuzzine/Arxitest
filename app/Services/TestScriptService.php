<?php

namespace App\Services;

use App\Models\Project;
use App\Models\TestCase;
use App\Models\TestScript;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestScriptService
{
    /**
     * Validate relationships between project, test case, and test script.
     */
    public function validateRelationships(Project $project, TestCase $testCase, ?TestScript $testScript = null): bool
    {
        // Validate project-test case relationship
        $suite = $testCase->testSuite;
        if (!$suite || $suite->project_id !== $project->id) {
            throw new \Exception('Test case not found in this project.');
        }

        // If test script is provided, validate test case-test script relationship
        if ($testScript && $testScript->test_case_id !== $testCase->id) {
            throw new \Exception('Test script not found in this test case.');
        }

        return true;
    }

     /**
     * Create a new test script.
     */
    public function createScript(
        TestCase $testCase,
        string $name,
        string $frameworkType,
        string $scriptContent,
        array $metadata = []
    ): TestScript {
        $defaultMetadata = [
            'created_through' => 'manual',
            'source' => 'user'
        ];

        $testScript = new TestScript();
        $testScript->test_case_id = $testCase->id;
        $testScript->creator_id = Auth::id();
        $testScript->name = $name;
        $testScript->framework_type = $frameworkType;
        $testScript->script_content = $scriptContent;
        $testScript->metadata = array_merge($defaultMetadata, $metadata);
        $testScript->save();

        return $testScript;
    }

}
