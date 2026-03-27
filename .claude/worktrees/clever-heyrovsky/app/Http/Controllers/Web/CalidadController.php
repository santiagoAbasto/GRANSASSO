<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CalidadDownload;
use App\Models\Page;

class CalidadController extends Controller
{
    public function index()
    {
        $page = Page::where('slug', 'calidad')
            ->with(['sections' => fn ($q) => $q->where('is_active', true)->orderBy('order')->with('images')])
            ->first();

        $downloads = CalidadDownload::where('is_active', true)->orderBy('order')->get();

        return view('web.calidad.index', compact('page', 'downloads'));
    }
}
