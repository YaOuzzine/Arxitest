<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ExecutionStatus extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
    ];

    public function executions()
    {
        return $this->hasMany(TestExecution::class, 'status_id');
    }
}
