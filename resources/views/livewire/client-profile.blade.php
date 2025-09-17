<div class="w-full py-6 px-4 sm:px-6 lg:px-8">
    <!-- Success Messages -->
    @if (session()->has('message'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('message') }}
        </div>
    @endif

    <!-- New API Key Alert -->
    @if (session()->has('newApiKey'))
        <div class="mb-6 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
            <div class="flex items-center justify-between">
                <div>
                    <strong>🔑 Nouvelle clé API générée :</strong>
                    <code class="bg-blue-200 px-2 py-1 rounded text-sm">{{ session('newApiKey') }}</code>
                </div>
                <button data-copy-text="{{ session('newApiKey') }}" 
                        class="text-blue-600 hover:text-blue-800 text-sm">
                    📋 Copy
                </button>
            </div>
            <p class="text-xs mt-2">⚠️ Sauvegardez cette clé en sécurité - elle ne sera plus affichée !</p>
        </div>
    @endif

    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.clients') }}" 
                   class="text-gray-500 hover:text-gray-700">
                    ← Retour aux Clients
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $client->name }}</h1>
                    <div class="flex items-center space-x-4 mt-1">
                        @if($client->active && !$client->isSuspended())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                🟢 Actif
                            </span>
                        @elseif($client->isSuspended())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                🔴 Suspendu
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                🟡 Inactif
                            </span>
                        @endif

                        @if($client->isOnTrial())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                🎯 Essai (fin le {{ $client->trial_ends_at->format('d/m') }})
                            </span>
                        @endif

                        <span class="text-sm text-gray-500">{{ $client->getClientTypeLabel() }}</span>
                        <span class="text-sm text-gray-500">Créé {{ $client->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
            
            <div class="flex space-x-3">
                <button wire:click="toggleEditModal"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    ✏️ Modifier Client
                </button>
                
                @if($client->isSuspended())
                    <button wire:click="unsuspendClient"
                            onclick="return confirm('Êtes-vous sûr de vouloir réactiver ce client ?')"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                        ✅ Réactiver
                    </button>
                @else
                    <button wire:click="suspendClient"
                            onclick="return confirm('Êtes-vous sûr de vouloir suspendre ce client ?')"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                        🛑 Suspendre
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center text-white text-sm">
                            💰
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Solde</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $client->balance }} {{ $client->currency }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center text-white text-sm">
                            📱
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total SMS</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($usageStats['total_messages']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center text-white text-sm">
                            📊
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Usage Aujourd'hui</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $usageStats['daily_usage'] }}/{{ number_format($client->daily_sms_limit) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center text-white text-sm">
                            ⚡
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Limite de Débit</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $client->rate_limit }}/min</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white shadow rounded-lg">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex">
                <button wire:click="switchTab('overview')"
                        class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm {{ $activeTab === 'overview' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    📋 Aperçu
                </button>
                <button wire:click="switchTab('usage')"
                        class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm {{ $activeTab === 'usage' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    📊 Usage & Statistiques
                </button>
                <button wire:click="switchTab('technical')"
                        class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm {{ $activeTab === 'technical' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    🔧 Technique
                </button>
                <button wire:click="switchTab('activity')"
                        class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm {{ $activeTab === 'activity' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    📈 Activité Récente
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Overview Tab -->
            @if($activeTab === 'overview')
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">👤 Informations de Base</h3>
                        <div class="bg-gray-50 p-4 rounded-lg space-y-3">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Nom d'utilisateur :</span>
                                    <p class="text-sm text-gray-900">{{ $client->username }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Type de Client :</span>
                                    <p class="text-sm text-gray-900">{{ $client->getClientTypeLabel() }}</p>
                                </div>
                            </div>
                            @if($client->description)
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Description :</span>
                                    <p class="text-sm text-gray-900">{{ $client->description }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Contact Information -->
                        @if($client->email || $client->phone || $client->company || $client->address)
                            <h3 class="text-lg font-medium text-gray-900 mb-4 mt-6">📞 Informations de Contact</h3>
                            <div class="bg-gray-50 p-4 rounded-lg space-y-3">
                                @if($client->email)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Email :</span>
                                        <p class="text-sm text-gray-900">{{ $client->email }}</p>
                                    </div>
                                @endif
                                @if($client->phone)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Téléphone :</span>
                                        <p class="text-sm text-gray-900">{{ $client->phone }}</p>
                                    </div>
                                @endif
                                @if($client->company)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Entreprise :</span>
                                        <p class="text-sm text-gray-900">{{ $client->company }}</p>
                                    </div>
                                @endif
                                @if($client->industry)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Secteur :</span>
                                        <p class="text-sm text-gray-900">{{ $client->industry }}</p>
                                    </div>
                                @endif
                                @if($client->website)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Site Web :</span>
                                        <p class="text-sm text-gray-900">
                                            <a href="{{ $client->website }}" target="_blank" class="text-indigo-600 hover:text-indigo-800">
                                                {{ $client->website }}
                                            </a>
                                        </p>
                                    </div>
                                @endif
                                @if($client->address)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Adresse :</span>
                                        <p class="text-sm text-gray-900">{{ $client->address }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Facturation & Limites -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">💰 Facturation & Limites</h3>
                        <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Solde :</span>
                                    <p class="text-lg font-semibold text-gray-900">{{ $client->balance }} {{ $client->currency }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Limite de Crédit :</span>
                                    <p class="text-lg font-semibold text-gray-900">{{ $client->credit_limit }} {{ $client->currency }}</p>
                                </div>
                            </div>
                            
                            <div class="border-t pt-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Daily SMS Limit:</span>
                                        <p class="text-sm text-gray-900">{{ number_format($client->daily_sms_limit) }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Monthly SMS Limit:</span>
                                        <p class="text-sm text-gray-900">{{ number_format($client->monthly_sms_limit) }}</p>
                                    </div>
                                </div>
                            </div>

                            @if($client->auto_recharge)
                                <div class="border-t pt-4">
                                    <span class="text-sm font-medium text-gray-500">Recharge Automatique :</span>
                                    <p class="text-sm text-gray-900">
                                        ✅ Activée - {{ $client->auto_recharge_amount }} {{ $client->currency }} 
                                        when balance drops below {{ $client->auto_recharge_threshold }} {{ $client->currency }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Usage & Stats Tab -->
            @if($activeTab === 'usage')
                <div class="space-y-6">
                    <!-- Usage Overview -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-green-50 p-6 rounded-lg">
                            <h4 class="text-sm font-medium text-green-900 mb-2">✅ Livrés</h4>
                            <p class="text-2xl font-bold text-green-600">{{ number_format($usageStats['successful_messages']) }}</p>
                            <p class="text-xs text-green-600 mt-1">
                                {{ $usageStats['total_messages'] > 0 ? round(($usageStats['successful_messages'] / $usageStats['total_messages']) * 100, 1) : 0 }}% taux de succès
                            </p>
                        </div>
                        
                        <div class="bg-red-50 p-6 rounded-lg">
                            <h4 class="text-sm font-medium text-red-900 mb-2">❌ Échoués</h4>
                            <p class="text-2xl font-bold text-red-600">{{ number_format($usageStats['failed_messages']) }}</p>
                            <p class="text-xs text-red-600 mt-1">
                                {{ $usageStats['total_messages'] > 0 ? round(($usageStats['failed_messages'] / $usageStats['total_messages']) * 100, 1) : 0 }}% taux d'échec
                            </p>
                        </div>
                        
                        <div class="bg-yellow-50 p-6 rounded-lg">
                            <h4 class="text-sm font-medium text-yellow-900 mb-2">⏳ En Attente</h4>
                            <p class="text-2xl font-bold text-yellow-600">{{ number_format($usageStats['pending_messages']) }}</p>
                            <p class="text-xs text-yellow-600 mt-1">En cours de traitement</p>
                        </div>
                    </div>

                    <!-- Quota Usage -->
                    <div class="bg-white border rounded-lg p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">📊 Utilisation des Quotas</h4>
                        
                        <!-- Quota Quotidien -->
                        <div class="mb-6">
                            <div class="flex justify-between text-sm font-medium text-gray-900 mb-2">
                                <span>Quota Quotidien</span>
                                <span>{{ $usageStats['daily_usage'] }} / {{ number_format($client->daily_sms_limit) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" 
                                     style="width: {{ $client->daily_sms_limit > 0 ? min(($usageStats['daily_usage'] / $client->daily_sms_limit) * 100, 100) : 0 }}%"></div>
                            </div>
                        </div>

                        <!-- Quota Mensuel -->
                        <div>
                            <div class="flex justify-between text-sm font-medium text-gray-900 mb-2">
                                <span>Quota Mensuel</span>
                                <span>{{ $usageStats['monthly_usage'] }} / {{ number_format($client->monthly_sms_limit) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-purple-600 h-2 rounded-full" 
                                     style="width: {{ $client->monthly_sms_limit > 0 ? min(($usageStats['monthly_usage'] / $client->monthly_sms_limit) * 100, 100) : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Technical Tab -->
            @if($activeTab === 'technical')
                <div class="space-y-6">
                    <!-- API Key Management -->
                    <div class="bg-white border rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-medium text-gray-900">🔑 API Key</h4>
                            <button wire:click="regenerateApiKey"
                                    onclick="return confirm('Are you sure? This will invalidate the current API key.')"
                                    class="text-sm text-indigo-600 hover:text-indigo-900">
                                🔄 Regénérer Clé
                            </button>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-md">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Clé API Actuelle</label>
                                    <code class="text-sm text-gray-900 font-mono">{{ $client->getMaskedApiKey() ?: 'Aucune clé API trouvée' }}</code>
                                    @if($client->api_key_expires_at)
                                        <div class="text-xs text-gray-500 mt-1">
                                            Expire: {{ $client->api_key_expires_at->format('d/m/Y') }}
                                            @if($client->isApiKeyExpired())
                                                <span class="text-red-600 font-medium">🔴 EXPIRÉE</span>
                                            @elseif(now()->diffInDays($client->api_key_expires_at) <= 30)
                                                <span class="text-yellow-600 font-medium">🟡 Expire bientôt</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Settings -->
                    <div class="bg-white border rounded-lg p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">🔒 Paramètres de Sécurité</h4>
                        
                        <div class="space-y-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Limite de Débit :</span>
                                <p class="text-sm text-gray-900">{{ $client->rate_limit }} requêtes par minute</p>
                            </div>
                            
                            @if($client->allowed_ips)
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Allowed IPs:</span>
                                    <div class="mt-1">
                                        @foreach($client->allowed_ips as $ip)
                                            <span class="inline-block bg-gray-200 rounded px-2 py-1 text-xs mr-1 mb-1">{{ $ip }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div>
                                    <span class="text-sm font-medium text-gray-500">IP Restrictions:</span>
                                    <p class="text-sm text-gray-900">All IPs allowed</p>
                                </div>
                            @endif

                            @if($client->last_login_at)
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Last Login:</span>
                                    <p class="text-sm text-gray-900">{{ $client->last_login_at->diffForHumans() }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Recent Activity Tab -->
            @if($activeTab === 'activity')
                <div class="space-y-6">
                    <div class="bg-white border rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h4 class="text-lg font-medium text-gray-900">📈 Recent SMS Messages</h4>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @forelse($client->smsMessages()->latest()->limit(10)->get() as $message)
                                <div class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-900">
                                                To: <span class="font-medium">{{ $message->to_number }}</span>
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ Str::limit($message->message, 100) }}
                                            </p>
                                        </div>
                                        <div class="ml-4 text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $message->status === 'delivered' ? 'bg-green-100 text-green-800' : 
                                                   ($message->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ ucfirst($message->status) }}
                                            </span>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $message->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="px-6 py-8 text-center">
                                    <p class="text-gray-500">No messages found</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Edit Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Edit Client - {{ $client->name }}</h3>
                        <button wire:click="toggleEditModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <form wire:submit.prevent="updateClient" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Info -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Basic Information</h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Name</label>
                                        <input wire:model="editData.name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @error('editData.name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email</label>
                                        <input wire:model="editData.email" type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @error('editData.email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                                        <input wire:model="editData.phone" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @error('editData.phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Company</label>
                                        <input wire:model="editData.company" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @error('editData.company') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Facturation & Limites -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Facturation & Limites</h4>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Balance</label>
                                            <input wire:model="editData.balance" type="number" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            @error('editData.balance') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Currency</label>
                                            <select wire:model="editData.currency" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="EUR">EUR</option>
                                                <option value="USD">USD</option>
                                                <option value="XOF">XOF</option>
                                            </select>
                                            @error('editData.currency') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Daily SMS Limit</label>
                                        <input wire:model="editData.daily_sms_limit" type="number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @error('editData.daily_sms_limit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Monthly SMS Limit</label>
                                        <input wire:model="editData.monthly_sms_limit" type="number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @error('editData.monthly_sms_limit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-6 border-t">
                            <button type="button" wire:click="toggleEditModal" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- JavaScript for clipboard functionality -->
    <script>
        // Copy functionality for API keys
        document.addEventListener('DOMContentLoaded', () => {
            document.addEventListener('click', (e) => {
                if (e.target.closest('[data-copy-text]')) {
                    e.preventDefault();
                    const copyText = e.target.closest('[data-copy-text]').getAttribute('data-copy-text');
                    
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(copyText).then(() => {
                            showCopySuccess();
                        });
                    } else {
                        // Fallback for older browsers
                        const textArea = document.createElement('textarea');
                        textArea.value = copyText;
                        document.body.appendChild(textArea);
                        textArea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textArea);
                        showCopySuccess();
                    }
                }
            });
        });

        function showCopySuccess() {
            const successMsg = document.createElement('div');
            successMsg.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded shadow z-50';
            successMsg.innerHTML = '✅ API key copied to clipboard!';
            document.body.appendChild(successMsg);
            
            setTimeout(() => {
                if (successMsg.parentNode) {
                    successMsg.parentNode.removeChild(successMsg);
                }
            }, 3000);
        }
    </script>
</div>
