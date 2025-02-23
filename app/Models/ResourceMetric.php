<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ResourceMetric extends Model
{
    use HasUuids;

    protected $fillable = [
        'container_id',
        'cpu_usage',
        'memory_usage',
        'additional_metrics',
        'metric_time',
    ];

    protected $casts = [
        'cpu_usage' => 'float',
        'memory_usage' => 'float',
        'additional_metrics' => 'json',
        'metric_time' => 'datetime',
    ];

    public function container()
    {
        return $this->belongsTo(Container::class);
    }
}
