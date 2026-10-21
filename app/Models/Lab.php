<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lab extends Model
{
    protected $fillable = [
        'name_bn', 'name_en', 'slug', 'badge_text', 'subtitle',
        'min_wpm', 'min_accuracy', 'min_duration_percent', 'durations', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * ইউজার এই ল্যাবে কোন কোন সময় (মিনিট) বেছে নিতে পারবে।
     * অ্যাডমিন ল্যাবে সময় সেট না করলে config/typing.php এর ডিফল্ট তালিকা।
     *
     * @return list<int>
     */
    public function availableDurations(): array
    {
        $list = collect(explode(',', (string) $this->durations))
            ->map(fn ($v) => (int) trim($v))
            ->filter(fn ($v) => $v >= 1 && $v <= 120)
            ->unique()->sort()->values()->all();

        return $list ?: array_values(array_map('intval', config('typing.durations')));
    }

    public function passages(): HasMany
    {
        return $this->hasMany(Passage::class);
    }
}
