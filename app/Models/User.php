<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Team;
use App\Models\TestExecution;
use App\Models\TestScript;

class User extends Authenticatable
{
    use HasApiTokens, HasUuids, Notifiable;

    protected $fillable = [
        'email',
        'password_hash',
        'name',
        'role',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function teams()
    {
        return $this->belongsToMany(Team::class);
    }

    public function testExecutions()
    {
        return $this->hasMany(TestExecution::class, 'initiator_id');
    }

    public function testScripts()
    {
        return $this->hasMany(TestScript::class, 'creator_id');
    }
}
