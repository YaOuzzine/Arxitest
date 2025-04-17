<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['name', 'description', 'settings'];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Get the team that owns the project.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the test suites for the project.
     */
    public function testSuites()
    {
        return $this->hasMany(TestSuite::class);
    }

    /**
     * Get all test cases for the project through test suites.
     */
    public function testCases()
    {
        // Project -> TestSuite -> TestCase
        // We define the path: Go through TestSuite model to get to TestCase model.
        return $this->hasManyThrough(
            TestCase::class,    // The final model we want to access (TestCase)
            TestSuite::class,   // The intermediate model (TestSuite)
            'project_id',       // Foreign key on the intermediate model (test_suites table) linking back to Project
            'suite_id',         // Foreign key on the final model (test_cases table) linking back to TestSuite
            'id',               // Local key on the starting model (projects table)
            'id'                // Local key on the intermediate model (test_suites table)
        );
    }

    /**
     * The environments associated with this project.
     */
    public function environments()
    {
        return $this->belongsToMany(Environment::class, 'environment_project')
                    ->withTimestamps();
    }

    /**
     * Get the project-specific integrations.
     */
    public function projectIntegrations()
    {
        return $this->hasMany(ProjectIntegration::class);
    }

    /**
     * Get all integrations associated with the project.
     */
    public function integrations()
    {
        return $this->belongsToMany(Integration::class, 'project_integrations')
                    ->withPivot(['encrypted_credentials', 'project_specific_config', 'is_active'])
                    ->withTimestamps();
    }


}
