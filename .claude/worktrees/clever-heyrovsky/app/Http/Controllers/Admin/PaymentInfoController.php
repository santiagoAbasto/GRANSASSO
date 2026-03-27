<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankInfo;
use App\Models\PaymentSubmission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentInfoController extends Controller
{
    public function index()
    {
        $bankInfo    = BankInfo::first();
        $submissions = PaymentSubmission::with('privateUser')
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('Admin/PrivateZone/PaymentInfo', [
            'bankInfo'    => $bankInfo,
            'submissions' => $submissions,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'content' => ['required', 'array'],
        ]);

        $bankInfo = BankInfo::firstOrCreate([]);
        $bankInfo->update(['content' => $data['content']]);

        return back()->with('success', 'Información bancaria actualizada.');
    }
}
