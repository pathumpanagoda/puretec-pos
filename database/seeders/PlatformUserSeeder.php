<?php

namespace Database\Seeders;

use App\Models\PlatformUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PlatformUserSeeder extends Seeder
{
    /**
     * Seed the initial Puretec platform admin user.
     */
    public function run(): void
    {
        // Create super admin if doesn't exist
        PlatformUser::firstOrCreate(
            ['email' => 'admin@puretec.com'],
            [
                'name' => 'Puretec Admin',
                'email' => 'admin@puretec.com',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        $this->command->info('Platform user created:');
        $this->command->info('Email: admin@puretec.com');
        $this->command->info('Password: admin123');
    }
}
