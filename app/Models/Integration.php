<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Integration extends Model
{
    use HasUuids;

    protected $fillable = [
        'type',
        'name',
        'base_url',
        'encrypted_credentials',
        'shared_config',
        'is_active',
    ];

    protected $casts = [
        'shared_config' => 'json',
        'is_active' => 'boolean',
    ];

    public function projectIntegrations()
    {
        return $this->hasMany(ProjectIntegration::class);
    }
}
