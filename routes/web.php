<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BookImportExportController;
use App\Http\Controllers\Admin\OrderExportController;
use App\Http\Controllers\Customer\InvoiceController;
use App\Http\Controllers\Admin\UserImportExportController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController; //already have "DashboardController" and needs another name

// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// Dashboard (from Breeze)
Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('admin.dashboard');

    Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/my-dashboard', [CustomerDashboardController::class, 'index'])
    ->name('customer.dashboard');
});

// Profile routes (from Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin category routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});

// Admin Imports Route
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/books/import', [BookImportExportController::class, 'showImportForm'])->name('admin.books.import');
    Route::post('/admin/books/import', [BookImportExportController::class, 'import'])->name('admin.books.import.store');
    Route::get('/admin/books/import/template', [BookImportExportController::class, 'downloadTemplate'])->name('admin.books.import.template');
    Route::get('/admin/books/export', [BookImportExportController::class, 'showExportForm'])->name('admin.books.export');
    Route::post('/admin/books/export', [BookImportExportController::class, 'export'])->name('admin.books.export.download');
// Admin Orders Route
    Route::get('/admin/orders/export', [OrderExportController::class, 'showExportForm'])->name('admin.orders.export');
    Route::post('/admin/orders/export', [OrderExportController::class, 'export'])->name('admin.orders.export.download');
    Route::get('/admin/orders/revenue', [OrderExportController::class, 'showRevenueForm'])->name('admin.orders.revenue');
    Route::post('/admin/orders/revenue', [OrderExportController::class, 'exportRevenue'])->name('admin.orders.revenue.download');
// Admin User Import and Export
Route::get('/admin/users/import', [UserImportExportController::class, 'showImportForm'])->name('admin.users.import');
Route::post('/admin/users/import', [UserImportExportController::class, 'import'])->name('admin.users.import.store');
Route::get('/admin/users/import/template', [UserImportExportController::class, 'downloadTemplate'])->name('admin.users.import.template');
Route::get('/admin/users/export', [UserImportExportController::class, 'showExportForm'])->name('admin.users.export');
Route::post('/admin/users/export', [UserImportExportController::class, 'export'])->name('admin.users.export.download');

// Backup
Route::get('/admin/backup', [BackupController::class, 'index'])->name('admin.backup.index');
Route::post('/admin/backup/run', [BackupController::class, 'run'])->name('admin.backup.run');
Route::post('/admin/backup/clean', [BackupController::class, 'clean'])->name('admin.backup.clean');
Route::get('/admin/backup/download/{filename}', [BackupController::class, 'download'])->name('admin.backup.download');
});

// Public category routes
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

// Admin book routes 
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::patch('/admin/users/{user}/verify', [AdminUserController::class, 'verify'])->name('admin.users.verify');
    Route::delete('/admin/users/{user}/deactivate', [AdminUserController::class, 'deactivate'])->name('admin.users.deactivate');
    Route::patch('/admin/users/{id}/restore', [AdminUserController::class, 'restore'])->name('admin.users.restore');

    
});

// Notifications
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy');
    Route::delete('/notifications', [NotificationController::class, 'clearAll'])
        ->name('notifications.clear');
});

// Public book routes
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

// Order routes (require authentication)
Route::middleware('auth', 'verified', 'two-factor')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// Admin order status update 
Route::middleware(['auth', 'admin'])->group(function () {
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
});

// Review routes (require authentication)
Route::middleware('auth', 'verified', 'two-factor')->group(function () {
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

// Shopping Cart routes (customers only)
Route::middleware('auth', 'verified', "two-factor")->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{book}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update/{bookId}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{bookId}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
});

// Addresses (Customer Only)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/addresses', [\App\Http\Controllers\Customer\AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [\App\Http\Controllers\Customer\AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [\App\Http\Controllers\Customer\AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [\App\Http\Controllers\Customer\AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::patch('/addresses/{address}/default', [\App\Http\Controllers\Customer\AddressController::class, 'setDefault'])->name('addresses.default');
// Invoice
    Route::get('/orders/{order}/invoice', [InvoiceController::class, 'download'])->name('orders.invoice');
});

require __DIR__.'/auth.php';