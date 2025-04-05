<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Laravel 10+ UUID helper

class User extends Model
{
    use HasUuids;

    protected $table = 'users';      // Optional if table name matches plural
    protected $keyType = 'string';   // UUID = string
    public $incrementing = false;    // UUID not auto increment

    protected $fillable = [
        'email',
        'password_hash',
        'name',
        'role',
    ];

    // Relationships

    // User belongs to a team
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    // User creates many TestScripts
    public function testScripts()
    {
        return $this->hasMany(TestScript::class, 'creator_id');
    }

    // User initiates many TestExecutions
    public function testExecutions()
    {
        return $this->hasMany(TestExecution::class, 'initiator_id');
    }
}
