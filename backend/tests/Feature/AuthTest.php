<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles needed for the tests
        Role::create(['name' => 'buyer']);
        Role::create(['name' => 'seller']);
    }

    /**
     * Test buyer registration.
     */
    public function test_buyer_can_register_and_get_token(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'first_name'   => 'Ali',
            'last_name'    => 'Ahmad',
            'email'        => 'ali@example.com',
            'phone'        => '966500000001',
            'password'     => 'Password123!',
            'password_confirmation' => 'Password123!',
            'account_type' => 'buyer',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'needs_approval' => false,
                ]
            ]);

        $this->assertNotNull($response->json('data.token'));
        $this->assertDatabaseHas('users', [
            'email' => 'ali@example.com',
            'account_type' => 'buyer',
            'is_approved' => true,
        ]);
    }

    /**
     * Test seller registration.
     */
    public function test_seller_register_needs_approval_and_gets_no_token(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'first_name'   => 'Basem',
            'last_name'    => 'Omar',
            'email'        => 'basem@example.com',
            'phone'        => '966500000002',
            'password'     => 'Password123!',
            'password_confirmation' => 'Password123!',
            'account_type' => 'seller',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'needs_approval' => true,
                    'token' => null,
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'basem@example.com',
            'account_type' => 'seller',
            'is_approved' => false,
        ]);
    }

    /**
     * Test login for approved user.
     */
    public function test_approved_user_can_login(): void
    {
        $user = User::create([
            'first_name'   => 'Saleh',
            'last_name'    => 'Hassan',
            'email'        => 'saleh@example.com',
            'phone'        => '966500000003',
            'password'     => 'Password123!',
            'account_type' => 'buyer',
            'is_approved'  => true,
            'is_active'    => true,
        ]);
        $user->assignRole('buyer');

        $response = $this->postJson('/api/v1/login', [
            'email'    => 'saleh@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertNotNull($response->json('data.token'));
    }

    /**
     * Test login for unapproved seller.
     */
    public function test_unapproved_seller_cannot_login(): void
    {
        $user = User::create([
            'first_name'   => 'Fahad',
            'last_name'    => 'Khaled',
            'email'        => 'fahad@example.com',
            'phone'        => '966500000004',
            'password'     => 'Password123!',
            'account_type' => 'seller',
            'is_approved'  => false,
            'is_active'    => true,
        ]);
        $user->assignRole('seller');

        $response = $this->postJson('/api/v1/login', [
            'email'    => 'fahad@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }
}
