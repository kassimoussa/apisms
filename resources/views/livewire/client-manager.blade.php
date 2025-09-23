<div>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Clients</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Gérer les clients API, générer des clés et configurer les contrôles d'accès
                </p>
                <!-- Debug info -->
                <div class="text-xs text-gray-400 mt-1">
                    Débogage : Heure actuelle {{ now()->format('Y-m-d H:i:s') }}
                </div>
            </div>
            <div>
                <a href="{{ route('admin.clients.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Ajouter Client
                </a>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('message') }}
        </div>
    @endif

    <!-- Error Message -->
    @if (session()->has('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- New API Key Alert -->
    @if (session()->has('newApiKey'))
        <div class="mb-6 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
            <div class="flex items-center justify-between">
                <div>
                    <strong>🔑 Nouvelle Clé API Générée :</strong>
                    <code class="bg-blue-200 px-2 py-1 rounded text-sm">{{ session('newApiKey') }}</code>
                </div>
                <button data-copy-text="{{ session('newApiKey') }}" 
                        class="text-blue-600 hover:text-blue-800 text-sm">
                    📋 Copier
                </button>
            </div>
            <p class="text-xs mt-2">⚠️ Sauvegardez cette clé en sécurité - elle ne sera plus affichée !</p>
        </div>
    @endif


    <!-- Clients Table -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">📋 Liste des Clients</h3>
                <p class="mt-1 text-sm text-gray-500">Gérez vos clients API et leurs configurations</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-500">
                    Total : <span class="font-medium">{{ $clients->total() }}</span> clients
                </div>
                <a href="{{ route('admin.clients.create') }}" 
                   class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Nouveau Client
                </a>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            👤 Client
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            📞 Contact
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            🟢 Statut
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            📊 Utilisation
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            🔐 Clé API
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ⚙️ Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($clients as $client)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <!-- Client Info -->
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full {{ $client->client_type === 'individual' ? 'bg-blue-100' : ($client->client_type === 'business' ? 'bg-green-100' : 'bg-purple-100') }} flex items-center justify-center">
                                            <span class="text-sm {{ $client->client_type === 'individual' ? 'text-blue-600' : ($client->client_type === 'business' ? 'text-green-600' : 'text-purple-600') }}">
                                                {{ $client->client_type === 'individual' ? '👤' : ($client->client_type === 'business' ? '🏢' : '🏛️') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ $client->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $client->username ?? 'N/A' }}
                                        </div>
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $client->client_type === 'individual' ? 'bg-blue-100 text-blue-800' : ($client->client_type === 'business' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800') }}">
                                                {{ ucfirst($client->client_type ?? 'individual') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact -->
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    @if($client->email)
                                        <div class="flex items-center text-sm text-gray-900">
                                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="truncate">{{ $client->email }}</span>
                                        </div>
                                    @endif
                                    @if($client->phone)
                                        <div class="flex items-center text-sm text-gray-700">
                                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            <span>{{ $client->phone }}</span>
                                        </div>
                                    @endif
                                    @if($client->company)
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            <span class="truncate">{{ $client->company }}</span>
                                        </div>
                                    @endif
                                    @if(!$client->email && !$client->phone && !$client->company)
                                        <span class="text-gray-400 text-sm italic">Aucun contact</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                <div class="space-y-2">
                                    @if($client->active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <span class="w-1.5 h-1.5 mr-1.5 bg-green-400 rounded-full"></span>
                                            Actif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <span class="w-1.5 h-1.5 mr-1.5 bg-red-400 rounded-full"></span>
                                            Inactif
                                        </span>
                                    @endif
                                    
                                    @if(method_exists($client, 'isOnTrial') && $client->isOnTrial())
                                        <div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                🎯 Période d'essai
                                            </span>
                                        </div>
                                    @endif
                                    
                                    <div class="text-xs text-gray-500">
                                        Créé {{ $client->created_at ? $client->created_at->diffForHumans() : 'N/A' }}
                                    </div>
                                </div>
                            </td>

                            <!-- Usage -->
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <span class="w-2 h-2 bg-blue-400 rounded-full mr-2"></span>
                                        <span>{{ number_format($client->sms_messages_count ?? 0) }} SMS envoyés</span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <span class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></span>
                                        <span>{{ $client->daily_sms_limit ?? 1000 }}/jour</span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <span class="w-2 h-2 bg-purple-400 rounded-full mr-2"></span>
                                        <span>{{ $client->monthly_sms_limit ?? 30000 }}/mois</span>
                                    </div>
                                </div>
                            </td>

                            <!-- API Key -->
                            <td class="px-6 py-4">
                                <div class="space-y-2">
                                    <div class="flex items-center space-x-2">
                                        @if(in_array($client->id, $revealedKeys))
                                            <code class="text-xs font-mono bg-gray-100 px-2 py-1 rounded border flex-1 truncate">
                                                {{ Str::limit($client->getDecryptedApiKey() ?: 'Erreur', 20) }}
                                            </code>
                                        @else
                                            <code class="text-xs font-mono bg-gray-100 px-2 py-1 rounded border flex-1">
                                                {{ $client->getMaskedApiKey() ?: 'Pas de clé' }}
                                            </code>
                                        @endif
                                        
                                        <div class="flex space-x-1">
                                            <button wire:click="toggleKeyVisibility({{ $client->id }})" 
                                                    class="p-1 text-gray-400 hover:text-indigo-600 transition-colors" 
                                                    title="{{ in_array($client->id, $revealedKeys) ? 'Masquer' : 'Afficher' }}">
                                                @if(in_array($client->id, $revealedKeys))
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                @endif
                                            </button>
                                            
                                            <button wire:click="copyApiKey({{ $client->id }})"
                                                    class="p-1 text-gray-400 hover:text-indigo-600 transition-colors" 
                                                    title="Copier">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    @if($client->api_key_expires_at)
                                        <div class="text-xs">
                                            @if(method_exists($client, 'isApiKeyExpired') && $client->isApiKeyExpired())
                                                <span class="text-red-600 font-medium">🔴 Expirée</span>
                                            @elseif(now()->diffInDays($client->api_key_expires_at) <= 30)
                                                <span class="text-yellow-600 font-medium">🟡 Expire bientôt</span>
                                            @else
                                                <span class="text-gray-500">Exp : {{ $client->api_key_expires_at->format('d/m/Y') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- View Profile -->
                                    <a href="{{ route('admin.clients.profile', $client) }}"
                                       class="inline-flex items-center justify-center w-8 h-8 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-md transition-all duration-150" 
                                       title="Voir le profil">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>

                                    <!-- Edit -->
                                    <a href="{{ route('admin.clients.edit', $client->id) }}"
                                       class="inline-flex items-center justify-center w-8 h-8 text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50 rounded-md transition-all duration-150" 
                                       title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>

                                    <!-- Toggle Status -->
                                    <button wire:click="toggleClient({{ $client->id }})"
                                            class="inline-flex items-center justify-center w-8 h-8 {{ $client->active ? 'text-red-600 hover:text-red-900 hover:bg-red-50' : 'text-green-600 hover:text-green-900 hover:bg-green-50' }} rounded-md transition-all duration-150"
                                            title="{{ $client->active ? 'Désactiver' : 'Activer' }}">
                                        @if($client->active)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @endif
                                    </button>

                                    <!-- Delete -->
                                    <button wire:click="deleteClient({{ $client->id }})"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ? Cette action est irréversible.')"
                                            class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-md transition-all duration-150" 
                                            title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>

                                    <!-- Regenerate API Key -->
                                    <button wire:click="regenerateApiKey({{ $client->id }})"
                                            onclick="return confirm('Êtes-vous sûr ? Cela invalidera la clé API actuelle.')"
                                            class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-md transition-all duration-150" 
                                            title="Régénérer la clé API">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <div class="mt-4">
                                    <h3 class="text-lg font-medium text-gray-900">Aucun client</h3>
                                    <p class="mt-1 text-sm text-gray-500">Commencez par créer votre premier client API.</p>
                                    <a href="{{ route('admin.clients.create') }}" 
                                       class="mt-3 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                        Créer un client
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($clients->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $clients->links() }}
            </div>
        @endif
    </div>


    <!-- JavaScript for clipboard functionality -->
    <script>
        // Helper function to show success message
        function showCopySuccess(message = '✅ Clé API copiée dans le presse-papiers !') {
            const successMsg = document.createElement('div');
            successMsg.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded shadow z-50';
            successMsg.innerHTML = message;
            document.body.appendChild(successMsg);
            
            setTimeout(() => {
                if (successMsg.parentNode) {
                    successMsg.parentNode.removeChild(successMsg);
                }
            }, 3000);
        }

        // Helper function to copy text with multiple fallbacks
        function copyToClipboard(text) {
            console.log('Attempting to copy:', text.substring(0, 10) + '...');
            
            // Method 1: Modern Clipboard API (works in secure contexts)
            if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                navigator.clipboard.writeText(text).then(() => {
                    console.log('Clipboard API copy successful');
                    showCopySuccess();
                }).catch((err) => {
                    console.warn('Clipboard API failed, trying fallback:', err);
                    fallbackCopyTextToClipboard(text);
                });
            } else {
                console.log('Clipboard API not available, using fallback');
                fallbackCopyTextToClipboard(text);
            }
        }

        // Fallback method using execCommand
        function fallbackCopyTextToClipboard(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            
            // Avoid scrolling to bottom
            textArea.style.top = '0';
            textArea.style.left = '0';
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    console.log('Fallback copy successful');
                    showCopySuccess();
                } else {
                    console.error('execCommand copy failed');
                    showManualCopyPrompt(text);
                }
            } catch (err) {
                console.error('execCommand error:', err);
                showManualCopyPrompt(text);
            }
            
            document.body.removeChild(textArea);
        }

        // Final fallback: show the key in a prompt for manual copy
        function showManualCopyPrompt(text) {
            const userAgent = navigator.userAgent.toLowerCase();
            if (userAgent.includes('mobile') || userAgent.includes('android') || userAgent.includes('iphone')) {
                // Mobile devices: show in a prompt
                prompt('Copier cette clé API manuellement :', text);
            } else {
                // Desktop: show in alert with instructions
                alert('Veuillez copier cette clé API manuellement :\n\n' + text + '\n\n(Le texte a été sélectionné pour vous)');
            }
        }

        // Initialize Livewire listeners
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('copyToClipboard', (data) => {
                const key = Array.isArray(data) ? data[0] : data;
                copyToClipboard(key);
            });
        });

        // Also handle direct button clicks (for new API key alerts)
        document.addEventListener('DOMContentLoaded', () => {
            document.addEventListener('click', (e) => {
                if (e.target.closest('[data-copy-text]')) {
                    e.preventDefault();
                    const copyText = e.target.closest('[data-copy-text]').getAttribute('data-copy-text');
                    copyToClipboard(copyText);
                }
            });
        });
    </script>
</div>
