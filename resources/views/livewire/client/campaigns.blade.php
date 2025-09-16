<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    📊 My Campaigns
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Manage and monitor your SMS campaigns
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('client.bulk-sms') }}" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    📤 New Campaign
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="mt-8">
            <div class="flex items-center space-x-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Filter by Status</label>
                    <select 
                        id="status" 
                        wire:model="statusFilter"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                        <option value="paused">Paused</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Campaigns List -->
        <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-md">
            @if($campaigns->count() > 0)
                <ul class="divide-y divide-gray-200">
                    @foreach($campaigns as $campaign)
                        <li>
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($campaign->status === 'completed')
                                                <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                                    <span class="text-green-600">✅</span>
                                                </div>
                                            @elseif($campaign->status === 'processing')
                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <span class="text-blue-600">⏳</span>
                                                </div>
                                            @elseif($campaign->status === 'failed')
                                                <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                                                    <span class="text-red-600">❌</span>
                                                </div>
                                            @elseif($campaign->status === 'paused')
                                                <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                                    <span class="text-yellow-600">⏸️</span>
                                                </div>
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                                                    <span class="text-gray-600">📋</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    {{ $campaign->name }}
                                                </p>
                                                <div class="ml-2 flex-shrink-0 flex">
                                                    <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($campaign->status === 'completed') bg-green-100 text-green-800
                                                        @elseif($campaign->status === 'processing') bg-blue-100 text-blue-800
                                                        @elseif($campaign->status === 'failed') bg-red-100 text-red-800
                                                        @elseif($campaign->status === 'paused') bg-yellow-100 text-yellow-800
                                                        @else bg-gray-100 text-gray-800 @endif">
                                                        {{ ucfirst($campaign->status) }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <div class="flex items-center text-sm text-gray-500">
                                                    <span class="mr-4">
                                                        📤 {{ number_format($campaign->sent_count) }}/{{ number_format($campaign->total_count) }} sent
                                                    </span>
                                                    @if($campaign->failed_count > 0)
                                                        <span class="mr-4 text-red-600">
                                                            ❌ {{ number_format($campaign->failed_count) }} failed
                                                        </span>
                                                    @endif
                                                    <span class="mr-4">
                                                        📊 {{ number_format($campaign->success_rate) }}% success
                                                    </span>
                                                    <span>
                                                        📅 {{ $campaign->created_at->format('M d, Y H:i') }}
                                                    </span>
                                                </div>
                                                @if($campaign->scheduled_at)
                                                    <div class="mt-1 text-sm text-blue-600">
                                                        🕒 Scheduled for {{ $campaign->scheduled_at->format('M d, Y H:i') }}
                                                    </div>
                                                @endif
                                                @if($campaign->status === 'processing' || $campaign->status === 'pending')
                                                    <div class="mt-2">
                                                        <div class="flex items-center">
                                                            <div class="w-32 bg-gray-200 rounded-full h-2">
                                                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $campaign->progress_percentage }}%"></div>
                                                            </div>
                                                            <span class="ml-2 text-xs text-gray-500">{{ number_format($campaign->progress_percentage, 1) }}%</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-shrink-0 flex space-x-2">
                                        @if($campaign->status === 'processing')
                                            <button class="bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-3 py-1 rounded-md text-xs font-medium">
                                                ⏸️ Pause
                                            </button>
                                        @elseif($campaign->status === 'paused')
                                            <button class="bg-green-100 hover:bg-green-200 text-green-800 px-3 py-1 rounded-md text-xs font-medium">
                                                ▶️ Resume
                                            </button>
                                        @endif
                                        <button class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-1 rounded-md text-xs font-medium">
                                            👁️ Details
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Campaign Content Preview -->
                                <div class="mt-3 text-sm text-gray-600 bg-gray-50 p-3 rounded-md">
                                    <p class="font-medium">Message:</p>
                                    <p class="mt-1">{{ Str::limit($campaign->content, 100) }}</p>
                                    @if($campaign->from)
                                        <p class="mt-1"><span class="font-medium">From:</span> {{ $campaign->from }}</p>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $campaigns->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <span class="text-4xl">📭</span>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No campaigns found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if($statusFilter)
                            No campaigns with status "{{ $statusFilter }}" found.
                        @else
                            You haven't created any campaigns yet.
                        @endif
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('client.bulk-sms') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            📤 Create Your First Campaign
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>