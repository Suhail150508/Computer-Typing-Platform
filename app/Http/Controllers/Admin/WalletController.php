<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** সব ইউজারের ওয়ালেট লেনদেনের একত্র তালিকা (জমা/কর্তন বদলাতে হলে ইউজারের প্রোফাইল পেজ থেকে করতে হবে) */
class WalletController extends Controller
{
    public function index(Request $request): View
    {
        $type = in_array($request->query('type'), ['credit', 'debit'], true) ? $request->query('type') : null;
        $search = trim((string) $request->query('q', ''));

        $query = WalletTransaction::query()->with(['user:id,name,email,type_code', 'admin:id,name'])->latest('id');

        if ($type) {
            $query->where('type', $type);
        }

        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', $like)->orWhere('email', 'like', $like)
                ->orWhere('phone', 'like', $like)->orWhere('type_code', 'like', $like));
        }

        $transactions = $query->paginate(20)->withQueryString();

        $totals = [
            'balance' => (float) User::sum('wallet_balance'),
            'credit' => (float) WalletTransaction::where('type', 'credit')->sum('amount'),
            'debit' => (float) WalletTransaction::where('type', 'debit')->sum('amount'),
            'holders' => User::where('wallet_balance', '>', 0)->count(),
        ];

        return view('admin.wallets.index', compact('transactions', 'totals', 'type', 'search'));
    }
}
