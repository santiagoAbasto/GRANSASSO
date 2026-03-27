<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = ['es', 'en', 'pt'];
        $locale  = session('locale', config('app.locale'));

        if (!in_array($locale, $allowed)) {
            $locale = 'es';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
