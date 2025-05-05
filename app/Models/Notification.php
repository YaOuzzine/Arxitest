<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    // Only has created_at timestamp, no updated_at needed
    const UPDATED_AT = null;

    protected $fillable = [
        'actor_id',
        'type',
        'data'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the actor (user) who triggered this notification.
     */
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Get the users this notification was sent to.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_notifications')
                    ->withPivot('is_read', 'read_at')
                    ->as('receipt');
    }
}
