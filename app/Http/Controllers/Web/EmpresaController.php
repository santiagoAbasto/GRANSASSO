<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;

class EmpresaController extends Controller
{
    public function index()
    {
        $page = Page::where('slug', 'empresa')
            ->with(['sections' => function ($q) {
                $q->where('is_active', true)
                  ->orderBy('order')
                  ->with(['images' => fn ($q) => $q->orderBy('order')]);
            }])
            ->first();

        return view('web.empresa.index', compact('page'));
    }
}
