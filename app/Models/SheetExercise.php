<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * শীট টাইপিং অনুশীলন — টেবিল দেখে ঘরে ঘরে টাইপ করা।
 * মূল্যায়নের সময় সব ঘর সারি-ক্রমে (row-major) এক লাইনে জুড়ে TypingEvaluator-এ দেওয়া হয়,
 * তাই WPM/Accuracy/পাস-ফেলের নিয়ম বাকি সব টেস্টের মতোই থাকে।
 */
class SheetExercise extends Model
{
    public const MAX_ROWS = 60;
    public const MAX_COLS = 8;
    public const MAX_CELL_LENGTH = 60;

    protected $fillable = [
        'title', 'description', 'icon', 'language', 'order', 'headers', 'rows',
        'duration_seconds', 'min_wpm', 'min_accuracy', 'min_duration_percent', 'is_active',
    ];

    protected function casts(): array
    {
        return ['headers' => 'array', 'rows' => 'array', 'is_active' => 'boolean'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('order')->orderBy('id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TypingAttempt::class);
    }

    public function rowCount(): int
    {
        return count($this->rows ?? []);
    }

    public function colCount(): int
    {
        return max(count($this->headers ?? []), ...array_map('count', $this->rows ?: [[]]));
    }

    /** মূল্যায়নের জন্য সব ঘরের লেখা সারি-ক্রমে, এক স্পেসে জুড়ে */
    public function flatText(): string
    {
        return static::flatten($this->rows ?? []);
    }

    /** @param  array<int, array<int, mixed>>  $cells */
    public static function flatten(array $cells): string
    {
        $parts = [];
        foreach ($cells as $row) {
            foreach ((array) $row as $cell) {
                $cell = trim(preg_replace('/\s+/u', ' ', (string) $cell) ?? '');
                if ($cell !== '') {
                    $parts[] = $cell;
                }
            }
        }

        return implode(' ', $parts);
    }

    /** অ্যাডমিন এডিট ফর্মে দেখানোর জন্য ট্যাব-বিভক্ত টেক্সট (Excel থেকে কপি-পেস্টের মতো) */
    public function toTsv(): string
    {
        $lines = [];
        if (! empty($this->headers)) {
            $lines[] = implode("\t", $this->headers);
        }
        foreach ($this->rows ?? [] as $row) {
            $lines[] = implode("\t", $row);
        }

        return implode("\n", $lines);
    }

    /**
     * Excel/Google Sheets থেকে কপি করা (ট্যাব-বিভক্ত) বা CSV (কমা-বিভক্ত) টেক্সটকে হেডার ও সারিতে ভাগ করে।
     *
     * @return array{headers: list<string>|null, rows: list<list<string>>}
     */
    public static function parseTable(string $raw, bool $firstRowIsHeader): array
    {
        $raw = str_replace(["\r\n", "\r"], "\n", trim($raw));
        $delimiter = str_contains($raw, "\t") ? "\t" : ',';

        $table = [];
        foreach (explode("\n", $raw) as $line) {
            if (trim($line) === '') {
                continue;
            }
            $cells = array_map(
                fn ($c) => trim(preg_replace('/\s+/u', ' ', (string) $c) ?? ''),
                str_getcsv($line, $delimiter, '"', '')
            );
            $table[] = $cells;
        }

        if ($table === []) {
            return ['headers' => null, 'rows' => []];
        }

        $width = max(array_map('count', $table));
        $table = array_map(fn ($r) => array_pad($r, $width, ''), $table);

        $headers = $firstRowIsHeader ? array_shift($table) : null;

        return ['headers' => $headers, 'rows' => array_values($table)];
    }
}
