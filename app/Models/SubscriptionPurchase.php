<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * এটি সাবস্ক্রিপশনের "ইতিহাস" রাখে (কবে কোন প্ল্যানে কতদিন অ্যাক্টিভ হয়েছিল)।
 * ইউজারের জমা দেওয়া পেমেন্ট-যাচাইয়ের অপেক্ষমাণ আবেদন থাকে আলাদা
 * PaymentSubmission মডেলে; Approve হলে তখনই এখানে একটা রেকর্ড তৈরি হয়
 * (দেখুন User::extendSubscription())।
 */
class SubscriptionPurchase extends Model
{
    protected $fillable = [
        'user_id', 'subscription_plan_id', 'amount_paid', 'payment_method', 'reference',
        'starts_at', 'ends_at', 'status', 'note', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'amount_paid' => 'decimal:2',
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

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
