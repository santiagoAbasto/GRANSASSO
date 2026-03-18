<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Gearbox;
use App\Models\Product;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'brands'    => Brand::count(),
                'gearboxes' => Gearbox::count(),
                'products'  => Product::count(),
            ],
        ]);
    }
}
