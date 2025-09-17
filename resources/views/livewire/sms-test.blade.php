<div>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">📤 Test SMS</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Envoyez des SMS de test directement via la passerelle Kannel
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <button wire:click="checkKannelStatus" 
                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    🔄 Check Kannel
                </button>
                
                <!-- Kannel Status -->
                @if(isset($kannelStatus['success']) && $kannelStatus['success'])
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        🟢 Kannel Online
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        🔴 Kannel Offline
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Test Form -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-6">Send Test SMS</h3>
        
        <form wire:submit.prevent="sendTestSms" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Client Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Client</label>
                    <select wire:model="client_id" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select a client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Recipient Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Recipient Number</label>
                    <input wire:model="to" type="text" 
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="+25377123456 or 77123456">
                    @error('to') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <p class="text-xs text-gray-500 mt-1">Formats: +253XXXXXXXX, 253XXXXXXXX, or 77XXXXXX</p>
                </div>
            </div>

            <!-- Sender Number (Optional) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sender Number/Text (Optional)</label>
                <input wire:model="from" type="text" 
                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       placeholder="DPCR, 77123456, or +25377123456">
                @error('from') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                <p class="text-xs text-gray-500 mt-1">Text (max 11 chars) or phone number</p>
            </div>

            <!-- Message Content -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Message Content</label>
                <textarea wire:model="message" rows="4" maxlength="160"
                          class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                          placeholder="Enter your SMS message here..."></textarea>
                @error('message') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>Maximum 160 characters</span>
                    <span>{{ strlen($message) }}/160</span>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" 
                        wire:loading.attr="disabled"
                        @disabled($isLoading)
                        class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 disabled:opacity-50">
                    
                    <svg wire:loading wire:target="sendTestSms" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    
                    <span wire:loading.remove wire:target="sendTestSms">📤 Send SMS</span>
                    <span wire:loading wire:target="sendTestSms">Sending...</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Result Display -->
    @if($result)
        <div class="mb-8">
            @if($result['success'])
                <!-- Success Result -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-green-800">✅ SMS Sent Successfully!</h3>
                            <p class="text-green-700">{{ $result['message'] }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-green-800">SMS ID:</span>
                            <span class="text-green-700">#{{ $result['sms_id'] }}</span>
                        </div>
                        @if(isset($result['kannel_id']))
                        <div>
                            <span class="font-medium text-green-800">Kannel ID:</span>
                            <span class="text-green-700">{{ $result['kannel_id'] }}</span>
                        </div>
                        @endif
                        <div>
                            <span class="font-medium text-green-800">Status:</span>
                            <span class="text-green-700 capitalize">{{ $result['status'] }}</span>
                        </div>
                    </div>
                </div>
            @else
                <!-- Error Result -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-red-800">❌ SMS Sending Failed</h3>
                            <p class="text-red-700">{{ $result['message'] }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        @if(isset($result['error_code']))
                            <div>
                                <span class="font-medium text-red-800">Error Code:</span>
                                <span class="text-red-700">{{ $result['error_code'] }}</span>
                            </div>
                        @endif
                        @if(isset($result['error_message']))
                            <div>
                                <span class="font-medium text-red-800">Error Message:</span>
                                <span class="text-red-700">{{ $result['error_message'] }}</span>
                            </div>
                        @endif
                        @if(isset($result['sms_id']))
                            <div>
                                <span class="font-medium text-red-800">SMS ID:</span>
                                <span class="text-red-700">#{{ $result['sms_id'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            
            <!-- Clear Result Button -->
            <div class="mt-4 flex justify-center">
                <button wire:click="clearResult" 
                        class="text-sm text-gray-600 hover:text-gray-900 underline">
                    Clear Result
                </button>
            </div>
        </div>
    @endif

    <!-- Kannel Status Info -->
    @if(!empty($kannelStatus))
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-900 mb-2">🔗 Kannel Gateway Status</h4>
            <div class="text-sm text-gray-600 space-y-1">
                <div><strong>URL:</strong> {{ config('services.kannel.url') }}</div>
                <div><strong>Username:</strong> {{ config('services.kannel.username') }}</div>
                <div><strong>Default Sender:</strong> {{ config('services.kannel.from') }}</div>
                @if(isset($kannelStatus['message']))
                    <div><strong>Status:</strong> {{ $kannelStatus['message'] }}</div>
                @endif
            </div>
        </div>
    @endif

    <!-- Usage Examples -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h4 class="text-sm font-medium text-blue-900 mb-3">💡 Usage Tips</h4>
        <div class="text-sm text-blue-800 space-y-2">
            <div>• <strong>Phone Format:</strong> +253XXXXXXXX, 253XXXXXXXX, or 77XXXXXX</div>
            <div>• <strong>Message Length:</strong> Keep messages under 160 characters for single SMS</div>
            <div>• <strong>Rate Limits:</strong> Each client has a configurable rate limit per minute</div>
            <div>• <strong>Testing:</strong> Use this interface to test before API integration</div>
        </div>
    </div>
</div>
