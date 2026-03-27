<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Inertia\Inertia;

class ContactMessageController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::latest()->paginate(20);
        $messages->through(fn ($m) => array_merge($m->toArray(), [
            'created_at' => $m->created_at->format('d/m/Y H:i'),
        ]));

        return Inertia::render('Admin/ContactMessages/Index', compact('messages'));
    }

    public function show(ContactMessage $contactMessage)
    {
        if (!$contactMessage->is_read) {
            $contactMessage->markAsRead();
        }

        return Inertia::render('Admin/ContactMessages/Show', [
            'message' => array_merge($contactMessage->toArray(), [
                'created_at' => $contactMessage->created_at->format('d/m/Y H:i'),
            ]),
        ]);
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();
        return redirect()->route('admin.contact-messages.index')->with('success', 'Mensaje eliminado.');
    }
}
