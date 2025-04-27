<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Epic extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'project_id',
        'external_id',
        'name',
        'status',
    ];

    /**
     * Get the project this epic belongs to.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the stories belonging to this epic.
     */
    public function stories()
    {
        return $this->hasMany(Story::class);
    }
}
