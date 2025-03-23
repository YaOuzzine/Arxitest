<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TestVersion extends Model
{
    use HasUuids;

    /**
     * Disable timestamps as we only have created_at in the table
     */
    public $timestamps = false;

    /**
     * Specify which timestamp fields to update automatically
     */
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;  // Tell Laravel there's no updated_at column

    protected $fillable = [
        'script_id',
        'version_hash',
        'script_content',
        'changes',
    ];

    protected $casts = [
        'changes' => 'json',
    ];

    public function testScript()
    {
        return $this->belongsTo(TestScript::class, 'script_id');
    }
}
