<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="mx-auto h-12 w-12 flex items-center justify-center bg-blue-100 rounded-full">
                <span class="text-2xl">📱</span>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Portail Client SMS Gateway
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Connectez-vous à votre compte pour gérer vos campagnes SMS
            </p>
        </div>
        
        <form class="mt-8 space-y-6" wire:submit.prevent="login">
            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="username" class="sr-only">Nom d'utilisateur</label>
                    <input 
                        id="username" 
                        name="username" 
                        type="text" 
                        required 
                        wire:model="username"
                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                        placeholder="Nom d'utilisateur"
                    >
                    @error('username') 
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                    @enderror
                </div>
                <div>
                    <label for="password" class="sr-only">Mot de passe</label>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        required 
                        wire:model="password"
                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                        placeholder="Mot de passe"
                    >
                    @error('password') 
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input 
                        id="remember" 
                        name="remember" 
                        type="checkbox" 
                        wire:model="remember"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="remember" class="ml-2 block text-sm text-gray-900">
                        Se souvenir de moi
                    </label>
                </div>
            </div>

            <div>
                <button 
                    type="submit" 
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                >
                    <span wire:loading.remove>
                        Se connecter
                    </span>
                    <span wire:loading>
                        Connexion en cours...
                    </span>
                </button>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Besoin d'accès ? Contactez votre administrateur système
                </p>
                <div class="mt-4">
                    <a href="{{ route('admin.login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        👨‍💼 Connexion Admin
                    </a>
                </div>
            </div>
        </form>

        <!-- Demo Credentials for Testing -->
        <div class="mt-8 p-4 bg-blue-50 rounded-md">
            <h3 class="text-sm font-medium text-blue-800 mb-2">Identifiants de Démo :</h3>
            <div class="text-xs text-blue-700 space-y-1">
                <p><strong>Nom d'utilisateur :</strong> demo_client</p>
                <p><strong>Mot de passe :</strong> password123</p>
            </div>
        </div>
    </div>
</div>