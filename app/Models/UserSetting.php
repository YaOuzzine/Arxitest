<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'team_id',
        'key',
        'value'
    ];

    /**
     * Get the user that owns the setting.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the team that owns the setting.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get or set a user setting.
     *
     * @param string $userId
     * @param string $key
     * @param mixed $value
     * @param string|null $teamId
     * @return mixed
     */
    public static function getOrSet($userId, $key, $value = null, $teamId = null)
    {
        $setting = self::where('user_id', $userId)
            ->where('key', $key)
            ->where('team_id', $teamId)
            ->first();

        // If we're just retrieving the setting
        if ($value === null) {
            return $setting ? $setting->value : null;
        }

        // If we're setting a value
        if ($setting) {
            $setting->value = $value;
            $setting->save();
        } else {
            $setting = self::create([
                'user_id' => $userId,
                'team_id' => $teamId,
                'key' => $key,
                'value' => $value
            ]);
        }

        return $value;
    }

    /**
     * Get all settings for a user.
     *
     * @param string $userId
     * @param string|null $teamId
     * @return array
     */
    public static function getAllForUser($userId, $teamId = null)
    {
        $query = self::where('user_id', $userId);

        if ($teamId !== null) {
            $query->where('team_id', $teamId);
        }

        $settings = $query->get();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        return $result;
    }
}
