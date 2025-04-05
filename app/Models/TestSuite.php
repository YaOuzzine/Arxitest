<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TestSuite extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['name', 'description', 'settings'];


    public function project(){
        return $this->belongsTo(Project::class);
    }

    public function testCases(){
        return $this->hasMany(TestCase::class);
    }

}
