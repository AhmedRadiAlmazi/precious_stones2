<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BidTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $seller;
    private Auction $auction;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $placeBidsPermission = Permission::create(['name' => 'place-bids']);
        
        $buyerRole = Role::create(['name' => 'buyer']);
        $buyerRole->givePermissionTo($placeBidsPermission);

        $sellerRole = Role::create(['name' => 'seller']);
        $sellerRole->givePermissionTo($placeBidsPermission);

        // Create buyer & seller users
        $this->buyer = User::create([
            'first_name'   => 'Ali',
            'last_name'    => 'Ahmad',
            'email'        => 'buyer@example.com',
            'phone'        => '966500000001',
            'password'     => 'Password123!',
            'account_type' => 'buyer',
            'is_approved'  => true,
            'is_active'    => true,
        ]);
        $this->buyer->assignRole('buyer');

        $this->seller = User::create([
            'first_name'   => 'Basem',
            'last_name'    => 'Omar',
            'email'        => 'seller@example.com',
            'phone'        => '966500000002',
            'password'     => 'Password123!',
            'account_type' => 'seller',
            'is_approved'  => true,
            'is_active'    => true,
        ]);
        $this->seller->assignRole('seller');

        // Create category
        $category = Category::create([
            'name' => 'Ruby',
            'slug' => 'ruby',
            'description' => 'Ruby stones',
            'is_active' => true,
        ]);

        // Create product
        $product = Product::create([
            'name' => 'Ruby stone',
            'description' => 'Red ruby stone',
            'price' => 5000,
            'stock' => 1,
            'category_id' => $category->id,
            'seller_id' => $this->seller->id,
            'is_active' => true,
        ]);

        // Create active auction
        $this->auction = Auction::create([
            'product_id' => $product->id,
            'seller_id' => $this->seller->id,
            'starting_price' => 5000,
            'current_price' => 5000,
            'status' => 'active',
            'start_time' => Carbon::now()->subHours(1),
            'end_time' => Carbon::now()->addHours(5),
            'bid_increment' => 100,
            'total_bids' => 0,
        ]);
    }

    /**
     * Test successful bid placement.
     */
    public function test_can_place_valid_bid(): void
    {
        $response = $this->actingAs($this->buyer, 'sanctum')->postJson('/api/v1/bids', [
            'auction_id' => $this->auction->id,
            'amount' => 5200, // starting is 5000, increment is 100, so 5200 is valid
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('bids', [
            'auction_id' => $this->auction->id,
            'user_id' => $this->buyer->id,
            'amount' => 5200,
            'is_winning' => true,
        ]);

        $this->assertDatabaseHas('auctions', [
            'id' => $this->auction->id,
            'current_price' => 5200,
            'total_bids' => 1,
        ]);
    }

    /**
     * Test bid below minimum increment is rejected.
     */
    public function test_cannot_place_bid_below_increment(): void
    {
        $response = $this->actingAs($this->buyer, 'sanctum')->postJson('/api/v1/bids', [
            'auction_id' => $this->auction->id,
            'amount' => 5050, // starting is 5000, increment is 100, so min is 5100. 5050 should fail.
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test seller cannot bid on their own auction.
     */
    public function test_seller_cannot_bid_on_own_auction(): void
    {
        $response = $this->actingAs($this->seller, 'sanctum')->postJson('/api/v1/bids', [
            'auction_id' => $this->auction->id,
            'amount' => 5200,
        ]);

        $response->assertStatus(403);
    }
}
