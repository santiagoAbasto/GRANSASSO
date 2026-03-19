<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Inertia\Inertia;

class NewsletterAdminController extends Controller
{
    public function index()
    {
        $subscribers  = NewsletterSubscriber::latest()->paginate(30);
        $total        = NewsletterSubscriber::count();
        $total_active = NewsletterSubscriber::where('is_active', true)->count();

        $subscribers->through(fn ($s) => array_merge($s->toArray(), [
            'created_at' => $s->created_at->format('d/m/Y'),
        ]));

        return Inertia::render('Admin/Newsletter/Index', compact('subscribers', 'total', 'total_active'));
    }

    public function send(\Illuminate\Http\Request $request)
    {
        // Stub: Newsletter send functionality (implement with mail driver)
        return back()->with('success', 'Campaña encolada para envío.');
    }

    public function destroy(NewsletterSubscriber $newsletterSubscriber)
    {
        $newsletterSubscriber->delete();
        return back()->with('success', 'Suscriptor eliminado.');
    }
}
