<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@arxitest.com',
            'password_hash' => Hash::make('admin'),
            'role' => 'admin'
        ]);

        // Create regular users
        $users = [
            [
                'name' => 'QA Tester',
                'email' => 'qa@arxitest.com',
                'password_hash' => Hash::make('tester'),
                'role' => 'user'
            ],
            [
                'name' => 'Developer',
                'email' => 'dev@arxitest.com',
                'password_hash' => Hash::make('dev'),
                'role' => 'user'
            ]
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
