<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TestScript extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'suite_id',
        'creator_id',
        'jira_story_id',
        'name',
        'framework_type',
        'script_content',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'json',
    ];

    /**
     * Get the test suite that owns the test script.
     */
    public function testSuite()
    {
        return $this->belongsTo(TestSuite::class, 'suite_id');
    }

    /**
     * Get the user that created the test script.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the Jira story associated with the test script.
     */
    public function jiraStory()
    {
        return $this->belongsTo(JiraStory::class);
    }

    /**
     * Get the test executions for the test script.
     */
    public function testExecutions()
    {
        return $this->hasMany(TestExecution::class, 'script_id');
    }

    /**
     * Get the test versions for the test script.
     */
    public function testVersions()
    {
        return $this->hasMany(TestVersion::class, 'script_id');
    }

    /**
     * Get the test data for the test script.
     */
    public function testScriptData()
    {
        return $this->hasMany(TestScriptData::class, 'script_id');
    }

    public static function getSuitesForCurrentUser()
    {
        $user = auth()->user();

        return TestSuite::whereHas('project.team', function ($query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        })
        ->with('project.team')
        ->get();
    }
}
