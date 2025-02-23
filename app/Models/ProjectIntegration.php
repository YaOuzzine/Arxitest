<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProjectIntegration extends Model
{
    use HasUuids;

    protected $fillable = [
        'project_id',
        'integration_id',
        'encrypted_credentials',
        'project_specific_config',
        'is_active',
    ];

    protected $casts = [
        'project_specific_config' => 'json',
        'is_active' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }
}
