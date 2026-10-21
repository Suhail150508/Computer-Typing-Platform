@extends('admin.layouts.app')
@section('title', $user->name)

@section('content')
<div class="max-w-5xl mx-auto">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-indigo-600 font-medium">← সব ইউজার</a>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 mt-3 mb-5">
        <div class="flex flex-wrap items-center gap-4">
            @include('partials.avatar', ['user' => $user, 'size' => 'w-14 h-14', 'text' => 'text-xl'])
            <div class="flex-1 min-w-[200px]">
                <h1 class="text-xl font-bold text-slate-800 flex flex-wrap items-center gap-2">{{ $user->name }}
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $user->statusClasses() }}">{{ $user->statusLabel() }}</span></h1>
                <p class="text-sm text-slate-500">{{ $user->email }} @if($user->phone) · {{ $user->phone }} @endif</p>
                <p class="text-xs text-slate-400 mt-0.5">Type Code: <b class="text-slate-600">{{ $user->type_code }}</b>
                    · যোগদান: {{ $user->created_at->format('d M Y') }}
                    · শেষ লগইন: {{ $user->last_login_at?->diffForHumans() ?? 'কখনো না' }}
                    @if ($user->institute) · {{ $user->institute }} @endif @if ($user->district) · {{ $user->district }} @endif</p>
            </div>

            <form method="POST" action="{{ route('admin.users.toggleActive', $user) }}" class="flex items-center gap-2"
                  @if ($user->is_active) onsubmit="var r = prompt('একাউন্ট বন্ধ করার কারণ লিখুন (ইউজার লগইনের সময় এটা দেখবে, ঐচ্ছিক):'); if (r === null) { return false; } this.reason.value = r; return true;" @endif>
                @csrf @method('PATCH')
                <input type="hidden" name="reason" value="">
                <button class="text-xs font-bold px-3 py-1.5 rounded-full {{ $user->is_active ? 'bg-rose-50 text-rose-600 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }}">
                    {{ $user->is_active ? 'নিষ্ক্রিয় করুন' : 'সক্রিয় করুন' }}
                </button>
            </form>

            <form method="POST" action="{{ route('admin.users.role', $user) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="role" value="{{ $user->role === 'admin' ? 'user' : 'admin' }}">
                <button class="text-xs font-bold px-3 py-1.5 rounded-full bg-indigo-50 text-indigo-600 hover:bg-indigo-100">
                    {{ $user->role === 'admin' ? 'অ্যাডমিন বাতিল করুন' : 'অ্যাডমিন বানান' }}
                </button>
            </form>
        </div>

        @unless ($user->is_active)
            <div class="mt-4 rounded-xl bg-slate-100 border border-slate-200 px-4 py-3 text-sm text-slate-600">
                🚫 এই একাউন্টটি বন্ধ আছে@if ($user->deactivated_at) ({{ $user->deactivated_at->format('d M Y, h:i A') }} থেকে)@endif।
                @if ($user->deactivated_reason) <b>কারণ:</b> {{ $user->deactivated_reason }} @endif
            </div>
        @endunless

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5">
            @foreach ([
                ['মোট টেস্ট', $stats['attempts'], '📄'],
                ['পাস', $stats['passed'], '✅'],
                ['সেরা WPM', \App\Support\Bn::num($stats['best_wpm']), '🚀'],
                ['ওয়ালেট', \App\Support\Bn::num($user->wallet_balance).' ৳', '👛'],
            ] as [$label, $value, $icon])
                <div class="rounded-xl bg-slate-50 p-3 text-center">
                    <div class="text-lg">{{ $icon }}</div>
                    <div class="font-bold text-slate-700">{{ $value }}</div>
                    <div class="text-xs text-slate-400">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-5">
        {{-- সাবস্ক্রিপশন --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-bold text-slate-700 mb-1">সাবস্ক্রিপশন</h2>
            <p class="text-sm mb-4">
                @if ($user->hasActiveSubscription())
                    <span class="text-emerald-600 font-semibold">সক্রিয়</span> — {{ $user->subscription_expires_at->format('d M Y') }} পর্যন্ত ({{ $user->daysLeft() }} দিন বাকি)
                @else
                    <span class="text-rose-600 font-semibold">মেয়াদ শেষ / নেই</span>
                @endif
            </p>

            <div class="flex flex-wrap gap-2 mb-4">
                <span class="text-xs font-semibold text-slate-400 self-center">দ্রুত গিফট:</span>
                @foreach ([7, 15, 30] as $d)
                    <form method="POST" action="{{ route('admin.users.subscription', $user) }}" onsubmit="return confirm('{{ $d }} দিনের ফ্রি মেয়াদ যোগ করবেন?')">
                        @csrf
                        <input type="hidden" name="days" value="{{ $d }}">
                        <input type="hidden" name="amount_paid" value="0">
                        <input type="hidden" name="note" value="অ্যাডমিন গিফট">
                        <button class="text-xs font-bold px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">+{{ $d }} দিন</button>
                    </form>
                @endforeach
            </div>

            <form method="POST" action="{{ route('admin.users.subscription', $user) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">প্ল্যান (ঐচ্ছিক)</label>
                    <select name="subscription_plan_id" class="w-full rounded-xl border-slate-200 text-sm">
                        <option value="">— ম্যানুয়াল (প্ল্যান ছাড়া) —</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" data-days="{{ $plan->duration_days }}" data-price="{{ $plan->price }}">{{ $plan->name }} ({{ $plan->duration_days }} দিন, {{ $plan->priceLabel() }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">দিন</label>
                        <input type="number" name="days" id="subDays" value="30" min="1" required class="w-full rounded-xl border-slate-200 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">পরিমাণ (৳, ঐচ্ছিক)</label>
                        <input type="number" step="0.01" name="amount_paid" id="subAmount" value="0" min="0" class="w-full rounded-xl border-slate-200 text-sm">
                    </div>
                </div>
                <input type="text" name="note" placeholder="নোট (ঐচ্ছিক)" class="w-full rounded-xl border-slate-200 text-sm">
                <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 text-sm font-semibold transition">মেয়াদ বাড়ান</button>
            </form>

            @if ($user->hasActiveSubscription())
                <div class="mt-5 pt-4 border-t border-slate-100">
                    <h3 class="text-sm font-bold text-rose-600 mb-2">⏳ মেয়াদ কমান</h3>
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach ([7, 15, 30] as $d)
                            <form method="POST" action="{{ route('admin.users.subscription.reduce', $user) }}" onsubmit="return confirm('{{ $d }} দিন মেয়াদ কমাবেন?')">
                                @csrf
                                <input type="hidden" name="mode" value="days">
                                <input type="hidden" name="days" value="{{ $d }}">
                                <button class="text-xs font-bold px-3 py-1.5 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 transition">−{{ $d }} দিন</button>
                            </form>
                        @endforeach
                        <form method="POST" action="{{ route('admin.users.subscription.reduce', $user) }}" onsubmit="return confirm('মেয়াদ এখনই শেষ করে দিবেন? ইউজার আর নতুন টেস্ট দিতে পারবে না (লগইন করতে পারবে)।')">
                            @csrf
                            <input type="hidden" name="mode" value="now">
                            <button class="text-xs font-bold px-3 py-1.5 rounded-full bg-slate-800 text-white hover:bg-slate-900 transition">এখনই শেষ করুন</button>
                        </form>
                    </div>
                    <form method="POST" action="{{ route('admin.users.subscription.reduce', $user) }}" class="flex gap-2" onsubmit="return confirm('মেয়াদ কমাবেন?')">
                        @csrf
                        <input type="hidden" name="mode" value="days">
                        <input type="number" name="days" min="1" max="3650" placeholder="কত দিন কমাবেন" required class="flex-1 min-w-0 rounded-xl border-slate-200 text-sm">
                        <input type="text" name="note" placeholder="কারণ (ঐচ্ছিক)" class="flex-1 min-w-0 rounded-xl border-slate-200 text-sm">
                        <button class="bg-rose-600 hover:bg-rose-700 text-white rounded-xl px-4 text-sm font-semibold transition">কমান</button>
                    </form>
                    <p class="text-[11px] text-slate-400 mt-2">যত দিন বাকি তার বেশি কমালে মেয়াদ এখনই শেষ হয়ে যাবে।</p>
                </div>
            @endif

            @if ($user->subscriptionPurchases->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <p class="text-xs font-semibold text-slate-400 mb-2">সাম্প্রতিক ইতিহাস</p>
                    <div class="space-y-1.5 max-h-40 overflow-y-auto text-xs">
                        @foreach ($user->subscriptionPurchases as $p)
                            <div class="flex justify-between text-slate-500">
                                <span class="{{ $p->status === 'cancelled' ? 'text-rose-500' : '' }}">{{ $p->status === 'cancelled' ? 'মেয়াদ কমানো' : ($p->plan?->name ?? 'ম্যানুয়াল') }} ({{ $p->payment_method }})</span>
                                <span>{{ $p->ends_at->format('d M Y') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- ওয়ালেট --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-bold text-slate-700 mb-1">ওয়ালেট</h2>
            <p class="text-sm mb-4">বর্তমান ব্যালেন্স: <b class="text-emerald-600">{{ \App\Support\Bn::num($user->wallet_balance) }} ৳</b></p>

            <form method="POST" action="{{ route('admin.users.wallet', $user) }}" class="space-y-3" x-data="{ type: 'credit' }">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center justify-center gap-1.5 rounded-xl border py-2 text-sm font-semibold cursor-pointer transition" :class="type === 'credit' ? 'bg-emerald-50 border-emerald-300 text-emerald-700' : 'border-slate-200 text-slate-500'">
                        <input type="radio" name="type" value="credit" x-model="type" class="accent-emerald-600"> জমা (Credit)
                    </label>
                    <label class="flex items-center justify-center gap-1.5 rounded-xl border py-2 text-sm font-semibold cursor-pointer transition" :class="type === 'debit' ? 'bg-rose-50 border-rose-300 text-rose-700' : 'border-slate-200 text-slate-500'">
                        <input type="radio" name="type" value="debit" x-model="type" class="accent-rose-600"> কর্তন (Debit)
                    </label>
                </div>
                <input type="number" step="0.01" name="amount" placeholder="পরিমাণ (৳)" min="0.01" required class="w-full rounded-xl border-slate-200 text-sm">
                <input type="text" name="reason" placeholder="কারণ লিখুন" required class="w-full rounded-xl border-slate-200 text-sm">
                <button class="w-full bg-slate-800 hover:bg-slate-900 text-white rounded-xl py-2.5 text-sm font-semibold transition">আপডেট করুন</button>
            </form>

            @if ($user->walletTransactions->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <p class="text-xs font-semibold text-slate-400 mb-2">সাম্প্রতিক লেনদেন</p>
                    <div class="space-y-1.5 max-h-40 overflow-y-auto text-xs">
                        @foreach ($user->walletTransactions as $t)
                            <div class="flex justify-between {{ $t->isCredit() ? 'text-emerald-600' : 'text-rose-600' }}">
                                <span>{{ $t->reason }}</span>
                                <span>{{ $t->isCredit() ? '+' : '-' }}{{ \App\Support\Bn::num($t->amount) }} ৳</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // প্ল্যান বেছে নিলে দিন ও পরিমাণ স্বয়ংক্রিয়ভাবে বসে যাবে (চাইলে বদলানো যাবে)
    document.querySelector('select[name="subscription_plan_id"]').addEventListener('change', function () {
        var opt = this.options[this.selectedIndex];
        if (opt.dataset.days) { document.getElementById('subDays').value = opt.dataset.days; }
        if (opt.dataset.price) { document.getElementById('subAmount').value = opt.dataset.price; }
    });
</script>
@endpush
