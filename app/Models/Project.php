<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Team;
use App\Models\TestSuite;
use App\Models\Environment;
use App\Models\ProjectIntegration;

class Project extends Model
{
    use HasUuids;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'settings',
    ];

    protected $casts = [
        'settings' => 'json',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function testSuites()
    {
        return $this->hasMany(TestSuite::class);
    }

    public function environments()
    {
        return $this->belongsToMany(Environment::class);
    }

    public function projectIntegrations()
    {
        return $this->hasMany(ProjectIntegration::class);
    }
}
