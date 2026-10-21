<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackReport extends Model
{
    protected $fillable = ['user_id', 'type', 'message', 'screenshot', 'page_url', 'user_agent', 'status'];

    public const TYPES = ['problem' => '⚠️ সমস্যা', 'idea' => '💡 পরামর্শ', 'other' => '💬 অন্যান্য'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
