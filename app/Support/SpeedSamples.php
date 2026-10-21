<?php

namespace App\Support;

/**
 * স্পিড টাইমলাইনের ডাটা।
 *
 * ব্রাউজার প্রতি সেকেন্ডে "এ পর্যন্ত মোট কতটি অক্ষর (স্পেস ছাড়া) টাইপ হয়েছে" পাঠায় — [3, 9, 15, ...]।
 * এটি শুধু চার্ট দেখানোর জন্য; চূড়ান্ত WPM/Accuracy/পাস-ফেল সবসময় সার্ভারেই হিসাব হয়, তাই এখানে
 * কেউ ডাটা বদলালেও ফলাফল বা প্রগ্রেসে কোনো প্রভাব পড়ে না।
 */
class SpeedSamples
{
    /** চার্টের উইন্ডো: সেকেন্ডের গতি এত সেকেন্ডের গড় করে দেখানো হয় (খুব এলোমেলো না দেখাতে) */
    public const WINDOW = 5;

    /**
     * ব্রাউজারের পাঠানো JSON যাচাই-বাছাই করে নিরাপদ তালিকা ফেরত দেয় (বা null)।
     *
     * @return list<int>|null
     */
    public static function sanitize(?string $json, int $maxSeconds): ?array
    {
        if ($json === null || $json === '') {
            return null;
        }

        $data = json_decode($json, true);
        if (! is_array($data) || $data === []) {
            return null;
        }

        $out = [];
        $prev = 0;
        foreach (array_slice(array_values($data), 0, max(1, $maxSeconds) + 2) as $v) {
            if (! is_numeric($v)) {
                return null;
            }
            $v = (int) max($prev, min(200000, (int) $v)); // কমতে পারে না (cumulative)
            $out[] = $v;
            $prev = $v;
        }

        return count($out) >= 2 ? $out : null;
    }

    /**
     * চার্টের জন্য সব হিসাব: প্রতি সেকেন্ডের (উইন্ডো-গড়) WPM, গড়, সর্বোচ্চ, সর্বনিম্ন, ধারাবাহিকতা, ধাপ (flow)।
     *
     * @param  list<int>  $chars  cumulative অক্ষর সংখ্যা, ইনডেক্স i = (i+1) সেকেন্ড শেষে
     * @return array<string, mixed>|null
     */
    public static function analyze(array $chars, float $targetWpm = 0): ?array
    {
        $n = count($chars);
        if ($n < 3) {
            return null;
        }

        $at = fn (int $sec) => $sec <= 0 ? 0 : $chars[min($sec, $n) - 1];

        // প্রতি সেকেন্ডে WPM = গত WINDOW সেকেন্ডে টাইপ হওয়া অক্ষর / ৫ / (উইন্ডো মিনিট)
        $series = [];
        for ($t = 1; $t <= $n; $t++) {
            $w = min(self::WINDOW, $t);
            $series[] = round((($at($t) - $at($t - $w)) / 5) / ($w / 60), 1);
        }

        $overall = round(($at($n) / 5) / ($n / 60), 1);

        // শুরুর ২-৩ সেকেন্ড বাদ দিয়ে সর্বনিম্ন (শুরুর ধীরগতি ধরলে অর্থহীন হয়)
        $stable = array_slice($series, min(self::WINDOW, $n - 1));
        $stable = $stable ?: $series;
        $peak = max($series);
        $low = min($stable);

        $mean = array_sum($stable) / max(1, count($stable));
        $var = array_sum(array_map(fn ($x) => ($x - $mean) ** 2, $stable)) / max(1, count($stable));
        $cv = $mean > 0 ? sqrt($var) / $mean : 1;
        $consistency = (int) round(max(0, min(100, 100 - $cv * 100)));

        // ধাপ (flow): সর্বোচ্চ ১২টি ধাপে ভাগ, প্রতিটি ধাপ ৫ সেকেন্ডের গুণিতক
        $bucket = max(5, (int) ceil($n / 12 / 5) * 5);
        $steps = [];
        for ($from = 0; $from < $n; $from += $bucket) {
            $to = min($n, $from + $bucket);
            $len = $to - $from;
            $wpm = $len > 0 ? round((($at($to) - $at($from)) / 5) / ($len / 60), 1) : 0.0;
            $steps[] = ['from' => $from, 'to' => $to, 'wpm' => $wpm];
        }

        // প্রতিটি ধাপ লক্ষ্যমাত্রার (বা নিজের গড়ের) তুলনায় কেমন
        $ref = $targetWpm > 0 ? $targetWpm : max(1, $overall);
        foreach ($steps as $i => &$s) {
            $ratio = $s['wpm'] / $ref;
            $s['level'] = match (true) {
                $ratio < 0.6 => 'slow',
                $ratio < 0.9 => 'mid',
                $ratio < 1.2 => 'good',
                default => 'fast',
            };
            $prev = $i > 0 ? $steps[$i - 1]['wpm'] : null;
            $s['trend'] = $prev === null ? null : ($s['wpm'] > $prev * 1.08 ? 'up' : ($s['wpm'] < $prev * 0.92 ? 'down' : 'flat'));
        }
        unset($s);

        $half = intdiv(count($steps), 2);
        $firstHalf = $half > 0 ? array_sum(array_column(array_slice($steps, 0, $half), 'wpm')) / $half : 0;
        $secondHalf = count($steps) - $half > 0 ? array_sum(array_column(array_slice($steps, $half), 'wpm')) / (count($steps) - $half) : 0;

        $best = collect($steps)->sortByDesc('wpm')->first();
        $worst = collect($steps)->sortBy('wpm')->first();

        return [
            'seconds' => $n,
            'series' => $series,
            'overall' => $overall,
            'peak' => $peak,
            'low' => $low,
            'consistency' => $consistency,
            'steps' => $steps,
            'bucket' => $bucket,
            'first_half' => round($firstHalf, 1),
            'second_half' => round($secondHalf, 1),
            'best' => $best,
            'worst' => $worst,
        ];
    }

    public static function clock(int $sec): string
    {
        return sprintf('%d:%02d', intdiv($sec, 60), $sec % 60);
    }
}
