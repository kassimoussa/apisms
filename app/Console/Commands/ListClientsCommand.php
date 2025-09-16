<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;

class ListClientsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'client:list 
                            {--active-only : Show only active clients}
                            {--show-expiry : Show API key expiration dates}
                            {--format=table : Output format (table, json, csv)}';

    /**
     * The console command description.
     */
    protected $description = 'List all API clients with their details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $activeOnly = $this->option('active-only');
        $showExpiry = $this->option('show-expiry');
        $format = $this->option('format');

        $this->info('📋 Listing API Clients...');
        $this->newLine();

        // Query clients
        $query = Client::withCount([
            'smsMessages as total_messages',
            'smsMessages as sent_messages' => function ($q) {
                $q->whereIn('status', ['sent', 'delivered']);
            },
            'smsMessages as failed_messages' => function ($q) {
                $q->where('status', 'failed');
            }
        ]);

        if ($activeOnly) {
            $query->where('active', true);
        }

        $clients = $query->orderBy('created_at', 'desc')->get();

        if ($clients->isEmpty()) {
            $this->warn('No clients found.');
            return Command::SUCCESS;
        }

        // Prepare data
        $clientData = $clients->map(function ($client) use ($showExpiry) {
            $successRate = $client->total_messages > 0 
                ? round(($client->sent_messages / $client->total_messages) * 100, 1) 
                : 0;

            $data = [
                'ID' => $client->id,
                'Name' => $client->name,
                'Status' => $client->active ? '✅ Active' : '❌ Inactive',
                'Rate Limit' => $client->rate_limit . '/min',
                'Total SMS' => number_format($client->total_messages),
                'Success Rate' => $successRate . '%',
                'Created' => $client->created_at->format('Y-m-d H:i'),
            ];

            if ($showExpiry) {
                $expiry = $client->api_key_expires_at 
                    ? $client->api_key_expires_at->format('Y-m-d')
                    : 'Never';
                $data['Key Expires'] = $expiry;
                
                if ($client->api_key_expires_at && $client->api_key_expires_at->isPast()) {
                    $data['Key Expires'] = '🔴 ' . $expiry;
                } elseif ($client->api_key_expires_at && $client->api_key_expires_at->diffInDays(now()) < 30) {
                    $data['Key Expires'] = '🟡 ' . $expiry;
                }
            }

            return $data;
        });

        // Display based on format
        switch ($format) {
            case 'json':
                $this->line(json_encode($clientData->toArray(), JSON_PRETTY_PRINT));
                break;

            case 'csv':
                if ($clientData->isNotEmpty()) {
                    $headers = array_keys($clientData->first());
                    $this->line(implode(',', $headers));
                    foreach ($clientData as $row) {
                        $this->line(implode(',', array_map(function($value) {
                            return '"' . str_replace('"', '""', $value) . '"';
                        }, $row)));
                    }
                }
                break;

            default: // table
                if ($clientData->isNotEmpty()) {
                    $headers = array_keys($clientData->first());
                    $rows = $clientData->map(function($item) {
                        return array_values($item);
                    })->toArray();
                    
                    $this->table($headers, $rows);
                }
        }

        $this->newLine();
        $this->info('📊 Summary:');
        $this->comment('Total clients: ' . $clients->count());
        $this->comment('Active clients: ' . $clients->where('active', true)->count());
        $this->comment('Inactive clients: ' . $clients->where('active', false)->count());
        
        if ($showExpiry) {
            $expired = $clients->filter(function($client) {
                return $client->api_key_expires_at && $client->api_key_expires_at->isPast();
            })->count();
            
            $expiringSoon = $clients->filter(function($client) {
                return $client->api_key_expires_at && 
                       !$client->api_key_expires_at->isPast() &&
                       $client->api_key_expires_at->diffInDays(now()) < 30;
            })->count();
            
            if ($expired > 0) {
                $this->warn('🔴 Keys expired: ' . $expired);
            }
            if ($expiringSoon > 0) {
                $this->comment('🟡 Keys expiring soon (< 30 days): ' . $expiringSoon);
            }
        }

        return Command::SUCCESS;
    }
}
