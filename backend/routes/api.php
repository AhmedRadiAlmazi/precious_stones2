<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuctionController;
use App\Http\Controllers\Api\BidController;
use App\Http\Controllers\Api\OrderController;
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
    Route::get('/products/{id}', [ProductController::class, 'show']);
    
    // Auctions - Public
    Route::get('/auctions', [AuctionController::class, 'index']);
    Route::get('/auctions/{id}', [AuctionController::class, 'show']);
    Route::get('/auctions/{id}/bids', [BidController::class, 'auctionBids']);
    
    // Authentication Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
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

    // Orders - Buyer
    Route::post('/orders', [OrderController::class, 'store']);
    
    // Seller Routes
    Route::middleware('auth:sanctum')->group(function () {
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
    
    // Admin Routes
    Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
        // User Management
        Route::get('/users', [\App\Http\Controllers\Api\AdminController::class, 'getAllUsers']);
        Route::post('/users/{id}/toggle-status', [\App\Http\Controllers\Api\AdminController::class, 'toggleUserStatus']);
        Route::get('/sellers/pending', [\App\Http\Controllers\Api\AdminController::class, 'getPendingSellers']);
        Route::post('/sellers/{id}/approve', [\App\Http\Controllers\Api\AdminController::class, 'approveSeller']);
        Route::post('/sellers/{id}/reject', [\App\Http\Controllers\Api\AdminController::class, 'rejectSeller']);
        Route::get('/stats', [\App\Http\Controllers\Api\AdminController::class, 'getDashboardStats']);
        
        // Auction Management
        Route::get('/auctions/pending', [\App\Http\Controllers\Api\AdminController::class, 'getPendingAuctions']);
        Route::post('/auctions/{id}/approve', [\App\Http\Controllers\Api\AdminController::class, 'approveAuction']);
        Route::post('/auctions/{id}/reject', [\App\Http\Controllers\Api\AdminController::class, 'rejectAuction']);
        Route::get('/auctions', [\App\Http\Controllers\Api\AdminController::class, 'getAllAuctions']);
        Route::put('/auctions/{id}', [\App\Http\Controllers\Api\AdminController::class, 'updateAuction']);
        Route::delete('/auctions/{id}', [\App\Http\Controllers\Api\AdminController::class, 'deleteAuction']);
        Route::post('/auctions/{id}/end', [\App\Http\Controllers\Api\AdminController::class, 'endAuction']);

        // Product Management
        Route::get('/products', [\App\Http\Controllers\Api\AdminController::class, 'getAllProducts']);
        Route::put('/products/{id}', [\App\Http\Controllers\Api\AdminController::class, 'updateProduct']);
        Route::delete('/products/{id}', [\App\Http\Controllers\Api\AdminController::class, 'deleteProduct']);
        Route::post('/products/{id}/toggle-status', [\App\Http\Controllers\Api\AdminController::class, 'toggleProductStatus']);

        // Category Management
        Route::get('/categories', [\App\Http\Controllers\Api\AdminController::class, 'getAllCategories']);
        Route::post('/categories', [\App\Http\Controllers\Api\AdminController::class, 'createCategory']);
        Route::put('/categories/{id}', [\App\Http\Controllers\Api\AdminController::class, 'updateCategory']);
        Route::delete('/categories/{id}', [\App\Http\Controllers\Api\AdminController::class, 'deleteCategory']);
        Route::post('/categories/{id}/toggle-status', [\App\Http\Controllers\Api\AdminController::class, 'toggleCategoryStatus']);

        // Settings Management
        Route::get('/settings', [\App\Http\Controllers\Api\SettingsController::class, 'index']);
        Route::get('/settings/{key}', [\App\Http\Controllers\Api\SettingsController::class, 'show']);
        Route::put('/settings', [\App\Http\Controllers\Api\SettingsController::class, 'update']);
        Route::put('/settings/{key}', [\App\Http\Controllers\Api\SettingsController::class, 'updateSingle']);
    });

    // Seller Routes
    Route::middleware('auth:sanctum')->prefix('seller')->group(function () {
        Route::get('/orders', [\App\Http\Controllers\Api\SellerController::class, 'getOrders']);
        Route::put('/orders/{id}/status', [\App\Http\Controllers\Api\SellerController::class, 'updateOrderStatus']);
        Route::get('/statistics', [\App\Http\Controllers\Api\SellerController::class, 'getStatistics']);
        Route::get('/earnings', [\App\Http\Controllers\Api\SellerController::class, 'getEarnings']);
        Route::get('/profile', [\App\Http\Controllers\Api\SellerController::class, 'getProfile']);
        Route::put('/profile', [\App\Http\Controllers\Api\SellerController::class, 'updateProfile']);
        Route::put('/settings', [\App\Http\Controllers\Api\SellerController::class, 'updateSettings']);
    });
});
