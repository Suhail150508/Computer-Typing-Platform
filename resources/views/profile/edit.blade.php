@extends('layouts.app')
@section('title', 'আমার প্রোফাইল')

@section('content')
@php
    $in = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm placeholder-slate-300 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-400 transition';
    $status = $user->accountStatus();
    $barColor = ['active' => 'bg-emerald-500', 'trial' => 'bg-sky-500', 'expiring' => 'bg-amber-500', 'grace' => 'bg-orange-500', 'expired' => 'bg-rose-500', 'inactive' => 'bg-slate-400'][$status];
    $startTab = ($errors->has('current_password') || $errors->has('password')) ? 'password' : 'info';
@endphp

<div class="max-w-5xl mx-auto" x-data="{ tab: location.hash === '#password' ? 'password' : '{{ $startTab }}' }">

    {{-- হিরো / ছবি --}}
    <div class="relative rounded-3xl bg-white border border-slate-200 shadow-sm overflow-hidden mb-5 anim-fade-up">
        <div class="relative h-28 sm:h-36 bg-gradient-to-br from-indigo-700 via-blue-600 to-sky-500 overflow-hidden">
            <div class="hero-blob w-40 h-40 bg-pink-400 -top-12 right-10"></div>
            <div class="hero-blob w-32 h-32 bg-amber-300 -bottom-10 left-1/3" style="animation-delay: 2s"></div>
            <div class="hero-blob w-24 h-24 bg-emerald-300 top-2 left-6" style="animation-delay: 4s"></div>
        </div>

        <div class="px-4 sm:px-8 pb-6">
            <div class="flex flex-wrap items-end gap-4 -mt-12 sm:-mt-14">
                {{-- ছবি আপলোড --}}
                <div class="relative group">
                    <div id="avatarBox" class="rounded-full ring-4 ring-white shadow-lg">
                        @include('partials.avatar', ['user' => $user, 'size' => 'w-24 h-24 sm:w-28 sm:h-28', 'text' => 'text-4xl'])
                    </div>
                    <form id="avatarForm" method="POST" action="{{ route('profile.avatar.upload') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="avatar" id="avatarInput" accept="image/png,image/jpeg,image/webp" class="hidden">
                        <button type="button" onclick="document.getElementById('avatarInput').click()"
                                class="absolute bottom-1 right-1 w-9 h-9 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white grid place-items-center shadow-lg ring-2 ring-white transition hover:scale-110"
                                title="ছবি পরিবর্তন করুন" aria-label="ছবি পরিবর্তন করুন">📷</button>
                    </form>
                </div>

                <div class="flex-1 min-w-[220px] pt-2 sm:pt-0">
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-800 flex flex-wrap items-center gap-2">
                        {{ $user->name }}
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $user->statusClasses() }}">{{ $user->statusLabel() }}</span>
                    </h1>
                    <p class="text-sm text-slate-500 mt-0.5">{{ $user->email }}@if ($user->phone) · {{ $user->phone }}@endif</p>
                    <div class="flex flex-wrap items-center gap-2 mt-2 text-xs text-slate-500">
                        <span class="inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 font-bold rounded-full px-3 py-1" x-data="{ copied: false }">
                            TYPE CODE
                            <button type="button" class="bg-indigo-600 text-white rounded-full px-2.5 py-0.5 hover:bg-indigo-700 transition"
                                    @click="navigator.clipboard && navigator.clipboard.writeText('{{ $user->type_code }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                    title="কপি করুন"><span x-text="copied ? 'কপি হয়েছে ✓' : '{{ $user->type_code }}'">{{ $user->type_code }}</span></button>
                        </span>
                        <span>📅 যোগদান: {{ $user->created_at->format('d M Y') }}</span>
                        @if ($user->last_login_at)<span>🕒 শেষ লগইন: {{ $user->last_login_at->diffForHumans() }}</span>@endif
                    </div>
                </div>

                @if ($user->avatar)
                    <form method="POST" action="{{ route('profile.avatar.remove') }}" onsubmit="return confirm('প্রোফাইল ছবি সরাতে চান?')">
                        @csrf @method('DELETE')
                        <button class="text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-full px-3 py-1.5 transition">ছবি সরান</button>
                    </form>
                @endif
            </div>
            @error('avatar')<p class="text-sm text-rose-600 mt-3">⚠ {{ $message }}</p>@enderror
            <p id="avatarErr" class="text-sm text-rose-600 mt-3 hidden"></p>
        </div>
    </div>

    {{-- পরিসংখ্যান --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        @foreach ([
            ['মোট টেস্ট', $stats['attempts'], 0, '📄', 'from-indigo-50'],
            ['পাস করেছেন', $stats['passed'], 0, '✅', 'from-emerald-50'],
            ['সেরা WPM', $stats['best_wpm'], 1, '🚀', 'from-amber-50'],
            ['গড় নির্ভুলতা (%)', $stats['avg_accuracy'], 1, '🎯', 'from-rose-50'],
        ] as $i => [$label, $value, $dec, $icon, $grad])
            <div class="anim-fade-up rounded-2xl bg-gradient-to-br {{ $grad }} to-white border border-slate-200 p-4 hover:-translate-y-0.5 hover:shadow-md transition" style="animation-delay: {{ $i * 70 }}ms">
                <div class="text-xl">{{ $icon }}</div>
                <div class="text-2xl font-bold text-slate-800 mt-1"><span class="count-up" data-target="{{ $value }}" data-decimals="{{ $dec }}">0</span></div>
                <div class="text-xs text-slate-500">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-5">
        {{-- বাম: ট্যাব ফর্ম --}}
        <div class="lg:col-span-2 rounded-3xl bg-white border border-slate-200 shadow-sm anim-fade-up">
            <div class="flex gap-1 p-2 border-b border-slate-100">
                <button type="button" @click="tab = 'info'" :class="tab === 'info' ? 'bg-indigo-600 text-white shadow' : 'text-slate-500 hover:bg-slate-100'" class="flex-1 sm:flex-none rounded-xl px-5 py-2 text-sm font-semibold transition">👤 প্রোফাইল তথ্য</button>
                <button type="button" @click="tab = 'password'" :class="tab === 'password' ? 'bg-indigo-600 text-white shadow' : 'text-slate-500 hover:bg-slate-100'" class="flex-1 sm:flex-none rounded-xl px-5 py-2 text-sm font-semibold transition">🔒 পাসওয়ার্ড</button>
            </div>

            {{-- তথ্য --}}
            <form x-show="tab === 'info'" method="POST" action="{{ route('profile.update') }}" class="p-5 sm:p-6 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">পূর্ণ নাম *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="100" class="{{ $in }}">
                    @error('name')<p class="text-xs text-rose-600 mt-1">⚠ {{ $message }}</p>@enderror
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">মোবাইল নম্বর</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="01712345678" inputmode="numeric" maxlength="11" class="{{ $in }}">
                        @error('phone')<p class="text-xs text-rose-600 mt-1">⚠ {{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">ইমেইল</label>
                        <input type="email" value="{{ $user->email }}" disabled class="{{ $in }} bg-slate-50 text-slate-400 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">প্রতিষ্ঠান / অফিস</label>
                        <input type="text" name="institute" value="{{ old('institute', $user->institute) }}" maxlength="120" placeholder="যেমন: ঢাকা কলেজ" class="{{ $in }}">
                        @error('institute')<p class="text-xs text-rose-600 mt-1">⚠ {{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">জেলা</label>
                        <input type="text" name="district" value="{{ old('district', $user->district) }}" maxlength="60" placeholder="যেমন: ঢাকা" class="{{ $in }}">
                        @error('district')<p class="text-xs text-rose-600 mt-1">⚠ {{ $message }}</p>@enderror
                    </div>
                </div>
                <button class="w-full sm:w-auto bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-semibold rounded-xl px-8 py-2.5 text-sm shadow-lg shadow-indigo-500/20 transition active:scale-95">সংরক্ষণ করুন</button>
            </form>

            {{-- পাসওয়ার্ড --}}
            <form x-show="tab === 'password'" x-cloak id="password" method="POST" action="{{ route('profile.password') }}" class="p-5 sm:p-6 space-y-4">
                @csrf @method('PUT')
                @unless ($user->hasSetPassword())
                    <div class="rounded-xl bg-sky-50 border border-sky-100 text-sky-700 text-sm px-4 py-3">ℹ️ আপনি গুগল দিয়ে একাউন্ট খুলেছেন। চাইলে এখানে একটি পাসওয়ার্ড সেট করে ইমেইল/পাসওয়ার্ড দিয়েও লগইন করতে পারবেন।</div>
                @else
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">বর্তমান পাসওয়ার্ড *</label>
                        <input type="password" name="current_password" autocomplete="current-password" class="{{ $in }}">
                        @error('current_password')<p class="text-xs text-rose-600 mt-1">⚠ {{ $message }}</p>@enderror
                    </div>
                @endunless
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">নতুন পাসওয়ার্ড *</label>
                        <input type="password" name="password" minlength="6" autocomplete="new-password" class="{{ $in }}">
                        @error('password')<p class="text-xs text-rose-600 mt-1">⚠ {{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">নতুন পাসওয়ার্ড আবার দিন *</label>
                        <input type="password" name="password_confirmation" minlength="6" autocomplete="new-password" class="{{ $in }}">
                    </div>
                </div>
                <button class="w-full sm:w-auto bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold rounded-xl px-8 py-2.5 text-sm shadow-lg shadow-amber-500/20 transition active:scale-95">পাসওয়ার্ড পরিবর্তন করুন</button>
            </form>
        </div>

        {{-- ডান: সাবস্ক্রিপশন --}}
        <div class="space-y-5">
            <div class="rounded-3xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/20 p-5 anim-fade-up">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold">👛 আমার ওয়ালেট</h2>
                    <a href="{{ route('transactions.index') }}" class="text-xs bg-white/20 hover:bg-white/30 rounded-full px-3 py-1 transition">সব লেনদেন →</a>
                </div>
                <p class="text-4xl font-bold mt-3">{{ \App\Support\Bn::num($user->wallet_balance) }} <span class="text-lg font-medium opacity-80">৳</span></p>
                @if ($recentWallet->isNotEmpty())
                    <div class="mt-4 pt-3 border-t border-white/20 space-y-1.5 text-xs">
                        @foreach ($recentWallet as $t)
                            <div class="flex justify-between gap-3">
                                <span class="truncate opacity-90">{{ $t->reason ?? ($t->isCredit() ? 'জমা' : 'কর্তন') }}</span>
                                <b class="shrink-0">{{ $t->isCredit() ? '+' : '-' }}{{ \App\Support\Bn::num($t->amount) }} ৳</b>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs opacity-80 mt-3">এখনো কোনো ওয়ালেট লেনদেন নেই। অ্যাডমিন ব্যালেন্স যোগ করলে এখানে দেখাবে।</p>
                @endif
            </div>

            <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-5 anim-fade-up">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-bold text-slate-700">💎 সাবস্ক্রিপশন</h2>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $user->statusClasses() }}">{{ $user->statusLabel() }}</span>
                </div>

                @if ($user->hasActiveSubscription())
                    <p class="text-3xl font-bold text-slate-800">{{ \App\Support\Bn::digits($user->daysLeft()) }} <span class="text-base font-medium text-slate-500">দিন বাকি</span></p>
                    <p class="text-xs text-slate-400 mt-0.5">মেয়াদ শেষ: {{ $user->subscription_expires_at->format('d M Y') }}</p>
                    <div class="h-2.5 rounded-full bg-slate-100 mt-4 overflow-hidden"><div class="h-full rounded-full {{ $barColor }} transition-all duration-1000" style="width: {{ max(4, $progress) }}%"></div></div>
                @else
                    <p class="text-lg font-bold text-rose-600">মেয়াদ শেষ</p>
                    <p class="text-xs text-slate-500 mt-0.5">@if ($user->subscription_expires_at) {{ $user->subscription_expires_at->format('d M Y') }} তারিখে শেষ হয়েছে @else এখনো কোনো প্যাকেজ নেই @endif</p>
                    <p class="text-xs text-slate-500 mt-3">আপনার রেজাল্ট ও প্রগ্রেস সম্পূর্ণ সুরক্ষিত আছে — রিনিউ করলেই আবার টেস্ট দিতে পারবেন।</p>
                @endif

                <a href="{{ route('premium.index') }}" class="mt-4 block text-center bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-700 hover:to-fuchsia-700 text-white font-semibold rounded-xl py-2.5 text-sm shadow-lg shadow-fuchsia-500/20 transition active:scale-95">
                    {{ $user->hasActiveSubscription() ? 'প্যাকেজ রিনিউ / আপগ্রেড' : 'একাউন্ট সক্রিয় করুন' }}
                </a>
            </div>

            <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-5 anim-fade-up">
                <h2 class="font-bold text-slate-700 mb-3">দ্রুত লিংক</h2>
                <div class="space-y-1">
                    @foreach ([
                        ['wallet', '👛', 'ওয়ালেট ব্যালেন্স', \App\Support\Bn::num($user->wallet_balance).' ৳', route('transactions.index')],
                        ['tx', '🧾', 'ট্রানজেকশন হিস্ট্রি', '', route('transactions.index')],
                        ['res', '🏆', 'আমার রেজাল্ট', '', route('results.index')],
                        ['wpm', '📈', 'WPM Viewer', '', route('wpm.index')],
                    ] as [$k, $ico, $lbl, $val, $url])
                        <a href="{{ $url }}" class="flex items-center gap-3 rounded-xl px-2 py-2 hover:bg-slate-50 transition text-sm text-slate-700">
                            <span class="w-8 h-8 rounded-lg bg-slate-100 grid place-items-center">{{ $ico }}</span>
                            <span class="flex-1 font-medium">{{ $lbl }}</span>
                            @if ($val)<b class="text-emerald-600">{{ $val }}</b>@else<span class="text-slate-300">→</span>@endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ছবি বেছে নিলে সাইজ/টাইপ যাচাই করে সাথে সাথে আপলোড হয়ে যাবে
    (function () {
        var input = document.getElementById('avatarInput');
        var err = document.getElementById('avatarErr');
        var maxKb = {{ (int) config('typing.avatar_max_kb') }};
        input.addEventListener('change', function () {
            var f = input.files[0];
            if (!f) { return; }
            err.classList.add('hidden');
            if (!/^image\/(png|jpe?g|webp)$/.test(f.type)) {
                err.textContent = '⚠ শুধু JPG, PNG বা WEBP ছবি দেওয়া যাবে।'; err.classList.remove('hidden'); input.value = ''; return;
            }
            if (f.size > maxKb * 1024) {
                err.textContent = '⚠ ছবির সাইজ সর্বোচ্চ ' + (maxKb / 1024).toFixed(1) + ' MB হতে পারবে।'; err.classList.remove('hidden'); input.value = ''; return;
            }
            document.getElementById('avatarBox').style.opacity = '.5';
            document.getElementById('avatarForm').submit();
        });
    })();
</script>
@endpush
