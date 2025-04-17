<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TestScriptData extends Pivot
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    // This model only has created_at timestamp, no updated_at
    const UPDATED_AT = null;

    protected $table = 'test_script_data';

    protected $fillable = [
        'script_id',
        'test_data_id',
        'usage_context'
    ];

    protected $casts = [
        'usage_context' => 'array'
    ];

    /**
     * Get the test script associated with this record.
     */
    public function testScript()
    {
        return $this->belongsTo(TestScript::class, 'script_id');
    }

    /**
     * Get the test data associated with this record.
     */
    public function testData()
    {
        return $this->belongsTo(TestData::class, 'test_data_id');
    }
}
