<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div>
            <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-gradient-to-r from-blue-500 to-indigo-600">
                <span class="text-3xl">📱</span>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                SMS Gateway Login
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Access your dashboard
            </p>
        </div>

        <!-- Login Type Tabs -->
        <div class="flex justify-center">
            <div class="flex space-x-1 rounded-lg bg-gray-100 p-1">
                <button 
                    wire:click="setLoginType('auto')"
                    class="flex-1 px-3 py-2 text-sm font-medium rounded-md transition-colors {{ $loginType === 'auto' ? 'bg-white text-gray-900 shadow' : 'text-gray-500 hover:text-gray-700' }}"
                >
                    🔍 Auto
                </button>
                <button 
                    wire:click="setLoginType('client')"
                    class="flex-1 px-3 py-2 text-sm font-medium rounded-md transition-colors {{ $loginType === 'client' ? 'bg-white text-gray-900 shadow' : 'text-gray-500 hover:text-gray-700' }}"
                >
                    👤 Client
                </button>
                <button 
                    wire:click="setLoginType('admin')"
                    class="flex-1 px-3 py-2 text-sm font-medium rounded-md transition-colors {{ $loginType === 'admin' ? 'bg-white text-gray-900 shadow' : 'text-gray-500 hover:text-gray-700' }}"
                >
                    👨‍💼 Admin
                </button>
            </div>
        </div>

        <!-- Login Type Description -->
        <div class="text-center">
            @if($loginType === 'auto')
                <p class="text-sm text-gray-600">
                    🔍 <strong>Auto-detection</strong>: We'll automatically determine if you're a client or admin
                </p>
            @elseif($loginType === 'client')
                <p class="text-sm text-blue-600">
                    👤 <strong>Client Login</strong>: Access your SMS campaigns and statistics
                </p>
            @else
                <p class="text-sm text-indigo-600">
                    👨‍💼 <strong>Admin Login</strong>: Access the administration panel
                </p>
            @endif
        </div>

        <!-- Login Form -->
        <form class="mt-8 space-y-6" wire:submit.prevent="login">
            <div class="rounded-md shadow-sm space-y-4">
                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">
                        @if($loginType === 'admin')
                            Username or Email
                        @else
                            Username
                        @endif
                    </label>
                    <input 
                        id="username"
                        name="username"
                        type="text"
                        wire:model="username"
                        required
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                        placeholder="{{ $loginType === 'admin' ? 'Enter username or email' : 'Enter username' }}"
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
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
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
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
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
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                >
                    <span wire:loading.remove>
                        @if($loginType === 'admin')
                            🔐 Sign in as Admin
                        @elseif($loginType === 'client')
                            🔐 Sign in as Client
                        @else
                            🔐 Sign in
                        @endif
                    </span>
                    <span wire:loading>
                        Signing in...
                    </span>
                </button>
            </div>
        </form>

        <!-- Development Info -->
        @if(app()->environment('local'))
            <div class="mt-8 space-y-4">
                @if($loginType === 'auto' || $loginType === 'admin')
                    <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                        <h4 class="text-sm font-medium text-indigo-800">👨‍💼 Admin Credentials:</h4>
                        <div class="mt-2 text-xs text-indigo-700">
                            <p><strong>Super Admin:</strong> admin / admin123</p>
                            <p><strong>Admin:</strong> manager / manager123</p>
                        </div>
                    </div>
                @endif
                
                @if($loginType === 'auto' || $loginType === 'client')
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 class="text-sm font-medium text-blue-800">👤 Client Credentials:</h4>
                        <div class="mt-2 text-xs text-blue-700">
                            <p><strong>Test Client:</strong> testclient / password123</p>
                            <p><em>Create clients via admin panel</em></p>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>