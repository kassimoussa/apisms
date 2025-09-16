<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BulkSmsJob;
use App\Jobs\ProcessBulkSmsJob;

class ProcessScheduledBulkSmsCommand extends Command
{
    protected $signature = 'bulk-sms:process-scheduled 
                           {--dry-run : Show what would be processed without executing}';

    protected $description = 'Process scheduled bulk SMS jobs';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('🔄 Processing scheduled bulk SMS jobs...');
        $this->newLine();

        // Find scheduled jobs that are ready to run
        $scheduledJobs = BulkSmsJob::scheduled()->get();

        if ($scheduledJobs->isEmpty()) {
            $this->info('✅ No scheduled bulk SMS jobs ready to process.');
            return self::SUCCESS;
        }

        $this->info("📋 Found {$scheduledJobs->count()} scheduled jobs ready to process:");
        $this->newLine();

        // Show jobs table
        $tableData = $scheduledJobs->map(function ($job) {
            return [
                $job->id,
                $job->name,
                $job->client->name,
                $job->total_count,
                $job->scheduled_at->format('Y-m-d H:i:s'),
                $job->scheduled_at->diffForHumans(),
                $job->status,
            ];
        })->toArray();

        $this->table([
            'ID', 'Name', 'Client', 'Recipients', 'Scheduled At', 'Due', 'Status'
        ], $tableData);

        if ($dryRun) {
            $this->info('🔍 Dry run mode - no jobs will be processed.');
            return self::SUCCESS;
        }

        $this->newLine();
        $processed = 0;
        $failed = 0;

        foreach ($scheduledJobs as $job) {
            try {
                $this->info("⏳ Processing job {$job->id}: {$job->name}");

                // Dispatch the bulk SMS processing job
                $batchSize = $job->settings['batch_size'] ?? 50;
                ProcessBulkSmsJob::dispatch($job->id, $batchSize);

                $processed++;
                $this->info("✅ Job {$job->id} dispatched successfully");

            } catch (\Exception $e) {
                $failed++;
                $this->error("❌ Failed to process job {$job->id}: " . $e->getMessage());
                
                // Mark job as failed
                $job->fail("Scheduled processing failed: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('📊 Processing Summary:');
        $this->table(['Result', 'Count'], [
            ['Successfully processed', $processed],
            ['Failed', $failed],
            ['Total', $scheduledJobs->count()],
        ]);

        if ($failed > 0) {
            $this->warn("⚠️  {$failed} jobs failed to process. Check logs for details.");
            return self::FAILURE;
        }

        $this->info('🎉 All scheduled bulk SMS jobs processed successfully!');
        return self::SUCCESS;
    }
}
