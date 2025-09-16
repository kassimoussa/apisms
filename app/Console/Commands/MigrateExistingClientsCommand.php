<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Services\ApiKeyEncryptionService;

class MigrateExistingClientsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'client:migrate-keys 
                            {--dry-run : Show what would be migrated without making changes}
                            {--force : Migrate without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Migrate existing clients from plain API keys to encrypted system';

    private ApiKeyEncryptionService $encryptionService;

    public function __construct(ApiKeyEncryptionService $encryptionService)
    {
        parent::__construct();
        $this->encryptionService = $encryptionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🔄 Migrating client API keys to encrypted system...');
        $this->newLine();

        // Find clients with old API keys that need migration
        $clientsToMigrate = Client::whereNotNull('api_key')
                                 ->whereNull('api_key_hash')
                                 ->get();

        if ($clientsToMigrate->isEmpty()) {
            $this->info('✅ No clients need migration. All clients are already using the encrypted system.');
            return Command::SUCCESS;
        }

        $this->info("📋 Found {$clientsToMigrate->count()} clients that need migration:");
        
        // Show clients that will be migrated
        $this->table(['ID', 'Name', 'Current API Key (masked)', 'Status'], 
            $clientsToMigrate->map(function ($client) {
                return [
                    $client->id,
                    $client->name,
                    $this->maskApiKey($client->api_key),
                    $client->active ? 'Active' : 'Inactive'
                ];
            })->toArray()
        );

        if ($dryRun) {
            $this->info('🔍 Dry run mode - no changes will be made.');
            $this->comment('Run without --dry-run to perform the actual migration.');
            return Command::SUCCESS;
        }

        $this->newLine();
        $this->warn('⚠️  Important: This migration will:');
        $this->comment('1. Create encrypted versions of existing API keys');
        $this->comment('2. Set expiration dates (365 days from now)');
        $this->comment('3. Keep old keys for backward compatibility during transition');
        $this->comment('4. Allow gradual migration to new authentication system');
        
        if (!$force) {
            $confirmed = $this->confirm('Do you want to proceed with the migration?');
            if (!$confirmed) {
                $this->info('Migration cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->newLine();
        $migrated = 0;
        $failed = 0;

        $progressBar = $this->output->createProgressBar($clientsToMigrate->count());
        $progressBar->start();

        foreach ($clientsToMigrate as $client) {
            try {
                // Encrypt the existing API key
                $hashedKey = $this->encryptionService->hashApiKey($client->api_key);
                $encryptedKey = $this->encryptionService->encryptApiKey($client->api_key);
                $expiresAt = now()->addDays(365);

                // Update the client with encrypted data
                $client->update([
                    'api_key_hash' => $hashedKey,
                    'api_key_encrypted' => $encryptedKey,
                    'api_key_expires_at' => $expiresAt,
                ]);

                $migrated++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to migrate client {$client->id} ({$client->name}): " . $e->getMessage());
                $failed++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('✅ Migration completed!');
        $this->newLine();
        
        $this->info('📊 Migration Summary:');
        $this->table(['Result', 'Count'], [
            ['Successfully migrated', $migrated],
            ['Failed', $failed],
            ['Total processed', $clientsToMigrate->count()],
        ]);

        if ($migrated > 0) {
            $this->newLine();
            $this->info('📝 Next Steps:');
            $this->comment('1. All existing API keys continue to work as before');
            $this->comment('2. The system now supports both old and new authentication methods');
            $this->comment('3. Plan to migrate client applications to use new keys over time');
            $this->comment('4. Monitor logs to see which clients are using old vs new keys');
            $this->comment('5. Eventually remove old api_key column after full migration');
        }

        if ($failed > 0) {
            $this->newLine();
            $this->warn('⚠️  Some migrations failed. Please check the errors above and retry if needed.');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Mask API key for display
     */
    private function maskApiKey(string $apiKey): string
    {
        if (strlen($apiKey) <= 8) {
            return str_repeat('*', strlen($apiKey));
        }

        return substr($apiKey, 0, 4) . str_repeat('*', strlen($apiKey) - 8) . substr($apiKey, -4);
    }
}
