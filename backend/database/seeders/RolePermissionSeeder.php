<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // Admin Permissions
            'manage-users',
            'manage-roles',
            'manage-products',
            'manage-auctions',
            'approve-sellers',
            'view-reports',
            'manage-categories',
            'manage-settings',
            
            // Seller Permissions
            'create-products',
            'edit-own-products',
            'delete-own-products',
            'create-auctions',
            'edit-own-auctions',
            'view-own-sales',
            'manage-inventory',
            'respond-to-reviews',
            
            // Buyer Permissions
            'place-bids',
            'purchase-products',
            'add-to-cart',
            'add-to-favorites',
            'view-own-orders',
            'write-reviews',
            'track-shipments',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Roles and Assign Permissions

        // Admin Role - Full Access
        $adminRole = Role::create(['name' => 'admin']);
        $adminPermissions = Permission::whereIn('name', [
            'manage-users',
            'manage-roles',
            'manage-products',
            'manage-auctions',
            'approve-sellers',
            'view-reports',
            'manage-categories',
            'manage-settings',
        ])->get();
        $adminRole->syncPermissions($adminPermissions);

        // Seller Role
        $sellerRole = Role::create(['name' => 'seller']);
        $sellerPermissions = Permission::whereIn('name', [
            'create-products',
            'edit-own-products',
            'delete-own-products',
            'create-auctions',
            'edit-own-auctions',
            'view-own-sales',
            'manage-inventory',
            'respond-to-reviews',
            // Sellers can also be buyers
            'place-bids',
            'purchase-products',
            'add-to-cart',
            'add-to-favorites',
            'view-own-orders',
            'write-reviews',
            'track-shipments',
        ])->get();
        $sellerRole->syncPermissions($sellerPermissions);

        // Buyer Role
        $buyerRole = Role::create(['name' => 'buyer']);
        $buyerPermissions = Permission::whereIn('name', [
            'place-bids',
            'purchase-products',
            'add-to-cart',
            'add-to-favorites',
            'view-own-orders',
            'write-reviews',
            'track-shipments',
        ])->get();
        $buyerRole->syncPermissions($buyerPermissions);

        $this->command->info('Roles and Permissions created successfully!');
        $this->command->info('- Admin role with 8 permissions');
        $this->command->info('- Seller role with 15 permissions');
        $this->command->info('- Buyer role with 7 permissions');
    }
}
