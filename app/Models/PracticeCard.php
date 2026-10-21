<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticeCard extends Model
{
    public const TIERS = ['basic', 'advance', 'hard'];

    protected $fillable = [
        'tier', 'order', 'title', 'description', 'icon', 'language', 'content',
        'word_count', 'char_count', 'duration_seconds', 'min_wpm', 'min_accuracy',
        'min_duration_percent', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (PracticeCard $card) {
            $content = trim((string) $card->content);
            $card->char_count = mb_strlen(preg_replace('/\s+/u', '', $content) ?? '');
            $card->word_count = $content === '' ? 0 : count(preg_split('/\s+/u', $content, -1, PREG_SPLIT_NO_EMPTY));
        });
    }

    public function scopeTier(Builder $query, string $tier): Builder
    {
        return $query->where('tier', $tier);
    }

    public function scopeLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', $language);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TypingAttempt::class);
    }

    public function tierLabel(): string
    {
        return match ($this->tier) {
            'basic' => 'বেসিক',
            'advance' => 'অ্যাডভান্স',
            'hard' => 'হার্ড',
            default => $this->tier,
        };
    }
}
