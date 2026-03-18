<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Inertia\Inertia;
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
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new ProductsImport();

        Excel::import($import, $request->file('file'));

        return back()->with([
            'import_result' => [
                'created' => $import->created,
                'updated' => $import->updated,
                'errors'  => $import->errors,
            ],
        ]);
    }
}
