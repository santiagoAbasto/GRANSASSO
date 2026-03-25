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
use App\Http\Controllers\Admin\HeroController;
use App\Http\Controllers\Admin\CmsController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\NewsletterAdminController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\HomeCalidadController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\CalidadController;
use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\ContactoWebController;
use App\Http\Controllers\Web\DesarrollosController;
use App\Http\Controllers\Admin\CalidadDownloadController;
use App\Http\Controllers\Admin\DesarrollosMessageController;

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
    Route::get('/{brand:slug}/{gearbox:slug}/{product}/pdf', [ProductController::class, 'pdf'])->name('pdf');
});

// API interna: gearboxes por marca (usada por los buscadores via AJAX)
Route::get('/api/gearboxes', function (\Illuminate\Http\Request $request) {
    $brand = \App\Models\Brand::where('slug', $request->input('brand'))->first();
    if (! $brand) return response()->json([]);
    $gearboxes = \App\Models\Gearbox::where('brand_id', $brand->id)
        ->where('is_active', true)
        ->orderBy('name')
        ->get(['id', 'name', 'slug']);
    return response()->json($gearboxes);
})->name('api.gearboxes');

Route::get('/desarrollos-especiales',  [DesarrollosController::class, 'index'])->name('desarrollos');
Route::post('/desarrollos-especiales', [DesarrollosController::class, 'store'])->name('desarrollos.store');

Route::get('/calidad', [CalidadController::class, 'index'])->name('calidad');
Route::get('/contacto',  [ContactoWebController::class, 'index'])->name('contacto');
Route::post('/contacto', [ContactController::class, 'store'])->name('contact.store');

