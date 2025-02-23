<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Container extends Model
{
    use HasUuids;

    protected $fillable = [
        'execution_id',
        'container_id',
        'status',
        'configuration',
        's3_logs_key',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'configuration' => 'json',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function testExecution()
    {
        return $this->belongsTo(TestExecution::class, 'execution_id');
    }

    public function resourceMetrics()
    {
        return $this->hasMany(ResourceMetric::class);
    }
}
