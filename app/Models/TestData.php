<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TestData extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        's3_data_key',
        'is_sensitive',
        'metadata',
    ];

    protected $casts = [
        'is_sensitive' => 'boolean',
        'metadata' => 'json',
    ];

    public function testScriptData()
    {
        return $this->hasMany(TestScriptData::class);
    }
}
