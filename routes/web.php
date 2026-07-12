<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\Admin\MemoryController;
use App\Http\Controllers\Admin\WishlistController;
use Illuminate\Support\Facades\Route;

// ─── Halaman Publik ───────────────────────────────────────────────────────────

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
Route::get('/cinema',  [GalleryController::class, 'cinema'])->name('cinema');

// ─── Admin Panel (dilindungi middleware auth) ──────────────────────────────────

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {

    // Redirect /admin ke /admin/memories
    Route::redirect('/', '/admin/memories');

    // CRUD Memories
    Route::get('/memories',                    [MemoryController::class, 'index'])  ->name('memories.index');
    Route::get('/memories/create',             [MemoryController::class, 'create']) ->name('memories.create');
    Route::post('/memories',                   [MemoryController::class, 'store'])  ->name('memories.store');
    Route::post('/memories/reorder',           [MemoryController::class, 'reorder'])->name('memories.reorder');
    Route::get('/memories/{memory}/edit',      [MemoryController::class, 'edit'])   ->name('memories.edit');
    Route::put('/memories/{memory}',           [MemoryController::class, 'update']) ->name('memories.update');
    Route::delete('/memories/{memory}',        [MemoryController::class, 'destroy'])->name('memories.destroy');

    // CRUD Wishlist (Daftar Impian)
    Route::get('/wishlists',                    [WishlistController::class, 'index'])  ->name('wishlists.index');
    Route::get('/wishlists/create',             [WishlistController::class, 'create']) ->name('wishlists.create');
    Route::post('/wishlists',                   [WishlistController::class, 'store'])  ->name('wishlists.store');
    Route::post('/wishlists/reorder',           [WishlistController::class, 'reorder'])->name('wishlists.reorder');
    Route::get('/wishlists/{wishlist}/edit',    [WishlistController::class, 'edit'])   ->name('wishlists.edit');
    Route::put('/wishlists/{wishlist}',         [WishlistController::class, 'update']) ->name('wishlists.update');
    Route::delete('/wishlists/{wishlist}',      [WishlistController::class, 'destroy'])->name('wishlists.destroy');
    Route::post('/wishlists/{wishlist}/toggle', [WishlistController::class, 'toggle']) ->name('wishlists.toggle');

});

require __DIR__.'/auth.php';
