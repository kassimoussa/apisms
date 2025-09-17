<div class="py-6">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    🔑 Gestion des Clés API
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Gérez vos clés d'accès à l'API SMS Gateway
                </p>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mt-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mt-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div class="mt-6 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
                {{ session('info') }}
            </div>
        @endif

        <!-- Current API Key Status -->
        <div class="mt-8 bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">État Actuel de votre Clé API</h3>
                
                <div class="mt-5">
                    @if($client->api_key_hash)
                        <!-- API Key exists -->
                        <div class="flex items-center justify-between p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-green-500 text-xl">✅</span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">
                                        Clé API Active
                                    </p>
                                    <p class="text-xs text-green-600">
                                        @if($client->api_key_expires_at)
                                            Expire le {{ $client->api_key_expires_at->format('d/m/Y à H:i') }}
                                            @if($client->isApiKeyExpired())
                                                <span class="text-red-600 font-semibold">(EXPIRÉE)</span>
                                            @else
                                                <span class="text-green-600">({{ $client->api_key_expires_at->diffForHumans() }})</span>
                                            @endif
                                        @else
                                            Aucune date d'expiration
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button 
                                    wire:click="toggleShowCurrentKey"
                                    class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-1 rounded-md text-xs font-medium"
                                >
                                    {{ $showCurrentKey ? '🙈 Masquer' : '👁️ Afficher' }}
                                </button>
                            </div>
                        </div>

                        <!-- Show current key if requested -->
                        @if($showCurrentKey && $client->getDecryptedApiKey())
                            <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Votre Clé API :</label>
                                <div class="flex items-center space-x-2">
                                    <code class="flex-1 p-2 bg-white border rounded text-sm font-mono">{{ $client->getDecryptedApiKey() }}</code>
                                    <button 
                                        onclick="copyToClipboard('{{ $client->getDecryptedApiKey() }}')" 
                                        class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-2 rounded text-xs font-medium"
                                        title="Copier"
                                    >
                                        📋
                                    </button>
                                </div>
                            </div>
                        @elseif($showCurrentKey)
                            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-sm text-yellow-800">⚠️ Impossible de récupérer la clé API actuelle. Veuillez la régénérer.</p>
                            </div>
                        @endif

                        <!-- Masked key display -->
                        @if(!$showCurrentKey && $client->getMaskedApiKey())
                            <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Clé API (masquée) :</label>
                                <code class="block p-2 bg-white border rounded text-sm font-mono">{{ $client->getMaskedApiKey() }}</code>
                            </div>
                        @endif

                    @else
                        <!-- No API Key -->
                        <div class="flex items-center justify-between p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-yellow-500 text-xl">⚠️</span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-yellow-800">
                                        Aucune Clé API
                                    </p>
                                    <p class="text-xs text-yellow-600">
                                        Vous devez générer une clé API pour utiliser nos services
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="mt-6 flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-3 sm:space-y-0">
                    <div class="text-sm text-gray-500">
                        <p>💡 <strong>Conseil :</strong> Gardez votre clé API en sécurité et ne la partagez jamais.</p>
                    </div>
                    <div class="flex space-x-3">
                        @if($client->api_key_hash)
                            @if(!$confirmRegenerate)
                                <button 
                                    wire:click="$set('confirmRegenerate', true)"
                                    class="bg-orange-100 hover:bg-orange-200 text-orange-800 px-4 py-2 rounded-md text-sm font-medium"
                                >
                                    🔄 Régénérer la Clé
                                </button>
                            @else
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-600">Êtes-vous sûr ?</span>
                                    <button 
                                        wire:click="regenerateToken"
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-medium"
                                    >
                                        Oui, régénérer
                                    </button>
                                    <button 
                                        wire:click="$set('confirmRegenerate', false)"
                                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-3 py-1 rounded text-xs font-medium"
                                    >
                                        Annuler
                                    </button>
                                </div>
                            @endif
                        @else
                            <button 
                                wire:click="regenerateToken"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium"
                            >
                                🔑 Générer une Clé API
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- New Token Display -->
        @if($showNewToken && $generatedToken)
            <div class="mt-8 bg-green-50 border border-green-200 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <span class="text-green-500 text-xl mr-2">🎉</span>
                    <h3 class="text-lg font-medium text-green-800">Nouvelle Clé API Générée</h3>
                </div>
                
                <div class="bg-white border border-green-300 rounded-lg p-4 mb-4">
                    <label class="block text-sm font-medium text-green-700 mb-2">Votre nouvelle clé API :</label>
                    <div class="flex items-center space-x-2">
                        <code class="flex-1 p-2 bg-green-50 border border-green-200 rounded text-sm font-mono break-all">{{ $generatedToken }}</code>
                        <button 
                            onclick="copyToClipboard('{{ $generatedToken }}')" 
                            class="bg-green-100 hover:bg-green-200 text-green-800 px-3 py-2 rounded text-xs font-medium"
                            title="Copier"
                        >
                            📋
                        </button>
                    </div>
                </div>

                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-red-800">
                        <strong>⚠️ IMPORTANT :</strong> Sauvegardez cette clé maintenant ! Elle ne sera plus affichée par la suite.
                        Vous devrez régénérer une nouvelle clé si vous la perdez.
                    </p>
                </div>

                <button 
                    wire:click="cancelNewToken"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium"
                >
                    ✅ J'ai sauvegardé ma clé
                </button>
            </div>
        @endif

        <!-- Usage Information -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-blue-900 mb-4">💡 Comment utiliser votre clé API</h3>
            
            <div class="space-y-4 text-sm text-blue-800">
                <div>
                    <h4 class="font-medium">1. Authentification :</h4>
                    <p class="ml-4">Incluez votre clé API dans l'en-tête <code class="bg-white px-1 rounded">X-API-Key</code> de vos requêtes.</p>
                </div>
                
                <div>
                    <h4 class="font-medium">2. URL de base :</h4>
                    <p class="ml-4"><code class="bg-white px-1 rounded">{{ url('/api') }}</code></p>
                </div>
                
                <div>
                    <h4 class="font-medium">3. Exemple de requête :</h4>
                    <div class="ml-4 mt-2 p-3 bg-white border rounded text-xs font-mono">
curl -X POST {{ url('/api/sms/bulk') }} \<br>
  -H "Content-Type: application/json" \<br>
  -H "X-API-Key: YOUR_API_KEY" \<br>
  -d '{"content": "Hello World", "recipients": ["77166677"]}'
                    </div>
                </div>
            </div>
        </div>

        <!-- Rate Limit Info -->
        <div class="mt-8 bg-gray-50 border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">📊 Limites de votre compte</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div class="bg-white p-4 rounded border">
                    <h4 class="font-medium text-gray-900">Limite de débit</h4>
                    <p class="text-2xl font-bold text-blue-600">{{ $client->rate_limit ?? 100 }}</p>
                    <p class="text-gray-500">requêtes par minute</p>
                </div>
                
                <div class="bg-white p-4 rounded border">
                    <h4 class="font-medium text-gray-900">Statut du compte</h4>
                    <p class="text-2xl font-bold {{ $client->isActive() ? 'text-green-600' : 'text-red-600' }}">
                        {{ $client->isActive() ? 'Actif' : 'Inactif' }}
                    </p>
                    <p class="text-gray-500">état actuel</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Déclencher un événement Livewire pour afficher le message
        @this.call('copyToClipboard');
    }, function(err) {
        console.error('Erreur lors de la copie: ', err);
    });
}
</script>