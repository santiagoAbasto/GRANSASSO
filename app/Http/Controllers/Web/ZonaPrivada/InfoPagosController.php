<?php

namespace App\Http\Controllers\Web\ZonaPrivada;

use App\Http\Controllers\Controller;
use App\Models\BankInfo;
use App\Models\PaymentSubmission;
use App\Models\PrivateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InfoPagosController extends Controller
{
    public function index()
    {
        $user         = Auth::guard('client')->user();
        $bankInfo     = BankInfo::first();
        $notification = PrivateNotification::where('is_active', true)->latest()->first();

        return view('web.zona-privada.info-pagos.index', compact('user', 'bankInfo', 'notification'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'payment_date'       => ['required', 'date'],
            'amount'             => ['required', 'numeric', 'min:0'],
            'bank'               => ['required', 'string', 'max:200'],
            'branch'             => ['nullable', 'string', 'max:200'],
            'invoices_cancelled' => ['nullable', 'string', 'max:500'],
            'observations'       => ['nullable', 'string'],
            'file'               => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('payment-submissions', 'public');
        }

        $user = Auth::guard('client')->user();

        PaymentSubmission::create([
            'private_user_id'    => $user->id,
            'payment_date'       => $data['payment_date'],
            'amount'             => $data['amount'],
            'bank'               => $data['bank'],
            'branch'             => $data['branch'] ?? null,
            'invoices_cancelled' => $data['invoices_cancelled'] ?? null,
            'observations'       => $data['observations'] ?? null,
            'file_path'          => $filePath,
        ]);

        return back()->with('success', 'Pago enviado correctamente.');
    }
}
