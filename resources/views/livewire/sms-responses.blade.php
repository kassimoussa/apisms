<div>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">📥 Réponses SMS</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Consultez et gérez les messages SMS entrants depuis Kannel
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <span class="w-1.5 h-1.5 mr-1.5 bg-green-400 rounded-full animate-pulse"></span>
                        Auto-refresh: {{ $refreshInterval }}s
                    </span>
                </div>
                <button wire:click="refreshData" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Actualiser
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500">Total Entrants</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_inbound']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500">Aujourd'hui</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ number_format($stats['today_inbound']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500">Non lus</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ number_format($stats['unread_count']) }}</dd>
                        </dl>
                    </div>
                </div>
                @if($stats['unread_count'] > 0)
                    <div class="mt-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            ⚠️ Attention requise
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500">Dernier reçu</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                @if($stats['last_received'])
                                    {{ $stats['last_received']->diffForHumans() }}
                                @else
                                    Jamais
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">🔍 Filtres & Recherche</h3>
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input wire:model.live="search" 
                               type="text" 
                               class="pl-10 block w-full border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Messages, numéros de téléphone...">
                    </div>
                </div>
                
                <div class="flex items-end space-x-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Client</label>
                        <select wire:model.live="selectedClient" 
                                class="border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 min-w-0 w-48">
                            <option value="all">Tous les clients</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">
                                    {{ $client->name }} ({{ $client->inbound_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button wire:click="$set('search', '')" 
                            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Effacer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages List -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        @if($inboundMessages->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-2.697-.413l-3.358 1.194a1 1 0 01-1.285-1.285l1.194-3.358A8.955 8.955 0 013 12a8 8 0 018-8 8 8 0 018 8z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No inbound messages</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if($search)
                        No messages match your search criteria.
                    @else
                        No SMS responses have been received yet.
                    @endif
                </p>
                @if($search)
                    <button wire:click="$set('search', '')" 
                            class="mt-3 text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                        Clear search
                    </button>
                @endif
            </div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach($inboundMessages as $message)
                    <li class="px-6 py-4 {{ !($message->metadata['read'] ?? false) ? 'bg-blue-50 border-l-4 border-blue-400' : '' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3">
                                    <!-- Status Indicator -->
                                    <div class="flex-shrink-0">
                                        @if(!($message->metadata['read'] ?? false))
                                            <div class="h-2.5 w-2.5 bg-blue-400 rounded-full"></div>
                                        @else
                                            <div class="h-2.5 w-2.5 bg-gray-300 rounded-full"></div>
                                        @endif
                                    </div>
                                    
                                    <!-- Message Info -->
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-gray-900">
                                                📱 From: {{ $message->from }} → To: {{ $message->to }}
                                            </p>
                                            <div class="flex items-center space-x-2 text-xs text-gray-500">
                                                <span class="bg-gray-100 px-2 py-1 rounded">{{ $message->client->name }}</span>
                                                <time datetime="{{ $message->created_at->toISOString() }}">
                                                    {{ $message->created_at->format('Y-m-d H:i:s') }}
                                                </time>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-2">
                                            <div class="bg-gray-50 rounded-lg p-3">
                                                <p class="text-sm text-gray-800">{{ $message->content }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-2 flex items-center justify-between">
                                            <div class="flex items-center space-x-4 text-xs text-gray-500">
                                                <span>📅 Received: {{ $message->created_at->diffForHumans() }}</span>
                                                @if($message->delivered_at)
                                                    <span>✅ Delivered: {{ $message->delivered_at->diffForHumans() }}</span>
                                                @endif
                                            </div>
                                            
                                            <!-- Actions -->
                                            <div class="flex items-center space-x-2">
                                                @if(!($message->metadata['read'] ?? false))
                                                    <button wire:click="markAsRead({{ $message->id }})" 
                                                            class="text-indigo-600 hover:text-indigo-900 text-xs font-medium">
                                                        👁️ Mark as Read
                                                    </button>
                                                @else
                                                    <span class="text-xs text-gray-500">✅ Read</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            
            <!-- Pagination -->
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $inboundMessages->links() }}
            </div>
        @endif
    </div>

    <!-- Auto Refresh Script -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Auto refresh every 30 seconds
            setInterval(() => {
                Livewire.first().refreshData();
            }, {{ $refreshInterval * 1000 }});
            
            // Listen for SMS read events
            Livewire.on('sms-marked-read', (smsId) => {
                console.log('SMS marked as read:', smsId);
            });
        });
    </script>
</div>
