<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TestExecution extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'script_id',
        'initiator_id',
        'environment_id',
        'status_id',
        's3_results_key',
        'start_time',
        'end_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the test script that is being executed.
     */
    public function testScript()
    {
        return $this->belongsTo(TestScript::class, 'script_id');
    }

    /**
     * Get the user that initiated the test execution.
     */
    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    /**
     * Get the environment used for the test execution.
     */
    public function environment()
    {
        return $this->belongsTo(Environment::class);
    }

    /**
     * Get the execution status.
     */
    public function executionStatus()
    {
        return $this->belongsTo(ExecutionStatus::class, 'status_id');
    }

    /**
     * Get the containers for the test execution.
     */
    public function containers()
    {
        return $this->hasMany(Container::class, 'execution_id');
    }
}
