<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\TestSuite;
use App\Models\User;
use App\Models\JiraStory;
use App\Models\TestVersion;
use App\Models\TestScriptData;
use App\Models\TestExecution;

class TestScript extends Model
{
    use HasUuids;

    protected $fillable = [
        'suite_id',
        'creator_id',
        'jira_story_id',
        'name',
        'framework_type',
        'script_content',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    public function suite()
    {
        return $this->belongsTo(TestSuite::class, 'suite_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function jiraStory()
    {
        return $this->belongsTo(JiraStory::class);
    }

    public function versions()
    {
        return $this->hasMany(TestVersion::class, 'script_id');
    }

    public function testScriptData()
    {
        return $this->hasMany(TestScriptData::class, 'script_id');
    }

    public function executions()
    {
        return $this->hasMany(TestExecution::class, 'script_id');
    }
}
