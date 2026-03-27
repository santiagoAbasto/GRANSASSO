<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DesarrollosMessage;
use App\Models\Page;
use Illuminate\Http\Request;

class DesarrollosController extends Controller
{
    public function index()
    {
        $page = Page::where('slug', 'desarrollos-especiales')
            ->with(['sections' => function ($q) {
                $q->where('is_active', true)
                  ->orderBy('order')
                  ->with(['images' => fn ($q) => $q->orderBy('order')]);
            }])
            ->first();

        return view('web.desarrollos.index', compact('page'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:200',
            'phone'   => 'required|string|max:50',
            'email'   => 'required|email|max:200',
            'file'    => 'nullable|file|max:10240|mimes:pdf,doc,docx,dwg,jpg,jpeg,png',
            'message' => 'required|string|max:3000',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('desarrollos', 'public');
        }

        DesarrollosMessage::create([
            'name'      => $data['name'],
            'phone'     => $data['phone'],
            'email'     => $data['email'],
            'file_path' => $filePath,
            'message'   => $data['message'],
        ]);

        return back()->with('success', 'Su mensaje fue enviado correctamente. Nos pondremos en contacto a la brevedad.');
    }
}
