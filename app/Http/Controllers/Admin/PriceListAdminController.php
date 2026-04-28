<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PriceList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PriceListAdminController extends Controller
{
    public function index()
    {
        $priceLists = PriceList::orderByDesc('created_at')
            ->get()
            ->map(fn (PriceList $priceList) => [
                'id'              => $priceList->id,
                'name'            => $priceList->name,
                'file_size'       => $priceList->file_size,
                'file_size_human' => $priceList->file_size_human,
                'is_active'       => (bool) $priceList->is_active,
                'created_at'      => $priceList->created_at?->toISOString(),
                'created_at_label'=> $priceList->created_at?->format('d/m/Y'),
                'view_url'        => asset('storage/' . $priceList->file_path),
            ]);

        return Inertia::render('Admin/PrivateZone/PriceLists', [
            'priceLists' => $priceLists,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $file     = $request->file('file');
        $path     = $file->store('price-lists', 'public');
        $fileSize = $file->getSize();

        PriceList::create([
            'name'      => $request->input('name'),
            'file_path' => $path,
            'file_size' => $fileSize,
            'is_active' => true,
        ]);

        return back()->with('success', 'Lista de precios subida.');
    }

    public function destroy(PriceList $priceList)
    {
        Storage::disk('public')->delete($priceList->file_path);
        $priceList->delete();

        return back()->with('success', 'Lista de precios eliminada.');
    }
}
