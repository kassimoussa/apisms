<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin
        Admin::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@apisms.local',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
                'active' => true
            ]
        );

        // Create regular admin
        Admin::updateOrCreate(
            ['username' => 'manager'],
            [
                'name' => 'SMS Manager',
                'email' => 'manager@apisms.local',
                'username' => 'manager',
                'password' => Hash::make('manager123'),
                'role' => 'admin',
                'active' => true
            ]
        );

        $this->command->info('✅ Admins created successfully:');
        $this->command->info('   Super Admin: admin / admin123');
        $this->command->info('   Admin: manager / manager123');
    }
}
