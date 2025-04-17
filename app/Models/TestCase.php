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
        'description',
        'expected_results',
        'steps',
        'suite_id',
        'priority',
        'status',
        'tags'
    ];

    protected $casts = [
        'steps' => 'array',
        'tags' => 'array'
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
        return $this->belongsToMany(TestData::class, 'test_case_data', 'test_case_id', 'test_data_id')
            ->withPivot('usage_context', 'created_at') // Specify existing columns
            ->as('pivot') // Optional: Alias for pivot data access ($data->pivot->usage_context)
            ->using(TestCaseData::class); // If you use a custom pivot model
    }

    /**
     * Get the test case data pivot records.
     */
    public function testCaseData()
    {
        return $this->hasMany(TestCaseData::class);
    }
}
