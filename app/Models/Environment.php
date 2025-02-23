<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Environment extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'configuration',
        'is_global',
        'is_active',
    ];

    protected $casts = [
        'configuration' => 'json',
        'is_global' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }
}
