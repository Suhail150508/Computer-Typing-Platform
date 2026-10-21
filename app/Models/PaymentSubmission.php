<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSubmission extends Model
{
    public const PLATFORMS = ['bkash', 'nagad', 'rocket'];

    protected $fillable = [
        'user_id', 'subscription_plan_id', 'platform', 'sender_number', 'transaction_id',
        'amount', 'status', 'admin_note', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function platformLabel(): string
    {
        return match ($this->platform) {
            'bkash' => 'bKash',
            'nagad' => 'Nagad',
            'rocket' => 'Rocket',
            default => ucfirst($this->platform),
        };
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
