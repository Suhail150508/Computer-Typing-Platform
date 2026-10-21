<?php

namespace App\Providers;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // APP_URL যদি https হয় (cPanel লাইভ সাইট), সব লিংক/অ্যাসেট https-এ যাবে; লোকালে http থাকলে কিছু বদলায় না
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // অ্যাক্টিভিটি লগ: লগইন/লগআউট/ব্যর্থ লগইন/পাসওয়ার্ড রিসেট
        Event::listen(Login::class, function (Login $e) {
            $req = request();
            if ($req->routeIs('register.attempt')) {
                return; // রেজিস্ট্রেশনের লগ আলাদাভাবে লেখা হয়
            }
            $how = $req->is('auth/google/*') ? 'গুগল দিয়ে লগইন' : 'লগইন';
            ActivityLog::record('login', "{$how}: {$e->user->name}", [], $req, (int) $e->user->getAuthIdentifier());
        });
        Event::listen(Logout::class, function (Logout $e) {
            if ($e->user) {
                ActivityLog::record('logout', "লগআউট: {$e->user->name}", [], request(), (int) $e->user->getAuthIdentifier());
            }
        });
        Event::listen(Failed::class, function (Failed $e) {
            ActivityLog::record('login_failed', 'ব্যর্থ লগইন চেষ্টা: '.mb_substr((string) ($e->credentials['email'] ?? $e->credentials['login'] ?? '—'), 0, 80), [], request(), $e->user?->getAuthIdentifier());
        });
        Event::listen(PasswordReset::class, function (PasswordReset $e) {
            ActivityLog::record('password_reset', "পাসওয়ার্ড রিসেট করেছেন: {$e->user->name}", [], request(), (int) $e->user->getAuthIdentifier());
        });
    }
}
