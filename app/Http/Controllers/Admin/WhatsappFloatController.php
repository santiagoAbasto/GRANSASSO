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
        $facebookUrl = trim((string) ($settings?->facebook ?? ''));
        $instagramUrl = trim((string) ($settings?->instagram ?? ''));

        return Inertia::render('Admin/PrivateZone/WhatsappFloat', [
            'whatsapp' => [
                'number' => $currentNumber,
                'fallback_number' => $fallbackNumber,
                'preview_url' => $this->buildPreviewUrl($currentNumber),
                'facebook' => $facebookUrl,
                'instagram' => $instagramUrl,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'number' => ['nullable', 'string', 'max:32', 'regex:/^[0-9\s\-()+]+$/'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
        ]);

        $facebookUrl = $this->normalizeUrl($data['facebook'] ?? null);
        $instagramUrl = $this->normalizeUrl($data['instagram'] ?? null);
        if ($facebookUrl !== null && ! filter_var($facebookUrl, FILTER_VALIDATE_URL)) {
            return back()->withErrors([
                'facebook' => 'Ingresá un enlace valido para Facebook.',
            ])->withInput();
        }

        if ($instagramUrl !== null && ! filter_var($instagramUrl, FILTER_VALIDATE_URL)) {
            return back()->withErrors([
                'instagram' => 'Ingresá un enlace valido para Instagram.',
            ])->withInput();
        }

        $this->whatsappSection()->update([
            'content' => [
                'number' => $data['number'] ?: '',
            ],
        ]);

        $settings = SiteSetting::query()->first();
        if (! $settings) {
            $settings = SiteSetting::create([
                'address' => [],
            ]);
        }

        $settings->update([
            'facebook' => $facebookUrl ?: null,
            'instagram' => $instagramUrl ?: null,
        ]);

        return back()->with('success', 'Redes sociales actualizadas.');
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

    private function normalizeUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        if (! preg_match('#^[a-z][a-z0-9+\-.]*://#i', $url)) {
            $url = 'https://' . $url;
        }

        return $url;
    }
}
