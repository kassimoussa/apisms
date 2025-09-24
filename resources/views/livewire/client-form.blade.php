<div>
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $isEditing ? 'Modifier le Client' : 'Créer un Client' }}
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $isEditing ? 'Modifiez les informations du client API' : 'Créez un nouveau client API avec ses paramètres' }}
                </p>
            </div>
            <div class="flex space-x-3">
                <button wire:click="cancel" type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Annuler
                </button>
                <button wire:click="save" type="button"
                        class="px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700">
                    {{ $isEditing ? '💾 Mettre à Jour' : '🚀 Créer Client' }}
                </button>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('message'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="save">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Card 1: Informations de Base -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">📋 Informations de Base</h3>
                    <p class="mt-1 text-sm text-gray-500">Informations principales du client</p>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom du Client *</label>
                        <input wire:model="name" type="text" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="ex: Entreprise ABC">
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Expéditeur</label>
                        <input wire:model="sender_id" type="text" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="ex: COMPANY (max 11 caractères alphanumériques)"
                               maxlength="11">
                        @error('sender_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        <p class="mt-1 text-xs text-gray-500">Identifiant expéditeur pour tous les SMS de ce client. Si vide, utilise la configuration par défaut.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom d'utilisateur *</label>
                        <input wire:model="username" type="text"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="nom_utilisateur_unique">
                        @error('username') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    @if(!$isEditing)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe *</label>
                        <input wire:model="password" type="password"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Minimum 6 caractères">
                        @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type de Client</label>
                        <select wire:model="client_type"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="individual">Particulier</option>
                            <option value="business">Entreprise</option>
                            <option value="enterprise">Grande Entreprise</option>
                        </select>
                        @error('client_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea wire:model="description" rows="3"
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Brève description de l'objectif du client"></textarea>
                        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Card 2: Contact & Entreprise -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">📞 Contact & Entreprise</h3>
                    <p class="mt-1 text-sm text-gray-500">Informations de contact et d'entreprise</p>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input wire:model="email" type="email"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="client@exemple.com">
                        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                        <input wire:model="phone" type="text"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="+25377123456">
                        @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Entreprise</label>
                        <input wire:model="company" type="text"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Nom de l'Entreprise">
                        @error('company') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Secteur</label>
                        <input wire:model="industry" type="text"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="ex: Technologie, Santé">
                        @error('industry') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Site Web</label>
                        <input wire:model="website" type="text"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="exemple.com ou www.exemple.com">
                        @error('website') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
                        <textarea wire:model="address" rows="2"
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Adresse complète"></textarea>
                        @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Card 3: Limites -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">📊 Limites</h3>
                    <p class="mt-1 text-sm text-gray-500">Configuration des limites d'usage SMS</p>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <!-- SMS Limits -->
                    <div class="bg-blue-50 p-4 rounded-lg space-y-3">
                        <h4 class="text-sm font-medium text-gray-900">📱 Limites SMS</h4>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Limite Quotidienne</label>
                                <input wire:model="daily_sms_limit" type="number" min="1"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('daily_sms_limit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Limite Mensuelle</label>
                                <input wire:model="monthly_sms_limit" type="number" min="1"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('monthly_sms_limit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Débit (par min)</label>
                                <input wire:model="rate_limit" type="number" min="1" max="1000"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('rate_limit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Paramètres Avancés -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">⚙️ Paramètres Avancés</h3>
                    <p class="mt-1 text-sm text-gray-500">Configuration technique et sécurité</p>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">🎯 Date de Fin d'Essai (optionnel)</label>
                        <input wire:model="trial_ends_at" type="date"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('trial_ends_at') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">🔒 IPs Autorisées (optionnel)</label>
                        <input wire:model="allowed_ips" type="text"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="192.168.1.1, 10.0.0.1 (séparées par virgules)">
                        @error('allowed_ips') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-500 mt-1">Laisser vide pour autoriser toutes les IPs</p>
                    </div>

                    <!-- Summary -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">📋 Résumé</h4>
                        <div class="space-y-2 text-sm">
                            <div><strong>Nom :</strong> {{ $name ?: 'Non spécifié' }}</div>
                            <div><strong>Type :</strong> {{ ucfirst($client_type) }}</div>
                            <div><strong>Nom d'utilisateur :</strong> {{ $username ?: 'Non spécifié' }}</div>
                            <div><strong>Limite SMS Quotidienne :</strong> {{ number_format($daily_sms_limit) }}</div>
                            <div><strong>Limite SMS Mensuelle :</strong> {{ number_format($monthly_sms_limit) }}</div>
                            <div><strong>Limite de Débit :</strong> {{ $rate_limit }}/min</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Action Bar -->
        <div class="mt-8 flex justify-between items-center bg-white border border-gray-200 rounded-lg px-6 py-4">
            <div class="text-sm text-gray-500">
                Assurez-vous que toutes les informations sont correctes avant de {{ $isEditing ? 'modifier' : 'créer' }} le client.
            </div>
            <div class="flex space-x-3">
                <button wire:click="cancel" type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Annuler
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700">
                    {{ $isEditing ? '💾 Mettre à Jour' : '🚀 Créer Client' }}
                </button>
            </div>
        </div>
    </form>
</div>