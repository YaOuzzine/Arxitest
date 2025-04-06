<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Environment extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'configuration',
        'is_global',
        'is_active',
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_global' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * The projects that use this environment.
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'environment_project')
                    ->withTimestamps();
    }

    /**
     * The test executions that ran in this environment.
     */
    public function testExecutions()
    {
        return $this->hasMany(TestExecution::class);
    }

    /**
     * Scope a query to only include global environments.
     */
    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    /**
     * Scope a query to only include active environments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
