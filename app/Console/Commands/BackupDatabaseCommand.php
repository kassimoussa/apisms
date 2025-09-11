<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseBackupService;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database 
                            {--tables=* : Specific tables to backup}
                            {--exclude=* : Tables to exclude from backup}
                            {--no-data : Backup structure only, no data}
                            {--force : Force backup even if conditions are not met}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a database backup';

    private DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        parent::__construct();
        $this->backupService = $backupService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!config('backup.enabled')) {
            $this->error('Database backup is disabled in configuration.');
            return Command::FAILURE;
        }

        $this->info('🔄 Starting database backup...');
        
        $options = [];
        
        // Handle specific tables option
        if ($this->option('tables')) {
            $options['tables'] = $this->option('tables');
            $this->info('📋 Backing up specific tables: ' . implode(', ', $options['tables']));
        }
        
        // Handle exclude tables option
        if ($this->option('exclude')) {
            $options['exclude_tables'] = array_merge(
                config('backup.exclude_tables', []),
                $this->option('exclude')
            );
            $this->info('🚫 Excluding tables: ' . implode(', ', $options['exclude_tables']));
        }
        
        // Handle no-data option
        if ($this->option('no-data')) {
            $options['skip_data'] = true;
            $this->info('📄 Structure only backup (no data)');
        }

        // Create progress bar
        $progressBar = $this->output->createProgressBar(3);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        $progressBar->setMessage('Analyzing database...');
        $progressBar->advance();
        
        $progressBar->setMessage('Creating backup...');
        $progressBar->advance();

        // Create backup
        $result = $this->backupService->createBackup($options);
        
        $progressBar->setMessage('Finalizing...');
        $progressBar->advance();
        $progressBar->finish();
        
        $this->newLine(2);

        if ($result['success']) {
            $this->info('✅ ' . $result['message']);
            
            $metadata = $result['metadata'];
            $this->table(['Property', 'Value'], [
                ['Filename', $metadata['filename']],
                ['Size', $metadata['size_human']],
                ['Duration', $metadata['duration_ms'] . ' ms'],
                ['Tables', count($metadata['tables_included'])],
                ['Compressed', $metadata['compressed'] ? 'Yes' : 'No'],
                ['Disk', $metadata['disk']],
                ['Checksum', substr($metadata['checksum'], 0, 16) . '...'],
            ]);
            
            return Command::SUCCESS;
        } else {
            $this->error('❌ Backup failed: ' . $result['error']);
            return Command::FAILURE;
        }
    }
}
