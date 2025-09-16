<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div>
            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-indigo-100">
                <span class="text-2xl">👨‍💼</span>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Admin Login
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                SMS Gateway Administration Panel
            </p>
        </div>

        <!-- Login Form -->
        <form class="mt-8 space-y-6" wire:submit.prevent="login">
            <div class="rounded-md shadow-sm space-y-4">
                <!-- Username/Email -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">
                        Username or Email
                    </label>
                    <input 
                        id="username"
                        name="username"
                        type="text"
                        wire:model="username"
                        required
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                        placeholder="Enter username or email"
                    >
                    @error('username') 
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Password
                    </label>
                    <input 
                        id="password"
                        name="password"
                        type="password"
                        wire:model="password"
                        required
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                        placeholder="Enter password"
                    >
                    @error('password') 
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                    @enderror
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input 
                        id="remember"
                        name="remember"
                        type="checkbox"
                        wire:model="remember"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                    >
                    <label for="remember" class="ml-2 block text-sm text-gray-900">
                        Remember me
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button 
                    type="submit"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                >
                    <span wire:loading.remove>
                        🔐 Sign In
                    </span>
                    <span wire:loading>
                        Signing in...
                    </span>
                </button>
            </div>
        </form>

        <!-- Divider -->
        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300" />
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-gray-50 text-gray-500">Or</span>
                </div>
            </div>
        </div>

        <!-- Client Login Link -->
        <div class="text-center">
            <a href="{{ route('client.login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                👤 Client Login
            </a>
        </div>

        <!-- Development Info -->
        @if(app()->environment('local'))
            <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h4 class="text-sm font-medium text-yellow-800">🧪 Development Credentials:</h4>
                <div class="mt-2 text-xs text-yellow-700">
                    <p><strong>Super Admin:</strong> admin / admin123</p>
                    <p><strong>Admin:</strong> manager / manager123</p>
                </div>
            </div>
        @endif
    </div>
</div>