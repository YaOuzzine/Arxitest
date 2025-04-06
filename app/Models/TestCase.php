<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TestCase extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'title',
        'steps',
        'expected_results'
    ];

    protected $casts = [
        'steps' => 'array'
    ];

    /**
     * Get the story this test case is derived from.
     */
    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    /**
     * Get the test suite this case belongs to.
     */
    public function testSuite()
    {
        return $this->belongsTo(TestSuite::class, 'suite_id');
    }

    /**
     * Get the test scripts generated from this test case.
     */
    public function testScripts()
    {
        return $this->hasMany(TestScript::class, 'test_case_id');
    }

    /**
     * Get the test data associated with this test case.
     */
    public function testData()
    {
        return $this->belongsToMany(TestData::class, 'test_case_data')
                    ->withPivot('usage_context')
                    ->withTimestamps();
    }

    /**
     * Get the test case data pivot records.
     */
    public function testCaseData()
    {
        return $this->hasMany(TestCaseData::class);
    }
}
