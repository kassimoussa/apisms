<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\SmsMessage;
use Carbon\Carbon;

class GlobalStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sms:stats 
                            {--period=month : Time period (today, week, month, year)}
                            {--format=table : Output format (table, json, csv)}
                            {--export= : Export to file path}';

    /**
     * The console command description.
     */
    protected $description = 'Display global SMS statistics and analytics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $period = $this->option('period');
        $format = $this->option('format');
        $exportPath = $this->option('export');

        $this->info('📊 Generating SMS Gateway Statistics...');
        $this->newLine();

        $stats = $this->generateStats($period);

        switch ($format) {
            case 'json':
                $output = json_encode($stats, JSON_PRETTY_PRINT);
                break;
            case 'csv':
                $output = $this->formatAsCsv($stats);
                break;
            default:
                $this->displayTableFormat($stats);
                $output = null;
        }

        if ($exportPath && $output) {
            file_put_contents($exportPath, $output);
            $this->info("📁 Statistics exported to: {$exportPath}");
        } elseif ($output) {
            $this->line($output);
        }

        return Command::SUCCESS;
    }

    private function generateStats(string $period): array
    {
        $startDate = $this->getStartDate($period);
        $endDate = now();

        // Overall statistics
        $totalMessages = SmsMessage::where('created_at', '>=', $startDate)->count();
        $sentMessages = SmsMessage::where('created_at', '>=', $startDate)
                                 ->whereIn('status', ['sent', 'delivered'])
                                 ->count();
        $failedMessages = SmsMessage::where('created_at', '>=', $startDate)
                                   ->where('status', 'failed')
                                   ->count();
        $pendingMessages = SmsMessage::where('status', 'pending')->count();
        
        $successRate = $totalMessages > 0 ? round(($sentMessages / $totalMessages) * 100, 2) : 0;

        // Client statistics
        $clientStats = Client::withCount([
            'smsMessages as total_messages' => function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            },
            'smsMessages as sent_messages' => function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate)
                      ->whereIn('status', ['sent', 'delivered']);
            },
            'smsMessages as failed_messages' => function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate)
                      ->where('status', 'failed');
            }
        ])->get();

        // Daily breakdown
        $dailyStats = $this->getDailyBreakdown($startDate, $endDate);

        return [
            'period' => [
                'name' => $period,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'days' => $startDate->diffInDays($endDate) + 1,
            ],
            'totals' => [
                'total_messages' => $totalMessages,
                'sent_messages' => $sentMessages,
                'failed_messages' => $failedMessages,
                'pending_messages' => $pendingMessages,
                'success_rate' => $successRate . '%',
            ],
            'clients' => $clientStats->map(function ($client) {
                $clientSuccessRate = $client->total_messages > 0 
                    ? round(($client->sent_messages / $client->total_messages) * 100, 2) 
                    : 0;
                    
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'active' => $client->active,
                    'total_messages' => $client->total_messages,
                    'sent_messages' => $client->sent_messages,
                    'failed_messages' => $client->failed_messages,
                    'success_rate' => $clientSuccessRate . '%',
                    'rate_limit' => $client->rate_limit,
                ];
            })->toArray(),
            'daily_breakdown' => $dailyStats,
            'generated_at' => now()->toISOString(),
        ];
    }

    private function displayTableFormat(array $stats): void
    {
        // Period info
        $this->info("📅 Period: {$stats['period']['name']} ({$stats['period']['start_date']} to {$stats['period']['end_date']})");
        $this->newLine();

        // Overall statistics
        $this->info('📊 Overall Statistics:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Messages', number_format($stats['totals']['total_messages'])],
                ['Sent Messages', number_format($stats['totals']['sent_messages'])],
                ['Failed Messages', number_format($stats['totals']['failed_messages'])],
                ['Pending Messages', number_format($stats['totals']['pending_messages'])],
                ['Success Rate', $stats['totals']['success_rate']],
            ]
        );
        $this->newLine();

        // Client statistics
        if (!empty($stats['clients'])) {
            $this->info('👥 Client Statistics:');
            $this->table(
                ['Client', 'Active', 'Total', 'Sent', 'Failed', 'Success Rate', 'Rate Limit'],
                collect($stats['clients'])->map(function ($client) {
                    return [
                        $client['name'],
                        $client['active'] ? '✅' : '❌',
                        number_format($client['total_messages']),
                        number_format($client['sent_messages']),
                        number_format($client['failed_messages']),
                        $client['success_rate'],
                        $client['rate_limit'],
                    ];
                })->toArray()
            );
            $this->newLine();
        }

        // Recent daily stats
        if (!empty($stats['daily_breakdown'])) {
            $this->info('📈 Recent Daily Activity:');
            $recentDays = array_slice($stats['daily_breakdown'], -7); // Last 7 days
            $this->table(
                ['Date', 'Total', 'Sent', 'Failed'],
                collect($recentDays)->map(function ($day) {
                    return [
                        $day['date'],
                        number_format($day['total']),
                        number_format($day['sent']),
                        number_format($day['failed']),
                    ];
                })->toArray()
            );
        }
    }

    private function formatAsCsv(array $stats): string
    {
        $csv = "Period,{$stats['period']['name']}\n";
        $csv .= "Start Date,{$stats['period']['start_date']}\n";
        $csv .= "End Date,{$stats['period']['end_date']}\n";
        $csv .= "\n";
        
        $csv .= "Overall Statistics\n";
        $csv .= "Total Messages,{$stats['totals']['total_messages']}\n";
        $csv .= "Sent Messages,{$stats['totals']['sent_messages']}\n";
        $csv .= "Failed Messages,{$stats['totals']['failed_messages']}\n";
        $csv .= "Pending Messages,{$stats['totals']['pending_messages']}\n";
        $csv .= "Success Rate,{$stats['totals']['success_rate']}\n";
        $csv .= "\n";
        
        if (!empty($stats['clients'])) {
            $csv .= "Client Statistics\n";
            $csv .= "Client,Active,Total,Sent,Failed,Success Rate,Rate Limit\n";
            foreach ($stats['clients'] as $client) {
                $csv .= "{$client['name']},{$client['active']},{$client['total_messages']},{$client['sent_messages']},{$client['failed_messages']},{$client['success_rate']},{$client['rate_limit']}\n";
            }
        }
        
        return $csv;
    }

    private function getDailyBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        $daily = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $dayStart = $current->copy()->startOfDay();
            $dayEnd = $current->copy()->endOfDay();
            
            $total = SmsMessage::whereBetween('created_at', [$dayStart, $dayEnd])->count();
            $sent = SmsMessage::whereBetween('created_at', [$dayStart, $dayEnd])
                             ->whereIn('status', ['sent', 'delivered'])
                             ->count();
            $failed = SmsMessage::whereBetween('created_at', [$dayStart, $dayEnd])
                               ->where('status', 'failed')
                               ->count();
            
            $daily[] = [
                'date' => $current->toDateString(),
                'total' => $total,
                'sent' => $sent,
                'failed' => $failed,
            ];
            
            $current->addDay();
        }

        return $daily;
    }

    private function getStartDate(string $period): Carbon
    {
        return match ($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
    }
}
