<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrivateNotification;
use App\Models\PrivateUser;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrivateNotificationController extends Controller
{
    public function index()
    {
        $notification = PrivateNotification::with('users:id,name,lastname,email')
            ->where('is_active', true)->latest()->first();
        $all = PrivateNotification::with('users:id')
            ->orderByDesc('created_at')->get();
        $users = PrivateUser::where('status', 'approved')
            ->orderBy('name')->get(['id', 'name', 'lastname', 'email']);

        return Inertia::render('Admin/PrivateZone/Notifications', [
            'notification' => $notification,
            'all'          => $all,
            'users'        => $users,
        ]);
    }

    public function upsert(Request $request)
    {
        $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'body'       => ['required', 'string'],
            'is_active'  => ['boolean'],
            'user_ids'   => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:private_users,id'],
        ]);

        PrivateNotification::query()->update(['is_active' => false]);

        $userIds = $request->input('user_ids', []);

        $fields = $request->only(['title', 'body', 'is_active']);

        if ($request->input('id')) {
            $notification = PrivateNotification::findOrFail($request->input('id'));
            $notification->update($fields);
        } else {
            $notification = PrivateNotification::create($fields);
        }

        $notification->users()->sync($userIds);

        return back()->with('success', 'Notificación guardada.');
    }

    public function destroy(PrivateNotification $privateNotification)
    {
        $privateNotification->users()->detach();
        $privateNotification->delete();

        return back()->with('success', 'Notificación eliminada.');
    }
}
