<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $query = PaymentSubmission::with(['user', 'plan'])->latest();
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $submissions = $query->paginate(15)->withQueryString();
        $pendingCount = PaymentSubmission::where('status', 'pending')->count();

        return view('admin.payments.index', compact('submissions', 'status', 'pendingCount'));
    }

    /** অনুমোদন করলে প্ল্যানের মেয়াদ অনুযায়ী ইউজারের সাবস্ক্রিপশন বাড়িয়ে দেওয়া হয় */
    public function approve(Request $request, PaymentSubmission $payment): RedirectResponse
    {
        if (! $payment->isPending()) {
            return back()->with('error', 'এই আবেদনটি ইতিমধ্যে প্রসেস করা হয়েছে।');
        }

        $payment->user->extendSubscription(
            $payment->plan->duration_days,
            $payment->plan,
            (float) $payment->amount,
            $payment->platform,
            $request->user(),
            'পেমেন্ট আইডি: '.$payment->transaction_id
        );

        $payment->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'পেমেন্ট অনুমোদিত হয়েছে এবং ইউজারের একাউন্ট সক্রিয় করা হয়েছে।');
    }

    public function reject(Request $request, PaymentSubmission $payment): RedirectResponse
    {
        if (! $payment->isPending()) {
            return back()->with('error', 'এই আবেদনটি ইতিমধ্যে প্রসেস করা হয়েছে।');
        }

        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:255']]);

        $payment->update([
            'status' => 'rejected',
            'admin_note' => $data['admin_note'] ?? 'তথ্য মেলেনি বা পেমেন্ট পাওয়া যায়নি।',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'আবেদনটি বাতিল করা হয়েছে।');
    }
}
