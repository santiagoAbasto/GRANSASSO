<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankInfo;
use App\Models\PaymentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PaymentInfoController extends Controller
{
    public function index()
    {
        $bankInfo    = BankInfo::first();
        $submissions = PaymentSubmission::with('privateUser')
            ->orderByDesc('created_at')
            ->paginate(20);

        $submissions->through(function (PaymentSubmission $submission) {
            return array_merge($submission->toArray(), [
                'download_url' => $submission->file_path
                    ? route('admin.payment-submissions.download', $submission)
                    : null,
            ]);
        });

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

    public function downloadSubmission(PaymentSubmission $submission)
    {
        abort_unless($submission->file_path, 404);

        $disk = Storage::disk('local')->exists($submission->file_path) ? 'local' : 'public';
        $base = $submission->privateUser?->full_name ?: 'comprobante-pago';
        $extension = pathinfo($submission->file_path, PATHINFO_EXTENSION);
        $filename = Str::slug($base) . ($extension ? '.' . $extension : '');

        return Storage::disk($disk)->download($submission->file_path, $filename);
    }
}
