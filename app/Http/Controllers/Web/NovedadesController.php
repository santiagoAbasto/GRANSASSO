<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PublicNovedad;

class NovedadesController extends Controller
{
    public function index()
    {
        $page = Page::firstOrCreate(
            ['slug' => 'novedades'],
            ['title' => ['es' => 'Novedades', 'en' => 'News', 'pt' => 'Novidades']]
        );

        $page->sections()->firstOrCreate(
            ['type' => 'banner'],
            ['is_active' => true, 'order' => 'A']
        );

        $page->sections()->firstOrCreate(
            ['type' => 'novedades_intro'],
            ['is_active' => true, 'order' => 'B']
        );

        $page->load(['sections' => fn ($q) => $q->where('is_active', true)->orderBy('order')->with('images')]);

        $novedades = PublicNovedad::where('is_active', true)
            ->latest()
            ->paginate(9);

        return view('web.novedades.index', compact('page', 'novedades'));
    }

    public function show(PublicNovedad $novedad)
    {
        if (! $novedad->is_active) {
            abort(404);
        }

        $page = Page::firstOrCreate(
            ['slug' => 'novedades'],
            ['title' => ['es' => 'Novedades', 'en' => 'News', 'pt' => 'Novidades']]
        );

        $page->sections()->firstOrCreate(
            ['type' => 'banner'],
            ['is_active' => true, 'order' => 'A']
        );

        $page->load([
            'sections' => fn ($q) => $q->where('is_active', true)->orderBy('order')->with('images'),
        ]);

        $novedad->load('galleryImages');

        $related = PublicNovedad::where('is_active', true)
            ->whereKeyNot($novedad->getKey())
            ->latest()
            ->take(3)
            ->get();

        return view('web.novedades.show', compact('page', 'novedad', 'related'));
    }
}
