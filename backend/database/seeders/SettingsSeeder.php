<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            ['key' => 'site_name', 'value' => 'جوهرة - منصة مزادات الأحجار الكريمة', 'type' => 'string', 'group' => 'general'],
            ['key' => 'site_email', 'value' => 'info@jawharah.com', 'type' => 'string', 'group' => 'general'],
            ['key' => 'site_phone', 'value' => '+967 775497454', 'type' => 'string', 'group' => 'general'],
            ['key' => 'site_currency', 'value' => 'SAR', 'type' => 'string', 'group' => 'general'],
            ['key' => 'site_description', 'value' => 'منصة رائدة لمزادات الأحجار الكريمة والمجوهرات الفاخرة فيالجمهورية اليمنية ', 'type' => 'string', 'group' => 'general'],
            ['key' => 'site_maintenance', 'value' => '0', 'type' => 'boolean', 'group' => 'general'],

            // Auctions Settings
            ['key' => 'default_auction_duration', 'value' => '7', 'type' => 'integer', 'group' => 'auctions'],
            ['key' => 'min_bid_increment', 'value' => '5', 'type' => 'integer', 'group' => 'auctions'],
            ['key' => 'platform_commission', 'value' => '10', 'type' => 'integer', 'group' => 'auctions'],
            ['key' => 'min_starting_price', 'value' => '100', 'type' => 'integer', 'group' => 'auctions'],
            ['key' => 'auto_approve_auctions', 'value' => '0', 'type' => 'boolean', 'group' => 'auctions'],
            ['key' => 'allow_auction_extension', 'value' => '1', 'type' => 'boolean', 'group' => 'auctions'],
            ['key' => 'require_seller_approval', 'value' => '1', 'type' => 'boolean', 'group' => 'auctions'],

            // Email Settings
            ['key' => 'smtp_host', 'value' => 'smtp.gmail.com', 'type' => 'string', 'group' => 'email'],
            ['key' => 'smtp_port', 'value' => '587', 'type' => 'integer', 'group' => 'email'],
            ['key' => 'smtp_username', 'value' => 'noreply@jawharah.com', 'type' => 'string', 'group' => 'email'],
            ['key' => 'smtp_password', 'value' => '', 'type' => 'string', 'group' => 'email'],
            ['key' => 'smtp_encryption', 'value' => 'tls', 'type' => 'string', 'group' => 'email'],
            ['key' => 'send_welcome_email', 'value' => '1', 'type' => 'boolean', 'group' => 'email'],
            ['key' => 'send_auction_notifications', 'value' => '1', 'type' => 'boolean', 'group' => 'email'],
            ['key' => 'send_bid_notifications', 'value' => '1', 'type' => 'boolean', 'group' => 'email'],

            // Payment Settings
            ['key' => 'payment_gateway', 'value' => 'stripe', 'type' => 'string', 'group' => 'payment'],
            ['key' => 'payment_mode', 'value' => 'test', 'type' => 'string', 'group' => 'payment'],
            ['key' => 'payment_public_key', 'value' => '', 'type' => 'string', 'group' => 'payment'],
            ['key' => 'payment_secret_key', 'value' => '', 'type' => 'string', 'group' => 'payment'],
            ['key' => 'accept_credit_cards', 'value' => '1', 'type' => 'boolean', 'group' => 'payment'],
            ['key' => 'accept_apple_pay', 'value' => '0', 'type' => 'boolean', 'group' => 'payment'],
            ['key' => 'accept_mada', 'value' => '1', 'type' => 'boolean', 'group' => 'payment'],

            // Security Settings
            ['key' => 'max_login_attempts', 'value' => '5', 'type' => 'integer', 'group' => 'security'],
            ['key' => 'lockout_duration', 'value' => '30', 'type' => 'integer', 'group' => 'security'],
            ['key' => 'min_password_length', 'value' => '8', 'type' => 'integer', 'group' => 'security'],
            ['key' => 'session_lifetime', 'value' => '120', 'type' => 'integer', 'group' => 'security'],
            ['key' => 'require_email_verification', 'value' => '1', 'type' => 'boolean', 'group' => 'security'],
            ['key' => 'enable_two_factor', 'value' => '0', 'type' => 'boolean', 'group' => 'security'],
            ['key' => 'require_strong_password', 'value' => '1', 'type' => 'boolean', 'group' => 'security'],
            ['key' => 'enable_recaptcha', 'value' => '0', 'type' => 'boolean', 'group' => 'security'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
