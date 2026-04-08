<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WhatsappFloatController extends Controller
{
    public function index()
    {
        $content = $this->whatsappSection()->content ?? [];
        $settings = SiteSetting::first();

        $storedNumber = trim((string) ($content['number'] ?? ''));
        $fallbackNumber = trim((string) ($settings?->phone_1 ?? ''));
        $currentNumber = $storedNumber !== '' ? $storedNumber : $fallbackNumber;

        return Inertia::render('Admin/PrivateZone/WhatsappFloat', [
            'whatsapp' => [
                'number' => $currentNumber,
                'fallback_number' => $fallbackNumber,
                'preview_url' => $this->buildPreviewUrl($currentNumber),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'number' => ['nullable', 'string', 'max:32', 'regex:/^[0-9\s\-()+]+$/'],
        ]);

        $this->whatsappSection()->update([
            'content' => [
                'number' => $data['number'] ?: '',
            ],
        ]);

        return back()->with('success', 'Botón flotante de WhatsApp actualizado.');
    }

    private function whatsappSection()
    {
        $page = Page::firstOrCreate(
            ['slug' => 'site-whatsapp'],
            ['title' => ['es' => 'WhatsApp flotante', 'en' => 'Floating WhatsApp', 'pt' => 'WhatsApp flutuante']]
        );

        return $page->sections()->firstOrCreate(
            ['type' => 'whatsapp_float'],
            [
                'is_active' => true,
                'order' => 'A',
                'content' => [
                    'number' => '',
                ],
            ]
        );
    }

    private function buildPreviewUrl(string $number): ?string
    {
        $digits = preg_replace('/\D/', '', $number);

        return $digits !== '' ? 'https://wa.me/' . $digits : null;
    }
}
