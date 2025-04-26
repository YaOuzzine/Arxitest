<?php

namespace App;

use App\Models\Project;
use App\Models\TestCase;
use App\Models\TestSuite;
use Illuminate\Support\Facades\Log;

trait AuthorizeResourceAccess
{
    /**
     * Temporary placeholder for authorization check.
     * This will be replaced with a more robust implementation later.
     */
    protected function authorizeAccess($project): void
    {
        Log::warning('AUTHORIZATION CHECK IS TEMPORARILY DISABLED', [
            'controller' => get_class($this),
            'method' => debug_backtrace()[1]['function'],
            'project_id' => $project->id ?? 'unknown'
        ]);
    }
}
