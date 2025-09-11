<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\KannelService;
use App\Models\Client;
use App\Models\SmsMessage;

class TestSmsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:test 
                            {to : Phone number to send SMS to (+253XXXXXXXX)} 
                            {--message= : Custom message (optional)} 
                            {--from= : Custom from number (optional)}
                            {--client= : Client ID to use (optional)}
                            {--check-connectivity : Only check Kannel connectivity}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SMS sending via Kannel gateway';

    protected KannelService $kannelService;

    public function __construct(KannelService $kannelService)
    {
        parent::__construct();
        $this->kannelService = $kannelService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 ApiSMS Gateway Test Command');
        $this->newLine();

        // Check connectivity only
        if ($this->option('check-connectivity')) {
            return $this->checkConnectivity();
        }

        // Get parameters
        $to = $this->argument('to');
        $message = $this->option('message') ?: 'Test SMS from ApiSMS Gateway at ' . now()->format('Y-m-d H:i:s');
        $from = $this->option('from');
        $clientId = $this->option('client');

        // Validate phone number
        if (!$this->kannelService->isValidPhoneNumber($to)) {
            $this->error("❌ Invalid phone number: $to");
            $this->info('Expected formats: +253XXXXXXXX, 253XXXXXXXX, or 77XXXXXX');
            return 1;
        }

        // Get or create test client
        $client = $this->getClient($clientId);
        if (!$client) {
            return 1;
        }

        // Display test info
        $this->info("📱 Testing SMS Send:");
        $this->table(['Parameter', 'Value'], [
            ['To', $to],
            ['Message', $message],
            ['From', $from ?: config('services.kannel.from')],
            ['Client', "{$client->name} (ID: {$client->id})"],
            ['Kannel URL', config('services.kannel.url')],
        ]);

        if (!$this->confirm('Send this SMS?', true)) {
            $this->info('SMS test cancelled.');
            return 0;
        }

        // Check connectivity first
        $this->info('🔗 Checking Kannel connectivity...');
        $connectivity = $this->kannelService->checkConnectivity();
        
        if (!$connectivity['success']) {
            $this->error('❌ Cannot connect to Kannel:');
            $this->error($connectivity['message'] ?? 'Unknown error');
            return 1;
        }
        
        $this->info('✅ Kannel is accessible');

        // Create SMS message record
        $this->info('💾 Creating SMS record...');
        $smsMessage = SmsMessage::create([
            'client_id' => $client->id,
            'direction' => 'outbound',
            'from' => $from ?: config('services.kannel.from'),
            'to' => $to,
            'content' => $message,
            'status' => 'pending',
            'metadata' => [
                'test_command' => true,
                'executed_at' => now()->toISOString(),
            ],
        ]);

        $this->info("📝 SMS record created with ID: {$smsMessage->id}");

        // Send SMS
        $this->info('📤 Sending SMS via Kannel...');
        $this->newLine();

        $result = $this->kannelService->sendSms($to, $message, $from);

        // Display results
        if ($result['success']) {
            $smsMessage->markAsSent($result['kannel_id'] ?? null);
            
            $this->info('✅ SMS sent successfully!');
            $this->table(['Result', 'Value'], [
                ['Status', 'Success'],
                ['Kannel ID', $result['kannel_id'] ?? 'N/A'],
                ['Message ID', $smsMessage->id],
                ['Sent At', $smsMessage->fresh()->sent_at?->format('Y-m-d H:i:s') ?? 'N/A'],
            ]);

            $this->newLine();
            $this->info("💡 Track delivery status: php artisan sms:status {$smsMessage->id}");

        } else {
            $smsMessage->markAsFailed(
                $result['error_code'] ?? 'UNKNOWN',
                $result['error_message'] ?? 'Unknown error'
            );

            $this->error('❌ SMS sending failed!');
            $this->table(['Error', 'Details'], [
                ['Code', $result['error_code'] ?? 'UNKNOWN'],
                ['Message', $result['error_message'] ?? 'Unknown error'],
                ['SMS ID', $smsMessage->id],
            ]);

            return 1;
        }

        return 0;
    }

    private function checkConnectivity(): int
    {
        $this->info('🔗 Testing Kannel connectivity...');
        $this->newLine();

        $result = $this->kannelService->checkConnectivity();

        if ($result['success']) {
            $this->info('✅ Kannel connectivity test passed!');
            $this->table(['Parameter', 'Value'], [
                ['URL', config('services.kannel.url')],
                ['Username', config('services.kannel.username')],
                ['Status Code', $result['status_code'] ?? 'N/A'],
                ['Message', $result['message'] ?? 'Connected'],
            ]);
            return 0;
        } else {
            $this->error('❌ Kannel connectivity test failed!');
            $this->error('Error: ' . ($result['error'] ?? 'Unknown error'));
            $this->newLine();
            $this->info('💡 Check your Kannel configuration in .env file:');
            $this->info('   - KANNEL_URL');
            $this->info('   - KANNEL_USERNAME');
            $this->info('   - KANNEL_PASSWORD');
            return 1;
        }
    }

    private function getClient(?string $clientId): ?Client
    {
        if ($clientId) {
            $client = Client::find($clientId);
            if (!$client) {
                $this->error("❌ Client with ID {$clientId} not found");
                return null;
            }
            if (!$client->active) {
                $this->error("❌ Client {$client->name} is not active");
                return null;
            }
            return $client;
        }

        // Get first active client or create test client
        $client = Client::active()->first();
        
        if (!$client) {
            $this->info('📝 No active clients found. Creating test client...');
            $client = Client::create([
                'name' => 'Test Client',
                'rate_limit' => 100,
                'active' => true,
                'description' => 'Auto-created test client for SMS testing',
            ]);
            $this->info("✅ Test client created with API key: {$client->api_key}");
        }

        return $client;
    }
}
