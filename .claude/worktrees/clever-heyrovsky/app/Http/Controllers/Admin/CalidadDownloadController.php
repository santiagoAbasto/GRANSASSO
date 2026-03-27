<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalidadDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CalidadDownloadController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name_es'  => 'nullable|string|max:200',
            'name_en'  => 'nullable|string|max:200',
            'name_pt'  => 'nullable|string|max:200',
            'logo'     => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'file'     => 'required|file|max:30720',
            'order'    => 'nullable|string|max:20',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('calidad/logos', 'public');
        }

        $uploadedFile = $request->file('file');
        $filePath     = $uploadedFile->store('calidad/downloads', 'public');
        $fileFormat   = strtoupper($uploadedFile->getClientOriginalExtension());
        $fileSize     = $uploadedFile->getSize();

        $fallbackName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);

        $existing = CalidadDownload::pluck('order')->toArray();
        $nextOrder = $request->filled('order')
            ? strtoupper(trim($request->input('order')))
            : $this->nextAlphaOrder($existing);

        CalidadDownload::create([
            'name'        => [
                'es' => $data['name_es'] ?: $fallbackName,
                'en' => $data['name_en'] ?? null,
                'pt' => $data['name_pt'] ?? null,
            ],
            'logo'        => $logoPath,
            'file'        => $filePath,
            'file_format' => $fileFormat,
            'file_size'   => $fileSize,
            'order'       => $nextOrder,
            'is_active'   => true,
        ]);

        return back()->with('success', 'Descarga agregada.');
    }

    public function update(Request $request, CalidadDownload $download)
    {
        $data = $request->validate([
            'name_es'   => 'nullable|string|max:200',
            'name_en'   => 'nullable|string|max:200',
            'name_pt'   => 'nullable|string|max:200',
            'logo'      => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'file'      => 'nullable|file|max:30720',
            'order'     => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $logoPath = $download->logo;
        if ($request->hasFile('logo')) {
            if ($logoPath) Storage::disk('public')->delete($logoPath);
            $logoPath = $request->file('logo')->store('calidad/logos', 'public');
        }

        $filePath   = $download->file;
        $fileFormat = $download->file_format;
        $fileSize   = $download->file_size;
        if ($request->hasFile('file')) {
            if ($filePath) Storage::disk('public')->delete($filePath);
            $uploaded   = $request->file('file');
            $filePath   = $uploaded->store('calidad/downloads', 'public');
            $fileFormat = strtoupper($uploaded->getClientOriginalExtension());
            $fileSize   = $uploaded->getSize();
        }

        $download->update([
            'name'        => [
                'es' => $data['name_es'] ?: (is_array($download->name) ? ($download->name['es'] ?? '') : $download->name),
                'en' => $data['name_en'] ?? null,
                'pt' => $data['name_pt'] ?? null,
            ],
            'logo'        => $logoPath,
            'file'        => $filePath,
            'file_format' => $fileFormat,
            'file_size'   => $fileSize,
            'order'       => isset($data['order']) ? strtoupper(trim($data['order'])) : $download->order,
            'is_active'   => $data['is_active'] ?? $download->is_active,
        ]);

        return back()->with('success', 'Descarga actualizada.');
    }

    public function destroy(CalidadDownload $download)
    {
        if ($download->logo) Storage::disk('public')->delete($download->logo);
        if ($download->file) Storage::disk('public')->delete($download->file);
        $download->delete();
        return back()->with('success', 'Descarga eliminada.');
    }

    private function nextAlphaOrder(array $existing): string
    {
        $existing = array_map('strtoupper', array_filter($existing));
        if (empty($existing)) return 'A';

        $increment = function (string $s) use (&$increment): string {
            $i = strlen($s) - 1;
            while ($i >= 0) {
                if ($s[$i] < 'Z') {
                    $s[$i] = chr(ord($s[$i]) + 1);
                    return $s;
                }
                $s[$i] = 'A';
                $i--;
            }
            return 'A' . $s;
        };

        usort($existing, 'strcmp');
        $last = end($existing);
        $next = $increment($last);
        while (in_array($next, $existing, true)) {
            $next = $increment($next);
        }
        return $next;
    }
}
