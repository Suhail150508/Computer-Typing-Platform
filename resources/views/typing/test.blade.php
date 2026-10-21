@extends('layouts.base')
@section('title', 'টাইপিং টেস্ট')

@section('body')
    @include('partials.flash')

    <div id="typing-root" data-duration="{{ $duration }}" class="min-h-screen flex flex-col gap-3 p-3 sm:p-5">
        {{-- উপরের বার --}}
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 anim-fade-in">
            <div class="text-base sm:text-lg">
                সময়: <b id="timer" class="tabular-nums text-xl" style="font-family:'Poppins',sans-serif">{{ sprintf('%02d:%02d', intdiv($duration, 60), $duration % 60) }}</b> <span class="text-sm">মিনিট</span>
            </div>
            <div class="text-sm text-slate-500 hidden sm:block">|</div>
            <div class="text-sm text-slate-600">শব্দ: <b id="live-words" class="tabular-nums">0</b></div>
            <div class="text-sm text-slate-600">লাইভ WPM: <b id="live-wpm" class="tabular-nums">0</b> <span class="text-xs text-slate-400">(আনুমানিক)</span></div>

            <div class="ml-auto flex items-center gap-2">
                <span class="hidden md:inline text-xs text-slate-500">{{ $sourceLabel ?? $passage->lab?->name_bn }}</span>
                <button type="button" id="btn-font-down" class="w-9 h-9 rounded-xl bg-white border border-slate-200 font-bold hover:bg-slate-50" title="ফন্ট ছোট">A-</button>
                <button type="button" id="btn-font-up" class="w-9 h-9 rounded-xl bg-white border border-slate-200 font-bold hover:bg-slate-50" title="ফন্ট বড়">A+</button>
                <button type="button" id="btn-content" class="bg-indigo-500 hover:bg-indigo-600 text-white font-semibold rounded-xl px-5 py-2 shadow transition">Content</button>
            </div>
        </div>

        @if (\App\Models\Setting::strictSkip())
            <div class="text-xs sm:text-sm rounded-xl bg-amber-50 border border-amber-200 text-amber-900 px-3 py-2">⚠️ কোনো শব্দ বা লাইন বাদ পড়লে এর পর থেকে সব শব্দ ভুল ধরা হবে। একটাও না ছেড়ে টাইপ করুন।</div>
        @endif

        {{-- প্যাসেজ --}}
        <div id="passage-wrap" class="anim-fade-up">
            <div id="passage" class="relative select-none bg-white border border-slate-300 rounded-2xl p-4 sm:p-5 h-[34vh] sm:h-[38vh] overflow-y-auto text-justify leading-[1.9] shadow-sm"
                 lang="{{ $language === 'bangla' ? 'bn' : 'en' }}">
                @foreach ($paragraphs as $words)
                    <p class="mb-4 last:mb-0">@foreach ($words as $w)<span class="pw">{{ $w }}</span> @endforeach</p>
                @endforeach
            </div>
        </div>

        {{-- টাইপিং এরিয়া --}}
        <textarea id="typing-area" class="flex-1 min-h-[180px] w-full bg-white border border-slate-300 rounded-2xl p-4 sm:p-5 leading-[1.9] outline-none focus:border-brand-600 focus:ring-4 focus:ring-blue-100 transition resize-none shadow-sm"
                  placeholder="এখানে লেখা শুরু করুন...." autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                  lang="{{ $language === 'bangla' ? 'bn' : 'en' }}" aria-label="টাইপিং এরিয়া"></textarea>

        <div class="flex items-center justify-between gap-3">
            <button type="button" id="btn-finish" class="bg-lime-500 hover:bg-lime-600 active:scale-95 text-white font-bold rounded-xl px-5 py-3 shadow-lg shadow-lime-500/30 transition">Go to Finish</button>
            <p class="text-xs text-slate-400 text-right">কপি-পেস্ট বন্ধ · সময় শেষ হলে নিজে থেকেই জমা হবে</p>
        </div>

        <form id="submit-form" method="POST" action="{{ $submitRoute ?? route('typing.submit') }}" class="hidden">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="typed_text" value="">
            <input type="hidden" name="elapsed" value="0">
            <input type="hidden" name="speed_samples" value="">
        </form>
    </div>

    <dialog id="confirm-dialog">
        <div class="p-6 text-center">
            <div class="text-4xl mb-2">🏁</div>
            <h2 class="text-lg font-bold">টেস্ট শেষ করবেন?</h2>
            <p class="text-sm text-slate-500 mt-1">জমা দেওয়ার পর আর টাইপ করা যাবে না।</p>
            <div class="grid grid-cols-2 gap-3 mt-5">
                <button type="button" id="btn-cancel" class="rounded-xl border border-slate-200 py-2.5 font-semibold hover:bg-slate-50">না, চালিয়ে যাই</button>
                <button type="button" id="btn-confirm" class="rounded-xl bg-lime-500 hover:bg-lime-600 text-white py-2.5 font-bold">হ্যাঁ, জমা দিন</button>
            </div>
        </div>
    </dialog>
@endsection

@push('scripts')
    <script src="{{ asset('js/typing.js') }}?v={{ @filemtime(public_path('js/typing.js')) }}"></script>
@endpush
