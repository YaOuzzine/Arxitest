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
     * Get the test cases derived from this story.
     */
    public function testCases()
    {
        return $this->hasMany(TestCase::class);
    }

    /**
     * Get the test scripts that reference this story.
     */
    public function testScripts()
    {
        return $this->hasMany(TestScript::class);
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
