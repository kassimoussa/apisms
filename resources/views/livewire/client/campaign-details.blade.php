<div class="py-6">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <li>
                            <a href="{{ route('client.campaigns') }}" class="text-gray-400 hover:text-gray-500">
                                📊 Campagnes
                            </a>
                        </li>
                        <li>
                            <span class="text-gray-400">/</span>
                        </li>
                        <li>
                            <span class="text-gray-900 font-medium">{{ $campaign->name }}</span>
                        </li>
                    </ol>
                </nav>
                <h2 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    📋 Détails de la Campagne
                </h2>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                @if($campaign->status === 'processing')
                    <button 
                        wire:click="pauseCampaign" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-yellow-700 bg-yellow-100 hover:bg-yellow-200">
                        ⏸️ Mettre en Pause
                    </button>
                @elseif($campaign->status === 'paused')
                    <button 
                        wire:click="resumeCampaign" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-green-700 bg-green-100 hover:bg-green-200">
                        ▶️ Reprendre
                    </button>
                @endif
                <a href="{{ route('client.campaigns') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    ← Retour aux Campagnes
                </a>
            </div>
        </div>

        <!-- Campaign Overview -->
        <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Informations Générales</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Détails et statistiques de la campagne</p>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Nom de la campagne</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $campaign->name }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Statut</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($campaign->status === 'completed') bg-green-100 text-green-800
                                @elseif($campaign->status === 'processing') bg-blue-100 text-blue-800
                                @elseif($campaign->status === 'failed') bg-red-100 text-red-800
                                @elseif($campaign->status === 'paused') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($campaign->status) }}
                            </span>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Progression</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex items-center">
                                <div class="w-48 bg-gray-200 rounded-full h-2 mr-3">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $campaign->progress_percentage }}%"></div>
                                </div>
                                <span class="text-sm font-medium">{{ number_format($campaign->progress_percentage, 1) }}%</span>
                            </div>
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Statistiques</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ number_format($campaign->total_count) }}</div>
                                    <div class="text-xs text-gray-500">Total</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ number_format($campaign->sent_count) }}</div>
                                    <div class="text-xs text-gray-500">Envoyés</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-red-600">{{ number_format($campaign->failed_count) }}</div>
                                    <div class="text-xs text-gray-500">Échoués</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-600">{{ number_format($campaign->pending_count) }}</div>
                                    <div class="text-xs text-gray-500">En attente</div>
                                </div>
                            </div>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Taux de succès</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <span class="text-lg font-semibold {{ $campaign->success_rate >= 95 ? 'text-green-600' : ($campaign->success_rate >= 80 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ number_format($campaign->success_rate, 1) }}%
                            </span>
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Dates</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="space-y-1">
                                <div><strong>Créée:</strong> {{ $campaign->created_at->format('d/m/Y H:i:s') }}</div>
                                @if($campaign->scheduled_at)
                                    <div><strong>Programmée:</strong> {{ $campaign->scheduled_at->format('d/m/Y H:i:s') }}</div>
                                @endif
                                @if($campaign->started_at)
                                    <div><strong>Démarrée:</strong> {{ $campaign->started_at->format('d/m/Y H:i:s') }}</div>
                                @endif
                                @if($campaign->completed_at)
                                    <div><strong>Terminée:</strong> {{ $campaign->completed_at->format('d/m/Y H:i:s') }}</div>
                                @endif
                            </div>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Contenu du message</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="bg-gray-100 p-3 rounded-md">
                                <p class="whitespace-pre-wrap">{{ $campaign->content }}</p>
                                @if($campaign->from)
                                    <p class="mt-2 text-xs text-gray-600"><strong>Expéditeur:</strong> {{ $campaign->from }}</p>
                                @endif
                            </div>
                        </dd>
                    </div>
                    @if($campaign->failure_reason)
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Raison de l'échec</dt>
                        <dd class="mt-1 text-sm text-red-600 sm:mt-0 sm:col-span-2">{{ $campaign->failure_reason }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Messages List -->
        <div class="mt-8">
            <div class="md:flex md:items-center md:justify-between">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Détail des Messages</h3>
                <div class="mt-4 md:mt-0 flex space-x-4">
                    <!-- Search Filter -->
                    <div class="relative">
                        <input 
                            type="text" 
                            wire:model.live="searchFilter"
                            placeholder="Rechercher un numéro..."
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                    </div>
                    <!-- Status Filter -->
                    <div>
                        <select 
                            wire:model.live="statusFilter"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                            <option value="">Tous les statuts</option>
                            <option value="pending">En attente</option>
                            <option value="sent">Envoyé</option>
                            <option value="delivered">Livré</option>
                            <option value="failed">Échoué</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-md">
                @if($messages->count() > 0)
                    <ul class="divide-y divide-gray-200">
                        @foreach($messages as $message)
                            <li class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            @if($message->status === 'delivered')
                                                <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                                    <span class="text-green-600 text-sm">✅</span>
                                                </div>
                                            @elseif($message->status === 'sent')
                                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <span class="text-blue-600 text-sm">📤</span>
                                                </div>
                                            @elseif($message->status === 'failed')
                                                <div class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center">
                                                    <span class="text-red-600 text-sm">❌</span>
                                                </div>
                                            @else
                                                <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center">
                                                    <span class="text-gray-600 text-sm">⏳</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="flex items-center">
                                                <p class="text-sm font-medium text-gray-900">{{ $message->to }}</p>
                                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($message->status === 'delivered') bg-green-100 text-green-800
                                                    @elseif($message->status === 'sent') bg-blue-100 text-blue-800
                                                    @elseif($message->status === 'failed') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($message->status) }}
                                                </span>
                                            </div>
                                            <div class="mt-1 flex items-center text-sm text-gray-500">
                                                <span>📅 {{ $message->created_at->format('d/m/Y H:i:s') }}</span>
                                                @if($message->kannel_id)
                                                    <span class="ml-4">🆔 {{ $message->kannel_id }}</span>
                                                @endif
                                                @if($message->delivered_at)
                                                    <span class="ml-4 text-green-600">✅ Livré le {{ $message->delivered_at->format('d/m H:i') }}</span>
                                                @endif
                                            </div>
                                            @if($message->failure_reason)
                                                <div class="mt-1 text-sm text-red-600">
                                                    ❌ {{ $message->failure_reason }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $messages->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <span class="text-4xl">📭</span>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun message trouvé</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            @if($statusFilter || $searchFilter)
                                Aucun message correspondant aux filtres appliqués.
                            @else
                                Cette campagne ne contient aucun message.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50">
            {{ session('success') }}
        </div>
    @endif
</div>