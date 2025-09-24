<div>
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">👤 Mon Profil</h1>
        <p class="mt-2 text-gray-600">Gérez vos informations personnelles et paramètres de compte</p>
    </div>

    <!-- Success Messages -->
    @if (session()->has('profile_success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            {{ session('profile_success') }}
        </div>
    @endif

    @if (session()->has('password_success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            {{ session('password_success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Account Overview -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">📊 Aperçu du Compte</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                            <span class="text-indigo-600 font-semibold">{{ substr($client->name, 0, 1) }}</span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ $client->name }}</p>
                            <p class="text-xs text-gray-500">{{ $client->username }}</p>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-4">
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Type de compte:</span>
                                <span class="font-medium text-gray-900 capitalize">{{ $client->client_type }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Statut:</span>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $client->isActive() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $client->isActive() ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Membre depuis:</span>
                                <span class="font-medium text-gray-900">{{ $client->created_at->format('M Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- API Info -->
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">🔑 Informations API</h4>
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-500">Limite quotidienne:</span>
                                <span class="font-medium">{{ number_format($client->daily_sms_limit) }} SMS</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-500">Limite mensuelle:</span>
                                <span class="font-medium">{{ number_format($client->monthly_sms_limit) }} SMS</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-500">ID Expéditeur:</span>
                                <span class="font-medium {{ $client->sender_id ? 'font-mono text-gray-900' : 'text-gray-400 italic' }}">
                                    {{ $client->sender_id ?: 'Non configuré' }}
                                </span>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Personal Information -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">📝 Informations Personnelles</h3>
                    <p class="mt-1 text-sm text-gray-500">Mettez à jour vos informations de profil</p>
                </div>
                <form wire:submit.prevent="updateProfile" class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
                            <input wire:model="name" type="text" id="name" 
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Votre nom complet">
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input wire:model="email" type="email" id="email"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="votre@email.com">
                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                            <input wire:model="phone" type="text" id="phone"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="+253 77 XX XX XX">
                            @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="company" class="block text-sm font-medium text-gray-700 mb-1">Entreprise</label>
                            <input wire:model="company" type="text" id="company"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Nom de votre entreprise">
                            @error('company') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="industry" class="block text-sm font-medium text-gray-700 mb-1">Secteur d'activité</label>
                            <input wire:model="industry" type="text" id="industry"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="ex: E-commerce, Finance">
                            @error('industry') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="website" class="block text-sm font-medium text-gray-700 mb-1">Site web</label>
                            <input wire:model="website" type="text" id="website"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="votresite.com ou www.votresite.com">
                            @error('website') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                            <textarea wire:model="address" id="address" rows="3"
                                      class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                      placeholder="Adresse complète"></textarea>
                            @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200 disabled:opacity-50">
                            <span wire:loading.remove wire:target="updateProfile">💾 Sauvegarder</span>
                            <span wire:loading wire:target="updateProfile">Sauvegarde...</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Change -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">🔐 Sécurité</h3>
                            <p class="mt-1 text-sm text-gray-500">Modifiez votre mot de passe</p>
                        </div>
                        <button wire:click="togglePasswordForm" type="button"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-md transition-colors duration-200">
                            {{ $showPasswordForm ? 'Annuler' : 'Changer le mot de passe' }}
                        </button>
                    </div>
                </div>
                
                @if($showPasswordForm)
                <form wire:submit.prevent="updatePassword" class="px-6 py-4">
                    <div class="space-y-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe actuel *</label>
                            <input wire:model="current_password" type="password" id="current_password"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('current_password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe *</label>
                                <input wire:model="new_password" type="password" id="new_password"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('new_password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe *</label>
                                <input wire:model="new_password_confirmation" type="password" id="new_password_confirmation"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button wire:click="togglePasswordForm" type="button"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-md transition-colors duration-200">
                            Annuler
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200 disabled:opacity-50">
                            <span wire:loading.remove wire:target="updatePassword">🔐 Changer le mot de passe</span>
                            <span wire:loading wire:target="updatePassword">Changement...</span>
                        </button>
                    </div>
                </form>
                @endif
            </div>

        </div>
    </div>
</div>