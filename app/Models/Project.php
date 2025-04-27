<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['name', 'description', 'settings', 'team_id'];

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
     * Get the epics for the project.
     */
    public function epics()
    {
        return $this->hasMany(Epic::class);
    }

    /**
     * Get the stories for the project.
     */
    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    /**
     * Get all test cases for the project through test suites.
     */
    public function testCases()
    {
        return $this->hasManyThrough(
            TestCase::class,
            TestSuite::class,
            'project_id',
            'suite_id',
            'id',
            'id'
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
