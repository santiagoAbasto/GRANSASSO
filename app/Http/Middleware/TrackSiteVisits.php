<?php

namespace App\Http\Middleware;

use App\Models\SiteVisitEvent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackSiteVisits
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldTrack($request, $response)) {
            return $response;
        }

        try {
            SiteVisitEvent::create([
                'session_id' => $request->session()->getId(),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'path' => '/' . ltrim($request->path(), '/'),
                'route_name' => $request->route()?->getName(),
                'locale' => app()->getLocale(),
                'private_user_id' => auth('client')->id(),
                'visited_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->ajax() || $request->expectsJson()) {
            return false;
        }

        if ($response->isRedirection() || $response->getStatusCode() >= 400) {
            return false;
        }

        if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return false;
        }

        $routeName = $request->route()?->getName();
        $path = trim($request->path(), '/');

        if (! $routeName && $path === '') {
            return true;
        }

        if ($request->is('admin') || $request->is('admin/*') || $request->is('api/*')) {
            return false;
        }

        if (in_array($routeName, ['lang.switch', 'newsletter.store', 'contact.store', 'desarrollos.store'], true)) {
            return false;
        }

        return true;
    }
}
