<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ExecutionStatus extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    // This model only has created_at timestamp, no updated_at
    const UPDATED_AT = null;

    protected $table = 'execution_statuses';

    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Get the test executions with this status.
     */
    public function testExecutions()
    {
        return $this->hasMany(TestExecution::class, 'status_id');
    }

    /**
     * Define common status constants
     */
    const PENDING = 'pending';
    const RUNNING = 'running';
    const COMPLETED = 'completed';
    const FAILED = 'failed';
    const ABORTED = 'aborted';
    const TIMEOUT = 'timeout';
}
