@extends('layouts.app')
@section('title', 'পেমেন্ট করুন')

@section('content')
@php
    $platforms = [
        'bkash' => ['label' => 'bKash', 'color' => 'pink', 'icon' => '📱'],
        'nagad' => ['label' => 'Nagad', 'color' => 'orange', 'icon' => '🧡'],
        'rocket' => ['label' => 'Rocket', 'color' => 'purple', 'icon' => '🚀'],
    ];
    $instructions = \App\Models\Setting::get('payment_instructions')
        ?: 'নির্দিষ্ট নম্বরে "Send Money" অপশনে গিয়ে সঠিক পরিমাণ টাকা পাঠান। পাঠানোর পর যে মোবাইল নম্বর থেকে পাঠিয়েছেন ও Transaction ID নিচের ফর্মে দিন।';
@endphp

<div class="max-w-3xl mx-auto" x-data="{ platform: '{{ old('platform', '') }}' }">
    <a href="{{ route('premium.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-violet-600 mb-4 transition">← সব প্যাকেজ</a>

    {{-- নির্বাচিত প্যাকেজ --}}
    <div class="rounded-2xl bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white p-5 mb-6 anim-fade-up flex items-center justify-between flex-wrap gap-3">
        <div>
            <p class="text-violet-100 text-xs">নির্বাচিত প্যাকেজ</p>
            <h1 class="text-xl font-bold">{{ $plan->name }} — {{ \App\Support\Bn::digits($plan->duration_days) }} দিন</h1>
        </div>
        <div class="text-2xl font-extrabold">{{ $plan->priceLabel() }}</div>
    </div>

    <form method="POST" action="{{ route('premium.submit', $plan) }}" class="space-y-6">
        @csrf

        {{-- ধাপ ১: প্ল্যাটফর্ম --}}
        <div class="rounded-2xl bg-white border border-slate-200 p-5 anim-fade-up">
            <h2 class="font-bold text-slate-800 mb-1">১. পেমেন্ট প্ল্যাটফর্ম বেছে নিন</h2>
            <p class="text-xs text-slate-400 mb-4">যেটাতে আপনার একাউন্ট আছে সেটায় ক্লিক করুন</p>

            <div class="grid grid-cols-3 gap-3">
                @foreach ($platforms as $key => $p)
                    <label class="cursor-pointer">
                        <input type="radio" name="platform" value="{{ $key }}" class="sr-only" x-model="platform">
                        <div class="rounded-xl border-2 p-4 text-center transition"
                             :class="platform === '{{ $key }}' ? 'border-{{ $p['color'] }}-500 bg-{{ $p['color'] }}-50 shadow-md scale-[1.03]' : 'border-slate-200 hover:border-{{ $p['color'] }}-300'">
                            <div class="text-2xl mb-1">{{ $p['icon'] }}</div>
                            <div class="font-bold text-sm">{{ $p['label'] }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('platform') <p class="text-rose-600 text-xs mt-2">⚠ {{ $message }}</p> @enderror
        </div>

        {{-- ধাপ ২: পেমেন্ট নির্দেশনা (প্ল্যাটফর্ম বাছার পর দেখাবে) --}}
        <div x-show="platform" x-cloak x-transition class="rounded-2xl bg-white border border-slate-200 p-5 anim-pop">
            <h2 class="font-bold text-slate-800 mb-3">২. টাকা পাঠান</h2>

            @foreach ($platforms as $key => $p)
                <div x-show="platform === '{{ $key }}'" x-cloak class="rounded-xl bg-{{ $p['color'] }}-50 border border-{{ $p['color'] }}-100 p-4">
                    <p class="text-sm text-slate-600">
                        <b>{{ $p['label']}} Personal</b> নম্বরে <b>{{ $plan->priceLabel() }}</b> টাকা <b>"Send Money"</b> করুন:
                    </p>
                    <div class="mt-2 flex items-center gap-2">
                        <span class="font-mono text-lg font-bold bg-white rounded-lg px-4 py-2 border border-{{ $p['color'] }}-200 tracking-wider">{{ $paymentNumbers[$key] ?? '—' }}</span>
                        <button type="button" class="copy-btn text-xs bg-white border border-{{ $p['color'] }}-200 rounded-lg px-3 py-2 hover:bg-{{ $p['color'] }}-100 transition" data-copy="{{ $paymentNumbers[$key] ?? '' }}">কপি করুন</button>
                    </div>
                </div>
            @endforeach

            <p class="text-xs text-slate-500 mt-3 leading-relaxed">{{ $instructions }}</p>
        </div>

        {{-- ধাপ ৩: ট্রানজেকশন তথ্য --}}
        <div x-show="platform" x-cloak x-transition class="rounded-2xl bg-white border border-slate-200 p-5 anim-pop">
            <h2 class="font-bold text-slate-800 mb-4">৩. ট্রানজেকশনের তথ্য দিন</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-1.5" for="sender_number">যে নম্বর থেকে টাকা পাঠিয়েছেন</label>
                    <input id="sender_number" name="sender_number" value="{{ old('sender_number') }}" class="field" placeholder="01XXXXXXXXX" inputmode="numeric" required>
                    @error('sender_number') <p class="text-rose-600 text-xs mt-1">⚠ {{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5" for="transaction_id">Transaction ID</label>
                    <input id="transaction_id" name="transaction_id" value="{{ old('transaction_id') }}" class="field" placeholder="যেমন 8N7A3XQ2Z1" required>
                    @error('transaction_id') <p class="text-rose-600 text-xs mt-1">⚠ {{ $message }}</p> @enderror
                </div>
            </div>

            <button type="submit" :disabled="!platform" class="w-full mt-5 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white font-bold py-3 shadow-lg shadow-fuchsia-300/50 hover:shadow-xl transition disabled:opacity-50 disabled:cursor-not-allowed">
                জমা দিন — অ্যাডমিন যাচাই করবেন
            </button>
            <p class="text-xs text-slate-400 text-center mt-2">ভুল তথ্য দিলে আবেদন বাতিল হতে পারে। সঠিক নম্বর ও Transaction ID দিন।</p>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.copy-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var text = btn.getAttribute('data-copy');
            if (!text) { return; }
            navigator.clipboard?.writeText(text).then(function () {
                var old = btn.textContent;
                btn.textContent = 'কপি হয়েছে ✓';
                setTimeout(function () { btn.textContent = old; }, 1500);
            });
        });
    });
</script>
@endpush
@endsection
