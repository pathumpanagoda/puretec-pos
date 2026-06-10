<?php

namespace Database\Seeders;

use App\Models\PlatformUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PlatformUserSeeder extends Seeder
{
    /**
     * Seed the initial Nexfloit platform admin user.
     */
    public function run(): void
    {
        // Create super admin if doesn't exist
        PlatformUser::firstOrCreate(
            ['email' => 'admin@nexfloit.com'],
            [
                'name' => 'Nexfloit Admin',
                'email' => 'admin@nexfloit.com',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        $this->command->info('Platform user created:');
        $this->command->info('Email: admin@nexfloit.com');
        $this->command->info('Password: admin123');
    }
}
