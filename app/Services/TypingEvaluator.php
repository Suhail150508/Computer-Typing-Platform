<?php

namespace App\Services;

/**
 * টাইপিং টেস্টের ফলাফল হিসাব করার ইঞ্জিন (ফ্রেমওয়ার্ক-নিরপেক্ষ, সহজে টেস্টযোগ্য)।
 *
 * নিয়ম:
 *  - ৫ স্ট্রোক (কোড পয়েন্ট, স্পেস ছাড়া) = ১ শব্দ
 *  - WPM = (সঠিক ক্যারেক্টার / ৫) / মিনিট
 *  - ভুল% = ভুল শব্দ / টাইপ করা শব্দ;  Accuracy = ১০০ - ভুল%
 *  - শব্দ মেলানো হয় edit-distance অ্যালাইনমেন্টে।
 *  - কড়া নিয়ম ($strictSkip = true, ডিফল্ট — সরকারি পরীক্ষার মতো): কোনো শব্দ বা লাইন বাদ পড়লে (অথবা বাড়তি শব্দ ঢুকে গেলে)
 *    তার পর থেকে টাইপ করা সব শব্দ ভুল ধরা হয়, সেগুলো থেকে কোনো সঠিক ক্যারেক্টার/গতি গোনা হয় না।
 *    একটা সাধারণ বানান ভুল (শব্দ বদলে যাওয়া) এর আওতায় পড়ে না — শুধু ওই একটি শব্দ ভুল।
 */
class TypingEvaluator
{
    private const LOOKAHEAD = 12;

    /**
     * @return array<string, mixed>
     */
    public function evaluate(
        string $original,
        string $typed,
        int $timeTakenSeconds,
        int $durationSeconds,
        int $minWpm = 20,
        int $minAccuracy = 90,
        int $minDurationPercent = 90,
        bool $strictSkip = true
    ): array {
        $original = $this->normalize($original);
        $typed = $this->normalize($typed);

        $origWords = $this->words($original);
        $typedWords = $this->words($typed);
        $m = count($typedWords);
        $timeTakenSeconds = max(1, $timeTakenSeconds);

        if ($m === 0) {
            return $this->emptyResult($timeTakenSeconds);
        }

        [$ops, $consumed] = $this->align($typedWords, $origWords);

        // কড়া নিয়ম: প্রথম বাদ-পড়া ('m') বা বাড়তি ('x') শব্দের পর থেকে সব টাইপ করা শব্দ ভুল
        $break = null;
        if ($strictSkip) {
            [$ops, $break] = $this->applyStrictSkip($ops);
        }

        $correct = 0;
        $typedChars = 0;
        $equalWords = 0;
        $wrongWords = 0;
        $extraWords = 0;
        $missedWords = 0;
        $afterSkipWords = 0;

        foreach ($ops as $op) {
            switch ($op[0]) {
                case 'e':
                    $len = $this->len($op[1]);
                    $correct += $len;
                    $typedChars += $len;
                    $equalWords++;
                    break;
                case 'w':
                    $typedChars += $this->len($op[1]);
                    $correct += $this->lcs($this->chars($op[1]), $this->chars($op[2]));
                    $wrongWords++;
                    break;
                case 'x':
                    $typedChars += $this->len($op[1]);
                    $extraWords++;
                    break;
                case 'm':
                    if (! ($op[2] ?? false)) { // বাদ-পড়ার পরের 'm' আলাদা করে গোনা হয় না (টাইপ করা শব্দগুলোই ভুল ধরা হচ্ছে)
                        $missedWords++;
                    }
                    break;
                case 's': // বাদ পড়ার পরের শব্দ: পুরোটাই ভুল, কোনো ক্যারেক্টার সঠিক গোনা হয় না
                    $typedChars += $this->len($op[1]);
                    $afterSkipWords++;
                    break;
            }
        }

        $wrongTotal = $wrongWords + $extraWords + $missedWords + $afterSkipWords;
        $errorPercent = min(100.0, $wrongTotal / $m * 100);
        $accuracy = max(0.0, 100 - $errorPercent);
        $minutes = $timeTakenSeconds / 60;
        $wpm = ($correct / 5) / $minutes;

        $correctWithSpace = $correct + $equalWords;
        $typedWithSpace = $typedChars + max(0, $m - 1);

        $failReasons = [];
        if ($wpm < $minWpm) {
            $failReasons[] = "গতি কম (ন্যূনতম {$minWpm} WPM প্রয়োজন)";
        }
        if ($accuracy < $minAccuracy) {
            $failReasons[] = "নির্ভুলতা কম (ন্যূনতম {$minAccuracy}% প্রয়োজন)";
        }
        if ($break !== null && $afterSkipWords > 0) {
            $failReasons[] = "শব্দ/লাইন বাদ পড়ায় এর পরের {$afterSkipWords}টি শব্দ ভুল ধরা হয়েছে";
        }
        $requiredSeconds = (int) ceil($durationSeconds * $minDurationPercent / 100);
        if ($timeTakenSeconds < $requiredSeconds) {
            $failReasons[] = "নির্ধারিত সময়ের ন্যূনতম {$minDurationPercent}% টাইপ করতে হবে";
        }

        return [
            'time_taken_seconds' => $timeTakenSeconds,
            'total_chars' => $this->len($typed),
            'total_chars_no_space' => $typedChars,
            'correct_chars_no_space' => $correct,
            'wrong_chars_no_space' => max(0, $typedChars - $correct),
            'total_words' => $m,
            'wrong_words' => $wrongTotal,
            'wpm' => round($wpm, 2),
            'accuracy' => round($accuracy, 2),
            'error_percent' => round($errorPercent, 2),
            'result' => $failReasons === [] ? 'pass' : 'fail',
            'diff_meta' => [
                'ops' => $ops,
                'consumed' => $consumed,
                'counts' => [
                    'wrong' => $wrongWords,
                    'extra' => $extraWords,
                    'missed' => $missedWords,
                    'equal' => $equalWords,
                    'after_skip' => $afterSkipWords,
                ],
                'strict_skip' => $strictSkip,
                'skip_break' => $break,
                'typed_words_by_stroke' => intdiv($correct, 5),
                'with_space' => [
                    'total_chars' => $typedWithSpace,
                    'correct_chars' => $correctWithSpace,
                    'wpm' => round(($correctWithSpace / 5) / $minutes, 2),
                    'typed_words_by_stroke' => intdiv($correctWithSpace, 5),
                ],
                'fail_reasons' => $failReasons,
            ],
        ];
    }

