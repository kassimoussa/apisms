<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    📤 Create Bulk SMS Campaign
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Send SMS to multiple recipients at once with advanced scheduling and control options
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
                                Campaign Name *
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                wire:model="name"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="e.g., Summer Sale Campaign"
                            >
                            @error('name') 
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                            @enderror
                        </div>

                        <!-- Message Content -->
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700">
                                Message Content *
                            </label>
                            <textarea 
                                id="content" 
                                rows="4" 
                                wire:model="content"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="🚀 Your message here... You can use emojis and accents! 😊"
                                maxlength="1600"
                            ></textarea>
                            <div class="mt-1 flex justify-between">
                                <span class="text-xs text-gray-500">
                                    Supports UTF-8 (emojis & accents). Max 1600 characters.
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
                                Sender ID (Optional)
                            </label>
                            <input 
                                type="text" 
                                id="from" 
                                wire:model="from"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="e.g., COMPANY, PROMO (max 20 chars)"
                                maxlength="20"
                            >
                            <p class="mt-1 text-xs text-gray-500">
                                Leave empty to use default sender. Alphanumeric only.
                            </p>
                            @error('from') 
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                            @enderror
                        </div>

                        <!-- Recipients -->
                        <div>
                            <label for="recipients" class="block text-sm font-medium text-gray-700">
                                Recipients *
                            </label>
                            <textarea 
                                id="recipients" 
                                rows="6" 
                                wire:model="recipients"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm font-mono"
                                placeholder="Enter phone numbers (one per line or comma-separated):&#10;77166677&#10;77123456&#10;77987654&#10;&#10;Or comma-separated: 77166677, 77123456, 77987654"
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                📋 One number per line or comma-separated. Max 10,000 recipients. Format: 77XXXXXX or +25377XXXXXX
                            </p>
                            @error('recipients') 
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                            @enderror
                        </div>

                        <!-- Scheduled Send -->
                        <div>
                            <label for="scheduled_at" class="block text-sm font-medium text-gray-700">
                                Schedule for Later (Optional)
                            </label>
                            <input 
                                type="datetime-local" 
                                id="scheduled_at" 
                                wire:model="scheduled_at"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                min="{{ now()->format('Y-m-d\TH:i') }}"
                            >
                            <p class="mt-1 text-xs text-gray-500">
                                Leave empty to send immediately. Future dates only.
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
                                {{ $showAdvancedSettings ? 'Hide' : 'Show' }} Advanced Settings
                                <span class="ml-1">{{ $showAdvancedSettings ? '▲' : '▼' }}</span>
                            </button>
                        </div>

                        <!-- Advanced Settings -->
                        @if($showAdvancedSettings)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-900 mb-4">Performance Settings</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Rate Limit -->
                                    <div>
                                        <label for="rate_limit" class="block text-sm font-medium text-gray-700">
                                            Rate Limit (SMS/minute)
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
                                            Sending speed. Higher = faster but may overwhelm network.
                                        </p>
                                        @error('rate_limit') 
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                        @enderror
                                    </div>

                                    <!-- Batch Size -->
                                    <div>
                                        <label for="batch_size" class="block text-sm font-medium text-gray-700">
                                            Batch Size
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
                                            Number of SMS processed together. Affects memory usage.
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
                            💡 Tip: Test with a small group first, then scale up!
                        </div>
                        <div class="flex space-x-3">
                            <a 
                                href="{{ route('client.dashboard') }}" 
                                class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Cancel
                            </a>
                            <button 
                                type="submit" 
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                            >
                                <span wire:loading.remove>
                                    🚀 {{ $scheduled_at ? 'Schedule Campaign' : 'Launch Campaign' }}
                                </span>
                                <span wire:loading>
                                    Creating Campaign...
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
                                <dt class="text-sm font-medium text-blue-700 truncate">Max Recipients</dt>
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
                                <dt class="text-sm font-medium text-green-700 truncate">Max Speed</dt>
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
                                <dt class="text-sm font-medium text-purple-700 truncate">Max Length</dt>
                                <dd class="text-lg font-medium text-purple-900">1,600 chars</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>