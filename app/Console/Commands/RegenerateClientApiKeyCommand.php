<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;

class RegenerateClientApiKeyCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'client:regenerate-key 
                            {client_id : ID of the client}
                            {--expire-days=365 : Number of days until key expires}
                            {--force : Force regeneration without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Regenerate API key for an existing client';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clientId = $this->argument('client_id');
        $expireDays = (int) $this->option('expire-days');
        $force = $this->option('force');

        // Find the client
        $client = Client::find($clientId);

        if (!$client) {
            $this->error("❌ Client with ID {$clientId} not found.");
            return Command::FAILURE;
        }

        // Show current client info
        $this->info("📋 Current Client Information:");
        $this->table(['Property', 'Value'], [
            ['ID', $client->id],
            ['Name', $client->name],
            ['Status', $client->active ? 'Active' : 'Inactive'],
            ['Rate Limit', $client->rate_limit . ' per minute'],
            ['Current Key Expires', $client->api_key_expires_at ? $client->api_key_expires_at->toDateString() : 'Never'],
            ['Masked Current Key', $client->getMaskedApiKey() ?: 'No encrypted key'],
        ]);

        $this->newLine();

        // Confirmation
        if (!$force) {
            $confirmed = $this->confirm(
                "⚠️  This will invalidate the current API key. Are you sure you want to regenerate it?"
            );

            if (!$confirmed) {
                $this->info("Operation cancelled.");
                return Command::SUCCESS;
            }
        }

        $this->info("🔄 Regenerating API key...");

        try {
            // Regenerate the API key
            $newApiKey = $client->regenerateApiKey();

            $this->info("✅ API key regenerated successfully!");
            $this->newLine();

            // Show updated info
            $this->info("📄 Updated Client Details:");
            $this->table(['Property', 'Value'], [
                ['ID', $client->id],
                ['Name', $client->name],
                ['Rate Limit', $client->rate_limit . ' per minute'],
                ['Status', $client->active ? 'Active' : 'Inactive'],
                ['New Key Expires', $client->api_key_expires_at->toDateString()],
                ['Created', $client->created_at->toDateTimeString()],
                ['Updated', $client->updated_at->toDateTimeString()],
            ]);

            $this->newLine();
            $this->info("🔑 New API Key (save this securely - it won't be shown again):");
            $this->warn($newApiKey);
            
            $this->newLine();
            $this->comment("📝 Important Notes:");
            $this->comment("1. The old API key is now invalid and must be updated in all applications");
            $this->comment("2. The new key will expire in {$expireDays} days");
            $this->comment("3. Update all systems using this client's API key immediately");
            $this->comment("4. Test the new key before deploying to production");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Failed to regenerate API key: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
