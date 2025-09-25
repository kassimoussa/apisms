<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\BulkSmsJob;

class ClearStuckBulkSmsJobs extends Command
{
    protected $signature = 'sms:clear-stuck-jobs {--force : Force clear without confirmation}';
    
    protected $description = 'Clear stuck bulk SMS jobs and queue items';

    public function handle()
    {
        // Clear stuck queue jobs
        $stuckQueueJobs = DB::table('jobs')
            ->where('queue', 'bulk-sms')
            ->where('created_at', '<', now()->subMinutes(30)) // Older than 30 minutes
            ->count();

        // Clear stuck bulk SMS jobs in processing status
        $stuckBulkJobs = BulkSmsJob::where('status', 'processing')
            ->where('updated_at', '<', now()->subMinutes(30))
            ->count();

        if ($stuckQueueJobs === 0 && $stuckBulkJobs === 0) {
            $this->info('No stuck jobs found.');
            return;
        }

        $this->warn("Found {$stuckQueueJobs} stuck queue jobs and {$stuckBulkJobs} stuck bulk SMS jobs.");

        if (!$this->option('force') && !$this->confirm('Do you want to clear these stuck jobs?')) {
            $this->info('Operation cancelled.');
            return;
        }

        // Clear stuck queue jobs
        if ($stuckQueueJobs > 0) {
            $deleted = DB::table('jobs')
                ->where('queue', 'bulk-sms')
                ->where('created_at', '<', now()->subMinutes(30))
                ->delete();
            
            $this->info("Cleared {$deleted} stuck queue jobs.");
        }

        // Reset stuck bulk SMS jobs
        if ($stuckBulkJobs > 0) {
            $updated = BulkSmsJob::where('status', 'processing')
                ->where('updated_at', '<', now()->subMinutes(30))
                ->update([
                    'status' => 'failed',
                    'failure_reason' => 'Job was stuck in processing status - auto-reset by clear-stuck-jobs command'
                ]);
            
            $this->info("Reset {$updated} stuck bulk SMS jobs to failed status.");
        }

        $this->info('✅ All stuck jobs cleared successfully!');
    }
}