<?php

namespace App\Support;

class Bn
{
    /** ইংরেজি ডিজিটকে বাংলা ডিজিটে রূপান্তর */
    public static function digits(int|float|string $value): string
    {
        return strtr((string) $value, [
            '0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
            '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯',
        ]);
    }

    /** 19.20 => "19.2", 21.00 => "21" (অপ্রয়োজনীয় শূন্য বাদ) */
    public static function num(int|float $value, int $decimals = 2): string
    {
        $formatted = number_format((float) $value, $decimals, '.', '');

        return str_contains($formatted, '.') ? rtrim(rtrim($formatted, '0'), '.') : $formatted;
    }

    /** সেকেন্ডকে বাংলা মিনিটে: 60 => "১", 90 => "১.৫", 600 => "১০" */
    public static function minutes(int $seconds): string
    {
        return self::digits(self::num($seconds / 60, 1));
    }
}
