<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EscrowAndAntiSnipingTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $seller;
    private Auction $auction;
    private Product $product;

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
            'first_name'     => 'Ali',
            'last_name'      => 'Ahmad',
            'email'          => 'buyer@example.com',
            'phone'          => '966500000001',
            'password'       => 'Password123!',
            'account_type'   => 'buyer',
            'is_approved'    => true,
            'is_active'      => true,
            'wallet_balance' => 1000.00,
        ]);
        $this->buyer->assignRole('buyer');

        $this->seller = User::create([
            'first_name'     => 'Basem',
            'last_name'      => 'Omar',
            'email'          => 'seller@example.com',
            'phone'          => '966500000002',
            'password'       => 'Password123!',
            'account_type'   => 'seller',
            'is_approved'    => true,
            'is_active'      => true,
            'wallet_balance' => 0.00,
        ]);
        $this->seller->assignRole('seller');

        // Create category
        $category = Category::create([
            'name'        => 'Ruby',
            'slug'        => 'ruby',
            'description' => 'Ruby stones',
            'is_active'   => true,
        ]);

        // Create product
        $this->product = Product::create([
            'name'        => 'Ruby stone',
            'description' => 'Red ruby stone',
            'price'       => 5000,
            'stock'       => 1,
            'category_id' => $category->id,
            'seller_id'   => $this->seller->id,
            'is_active'   => true,
        ]);

        // Create active auction
        $this->auction = Auction::create([
            'product_id'     => $this->product->id,
            'seller_id'      => $this->seller->id,
            'starting_price' => 5000,
            'current_price'  => 5000,
            'status'         => 'active',
            'start_time'     => Carbon::now()->subHours(1),
            'end_time'       => Carbon::now()->addHours(5),
            'bid_increment'  => 100,
            'total_bids'     => 0,
        ]);
    }

    /**
     * Test Anti-Sniping triggers when a bid is placed in the last 60 seconds.
     */
    public function test_bid_in_last_60_seconds_extends_auction_duration(): void
    {
        // Change auction end time to 30 seconds from now
        $originalEndTime = Carbon::now()->addSeconds(30);
        $this->auction->update([
            'end_time' => $originalEndTime,
        ]);

        // Place a valid bid
        $response = $this->actingAs($this->buyer, 'sanctum')->postJson('/api/v1/bids', [
            'auction_id' => $this->auction->id,
            'amount'     => 5200,
        ]);

        $response->assertStatus(201);

        // Fetch fresh auction data
        $this->auction->refresh();

        // The end time should be extended by 120 seconds
        $expectedEndTime = $originalEndTime->addSeconds(120);
        
        $this->assertTrue(
            $this->auction->end_time->diffInSeconds($expectedEndTime) < 2,
            "Auction end time was not extended correctly. Expected: {$expectedEndTime->toIso8601String()}, Got: {$this->auction->end_time->toIso8601String()}"
        );
    }

    /**
     * Test the entire Escrow payment, shipping, delivery, and release cycle.
     */
    public function test_escrow_lifecycle_flows_correctly(): void
    {
        // 1. Create a pending order for the buyer
        $order = Order::create([
            'buyer_id'         => $this->buyer->id,
            'seller_id'        => $this->seller->id,
            'product_id'       => $this->product->id,
            'order_number'     => 'ORD-TEST-999',
            'total_amount'     => 400.00,
            'status'           => 'pending',
            'payment_method'   => 'pending',
            'payment_status'   => 'pending',
            'shipping_address' => ['street' => 'Main St', 'city' => 'Riyadh'],
        ]);

        // 2. Buyer pays with wallet
        $response = $this->actingAs($this->buyer, 'sanctum')->postJson("/api/v1/orders/{$order->id}/pay");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'paid')
            ->assertJsonPath('data.payment_method', 'wallet');

        $this->buyer->refresh();
        $this->assertEquals(600.00, $this->buyer->wallet_balance); // 1000 - 400 = 600

        // 3. Seller ships the order
        $response = $this->actingAs($this->seller, 'sanctum')->postJson("/api/v1/orders/{$order->id}/ship", [
            'tracking_number' => 'TRACK-123-XYZ',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'shipped')
            ->assertJsonPath('data.tracking_number', 'TRACK-123-XYZ');

        // 4. Buyer confirms delivery
        $response = $this->actingAs($this->buyer, 'sanctum')->postJson("/api/v1/orders/{$order->id}/deliver");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'delivered');

        // 5. Buyer releases funds to seller
        $response = $this->actingAs($this->buyer, 'sanctum')->postJson("/api/v1/orders/{$order->id}/release");

        $response->assertStatus(200)
            ->assertJsonPath('data.payment_status', 'completed');

        // Seller balance should be credited
        $this->seller->refresh();
        $this->assertEquals(400.00, $this->seller->wallet_balance);
    }
}
