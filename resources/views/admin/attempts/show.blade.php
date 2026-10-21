@extends('admin.layouts.app')
@section('title', 'অ্যাটেম্পট বিস্তারিত')

@section('content')
<div class="max-w-4xl mx-auto">
    <a href="{{ route('admin.attempts.index') }}" class="text-sm text-slate-500 hover:text-indigo-600 font-medium">← সব অ্যাটেম্পট</a>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 mt-3">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div>
                <h1 class="text-xl font-bold text-slate-800">{{ $attempt->user?->name ?? '—' }}</h1>
                <p class="text-sm text-slate-500">{{ $attempt->sourceLabel() }} · {{ $attempt->created_at->format('d M Y, h:i A') }}</p>
            </div>
            <span class="text-sm font-bold px-3 py-1.5 rounded-full {{ $attempt->isPass() ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ strtoupper($attempt->result) }}</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            @foreach ([
                ['WPM', \App\Support\Bn::num($attempt->wpm), '🚀'],
                ['Accuracy', \App\Support\Bn::num($attempt->accuracy).'%', '🎯'],
                ['ভুল শব্দ', $attempt->wrong_words, '❌'],
                ['সময়', $attempt->timeLabel(), '⏱'],
            ] as [$label, $value, $icon])
                <div class="rounded-xl bg-slate-50 p-3 text-center">
                    <div class="text-lg">{{ $icon }}</div>
                    <div class="font-bold text-slate-700">{{ $value }}</div>
                    <div class="text-xs text-slate-400">{{ $label }}</div>
                </div>
            @endforeach
        </div>

        <p class="font-bold text-slate-700 mb-1.5">ইউজারের লেখা:</p>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 leading-relaxed text-justify max-h-56 overflow-y-auto text-sm mb-4" lang="{{ $attempt->language === 'bangla' ? 'bn' : 'en' }}">
            {{ $attempt->typed_text }}
        </div>

        <p class="font-bold text-slate-700 mb-1.5">মূল প্যাসেজ:</p>
        <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-4 leading-relaxed text-justify max-h-56 overflow-y-auto text-sm" lang="{{ $attempt->language === 'bangla' ? 'bn' : 'en' }}">
            {{ $attempt->original_text }}
        </div>

        <form method="POST" action="{{ route('admin.attempts.destroy', $attempt) }}" onsubmit="return confirm('এই রেকর্ডটি মুছে ফেলতে চান?')" class="mt-5">
            @csrf @method('DELETE')
            <button class="text-sm font-semibold text-rose-600 hover:underline">🗑 এই রেকর্ডটি মুছুন</button>
        </form>
    </div>
</div>
@endsection
