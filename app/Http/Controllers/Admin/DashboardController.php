<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use App\Models\PracticeCard;
use App\Models\SubscriptionPurchase;
use App\Models\TypingAttempt;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::count(),
            'active_subscribers' => User::whereNotNull('subscription_expires_at')->where('subscription_expires_at', '>', now())->count(),
            'expiring_soon' => User::expiringSoon()->where('is_active', true)->count(),
            'expired_users' => User::withoutLiveSubscription()->where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'pending_payments' => \App\Models\PaymentSubmission::where('status', 'pending')->count(),
            'new_feedback' => \Illuminate\Support\Facades\Schema::hasTable('feedback_reports') ? \App\Models\FeedbackReport::where('status', 'new')->count() : 0,
            'online_now' => \App\Models\ActivityLog::ready() ? \App\Models\ActivityLog::where('created_at', '>=', now()->subMinutes(5))->distinct()->count('visitor') : 0,
            'new_users_7d' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'total_attempts' => TypingAttempt::count(),
            'attempts_today' => TypingAttempt::whereDate('created_at', today())->count(),
            'pass_rate' => $this->passRate(),
            'total_labs' => Lab::count(),
            'total_practice_cards' => PracticeCard::count(),
            'wallet_total' => (float) User::sum('wallet_balance'),
            'revenue_30d' => (float) SubscriptionPurchase::where('created_at', '>=', now()->subDays(30))->sum('amount_paid'),
        ];

        // গত ৭ দিনের দৈনিক নতুন ইউজার ও টেস্ট সংখ্যা (চার্টের জন্য)
        $days = collect(range(6, 0))->map(fn ($i) => today()->subDays($i));
        $signupSeries = $days->map(fn ($d) => User::whereDate('created_at', $d)->count());
        $attemptSeries = $days->map(fn ($d) => TypingAttempt::whereDate('created_at', $d)->count());
        $labels = $days->map(fn ($d) => $d->format('d M'));

        $recentUsers = User::latest()->take(6)->get();
        $recentAttempts = TypingAttempt::with(['user', 'lab', 'practiceCard'])->latest()->take(8)->get();

        return view('admin.dashboard', compact('stats', 'labels', 'signupSeries', 'attemptSeries', 'recentUsers', 'recentAttempts'));
    }

    private function passRate(): int
    {
        $total = TypingAttempt::count();
        if ($total === 0) {
            return 0;
        }

        return (int) round(TypingAttempt::where('result', 'pass')->count() / $total * 100);
    }
}
