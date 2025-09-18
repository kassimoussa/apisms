<div class="py-6">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    📈 Statistiques et Analyses
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Suivez les performances et l'utilisation de vos campagnes SMS
                </p>
            </div>
        </div>

        <!-- Overall Stats -->
        <div class="mt-8">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Performance Générale</h3>
            <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
                <!-- Total SMS -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">📤</span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total SMS</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_sms']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sent SMS -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">✅</span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">SMS Envoyés</dt>
                                    <dd class="text-lg font-medium text-green-600">{{ number_format($stats['sent_sms']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Failed SMS -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">❌</span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">SMS Échoués</dt>
                                    <dd class="text-lg font-medium text-red-600">{{ number_format($stats['failed_sms']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Campaigns -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">📊</span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Campagnes</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_campaigns']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success Rate -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">🎯</span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Taux de Succès</dt>
                                    <dd class="text-lg font-medium text-blue-600">
                                        @if($stats['total_sms'] > 0)
                                            {{ number_format(($stats['sent_sms'] / $stats['total_sms']) * 100, 1) }}%
                                        @else
                                            0%
                                        @endif
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Breakdown -->
        <div class="mt-8">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Répartition sur 12 Mois</h3>
            <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mois
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SMS Envoyés
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SMS Échoués
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Campagnes
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Taux de Succès
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($monthlyStats as $month)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $month['month'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                        <div class="flex items-center">
                                            <span class="mr-2">✅</span>
                                            {{ number_format($month['sent']) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                        <div class="flex items-center">
                                            <span class="mr-2">❌</span>
                                            {{ number_format($month['failed']) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex items-center">
                                            <span class="mr-2">📊</span>
                                            {{ number_format($month['campaigns']) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
                                        @php
                                            $total = $month['sent'] + $month['failed'];
                                            $rate = $total > 0 ? ($month['sent'] / $total) * 100 : 0;
                                        @endphp
                                        <div class="flex items-center">
                                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $rate }}%"></div>
                                            </div>
                                            <span>{{ number_format($rate, 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Performance Insights -->
        <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2">
            <!-- Best Performance -->
            <div class="bg-green-50 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <h4 class="text-lg font-medium text-green-900">🏆 Meilleur Mois</h4>
                    @php
                        $bestMonth = $monthlyStats->sortByDesc('sent')->first();
                    @endphp
                    @if($bestMonth && $bestMonth['sent'] > 0)
                        <div class="mt-2">
                            <p class="text-2xl font-bold text-green-600">{{ $bestMonth['month'] }}</p>
                            <p class="text-sm text-green-700">{{ number_format($bestMonth['sent']) }} SMS envoyés</p>
                        </div>
                    @else
                        <p class="mt-2 text-sm text-green-700">Aucune donnée disponible pour l'instant</p>
                    @endif
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-blue-50 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <h4 class="text-lg font-medium text-blue-900">📈 Ce Mois-ci</h4>
                    @php
                        $thisMonth = $monthlyStats->last();
                    @endphp
                    @if($thisMonth)
                        <div class="mt-2">
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($thisMonth['sent']) }}</p>
                            <p class="text-sm text-blue-700">SMS envoyés jusqu'à présent</p>
                            @if($thisMonth['campaigns'] > 0)
                                <p class="text-xs text-blue-600 mt-1">{{ $thisMonth['campaigns'] }} campagnes lancées</p>
                            @endif
                        </div>
                    @else
                        <p class="mt-2 text-sm text-blue-700">Aucune activité ce mois-ci</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Usage Tips -->
        <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <span class="text-2xl">💡</span>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Conseils de Performance
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Surveillez vos taux de succès pour identifier les problèmes de livraison</li>
                            <li>Testez les campagnes avec de petits groupes avant de les étendre</li>
                            <li>Utilisez des vitesses d'envoi appropriées pour éviter de surcharger le réseau</li>
                            <li>Programmez les campagnes pendant les heures optimales pour un meilleur engagement</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>