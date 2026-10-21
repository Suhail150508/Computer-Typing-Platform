<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'avatar', 'google_id', 'role',
        'wallet_balance', 'subscription_expires_at', 'is_active',
        'type_code', 'institute', 'district', 'password_set_at', 'last_login_at',
        'deactivated_reason', 'deactivated_at',
    ];

    /** অ্যাকাউন্টের অবস্থা → [বাংলা লেবেল, অ্যাডমিন ব্যাজের টেইলউইন্ড ক্লাস] */
    public const STATUSES = [
        'inactive' => ['বন্ধ (নিষ্ক্রিয়)', 'bg-slate-200 text-slate-700'],
        'expired' => ['মেয়াদ শেষ', 'bg-rose-100 text-rose-700'],
        'grace' => ['গ্রেস পিরিয়ড', 'bg-orange-100 text-orange-700'],
        'expiring' => ['শীঘ্রই শেষ হবে', 'bg-amber-100 text-amber-700'],
        'trial' => ['ফ্রি ট্রায়াল/গিফট', 'bg-sky-100 text-sky-700'],
        'active' => ['সক্রিয় (পেইড)', 'bg-emerald-100 text-emerald-700'],
    ];

    private ?bool $paidCache = null;

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'subscription_expires_at' => 'datetime',
            'password' => 'hashed',
            'wallet_balance' => 'decimal:2',
            'is_active' => 'boolean',
            'password_set_at' => 'datetime',
            'last_login_at' => 'datetime',
            'deactivated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // নতুন ইউজার তৈরির সময়ই ৬ সংখ্যার ইউনিক "টাইপ কোড" বসে যায়
        static::creating(function (User $user) {
            if (blank($user->type_code)) {
                $user->type_code = static::generateTypeCode();
            }
        });
    }

    public static function generateTypeCode(): string
    {
        do {
            $code = (string) random_int(100000, 999999);
        } while (static::where('type_code', $code)->exists());

        return $code;
    }

    /** প্রোফাইল ছবির URL (গুগলের লিংক বা আপলোড করা ফাইল); না থাকলে null */
    public function avatarUrl(): ?string
    {
        if (blank($this->avatar)) {
            return null;
        }

        return str_starts_with($this->avatar, 'http') ? $this->avatar : asset($this->avatar);
    }

    /** গুগল ইউজার যে পাসওয়ার্ড কখনো সেট করেনি */
    public function hasSetPassword(): bool
    {
        return $this->password_set_at !== null;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription_expires_at !== null && $this->subscription_expires_at->isFuture();
    }

    /** মেয়াদ শেষের পর গ্রেস পিরিয়ডের মধ্যে আছে কি না */
    public function isInGrace(): bool
    {
        $grace = Setting::typing('grace_days');

        return $grace > 0
            && $this->subscription_expires_at !== null
            && $this->subscription_expires_at->isPast()
            && $this->subscription_expires_at->copy()->addDays($grace)->isFuture();
    }

    /** টেস্ট দিতে পারবে কি না: সক্রিয় মেয়াদ অথবা গ্রেস পিরিয়ড */
    public function canPractice(): bool
    {
        return $this->hasActiveSubscription() || $this->isInGrace();
    }

    public function isExpiringSoon(): bool
    {
        return $this->hasActiveSubscription() && $this->daysLeft() <= Setting::typing('reminder_days');
    }

    /** কখনো টাকা দিয়ে প্যাকেজ কিনেছে কি না (গিফট/ফ্রি ট্রায়াল বাদে) */
    public function hasPaid(): bool
    {
        return $this->paidCache ??= $this->subscriptionPurchases()->where('amount_paid', '>', 0)->exists();
    }

    /** inactive | expired | grace | expiring | trial | active */
    public function accountStatus(): string
    {
        return match (true) {
            ! $this->is_active => 'inactive',
            $this->isInGrace() => 'grace',
            ! $this->hasActiveSubscription() => 'expired',
            $this->isExpiringSoon() => 'expiring',
            $this->hasPaid() => 'active',
            default => 'trial',
        };
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->accountStatus()][0];
    }

    public function statusClasses(): string
    {
        return self::STATUSES[$this->accountStatus()][1];
    }

    // ---------- কুয়েরি স্কোপ (অ্যাডমিন ফিল্টার ও কাউন্টের জন্য) ----------

    public function scopeWithLiveSubscription($q)
    {
        return $q->whereNotNull('subscription_expires_at')->where('subscription_expires_at', '>', now());
    }

    public function scopeWithoutLiveSubscription($q)
    {
        return $q->where(function ($w) {
            $w->whereNull('subscription_expires_at')->orWhere('subscription_expires_at', '<=', now());
        });
    }

    public function scopeExpiringSoon($q)
    {
        return $q->whereNotNull('subscription_expires_at')
            ->whereBetween('subscription_expires_at', [now(), now()->addDays(Setting::typing('reminder_days'))]);
    }

    public function scopePaying($q)
    {
        return $q->withLiveSubscription()->whereHas('subscriptionPurchases', fn ($p) => $p->where('amount_paid', '>', 0));
    }

    public function scopeTrialing($q)
    {
        return $q->withLiveSubscription()->whereDoesntHave('subscriptionPurchases', fn ($p) => $p->where('amount_paid', '>', 0));
    }

    public function daysLeft(): int
    {
        if (! $this->hasActiveSubscription()) {
            return 0;
        }

        return (int) ceil(now()->diffInDays($this->subscription_expires_at, false));
    }

    public function typingAttempts(): HasMany
    {
        return $this->hasMany(TypingAttempt::class);
    }

    public function subscriptionPurchases(): HasMany
    {
        return $this->hasMany(SubscriptionPurchase::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function paymentSubmissions(): HasMany
    {
        return $this->hasMany(PaymentSubmission::class);
    }

    /**
     * ওয়ালেটে টাকা যোগ করে এবং লেজারে একটা এন্ট্রি রাখে — পেমেন্ট গেটওয়ে
     * যুক্ত হলে সফল পেমেন্টের পরও এই একই মেথড কল করলেই চলবে।
     */
    public function creditWallet(float $amount, string $reason, ?User $actor = null, ?string $reference = null): WalletTransaction
    {
        return DB::transaction(function () use ($amount, $reason, $actor, $reference) {
            $this->increment('wallet_balance', $amount);
            $this->refresh();

            return $this->walletTransactions()->create([
                'type' => 'credit',
                'amount' => $amount,
                'balance_after' => $this->wallet_balance,
                'reason' => $reason,
                'reference' => $reference,
                'created_by' => $actor?->id,
            ]);
        });
    }

    /**
     * ওয়ালেট থেকে টাকা কাটে (ব্যালেন্স ঋণাত্মক হতে দেওয়া হয় না)।
     */
    public function debitWallet(float $amount, string $reason, ?User $actor = null, ?string $reference = null): WalletTransaction
    {
        return DB::transaction(function () use ($amount, $reason, $actor, $reference) {
            $amount = min($amount, (float) $this->wallet_balance);
            $this->decrement('wallet_balance', $amount);
            $this->refresh();

            return $this->walletTransactions()->create([
                'type' => 'debit',
                'amount' => $amount,
                'balance_after' => $this->wallet_balance,
                'reason' => $reason,
                'reference' => $reference,
                'created_by' => $actor?->id,
            ]);
        });
    }

    /**
     * সাবস্ক্রিপশনের মেয়াদ বাড়ায় (বর্তমান মেয়াদ চলমান থাকলে তার উপর যোগ হয়,
     * নাহলে আজ থেকে শুরু হয়) এবং একটা পার্চেজ রেকর্ড রাখে — ম্যানুয়াল গ্রান্ট
     * ও ভবিষ্যতের পেমেন্ট গেটওয়ে, দুটোতেই এই একই মেথড ব্যবহার করা যাবে।
     */
    public function extendSubscription(
        int $days,
        ?SubscriptionPlan $plan = null,
        float $amountPaid = 0,
        string $paymentMethod = 'manual',
        ?User $actor = null,
        ?string $note = null
    ): SubscriptionPurchase {
        return DB::transaction(function () use ($days, $plan, $amountPaid, $paymentMethod, $actor, $note) {
            $base = $this->hasActiveSubscription() ? $this->subscription_expires_at : now();
            $endsAt = $base->copy()->addDays($days);

            $this->forceFill(['subscription_expires_at' => $endsAt])->save();

            return $this->subscriptionPurchases()->create([
                'subscription_plan_id' => $plan?->id,
                'amount_paid' => $amountPaid,
                'payment_method' => $paymentMethod,
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'status' => 'active',
                'note' => $note,
                'created_by' => $actor?->id,
            ]);
        });
    }

    /**
     * মেয়াদ কমায় ($days = null হলে এখনই শেষ করে দেয়)। মেয়াদ আগেই শেষ থাকলে কিছু বদলায় না।
     * অডিটের জন্য "cancelled" স্ট্যাটাসে একটা রেকর্ড রাখা হয় (টাকার অঙ্ক ০, তাই পেইড/ট্রায়াল গণনায় প্রভাব পড়ে না)।
     */
    public function reduceSubscription(?int $days, ?User $actor = null, ?string $note = null): ?SubscriptionPurchase
    {
        if (! $this->hasActiveSubscription()) {
            return null;
        }

        return DB::transaction(function () use ($days, $actor, $note) {
            $endsAt = $days === null
                ? now()
                : $this->subscription_expires_at->copy()->subDays($days);

            if ($endsAt->isFuture() === false) {
                $endsAt = now(); // ভবিষ্যতের নিচে নামলে "এখনই শেষ"
            }

            $this->forceFill(['subscription_expires_at' => $endsAt])->save();

            return $this->subscriptionPurchases()->create([
                'amount_paid' => 0,
                'payment_method' => 'manual',
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'status' => 'cancelled',
                'note' => $note ?: ($days === null ? 'অ্যাডমিন: মেয়াদ এখনই শেষ করা হয়েছে' : "অ্যাডমিন: মেয়াদ {$days} দিন কমানো হয়েছে"),
                'created_by' => $actor?->id,
            ]);
        });
    }

    /** পাসওয়ার্ড রিসেটের বাংলা ইমেইল */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
