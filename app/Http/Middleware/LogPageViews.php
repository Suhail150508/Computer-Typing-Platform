<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use App\Support\Lessons;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/** সফল HTML পেজ ভিজিট (GET) অ্যাক্টিভিটি লগে রাখে। অ্যাডমিন প্যানেল, AJAX, বট ও অ্যাসেট বাদ। */
class LogPageViews
{
    private const SKIP_ROUTES = ['feedback.token', 'seo.robots', 'seo.sitemap'];

    private const LABELS = [
        'login' => 'লগইন পেজ', 'register' => 'রেজিস্টার পেজ', 'password.request' => 'পাসওয়ার্ড ভুলে গেছি পেজ',
        'dashboard' => 'ড্যাশবোর্ড', 'typing.setup' => 'টাইপ ল্যাব সেটআপ', 'typing.test' => 'টাইপ ল্যাব টেস্ট পেজ',
        'practice.index' => 'টাইপিং শুরু', 'practice.test' => 'টাইপিং শুরু টেস্ট পেজ', 'practice.preview' => 'কার্ড প্রিভিউ',
        'learn.index' => 'অনুশীলন (পাঠ তালিকা)', 'free.index' => 'শীট দেখে টাইপ', 'tips.index' => 'স্পিড বাড়ানোর উপায়',
        'wpm.index' => 'WPM Viewer', 'results.index' => 'টাইপিং রেজাল্ট তালিকা', 'results.show' => 'রেজাল্ট বিস্তারিত',
        'profile.edit' => 'প্রোফাইল', 'transactions.index' => 'লেনদেন', 'premium.index' => 'প্রিমিয়াম প্ল্যান', 'premium.show' => 'প্ল্যান পেমেন্ট পেজ',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $this->log($request, $response);
        } catch (\Throwable $e) {
            // লগের কারণে পেজ লোড আটকাবে না
        }

        return $response;
    }

    private function log(Request $request, Response $response): void
    {
        if (! $request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return;
        }
        if ($request->expectsJson() || $request->ajax() || $request->header('Purpose') === 'prefetch') {
            return;
        }
        if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return;
        }
        if ($request->is('admin', 'admin/*', 'up') || ! ActivityLog::ready()) {
            return;
        }

        $name = $request->route()?->getName();
        if ($name !== null && in_array($name, self::SKIP_ROUTES, true)) {
            return;
        }

        // একই ভিজিটর একই পেজ ১৫ সেকেন্ডের মধ্যে রিফ্রেশ করলে আবার লেখা হবে না
        $visitor = ActivityLog::visitorId($request);
        if (! Cache::add('al:'.sha1($visitor.$request->path()), 1, 15)) {
            return;
        }

        ActivityLog::record('page', $this->label($request, $name), ['route' => $name], $request);
    }

    private function label(Request $request, ?string $name): string
    {
        if ($name === 'learn.show') {
            $id = (string) $request->route('lesson');
            $l = Lessons::find($id);

            return 'অনুশীলন পাঠ'.($l ? ': '.$l['title'] : ' '.$id);
        }

        return self::LABELS[$name ?? ''] ?? ('/'.ltrim($request->path(), '/'));
    }
}
