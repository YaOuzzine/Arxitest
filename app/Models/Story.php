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

    public function testCases(){
        return $this->hasMany(TestCase::class);
    }

    public function testScripts(){
        return $this->hasMany(TestScript::class);
    }
}
