<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ResourceMetric extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    // This model only has created_at timestamp, no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'container_id',
        'cpu_usage',
        'memory_usage',
        'additional_metrics',
        'metric_time'
    ];

    protected $casts = [
        'cpu_usage' => 'float',
        'memory_usage' => 'float',
        'additional_metrics' => 'array',
        'metric_time' => 'datetime'
    ];

    /**
     * Get the container this metric belongs to.
     */
    public function container()
    {
        return $this->belongsTo(Container::class, 'container_id');
    }
}
