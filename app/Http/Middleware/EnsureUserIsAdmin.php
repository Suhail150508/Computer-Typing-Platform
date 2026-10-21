<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * শুধুমাত্র অ্যাডমিন রোলের ইউজার অ্যাডমিন প্যানেলে ঢুকতে পারবে।
     * সাধারণ ইউজার গেলে 403 (Forbidden) দেখানো হবে।
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            abort(403, 'এই পেজ শুধুমাত্র অ্যাডমিনদের জন্য।');
        }

        return $next($request);
    }
}
