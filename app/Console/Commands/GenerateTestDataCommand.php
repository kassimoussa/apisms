<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\SmsMessage;
use Carbon\Carbon;

class GenerateTestDataCommand extends Command
{
    protected $signature = 'sms:generate-test-data {--count=50 : Number of test SMS to generate}';
    protected $description = 'Generate test SMS data for dashboard testing';

    public function handle()
    {
        $count = $this->option('count');
        
        $this->info("Generating {$count} test SMS messages...");
        
        // Get or create a test client
        $client = Client::firstOrCreate([
            'name' => 'Test Client'
        ], [
            'rate_limit' => 100,
            'active' => true,
            'description' => 'Auto-generated test client for dashboard data',
        ]);
        
        $this->info("Using client: {$client->name} (ID: {$client->id})");
        
        // Generate test data for the last 7 days
        $statuses = ['sent', 'delivered', 'failed', 'pending'];
        $statusWeights = [30, 50, 15, 5]; // Probability weights
        
        for ($i = 0; $i < $count; $i++) {
            // Random date within last 7 days
            $createdAt = Carbon::now()->subDays(rand(0, 6))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            
            // Weighted random status selection
            $status = $this->getWeightedRandomStatus($statuses, $statusWeights);
            
            $smsMessage = SmsMessage::create([
                'client_id' => $client->id,
                'direction' => 'outbound',
                'from' => $this->generateRandomSender(),
                'to' => $this->generateRandomPhone(),
                'content' => $this->generateRandomMessage(),
                'status' => $status,
                'kannel_id' => 'test_' . uniqid(),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'sent_at' => $status !== 'pending' ? $createdAt->addSeconds(rand(1, 30)) : null,
                'delivered_at' => $status === 'delivered' ? $createdAt->addSeconds(rand(30, 120)) : null,
                'metadata' => [
                    'test_data' => true,
                    'generated_at' => now()->toISOString(),
                ],
            ]);
            
            if ($i % 10 === 0) {
                $this->info("Generated {$i}/{$count} messages...");
            }
        }
        
        $this->info("✅ Successfully generated {$count} test SMS messages!");
        $this->newLine();
        
        // Show summary
        $this->info("📊 Summary by status:");
        foreach ($statuses as $status) {
            $statusCount = SmsMessage::where('status', $status)->count();
            $this->info("   {$status}: {$statusCount}");
        }
        
        $this->newLine();
        $this->info("🎯 You can now view the dashboard with test data!");
        $this->info("💡 To clear test data later: php artisan sms:clear-test-data");
    }
    
    private function getWeightedRandomStatus(array $statuses, array $weights): string
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($statuses as $index => $status) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $status;
            }
        }
        
        return $statuses[0]; // Fallback
    }
    
    private function generateRandomPhone(): string
    {
        // Use specific number 50% of the time, random otherwise
        if (rand(0, 1) === 0) {
            return '+25377166677';
        }
        
        $prefixes = ['77', '78', '79', '70'];
        $prefix = $prefixes[array_rand($prefixes)];
        $number = $prefix . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        return '+253' . $number;
    }
    
    private function generateRandomSender(): string
    {
        $senders = [
            '11123',
            'DPCR',
            'FLEET',
            'ALERTS',
            'INFO',
        ];
        
        // Use 11123 more frequently (60% of the time)
        if (rand(1, 100) <= 60) {
            return '11123';
        }
        
        return $senders[array_rand($senders)];
    }
    
    private function generateRandomMessage(): string
    {
        $messages = [
            'Test SMS from ApiSMS Gateway',
            'Your fleet vehicle #12345 has completed maintenance',
            'Alert: Vehicle speed limit exceeded on Route A',
            'Reminder: Vehicle inspection due next week',
            'Success: Message delivery confirmed',
            'System notification: All systems operational',
            'Update: New route assigned to driver John',
            'Warning: Low fuel detected on vehicle #67890',
            'Info: GPS tracking active for all vehicles',
            'Maintenance scheduled for tomorrow at 10:00 AM',
        ];
        
        return $messages[array_rand($messages)];
    }
}