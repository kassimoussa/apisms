<div x-data="{ autoRefresh: @entangle('autoRefresh') }" class="space-y-6">
    <!-- Header with Controls -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">📊 Tableau de Bord</h1>
            <p class="mt-1 text-sm text-gray-600">Surveillance et analyses en temps réel de la passerelle SMS</p>
        </div>
        
        <div class="flex items-center space-x-3">
            <!-- Auto-refresh toggle -->
            <div class="flex items-center space-x-2">
                <label class="text-sm text-gray-700">Actualisation auto</label>
                <button 
                    wire:click="toggleAutoRefresh"
                    :class="autoRefresh ? 'bg-green-600' : 'bg-gray-300'"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <span 
                        :class="autoRefresh ? 'translate-x-6' : 'translate-x-1'"
                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                </button>
            </div>

            <!-- Manual refresh -->
            <button wire:click="refreshData" 
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                <svg wire:loading wire:target="refreshData" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                🔄 Actualiser
            </button>

            <!-- Export data -->
            <button wire:click="exportData"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                📥 Exporter
            </button>

            <!-- Kannel Status Indicator -->
            @if(isset($systemHealth['kannel']['success']) && $systemHealth['kannel']['success'])
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    🟢 Kannel En ligne
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    🔴 Kannel Hors ligne
                </span>
            @endif
        </div>
    </div>

    <!-- Real-time Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total SMS Today -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="text-2xl">📱</div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">SMS Aujourd'hui</dt>
                            <dd class="text-3xl font-bold text-gray-900">{{ number_format($realTimeStats['today_sms'] ?? 0) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-gray-700">Taux de Réussite : </span>
                    <span class="font-medium text-green-600">{{ $realTimeStats['success_rate'] ?? 0 }}%</span>
                </div>
            </div>
        </div>

        <!-- Delivered Today -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="text-2xl">✅</div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Livrés Aujourd'hui</dt>
                            <dd class="text-3xl font-bold text-green-600">{{ number_format($realTimeStats['delivered_today'] ?? 0) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-gray-700">Temps Moyen : </span>
                    <span class="font-medium text-blue-600">{{ $realTimeStats['avg_delivery_time'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Failed Today -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="text-2xl">❌</div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Échecs Aujourd'hui</dt>
                            <dd class="text-3xl font-bold text-red-600">{{ number_format($realTimeStats['failed_today'] ?? 0) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-gray-700">En Attente : </span>
                    <span class="font-medium text-yellow-600">{{ number_format($realTimeStats['pending_sms'] ?? 0) }}</span>
                </div>
            </div>
        </div>

        <!-- Active Clients -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="text-2xl">👥</div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Clients Actifs</dt>
                            <dd class="text-3xl font-bold text-indigo-600">{{ number_format($realTimeStats['active_clients'] ?? 0) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-gray-700">Tâches en File : </span>
                    <span class="font-medium text-purple-600">{{ number_format($realTimeStats['queue_jobs']['total'] ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-medium text-gray-900">📈 Activité SMS (7 Derniers Jours)</h3>
            <div class="flex items-center space-x-4 text-sm">
                <div class="flex items-center space-x-1">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span>Livrés</span>
                </div>
                <div class="flex items-center space-x-1">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <span>Envoyés</span>
                </div>
                <div class="flex items-center space-x-1">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <span>Échecs</span>
                </div>
            </div>
        </div>
        
        <div class="h-80 relative">
            <!-- CSS Chart (Default) -->
            <div id="cssChart" class="w-full h-full p-4">
                @php
                    $maxValue = max(
                        max(array_column($chartData, 'delivered')),
                        max(array_column($chartData, 'sent')),
                        max(array_column($chartData, 'failed'))
                    );
                    $maxValue = max($maxValue, 1); // Avoid division by zero
                @endphp
                <div class="h-full flex items-end justify-between space-x-2">
                    @forelse($chartData as $index => $day)
                        <div class="flex-1 flex flex-col items-center space-y-1">
                            <div class="w-full flex flex-col-reverse justify-end space-y-1" style="height: 200px;">
                                <!-- Failed bar (bottom) -->
                                @if(($day['failed'] ?? 0) > 0)
                                    <div class="w-full bg-red-500" 
                                         style="height: {{ round(($day['failed'] / $maxValue) * 150) }}px;"
                                         title="Failed: {{ $day['failed'] }}"></div>
                                @endif
                                
                                <!-- Sent bar (middle) -->
                                @if(($day['sent'] ?? 0) > 0)
                                    <div class="w-full bg-blue-500" 
                                         style="height: {{ round(($day['sent'] / $maxValue) * 150) }}px;"
                                         title="Sent: {{ $day['sent'] }}"></div>
                                @endif
                                
                                <!-- Delivered bar (top) -->
                                @if(($day['delivered'] ?? 0) > 0)
                                    <div class="w-full bg-green-500" 
                                         style="height: {{ round(($day['delivered'] / $maxValue) * 150) }}px;"
                                         title="Delivered: {{ $day['delivered'] }}"></div>
                                @endif
                                
                                <!-- Empty state -->
                                @if(($day['delivered'] ?? 0) == 0 && ($day['sent'] ?? 0) == 0 && ($day['failed'] ?? 0) == 0)
                                    <div class="w-full bg-gray-200" 
                                         style="height: 5px;"
                                         title="No data"></div>
                                @endif
                            </div>
                            <div class="text-xs text-gray-600 text-center font-medium">{{ $day['date'] ?? '' }}</div>
                        </div>
                    @empty
                        <div class="flex-1 flex items-center justify-center">
                            <div class="text-center text-gray-500">
                                <div class="text-2xl mb-2">📊</div>
                                <p class="text-sm">Aucune donnée de graphique disponible</p>
                            </div>
                        </div>
                    @endforelse
                </div>
                
                <!-- Legend for CSS chart -->
                <div class="flex justify-center space-x-6 mt-4 text-sm">
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-green-500 rounded"></div>
                        <span>Livrés</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-blue-500 rounded"></div>
                        <span>Envoyés</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-red-500 rounded"></div>
                        <span>Échecs</span>
                    </div>
                </div>
            </div>
            
            <!-- Auto-refresh controls -->
            <div class="mt-4 flex justify-center">
                <button wire:click="refreshData" 
                        class="text-sm text-blue-600 hover:text-blue-800 underline">
                    🔄 Actualiser les Données du Graphique
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Messages -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">📨 Messages Récents</h3>
            </div>
            <div class="overflow-hidden">
                <div class="max-h-96 overflow-y-auto">
                    @forelse($recentMessages as $message)
                        <div class="px-6 py-3 border-b border-gray-100 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-gray-900">{{ $message['client_name'] }}</span>
                                        <span class="text-sm text-gray-500">→</span>
                                        <span class="text-sm text-gray-600">{{ $message['to'] }}</span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600 truncate">{{ $message['content_preview'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $message['created_at'] }}</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $message['status_class'] }}">
                                        {{ ucfirst($message['status']) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500">
                            <div class="text-2xl mb-2">📭</div>
                            Aucun message récent
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">🏥 État du Système</h3>
            </div>
            <div class="p-6 space-y-4">
                <!-- Kannel Status -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="text-lg">🔗</div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">Passerelle Kannel</div>
                            <div class="text-xs text-gray-500">Connexion Passerelle SMS</div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if(isset($systemHealth['kannel']['success']) && $systemHealth['kannel']['success'])
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                🟢 En ligne
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                🔴 Hors ligne
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Database Status -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="text-lg">🗄️</div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">Base de Données</div>
                            <div class="text-xs text-gray-500">{{ $systemHealth['database']['response_time_ms'] ?? 0 }}ms de réponse</div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if(isset($systemHealth['database']['success']) && $systemHealth['database']['success'])
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                🟢 OK
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                🔴 Erreur
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Queue Status -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="text-lg">⏳</div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">Système de File</div>
                            <div class="text-xs text-gray-500">
                                {{ $systemHealth['queue']['pending_jobs'] ?? 0 }} en attente, 
                                {{ $systemHealth['queue']['failed_jobs'] ?? 0 }} échec(s)
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if(isset($systemHealth['queue']['success']) && $systemHealth['queue']['success'])
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                🟢 Actif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                🔴 Erreur
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Disk Space -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="text-lg">💾</div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">Espace Disque</div>
                            <div class="text-xs text-gray-500">{{ $systemHealth['disk_space']['free_space_gb'] ?? 0 }} GB libre</div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if(isset($systemHealth['disk_space']['success']) && $systemHealth['disk_space']['success'])
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                🟢 OK
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                ⚠️ Faible
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-refresh notification -->
    <div x-data="{ show: false, timestamp: '' }" 
         x-show="show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         @data-refreshed.window="show = true; timestamp = $event.detail.timestamp; setTimeout(() => show = false, 3000)"
         class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50">
        <div class="flex items-center space-x-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span>Données actualisées à <span x-text="timestamp"></span></span>
        </div>
    </div>
</div>

