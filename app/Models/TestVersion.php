<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TestVersion extends Model
{
    use HasUuids;

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
