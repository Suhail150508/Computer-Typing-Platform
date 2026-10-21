@extends('layouts.guest')
@section('title', 'নতুন পাসওয়ার্ড দিন')
@section('meta_robots', 'noindex, nofollow')

@section('content')
    <div class="text-center">
        <div class="mx-auto w-14 h-14 rounded-2xl bg-emerald-50 grid place-items-center text-3xl anim-fade-up">🔒</div>
        <h1 class="text-2xl font-bold mt-3">নতুন পাসওয়ার্ড দিন</h1>
        <p class="text-slate-500 text-sm mt-1 mb-6">কমপক্ষে ৬ অক্ষরের একটি পাসওয়ার্ড বেছে নিন।</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4"
          x-data="{ loading: false, show: false, pw: '', pw2: '',
                    get strength() { let s = 0; if (this.pw.length >= 6) s++; if (this.pw.length >= 10) s++; if (/[A-Za-z]/.test(this.pw) && /\d/.test(this.pw)) s++; if (/[^A-Za-z0-9]/.test(this.pw)) s++; return s; } }"
          @submit="loading = true">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label class="block text-sm font-semibold mb-1" for="email">ইমেইল</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" class="field" required autocomplete="email">
            @error('email') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1" for="password">নতুন পাসওয়ার্ড</label>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" name="password" x-model="pw" class="field pr-16" required minlength="6" autocomplete="new-password" autofocus>
                <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-brand-600" x-text="show ? 'লুকান' : 'দেখান'"></button>
            </div>
            <div class="flex gap-1 mt-2" x-show="pw.length" x-cloak>
                <template x-for="i in 4" :key="i">
                    <span class="h-1.5 flex-1 rounded-full transition-all duration-300"
                          :class="i <= strength ? (strength <= 1 ? 'bg-rose-400' : strength == 2 ? 'bg-amber-400' : 'bg-emerald-500') : 'bg-slate-200'"></span>
                </template>
            </div>
            @error('password') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1" for="password_confirmation">পাসওয়ার্ড আবার</label>
            <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation" x-model="pw2" class="field" required autocomplete="new-password">
            <p class="text-xs mt-1 text-rose-600" x-show="pw2.length && pw !== pw2" x-cloak>পাসওয়ার্ড দুটি মিলছে না।</p>
        </div>
        <button class="btn-primary" :disabled="loading" :class="loading && 'opacity-70'">
            <span x-show="!loading">পাসওয়ার্ড বদলান</span><span x-show="loading" x-cloak>অপেক্ষা করুন...</span>
        </button>
    </form>

    <p class="text-center text-sm text-slate-600 mt-6"><a href="{{ route('password.request') }}" class="text-brand-600 font-semibold hover:underline">নতুন লিংক চান?</a> · <a href="{{ route('login') }}" class="text-brand-600 font-semibold hover:underline">লগইন</a></p>
@endsection
