<?php

use App\Http\Controllers\Api\BookApiController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\OrderApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ── Lab 6 — PUBLIC routes (30 req/min) ───────────────────────────────────
Route::middleware(['throttle:public'])->group(function () {
    Route::get('/books',                    [BookApiController::class, 'index']);
    Route::get('/books/{book}',             [BookApiController::class, 'show']);
    Route::get('/categories',               [CategoryApiController::class, 'index']);
    Route::get('/categories/{category}',    [CategoryApiController::class, 'show']);
});

// ── Lab 6 — AUTHENTICATED routes ─────────────────────────────────────────
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/orders',           [OrderApiController::class, 'index']);
    Route::get('/orders/{order}',   [OrderApiController::class, 'show']);
});

// ── Lab 6 — ADMIN routes ──────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/admin/books',             [BookApiController::class, 'store']);
    Route::put('/admin/books/{book}',       [BookApiController::class, 'update']);
    Route::delete('/admin/books/{book}',    [BookApiController::class, 'destroy']);
});

// ── Lab 7 — Optimized Book catalog routes ────────────────────────────────
Route::prefix('books')->name('api.books.')->group(function () {
    Route::get('/',                         [BookController::class, 'index'])->name('index');
    Route::get('/search',                   [BookController::class, 'search'])->name('search');
    Route::get('/category/{categoryId}',    [BookController::class, 'byCategory'])->name('byCategory');
    Route::get('/{book}',                   [BookController::class, 'show'])->name('show');
});