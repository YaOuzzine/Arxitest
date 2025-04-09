<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements MustVerifyEmail
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
        'email_verified_at',
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

    // User has many phone verifications
    public function phone_verifications(){
        return $this->hasMany(PhoneVerification::class);
    }

     /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        // If the user logged in via OAuth, consider them verified
        if ($this->google_id || $this->github_id || $this->microsoft_id) {
            return true;
        }

        // Otherwise, check if email_verified_at is set
        return ! is_null($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Set the user's password.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password_hash'] = Hash::make($value);
    }
}
