<?php

use Illuminate\Support\Facades\Route;

// الصفحات العامة
Route::view('/', 'welcome');
Route::view('/shop', 'shop');
Route::view('/auctions', 'auction');
Route::view('/auction-details', 'auction-details');
Route::view('/login', 'auth.login');
Route::view('/register', 'auth.register');

// لوحة تحكم البائع
Route::prefix('seller')->group(function () {
    Route::view('/dashboard', 'seller.dashboard');
    Route::view('/products', 'seller.products');
    Route::view('/auctions', 'seller.auctions');
    Route::view('/orders', 'seller.orders');
    Route::view('/statistics', 'seller.statistics');
    Route::view('/earnings', 'seller.earnings');
    Route::view('/settings', 'seller.settings');
    Route::view('/add-product', 'seller.add-product');
    Route::view('/create-auction', 'seller.create-auction');
});

// لوحة تحكم المدير
Route::prefix('admin')->group(function () {
    Route::view('/dashboard', 'admin.dashboard');
    Route::view('/users', 'admin.users');
    Route::view('/sellers', 'admin.sellers');
    Route::view('/products', 'admin.products');
    Route::view('/auctions', 'admin.auctions');
    Route::view('/orders', 'admin.orders');
    Route::view('/categories', 'admin.categories');
    Route::view('/settings', 'admin.settings');
});
