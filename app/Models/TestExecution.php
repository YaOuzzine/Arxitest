<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class JiraStory extends Model
{
    use HasUuids;

    protected $fillable = [
        'jira_key',
        'title',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    public function testScripts()
    {
        return $this->hasMany(TestScript::class);
    }
}
