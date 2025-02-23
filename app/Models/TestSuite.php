<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TestSuite extends Model
{
    use HasUuids;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'settings',
    ];

    protected $casts = [
        'settings' => 'json',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function testScripts()
    {
        return $this->hasMany(TestScript::class, 'suite_id');
    }
}
