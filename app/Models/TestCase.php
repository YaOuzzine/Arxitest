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

    public function story(){
        return $this->belongsTo(Story::class);
    }

    public function testSuite(){
        return $this->belongsTo(TestSuite::class);
    }

}
