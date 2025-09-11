<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Services\ApiKeyEncryptionService;

class CreateClientCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'client:create 
                            {name : Client name}
                            {--rate-limit=100 : Rate limit per minute}
                            {--description= : Client description}
                            {--allowed-ips=* : Allowed IP addresses}
                            {--inactive : Create as inactive client}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new API client';

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
        $name = $this->argument('name');
        $rateLimit = (int) $this->option('rate-limit');
        $description = $this->option('description');
        $allowedIps = $this->option('allowed-ips');
        $active = !$this->option('inactive');

        $this->info("📝 Creating new API client: {$name}");
        $this->newLine();

        // Generate API key
        $keyData = $this->encryptionService->generateExpiringApiKey('sk', 365);

        try {
            $client = Client::create([
                'name' => $name,
                'api_key_hash' => $keyData['hashed_key'],
                'api_key_encrypted' => $keyData['encrypted_key'],
                'api_key_expires_at' => $keyData['expires_at'],
                'rate_limit' => $rateLimit,
                'active' => $active,
                'allowed_ips' => $allowedIps ?: null,
                'description' => $description,
            ]);

            $this->info("✅ Client created successfully!");
            $this->newLine();

            // Display client information
            $this->info('📄 Client Details:');
            $this->table(['Property', 'Value'], [
                ['ID', $client->id],
                ['Name', $client->name],
                ['Rate Limit', $client->rate_limit . ' per minute'],
                ['Status', $client->active ? 'Active' : 'Inactive'],
                ['Description', $client->description ?: 'None'],
                ['Allowed IPs', $client->allowed_ips ? implode(', ', $client->allowed_ips) : 'Any'],
                ['API Key Expires', $client->api_key_expires_at->toDateString()],
                ['Created', $client->created_at->toDateTimeString()],
            ]);

            $this->newLine();
            $this->info('🔑 API Key (save this securely - it won\'t be shown again):');
            $this->warn($keyData['plain_key']);
            
            $this->newLine();
            $this->comment('📝 Important Notes:');
            $this->comment('1. Store the API key securely - it cannot be retrieved later');
            $this->comment('2. The key will expire in 365 days');
            $this->comment('3. Use this key in the X-API-Key header or Authorization Bearer token');
            if ($client->allowed_ips) {
                $this->comment('4. API access is restricted to specified IP addresses');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to create client: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
