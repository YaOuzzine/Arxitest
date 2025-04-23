<?php

namespace Database\Seeders;

use App\Models\ExecutionStatus;
use Illuminate\Database\Seeder;

class ExecutionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'pending',
                'description' => 'Execution is queued and waiting to be processed'
            ],
            [
                'name' => 'running',
                'description' => 'Execution is currently in progress'
            ],
            [
                'name' => 'completed',
                'description' => 'Execution completed successfully'
            ],
            [
                'name' => 'failed',
                'description' => 'Execution failed due to errors'
            ],
            [
                'name' => 'aborted',
                'description' => 'Execution was manually aborted'
            ],
            [
                'name' => 'timeout',
                'description' => 'Execution timed out'
            ]
        ];

        foreach ($statuses as $status) {
            ExecutionStatus::updateOrCreate(
                ['name' => $status['name']],
                ['description' => $status['description']]
            );
        }
    }
}
