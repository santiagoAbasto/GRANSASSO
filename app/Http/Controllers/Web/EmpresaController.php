<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;

class EmpresaController extends Controller
{
    public function index()
    {
        $page = Page::where('slug', 'empresa')
            ->with(['sections' => fn ($q) => $q->where('is_active', true)->orderBy('order')])
            ->first();

        return view('web.about.index', compact('page'));
    }
}
