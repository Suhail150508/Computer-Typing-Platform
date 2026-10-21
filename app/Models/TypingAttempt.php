<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TypingAttempt extends Model
{
    protected $fillable = [
        'user_id', 'passage_id', 'lab_id', 'practice_card_id', 'source', 'language',
        'duration_seconds', 'time_taken_seconds',
        'typed_text', 'original_text', 'total_chars', 'total_chars_no_space', 'correct_chars_no_space',
        'wrong_chars_no_space', 'total_words', 'wrong_words', 'wpm', 'accuracy', 'error_percent',
        'result', 'diff_meta', 'speed_samples',
    ];

    protected function casts(): array
    {
        return [
            'diff_meta' => 'array',
            'speed_samples' => 'array',
            'wpm' => 'float',
            'accuracy' => 'float',
            'error_percent' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    public function passage(): BelongsTo
    {
        return $this->belongsTo(Passage::class);
    }

    public function practiceCard(): BelongsTo
    {
        return $this->belongsTo(PracticeCard::class);
    }

    public function isPractice(): bool
    {
        return $this->source === 'practice';
    }

    /** টেবিলে/কার্ডে দেখানোর জন্য উৎসের নাম */
    public function sourceLabel(): string
    {
        if ($this->isPractice()) {
            return 'টাইপিং শুরু: '.($this->practiceCard?->tierLabel() ?? '').' #'.($this->practiceCard?->order ?? '?');
        }

        return $this->lab?->name_bn ?? '—';
    }

    public function isPass(): bool
    {
        return $this->result === 'pass';
    }

    public function timeLabel(): string
    {
        return intdiv($this->time_taken_seconds, 60).' Min '.($this->time_taken_seconds % 60).' Sec';
    }
}
