<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'team_id',
        'plan_type',
        'max_containers',
        'retention_days',
        'max_parallel_runs',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the team that owns the subscription.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Check if the subscription is currently active.
     */
    public function isActive()
    {
        return $this->is_active &&
               $this->start_date <= now() &&
               ($this->end_date === null || $this->end_date >= now());
    }
}
