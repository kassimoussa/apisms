<div>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Client Management</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Manage API clients, generate keys, and configure access controls
                </p>
            </div>
            <div>
                <button wire:click="toggleCreateForm" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Client
                </button>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('message') }}
        </div>
    @endif

    <!-- Create Client Form -->
    @if($showCreateForm)
        <div class="mb-8 bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Client</h3>
            
            <form wire:submit.prevent="createClient" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Client Name</label>
                        <input wire:model="name" type="text" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="e.g., DPCR Fleet Management">
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rate Limit (per minute)</label>
                        <input wire:model="rate_limit" type="number" min="1" max="1000"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('rate_limit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea wire:model="description" rows="2"
                              class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Brief description of the client's purpose"></textarea>
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Allowed IPs (optional)</label>
                    <input wire:model="allowed_ips" type="text"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="192.168.1.1, 10.0.0.1 (comma separated)">
                    @error('allowed_ips') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <p class="text-xs text-gray-500 mt-1">Leave empty to allow all IPs</p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" wire:click="toggleCreateForm"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                        Create Client
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Clients List -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">API Clients</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Manage your API clients and their access keys</p>
        </div>
        
        <ul class="divide-y divide-gray-200">
            @forelse($clients as $client)
                <li class="px-4 py-4 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @if($client->active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            🟢 Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            🔴 Inactive
                                        </span>
                                    @endif
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="text-sm font-medium text-gray-900">{{ $client->name }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $client->description ?: 'No description' }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        Created {{ $client->created_at->diffForHumans() }} • 
                                        {{ $client->sms_messages_count }} SMS sent •
                                        Rate limit: {{ $client->rate_limit }}/min
                                    </div>
                                </div>
                            </div>
                            
                            <!-- API Key Display -->
                            <div class="mt-3 bg-gray-50 p-3 rounded-md">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">API Key</label>
                                        <code class="text-sm text-gray-900 font-mono">{{ $client->api_key }}</code>
                                    </div>
                                    <div class="ml-4">
                                        <button onclick="navigator.clipboard.writeText('{{ $client->api_key }}')"
                                                class="text-indigo-600 hover:text-indigo-900 text-sm">
                                            📋 Copy
                                        </button>
                                    </div>
                                </div>
                                
                                @if($client->allowed_ips)
                                    <div class="mt-2">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Allowed IPs</label>
                                        <div class="text-sm text-gray-600">
                                            @foreach($client->allowed_ips as $ip)
                                                <span class="inline-block bg-gray-200 rounded px-2 py-1 text-xs mr-1">{{ $ip }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="ml-6 flex flex-col space-y-2">
                            <button wire:click="toggleClient({{ $client->id }})"
                                    class="text-sm {{ $client->active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}">
                                {{ $client->active ? '🛑 Deactivate' : '✅ Activate' }}
                            </button>
                            
                            <button wire:click="regenerateApiKey({{ $client->id }})"
                                    onclick="return confirm('Are you sure? This will invalidate the current API key.')"
                                    class="text-sm text-indigo-600 hover:text-indigo-900">
                                🔄 Regenerate Key
                            </button>
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-4 py-8 sm:px-6 text-center">
                    <div class="text-gray-500">
                        <p class="text-sm">No API clients found</p>
                        <p class="text-xs mt-1">Create your first client to get started</p>
                    </div>
                </li>
            @endforelse
        </ul>
        
        <!-- Pagination -->
        @if($clients->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</div>