    /**
     * প্রথম বাদ-পড়া ('m') বা বাড়তি ('x') শব্দ খুঁজে তার পরের সব টাইপ করা শব্দকে ভুল ('s') করে দেয়।
     *
     * @param  list<array<int, mixed>>  $ops
     * @return array{0: list<array<int, mixed>>, 1: array<string, mixed>|null}
     */
    private function applyStrictSkip(array $ops): array
    {
        $out = [];
        $break = null;
        $typedIndex = 0; // এ পর্যন্ত কতটি টাইপ করা শব্দ পার হয়েছে
        $after = 0;

        foreach ($ops as $op) {
            if ($break === null) {
                if ($op[0] === 'm' || $op[0] === 'x') {
                    $break = [
                        'type' => $op[0] === 'm' ? 'missed' : 'extra',
                        'word' => $op[1],
                        'position' => $typedIndex + 1, // টাইপ করা শব্দের ক্রমে কততম জায়গায় ঘটেছে
                        'after_words' => 0,
                    ];
                    if ($op[0] === 'x') {
                        $typedIndex++;
                    }
                    $out[] = $op;

                    continue;
                }
                if ($op[0] === 'e' || $op[0] === 'w') {
                    $typedIndex++;
                }
                $out[] = $op;

                continue;
            }

            // বাদ পড়ার পরের অংশ
            if ($op[0] === 'm') {
                $out[] = ['m', $op[1], true]; // দেখানোর জন্য রাখা হলো, ভুলের হিসাবে ধরা হবে না
            } else {
                $typed = $op[1];
                $orig = $op[0] === 'e' ? $op[1] : ($op[0] === 'w' ? $op[2] : '');
                $out[] = ['s', $typed, $orig];
                $after++;
            }
        }

        if ($break !== null) {
            $break['after_words'] = $after;
        }

        return [$out, $break];
    }

