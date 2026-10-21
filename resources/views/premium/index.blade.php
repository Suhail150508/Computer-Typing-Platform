@extends('layouts.app')
@section('title', 'প্রিমিয়াম প্যাকেজ')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="text-center mb-8 anim-fade-up">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-500 text-3xl shadow-lg shadow-fuchsia-200 mb-3">💎</div>
        <h1 class="text-2xl sm:text-3xl font-bold">প্রিমিয়াম প্যাকেজ</h1>
        <p class="text-slate-500 text-sm mt-2 max-w-xl mx-auto">একটা প্যাকেজ বেছে নিন, bKash/Nagad/Rocket দিয়ে পেমেন্ট করুন — অ্যাডমিন যাচাই করে কিছুক্ষণের মধ্যেই আপনার একাউন্ট সক্রিয় করে দেবেন।</p>
    </div>

    @php $user = auth()->user(); @endphp

    {{-- বর্তমান স্ট্যাটাস --}}
    <div class="mb-6 rounded-2xl border p-4 sm:p-5 anim-fade-up {{ $user->hasActiveSubscription() ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200' }}">
        @if ($user->hasActiveSubscription())
            <p class="font-semibold text-emerald-700">✅ আপনার একাউন্ট সক্রিয় — মেয়াদ শেষ {{ $user->subscription_expires_at->format('d M Y') }} ({{ \App\Support\Bn::digits($user->daysLeft()) }} দিন বাকি)।</p>
            <p class="text-sm text-emerald-600 mt-1">মেয়াদ শেষ হওয়ার আগেই রিনিউ করলে নতুন মেয়াদ বর্তমান মেয়াদের সাথে যোগ হবে — একদিনও নষ্ট হবে না।</p>
        @else
            <p class="font-semibold text-rose-700">👋 আবার স্বাগতম! আপনার একাউন্টের মেয়াদ শেষ হয়েছে। নিচ থেকে একটা প্যাকেজ বেছে একাউন্ট সক্রিয় করুন।</p>
            <p class="text-sm text-rose-600 mt-1">চিন্তা নেই — আপনার সব রেজাল্ট ও প্রগ্রেস সুরক্ষিত আছে{{ $user->isInGrace() ? '। গ্রেস পিরিয়ডে এখনো টেস্টও দিতে পারছেন' : '' }}।</p>
        @endif
    </div>

    {{-- পেন্ডিং পেমেন্ট থাকলে --}}
    @if ($pending && $pending->isPending())
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 anim-pop">
            <div class="flex items-start gap-3">
                <span class="text-2xl">⏳</span>
                <div>
                    <p class="font-bold text-amber-800">আপনার একটি পেমেন্ট যাচাইয়ের অপেক্ষায় আছে</p>
                    <p class="text-sm text-amber-700 mt-1">
                        প্যাকেজ: <b>{{ $pending->plan?->name }}</b> ·
                        প্ল্যাটফর্ম: <b>{{ $pending->platformLabel() }}</b> ·
                        Transaction ID: <b>{{ $pending->transaction_id }}</b> ·
                        জমা দিয়েছেন: {{ $pending->created_at->diffForHumans() }}
                    </p>
                    <p class="text-xs text-amber-600 mt-2">অ্যাডমিন যাচাই করে শীঘ্রই আপনার একাউন্ট সক্রিয় করে দেবেন। নতুন আবেদনের প্রয়োজন নেই।</p>
                </div>
            </div>
        </div>
    @elseif ($pending && $pending->status === 'rejected')
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-5 anim-pop">
            <p class="font-bold text-rose-700">❌ আপনার আগের পেমেন্ট আবেদনটি বাতিল হয়েছে</p>
            @if ($pending->admin_note)
                <p class="text-sm text-rose-600 mt-1">কারণ: {{ $pending->admin_note }}</p>
            @endif
            <p class="text-xs text-rose-500 mt-2">নিচ থেকে আবার সঠিক তথ্য দিয়ে আবেদন করুন।</p>
        </div>
    @endif

    {{-- প্যাকেজ তালিকা --}}
    @if (! $pending || ! $pending->isPending())
        @if ($plans->isEmpty())
            <div class="rounded-2xl bg-white border border-dashed border-slate-300 p-10 text-center text-slate-500">
                এখনো কোনো প্যাকেজ যোগ করা হয়নি। অ্যাডমিন প্যানেল থেকে প্যাকেজ তৈরি করুন।
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($plans as $i => $plan)
                    <div class="anim-fade-up relative rounded-2xl border-2 border-slate-200 bg-white p-5 hover:border-violet-300 hover:shadow-lg hover:-translate-y-1 transition-all duration-300"
                         style="animation-delay: {{ $i * 60 }}ms">
                        @if ($i === 1 && $plans->count() > 2)
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white text-[11px] font-bold rounded-full px-3 py-1 shadow">জনপ্রিয়</span>
                        @endif
                        <h3 class="font-bold text-lg text-slate-800">{{ $plan->name }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ \App\Support\Bn::digits($plan->duration_days) }} দিন মেয়াদ</p>

                        <div class="mt-4 mb-4">
                            <span class="text-3xl font-extrabold text-violet-700">{{ $plan->priceLabel() }}</span>
                        </div>

                        @if ($plan->description)
                            <p class="text-sm text-slate-500 mb-3">{{ $plan->description }}</p>
                        @endif

                        @if (is_array($plan->features) && count($plan->features))
                            <ul class="space-y-1.5 mb-5 text-sm text-slate-600">
                                @foreach ($plan->features as $feature)
                                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">✓</span><span>{{ $feature }}</span></li>
                                @endforeach
                            </ul>
                        @endif

                        <a href="{{ route('premium.show', $plan) }}" class="block text-center rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:shadow-lg hover:shadow-fuchsia-300 text-white font-semibold py-2.5 transition">
                            নির্বাচন করুন →
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
@endsection
