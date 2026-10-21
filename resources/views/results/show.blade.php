@extends('layouts.app')
@section('title', 'রেজাল্ট বিস্তারিত')

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center gap-2 mb-3">
            <a href="{{ route('results.index') }}" class="rounded-full border border-slate-200 bg-white px-4 py-1.5 text-sm font-semibold hover:bg-slate-50 transition">← সব রেজাল্ট</a>
            @if (session('just_finished') && $attempt->isPractice())
                <a href="{{ route('practice.index', ['tier' => $attempt->practiceCard?->tier]) }}" class="rounded-full bg-brand-600 text-white px-4 py-1.5 text-sm font-semibold hover:bg-brand-700 transition">টাইপিং শুরুে ফিরে যান</a>
            @else
                <a href="{{ route('dashboard') }}" class="rounded-full bg-brand-600 text-white px-4 py-1.5 text-sm font-semibold hover:bg-brand-700 transition">আবার টেস্ট দিন</a>
            @endif
        </div>

        @if (session('just_unlocked'))
            @php
                $unlockedLabel = session('just_unlocked') === 'hard' ? 'হার্ড' : 'অ্যাডভান্স';
            @endphp
            <div class="mb-4 anim-pop rounded-2xl bg-gradient-to-r from-amber-400 to-orange-500 text-white text-center px-4 py-4 shadow-lg">
                <div class="text-3xl mb-1">🎉</div>
                <p class="font-bold text-lg">অভিনন্দন! আপনি "{{ $unlockedLabel }}" ধাপ আনলক করেছেন।</p>
                <a href="{{ route('practice.index', ['tier' => session('just_unlocked')]) }}" class="inline-block mt-2 bg-white text-orange-600 font-semibold rounded-full px-4 py-1.5 text-sm">এখনই দেখুন →</a>
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-lg border border-slate-100 p-3 sm:p-6 anim-pop">
            @include('results.partials.detail')
        </div>
    </div>

    @if (session('just_finished') && $attempt->isPass())
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.fireConfetti) { window.fireConfetti(); }
            });
        </script>
        @endpush
    @endif
@endsection
