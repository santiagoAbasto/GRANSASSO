<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DesarrollosMessage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
                'download_url' => $desarrollosMessage->file_path
                    ? route('admin.desarrollos-messages.download', $desarrollosMessage)
                    : null,
            ]),
        ]);
    }

    public function download(DesarrollosMessage $desarrollosMessage)
    {
        abort_unless($desarrollosMessage->file_path, 404);

        $disk = Storage::disk('local')->exists($desarrollosMessage->file_path) ? 'local' : 'public';
        $name = Str::slug(pathinfo($desarrollosMessage->name, PATHINFO_FILENAME) ?: 'adjunto-desarrollo');
        $extension = pathinfo($desarrollosMessage->file_path, PATHINFO_EXTENSION);
        $filename = $name . ($extension ? '.' . $extension : '');

        return Storage::disk($disk)->download($desarrollosMessage->file_path, $filename);
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
