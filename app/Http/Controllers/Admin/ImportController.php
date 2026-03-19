<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\CatalogProductsImport;
use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Import/Index');
    }

    public function products(Request $request)
    {
        $request->validate([
            'file'   => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'format' => 'nullable|in:auto,flat,catalog',
        ]);

        $file   = $request->file('file');
        $format = $request->input('format', 'auto');

        // ── Auto-detección: leer solo la primera celda del archivo ─────────
        if ($format === 'auto') {
            $format = $this->detectFormat($file);
        }

        // ── Despachar al importador correcto ───────────────────────────────
        if ($format === 'catalog') {
            $import = new CatalogProductsImport();
        } else {
            $import = new ProductsImport();
        }

        Excel::import($import, $file);

        return back()->with([
            'import_result' => [
                'format'           => $format,
                'products_created' => $import->created,
                'products_updated' => $import->updated,
                'items_created'    => $import->itemsCreated,
                'items_updated'    => $import->itemsUpdated,
                'errors'           => $import->errors,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DETECCIÓN DE FORMATO
    |--------------------------------------------------------------------------
    |
    | Lee solo la primera celda del archivo.
    | Si es "tipo" (case-insensitive) → formato catálogo.
    | En cualquier otro caso           → formato plano.
    |
    */

    private function detectFormat(\Illuminate\Http\UploadedFile $file): string
    {
        // Clase anónima ligera que lee todas las filas pero solo nos interesa
        // la primera celda de la primera fila.
        $detector = new class implements ToArray {
            public string $firstCell = '';

            public function array(array $array): void
            {
                $this->firstCell = strtolower(trim((string) ($array[0][0] ?? '')));
            }
        };

        Excel::import($detector, $file);

        return $detector->firstCell === 'tipo' ? 'catalog' : 'flat';
    }
}
