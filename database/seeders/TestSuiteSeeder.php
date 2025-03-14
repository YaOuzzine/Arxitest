<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\User;
use App\Models\Project;
use App\Models\TestSuite;
use App\Models\JiraStory;
use App\Models\Environment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TestSuiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the admin user
        $adminUser = User::where('email', 'admin@arxitest.com')->first();

        if (!$adminUser) {
            $this->command->error('Admin user not found. Please make sure the admin user exists first.');
            return;
        }

        // Make sure we're using UUIDs
        if (!method_exists(new User, 'newUniqueId')) {
            $this->command->error('Models must use the HasUuids trait to generate UUIDs correctly.');
            return;
        }

        // Create or find the team for the admin user
        $team = Team::firstOrCreate(
            ['name' => 'Arxitest Admin Team'],
            [
                'description' => 'Team for testing purposes',
            ]
        );

        // Associate admin with team if not already associated
        if (!$adminUser->teams()->where('team_id', $team->id)->exists()) {
            // Use direct DB insert with a UUID
            DB::table('team_user')->insert([
                'id' => Str::uuid()->toString(),
                'team_id' => $team->id,
                'user_id' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Create default environment
        $devEnvironment = Environment::firstOrCreate(
            ['name' => 'Development'],
            [
                'configuration' => [
                    'base_url' => 'https://dev.example.com',
                    'selenium_grid_url' => 'http://localhost:4444/wd/hub',
                    'browser' => 'chrome',
                    'headless' => true,
                ],
                'is_global' => true,
                'is_active' => true,
            ]
        );

        $stagingEnvironment = Environment::firstOrCreate(
            ['name' => 'Staging'],
            [
                'configuration' => [
                    'base_url' => 'https://staging.example.com',
                    'selenium_grid_url' => 'http://selenium-grid:4444/wd/hub',
                    'browser' => 'chrome',
                    'headless' => true,
                ],
                'is_global' => true,
                'is_active' => true,
            ]
        );

        // Create projects
        $projects = [
            [
                'name' => 'E-Commerce Platform',
                'description' => 'Online shopping application with user accounts, product catalog, and checkout',
                'settings' => [
                    'ai_enabled' => true,
                    'ai_provider' => 'openai',
                    'test_generation' => 'semi-automatic',
                ],
                'suites' => [
                    [
                        'name' => 'User Authentication',
                        'description' => 'Test cases for user login, registration, and account management',
                        'jira_stories' => [
                            ['jira_key' => 'ECOM-101', 'title' => 'User Registration', 'description' => 'As a customer, I want to register for an account so that I can save my information and track orders.'],
                            ['jira_key' => 'ECOM-102', 'title' => 'User Login', 'description' => 'As a customer, I want to log in to my account so that I can access my personal information.'],
                            ['jira_key' => 'ECOM-103', 'title' => 'Password Reset', 'description' => 'As a customer, I want to reset my password so that I can regain access to my account if I forget my credentials.'],
                        ]
                    ],
                    [
                        'name' => 'Shopping Cart',
                        'description' => 'Test cases for adding products to cart and managing the shopping cart',
                        'jira_stories' => [
                            ['jira_key' => 'ECOM-201', 'title' => 'Add to Cart', 'description' => 'As a customer, I want to add items to my shopping cart so that I can purchase them later.'],
                            ['jira_key' => 'ECOM-202', 'title' => 'Update Cart Quantity', 'description' => 'As a customer, I want to update the quantity of items in my cart so that I can purchase the desired amount.'],
                            ['jira_key' => 'ECOM-203', 'title' => 'Remove from Cart', 'description' => 'As a customer, I want to remove items from my cart so that I can change my mind about a purchase.'],
                        ]
                    ],
                    [
                        'name' => 'Checkout Process',
                        'description' => 'Test cases for the checkout flow including payment processing',
                        'jira_stories' => [
                            ['jira_key' => 'ECOM-301', 'title' => 'Checkout with Guest Account', 'description' => 'As a customer, I want to check out as a guest so that I can make a purchase without creating an account.'],
                            ['jira_key' => 'ECOM-302', 'title' => 'Payment Processing', 'description' => 'As a customer, I want to securely pay for my order so that I can complete my purchase.'],
                            ['jira_key' => 'ECOM-303', 'title' => 'Order Confirmation', 'description' => 'As a customer, I want to receive an order confirmation so that I know my purchase was successful.'],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'CRM System',
                'description' => 'Customer relationship management system with contact management and sales pipeline',
                'settings' => [
                    'ai_enabled' => true,
                    'ai_provider' => 'openai',
                    'test_generation' => 'fully-automatic',
                ],
                'suites' => [
                    [
                        'name' => 'Contact Management',
                        'description' => 'Test cases for adding, editing, and managing customer contacts',
                        'jira_stories' => [
                            ['jira_key' => 'CRM-101', 'title' => 'Create Contact', 'description' => 'As a sales rep, I want to create a new contact so that I can keep track of potential customers.'],
                            ['jira_key' => 'CRM-102', 'title' => 'Edit Contact Information', 'description' => 'As a sales rep, I want to edit contact details so that I can keep information up to date.'],
                            ['jira_key' => 'CRM-103', 'title' => 'Contact Search', 'description' => 'As a sales rep, I want to search for contacts so that I can quickly find the information I need.'],
                        ]
                    ],
                    [
                        'name' => 'Deal Management',
                        'description' => 'Test cases for creating and tracking sales deals',
                        'jira_stories' => [
                            ['jira_key' => 'CRM-201', 'title' => 'Create Deal', 'description' => 'As a sales rep, I want to create a new deal so that I can track potential sales.'],
                            ['jira_key' => 'CRM-202', 'title' => 'Update Deal Stage', 'description' => 'As a sales rep, I want to update the stage of a deal so that I can track its progress in the sales pipeline.'],
                            ['jira_key' => 'CRM-203', 'title' => 'Deal Reporting', 'description' => 'As a sales manager, I want to view reports on deals so that I can analyze sales performance.'],
                        ]
                    ]
                ]
            ]
        ];

        foreach ($projects as $projectData) {
            // Create or update project
            $project = Project::firstOrCreate(
                ['name' => $projectData['name'], 'team_id' => $team->id],
                [
                    'description' => $projectData['description'],
                    'settings' => $projectData['settings'],
                ]
            );

            // Associate environments with project
            if (!$project->environments()->where('environment_id', $devEnvironment->id)->exists()) {
                // Use direct DB insert with a UUID
                DB::table('environment_project')->insert([
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'environment_id' => $devEnvironment->id,
                    'project_id' => $project->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            if (!$project->environments()->where('environment_id', $stagingEnvironment->id)->exists()) {
                // Use direct DB insert with a UUID
                DB::table('environment_project')->insert([
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'environment_id' => $stagingEnvironment->id,
                    'project_id' => $project->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Create test suites and Jira stories for each project
            foreach ($projectData['suites'] as $suiteData) {
                $suite = TestSuite::firstOrCreate(
                    ['name' => $suiteData['name'], 'project_id' => $project->id],
                    [
                        'description' => $suiteData['description'],
                        'settings' => [
                            'default_framework' => 'selenium_python',
                            'auto_generate_tests' => true,
                        ],
                    ]
                );

                // Create Jira stories for this suite
                foreach ($suiteData['jira_stories'] as $storyData) {
                    JiraStory::firstOrCreate(
                        ['jira_key' => $storyData['jira_key']],
                        [
                            'title' => $storyData['title'],
                            'description' => $storyData['description'],
                            'metadata' => [
                                'priority' => 'Medium',
                                'story_points' => rand(1, 5),
                                'acceptance_criteria' => [
                                    'Given I am on the appropriate page',
                                    'When I perform the required action',
                                    'Then I should see the expected result'
                                ]
                            ],
                        ]
                    );
                }
            }
        }

        $this->command->info('Test suites, projects, and Jira stories created successfully for admin user!');
    }
}
