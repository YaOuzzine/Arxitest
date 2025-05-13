<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SyncMapping extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'arxitest_type',
        'arxitest_id',
        'external_system',
        'external_id',
        'last_sync',
        'metadata'
    ];

    protected $casts = [
        'last_sync' => 'datetime',
        'metadata' => 'array'
    ];
}
