<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Team extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['name', 'description'];

    /**
     * The users that belong to the team.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('team_role')
                    ->withTimestamps();
    }

    /**
     * The projects managed by this team.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * The subscriptions owned by this team.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
