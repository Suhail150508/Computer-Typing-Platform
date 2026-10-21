<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    /** ফিল্টার কী => ইউজার কুয়েরিতে প্রয়োগ */
    private function applyFilter($query, ?string $filter)
    {
        return match ($filter) {
            'trial' => $query->trialing(),
            'active' => $query->paying(),
            'expiring' => $query->expiringSoon(),
            'expired' => $query->withoutLiveSubscription()->where('is_active', true),
            'inactive' => $query->where('is_active', false),
            'admin' => $query->where('role', 'admin'),
            default => $query,
        };
    }

    private function applySearch($query, string $search)
    {
        if ($search === '') {
            return $query;
        }

        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';

        return $query->where(function ($q) use ($like) {
            $q->where('name', 'like', $like)->orWhere('email', 'like', $like)
                ->orWhere('phone', 'like', $like)->orWhere('type_code', 'like', $like);
        });
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $filter = $request->query('filter');

        $query = User::query()->withCount('typingAttempts')->latest();
        $this->applySearch($query, $search);
        $this->applyFilter($query, $filter);

        $users = $query->paginate(15)->withQueryString();

        // ফিল্টার চিপের কাউন্ট
        $counts = [
            'all' => User::count(),
            'trial' => User::trialing()->count(),
            'active' => User::paying()->count(),
            'expiring' => User::expiringSoon()->count(),
            'expired' => User::withoutLiveSubscription()->where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'admin' => User::where('role', 'admin')->count(),
        ];

        return view('admin.users.index', compact('users', 'search', 'filter', 'counts'));
    }

    /** বর্তমান ফিল্টার/সার্চ অনুযায়ী ইউজার তালিকা CSV হিসেবে ডাউনলোড (যেমন: শীঘ্রই মেয়াদ শেষ হবে এমন ইউজার) */
    public function export(Request $request): StreamedResponse
    {
        $query = User::query()->orderBy('id');
        $this->applySearch($query, trim((string) $request->query('q', '')));
        $this->applyFilter($query, $request->query('filter'));

        $name = 'users-'.($request->query('filter') ?: 'all').'-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // Excel-এ বাংলা ঠিকমতো দেখানোর জন্য UTF-8 BOM
            fputcsv($out, ['ID', 'Type Code', 'নাম', 'ইমেইল', 'ফোন', 'অবস্থা', 'মেয়াদ শেষ', 'ওয়ালেট', 'শেষ লগইন', 'যোগদান']);
            $query->chunkById(500, function ($users) use ($out) {
                foreach ($users as $u) {
                    fputcsv($out, [
                        $u->id, $u->type_code, $u->name, $u->email, $u->phone, $u->statusLabel(),
                        $u->subscription_expires_at?->format('Y-m-d'), $u->wallet_balance,
                        $u->last_login_at?->format('Y-m-d H:i'), $u->created_at->format('Y-m-d'),
                    ]);
                }
            });
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function show(User $user): View
    {
        $user->load([
            'walletTransactions' => fn ($q) => $q->latest()->take(20),
            'subscriptionPurchases' => fn ($q) => $q->with('plan')->latest()->take(20),
        ]);

        $stats = [
            'attempts' => $user->typingAttempts()->count(),
            'passed' => $user->typingAttempts()->where('result', 'pass')->count(),
            'best_wpm' => (float) $user->typingAttempts()->max('wpm'),
        ];

        $plans = SubscriptionPlan::active()->get();

        return view('admin.users.show', compact('user', 'stats', 'plans'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['role' => ['required', Rule::in(['user', 'admin'])]]);

        if ((int) $user->id === (int) $request->user()->id && $data['role'] !== 'admin') {
            return back()->with('error', 'নিজের অ্যাডমিন অ্যাক্সেস নিজে সরানো যাবে না।');
        }

        $user->update(['role' => $data['role']]);

        return back()->with('success', 'ইউজারের রোল আপডেট হয়েছে।');
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        if ((int) $user->id === (int) $request->user()->id) {
            return back()->with('error', 'নিজের একাউন্ট নিজে নিষ্ক্রিয় করা যাবে না।');
        }

        $request->validate(['reason' => ['nullable', 'string', 'max:255']]);

        if ($user->is_active) {
            $user->update([
                'is_active' => false,
                'deactivated_reason' => $request->input('reason') ?: null,
                'deactivated_at' => now(),
            ]);
        } else {
            $user->update(['is_active' => true, 'deactivated_reason' => null, 'deactivated_at' => null]);
        }

        return back()->with('success', $user->is_active ? 'একাউন্ট সক্রিয় করা হয়েছে।' : 'একাউন্ট বন্ধ করা হয়েছে — ইউজার আর লগইন করতে পারবে না।');
    }

    public function adjustWallet(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['credit', 'debit'])],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        if ($data['type'] === 'credit') {
            $user->creditWallet((float) $data['amount'], $data['reason'], $request->user());
        } else {
            $user->debitWallet((float) $data['amount'], $data['reason'], $request->user());
        }

        return back()->with('success', 'ওয়ালেট আপডেট হয়েছে।');
    }

    public function extendSubscription(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'subscription_plan_id' => ['nullable', 'exists:subscription_plans,id'],
            'days' => ['required', 'integer', 'min:1', 'max:3650'],
            'amount_paid' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $planId = $data['subscription_plan_id'] ?? null;
        $plan = $planId ? SubscriptionPlan::find($planId) : null;

        $user->extendSubscription(
            (int) $data['days'],
            $plan,
            (float) ($data['amount_paid'] ?? 0),
            'manual',
            $request->user(),
            $data['note'] ?? null
        );

        return back()->with('success', 'সাবস্ক্রিপশনের মেয়াদ বাড়ানো হয়েছে।');
    }

    public function reduceSubscription(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(['days', 'now'])],
            'days' => ['required_if:mode,days', 'nullable', 'integer', 'min:1', 'max:3650'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if (! $user->hasActiveSubscription()) {
            return back()->with('error', 'এই ইউজারের মেয়াদ আগেই শেষ — কমানোর কিছু নেই।');
        }

        $days = $data['mode'] === 'now' ? null : (int) $data['days'];
        $user->reduceSubscription($days, $request->user(), $data['note'] ?? null);

        return back()->with('success', $days === null ? 'মেয়াদ এখনই শেষ করা হয়েছে।' : "মেয়াদ {$days} দিন কমানো হয়েছে।");
    }
}
