<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'buyer']);
    }

    /**
     * Test normal buyer cannot access admin users list.
     */
    public function test_non_admin_cannot_access_admin_endpoints(): void
    {
        $buyer = User::create([
            'first_name'   => 'Ali',
            'last_name'    => 'Ahmad',
            'email'        => 'ali@example.com',
            'phone'        => '966500000001',
            'password'     => 'Password123!',
            'account_type' => 'buyer',
            'is_approved'  => true,
            'is_active'    => true,
        ]);
        $buyer->assignRole('buyer');

        $response = $this->actingAs($buyer, 'sanctum')->getJson('/api/v1/admin/users');

        // Spatie's RoleMiddleware throws UnauthorizedException which Laravel translates to 403
        $response->assertStatus(403);
    }

    /**
     * Test admin can access admin users list.
     */
    public function test_admin_can_access_admin_endpoints(): void
    {
        $admin = User::create([
            'first_name'   => 'Admin',
            'last_name'    => 'User',
            'email'        => 'admin@example.com',
            'phone'        => '966500000002',
            'password'     => 'Password123!',
            'account_type' => 'buyer',
            'is_approved'  => true,
            'is_active'    => true,
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/admin/users');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }
}
