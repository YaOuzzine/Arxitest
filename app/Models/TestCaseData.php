<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TestCaseData extends Pivot
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    // This model only has created_at timestamp, no updated_at
    const UPDATED_AT = null;

    protected $table = 'test_case_data';

    protected $fillable = [
        'test_case_id',
        'test_data_id',
        'usage_context'
    ];

    protected $casts = [
        'usage_context' => 'array'
    ];

    /**
     * Get the test case associated with this record.
     */
    public function testCase()
    {
        return $this->belongsTo(TestCase::class, 'test_case_id');
    }

    /**
     * Get the test data associated with this record.
     */
    public function testData()
    {
        return $this->belongsTo(TestData::class, 'test_data_id');
    }
}

