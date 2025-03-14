@extends('layouts.app')

@section('title', 'Manage Integrations')

@section('content')
<div class="py-6 px-8">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manage Integrations</h1>
            <p class="text-gray-600">Connect Arxitest with your development tools</p>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- Integrations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        <!-- Jira Integration Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">Jira</h3>
                    <div class="flex items-center">
                        @if (session('jira_access_token'))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                Connected
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <i data-lucide="circle" class="w-3 h-3 mr-1"></i>
                                Not Connected
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                        <i data-lucide="trello" class="w-6 h-6 text-blue-600"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-medium text-gray-900">Atlassian Jira</h4>
                        <p class="text-sm text-gray-500">Import stories and track test coverage</p>
                    </div>
                </div>

                @if (session('jira_access_token'))
                    <div class="mb-4 border border-gray-200 rounded-md bg-gray-50 p-3">
                        <div class="text-sm">
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-medium text-gray-700">Current Connection:</span>
                                <span class="text-blue-600">{{ session('jira_site_name') ?? 'Unknown' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-gray-700">Last synced:</span>
                                <span class="text-gray-600">{{ session('jira_connected_at') ? \Carbon\Carbon::parse(session('jira_connected_at'))->diffForHumans() : 'Never' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Reconnect to Jira Form -->
                    <form action="{{ url('/integrations/jira/reconnect') }}" method="POST" class="mt-4 mb-4">
                        @csrf
                        <div class="mb-4">
                            <label for="jira_domain" class="block text-sm font-medium text-gray-700 mb-1">Change Jira Domain (Optional)</label>
                            <input type="text" id="jira_domain" name="jira_domain"
                                   placeholder="your-company.atlassian.net"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <p class="mt-1 text-xs text-gray-500">Leave empty to use the default from your Jira account</p>
                        </div>
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium bg-blue-50 text-blue-700 border border-blue-200 rounded-md hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                            Reconnect to Jira
                        </button>
                    </form>

                    <div class="flex space-x-3">
                        <a href="{{ url('/jira/import') }}" class="flex-1 inline-flex justify-center items-center px-4 py-2 text-sm font-medium bg-blue-600 text-white border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                            Import Stories
                        </a>
                        <form action="{{ url('/integrations/jira/disconnect') }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium bg-white text-red-700 border border-red-300 rounded-md hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <i data-lucide="log-out" class="w-4 h-4 mr-2"></i>
                                Disconnect
                            </button>
                        </form>
                    </div>
                @else
                    <p class="text-sm text-gray-600 mb-4">Connect Jira to import user stories and generate test scripts automatically.</p>
                    <a href="{{ url('/jira/oauth') }}" class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium bg-blue-600 text-white border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i data-lucide="link" class="w-4 h-4 mr-2"></i>
                        Connect to Jira
                    </a>
                @endif
            </div>
        </div>

        <!-- GitHub Integration Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">GitHub</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        <i data-lucide="clock" class="w-3 h-3 mr-1"></i>
                        Coming Soon
                    </span>
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
                        <i data-lucide="github" class="w-6 h-6 text-gray-600"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-medium text-gray-900">GitHub</h4>
                        <p class="text-sm text-gray-500">Link tests to PRs and track coverage</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-4">Integration with GitHub repositories will be available soon.</p>
                <button disabled class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium bg-gray-100 text-gray-400 border border-gray-200 rounded-md cursor-not-allowed">
                    <i data-lucide="link" class="w-4 h-4 mr-2"></i>
                    Connect to GitHub
                </button>
            </div>
        </div>

        <!-- GitLab Integration Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">GitLab</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        <i data-lucide="clock" class="w-3 h-3 mr-1"></i>
                        Coming Soon
                    </span>
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
                        <i data-lucide="gitlab" class="w-6 h-6 text-gray-600"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-medium text-gray-900">GitLab</h4>
                        <p class="text-sm text-gray-500">Integrate tests with merge requests</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-4">Integration with GitLab repositories will be available soon.</p>
                <button disabled class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium bg-gray-100 text-gray-400 border border-gray-200 rounded-md cursor-not-allowed">
                    <i data-lucide="link" class="w-4 h-4 mr-2"></i>
                    Connect to GitLab
                </button>
            </div>
        </div>

        <!-- Jenkins Integration Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">Jenkins</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        <i data-lucide="clock" class="w-3 h-3 mr-1"></i>
                        Coming Soon
                    </span>
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
                        <i data-lucide="cpu" class="w-6 h-6 text-gray-600"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-medium text-gray-900">Jenkins</h4>
                        <p class="text-sm text-gray-500">Run tests as part of CI/CD pipeline</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-4">Integration with Jenkins CI/CD pipelines will be available soon.</p>
                <button disabled class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium bg-gray-100 text-gray-400 border border-gray-200 rounded-md cursor-not-allowed">
                    <i data-lucide="link" class="w-4 h-4 mr-2"></i>
                    Connect to Jenkins
                </button>
            </div>
        </div>

        <!-- Slack Integration Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">Slack</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        <i data-lucide="clock" class="w-3 h-3 mr-1"></i>
                        Coming Soon
                    </span>
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
                        <i data-lucide="message-square" class="w-6 h-6 text-gray-600"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-medium text-gray-900">Slack</h4>
                        <p class="text-sm text-gray-500">Get test results and notifications</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-4">Integration with Slack for notifications will be available soon.</p>
                <button disabled class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium bg-gray-100 text-gray-400 border border-gray-200 rounded-md cursor-not-allowed">
                    <i data-lucide="link" class="w-4 h-4 mr-2"></i>
                    Connect to Slack
                </button>
            </div>
        </div>

        <!-- Custom Integration Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden bg-gradient-to-br from-blue-50 via-purple-50 to-white">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-white">
                <div class="flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">Custom Integration</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <i data-lucide="code" class="w-3 h-3 mr-1"></i>
                        API
                    </span>
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-purple-100 rounded-lg flex items-center justify-center mr-4">
                        <i data-lucide="settings" class="w-6 h-6 text-indigo-600"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-medium text-gray-900">Custom Integration</h4>
                        <p class="text-sm text-gray-500">Connect to any system via our API</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-4">Use our API to integrate Arxitest with your custom tooling and systems.</p>
                <a href="#" class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium bg-indigo-600 text-white border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i data-lucide="code" class="w-4 h-4 mr-2"></i>
                    View API Documentation
                </a>
            </div>
        </div>
    </div>

    <!-- API Key Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mt-8">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">API Keys</h3>
                <button type="button" id="generate-api-key-btn" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                    <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                    Generate New Key
                </button>
            </div>
        </div>
        <div class="p-6">
            <p class="text-sm text-gray-600 mb-4">API keys allow external systems to authenticate with Arxitest. Keep your keys secure.</p>

            <div class="overflow-hidden overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Key Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Used</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Example API Key (Replace with dynamic data) -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Integration Key
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Mar 10, 2025
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Mar 14, 2025
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button class="text-red-600 hover:text-red-900 mr-3">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                                <button class="text-blue-600 hover:text-blue-900">
                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                        <!-- No API Keys Message -->
                        <tr class="hidden">
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                No API keys have been generated yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Integration Webhooks Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mt-8">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Webhooks</h3>
                <button type="button" id="add-webhook-btn" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                    <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                    Add Webhook
                </button>
            </div>
        </div>
        <div class="p-6">
            <p class="text-sm text-gray-600 mb-4">Webhooks allow Arxitest to send notifications to your systems when events occur.</p>

            <div class="overflow-hidden overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Events</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- No Webhooks Message -->
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                No webhooks have been set up yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- API Key Modal (hidden by default) -->
<div id="api-key-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Generate API Key</h3>
            <button type="button" class="close-modal text-gray-400 hover:text-gray-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="api-key-form">
            <div class="mb-4">
                <label for="key-name" class="block text-sm font-medium text-gray-700 mb-1">Key Name</label>
                <input type="text" id="key-name" name="key_name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" placeholder="e.g., Jenkins Integration">
            </div>
            <div class="mb-4">
                <label for="key-expiry" class="block text-sm font-medium text-gray-700 mb-1">Expiration</label>
                <select id="key-expiry" name="key_expiry" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="30">30 days</option>
                    <option value="90">90 days</option>
                    <option value="365">1 year</option>
                    <option value="0">Never (not recommended)</option>
                </select>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" class="close-modal px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Generate Key
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Webhook Modal (hidden by default) -->
<div id="webhook-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Add Webhook</h3>
            <button type="button" class="close-modal text-gray-400 hover:text-gray-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="webhook-form">
            <div class="mb-4">
                <label for="webhook-url" class="block text-sm font-medium text-gray-700 mb-1">Payload URL</label>
                <input type="url" id="webhook-url" name="webhook_url" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" placeholder="https://example.com/webhook">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Trigger Events</label>
                <div class="space-y-2">
                    <div class="flex items-center">
                        <input type="checkbox" id="event-test-execution" name="events[]" value="test.execution" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="event-test-execution" class="ml-2 block text-sm text-gray-700">Test Execution (Started/Completed)</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="event-test-creation" name="events[]" value="test.creation" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="event-test-creation" class="ml-2 block text-sm text-gray-700">Test Creation</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="event-jira-import" name="events[]" value="jira.import" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="event-jira-import" class="ml-2 block text-sm text-gray-700">Jira Import</label>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label for="webhook-secret" class="block text-sm font-medium text-gray-700 mb-1">Secret (Optional)</label>
                <input type="text" id="webhook-secret" name="webhook_secret" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" placeholder="Secret used to validate requests">
                <p class="mt-1 text-xs text-gray-500">Used to create a signature with each request for verification.</p>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" class="close-modal px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Create Webhook
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // API Key Modal
        const apiKeyModal = document.getElementById('api-key-modal');
        const apiKeyBtn = document.getElementById('generate-api-key-btn');
        const apiKeyForm = document.getElementById('api-key-form');

        // Webhook Modal
        const webhookModal = document.getElementById('webhook-modal');
        const webhookBtn = document.getElementById('add-webhook-btn');
        const webhookForm = document.getElementById('webhook-form');

        // Open API Key Modal
        if (apiKeyBtn) {
            apiKeyBtn.addEventListener('click', function() {
                apiKeyModal.classList.remove('hidden');
            });
        }

        // Open Webhook Modal
        if (webhookBtn) {
            webhookBtn.addEventListener('click', function() {
                webhookModal.classList.remove('hidden');
            });
        }

        // Close Modals
        document.querySelectorAll('.close-modal').forEach(button => {
            button.addEventListener('click', function() {
                apiKeyModal.classList.add('hidden');
                webhookModal.classList.add('hidden');
            });
        });

        // Close Modal on Outside Click
        window.addEventListener('click', function(event) {
            if (event.target === apiKeyModal) {
                apiKeyModal.classList.add('hidden');
            }
            if (event.target === webhookModal) {
                webhookModal.classList.add('hidden');
            }
        });

        // Handle API Key Form Submit
        if (apiKeyForm) {
            apiKeyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                // In a real implementation, this would send an AJAX request to create the API key

                // Mock response - show success message
                alert('API key generated successfully!');
                apiKeyModal.classList.add('hidden');

                // In a real implementation, you would refresh the API keys table
            });
        }

        // Handle Webhook Form Submit
        if (webhookForm) {
            webhookForm.addEventListener('submit', function(e) {
                e.preventDefault();
                // In a real implementation, this would send an AJAX request to create the webhook

                // Mock response - show success message
                alert('Webhook created successfully!');
                webhookModal.classList.add('hidden');

                // In a real implementation, you would refresh the webhooks table
            });
        }
    });
</script>
@endsection
