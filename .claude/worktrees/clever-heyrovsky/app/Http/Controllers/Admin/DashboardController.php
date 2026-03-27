<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\ContactMessage;
use App\Models\Gearbox;
use App\Models\NewsletterSubscriber;
use App\Models\Product;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $recentMessages = ContactMessage::latest()->limit(5)->get()->map(fn ($m) => [
            'id'         => $m->id,
            'name'       => $m->name,
            'email'      => $m->email,
            'company'    => $m->company,
            'is_read'    => $m->is_read,
            'created_at' => $m->created_at->format('d/m/Y H:i'),
        ]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'brands'          => Brand::count(),
                'gearboxes'       => Gearbox::count(),
                'products'        => Product::count(),
                'messages'        => ContactMessage::count(),
                'unread_messages' => ContactMessage::unread()->count(),
                'subscribers'     => NewsletterSubscriber::count(),
            ],
            'recent_messages' => $recentMessages,
        ]);
    }
}
