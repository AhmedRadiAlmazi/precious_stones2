<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Auction;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class StableAuctionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing auctions
        Schema::disableForeignKeyConstraints();
        Auction::truncate();
        Schema::enableForeignKeyConstraints();

        // Ensure we have a seller user
        $seller = User::where('account_type', 'seller')->first();
        if (!$seller) {
            $seller = User::factory()->create([
                'first_name' => 'Demo',
                'last_name' => 'Seller',
                'email' => 'seller@jawhara.com',
                'phone' => '123456789',
                'account_type' => 'seller',
                'is_approved' => true
            ]);
            $seller->assignRole('seller');
        }

        // Get products
        $products = Product::limit(10)->get();
        if ($products->isEmpty()) {
            return;
        }

        // Create Specific Auctions for Demo
        // Auction 1: Active, Ends in 2 Days (days counter)
        Auction::create([
            'product_id' => $products[0]->id,
            'seller_id' => $seller->id,
            'starting_price' => 5000,
            'current_price' => 5500,
            'status' => 'active',
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->addDays(2)->addHours(4)->addMinutes(30),
            'bid_increment' => 100,
        ]);

        // Auction 2: Active, Ends in 4 Hours (hours counter)
        Auction::create([
            'product_id' => $products[1]->id ?? $products[0]->id,
            'seller_id' => $seller->id,
            'starting_price' => 12000,
            'current_price' => 12500,
            'status' => 'active',
            'start_time' => Carbon::now()->subHours(2),
            'end_time' => Carbon::now()->addHours(3)->addMinutes(15),
            'bid_increment' => 200,
        ]);

        // Auction 3: Active, Ends in 15 Minutes (minutes counter, urgent)
        Auction::create([
            'product_id' => $products[2]->id ?? $products[0]->id,
            'seller_id' => $seller->id,
            'starting_price' => 800,
            'current_price' => 950,
            'status' => 'active',
            'start_time' => Carbon::now()->subHours(1),
            'end_time' => Carbon::now()->addMinutes(15)->addSeconds(30),
            'bid_increment' => 50,
        ]);

        // Auction 4: Expired recently (should show as Finished)
        Auction::create([
            'product_id' => $products[3]->id ?? $products[0]->id,
            'seller_id' => $seller->id,
            'starting_price' => 3000,
            'current_price' => 3000,
            'status' => 'active', // Intentionally 'active' status but past time, to test expiry logic
            'start_time' => Carbon::now()->subDays(2),
            'end_time' => Carbon::now()->subMinutes(30),
            'bid_increment' => 100,
        ]);

        // Auction 5: Expired long ago (should be filtered out by limit if logic works, or show last)
        Auction::create([
            'product_id' => $products[4]->id ?? $products[0]->id,
            'seller_id' => $seller->id,
            'starting_price' => 1000,
            'current_price' => 1000,
            'status' => 'active',
            'start_time' => Carbon::now()->subDays(10),
            'end_time' => Carbon::now()->subDays(5),
            'bid_increment' => 100,
        ]);
    }
}
