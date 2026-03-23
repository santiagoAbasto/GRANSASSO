<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DesarrollosMessage;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DesarrollosMessageController extends Controller
{
    public function index()
    {
        $messages = DesarrollosMessage::latest()->paginate(20);
        $messages->through(fn ($m) => array_merge($m->toArray(), [
            'created_at' => $m->created_at->format('d/m/Y H:i'),
        ]));

        return Inertia::render('Admin/DesarrollosMessages/Index', compact('messages'));
    }

    public function show(DesarrollosMessage $desarrollosMessage)
    {
        if (! $desarrollosMessage->is_read) {
            $desarrollosMessage->markAsRead();
        }

        return Inertia::render('Admin/DesarrollosMessages/Show', [
            'message' => array_merge($desarrollosMessage->toArray(), [
                'created_at' => $desarrollosMessage->created_at->format('d/m/Y H:i'),
                'file_url'   => $desarrollosMessage->file_path
                    ? asset('storage/' . $desarrollosMessage->file_path)
                    : null,
            ]),
        ]);
    }

    public function destroy(DesarrollosMessage $desarrollosMessage)
    {
        if ($desarrollosMessage->file_path) {
            Storage::disk('public')->delete($desarrollosMessage->file_path);
        }
        $desarrollosMessage->delete();

        return redirect()->route('admin.desarrollos-messages.index')->with('success', 'Mensaje eliminado.');
    }
}
