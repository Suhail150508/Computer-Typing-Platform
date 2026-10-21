@php
    $ops = $meta['ops'] ?? [];
    $counts = $meta['counts'] ?? ['wrong' => 0, 'extra' => 0, 'missed' => 0, 'equal' => 0];
    $ws = $meta['with_space'] ?? ['total_chars' => 0, 'correct_chars' => 0, 'wpm' => 0, 'typed_words_by_stroke' => 0];
    $reasons = $meta['fail_reasons'] ?? [];
    $mistakes = collect($ops)->filter(fn ($o) => $o[0] === 'w')->take(60);
    $pass = $attempt->isPass();
@endphp

<div x-data="{ ws: false, ai: false }">
    <div class="rounded-2xl bg-gradient-to-r from-blue-50 to-sky-50 border border-blue-100 p-3 sm:p-4">
        <div class="text-center mb-3">
            <span class="font-bold text-blue-700 text-base sm:text-xl">{{ $attempt->sourceLabel() }} — RESULTS CHECKING</span>
            <span class="ml-1 inline-block align-middle bg-lime-500 text-white text-xs font-bold rounded-full px-3 py-1 ring-4 ring-pink-100">TypingMoja</span>
        </div>
        <hr class="border-slate-800/70 mb-3">

        <div class="grid md:grid-cols-3 gap-3">
            {{-- তারিখ, ভাষা, সময় --}}
            <div class="rounded-xl bg-green-50 border-l-4 border-green-600 p-3 text-sm">
                <h3 class="font-bold text-green-700 mb-2">তারিখ, ভাষা, সময়</h3>
                <p class="py-0.5">📅 <b>তারিখ:</b> <span class="font-semibold text-green-800">{{ $attempt->created_at->format('d M Y, h:i:s a') }}</span></p>
                <p class="py-0.5">🗣 <b>ভাষা:</b> <span class="font-semibold text-green-800">{{ $attempt->language }}</span></p>
                <p class="py-0.5">⏱ <b>সময় (টাইপ):</b> <span class="font-semibold text-green-800">{{ $attempt->timeLabel() }}</span></p>
                <p class="py-0.5">🎯 <b>নির্ধারিত সময়:</b> <span class="font-semibold text-green-800">{{ \App\Support\Bn::num($attempt->duration_seconds / 60, 1) }} Min</span></p>
            </div>

            {{-- দক্ষতা, গতি --}}
            <div class="rounded-xl bg-cyan-50 border-l-4 border-cyan-700 p-3 text-sm">
                <h3 class="font-bold text-cyan-800 mb-2">দক্ষতা, গতি, গণনা <span class="font-normal text-xs" x-text="ws ? '[With Space]' : '[Without Space]'"></span></h3>
                <p class="py-0.5">🚀 <b>মিনিটে (WPM):</b> <span class="font-bold text-cyan-800" x-show="!ws">{{ \App\Support\Bn::num($attempt->wpm) }}</span><span class="font-bold text-cyan-800" x-show="ws" x-cloak>{{ \App\Support\Bn::num($ws['wpm']) }}</span></p>
                <p class="py-0.5">🎯 <b>Accuracy:</b> <span class="font-bold text-cyan-800">{{ \App\Support\Bn::num($attempt->accuracy) }}%</span></p>
                <p class="py-0.5">⌨️ <b>টাইপ (শব্দ):</b> <span class="font-bold text-cyan-800" x-show="!ws">{{ $meta['typed_words_by_stroke'] ?? 0 }}</span><span class="font-bold text-cyan-800" x-show="ws" x-cloak>{{ $ws['typed_words_by_stroke'] }}</span></p>
                <p class="py-0.5 text-fuchsia-800 font-semibold">ƒ গণনা: ৫ Stroke = ১ Word হিসেবে</p>
            </div>

            {{-- বিশ্লেষণ --}}
            <div class="rounded-xl bg-rose-50 border-l-4 border-rose-500 p-3 text-sm">
                <h3 class="font-bold text-rose-700 mb-2">বিশ্লেষণ / যাচাই</h3>
                <p class="py-0.5">🔤 <b>Character <span x-text="ws ? '(Space সহ)' : '(Space ছাড়া)'"></span>:</b> <span class="font-bold text-rose-600" x-show="!ws">{{ $attempt->total_chars_no_space }}</span><span class="font-bold text-rose-600" x-show="ws" x-cloak>{{ $ws['total_chars'] }}</span></p>
                <p class="py-0.5">✅ <b>সঠিক Character:</b> <span class="font-bold text-rose-600" x-show="!ws">{{ $attempt->correct_chars_no_space }}</span><span class="font-bold text-rose-600" x-show="ws" x-cloak>{{ $ws['correct_chars'] }}</span></p>
                <p class="py-0.5">❌ <b>ভুল শব্দ:</b> <span class="font-bold text-rose-600">{{ $attempt->wrong_words ?: '-' }}</span></p>
                <p class="py-0.5">📉 <b>ভুলের হার:</b> <span class="font-bold text-rose-600">{{ number_format($attempt->error_percent, 2) }}%</span></p>
            </div>
        </div>

        {{-- চূড়ান্ত ফলাফল --}}
        <div class="mt-3 rounded-2xl bg-gradient-to-r from-green-600 via-slate-800 to-slate-800 p-3 flex flex-wrap items-center gap-3 text-white">
            <span class="text-2xl">{{ $pass ? '✅' : '❌' }}</span>
            <span class="font-bold text-lg sm:text-2xl">Final Result:</span>
            <span class="rounded-xl bg-white px-5 py-1 font-extrabold text-xl sm:text-2xl {{ $pass ? 'text-green-600' : 'text-red-600' }}">{{ $pass ? 'PASS' : 'FAIL' }}</span>

            <div class="ml-auto flex flex-wrap gap-2">
                <button type="button" @click="ws = !ws" class="rounded-full bg-lime-500 hover:bg-lime-600 px-4 py-1.5 text-sm font-bold shadow transition" x-text="ws ? 'Without Space Result' : 'Withspace Result'"></button>
                <button type="button" @click="ai = !ai" class="rounded-full bg-amber-50 text-slate-800 hover:bg-white ring-2 ring-fuchsia-300 px-4 py-1.5 text-sm font-bold shadow transition">✨ স্মার্ট চেকার</button>
            </div>
        </div>

        @php $skip = $meta['skip_break'] ?? null; @endphp
        @if ($skip)
            <div class="mt-3 rounded-xl border border-amber-300 bg-amber-50 text-amber-900 text-sm px-4 py-3 leading-relaxed">
                <b>⚠️ {{ $skip['type'] === 'missed' ? 'একটি শব্দ/লাইন বাদ পড়েছে' : 'একটি বাড়তি শব্দ ঢুকে গেছে' }}</b>
                ({{ \App\Support\Bn::digits($skip['position']) }} নম্বর শব্দের জায়গায়: <b>{{ $skip['word'] }}</b>)।
                পরীক্ষার নিয়ম অনুযায়ী এর পর থেকে টাইপ করা <b>{{ \App\Support\Bn::digits($skip['after_words']) }}টি শব্দ</b> ভুল ধরা হয়েছে এবং সেগুলোর গতি গোনা হয়নি।
                <span class="block text-xs text-amber-700 mt-1">💡 টিপস: কোনো শব্দ বা লাইন ছুটে গেলে থেমে না গিয়ে ওই জায়গা থেকেই আবার ঠিক শব্দ ধরে টাইপ করুন, আর টাইপের সময় মাঝে মাঝে মূল লেখার দিকে চোখ রাখুন।</span>
            </div>
        @endif

        @if (! $pass && count($reasons))
            <ul class="mt-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm px-4 py-2.5 space-y-1">
                @foreach ($reasons as $r) <li>• {{ $r }}</li> @endforeach
            </ul>
        @endif
    </div>

    @include('results.partials.speed-chart')

    {{-- স্মার্ট চেকার --}}
    <div x-show="ai" x-cloak x-transition class="mt-4 rounded-2xl border border-fuchsia-200 bg-fuchsia-50/60 p-4">
        <h3 class="font-bold text-fuchsia-800 mb-3">✨ স্মার্ট চেকার — আপনার ভুলের বিশ্লেষণ</h3>
        <div class="grid grid-cols-3 gap-2 text-center text-sm mb-3">
            <div class="rounded-xl bg-white border border-red-100 p-2"><div class="text-xl font-bold text-red-600">{{ $counts['wrong'] }}</div><div class="text-xs text-slate-500">ভুল শব্দ</div></div>
            <div class="rounded-xl bg-white border border-orange-100 p-2"><div class="text-xl font-bold text-orange-600">{{ $counts['missed'] }}</div><div class="text-xs text-slate-500">বাদ পড়া শব্দ</div></div>
            <div class="rounded-xl bg-white border border-rose-100 p-2"><div class="text-xl font-bold text-rose-600">{{ $counts['extra'] }}</div><div class="text-xs text-slate-500">বাড়তি শব্দ</div></div>
        </div>

        @if ($mistakes->isNotEmpty())
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-3 py-2 text-left">আপনি লিখেছেন</th><th class="px-3 py-2 text-left">সঠিক শব্দ</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($mistakes as $m)
                            <tr><td class="px-3 py-1.5"><span class="d-wrong">{{ $m[1] }}</span></td><td class="px-3 py-1.5"><span class="d-expect">{{ $m[2] }}</span></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <p class="mt-3 text-sm text-slate-700 leading-relaxed">
            @if ($attempt->accuracy >= 98 && $attempt->wpm >= ($meta['thresholds']['min_wpm'] ?? 20))
                🎉 চমৎকার! গতি ও নির্ভুলতা দুটোই ভালো। এই ধারা ধরে রাখুন।
            @elseif ($attempt->accuracy < 90)
                ✍️ আগে নির্ভুলতায় জোর দিন — ধীরে কিন্তু শুদ্ধ টাইপ করুন। গতি নিজে থেকেই বাড়বে।
            @elseif ($attempt->wpm < ($meta['thresholds']['min_wpm'] ?? 20))
                🚀 নির্ভুলতা ভালো, এবার গতি বাড়ানোর অনুশীলন করুন। পরের ২-৩টি শব্দ আগে থেকে দেখে টাইপ করার চেষ্টা করুন।
            @else
                👍 ভালো হচ্ছে! ভুল শব্দগুলো আলাদা করে কয়েকবার অনুশীলন করুন।
            @endif
        </p>
        <p class="mt-1 text-xs text-slate-400">এটি সার্ভারের নিজস্ব শব্দ-মিলানো (diff) বিশ্লেষণ; কোনো বাইরের AI সার্ভিস ব্যবহার হয়নি।</p>
    </div>

    {{-- লেখা ও মূল প্যাসেজ --}}
    <h3 class="mt-5 mb-2 font-bold text-fuchsia-800">আপনার লেখা ও মূল প্যাসেজ বিশ্লেষণ:</h3>
    <div class="flex flex-wrap gap-2 text-xs mb-3">
        <span class="d-wrong">ভুল শব্দ (বা বাদ পড়ার পরের শব্দ)</span><span class="d-extra">বাড়তি শব্দ</span><span class="d-expect">সঠিক শব্দ (মূল)</span><span class="d-missed">বাদ পড়েছে</span>
    </div>

    <p class="font-bold mb-1.5">আপনার লেখা:</p>
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 sm:p-4 leading-[1.9] text-justify max-h-72 overflow-y-auto" lang="{{ $attempt->language === 'bangla' ? 'bn' : 'en' }}">
        @foreach ($ops as $o)
            @if ($o[0] === 'e') {{ $o[1] }}
            @elseif ($o[0] === 'w') <span class="d-wrong" title="সঠিক: {{ $o[2] }}">{{ $o[1] }}</span>
            @elseif ($o[0] === 's') <span class="d-wrong" title="বাদ পড়ার পরের শব্দ — ভুল ধরা হয়েছে">{{ $o[1] }}</span>
            @elseif ($o[0] === 'x') <span class="d-extra">{{ $o[1] }}</span>
            @endif
        @endforeach
    </div>

    <p class="font-bold mt-4 mb-1.5">মূল প্যাসেজ:</p>
    <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-3 sm:p-4 leading-[1.9] text-justify max-h-72 overflow-y-auto" lang="{{ $attempt->language === 'bangla' ? 'bn' : 'en' }}">
        @foreach ($ops as $o)
            @if ($o[0] === 'e') {{ $o[1] }}
            @elseif ($o[0] === 'w') <span class="d-expect">{{ $o[2] }}</span>
            @elseif ($o[0] === 's') @if ($o[2] !== '') <span class="d-expect">{{ $o[2] }}</span> @endif
            @elseif ($o[0] === 'm') <span class="d-missed">{{ $o[1] }}</span>
            @endif
        @endforeach
        @if (count($rest)) <span class="d-rest">{{ implode(' ', $rest) }}</span> @endif
    </div>
</div>
