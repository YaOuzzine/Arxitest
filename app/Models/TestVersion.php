<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TestVersion extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    // This model only has created_at timestamp, no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'script_id',
        'version_hash',
        'script_content',
        'changes'
    ];

    protected $casts = [
        'changes' => 'array'
    ];

    /**
     * Get the test script this version belongs to.
     */
    public function testScript()
    {
        return $this->belongsTo(TestScript::class, 'script_id');
    }

    /**
     * Generate a unique hash for this version.
     */
    public static function generateHash($scriptContent)
    {
        return md5($scriptContent . microtime(true));
    }
}
