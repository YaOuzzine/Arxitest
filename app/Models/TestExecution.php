<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TestExecution extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    // This model only has created_at timestamp, no updated_at
    const UPDATED_AT = null;

    protected $table = 'test_executions';

    protected $fillable = [
        'script_id',
        'initiator_id',
        'environment_id',
        'status_id',
        's3_results_key',
        'start_time',
        'end_time'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'metadata' => 'array'
    ];
    /**
     * Get the test script being executed.
     */
    public function testScript()
    {
        return $this->belongsTo(TestScript::class, 'script_id');
    }

    /**
     * Get the user who initiated the execution.
     */
    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    /**
     * Get the environment in which the execution is running.
     */
    public function environment()
    {
        return $this->belongsTo(Environment::class);
    }

    /**
     * Get the current status of the execution.
     */
    public function status()
    {
        return $this->belongsTo(ExecutionStatus::class, 'status_id');
    }

    /**
     * Get the containers used for this execution.
     */
    public function containers()
    {
        return $this->hasMany(Container::class, 'execution_id');
    }

    /**
     * Calculate the duration of the execution.
     */
    public function getDurationAttribute()
    {
        if (!$this->end_time) {
            return null;
        }

        return $this->start_time->diffInSeconds($this->end_time);
    }

    /**
     * Check if execution is currently running.
     */
    public function isRunning()
    {
        return $this->status->name === ExecutionStatus::RUNNING;
    }

    /**
     * Check if execution is completed.
     */
    public function isCompleted()
    {
        return $this->status->name === ExecutionStatus::COMPLETED;
    }

    /**
     * Check if execution failed.
     */
    public function isFailed()
    {
        return in_array($this->status->name, [
            ExecutionStatus::FAILED,
            ExecutionStatus::ABORTED,
            ExecutionStatus::TIMEOUT
        ]);
    }
}
