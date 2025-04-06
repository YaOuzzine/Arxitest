<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TestScript extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'test_case_id',
        'creator_id',
        'story_id',
        'name',
        'framework_type',
        'script_content',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    /**
     * Get the test case this script was generated from.
     */
    public function testCase()
    {
        return $this->belongsTo(TestCase::class);
    }

    /**
     * Get the user who created this script.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the story this script references.
     */
    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    /**
     * Get the versions of this test script.
     */
    public function versions()
    {
        return $this->hasMany(TestVersion::class, 'script_id');
    }

    /**
     * Get the most recent version of this script.
     */
    public function latestVersion()
    {
        return $this->hasOne(TestVersion::class, 'script_id')
                    ->latest('created_at');
    }

    /**
     * Get the test executions of this script.
     */
    public function executions()
    {
        return $this->hasMany(TestExecution::class, 'script_id');
    }

    /**
     * Get the test data associated with this script.
     */
    public function testData()
    {
        return $this->belongsToMany(TestData::class, 'test_script_data')
                    ->withPivot('usage_context')
                    ->withTimestamps();
    }

    /**
     * Get the test script data pivot records.
     */
    public function testScriptData()
    {
        return $this->hasMany(TestScriptData::class, 'script_id');
    }
}
