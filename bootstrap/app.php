<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // cPanel/Cloudflare/প্রক্সির পেছনে থাকলেও সঠিকভাবে https চিনবে
        $middleware->trustProxies(at: '*');

        // ইউজার কোন পেজ দেখছে তার লগ (অ্যাডমিন প্যানেলে দেখা যায়)
        $middleware->web(append: [\App\Http\Middleware\LogPageViews::class]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'active' => \App\Http\Middleware\EnsureAccountIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
