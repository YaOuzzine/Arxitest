<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasUuids, Notifiable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'role',
        'phone_number',
        'phone_verified',
        'google_id',
        'github_id',
        'microsoft_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified' => 'boolean',
    ];

    // User belongs to many teams (many-to-many)
    public function teams()
    {
        return $this->belongsToMany(Team::class)
                    ->withPivot('team_role')
                    ->withTimestamps();
    }

    // User initiates many test executions
    public function testExecutions()
    {
        return $this->hasMany(TestExecution::class, 'initiator_id');
    }

    // User creates many test scripts
    public function testScripts()
    {
        return $this->hasMany(TestScript::class, 'creator_id');
    }

    public function phone_verifications(){
        return $this->hasMany(PhoneVerification::class);
    }
}
