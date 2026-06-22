<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuctionController;
use App\Http\Controllers\Api\BidController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\EscrowController;
use App\Http\Controllers\Api\AiValuationController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminProductController;
use App\Http\Controllers\Api\Admin\AdminAuctionController;
use App\Http\Controllers\Api\Admin\AdminCategoryController;
use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Routes (No Authentication Required)
Route::prefix('v1')->group(function () {
    
    // Products - Public
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/recommendations', [AiValuationController::class, 'recommendations']);
    Route::get('/products/{id}/estimate', [AiValuationController::class, 'estimate']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    
    // Auctions - Public
    Route::get('/auctions', [AuctionController::class, 'index']);
    Route::get('/auctions/{id}', [AuctionController::class, 'show']);
    Route::get('/auctions/{id}/bids', [BidController::class, 'auctionBids']);
    
    // Authentication Routes with Rate Limiting (10 requests per minute)
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });
});

// Protected Routes (Authentication Required)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Bids - Buyer & Seller
    Route::post('/bids', [BidController::class, 'store'])
        ->middleware('permission:place-bids');
    Route::get('/my-bids', [BidController::class, 'myBids']);

    // Orders - Buyer & Escrow
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/{id}/pay', [EscrowController::class, 'payWithWallet']);
    Route::post('/orders/{id}/ship', [EscrowController::class, 'shipOrder']);
    Route::post('/orders/{id}/deliver', [EscrowController::class, 'deliverOrder']);
    Route::post('/orders/{id}/release', [EscrowController::class, 'releaseFunds']);
    
    // Seller - Products & Auctions Management
    Route::middleware('role:seller')->group(function () {
        // Products
        Route::post('/products', [ProductController::class, 'store'])
            ->middleware('permission:create-products');
        Route::put('/products/{id}', [ProductController::class, 'update'])
            ->middleware('permission:edit-own-products');
        Route::delete('/products/{id}', [ProductController::class, 'destroy'])
            ->middleware('permission:delete-own-products');
        Route::get('/my-products', [ProductController::class, 'myProducts']);
        
        // Auctions
        Route::post('/auctions', [AuctionController::class, 'store'])
            ->middleware('permission:create-auctions');
        Route::put('/auctions/{id}', [AuctionController::class, 'update'])
            ->middleware('permission:edit-own-auctions');
        Route::delete('/auctions/{id}', [AuctionController::class, 'destroy']);
        Route::get('/my-auctions', [AuctionController::class, 'myAuctions']);
    });
    
    // Admin Routes (Restricted to admin role)
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // User Management
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::put('/users/{id}', [AdminUserController::class, 'update']);
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
        Route::post('/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus']);
        Route::get('/sellers/pending', [AdminUserController::class, 'pendingSellers']);
        Route::post('/sellers/{id}/approve', [AdminUserController::class, 'approveSeller']);
        Route::post('/sellers/{id}/reject', [AdminUserController::class, 'rejectSeller']);
        Route::get('/stats', [AdminDashboardController::class, 'getDashboardStats']);
        
        // Auction Management
        Route::get('/auctions/pending', [AdminAuctionController::class, 'pending']);
        Route::post('/auctions/{id}/approve', [AdminAuctionController::class, 'approve']);
        Route::post('/auctions/{id}/reject', [AdminAuctionController::class, 'reject']);
        Route::get('/auctions', [AdminAuctionController::class, 'index']);
        Route::put('/auctions/{id}', [AdminAuctionController::class, 'update']);
        Route::delete('/auctions/{id}', [AdminAuctionController::class, 'destroy']);
        Route::post('/auctions/{id}/end', [AdminAuctionController::class, 'end']);

        // Product Management
        Route::get('/products', [AdminProductController::class, 'index']);
        Route::put('/products/{id}', [AdminProductController::class, 'update']);
        Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);
        Route::post('/products/{id}/toggle-status', [AdminProductController::class, 'toggleStatus']);
        
        // Order Management
        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::put('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);

        // Category Management
        Route::get('/categories', [AdminCategoryController::class, 'index']);
        Route::post('/categories', [AdminCategoryController::class, 'store']);
        Route::put('/categories/{id}', [AdminCategoryController::class, 'update']);
        Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy']);
        Route::post('/categories/{id}/toggle-status', [AdminCategoryController::class, 'toggleStatus']);

        // Settings Management
        Route::get('/settings', [SettingsController::class, 'index']);
        Route::get('/settings/{key}', [SettingsController::class, 'show']);
        Route::put('/settings', [SettingsController::class, 'update']);
        Route::put('/settings/{key}', [SettingsController::class, 'updateSingle']);
    });

    // Seller Specific Routes (Restricted to seller role)
    Route::middleware('role:seller')->prefix('seller')->group(function () {
        Route::get('/orders', [SellerController::class, 'getOrders']);
        Route::put('/orders/{id}/status', [SellerController::class, 'updateOrderStatus']);
        Route::get('/statistics', [SellerController::class, 'getStatistics']);
        Route::get('/earnings', [SellerController::class, 'getEarnings']);
        Route::get('/profile', [SellerController::class, 'getProfile']);
        Route::put('/profile', [SellerController::class, 'updateProfile']);
        Route::put('/settings', [SellerController::class, 'updateSettings']);
    });
});

