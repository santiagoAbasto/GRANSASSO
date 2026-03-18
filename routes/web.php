<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\EmpresaController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\NewsletterController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\GearboxController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| WEB PÚBLICA (BLADE)
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/empresa', [EmpresaController::class, 'index'])->name('empresa');

// Productos
Route::prefix('productos')->name('products.')->group(function () {
    Route::get('/',                           [ProductController::class, 'index'])->name('index');
    Route::get('/buscar',                     [ProductController::class, 'search'])->name('search');
    Route::get('/{brand:slug}',               [ProductController::class, 'brand'])->name('brand');
    Route::get('/{brand:slug}/{gearbox:slug}', [ProductController::class, 'gearbox'])->name('gearbox');
    Route::get('/{brand:slug}/{gearbox:slug}/{product}', [ProductController::class, 'show'])->name('show');
});

// Stubs — se desarrollan en iteraciones posteriores
Route::view('/calidad',   'web.about.index')->name('calidad');
Route::view('/contacto',  'web.contact.index')->name('contact.index');

// Newsletter
Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store');

/*
|--------------------------------------------------------------------------
| ADMIN PANEL (INERTIA + REACT)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    // ── Autenticado ────────────────────────────────────────────────────
    Route::middleware(['auth', 'verified'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Profile
        Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Marcas
        Route::get('/brands',           [BrandController::class, 'index'])->name('brands.index');
        Route::post('/brands',          [BrandController::class, 'store'])->name('brands.store');
        Route::put('/brands/{brand}',   [BrandController::class, 'update'])->name('brands.update');
        Route::delete('/brands/{brand}',[BrandController::class, 'destroy'])->name('brands.destroy');

        // Cajas de cambios
        Route::get('/gearboxes',              [GearboxController::class, 'index'])->name('gearboxes.index');
        Route::post('/gearboxes',             [GearboxController::class, 'store'])->name('gearboxes.store');
        Route::put('/gearboxes/{gearbox}',    [GearboxController::class, 'update'])->name('gearboxes.update');
        Route::delete('/gearboxes/{gearbox}', [GearboxController::class, 'destroy'])->name('gearboxes.destroy');

        // Productos
        Route::get('/products',                           [AdminProductController::class, 'index'])->name('products.index');
        Route::get('/products/create',                    [AdminProductController::class, 'create'])->name('products.create');
        Route::post('/products',                          [AdminProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit',            [AdminProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}',                 [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}',              [AdminProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/bulk-image',               [AdminProductController::class, 'bulkImage'])->name('products.bulk-image');

        // Importación masiva desde Excel
        Route::get('/import',          [ImportController::class, 'index'])->name('import.index');
        Route::post('/import/products',[ImportController::class, 'products'])->name('import.products');

    });

});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES (BREEZE)
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
