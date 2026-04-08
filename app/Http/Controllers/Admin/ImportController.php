<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\CatalogProductsImport;
use App\Imports\ProductsImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

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

    private function detectFormat(UploadedFile $file): string
    {
        $path = $file->getRealPath() ?: $file->getPathname();

        try {
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);

            $spreadsheet = $reader->load($path);
            $firstCell = strtolower(trim((string) $spreadsheet->getSheet(0)->getCell('A1')->getValue()));

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return $firstCell === 'tipo' ? 'catalog' : 'flat';
        } catch (\Throwable) {
            return 'flat';
        }
    }
}
