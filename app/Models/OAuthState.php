<?php
// app/Models/OAuthState.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OAuthState extends Model
{
    protected $table = 'oauth_states';

    protected $fillable = ['state_token', 'user_id', 'project_id', 'provider', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
    public static function generateState($userId, $projectId, $provider = 'jira')
    {
        $stateToken = Str::random(40);

        self::create([
            'state_token' => $stateToken,
            'user_id' => $userId,
            'project_id' => $projectId,
            'provider' => $provider,
            'expires_at' => now()->addMinutes(15)
        ]);

        return $stateToken;
    }

    public static function findValidState($stateToken)
    {
        return self::where('state_token', $stateToken)
            ->where('expires_at', '>', now())
            ->first();
    }
}
