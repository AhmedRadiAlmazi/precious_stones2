<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProductAuctionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key constraints during seeding
        DB::statement('PRAGMA foreign_keys = OFF;');
        
        // Clean tables
        DB::table('bids')->truncate();
        DB::table('auctions')->truncate();
        DB::table('products')->truncate();

        // Re-enable foreign key constraints
        DB::statement('PRAGMA foreign_keys = ON;');

        // Find users
        $seller = User::where('email', 'seller@jawharah.com')->first();
        $buyer = User::where('email', 'buyer@jawharah.com')->first();
        $admin = User::where('email', 'admin@jawharah.com')->first();

        if (!$seller) {
            $seller = User::create([
                'first_name' => 'أحمد',
                'last_name' => 'الجواهري',
                'email' => 'seller@jawharah.com',
                'phone' => '966501111111',
                'password' => bcrypt('password123'),
                'account_type' => 'seller',
                'is_approved' => true,
            ]);
            $seller->assignRole('seller');
        }

        if (!$buyer) {
            $buyer = User::create([
                'first_name' => 'محمد',
                'last_name' => 'العميل',
                'email' => 'buyer@jawharah.com',
                'phone' => '966502222222',
                'password' => bcrypt('password123'),
                'account_type' => 'buyer',
                'is_approved' => true,
            ]);
            $buyer->assignRole('buyer');
        }

        if (!$admin) {
            $admin = User::create([
                'first_name' => 'Admin',
                'last_name' => 'System',
                'email' => 'admin@jawharah.com',
                'phone' => '966500000000',
                'password' => bcrypt('password123'),
                'account_type' => 'buyer',
                'is_approved' => true,
            ]);
            $admin->assignRole('admin');
        }

        // 1. Kashmir Sapphire
        $product1 = Product::create([
            'seller_id' => $seller->id,
            'category_id' => 4, // ياقوت أزرق
            'name' => 'ياقوت أزرق كشميري فاخر',
            'description' => 'ياقوت أزرق طبيعي نادر بوزن 4.25 قيراط من جبال كشمير العريقة، يتميز بوضوح ممتاز ولون ملكي فريد. حاصل على شهادة GIA لتوثيق الجودة والمنشأ الطبيعي الحصري.',
            'price' => 35000.00,
            'stock' => 1,
            'weight' => 4.25,
            'origin_country' => 'سريلانكا',
            'certification' => 'GIA-8734892',
            'images' => ['/imges/ياقوت أزرق نادر.jpeg'],
            'is_featured' => true,
            'is_active' => true,
        ]);

        // 2. Muzo Emerald
        $product2 = Product::create([
            'seller_id' => $seller->id,
            'category_id' => 3, // زمرد
            'name' => 'زمرد كولومبي نقي جداً',
            'description' => 'زمرد كولومبي طبيعي مستخرج من مناجم موزو الشهيرة. درجة لون خضراء مشبعة ونقاء نادر VVS1. حجر استثماري من الطراز الأول للمقتنيات النادرة.',
            'price' => 65000.00,
            'stock' => 1,
            'weight' => 3.80,
            'origin_country' => 'كولومبيا',
            'certification' => 'GIA-9823481',
            'images' => ['/imges/زمرد كولومبي نقي.jpg'],
            'is_featured' => true,
            'is_active' => true,
        ]);

        // 3. Yemeni Agate
        $product3 = Product::create([
            'seller_id' => $seller->id,
            'category_id' => 5, // عقيق
            'name' => 'عقيق يماني كبدي أصيل',
            'description' => 'عقيق يمني طبيعي 100% بلون كبدي غامق مميز وشفافية رائعة تحت الضوء. صياغة يدوية فاخرة تناسب المقتنيات النادرة والهدايا الفاخرة.',
            'price' => 4500.00,
            'stock' => 1,
            'weight' => 12.50,
            'origin_country' => 'اليمن',
            'certification' => 'IGI-384729',
            'images' => ['/imges/عقيق نادر من اليمن.jpeg'],
            'is_featured' => false,
            'is_active' => true,
        ]);

        // 4. Fancy Pink Diamond
        $product4 = Product::create([
            'seller_id' => $seller->id,
            'category_id' => 1, // ألماس
            'name' => 'ألماس وردي أفريقي نادر',
            'description' => 'ألماس طبيعي بلون وردي خيالي Fancy Pink بوزن 2.50 قيراط، درجة النقاء IF خالي تماماً من الشوائب الداخيلة والخارجية. حجر فريد واستثماري من الدرجة الأولى.',
            'price' => 185000.00,
            'stock' => 1,
            'weight' => 2.50,
            'origin_country' => 'أفريقيا',
            'certification' => 'GIA-2938472',
            'images' => ['/imges/ألماس وردي نادر.jpeg'],
            'is_featured' => true,
            'is_active' => true,
        ]);

        // Create Auctions
        $auction1 = Auction::create([
            'product_id' => $product1->id,
            'seller_id' => $seller->id,
            'starting_price' => 30000.00,
            'current_price' => 32500.00,
            'reserve_price' => 34000.00,
            'start_time' => now()->subDays(1),
            'end_time' => now()->addDays(3),
            'status' => 'active',
            'bid_increment' => 500.00,
            'total_bids' => 3,
        ]);

        $auction2 = Auction::create([
            'product_id' => $product2->id,
            'seller_id' => $seller->id,
            'starting_price' => 55000.00,
            'current_price' => 58000.00,
            'reserve_price' => 60000.00,
            'start_time' => now()->subDays(2),
            'end_time' => now()->addDays(5),
            'status' => 'active',
            'bid_increment' => 1000.00,
            'total_bids' => 2,
        ]);

        $auction3 = Auction::create([
            'product_id' => $product3->id,
            'seller_id' => $seller->id,
            'starting_price' => 3500.00,
            'current_price' => 4000.00,
            'reserve_price' => 4200.00,
            'start_time' => now()->subHours(12),
            'end_time' => now()->addHours(12),
            'status' => 'active',
            'bid_increment' => 200.00,
            'total_bids' => 3,
        ]);

        $auction4 = Auction::create([
            'product_id' => $product4->id,
            'seller_id' => $seller->id,
            'starting_price' => 150000.00,
            'current_price' => 165000.00,
            'reserve_price' => 180000.00,
            'start_time' => now()->subDays(3),
            'end_time' => now()->addDays(2),
            'status' => 'active',
            'bid_increment' => 5000.00,
            'total_bids' => 2,
        ]);

        // Create Bids
        // Auction 1 Bids
        Bid::create([
            'auction_id' => $auction1->id,
            'user_id' => $buyer->id,
            'amount' => 31000.00,
            'is_winning' => false,
            'created_at' => now()->subHours(18),
        ]);
        Bid::create([
            'auction_id' => $auction1->id,
            'user_id' => $admin->id,
            'amount' => 32000.00,
            'is_winning' => false,
            'created_at' => now()->subHours(12),
        ]);
        Bid::create([
            'auction_id' => $auction1->id,
            'user_id' => $buyer->id,
            'amount' => 32500.00,
            'is_winning' => true,
            'created_at' => now()->subHours(4),
        ]);

        // Auction 2 Bids
        Bid::create([
            'auction_id' => $auction2->id,
            'user_id' => $admin->id,
            'amount' => 56000.00,
            'is_winning' => false,
            'created_at' => now()->subDays(1),
        ]);
        Bid::create([
            'auction_id' => $auction2->id,
            'user_id' => $buyer->id,
            'amount' => 58000.00,
            'is_winning' => true,
            'created_at' => now()->subHours(2),
        ]);

        // Auction 3 Bids
        Bid::create([
            'auction_id' => $auction3->id,
            'user_id' => $buyer->id,
            'amount' => 3600.00,
            'is_winning' => false,
            'created_at' => now()->subHours(10),
        ]);
        Bid::create([
            'auction_id' => $auction3->id,
            'user_id' => $admin->id,
            'amount' => 3800.00,
            'is_winning' => false,
            'created_at' => now()->subHours(6),
        ]);
        Bid::create([
            'auction_id' => $auction3->id,
            'user_id' => $buyer->id,
            'amount' => 4000.00,
            'is_winning' => true,
            'created_at' => now()->subHours(1),
        ]);

        // Auction 4 Bids
        Bid::create([
            'auction_id' => $auction4->id,
            'user_id' => $buyer->id,
            'amount' => 160000.00,
            'is_winning' => false,
            'created_at' => now()->subDays(2),
        ]);
        Bid::create([
            'auction_id' => $auction4->id,
            'user_id' => $admin->id,
            'amount' => 165000.00,
            'is_winning' => true,
            'created_at' => now()->subDays(1),
        ]);

        $this->command->info('Gemstone products, active auctions, and bid history seeded successfully!');
    }
}
