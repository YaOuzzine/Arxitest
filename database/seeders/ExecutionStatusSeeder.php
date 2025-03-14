<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExecutionStatus;

class ExecutionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Pending',
                'description' => 'Test execution is pending and has not started yet',
            ],
            [
                'name' => 'Running',
                'description' => 'Test execution is currently in progress',
            ],
            [
                'name' => 'Passed',
                'description' => 'Test execution completed successfully with all tests passing',
            ],
            [
                'name' => 'Failed',
                'description' => 'Test execution completed with one or more test failures',
            ],
            [
                'name' => 'Error',
                'description' => 'Test execution failed due to a system or environment error',
            ],
            [
                'name' => 'Cancelled',
                'description' => 'Test execution was cancelled by a user or system',
            ],
        ];

        foreach ($statuses as $status) {
            ExecutionStatus::firstOrCreate(
                ['name' => $status['name']],
                ['description' => $status['description']]
            );
        }

        $this->command->info('Execution statuses seeded successfully!');
    }
}
