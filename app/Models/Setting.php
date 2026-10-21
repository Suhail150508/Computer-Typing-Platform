<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            $row = static::query()->find($key);

            return $row ? $row->value : $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting:{$key}");
    }

    /** শব্দ/লাইন বাদ পড়লে এর পর থেকে সব ভুল ধরার কড়া নিয়ম চালু আছে কিনা (ডিফল্ট: চালু) */
    public static function strictSkip(): bool
    {
        return (string) static::get('strict_skip', '1') !== '0';
    }

    /** অ্যাডমিন প্যানেল থেকে বদলানো যায় এমন সংখ্যাভিত্তিক নিয়ম (key => [ন্যূনতম, সর্বোচ্চ]) */
    public const TYPING_RULES = [
        'practice_unlock_threshold' => [1, 50],
        'trial_days' => [0, 365],
        'grace_days' => [0, 30],
        'reminder_days' => [1, 60],
    ];

    /**
     * টাইপিং/সাবস্ক্রিপশনের নিয়ম পড়ে: অ্যাডমিন সেট করে থাকলে সেই মান, নাহলে config/typing.php এর ডিফল্ট।
     */
    public static function typing(string $key): int
    {
        $stored = static::get('typing_'.$key);

        return $stored !== null && $stored !== '' && is_numeric($stored)
            ? (int) $stored
            : (int) config('typing.'.$key);
    }

    /**
     * পেমেন্ট সংক্রান্ত সব নম্বর একসাথে — অ্যাডমিন প্যানেল থেকে সেট করা মান
     * অগ্রাধিকার পায়; অ্যাডমিন এখনো কিছু সেট না করলে config/typing.php এর
     * (.env থেকে আসা) ডিফল্ট মান দেখানো হয়, যাতে প্রথম থেকেই একটা কার্যকরী
     * মান থাকে।
     *
     * @return array<string, string>
     */
    public static function paymentNumbers(): array
    {
        $fallback = config('typing.payment_numbers', []);

        return [
            'bkash' => (string) static::get('payment_number_bkash', '') ?: (string) ($fallback['bkash'] ?? ''),
            'nagad' => (string) static::get('payment_number_nagad', '') ?: (string) ($fallback['nagad'] ?? ''),
            'rocket' => (string) static::get('payment_number_rocket', '') ?: (string) ($fallback['rocket'] ?? ''),
        ];
    }
}
