<?php

use Illuminate\Support\Facades\DB;
use App\Models\Auction;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Stabilizing Auction Data...\n";

// 1. Clear existing auctions to remove 'random' noise
echo "Clearing existing auctions...\n";
use Illuminate\Support\Facades\Schema;

// 1. Clear existing auctions
echo "Clearing existing auctions...\n";
Schema::disableForeignKeyConstraints();
Auction::truncate();
Schema::enableForeignKeyConstraints();

// 2. Ensure we have users and products
$seller = User::where('account_type', 'seller')->first();
if (!$seller) {
    $seller = User::factory()->create(['account_type' => 'seller', 'name' => 'Demo Seller']);
}

// Get or create products
$products = Product::limit(10)->get();
if ($products->isEmpty()) {
    echo "No products found. Please seed products first or run standard seeder.\n";
    exit;
}

// 3. Create Specific Auctions for Demo

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

echo "Created 5 stable auctions (3 Active, 2 Expired).\n";
echo "Done.\n";
