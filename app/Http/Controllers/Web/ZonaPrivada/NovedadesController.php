<?php

namespace App\Http\Controllers\Web\ZonaPrivada;

use App\Http\Controllers\Controller;
use App\Models\Novedad;
use App\Models\PrivateNotification;
use Illuminate\Support\Facades\Auth;

class NovedadesController extends Controller
{
    public function index()
    {
        $user         = Auth::guard('client')->user();
        $novedades    = Novedad::where('is_active', true)->latest()->paginate(9);
        $notification = PrivateNotification::where('is_active', true)->latest()->first();

        return view('web.zona-privada.novedades.index', compact('user', 'novedades', 'notification'));
    }

    public function show(Novedad $novedad)
    {
        if (! $novedad->is_active) {
            abort(404);
        }

        $user         = Auth::guard('client')->user();
        $notification = PrivateNotification::where('is_active', true)->latest()->first();

        return view('web.zona-privada.novedades.show', compact('user', 'novedad', 'notification'));
    }
}
