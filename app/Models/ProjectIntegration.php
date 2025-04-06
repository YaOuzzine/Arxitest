<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProjectIntegration extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'project_id',
        'integration_id',
        'encrypted_credentials',
        'project_specific_config',
        'is_active'
    ];

    protected $casts = [
        'project_specific_config' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Get the project this integration is configured for.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the base integration.
     */
    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }

    /**
     * Get the combined configuration (shared + project-specific).
     */
    public function getCompleteConfigAttribute()
    {
        return array_merge(
            $this->integration->shared_config ?? [],
            $this->project_specific_config ?? []
        );
    }

    /**
     * Scope a query to only include active project integrations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
