<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * ইউজারের কার্যকলাপ (কে ঢুকলো, কোন পেজ দেখলো, টেস্ট দিল ইত্যাদি) — অ্যাডমিন প্যানেলে দেখার জন্য।
 * টেবিল না থাকলেও সাইট কখনো ভাঙবে না (সব কিছু try/catch ও টেবিল-চেকের ভেতরে)।
 */
class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'visitor', 'type', 'label', 'path', 'meta', 'ip', 'device', 'created_at'];

    protected $casts = ['meta' => 'array', 'created_at' => 'datetime'];

    /** type => [বাংলা নাম, রঙের ক্লাস] */
    public const TYPES = [
        'page' => ['পেজ দেখেছেন', 'bg-slate-100 text-slate-600'],
        'login' => ['লগইন', 'bg-emerald-100 text-emerald-700'],
        'login_failed' => ['ব্যর্থ লগইন', 'bg-rose-100 text-rose-700'],
        'logout' => ['লগআউট', 'bg-slate-200 text-slate-600'],
        'register' => ['নতুন রেজিস্ট্রেশন', 'bg-indigo-100 text-indigo-700'],
        'test_start' => ['টেস্ট শুরু', 'bg-sky-100 text-sky-700'],
        'test_submit' => ['টেস্ট শেষ', 'bg-blue-100 text-blue-700'],
        'lesson_done' => ['অনুশীলন সম্পন্ন', 'bg-teal-100 text-teal-700'],
        'feedback' => ['মেসেজ পাঠিয়েছেন', 'bg-amber-100 text-amber-700'],
        'payment' => ['পেমেন্ট জমা', 'bg-violet-100 text-violet-700'],
        'password_reset' => ['পাসওয়ার্ড রিসেট', 'bg-orange-100 text-orange-700'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** টেবিল আছে কিনা (আছে → ১ দিন ক্যাশ; নেই → মাত্র ৩০ সেকেন্ড, যাতে টেবিল বানানোর পর দ্রুত চালু হয়) */
    public static function ready(): bool
    {
        try {
            $cached = Cache::get('activity_log_ready');
            if (is_bool($cached)) {
                return $cached;
            }
            $has = Schema::hasTable('activity_logs');
            Cache::put('activity_log_ready', $has, $has ? 86400 : 30);

            return $has;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function record(string $type, string $label, array $meta = [], ?Request $request = null, ?int $userId = null): void
    {
        try {
            if (! static::ready()) {
                return;
            }
            $request ??= request();
            $ua = (string) $request->userAgent();
            if (static::isBot($ua)) {
                return;
            }

            static::create([
                'user_id' => $userId ?? $request->user()?->id,
                'visitor' => static::visitorId($request),
                'type' => $type,
                'label' => mb_substr($label, 0, 250),
                'path' => mb_substr('/'.ltrim($request->path(), '/'), 0, 200),
                'meta' => $meta ?: null,
                'ip' => $request->ip(),
                'device' => static::device($ua),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // লগ লিখতে না পারলে সাইটের কাজ আটকাবে না — তবে কারণটা অ্যাডমিনের ডায়াগনস্টিকে দেখানোর জন্য রেখে দিই
            try {
                Cache::put('activity_log_last_error', now()->format('d M h:i A').' — '.mb_substr($e->getMessage(), 0, 400), 86400);
            } catch (\Throwable $e2) {
            }
        }
    }

    public static function visitorId(Request $request): string
    {
        $base = $request->hasSession() ? $request->session()->getId() : ($request->ip().'|'.$request->userAgent());

        return substr(sha1($base), 0, 12);
    }

    public static function isBot(string $ua): bool
    {
        return $ua === '' || (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|monitor|curl|wget|python-requests/i', $ua);
    }

    public static function device(string $ua): string
    {
        if (preg_match('/ipad|tablet/i', $ua)) {
            return 'tablet';
        }

        return preg_match('/mobile|android|iphone/i', $ua) ? 'mobile' : 'desktop';
    }
}
