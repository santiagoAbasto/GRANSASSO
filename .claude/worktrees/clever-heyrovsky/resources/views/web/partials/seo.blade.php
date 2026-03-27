@php
use App\Models\SeoMeta;

/*
|--------------------------------------------------------------------------
| SEO AUTO-DETECT
| Detecta la ruta actual y carga los metadatos guardados en la base de datos.
| Si el admin no configuró metadatos para esta página, usa los valores por defecto.
|--------------------------------------------------------------------------
*/

$_route = Route::currentRouteName() ?? '';

$_pageKey = match(true) {
    $_route === 'home'                          => 'home',
    $_route === 'products.index'                => 'products',
    $_route === 'products.category'             => 'categories',
    $_route === 'products.subcategory'          => 'subcategories',
    str_starts_with($_route, 'industries')      => 'industries',
    str_starts_with($_route, 'posts')           => 'novedades',
    str_starts_with($_route, 'contact')         => 'contacto',
    default                                     => null,
};

$_seo = $_pageKey
    ? SeoMeta::where('page', $_pageKey)->first()
    : null;

$seoTitle    = $_seo?->title       ?: null;
$seoDesc     = $_seo?->description ?: 'Gran Sasso – Repuestos y componentes para cajas de cambio.';
$seoKeywords = $_seo?->keywords    ?: '';
$seoOgImage  = $_seo?->og_image_url ?: null;
@endphp
