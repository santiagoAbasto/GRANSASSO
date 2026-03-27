<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $allowedEmails = config('security.admin_allowed_emails', []);

        if (! $user || ! in_array(strtolower($user->email), $allowedEmails, true)) {
            abort(403, 'No tienes permisos para acceder al panel de administracion.');
        }

        return $next($request);
    }
}
