<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * লগইন করা অবস্থায় অ্যাডমিন একাউন্ট বন্ধ করে দিলে ইউজারকে সাথে সাথে লগআউট করে দেয়।
 * (মেয়াদ শেষ হলে কিন্তু লগআউট হয় না — শুধু টেস্ট শুরু আটকায়।)
 */
class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            $reason = $user->deactivated_reason;
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'login' => 'আপনার একাউন্টটি বন্ধ করা হয়েছে।'.($reason ? " কারণ: {$reason}।" : '').' অ্যাডমিনের সাথে যোগাযোগ করুন।',
            ]);
        }

        return $next($request);
    }
}
