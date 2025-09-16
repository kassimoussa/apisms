<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Welcome back, {{ $client->name }}!
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Here's your SMS campaign overview
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('client.bulk-sms') }}" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    📤 New Campaign
                </a>
            </div>
        </div>

        <!-- Today's Stats -->
        <div class="mt-8">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Today's Statistics</h3>
            <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Sent SMS -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">✅</span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">SMS Sent</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($todayStats['sent']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Failed SMS -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">❌</span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Failed SMS</dt>
                                    <dd class="text-lg font-medium text-red-600">{{ number_format($todayStats['failed']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending SMS -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">⏳</span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Pending SMS</dt>
                                    <dd class="text-lg font-medium text-yellow-600">{{ number_format($todayStats['pending']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Campaigns -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">📊</span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Campaigns</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($todayStats['campaigns']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Week Stats -->
        <div class="mt-8">
            <h3 class="text-lg leading-6 font-medium text-gray-900">This Week's Performance</h3>
            <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="text-2xl text-white">📤</span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-blue-100 truncate">Total Sent</dt>
                                    <dd class="text-2xl font-bold text-white">{{ number_format($weekStats['sent']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-red-500 to-red-600 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="text-2xl text-white">💥</span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-red-100 truncate">Total Failed</dt>
                                    <dd class="text-2xl font-bold text-white">{{ number_format($weekStats['failed']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-500 to-green-600 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="text-2xl text-white">🚀</span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-green-100 truncate">Campaigns</dt>
                                    <dd class="text-2xl font-bold text-white">{{ number_format($weekStats['campaigns']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Key Status -->
        <div class="mt-8 bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">🔑</span>
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">API Key Status</h3>
                            <p class="text-sm text-gray-500">
                                @if($client->api_key_hash)
                                    @if($client->isApiKeyExpired())
                                        <span class="text-red-600 font-semibold">Your API key has expired</span>
                                    @else
                                        <span class="text-green-600">Your API key is active</span>
                                        @if($client->api_key_expires_at)
                                            • Expires {{ $client->api_key_expires_at->diffForHumans() }}
                                        @endif
                                    @endif
                                @else
                                    <span class="text-yellow-600">No API key configured</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('client.api-keys') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-600 bg-blue-100 hover:bg-blue-200">
                        Manage Keys
                    </a>
                </div>
                
                @if($client->api_key_hash && $client->getMaskedApiKey())
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                        <code class="text-sm font-mono text-gray-800">{{ $client->getMaskedApiKey() }}</code>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Campaigns -->
        <div class="mt-8">
            <div class="flex items-center justify-between">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Campaigns</h3>
                <a href="{{ route('client.campaigns') }}" class="text-sm text-blue-600 hover:text-blue-500">
                    View all campaigns →
                </a>
            </div>
            <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-md">
                @if(count($recentCampaigns) > 0)
                    <ul class="divide-y divide-gray-200">
                        @foreach($recentCampaigns as $campaign)
                            <li>
                                <div class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($campaign['status'] === 'completed')
                                                    <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                                        <span class="text-green-600">✅</span>
                                                    </div>
                                                @elseif($campaign['status'] === 'processing')
                                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                        <span class="text-blue-600">⏳</span>
                                                    </div>
                                                @elseif($campaign['status'] === 'failed')
                                                    <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                                                        <span class="text-red-600">❌</span>
                                                    </div>
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                                                        <span class="text-gray-600">📋</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $campaign['name'] }}</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $campaign['sent'] }}/{{ $campaign['total'] }} sent
                                                    @if($campaign['failed'] > 0)
                                                        • {{ $campaign['failed'] }} failed
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="text-sm text-gray-500 mr-4">{{ $campaign['created_at'] }}</div>
                                            @if($campaign['status'] === 'processing' || $campaign['status'] === 'pending')
                                                <div class="flex items-center">
                                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $campaign['progress'] }}%"></div>
                                                    </div>
                                                    <span class="ml-2 text-xs text-gray-500">{{ number_format($campaign['progress'], 1) }}%</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-12">
                        <span class="text-4xl">📭</span>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No campaigns yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating your first bulk SMS campaign.</p>
                        <div class="mt-6">
                            <a href="{{ route('client.bulk-sms') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                📤 Create Campaign
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>