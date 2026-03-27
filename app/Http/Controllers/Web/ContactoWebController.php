<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Traits\SanitizesHtml;

class ContactoWebController extends Controller
{
    use SanitizesHtml;

    public function index()
    {
        $page = Page::where('slug', 'contacto')
            ->with(['sections' => fn ($q) => $q->orderBy('order')->with('images')])
            ->first();

        $bannerSection = $page?->sections->where('type', 'banner')->first();
        $bannerSlides  = $bannerSection?->images->sortBy('order') ?? collect();

        $infoSection = $page?->sections->where('type', 'contact_info')->first();
        $raw = is_array($infoSection?->content) ? $infoSection->content : [];

        // Compatibilidad con formato anterior (phone/email string → arrays)
        $phones = $raw['phones'] ?? (isset($raw['phone']) && $raw['phone'] ? [$raw['phone']] : []);
        $emails = $raw['emails'] ?? (isset($raw['email']) && $raw['email'] ? [$raw['email']] : []);

        $contactInfo = [
            'address'   => $raw['address']   ?? '',
            'phones'    => array_values(array_filter($phones)),
            'emails'    => array_values(array_filter($emails)),
            'map_embed' => $this->sanitizeMapEmbed($raw['map_embed'] ?? ''),
        ];

        return view('web.contacto.index', compact('page', 'bannerSlides', 'contactInfo'));
    }
}
