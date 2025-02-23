<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TestScriptData extends Model
{
    use HasUuids;

    protected $fillable = [
        'script_id',
        'test_data_id',
        'usage_context',
    ];

    protected $casts = [
        'usage_context' => 'json',
    ];

    public function testScript()
    {
        return $this->belongsTo(TestScript::class, 'script_id');
    }

    public function testData()
    {
        return $this->belongsTo(TestData::class);
    }
}
