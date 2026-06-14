<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'System',
            'email' => 'admin@jawharah.com',
            'phone' => '966500000000',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'password' => Hash::make('password123'),
            'account_type' => 'buyer', // Admin doesn't need seller type
            'is_approved' => true,
        ]);

        // Assign admin role
        $admin->assignRole('admin');

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@jawharah.com');
        $this->command->info('Password: password123');
        $this->command->warn('⚠️  Please change the password in production!');

        // Create a sample seller (approved)
        $seller = User::create([
            'first_name' => 'أحمد',
            'last_name' => 'الجواهري',
            'email' => 'seller@jawharah.com',
            'phone' => '966501111111',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'password' => Hash::make('password123'),
            'account_type' => 'seller',
            'is_approved' => true,
        ]);

        $seller->assignRole('seller');

        $this->command->info('Sample seller created!');
        $this->command->info('Email: seller@jawharah.com');

        // Create a sample buyer
        $buyer = User::create([
            'first_name' => 'محمد',
            'last_name' => 'العميل',
            'email' => 'buyer@jawharah.com',
            'phone' => '966502222222',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'password' => Hash::make('password123'),
            'account_type' => 'buyer',
            'is_approved' => true,
        ]);

        $buyer->assignRole('buyer');

        $this->command->info('Sample buyer created!');
        $this->command->info('Email: buyer@jawharah.com');
    }
}
