<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        NewsletterSubscriber::firstOrCreate(
            ['email' => $request->email]
        );

        if ($request->expectsJson()) {
            return response()->json(['message' => '¡Gracias por suscribirte!']);
        }

        return back()->with('success', '¡Gracias por suscribirte!');
    }
}
