<?php

namespace Database\Seeders;

use App\Models\ExecutionStatus;
use Illuminate\Database\Seeder;

class ExecutionStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            [
                'name' => 'pending',
                'description' => 'Test execution is queued'
            ],
            [
                'name' => 'running',
                'description' => 'Test is currently executing'
            ],
            [
                'name' => 'completed',
                'description' => 'Test completed successfully'
            ],
            [
                'name' => 'failed',
                'description' => 'Test execution failed'
            ],
            [
                'name' => 'cancelled',
                'description' => 'Test execution was cancelled'
            ],
            [
                'name' => 'error',
                'description' => 'Test encountered an error during execution'
            ]
        ];

        foreach ($statuses as $status) {
            ExecutionStatus::create($status);
        }
    }
}
