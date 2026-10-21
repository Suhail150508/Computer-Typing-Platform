<?php

namespace App\Http\Controllers;

use App\Models\PaymentSubmission;
use App\Models\Setting;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PremiumController extends Controller
{
    /** ধাপ ১: প্যাকেজ সিলেকশন + সাম্প্রতিক সাবমিশনের স্ট্যাটাস */
    public function index(Request $request): View
    {
        $plans = SubscriptionPlan::active()->get();
        $pending = $request->user()->paymentSubmissions()->with('plan')->latest()->first();

        return view('premium.index', compact('plans', 'pending'));
    }

    /** ধাপ ২-৪: প্ল্যাটফর্ম বেছে টাকা পাঠানোর নির্দেশনা ও ফর্ম */
    public function show(Request $request, SubscriptionPlan $plan): View|RedirectResponse
    {
        abort_unless($plan->is_active, 404);

        if ($request->user()->paymentSubmissions()->where('status', 'pending')->exists()) {
            return redirect()->route('premium.index')->with('error', 'আপনার একটি পেমেন্ট যাচাইয়ের অপেক্ষায় আছে। সেটি অনুমোদন না হওয়া পর্যন্ত নতুন আবেদন করা যাবে না।');
        }

        $paymentNumbers = Setting::paymentNumbers();

        return view('premium.show', compact('plan', 'paymentNumbers'));
    }

    /** ধাপ ৫: ট্রানজেকশন তথ্য জমা — অ্যাডমিন যাচাইয়ের অপেক্ষায় থাকবে */
    public function submit(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        abort_unless($plan->is_active, 404);

        if ($request->user()->paymentSubmissions()->where('status', 'pending')->exists()) {
            return redirect()->route('premium.index')->with('error', 'আপনার একটি পেমেন্ট ইতিমধ্যে যাচাইয়ের অপেক্ষায় আছে।');
        }

        $data = $request->validate([
            'platform' => ['required', Rule::in(PaymentSubmission::PLATFORMS)],
            'sender_number' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'transaction_id' => ['required', 'string', 'min:4', 'max:50'],
        ], [
            'sender_number.regex' => 'সঠিক ১১ ডিজিটের মোবাইল নম্বর দিন (যেমন 01712345678)।',
        ]);

        $request->user()->paymentSubmissions()->create([
            'subscription_plan_id' => $plan->id,
            'platform' => $data['platform'],
            'sender_number' => $data['sender_number'],
            'transaction_id' => $data['transaction_id'],
            'amount' => $plan->price,
            'status' => 'pending',
        ]);

        \App\Models\ActivityLog::record('payment', "পেমেন্ট জমা দিয়েছেন: {$plan->price} টাকা ({$data['platform']})", [], $request);

        return redirect()->route('premium.index')
            ->with('success', 'আপনার পেমেন্টের তথ্য জমা হয়েছে। অ্যাডমিন যাচাই করে শীঘ্রই আপনার একাউন্ট সক্রিয় করে দেবেন।');
    }
}
