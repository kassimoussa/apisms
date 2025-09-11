<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SmsMessage;

class ClearTestDataCommand extends Command
{
    protected $signature = 'sms:clear-test-data {--force : Skip confirmation}';
    protected $description = 'Clear test SMS data from the database';

    public function handle()
    {
        $testMessages = SmsMessage::whereJsonContains('metadata->test_data', true);
        $count = $testMessages->count();
        
        if ($count === 0) {
            $this->info('No test data found to clear.');
            return 0;
        }
        
        $this->info("Found {$count} test SMS messages to delete.");
        
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to delete all test data?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }
        
        $testMessages->delete();
        
        $this->info("✅ Successfully deleted {$count} test SMS messages!");
        
        return 0;
    }
}