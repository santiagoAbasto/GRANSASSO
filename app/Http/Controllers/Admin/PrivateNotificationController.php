<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrivateNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrivateNotificationController extends Controller
{
    public function index()
    {
        $notification = PrivateNotification::where('is_active', true)->latest()->first();
        $all          = PrivateNotification::orderByDesc('created_at')->get();

        return Inertia::render('Admin/PrivateZone/Notifications', [
            'notification' => $notification,
            'all'          => $all,
        ]);
    }

    public function upsert(Request $request)
    {
        $data = $request->validate([
            'title'     => ['required', 'string', 'max:255'],
            'body'      => ['required', 'string'],
            'is_active' => ['boolean'],
        ]);

        // Deactivate all existing ones first
        PrivateNotification::query()->update(['is_active' => false]);

        if ($request->input('id')) {
            $notification = PrivateNotification::findOrFail($request->input('id'));
            $notification->update($data);
        } else {
            PrivateNotification::create($data);
        }

        return back()->with('success', 'Notificación guardada.');
    }
}
