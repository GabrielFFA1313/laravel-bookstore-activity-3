<?php

use App\Http\Controllers\Api\BookApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\OrderApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes are prefixed with /api automatically by bootstrap/app.php
*/

// ── PUBLIC routes (30 req/min) ────────────────────────────────────────────
Route::middleware(['throttle:public'])->group(function () {
    Route::get('/books', [BookApiController::class, 'index']);
    Route::get('/books/{book}', [BookApiController::class, 'show']);
    Route::get('/categories', [CategoryApiController::class, 'index']);
    Route::get('/categories/{category}', [CategoryApiController::class, 'show']);
});

// ── AUTHENTICATED routes (60/300/1000 req/min based on role) ─────────────
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/orders', [OrderApiController::class, 'index']);
    Route::get('/orders/{order}', [OrderApiController::class, 'show']);
});

// ── ADMIN routes (1000 req/min) ───────────────────────────────────────────
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/admin/books', [BookApiController::class, 'adminIndex']);
    Route::post('/admin/books', [BookApiController::class, 'store']);
    Route::put('/admin/books/{book}', [BookApiController::class, 'update']);
    Route::delete('/admin/books/{book}', [BookApiController::class, 'destroy']);
});