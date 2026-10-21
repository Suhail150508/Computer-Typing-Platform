{{-- স্পিড টাইমলাইন: সেকেন্ড-ভিত্তিক গ্রাফ + ধাপে ধাপে ফ্লো-চার্ট (বাইরের লাইব্রেরি ছাড়া, সার্ভারে আঁকা SVG) --}}
@php
    $samples = $attempt->speed_samples;
    $target = (float) ($meta['thresholds']['min_wpm'] ?? 0);
    $sp = is_array($samples) ? \App\Support\SpeedSamples::analyze($samples, $target) : null;
@endphp

@if ($sp)
    @php
        $W = 640; $H = 240; $pl = 40; $pr = 14; $pt = 16; $pb = 30;
        $n = $sp['seconds'];
        $maxV = max($sp['peak'], $target, $sp['overall'], 10) * 1.15;
        $step = $maxV <= 40 ? 10 : ($maxV <= 100 ? 20 : ($maxV <= 200 ? 50 : 100));
        $top = (int) (ceil($maxV / $step) * $step);
        $X = fn ($t) => $pl + ($W - $pl - $pr) * (($t - 1) / max(1, $n - 1));
        $Y = fn ($v) => $pt + ($H - $pt - $pb) * (1 - min($v, $top) / $top);
        $pts = collect($sp['series'])->map(fn ($v, $i) => round($X($i + 1), 1).','.round($Y($v), 1))->implode(' ');
        $area = round($X(1), 1).','.round($Y(0), 1).' '.$pts.' '.round($X($n), 1).','.round($Y(0), 1);
        $tickEvery = max(5, (int) (ceil($n / 6 / 5) * 5));
        $levelStyle = [
            'slow' => ['bg-rose-50 border-rose-200 text-rose-700', 'ধীর', '🐢'],
            'mid' => ['bg-amber-50 border-amber-200 text-amber-700', 'মোটামুটি', '🚶'],
            'good' => ['bg-emerald-50 border-emerald-200 text-emerald-700', 'ভালো', '🏃'],
            'fast' => ['bg-violet-50 border-violet-200 text-violet-700', 'দ্রুত', '🚀'],
        ];
        $trendIcon = ['up' => '▲', 'down' => '▼', 'flat' => '▶'];
        $trendColor = ['up' => 'text-emerald-600', 'down' => 'text-rose-600', 'flat' => 'text-slate-400'];
        $diff = $sp['second_half'] - $sp['first_half'];
    @endphp

    <div class="mt-4 rounded-2xl border border-indigo-100 bg-white shadow-sm overflow-hidden anim-fade-up">
        <div class="px-4 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 text-white flex flex-wrap items-center gap-2">
            <span class="text-xl">📊</span>
            <h3 class="font-bold">স্পিড টাইমলাইন <span class="font-normal text-indigo-100 text-sm">— কখন কত স্পিড ছিল</span></h3>
        </div>

        <div class="p-3 sm:p-4">
            {{-- সারসংক্ষেপ --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-4 text-center">
                <div class="rounded-xl bg-violet-50 border border-violet-100 p-2.5">
                    <div class="text-xl font-extrabold text-violet-700">{{ \App\Support\Bn::num($sp['peak'], 0) }}</div>
                    <div class="text-[11px] text-slate-500">🚀 সর্বোচ্চ গতি (WPM)</div>
                </div>
                <div class="rounded-xl bg-rose-50 border border-rose-100 p-2.5">
                    <div class="text-xl font-extrabold text-rose-600">{{ \App\Support\Bn::num($sp['low'], 0) }}</div>
                    <div class="text-[11px] text-slate-500">🐢 সবচেয়ে ধীর (WPM)</div>
                </div>
                <div class="rounded-xl bg-sky-50 border border-sky-100 p-2.5">
                    <div class="text-xl font-extrabold text-sky-700">{{ \App\Support\Bn::num($sp['overall'], 0) }}</div>
                    <div class="text-[11px] text-slate-500">⚖️ গড় গতি (WPM)</div>
                </div>
                <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-2.5">
                    <div class="text-xl font-extrabold text-emerald-700">{{ \App\Support\Bn::digits($sp['consistency']) }}%</div>
                    <div class="text-[11px] text-slate-500">🎯 ধারাবাহিকতা</div>
                </div>
            </div>

            {{-- লাইন গ্রাফ --}}
            <svg viewBox="0 0 {{ $W }} {{ $H }}" class="w-full h-auto" role="img" aria-label="সেকেন্ড অনুযায়ী টাইপিং স্পিডের গ্রাফ">
                <defs>
                    <linearGradient id="spd-fill-{{ $attempt->id }}" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#6366f1" stop-opacity=".35"/>
                        <stop offset="100%" stop-color="#6366f1" stop-opacity=".02"/>
                    </linearGradient>
                </defs>
                @for ($g = 0; $g <= $top; $g += $step)
                    <line x1="{{ $pl }}" x2="{{ $W - $pr }}" y1="{{ $Y($g) }}" y2="{{ $Y($g) }}" stroke="#e2e8f0" stroke-width="1"/>
                    <text x="{{ $pl - 6 }}" y="{{ $Y($g) + 4 }}" text-anchor="end" font-size="11" fill="#94a3b8">{{ $g }}</text>
                @endfor
                @for ($t = 0; $t <= $n; $t += $tickEvery)
                    <text x="{{ $X(max(1, $t)) }}" y="{{ $H - 8 }}" text-anchor="middle" font-size="11" fill="#94a3b8">{{ \App\Support\SpeedSamples::clock($t) }}</text>
                @endfor

                @if ($target > 0)
                    <line x1="{{ $pl }}" x2="{{ $W - $pr }}" y1="{{ $Y($target) }}" y2="{{ $Y($target) }}" stroke="#f43f5e" stroke-width="1.5" stroke-dasharray="6 4"/>
                    <text x="{{ $W - $pr }}" y="{{ $Y($target) - 5 }}" text-anchor="end" font-size="11" fill="#e11d48">ন্যূনতম {{ \App\Support\Bn::digits((int) $target) }} WPM</text>
                @endif
                <line x1="{{ $pl }}" x2="{{ $W - $pr }}" y1="{{ $Y($sp['overall']) }}" y2="{{ $Y($sp['overall']) }}" stroke="#0ea5e9" stroke-width="1.5" stroke-dasharray="2 4"/>

                <polygon points="{{ $area }}" fill="url(#spd-fill-{{ $attempt->id }})"/>
                <polyline points="{{ $pts }}" fill="none" stroke="#4f46e5" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>

                @foreach ($sp['series'] as $i => $v)
                    @if ($n <= 120 || ($i + 1) % 5 === 0)
                        <circle cx="{{ round($X($i + 1), 1) }}" cy="{{ round($Y($v), 1) }}" r="5" fill="transparent"><title>{{ \App\Support\SpeedSamples::clock($i + 1) }} — {{ \App\Support\Bn::num($v, 0) }} WPM</title></circle>
                    @endif
                @endforeach
            </svg>
            <div class="flex flex-wrap justify-center gap-x-5 gap-y-1 mt-1 text-[11px] text-slate-500">
                <span class="inline-flex items-center gap-1.5"><span class="w-4 h-1 rounded bg-indigo-600"></span>প্রতি সেকেন্ডের গতি (গত {{ \App\Support\Bn::digits(\App\Support\SpeedSamples::WINDOW) }} সেকেন্ডের গড়)</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-4 border-t-2 border-dotted border-sky-500"></span>গড়</span>
                @if ($target > 0)<span class="inline-flex items-center gap-1.5"><span class="w-4 border-t-2 border-dashed border-rose-500"></span>পাস করার ন্যূনতম গতি</span>@endif
            </div>

            {{-- ফ্লো-চার্ট: ধাপে ধাপে গতির পরিবর্তন --}}
            <h4 class="mt-5 mb-2 font-bold text-slate-700 text-sm">🧭 গতির ধাপ (প্রতি {{ \App\Support\Bn::digits($sp['bucket']) }} সেকেন্ড)</h4>
            <div class="flex items-stretch gap-1 overflow-x-auto pb-2">
                @foreach ($sp['steps'] as $i => $s)
                    @php [$cls, $lbl, $emo] = $levelStyle[$s['level']]; @endphp
                    @if ($i > 0)
                        <div class="self-center text-slate-300 text-lg shrink-0" aria-hidden="true">➜</div>
                    @endif
                    <div class="shrink-0 w-[5.6rem] rounded-xl border {{ $cls }} px-2 py-2 text-center">
                        <div class="text-[10px] opacity-70">{{ \App\Support\SpeedSamples::clock($s['from']) }}–{{ \App\Support\SpeedSamples::clock($s['to']) }}</div>
                        <div class="text-lg leading-tight">{{ $emo }}</div>
                        <div class="font-extrabold text-base leading-tight">{{ \App\Support\Bn::num($s['wpm'], 0) }}<span class="text-[10px] font-semibold opacity-70"> WPM</span></div>
                        <div class="text-[10px] font-semibold">{{ $lbl }}@if ($s['trend']) <span class="{{ $trendColor[$s['trend']] }}">{{ $trendIcon[$s['trend']] }}</span>@endif</div>
                    </div>
                @endforeach
            </div>

            {{-- স্বয়ংক্রিয় মন্তব্য --}}
            <div class="mt-2 rounded-xl bg-slate-50 border border-slate-200 px-3 py-2.5 text-sm text-slate-700 leading-relaxed space-y-1">
                <p>
                    @if ($diff > 4)
                        📈 শুরুর চেয়ে শেষের দিকে আপনার গতি বেড়েছে (<b>{{ \App\Support\Bn::num($sp['first_half'], 0) }} → {{ \App\Support\Bn::num($sp['second_half'], 0) }} WPM</b>) — ওয়ার্ম-আপের পর ভালো ছন্দ পেয়েছেন।
                    @elseif ($diff < -4)
                        📉 শেষের দিকে গতি কমেছে (<b>{{ \App\Support\Bn::num($sp['first_half'], 0) }} → {{ \App\Support\Bn::num($sp['second_half'], 0) }} WPM</b>) — ক্লান্তি বা তাড়াহুড়োর ভুলে ধীর হতে পারে; মাঝে মাঝে হালকা বিরতি ও স্থির ছন্দে টাইপ করুন।
                    @else
                        ⚖️ পুরো সময় জুড়ে গতি মোটামুটি স্থির ছিল — এটা ভালো অভ্যাস।
                    @endif
                </p>
                @if ($sp['best'] && $sp['worst'] && $sp['best']['from'] !== $sp['worst']['from'])
                    <p>🏅 সবচেয়ে ভালো সময়: <b>{{ \App\Support\SpeedSamples::clock($sp['best']['from']) }}–{{ \App\Support\SpeedSamples::clock($sp['best']['to']) }}</b> ({{ \App\Support\Bn::num($sp['best']['wpm'], 0) }} WPM) ·
                       সবচেয়ে ধীর: <b>{{ \App\Support\SpeedSamples::clock($sp['worst']['from']) }}–{{ \App\Support\SpeedSamples::clock($sp['worst']['to']) }}</b> ({{ \App\Support\Bn::num($sp['worst']['wpm'], 0) }} WPM)</p>
                @endif
                <p class="text-[11px] text-slate-400">গ্রাফে কাঁচা গতি (ভুলসহ সব অক্ষর) দেখানো হয়েছে; চূড়ান্ত WPM ও পাস-ফেল সার্ভারের যাচাই করা ফলাফল।</p>
            </div>
        </div>
    </div>
@endif
