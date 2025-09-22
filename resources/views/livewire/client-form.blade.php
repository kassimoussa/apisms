<div class="max-w-4xl mx-auto">
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

    <!-- Form Card -->
    <div class="bg-white shadow rounded-lg">
        <!-- Progress Steps -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center">
                @for($i = 1; $i <= 4; $i++)
                    <div class="flex items-center {{ $i > 1 ? 'ml-8' : '' }}">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $currentStep >= $i ? 'bg-indigo-600 text-white' : 'bg-gray-300 text-gray-600' }}">
                            {{ $i }}
                        </div>
                        <div class="ml-3 text-sm {{ $currentStep >= $i ? 'text-indigo-600 font-medium' : 'text-gray-500' }}">
                            @if($i == 1) Informations de Base
                            @elseif($i == 2) Contact & Entreprise
                            @elseif($i == 3) Facturation & Limites
                            @else Paramètres Avancés
                            @endif
                        </div>
                        @if($i < 4)
                            <div class="w-16 h-0.5 ml-8 {{ $currentStep > $i ? 'bg-indigo-600' : 'bg-gray-300' }}"></div>
                        @endif
                    </div>
                @endfor
            </div>
        </div>

        <form wire:submit.prevent="save" class="p-6">
            <!-- Étape 1: Informations de Base -->
            @if($currentStep == 1)
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom du Client *</label>
                            <input wire:model="name" type="text" 
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="ex: Entreprise ABC">
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom d'utilisateur *</label>
                            <input wire:model="username" type="text"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="nom_utilisateur_unique">
                            @error('username') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if(!$isEditing)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe *</label>
                            <input wire:model="password" type="password"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Minimum 6 caractères">
                            @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
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
                    </div>
                    @else
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
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea wire:model="description" rows="3"
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Brève description de l'objectif du client"></textarea>
                        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            @endif

            <!-- Étape 2: Contact & Entreprise -->
            @if($currentStep == 2)
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Site Web</label>
                            <input wire:model="website" type="url"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="https://exemple.com">
                            @error('website') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
                        <textarea wire:model="address" rows="3"
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Adresse complète"></textarea>
                        @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            @endif

            <!-- Étape 3: Facturation & Limites -->
            @if($currentStep == 3)
                <div class="space-y-6">
                    <!-- Billing Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-900 mb-4">💰 Informations de Facturation</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Solde Initial</label>
                                <input wire:model="balance" type="number" step="0.01" min="0"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('balance') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Limite de Crédit</label>
                                <input wire:model="credit_limit" type="number" step="0.01" min="0"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('credit_limit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Monnaie</label>
                                <select wire:model="currency"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="EUR">EUR</option>
                                    <option value="USD">USD</option>
                                    <option value="XOF">XOF</option>
                                </select>
                                @error('currency') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- SMS Limits -->
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-900 mb-4">📱 Limites SMS</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Limite SMS Quotidienne</label>
                                <input wire:model="daily_sms_limit" type="number" min="1"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('daily_sms_limit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Limite SMS Mensuelle</label>
                                <input wire:model="monthly_sms_limit" type="number" min="1"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('monthly_sms_limit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Limite de Débit (par min)</label>
                                <input wire:model="rate_limit" type="number" min="1" max="1000"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('rate_limit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Auto-recharge -->
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-medium text-gray-900">🔄 Recharge Automatique</h4>
                            <label class="inline-flex items-center">
                                <input wire:model="auto_recharge" type="checkbox" 
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-600">Activer la recharge automatique</span>
                            </label>
                        </div>
                        
                        @if($auto_recharge)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Montant de Recharge</label>
                                    <input wire:model="auto_recharge_amount" type="number" step="0.01" min="0"
                                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('auto_recharge_amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Seuil</label>
                                    <input wire:model="auto_recharge_threshold" type="number" step="0.01" min="0"
                                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('auto_recharge_threshold') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Étape 4: Paramètres Avancés -->
            @if($currentStep == 4)
                <div class="space-y-6">
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <strong>Nom :</strong> {{ $name ?: 'Non spécifié' }}<br>
                                <strong>Type :</strong> {{ ucfirst($client_type) }}<br>
                                <strong>Nom d'utilisateur :</strong> {{ $username ?: 'Non spécifié' }}
                            </div>
                            <div>
                                <strong>Limite SMS Quotidienne :</strong> {{ number_format($daily_sms_limit) }}<br>
                                <strong>Limite SMS Mensuelle :</strong> {{ number_format($monthly_sms_limit) }}<br>
                                <strong>Limite de Débit :</strong> {{ $rate_limit }}/min
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-8 pt-6 border-t border-gray-200">
                <div class="flex space-x-3">
                    @if($currentStep > 1)
                        <button type="button" wire:click="prevStep"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            ← Précédent
                        </button>
                    @endif
                    
                    <button type="button" wire:click="cancel"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Annuler
                    </button>
                </div>
                
                <div>
                    @if($currentStep < 4)
                        <button type="button" wire:click="nextStep"
                                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                            Suivant →
                        </button>
                    @else
                        <button type="submit"
                                class="px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700">
                            {{ $isEditing ? '💾 Mettre à Jour' : '🚀 Créer Client' }}
                        </button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>