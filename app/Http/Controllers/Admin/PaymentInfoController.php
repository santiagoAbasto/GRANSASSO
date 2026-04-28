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
        $search = trim((string) request()->input('q', ''));
        $bankInfo    = BankInfo::first();
        $query = PaymentSubmission::with('privateUser')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $term = '%' . $search . '%';
                    $inner->where('bank', 'like', $term)
                        ->orWhere('branch', 'like', $term)
                        ->orWhere('invoices_cancelled', 'like', $term)
                        ->orWhere('observations', 'like', $term)
                        ->orWhereHas('privateUser', function ($userQuery) use ($term) {
                            $userQuery->where('name', 'like', $term)
                                ->orWhere('lastname', 'like', $term)
                                ->orWhere('email', 'like', $term);
                        });
                });
            });

        $stats = [
            'count'        => (clone $query)->count(),
            'total_amount' => (float) ((clone $query)->sum('amount') ?? 0),
        ];

        $submissions = $query
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $submissions->through(function (PaymentSubmission $submission) {
            return array_merge($submission->toArray(), [
                'customer_name' => $submission->privateUser?->full_name,
                'customer_email'=> $submission->privateUser?->email,
                'download_url' => $submission->file_path
                    ? route('admin.payment-submissions.download', $submission)
                    : null,
                'payment_date_label' => $submission->payment_date?->format('d/m/Y'),
                'created_at_label'   => $submission->created_at?->setTimezone(config('app.timezone'))->format('d/m/Y H:i'),
            ]);
        });

        return Inertia::render('Admin/PrivateZone/PaymentInfo', [
            'bankInfo'    => $bankInfo,
            'submissions' => $submissions,
            'q'           => $search,
            'stats'       => $stats,
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
