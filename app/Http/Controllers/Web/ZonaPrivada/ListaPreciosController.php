<?php

namespace App\Http\Controllers\Web\ZonaPrivada;

use App\Http\Controllers\Controller;
use App\Models\PriceList;
use App\Models\PrivateNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListaPreciosController extends Controller
{
    public function index()
    {
        $user       = Auth::guard('client')->user();
        $priceLists = PriceList::where('is_active', true)->orderBy('name')->get();
        $notification = PrivateNotification::where('is_active', true)
            ->whereHas('users', fn($q) => $q->where('private_user_id', $user->id))
            ->latest()->first();

        return view('web.zona-privada.lista-precios.index', compact('user', 'priceLists', 'notification'));
    }

    public function view(PriceList $priceList)
    {
        $name = Str::slug($priceList->name) . '.pdf';
        return Storage::disk('public')->response($priceList->file_path, $name, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $name . '"',
        ]);
    }

    public function download(PriceList $priceList)
    {
        $name = Str::slug($priceList->name) . '.pdf';
        return Storage::disk('public')->download($priceList->file_path, $name);
    }
}
