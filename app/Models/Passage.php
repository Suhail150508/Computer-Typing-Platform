<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Passage extends Model
{
    protected $fillable = ['lab_id', 'title', 'language', 'content', 'word_count', 'char_count', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        // কনটেন্ট সেভ হওয়ার সময় শব্দ ও ক্যারেক্টার সংখ্যা নিজে থেকেই হিসাব হবে
        static::saving(function (Passage $passage) {
            $content = trim((string) $passage->content);
            $passage->char_count = mb_strlen(preg_replace('/\s+/u', '', $content) ?? '');
            $passage->word_count = $content === '' ? 0 : count(preg_split('/\s+/u', $content, -1, PREG_SPLIT_NO_EMPTY));
        });
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }
}
