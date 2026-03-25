<?php

namespace App\Http\Controllers\Web\ZonaPrivada;

use App\Http\Controllers\Controller;
use App\Models\PriceList;
use App\Models\PrivateNotification;
use Illuminate\Support\Facades\Auth;

class ListaPreciosController extends Controller
{
    public function index()
    {
        $user       = Auth::guard('client')->user();
        $priceLists = PriceList::where('is_active', true)->orderBy('name')->get();
        $notification = PrivateNotification::where('is_active', true)->latest()->first();

        return view('web.zona-privada.lista-precios.index', compact('user', 'priceLists', 'notification'));
    }
}
