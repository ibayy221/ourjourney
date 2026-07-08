<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\Admin\MemoryController;
use Illuminate\Support\Facades\Route;

// ─── Halaman Publik ───────────────────────────────────────────────────────────

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');

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

});

require __DIR__.'/auth.php';
