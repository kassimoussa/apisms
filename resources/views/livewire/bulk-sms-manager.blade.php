<div class="py-6">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-3 mb-2">
                    <a href="{{ route('client.campaigns') }}" class="text-blue-600 hover:text-blue-500">
                        ← Retour aux campagnes
                    </a>
                </div>
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    📤 Créer une Campagne SMS
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Envoyez des SMS à plusieurs destinataires avec des options avancées de programmation et de contrôle
                </p>
            </div>
        </div>

        <!-- Success Message -->
        @if($successMessage)
            <div class="mt-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ $successMessage }}
            </div>
        @endif

        <!-- General Error -->
        @error('general')
            <div class="mt-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ $message }}
            </div>
        @enderror

        <!-- Campaign Form -->
        <div class="mt-8 bg-white shadow sm:rounded-lg">
            <form wire:submit.prevent="createCampaign">
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Campaign Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Nom de la Campagne *
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                wire:model="name"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="ex: Campagne Soldes d'Été"
                            >
                            @error('name') 
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                            @enderror
                        </div>

                        <!-- Message Content -->
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700">
                                Contenu du Message *
                            </label>
                            <textarea 
                                id="content" 
                                rows="4" 
                                wire:model="content"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="🚀 Votre message ici... Vous pouvez utiliser des emojis et des accents ! 😊"
                                maxlength="1600"
                            ></textarea>
                            <div class="mt-1 flex justify-between">
                                <span class="text-xs text-gray-500">
                                    Support UTF-8 (emojis et accents). Max 1600 caractères.
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ strlen($content) }}/1600
                                </span>
                            </div>
                            @error('content') 
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                            @enderror
                        </div>

                        <!-- Sender ID -->
                        <div>
                            <label for="from" class="block text-sm font-medium text-gray-700">
                                ID Expéditeur (Optionnel)
                            </label>
                            <input 
                                type="text" 
                                id="from" 
                                wire:model="from"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="ex: ENTREPRISE, PROMO (max 20 caractères)"
                                maxlength="20"
                            >
                            <p class="mt-1 text-xs text-gray-500">
                                Laissez vide pour utiliser l'expéditeur par défaut. Alphanumérique uniquement.
                            </p>
                            @error('from') 
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                            @enderror
                        </div>

                        <!-- Recipients -->
                        <div>
                            <label for="recipients" class="block text-sm font-medium text-gray-700">
                                Destinataires *
                            </label>
                            <textarea 
                                id="recipients" 
                                rows="6" 
                                wire:model="recipients"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm font-mono"
                                placeholder="Entrez les numéros de téléphone (un par ligne ou séparés par des virgules) :&#10;77166677&#10;77123456&#10;77987654&#10;&#10;Ou séparés par des virgules : 77166677, 77123456, 77987654"
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                📋 Un numéro par ligne ou séparés par des virgules. Max 10 000 destinataires. Format : 77XXXXXX ou +25377XXXXXX
                            </p>
                            @error('recipients') 
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                            @enderror
                        </div>

                        <!-- Scheduled Send -->
                        <div>
                            <label for="scheduled_at" class="block text-sm font-medium text-gray-700">
                                Programmer pour Plus Tard (Optionnel)
                            </label>
                            <input 
                                type="datetime-local" 
                                id="scheduled_at" 
                                wire:model="scheduled_at"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                min="{{ now()->format('Y-m-d\TH:i') }}"
                            >
                            <p class="mt-1 text-xs text-gray-500">
                                Laissez vide pour envoyer immédiatement. Dates futures uniquement.
                            </p>
                            @error('scheduled_at') 
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                            @enderror
                        </div>

                        <!-- Advanced Settings Toggle -->
                        <div>
                            <button 
                                type="button"
                                wire:click="toggleAdvancedSettings"
                                class="inline-flex items-center text-sm text-blue-600 hover:text-blue-500"
                            >
                                <span class="mr-1">⚙️</span>
                                {{ $showAdvancedSettings ? 'Masquer' : 'Afficher' }} les Paramètres Avancés
                                <span class="ml-1">{{ $showAdvancedSettings ? '▲' : '▼' }}</span>
                            </button>
                        </div>

                        <!-- Advanced Settings -->
                        @if($showAdvancedSettings)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-900 mb-4">Paramètres de Performance</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Rate Limit -->
                                    <div>
                                        <label for="rate_limit" class="block text-sm font-medium text-gray-700">
                                            Limite de Débit (SMS/minute)
                                        </label>
                                        <input 
                                            type="number" 
                                            id="rate_limit" 
                                            wire:model="rate_limit"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            min="1" 
                                            max="1000"
                                        >
                                        <p class="mt-1 text-xs text-gray-500">
                                            Vitesse d'envoi. Plus élevé = plus rapide mais peut surcharger le réseau.
                                        </p>
                                        @error('rate_limit') 
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                        @enderror
                                    </div>

                                    <!-- Batch Size -->
                                    <div>
                                        <label for="batch_size" class="block text-sm font-medium text-gray-700">
                                            Taille du Lot
                                        </label>
                                        <input 
                                            type="number" 
                                            id="batch_size" 
                                            wire:model="batch_size"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            min="10" 
                                            max="500"
                                        >
                                        <p class="mt-1 text-xs text-gray-500">
                                            Nombre de SMS traités ensemble. Affecte l'utilisation de la mémoire.
                                        </p>
                                        @error('batch_size') 
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 sm:rounded-bl-lg sm:rounded-br-lg">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            💡 Conseil : Testez d'abord avec un petit groupe, puis élargissez !
                        </div>
                        <div class="flex space-x-3">
                            <a 
                                href="{{ route('client.dashboard') }}" 
                                class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Annuler
                            </a>
                            <button 
                                type="submit" 
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                            >
                                <span wire:loading.remove>
                                    🚀 {{ $scheduled_at ? 'Programmer la Campagne' : 'Lancer la Campagne' }}
                                </span>
                                <span wire:loading>
                                    Création de la campagne...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Info Cards -->
        <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="bg-blue-50 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">📊</span>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-blue-700 truncate">Max Destinataires</dt>
                                <dd class="text-lg font-medium text-blue-900">10,000</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">⚡</span>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-green-700 truncate">Vitesse Max</dt>
                                <dd class="text-lg font-medium text-green-900">1,000/min</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">📝</span>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-purple-700 truncate">Longueur Max</dt>
                                <dd class="text-lg font-medium text-purple-900">1 600 caract.</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>