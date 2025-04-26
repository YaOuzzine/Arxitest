<?php

namespace App\Services;

use App\Models\Project;
use App\Models\TestSuite;

class TestSuiteService
{
    /**
     * Create a new TestSuite under a given project.
     */
    public function create(Project $project, array $data): TestSuite
    {
        $suite = new TestSuite();
        $suite->project_id  = $project->id;
        $suite->name        = $data['name'];
        $suite->description = $data['description'] ?? null;
        $suite->settings    = [
            'default_priority' => $data['settings']['default_priority'],
            'execution_mode'   => $data['settings']['execution_mode'] ?? 'sequential',
        ];
        $suite->save();

        return $suite;
    }

    /**
     * Update an existing TestSuite.
     */
    public function update(TestSuite $suite, array $data): TestSuite
    {
        $suite->name        = $data['name'];
        $suite->description = $data['description'] ?? null;

        $settings = $suite->settings ?? [];
        $settings['default_priority'] = $data['settings']['default_priority'];
        $settings['execution_mode']   = $data['settings']['execution_mode']
            ?? $settings['execution_mode']
            ?? 'sequential';

        $suite->settings = $settings;
        $suite->save();

        return $suite;
    }

    /**
     * Delete a TestSuite.
     */
    public function delete(TestSuite $suite): void
    {
        $suite->delete();
    }
}
