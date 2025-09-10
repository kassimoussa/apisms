<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create DPCR client
        Client::create([
            'name' => 'DPCR - Fleet Management',
            'rate_limit' => 100,
            'active' => true,
            'description' => 'DPCR Fleet Management IoT system client - Primary customer for SMS notifications',
        ]);

        // Create a demo/test client
        Client::create([
            'name' => 'Demo Client',
            'rate_limit' => 50,
            'active' => true,
            'description' => 'Demo client for testing and showcase purposes',
        ]);

        $this->command->info('✅ Created clients with API keys:');
        
        Client::all()->each(function ($client) {
            $this->command->info("  - {$client->name}: {$client->api_key}");
        });
    }
}
