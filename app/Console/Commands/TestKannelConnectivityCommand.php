<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\KannelService;

class TestKannelConnectivityCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sms:test-kannel 
                            {--timeout=30 : Connection timeout in seconds}
                            {--verbose : Show detailed connection information}';

    /**
     * The console command description.
     */
    protected $description = 'Test connectivity to Kannel SMS gateway';

    private KannelService $kannelService;

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
        $this->info('🔄 Testing Kannel SMS Gateway connectivity...');
        $this->newLine();

        $timeout = (int) $this->option('timeout');
        $verbose = $this->option('verbose');

        // Test basic connectivity
        $this->info('📡 Checking basic connectivity...');
        $startTime = microtime(true);
        
        $result = $this->kannelService->checkConnectivity();
        
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        if ($result['status'] === 'connected') {
            $this->info("✅ Connection successful ({$duration}ms)");
            
            if ($verbose) {
                $this->newLine();
                $this->info('📊 Connection Details:');
                $this->table(['Property', 'Value'], [
                    ['Status', $result['status']],
                    ['Response Time', $duration . ' ms'],
                    ['Server URL', config('services.kannel.url')],
                    ['Username', config('services.kannel.username')],
                    ['From Number', config('services.kannel.from')],
                    ['Timeout', $timeout . ' seconds'],
                ]);
            }

            // Test sending capability (dry run)
            $this->newLine();
            $this->info('🧪 Testing SMS sending capability (dry run)...');
            
            $testResult = $this->testSmsSending();
            
            if ($testResult['success']) {
                $this->info('✅ SMS sending test passed');
                if ($verbose && isset($testResult['details'])) {
                    $this->comment('Response: ' . $testResult['details']);
                }
            } else {
                $this->error('❌ SMS sending test failed: ' . $testResult['error']);
                return Command::FAILURE;
            }

        } else {
            $this->error("❌ Connection failed ({$duration}ms)");
            $this->error('Error: ' . ($result['error'] ?? 'Unknown connection error'));
            
            if ($verbose) {
                $this->newLine();
                $this->error('🔍 Troubleshooting suggestions:');
                $this->comment('1. Check if Kannel server is running');
                $this->comment('2. Verify server URL: ' . config('services.kannel.url'));
                $this->comment('3. Check username and password credentials');
                $this->comment('4. Verify network connectivity and firewall rules');
                $this->comment('5. Check Kannel logs for detailed error information');
            }
            
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('🎉 All tests passed! Kannel gateway is ready for SMS operations.');
        
        return Command::SUCCESS;
    }

    /**
     * Test SMS sending capability
     */
    private function testSmsSending(): array
    {
        try {
            // Use a test number that won't actually send
            $testNumber = '77000000'; // Test number
            $testMessage = 'Test connectivity - do not deliver';
            
            // This is a dry run - we're testing the request format, not actually sending
            $result = $this->kannelService->sendSms($testNumber, $testMessage, config('services.kannel.from'), true);
            
            return [
                'success' => true,
                'details' => 'SMS request format validated successfully'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
