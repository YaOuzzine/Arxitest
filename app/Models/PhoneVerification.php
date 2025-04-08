<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PhoneVerification extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'phone_number',
        'code',
        'expires_at',
        'used'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean'
    ];

    /**
     * Get the user associated with this verification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
