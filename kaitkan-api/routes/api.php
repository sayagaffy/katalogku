<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\LinkGroupController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ClickController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication endpoints
Route::prefix('auth')->group(function () {
    Route::post('/send-otp', [AuthController::class, 'sendOTP']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOTP']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public catalog endpoints
Route::get('/c/{username}', [CatalogController::class, 'getPublicCatalog']);
Route::post('/clicks/{productId}', [ClickController::class, 'trackClick']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'getUser']);
    });

    // Profile routes (maps to Catalog)
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/onboarding', [ProfileController::class, 'onboarding']);
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);

    // Catalog routes
    Route::prefix('catalog')->group(function () {
        Route::get('/', [CatalogController::class, 'show']);
        Route::post('/', [CatalogController::class, 'store']);
    });

    // Theme routes
    Route::get('/themes', [ThemeController::class, 'index']);

    // Link routes
    Route::prefix('links')->group(function () {
        Route::get('/', [LinkController::class, 'index']);
        Route::post('/', [LinkController::class, 'store']);
        Route::patch('/{id}', [LinkController::class, 'update']);
        Route::patch('/reorder', [LinkController::class, 'reorder']);
        Route::delete('/{id}', [LinkController::class, 'destroy']);
    });

    // Link group routes
    Route::prefix('link-groups')->group(function () {
        Route::get('/', [LinkGroupController::class, 'index']);
        Route::post('/', [LinkGroupController::class, 'store']);
        Route::patch('/{id}', [LinkGroupController::class, 'update']);
        Route::delete('/{id}', [LinkGroupController::class, 'destroy']);
    });

    // Product routes
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::post('/{id}', [ProductController::class, 'update']); // Using POST for multipart/form-data
        Route::delete('/{id}', [ProductController::class, 'destroy']);
        Route::post('/reorder', [ProductController::class, 'reorder']);
    });

    // Analytics routes
    Route::get('/analytics', [ClickController::class, 'getAnalytics']);
});
