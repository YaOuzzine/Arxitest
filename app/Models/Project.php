<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['name', 'description', 'settings'];


    //Relationships

    public function team(){
        return $this->belongsTo(Team::class);
    }

    public function testSuites(){
        return $this->hasMany(TestSuite::class);
    }

    public function environments(){
        return $this->belongsToMany(Environment::class, 'environment_project');
    }

    public function projectIntegrations()
    {
        return $this->hasMany(ProjectIntegration::class, 'project_id');
    }
}
