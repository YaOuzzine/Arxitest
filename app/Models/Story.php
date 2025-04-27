<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'project_id',
        'epic_id',
        'source',
        'external_id',
        'title',
        'description',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    /**
     * Get the project this story belongs to.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the epic this story belongs to (if any).
     */
    public function epic()
    {
        return $this->belongsTo(Epic::class);
    }

    /**
     * Get the test cases derived from this story.
     */
    public function testCases()
    {
        return $this->hasMany(TestCase::class);
    }

    /**
     * Scope query to stories from a specific source.
     */
    public function scopeFromSource($query, $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope query to find a story by its external ID.
     */
    public function scopeByExternalId($query, $externalId)
    {
        return $query->where('external_id', $externalId);
    }
}
