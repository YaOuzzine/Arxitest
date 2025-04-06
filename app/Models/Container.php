<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    // This model only has created_at timestamp, no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'execution_id',
        'container_id',
        'status',
        'configuration',
        's3_logs_key',
        'start_time',
        'end_time'
    ];

    protected $casts = [
        'configuration' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    /**
     * Get the test execution this container belongs to.
     */
    public function testExecution()
    {
        return $this->belongsTo(TestExecution::class, 'execution_id');
    }

    /**
     * Get the resource metrics for this container.
     */
    public function resourceMetrics()
    {
        return $this->hasMany(ResourceMetric::class, 'container_id');
    }

    /**
     * Calculate the duration of the container runtime.
     */
    public function getDurationAttribute()
    {
        if (!$this->end_time || !$this->start_time) {
            return null;
        }

        return $this->start_time->diffInSeconds($this->end_time);
    }

    /**
     * Define common status constants
     */
    const PENDING = 'pending';
    const RUNNING = 'running';
    const COMPLETED = 'completed';
    const FAILED = 'failed';
    const TERMINATED = 'terminated';
}
