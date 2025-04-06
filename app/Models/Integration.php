<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'type',
        'name',
        'base_url',
        'encrypted_credentials',
        'shared_config',
        'is_active'
    ];

    protected $casts = [
        'shared_config' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Get the projects using this integration.
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_integrations')
                   ->withPivot(['encrypted_credentials', 'project_specific_config', 'is_active'])
                   ->withTimestamps();
    }

    /**
     * Get the project integrations.
     */
    public function projectIntegrations()
    {
        return $this->hasMany(ProjectIntegration::class);
    }

    /**
     * Scope a query to only include active integrations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include integrations of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Define common integration types
     */
    const TYPE_JIRA = 'jira';
    const TYPE_GITHUB = 'github';
    const TYPE_GITLAB = 'gitlab';
    const TYPE_AZURE_DEVOPS = 'azure_devops';
    const TYPE_SLACK = 'slack';
    const TYPE_CUSTOM = 'custom';
}
