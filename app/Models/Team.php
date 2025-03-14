<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\User;
use App\Models\Project;
use App\Models\Subscription;

class Team extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'settings',
    ];

    protected $casts = [
        'settings' => 'json',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the current invitation code
     *
     * @return string|null
     */
    public function getInvitationCode()
    {
        return $this->settings['invitation_code'] ?? null;
    }

    /**
     * Get when the invitation code was generated
     *
     * @return string|null
     */
    public function getInvitationGeneratedAt()
    {
        return $this->settings['invitation_generated_at'] ?? null;
    }
}
