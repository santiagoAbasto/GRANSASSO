<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Page;
use App\Models\Slider;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function index()
    {
        $slides = Slider::where('is_active', true)
            ->orderBy('order')
            ->get();

        $page = Page::where('slug', 'home')
            ->with(['sections' => fn ($q) => $q->where('is_active', true)->orderBy('order')->with('images')])
            ->first();

        // Si la página home no tiene secciones aún, evitamos null pointer en las vistas
        if ($page && ! $page->relationLoaded('sections')) {
            $page->setRelation('sections', collect());
        }

        $brands = Brand::active()
            ->where('show_in_home', true)
            ->ordered()
            ->get();

        // Posts/Novedades: colección vacía hasta que se implemente el módulo de posts
        $posts = collect();

        return view('web.home.home', compact('slides', 'page', 'brands', 'posts'));
    }
}
