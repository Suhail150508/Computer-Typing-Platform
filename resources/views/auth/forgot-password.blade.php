@extends('layouts.guest')
@section('title', 'পাসওয়ার্ড ভুলে গেছেন?')
@section('meta_robots', 'noindex, nofollow')

@section('content')
    <div class="text-center">
        <div class="mx-auto w-14 h-14 rounded-2xl bg-blue-50 grid place-items-center text-3xl anim-fade-up">🔑</div>
        <h1 class="text-2xl font-bold mt-3">পাসওয়ার্ড ভুলে গেছেন?</h1>
        <p class="text-slate-500 text-sm mt-1 mb-6">চিন্তা নেই! রেজিস্টার করা ইমেইল দিন, আমরা রিসেটের লিংক পাঠিয়ে দেব।</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3 flex gap-2 anim-fade-up">
            <span>📩</span><p>{{ session('status') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        <div>
            <label class="block text-sm font-semibold mb-1" for="email">ইমেইল</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="field" required autofocus autocomplete="email" placeholder="you@example.com">
            @error('email') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <button class="btn-primary" :disabled="loading" :class="loading && 'opacity-70'">
            <span x-show="!loading">রিসেট লিংক পাঠান</span><span x-show="loading" x-cloak>পাঠানো হচ্ছে...</span>
        </button>
    </form>

    <p class="text-center text-sm text-slate-600 mt-6">মনে পড়েছে? <a href="{{ route('login') }}" class="text-brand-600 font-semibold hover:underline">লগইনে ফিরুন</a></p>
@endsection
