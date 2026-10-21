@extends('layouts.app')
@section('title', 'টাইপিং শুরু')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ @filemtime(public_path('css/pages.css')) }}">
@endpush

@section('content')
@php
    $tierMeta = [
        'basic' => ['label' => 'বেসিক', 'icon' => '🌱', 'sub' => 'হাতেখড়ি'],
        'advance' => ['label' => 'অ্যাডভান্স', 'icon' => '🚀', 'sub' => 'গতি বাড়ান'],
        'hard' => ['label' => 'হার্ড', 'icon' => '🔥', 'sub' => 'এক্সপার্ট লেভেল'],
    ];
    $totalCards = 0; $totalPassed = 0;
    foreach ($tierMeta as $k => $_m) {
        foreach ($board[$k]['cards'] as $r) { $totalCards++; if ($r->passed) { $totalPassed++; } }
    }
    $overall = $totalCards > 0 ? (int) round($totalPassed / $totalCards * 100) : 0;
@endphp
<div class="pg-wrap" x-data="practiceHub('{{ $tier }}')">
    {{-- ব্যানার --}}
    <section class="pg-banner anim-fade-up">
        <span class="pg-orb o1"></span><span class="pg-orb o2"></span>
        <div class="b-text">
            <h1><span class="rocket">🎯</span> টাইপিং শুরু</h1>
            <p>বেসিক থেকে শুরু করে ধাপে ধাপে এক্সপার্ট হয়ে উঠুন — প্রতিটি ধাপে কমপক্ষে {{ \App\Support\Bn::digits(\App\Models\Setting::typing('practice_unlock_threshold')) }}টি কার্ড পাস করলেই পরের ধাপ আনলক হবে।</p>
            <div style="margin-top:.8rem;display:flex;flex-wrap:wrap;gap:.65rem;align-items:center">@include('partials.typing-guide', ['variant' => 'glass'])@include('partials.exam-info')</div>
            @include('partials.level-picker')
        </div>
        <div class="pg-donut" style="--p: {{ $overall }}" title="মোট অগ্রগতি">
            <svg viewBox="0 0 36 36"><circle class="bg" cx="18" cy="18" r="15.9155" pathLength="100"/><circle class="fg" cx="18" cy="18" r="15.9155" pathLength="100"/></svg>
            <div class="mid"><div>{{ \App\Support\Bn::digits($overall) }}%<small>{{ \App\Support\Bn::digits($totalPassed) }}/{{ \App\Support\Bn::digits($totalCards) }} পাস</small></div></div>
        </div>
    </section>

    {{-- ভাষা টগল --}}
    <div class="pg-lang-toggle anim-fade-up">
        <div class="pg-seg">
            <a href="{{ route('practice.index', ['lang' => 'bangla', 'tier' => $tier]) }}" class="{{ $language === 'bangla' ? 'on' : '' }}">🇧🇩 বাংলা</a>
            <a href="{{ route('practice.index', ['lang' => 'english', 'tier' => $tier]) }}" class="{{ $language === 'english' ? 'on' : '' }}">🇬🇧 English</a>
        </div>
    </div>

    @if ($allDone)
        <div class="pg-win">
            <div class="tro">🏆</div>
            <p style="margin:.2rem 0;font-weight:800;font-size:1.05rem">অভিনন্দন! আপনি {{ $language === 'bangla' ? 'বাংলা' : 'English' }}-তে বেসিক, অ্যাডভান্স ও হার্ড — তিনটি ধাপই সম্পন্ন করেছেন।</p>
            <p style="margin:0;font-size:.85rem;opacity:.92">আপনি এখন একজন দক্ষ টাইপিস্ট। চাইলে যেকোনো কার্ড আবার অনুশীলন করে স্পিড আরও বাড়াতে পারেন।</p>
        </div>
    @endif

    {{-- টায়ার ট্যাব বাটন --}}
    <div class="pg-tiers">
        @foreach ($tierMeta as $key => $m)
            <button type="button" @click="active = '{{ $key }}'" class="pg-tier t-{{ $key }}" :class="{ 'on': active === '{{ $key }}' }">
                <span class="ti">{{ $m['icon'] }}</span>
                <span class="tl">{{ $m['label'] }}</span>
                <span class="ts">{{ $m['sub'] }}</span>
                @if ($key !== 'basic')
                    <span class="tk {{ $board[$key]['unlocked'] ? 'open' : 'lock' }}">{{ $board[$key]['unlocked'] ? '🔓 আনলক' : '🔒 লক' }}</span>
                @endif
            </button>
        @endforeach
    </div>

    @foreach ($tierMeta as $key => $m)
        <div x-show="active === '{{ $key }}'" x-cloak x-transition.opacity.duration.300ms>
            {{-- প্রগ্রেস --}}
            <div class="pg-prog c-{{ $key }}">
                @if (! $board[$key]['unlocked'])
                    <p class="p-lock">🔒
                        @if ($key === 'advance')
                            আনলক করতে বেসিকের কমপক্ষে {{ \App\Support\Bn::digits($board['basic']['threshold']) }}টি কার্ড পাস করতে হবে
                        @else
                            আনলক করতে অ্যাডভান্সের কমপক্ষে {{ \App\Support\Bn::digits($board['advance']['threshold']) }}টি কার্ড পাস করতে হবে
                        @endif
                    </p>
                @else
                    <div class="p-head">
                        <b>{{ $m['label'] }} অগ্রগতি</b>
                        <span>{{ \App\Support\Bn::digits(min($board[$key]['passed'], $board[$key]['threshold'])) }} / {{ \App\Support\Bn::digits($board[$key]['threshold']) }} পাস</span>
                    </div>
                @endif
                <div class="pg-track"><i style="--w: {{ $board[$key]['unlocked'] ? min(100, (int) round($board[$key]['passed'] / max(1, $board[$key]['threshold']) * 100)) : 0 }}%"></i></div>
                @if ($board[$key]['unlocked'] && $key !== 'hard' && $board[$key]['next_needs'] > 0)
                    <p class="p-note">আর {{ \App\Support\Bn::digits($board[$key]['next_needs']) }}টি পাস করলে পরের ধাপ আনলক হবে।</p>
                @endif
            </div>

            {{-- কার্ড গ্রিড --}}
            <div class="pg-cards">
                @foreach ($board[$key]['cards'] as $i => $row)
                    @php $c = $row->card; @endphp
                    <div class="pg-card c-{{ $key }} pg-tilt pg-reveal {{ $row->locked ? 'is-locked' : ($row->passed ? 'is-passed' : '') }}" style="--d: {{ min($i, 8) * 60 }}ms">
                        <span class="c-strip"></span>

                        @if ($row->passed)
                            <span class="pg-ribbon ok">✓</span>
                        @elseif ($row->locked)
                            <span class="pg-ribbon lk">🔒</span>
                        @endif

                        <div class="c-main">
                            <div class="c-head">
                                <span class="c-ico">{{ $c->icon }}</span>
                                <div style="min-width:0">
                                    <span class="c-no">{{ $m['label'] }} #{{ \App\Support\Bn::digits($c->order) }}</span>
                                    <h3>{{ \Illuminate\Support\Str::after($c->title, '— ') }}</h3>
                                </div>
                            </div>

                            <p class="c-desc">{{ $c->description }}</p>

                            <div class="c-chips">
                                <div class="pg-chip2"><b>⏱ {{ \App\Support\Bn::minutes($c->duration_seconds) }}</b><span>মিনিট</span></div>
                                <div class="pg-chip2"><b>🚀 {{ \App\Support\Bn::digits($c->min_wpm) }}+</b><span>WPM</span></div>
                                <div class="pg-chip2"><b>🎯 {{ \App\Support\Bn::digits($c->min_accuracy) }}%+</b><span>নির্ভুলতা</span></div>
                            </div>

                            <div class="c-best">
                                @if ($row->tries > 0)
                                    সেরা: <b>{{ \App\Support\Bn::num($row->best_wpm) }} WPM</b> · {{ \App\Support\Bn::num($row->best_accuracy) }}% · {{ \App\Support\Bn::digits($row->tries) }}বার চেষ্টা
                                @endif
                            </div>
                        </div>

                        <div class="c-act">
                            @if ($row->locked)
                                <button type="button" @click="preview({{ $c->id }})" class="pg-go locked">🔒 দেখুন (লক করা)</button>
                            @else
                                <form method="POST" action="{{ route('practice.start', $c) }}">
                                    @csrf
                                    <button class="pg-go {{ $row->passed ? 'passed' : '' }}">
                                        {{ $row->passed ? '🔁 আবার অনুশীলন' : ($row->tries > 0 ? '↻ আবার চেষ্টা করুন' : '▶ শুরু করুন') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- লক করা কার্ডের প্রিভিউ মডাল --}}
    <div x-show="modal" x-cloak class="pg-modal">
        <div class="bk" @click="modal = false"></div>
        <div x-show="modal" x-transition class="bx">
            <button type="button" @click="modal = false" class="x" aria-label="বন্ধ করুন">&times;</button>
            <template x-if="loading"><div class="pg-spin"></div></template>
            <template x-if="!loading && data">
                <div>
                    <div style="font-size:2.4rem;margin-bottom:.4rem">🔒</div>
                    <h3 style="margin:0;font-weight:800;font-size:1.1rem" x-text="data.title"></h3>
                    <p style="margin:.4rem 0 0;font-size:.85rem;color:#64748b" x-text="data.description"></p>
                    <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:.4rem;margin-top:.8rem;font-size:.72rem;color:#475569">
                        <span class="pg-pill" x-text="'⏱ ' + (Math.round(data.duration_seconds/6)/10) + ' মিনিট'"></span>
                        <span class="pg-pill" x-text="'🚀 ' + data.min_wpm + '+ WPM'"></span>
                        <span class="pg-pill" x-text="'🎯 ' + data.min_accuracy + '%+'"></span>
                    </div>
                    <div style="margin-top:1rem;padding:.7rem .9rem;border-radius:.9rem;background:#fff1f2;border:1px solid #fecdd3;color:#e11d48;font-size:.85rem" x-text="data.requirement"></div>
                </div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script defer src="{{ asset('js/pages.js') }}?v={{ @filemtime(public_path('js/pages.js')) }}"></script>
<script>
    function practiceHub(initialTier) {
        return {
            active: initialTier || 'basic',
            modal: false, loading: false, data: null,
            async preview(cardId) {
                this.modal = true; this.loading = true; this.data = null;
                try {
                    const res = await fetch(`/typing-start/card/${cardId}/preview`, { headers: { 'Accept': 'application/json' } });
                    this.data = await res.json();
                } catch (e) {
                    this.data = { title: 'তথ্য লোড করা যায়নি', description: 'আবার চেষ্টা করুন।', requirement: '' };
                } finally {
                    this.loading = false;
                }
            },
        };
    }
</script>
@endpush
@endsection
