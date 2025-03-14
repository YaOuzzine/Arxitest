<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\JiraStory;

class OpenAIService
{
    protected $apiKey;
    protected $apiUrl = 'https://api.openai.com/v1/chat/completions';
    protected $model = 'gpt-4-0125-preview'; // Using the most capable model

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');

        if (empty($this->apiKey)) {
            Log::error('OpenAI API key is not configured');
        }
    }

    /**
     * Generate test script using OpenAI with function calling
     *
     * @param array $data Context data for script generation
     * @return array Response with script content or error
     */
    public function generateTestScript(array $data)
    {
        try {
            // Validate input
            if (empty($data['framework_type'])) {
                return ['success' => false, 'message' => 'Framework type is required'];
            }

            // Prepare context data for the API
            $context = $this->prepareContext($data);

            // Define function call for formatting the output
            $functions = [
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'generate_test_script',
                        'description' => 'Generate a test script based on the provided context',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'script_content' => [
                                    'type' => 'string',
                                    'description' => 'The complete test script content'
                                ],
                                'suggested_name' => [
                                    'type' => 'string',
                                    'description' => 'A suggested name for the test script'
                                ],
                                'estimated_coverage' => [
                                    'type' => 'array',
                                    'description' => 'List of test scenarios covered by this script',
                                    'items' => [
                                        'type' => 'string'
                                    ]
                                ]
                            ],
                            'required' => ['script_content']
                        ]
                    ]
                ]
            ];

            // Build the API request payload
            $payload = [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->getSystemPrompt($data['framework_type'])
                    ],
                    [
                        'role' => 'user',
                        'content' => $context
                    ]
                ],
                'functions' => $functions,
                'function_call' => ['name' => 'generate_test_script']
            ];

            // Make the API request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl, $payload);

            // Parse and handle the response
            if ($response->successful()) {
                $responseData = $response->json();

                // Extract function call arguments
                $functionCall = $responseData['choices'][0]['message']['function_call'] ?? null;

                if ($functionCall && isset($functionCall['arguments'])) {
                    $arguments = json_decode($functionCall['arguments'], true);

                    return [
                        'success' => true,
                        'script_content' => $arguments['script_content'],
                        'suggested_name' => $arguments['suggested_name'] ?? null,
                        'coverage' => $arguments['estimated_coverage'] ?? []
                    ];
                }

                // Fallback if function call format is not as expected
                return [
                    'success' => false,
                    'message' => 'Invalid response format from OpenAI API'
                ];
            }

            // Handle API errors
            Log::error('OpenAI API error: ' . $response->body());
            return [
                'success' => false,
                'message' => 'Error from OpenAI API: ' . ($response->json()['error']['message'] ?? 'Unknown error')
            ];
        } catch (\Exception $e) {
            Log::error('Error generating test script with OpenAI: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while generating the test script: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Prepare context information for OpenAI
     *
     * @param array $data Input data
     * @return string Formatted context
     */
    protected function prepareContext(array $data)
    {
        $context = "Please generate a {$data['framework_type']} test script based on the following information:\n\n";

        // Add Jira story information if available
        if (!empty($data['context']['jira_story_id'])) {
            $jiraStory = JiraStory::find($data['context']['jira_story_id']);

            if ($jiraStory) {
                $context .= "## Jira Story\n";
                $context .= "- ID: {$jiraStory->jira_key}\n";
                $context .= "- Title: {$jiraStory->title}\n";
                $context .= "- Description: {$jiraStory->description}\n\n";

                // Add metadata if available
                if (!empty($jiraStory->metadata)) {
                    $context .= "- Acceptance Criteria:\n";

                    if (isset($jiraStory->metadata['acceptance_criteria'])) {
                        foreach ($jiraStory->metadata['acceptance_criteria'] as $criteria) {
                            $context .= "  * {$criteria}\n";
                        }
                    }

                    $context .= "\n";
                }
            }
        }

        // Add user stories information
        if (!empty($data['context']['user_stories']) && is_array($data['context']['user_stories'])) {
            $stories = JiraStory::whereIn('id', $data['context']['user_stories'])->get();

            if ($stories->count() > 0) {
                $context .= "## Additional User Stories\n";

                foreach ($stories as $story) {
                    $context .= "- {$story->jira_key}: {$story->title}\n";
                }

                $context .= "\n";
            }
        }

        // Add project description
        if (!empty($data['context']['project_description'])) {
            $context .= "## Project Description\n";
            $context .= $data['context']['project_description'] . "\n\n";
        }

        // Add custom instructions
        if (!empty($data['context']['custom_instructions'])) {
            $context .= "## Custom Instructions\n";
            $context .= $data['context']['custom_instructions'] . "\n\n";
        }

        // Process file content if available
        if (!empty($data['context']['files']) && is_array($data['context']['files'])) {
            $context .= "## File Contents\n";

            foreach ($data['context']['files'] as $file) {
                $context .= "### File: {$file['name']}\n";
                $context .= "```{$file['extension']}\n";
                $context .= $file['content'] . "\n";
                $context .= "```\n\n";
            }
        }

        // Add framework-specific instructions
        $context .= "## Framework Information\n";
        if ($data['framework_type'] === 'selenium_python') {
            $context .= "- Framework: Selenium with Python\n";
            $context .= "- Use unittest framework for test organization\n";
            $context .= "- Include proper setup and teardown methods\n";
            $context .= "- Use explicit waits for element interactions\n";
        } else if ($data['framework_type'] === 'cypress') {
            $context .= "- Framework: Cypress\n";
            $context .= "- Use describe/it pattern for test organization\n";
            $context .= "- Use cy commands for interactions\n";
            $context .= "- Include proper beforeEach setup if needed\n";
        }

        return $context;
    }

    /**
     * Get the system prompt based on the framework type
     *
     * @param string $frameworkType The selected framework type
     * @return string System prompt
     */
    protected function getSystemPrompt($frameworkType)
    {
        $basePrompt = "You are an expert test automation engineer specializing in generating high-quality, maintainable test scripts. ";

        if ($frameworkType === 'selenium_python') {
            return $basePrompt . "You specialize in Selenium with Python using the unittest framework. Generate comprehensive, well-structured tests that follow best practices such as explicit waits, proper assertions, and clear test case organization. Include appropriate comments and docstrings. Always include proper setup and teardown methods.";
        } else {
            return $basePrompt . "You specialize in Cypress framework for JavaScript-based UI testing. Generate comprehensive, well-structured tests that follow Cypress best practices, including proper command chaining, assertions, and test organization with describe/it blocks. Include appropriate comments and make use of Cypress's built-in retry-ability when interacting with elements.";
        }
    }
}
