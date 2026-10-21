@extends('layouts.app')
@section('title', 'ট্রানজেকশন হিস্ট্রি')

@section('content')
@php
    $user = auth()->user();
    $statusMap = [
        'pending' => ['যাচাইয়ের অপেক্ষায়', 'bg-amber-100 text-amber-700'],
        'approved' => ['অনুমোদিত', 'bg-emerald-100 text-emerald-700'],
        'rejected' => ['বাতিল', 'bg-rose-100 text-rose-700'],
    ];
    $platformColor = ['bkash' => 'bg-pink-100 text-pink-700', 'nagad' => 'bg-orange-100 text-orange-700', 'rocket' => 'bg-purple-100 text-purple-700'];
@endphp
<div class="max-w-4xl mx-auto" x-data="{ tab: 'payments' }">
    <div class="flex flex-wrap items-center gap-3 mb-5 anim-fade-up">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white text-2xl grid place-items-center shadow-lg shadow-emerald-500/30">🧾</div>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-slate-800">ট্রানজেকশন হিস্ট্রি</h1>
            <p class="text-sm text-slate-500">আপনার পেমেন্ট আবেদন, সাবস্ক্রিপশন ও ওয়ালেটের সব লেনদেন একসাথে।</p>
        </div>
        <div class="rounded-2xl bg-emerald-50 border border-emerald-100 px-4 py-2 text-sm text-emerald-800">👛 ব্যালেন্স: <b>{{ \App\Support\Bn::num($user->wallet_balance) }} ৳</b></div>
    </div>

    <div class="flex gap-1 p-1.5 bg-white rounded-2xl border border-slate-200 mb-4 overflow-x-auto anim-fade-up">
        @foreach ([['payments', '💳 পেমেন্ট আবেদন', $payments->count()], ['purchases', '💎 সাবস্ক্রিপশন', $purchases->count()], ['wallet', '👛 ওয়ালেট', $wallet->count()]] as [$key, $label, $count])
            <button type="button" @click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'bg-indigo-600 text-white shadow' : 'text-slate-500 hover:bg-slate-100'" class="flex-1 whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold transition">
                {{ $label }} <span class="opacity-70">({{ \App\Support\Bn::digits($count) }})</span>
            </button>
        @endforeach
    </div>

    {{-- পেমেন্ট আবেদন --}}
    <div x-show="tab === 'payments'" class="space-y-3">
        @forelse ($payments as $p)
            <div class="rounded-2xl bg-white border border-slate-200 p-4 flex flex-wrap items-center gap-3 anim-fade-up hover:shadow-md transition">
                <span class="text-xs font-bold px-3 py-1.5 rounded-full {{ $platformColor[$p->platform] ?? 'bg-slate-100 text-slate-600' }}">{{ $p->platformLabel() }}</span>
                <div class="flex-1 min-w-[180px]">
                    <p class="font-semibold text-slate-700 text-sm">{{ $p->plan?->name ?? 'প্যাকেজ' }} — {{ \App\Support\Bn::num($p->amount) }} ৳</p>
                    <p class="text-xs text-slate-400">TrxID: <span class="font-mono text-slate-500">{{ $p->transaction_id }}</span> · প্রেরক: {{ $p->sender_number }}</p>
                    @if ($p->status === 'rejected' && $p->admin_note)<p class="text-xs text-rose-500 mt-0.5">কারণ: {{ $p->admin_note }}</p>@endif
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ ($statusMap[$p->status] ?? $statusMap['pending'])[1] }}">{{ ($statusMap[$p->status] ?? $statusMap['pending'])[0] }}</span>
                    <p class="text-[11px] text-slate-400 mt-1">{{ $p->created_at->format('d M Y, h:i A') }}</p>
                </div>
            </div>
        @empty
            <div class="rounded-2xl bg-white border border-dashed border-slate-300 p-10 text-center text-slate-400">এখনো কোনো পেমেন্ট আবেদন নেই। <a href="{{ route('premium.index') }}" class="text-indigo-600 font-semibold hover:underline">প্যাকেজ দেখুন →</a></div>
        @endforelse
    </div>

    {{-- সাবস্ক্রিপশন --}}
    <div x-show="tab === 'purchases'" x-cloak class="space-y-3">
        @forelse ($purchases as $p)
            <div class="rounded-2xl bg-white border border-slate-200 p-4 flex flex-wrap items-center gap-3 anim-fade-up hover:shadow-md transition">
                <span class="w-10 h-10 rounded-xl bg-violet-100 grid place-items-center text-lg">💎</span>
                <div class="flex-1 min-w-[180px]">
                    <p class="font-semibold text-slate-700 text-sm">{{ $p->plan?->name ?? ($p->amount_paid > 0 ? 'ম্যানুয়াল প্যাকেজ' : 'গিফট / ফ্রি মেয়াদ') }}</p>
                    <p class="text-xs text-slate-400">{{ $p->starts_at->format('d M Y') }} → {{ $p->ends_at->format('d M Y') }} · {{ ucfirst($p->payment_method) }}</p>
                </div>
                <b class="text-slate-700 text-sm">{{ $p->amount_paid > 0 ? \App\Support\Bn::num($p->amount_paid).' ৳' : 'ফ্রি' }}</b>
            </div>
        @empty
            <div class="rounded-2xl bg-white border border-dashed border-slate-300 p-10 text-center text-slate-400">কোনো সাবস্ক্রিপশন ইতিহাস নেই।</div>
        @endforelse
    </div>

    {{-- ওয়ালেট --}}
    <div x-show="tab === 'wallet'" x-cloak class="space-y-3">
        @forelse ($wallet as $t)
            <div class="rounded-2xl bg-white border border-slate-200 p-4 flex items-center gap-3 anim-fade-up hover:shadow-md transition">
                <span class="w-10 h-10 rounded-xl grid place-items-center text-lg {{ $t->isCredit() ? 'bg-emerald-100' : 'bg-rose-100' }}">{{ $t->isCredit() ? '⬇️' : '⬆️' }}</span>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-700 text-sm truncate">{{ $t->reason }}</p>
                    <p class="text-xs text-slate-400">{{ $t->created_at->format('d M Y, h:i A') }} · ব্যালেন্স: {{ \App\Support\Bn::num($t->balance_after) }} ৳</p>
                </div>
                <b class="{{ $t->isCredit() ? 'text-emerald-600' : 'text-rose-600' }}">{{ $t->isCredit() ? '+' : '-' }}{{ \App\Support\Bn::num($t->amount) }} ৳</b>
            </div>
        @empty
            <div class="rounded-2xl bg-white border border-dashed border-slate-300 p-10 text-center text-slate-400">ওয়ালেটে এখনো কোনো লেনদেন নেই।</div>
        @endforelse
    </div>
</div>
@endsection
