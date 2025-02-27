<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TestSuite extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'name',
        'description',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'json',
    ];

    /**
     * Get the project that owns the test suite.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the test scripts for the test suite.
     */
    public function testScripts()
    {
        return $this->hasMany(TestScript::class, 'suite_id');
    }
}
