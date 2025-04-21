<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TestSuite extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['name', 'description', 'settings', 'project_id'];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Get the project that owns the test suite.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the test cases for this suite.
     */
    public function testCases()
    {
        return $this->hasMany(TestCase::class, 'suite_id');
    }

    /**
     * Get all test scripts through test cases.
     */
    public function testScripts()
    {
        return $this->hasManyThrough(TestScript::class, TestCase::class, 'suite_id', 'test_case_id');
    }
}
