<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TestData extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'content',
        'format',
        'is_sensitive',
        'metadata',
    ];

    protected $casts = [
        'is_sensitive' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the test cases associated with this test data.
     */
    public function testCases()
    {
        return $this->belongsToMany(TestCase::class, 'test_case_data')
                    ->withPivot('usage_context')
                    ->using(TestCaseData::class);
    }

    /**
     * Get the test scripts associated with this test data.
     */
    public function testScripts()
    {
        return $this->belongsToMany(TestScript::class, 'test_script_data')
                    ->withPivot('usage_context')
                    ->withTimestamps();
    }

    /**
     * Scope a query to only include sensitive test data.
     */
    public function scopeSensitive($query)
    {
        return $query->where('is_sensitive', true);
    }

        /**
     * Scope a query to only include none sensitive test data.
     */

    public function scopeNotSensitive($query)
    {
        return $query->where('is_sensitive', false);
    }

}