// Idioma
Route::get('/lang/{locale}', function (string $locale) {
    $allowed = ['es', 'en', 'pt'];
    if (in_array($locale, $allowed)) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

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
        Route::get('/brands',                              [BrandController::class, 'index'])->name('brands.index');
        Route::post('/brands',                             [BrandController::class, 'store'])->name('brands.store');
        Route::match(['POST','PUT'], '/brands/{brand}',    [BrandController::class, 'update'])->name('brands.update');
        Route::patch('/brands/{brand}/toggle-home',        [BrandController::class, 'toggleHome'])->name('brands.toggle-home');
        Route::delete('/brands/{brand}',                   [BrandController::class, 'destroy'])->name('brands.destroy');

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
        Route::patch('/products/images/{image}/primary',  [AdminProductController::class, 'setPrimaryImage'])->name('products.images.primary');
        Route::delete('/products/images/{image}',          [AdminProductController::class, 'destroyImage'])->name('products.images.destroy');

        // Home – Calidad (sección del home)
        Route::get('/home-calidad',              [HomeCalidadController::class, 'edit'])->name('home-calidad.edit');
        Route::post('/home-calidad',             [HomeCalidadController::class, 'update'])->name('home-calidad.update');
        Route::delete('/home-calidad/images/{image}', [HomeCalidadController::class, 'destroyImage'])->name('home-calidad.images.destroy');

        // Importación masiva desde Excel
        Route::get('/import',          [ImportController::class, 'index'])->name('import.index');
        Route::post('/import/products',[ImportController::class, 'products'])->name('import.products');

        // ── CMS: Hero / Slider ────────────────────────────────────────────
        Route::get('/hero',                    [HeroController::class, 'index'])->name('hero.index');
        Route::get('/hero/create',             [HeroController::class, 'create'])->name('hero.create');
        Route::post('/hero',                   [HeroController::class, 'store'])->name('hero.store');
        Route::get('/hero/{hero}/edit',        [HeroController::class, 'edit'])->name('hero.edit');
        Route::put('/hero/{hero}',             [HeroController::class, 'update'])->name('hero.update');
        Route::patch('/hero/{hero}/toggle',    [HeroController::class, 'toggle'])->name('hero.toggle');
        Route::delete('/hero/{hero}',          [HeroController::class, 'destroy'])->name('hero.destroy');

        // ── CMS: Home → About ─────────────────────────────────────────────
        Route::get('/home-about',              [CmsController::class, 'homeAbout'])->name('home-about.index');

        // ── CMS: Banner Productos ─────────────────────────────────────────
        Route::get('/products-banner',         [CmsController::class, 'productsBanner'])->name('cms.products-banner');

        // ── CMS: Empresa ──────────────────────────────────────────────────
        Route::get('/empresa',                 [CmsController::class, 'empresa'])->name('cms.empresa');

        // ── CMS: Calidad ──────────────────────────────────────────────────
        Route::get('/calidad',                 [CmsController::class, 'calidad'])->name('cms.calidad');

        // ── Descargas de Calidad ──────────────────────────────────────────
        Route::post('/calidad-downloads',                        [CalidadDownloadController::class, 'store'])->name('calidad-downloads.store');
        Route::match(['POST', 'PUT'], '/calidad-downloads/{download}', [CalidadDownloadController::class, 'update'])->name('calidad-downloads.update');
        Route::delete('/calidad-downloads/{download}',           [CalidadDownloadController::class, 'destroy'])->name('calidad-downloads.destroy');

        // ── CMS: Contacto ─────────────────────────────────────────────────
        Route::get('/contacto',                [CmsController::class, 'contacto'])->name('cms.contacto');
        Route::post('/contacto/info',          [CmsController::class, 'updateContactInfo'])->name('cms.contacto.info');

        // ── CMS: Desarrollos especiales ───────────────────────────────────
        Route::get('/desarrollos',             [CmsController::class, 'desarrollos'])->name('cms.desarrollos');

        // ── Mensajes de Desarrollos ───────────────────────────────────────
        Route::get('/desarrollos-messages',                         [DesarrollosMessageController::class, 'index'])->name('desarrollos-messages.index');
        Route::get('/desarrollos-messages/{desarrollosMessage}',    [DesarrollosMessageController::class, 'show'])->name('desarrollos-messages.show');
        Route::delete('/desarrollos-messages/{desarrollosMessage}', [DesarrollosMessageController::class, 'destroy'])->name('desarrollos-messages.destroy');

        // ── Secciones genéricas ───────────────────────────────────────────
        Route::post('/pages/{page}/sections',                    [CmsController::class, 'createSection'])->name('sections.create');
        Route::match(['POST', 'PUT'], '/sections/{section}',  [CmsController::class, 'updateSection'])->name('sections.update');
        Route::patch('/sections/{section}/toggle',            [CmsController::class, 'toggleSection'])->name('sections.toggle');
        Route::delete('/sections/{section}',                  [CmsController::class, 'destroySection'])->name('sections.destroy');

        // ── Imágenes de sección (banner Empresa, galería, etc.) ──────────
        Route::post('/sections/{section}/images',              [CmsController::class, 'addSectionImage'])->name('section-images.store');
        Route::patch('/section-images/{image}',               [CmsController::class, 'updateSectionImage'])->name('section-images.update');
        Route::delete('/section-images/{image}',              [CmsController::class, 'destroySectionImage'])->name('section-images.destroy');

        // ── Mensajes de Contacto ──────────────────────────────────────────
        Route::get('/contact-messages',               [ContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::get('/contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
        Route::delete('/contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');

        // ── Newsletter ────────────────────────────────────────────────────
        Route::get('/newsletter',              [NewsletterAdminController::class, 'index'])->name('newsletter.index');
        Route::post('/newsletter/send',         [NewsletterAdminController::class, 'send'])->name('newsletter.send');
        Route::delete('/newsletter/{newsletterSubscriber}', [NewsletterAdminController::class, 'destroy'])->name('newsletter.destroy');

        // ── SEO ───────────────────────────────────────────────────────────
        Route::get('/seo',                     [SeoController::class, 'index'])->name('seo.index');
        Route::post('/seo',                    [SeoController::class, 'upsert'])->name('seo.upsert');

        // ── Usuarios Admin ────────────────────────────────────────────────
        Route::get('/users',                   [UserController::class, 'index'])->name('users.index');
        Route::post('/users',                  [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}',            [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}',         [UserController::class, 'destroy'])->name('users.destroy');

    });

});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES (BREEZE)
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
