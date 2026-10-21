@extends('layouts.app')
@section('title', 'টাইপিং স্পিড বাড়ানোর উপায়')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="text-center mb-8 anim-fade-up">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-3xl shadow-lg shadow-orange-200 mb-3">💡</div>
        <h1 class="text-2xl sm:text-3xl font-bold">টাইপিং স্পিড বাড়ানোর উপায়</h1>
        <p class="text-slate-500 text-sm mt-2 max-w-xl mx-auto">ছয়টি সহজ অভ্যাস মেনে নিয়মিত অনুশীলন করলে যে কেউ ধীরে ধীরে দ্রুত ও নির্ভুল টাইপিস্ট হয়ে উঠতে পারেন। প্রতিটি ধাপ পড়ুন, তারপর "টাইপিং শুরু" মেনুতে গিয়ে প্র্যাকটিস করুন।</p>
    </div>

    <div class="grid sm:grid-cols-2 gap-4 sm:gap-5">
        @foreach ($categories as $i => $cat)
            <div class="anim-fade-up rounded-2xl bg-white border-2 border-{{ $cat['color'] }}-100 p-5 shadow-sm hover:shadow-lg hover:-translate-y-1 hover:border-{{ $cat['color'] }}-300 transition-all duration-300"
                 style="animation-delay: {{ $i * 70 }}ms">
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-12 h-12 rounded-xl bg-{{ $cat['color'] }}-100 grid place-items-center text-2xl shrink-0">{{ $cat['icon'] }}</span>
                    <h2 class="font-bold text-lg text-slate-800 leading-snug">{{ $cat['title'] }}</h2>
                </div>
                <ul class="space-y-2.5">
                    @foreach ($cat['tips'] as $tip)
                        <li class="flex items-start gap-2.5 text-[14px] text-slate-600 leading-relaxed">
                            <span class="mt-1 w-1.5 h-1.5 rounded-full bg-{{ $cat['color'] }}-400 shrink-0"></span>
                            <span>{{ $tip }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    <a href="{{ route('practice.index') }}"
       class="anim-pop mt-8 flex items-center gap-4 rounded-2xl bg-gradient-to-r from-indigo-600 via-blue-600 to-sky-500 text-white p-5 shadow-lg shadow-blue-500/30 hover:shadow-xl transition group">
        <div class="text-3xl sm:text-4xl">🎯</div>
        <div class="flex-1 min-w-0">
            <h2 class="font-bold text-lg">এখনই অনুশীলন শুরু করুন</h2>
            <p class="text-sm text-blue-100 mt-0.5">"টাইপিং শুরু" মেনুতে গিয়ে বেসিক কার্ড থেকে শুরু করুন — এই টিপসগুলো মনে রেখে প্র্যাকটিস করলে ফলাফল দ্রুতই চোখে পড়বে।</p>
        </div>
        <span class="hidden sm:inline-flex items-center gap-1 bg-white/20 group-hover:bg-white/30 rounded-full px-4 py-2 text-sm font-semibold transition shrink-0">শুরু করুন →</span>
    </a>
</div>
@endsection
