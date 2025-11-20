<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;

// ========================================================
// 🔓 PUBLIC ROUTES (TIDAK PERLU LOGIN)
// ========================================================
Route::post('/register', [AuthController::class, 'apiRegister'])->name('api.register');
Route::post('/login', [AuthController::class, 'apiLogin'])->name('api.login');

// 🔹 Produk bisa diakses tanpa token
Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('api.products.show');

// ========================================================
// 🔒 PROTECTED ROUTES (HARUS LOGIN DENGAN TOKEN SANCTUM)
// ========================================================
Route::middleware('auth:sanctum')->group(function () {

    // ================== AUTH ==================
    Route::post('/logout', [AuthController::class, 'apiLogout'])->name('api.logout');

    // ================== CART ==================
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'store']); // ✅ ROUTE INI PENTING
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);


    // ================== PROFILE ==================
    Route::get('/profile', [ProfileController::class, 'show'])->name('api.profile.show');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('api.profile.update');
    
    // ================== CHECKOUT & PAYMENT ==================
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('api.checkout.store');
    Route::get('/checkout/history', [CheckoutController::class, 'history'])->name('api.checkout.history');

    Route::post('/payment', [PaymentController::class, 'processPayment'])->name('api.payment.process');
    Route::get('/payment/status/{orderId}', [PaymentController::class, 'checkStatus'])->name('api.payment.status');
});

// ========================================================
// 🚫 HANDLE ROUTE TIDAK DITEMUKAN
// ========================================================
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint tidak ditemukan. Periksa URL API Anda.',
    ], 404);

    
});