    /**
     * @param  list<string>  $typed
     * @param  list<string>  $orig
     * @return array{0: list<array<int, string>>, 1: int}
     */
    private function align(array $typed, array $orig): array
    {
        $m = count($typed);
        $limit = min(count($orig), $m + self::LOOKAHEAD);
        $orig = array_slice($orig, 0, $limit);

        $d = [];
        for ($i = 0; $i <= $m; $i++) {
            $d[$i] = array_fill(0, $limit + 1, 0);
            $d[$i][0] = $i;
        }
        for ($j = 0; $j <= $limit; $j++) {
            $d[0][$j] = $j;
        }

        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $limit; $j++) {
                $cost = $typed[$i - 1] === $orig[$j - 1] ? 0 : 1;
                $d[$i][$j] = min(
                    $d[$i - 1][$j - 1] + $cost,
                    $d[$i - 1][$j] + 1,
                    $d[$i][$j - 1] + 1
                );
            }
        }

        // শেষের না-টাইপ-করা মূল শব্দ ভুল নয়: সবচেয়ে কম খরচের কলাম বেছে নিই (টাই হলে বড় j)
        $bestJ = 0;
        $bestCost = PHP_INT_MAX;
        for ($j = 0; $j <= $limit; $j++) {
            if ($d[$m][$j] <= $bestCost) {
                $bestCost = $d[$m][$j];
                $bestJ = $j;
            }
        }

        $ops = [];
        $i = $m;
        $j = $bestJ;
        while ($i > 0 || $j > 0) {
            if ($i > 0 && $j > 0) {
                $cost = $typed[$i - 1] === $orig[$j - 1] ? 0 : 1;
                if ($d[$i][$j] === $d[$i - 1][$j - 1] + $cost) {
                    $ops[] = $cost === 0
                        ? ['e', $typed[$i - 1]]
                        : ['w', $typed[$i - 1], $orig[$j - 1]];
                    $i--;
                    $j--;

                    continue;
                }
            }
            if ($i > 0 && $d[$i][$j] === $d[$i - 1][$j] + 1) {
                $ops[] = ['x', $typed[$i - 1]];
                $i--;

                continue;
            }
            $ops[] = ['m', $orig[$j - 1]];
            $j--;
        }

        return [array_reverse($ops), $bestJ];
    }

    /**
     * @return list<string>
     */
    public function words(string $text): array
    {
        $text = trim(str_replace(["\r\n", "\r"], "\n", $text));
        if ($text === '') {
            return [];
        }

        return preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /**
     * @return list<string>
     */
    private function chars(string $word): array
    {
        return mb_str_split($word) ?: [];
    }

    private function len(string $text): int
    {
        return mb_strlen(preg_replace('/\s+/u', '', $text) ?? '');
    }

    /**
     * @param  list<string>  $a
     * @param  list<string>  $b
     */
    private function lcs(array $a, array $b): int
    {
        $n = count($a);
        $k = count($b);
        if ($n === 0 || $k === 0) {
            return 0;
        }
        $prev = array_fill(0, $k + 1, 0);
        for ($i = 1; $i <= $n; $i++) {
            $cur = array_fill(0, $k + 1, 0);
            for ($j = 1; $j <= $k; $j++) {
                $cur[$j] = $a[$i - 1] === $b[$j - 1]
                    ? $prev[$j - 1] + 1
                    : max($prev[$j], $cur[$j - 1]);
            }
            $prev = $cur;
        }

        return $prev[$k];
    }

    /**
     * বাংলা "ড়, ঢ়, য়" এর মতো অক্ষর দুইভাবে এনকোড হতে পারে (একটাই কোড পয়েন্ট,
     * অথবা মূল অক্ষর + আলাদা নুক্তা চিহ্ন) — দেখতে একই কিন্তু বাইট আলাদা।
     * তুলনা করার আগে দুটোকেই একই ফরম্যাটে (NFC) নিয়ে আসা হয়, নাহলে
     * হুবহু-সঠিক শব্দও ভুল ধরা পড়ে।
     */
    private function normalize(string $text): string
    {
        if (class_exists(\Normalizer::class)) {
            $result = \Normalizer::normalize($text, \Normalizer::FORM_C);
            if ($result !== false) {
                return $result;
            }
        }

        // intl এক্সটেনশন না থাকলে অন্তত বাংলার পরিচিত নুক্তা-অক্ষরগুলো ম্যানুয়ালি মিলিয়ে নিই
        $map = [
            "\u{09A1}\u{09BC}" => "\u{09DC}", // ড + ় => ড়
            "\u{09A2}\u{09BC}" => "\u{09DD}", // ঢ + ় => ঢ়
            "\u{09AF}\u{09BC}" => "\u{09DF}", // য + ় => য়
        ];

        return strtr($text, $map);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyResult(int $timeTaken): array
    {
        return [
            'time_taken_seconds' => $timeTaken,
            'total_chars' => 0,
            'total_chars_no_space' => 0,
            'correct_chars_no_space' => 0,
            'wrong_chars_no_space' => 0,
            'total_words' => 0,
            'wrong_words' => 0,
            'wpm' => 0,
            'accuracy' => 0,
            'error_percent' => 0,
            'result' => 'fail',
            'diff_meta' => [
                'ops' => [],
                'consumed' => 0,
                'counts' => ['wrong' => 0, 'extra' => 0, 'missed' => 0, 'equal' => 0, 'after_skip' => 0],
                'strict_skip' => true,
                'skip_break' => null,
                'typed_words_by_stroke' => 0,
                'with_space' => ['total_chars' => 0, 'correct_chars' => 0, 'wpm' => 0, 'typed_words_by_stroke' => 0],
                'fail_reasons' => ['কিছুই টাইপ করা হয়নি'],
            ],
        ];
    }
}
